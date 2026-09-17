<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Notification Controller
|
| Purpose:
| - Shows notifications for the logged-in user.
| - Allows one notification to be marked as read.
| - Allows all notifications to be marked as read.
|
| Defense explanation:
| This controller ensures each user only sees notifications addressed to
| their own authenticated account.
|--------------------------------------------------------------------------
*/

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AppNotification::where('recipient_user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function read(Request $request, AppNotification $notification)
    {
        if ((int) $notification->recipient_user_id !== (int) $request->user()->id) {
            abort(403, 'You are not allowed to read this notification.');
        }

        $notification->update([
            'read_at' => now(),
        ]);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    public function readAll(Request $request)
    {
        AppNotification::where('recipient_user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}