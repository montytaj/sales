<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\User;
use App\Models\ProjectStage;
use App\Models\ProjectChangeOrder;
use App\Models\ProjectExpense;
use App\Models\ActivityLog;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request)
    {
        $this->authorize('view-projects');

        $query = Project::with(['customer', 'branch', 'manager']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('project_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $projects = $query->latest()->paginate(15)->withQueryString();

        return view('projects.projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create-projects');

        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $managers = User::where('is_active', true)->get();

        return view('projects.projects.create', compact('customers', 'branches', 'managers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-projects');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'expected_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $project = Project::create([
            'project_number' => Project::generateProjectNumber(),
            'name' => $validated['name'],
            'customer_id' => $validated['customer_id'],
            'branch_id' => $validated['branch_id'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'start_date' => $validated['start_date'],
            'expected_end_date' => $validated['expected_end_date'] ?? null,
            'budget' => $validated['budget'],
            'completion_percentage' => 0.00,
            'status' => 'in_progress',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Default stages
        $project->stages()->createMany([
            ['name' => 'التجهيز والتوريد', 'weight_percentage' => 30.0, 'completion_percentage' => 0.0, 'status' => 'pending'],
            ['name' => 'التصنيع والتنفيذ', 'weight_percentage' => 50.0, 'completion_percentage' => 0.0, 'status' => 'pending'],
            ['name' => 'التركيب والتسليم', 'weight_percentage' => 20.0, 'completion_percentage' => 0.0, 'status' => 'pending'],
        ]);

        ActivityLog::log(
            'project_created',
            $project,
            "Created direct project {$project->project_number}"
        );

        return redirect()->route('projects.show', $project)->with('success', 'تم إنشاء المشروع بنجاح.');
    }

    public function show($locale, Project $project)
    {
        $this->authorize('view-projects');

        $project->load(['customer', 'branch', 'manager', 'contract', 'stages', 'changeOrders.approver', 'expenses', 'signageOrders']);

        $profitability = $this->projectService->calculateProfitability($project);

        return view('projects.projects.show', compact('project', 'profitability'));
    }

    public function addStage(Request $request, $locale, Project $project)
    {
        $this->authorize('manage-projects');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'weight_percentage' => ['required', 'numeric', 'min:0.1', 'max:100'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project->stages()->create([
            'name' => $validated['name'],
            'weight_percentage' => $validated['weight_percentage'],
            'completion_percentage' => 0.00,
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'pending',
        ]);

        $this->projectService->recalculateProgress($project);

        return back()->with('success', 'تم إضافة المرحلة للمشروع إعادة إجمالي نسبة الإنجاز.');
    }

    public function updateStageProgress(Request $request, $locale, Project $project, ProjectStage $stage)
    {
        $this->authorize('manage-projects');

        $validated = $request->validate([
            'completion_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $percentage = (float) $validated['completion_percentage'];
        $status = $percentage >= 100 ? 'completed' : ($percentage > 0 ? 'in_progress' : 'pending');

        $stage->update([
            'completion_percentage' => $percentage,
            'status' => $status,
        ]);

        $this->projectService->recalculateProgress($project);

        return back()->with('success', 'تم تحديث نسبة إنجاز المرحلة وحساب التقدم التراكمي للمشروع.');
    }

    public function addChangeOrder(Request $request, $locale, Project $project)
    {
        $this->authorize('manage-projects');

        $validated = $request->validate([
            'description' => ['required', 'string'],
            'cost_impact' => ['required', 'numeric'],
            'time_impact_days' => ['required', 'integer', 'min:0'],
        ]);

        $project->changeOrders()->create([
            'order_number' => ProjectChangeOrder::generateOrderNumber(),
            'description' => $validated['description'],
            'cost_impact' => $validated['cost_impact'],
            'time_impact_days' => $validated['time_impact_days'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'تم تسجيل طلب أمر التغيير وبانتظار الاعتماد.');
    }

    public function approveChangeOrder($locale, Project $project, ProjectChangeOrder $changeOrder)
    {
        $this->authorize('manage-projects');

        try {
            $this->projectService->approveChangeOrder($changeOrder);
            return back()->with('success', 'تم اعتماد أمر التغيير وتحديث ميزانية المشروع والجدول الزمني بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function addExpense(Request $request, $locale, Project $project)
    {
        $this->authorize('manage-projects');

        $validated = $request->validate([
            'type' => ['required', 'in:material,subcontractor,labor,other'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
        ]);

        $project->expenses()->create([
            'type' => $validated['type'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'تم تسجيل المصروف/عقد الباطن بالمشروع.');
    }
}
