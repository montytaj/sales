<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = SystemNotification::where('user_id', $user->id);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $notifications = $query->latest()->paginate(20)->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($locale, SystemNotification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back()->with('success', 'تم تحديد الإشعار كمقروء.');
    }

    public function markAllAsRead()
    {
        SystemNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'تم تحديد كافة الإشعارات كمقروءة.');
    }
}
