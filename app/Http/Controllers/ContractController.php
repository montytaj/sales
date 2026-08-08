<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\ActivityLog;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContractController extends Controller
{
    use AuthorizesRequests;

    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request)
    {
        $this->authorize('view-contracts');

        $query = Contract::with(['customer', 'branch', 'project']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhere('scope_of_work', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $contracts = $query->latest()->paginate(15)->withQueryString();

        return view('projects.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $this->authorize('create-contracts');

        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('projects.contracts.create', compact('customers', 'branches'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-contracts');

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'scope_of_work' => ['required', 'string'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'warranty_terms' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],

            // Payment Milestones
            'milestones' => ['required', 'array', 'min:1'],
            'milestones.*.milestone_name' => ['required', 'string', 'max:255'],
            'milestones.*.due_date' => ['required', 'date'],
            'milestones.*.amount_type' => ['required', 'in:fixed,percentage'],
            'milestones.*.value' => ['required', 'numeric', 'min:0.01'],
        ]);

        return DB::transaction(function () use ($validated) {
            $total = (float) $validated['total_amount'];
            $discount = (float) ($validated['discount_amount'] ?? 0.0);
            $tax = (float) ($validated['tax_amount'] ?? 0.0);
            $net = round(($total - $discount) + $tax, 2);

            $contract = Contract::create([
                'contract_number' => Contract::generateContractNumber(),
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'] ?? null,
                'scope_of_work' => $validated['scope_of_work'],
                'total_amount' => $total,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'net_amount' => $net,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'warranty_terms' => $validated['warranty_terms'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'status' => 'draft',
                'is_approved' => false,
                'created_by' => auth()->id(),
            ]);

            // Save Milestones
            foreach ($validated['milestones'] as $m) {
                $val = (float) $m['value'];
                $calcAmount = $m['amount_type'] === 'percentage' ? round(($net * $val) / 100, 2) : $val;

                $contract->paymentTerms()->create([
                    'milestone_name' => $m['milestone_name'],
                    'due_date' => $m['due_date'],
                    'amount_type' => $m['amount_type'],
                    'value' => $val,
                    'calculated_amount' => $calcAmount,
                    'paid_amount' => 0.00,
                    'status' => 'pending',
                ]);
            }

            ActivityLog::log(
                'contract_created',
                $contract,
                "Created contract {$contract->contract_number} net amount SAR {$contract->net_amount}"
            );

            return redirect()->route('contracts.show', $contract)->with('success', 'تم حفظ العقد وخطة الدفعات بنجاح.');
        });
    }

    public function show($locale, Contract $contract)
    {
        $this->authorize('view-contracts');

        $contract->load(['customer', 'branch', 'paymentTerms', 'project', 'approver', 'attachments']);

        return view('projects.contracts.show', compact('contract'));
    }

    public function approve($locale, Contract $contract)
    {
        $this->authorize('approve-contracts');

        $contract->update([
            'is_approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => 'approved',
        ]);

        ActivityLog::log(
            'contract_approved',
            $contract,
            "Approved contract {$contract->contract_number}"
        );

        return back()->with('success', 'تم اعتماد العقد رسمياً.');
    }

    public function convertToProject(Request $request, $locale, Contract $contract)
    {
        $this->authorize('create-projects');

        if ($contract->project) {
            return back()->with('error', 'تم إنشاء مشروع لهذا العقد مسبقاً.');
        }

        try {
            $project = $this->projectService->createProjectFromContract($contract);
            return redirect()->route('projects.show', $project)->with('success', "تم تحويل العقد المعتمد إلى مشروع جديد برقم ({$project->project_number}) بنجاح.");
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إنشاء المشروع: ' . $e->getMessage());
        }
    }
}
