<?php

namespace App\Http\Controllers;

use App\Models\SiteSurvey;
use App\Models\Customer;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SiteSurveyController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-surveys');

        $query = SiteSurvey::with(['customer', 'assignee']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('survey_number', 'like', "%{$search}%")
                  ->orWhere('site_address', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $surveys = $query->latest()->paginate(15)->withQueryString();

        return view('projects.surveys.index', compact('surveys'));
    }

    public function create()
    {
        $this->authorize('create-surveys');

        $customers = Customer::where('is_active', true)->get();
        $surveyors = User::where('is_active', true)->get();

        return view('projects.surveys.create', compact('customers', 'surveyors'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-surveys');

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'site_address' => ['required', 'string', 'max:255'],
            'location_coordinates' => ['nullable', 'string', 'max:255'],
            'dimensions_data' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'survey_date' => ['required', 'date'],
            'photos.*' => ['nullable', 'image', 'max:10240'],
        ]);

        $survey = SiteSurvey::create([
            'survey_number' => SiteSurvey::generateSurveyNumber(),
            'customer_id' => $validated['customer_id'],
            'site_address' => $validated['site_address'],
            'location_coordinates' => $validated['location_coordinates'] ?? null,
            'dimensions_data' => $validated['dimensions_data'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'survey_date' => $validated['survey_date'],
            'status' => 'scheduled',
            'created_by' => auth()->id(),
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('attachments/surveys', 'public');
                $survey->attachments()->create([
                    'filename' => $photo->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $photo->getClientMimeType(),
                    'file_size' => $photo->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        ActivityLog::log(
            'site_survey_created',
            $survey,
            "Created site survey {$survey->survey_number}"
        );

        return redirect()->route('site-surveys.index')->with('success', 'تم تسجيل معاينة الموقع بنجاح.');
    }

    public function show($locale, SiteSurvey $siteSurvey)
    {
        $this->authorize('view-surveys');

        $siteSurvey->load(['customer', 'assignee', 'attachments']);

        return view('projects.surveys.show', compact('siteSurvey'));
    }
}
