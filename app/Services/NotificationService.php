<?php

namespace App\Services;

use App\Mail\NsBeautyNotificationMail;
use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Notification Service
|
| Purpose:
| - Creates in-app notifications.
| - Sends email notifications.
| - Supports admin, staff, and customer recipients.
|
| Defense explanation:
| A centralized notification service prevents duplicated notification logic
| across controllers and makes the notification process easier to maintain.
|--------------------------------------------------------------------------
*/

class NotificationService
{
    public function notifyUser(
        ?User $user,
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'general',
        bool $sendEmail = true
    ): void {
        if (!$user) {
            return;
        }

        AppNotification::create([
            'recipient_user_id' => $user->id,
            'recipient_role' => $user->role,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'type' => $type,
        ]);

        if ($sendEmail && $user->email) {
            Mail::to($user->email)->send(
                new NsBeautyNotificationMail($title, $message, $link)
            );
        }
    }

    public function notifyAdmins(
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'admin',
        bool $sendEmail = true
    ): void {
        $admins = User::where('role', 'admin')
            ->where('status', 'active')
            ->get();

        foreach ($admins as $admin) {
            $this->notifyUser($admin, $title, $message, $link, $type, $sendEmail);
        }
    }

    public function notifyStaff(
        ?Staff $staff,
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'staff',
        bool $sendEmail = true
    ): void {
        if (!$staff) {
            return;
        }

        $user = null;

        if ($staff->user_id) {
            $user = User::find($staff->user_id);
        }

        if (!$user && $staff->email) {
            $user = User::where('email', $staff->email)->first();
        }

        $this->notifyUser($user, $title, $message, $link, $type, $sendEmail);
    }

    public function notifyCustomer(
        ?Customer $customer,
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'customer',
        bool $sendEmail = true
    ): void {
        if (!$customer) {
            return;
        }

        $user = null;

        if ($customer->user_id) {
            $user = User::find($customer->user_id);
        }

        if (!$user && $customer->email) {
            $user = User::where('email', $customer->email)->first();
        }

        $this->notifyUser($user, $title, $message, $link, $type, $sendEmail);
    }
}