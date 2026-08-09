<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkshopKioskController extends Controller
{
    use AuthorizesRequests;

    protected WorkOrderService $workOrderService;

    public function __construct(WorkOrderService $workOrderService)
    {
        $this->workOrderService = $workOrderService;
    }

    public function index()
    {
        $this->authorize('execute-work-orders');

        $activeOrders = WorkOrder::with(['customer', 'authorization'])
            ->whereIn('status', ['authorized_to_start', 'pending_execution', 'in_progress', 'paused'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->get();

        return view('workshop.kiosk.index', compact('activeOrders'));
    }

    public function action(Request $request, $locale = 'ar', $workOrder = null)
    {
        $workOrder = ($workOrder instanceof WorkOrder) ? $workOrder : WorkOrder::findOrFail($workOrder);
        $this->authorize('execute-work-orders');

        $validated = $request->validate([
            'action' => ['required', 'in:start,pause,resume,complete'],
            'good_pieces' => ['nullable', 'integer', 'min:0'],
            'waste_pieces' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            switch ($validated['action']) {
                case 'start':
                    $this->workOrderService->startExecution($workOrder, $validated['notes'] ?? null);
                    $msg = 'تم بدء التشغيل بنجاح.';
                    break;

                case 'pause':
                    $this->workOrderService->pauseExecution($workOrder, $validated['notes'] ?? null);
                    $msg = 'تم الإيقاف المؤقت للعملية.';
                    break;

                case 'resume':
                    $this->workOrderService->resumeExecution($workOrder, $validated['notes'] ?? null);
                    $msg = 'تم استئناف العملية بنجاح.';
                    break;

                case 'complete':
                    $good = (int) ($validated['good_pieces'] ?? $workOrder->sheet_count);
                    $waste = (int) ($validated['waste_pieces'] ?? 0);
                    $this->workOrderService->completeExecution($workOrder, $good, $waste, $validated['notes'] ?? null);
                    $msg = 'تم إكمال تشغيل أمر العمل بنجاح ونقله إلى جاهز للتسليم.';
                    break;
            }

            return back()->with('success', $msg);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تنفيذ الحركة: ' . $e->getMessage());
        }
    }
}
