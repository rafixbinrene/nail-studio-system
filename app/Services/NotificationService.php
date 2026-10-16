<?php

namespace App\Services;

use App\Mail\NsBeautyNotificationMail;
use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Centralized Notification Service
|--------------------------------------------------------------------------
| Purpose:
| - Create in-app notifications
| - Send email notifications
| - Support admin, staff and customer accounts
| - Support emergency staff reassignment
| - Support appointment rescheduling notices
| - Prevent email delivery failures from blocking in-app notifications
|
| Notification delivery:
| 1 - Save notification to the database
| 2 - Attempt to send the email
| 3 - Log email delivery errors when they occur
|
| The actual email template is handled by NsBeautyNotificationMail
|--------------------------------------------------------------------------
*/

class NotificationService
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notify a Specific User
    |--------------------------------------------------------------------------
    | Used by:
    | - Customer notifications
    | - Staff notifications
    | - Admin notifications
    | - Booking confirmations
    | - Emergency scheduling notifications
    |--------------------------------------------------------------------------
    */

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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Create In-App Notification
        |--------------------------------------------------------------------------
        | The notification is stored in the existing app notifications table
        | This allows the recipient to view the notification on the website
        |--------------------------------------------------------------------------
        */

        AppNotification::create([
            'recipient_user_id' => $user->id,
            'recipient_role' => $user->role,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'type' => $type,
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Send Email Notification
        |--------------------------------------------------------------------------
        | Do not interrupt the in-app notification workflow
        | if the mail provider fails
        |--------------------------------------------------------------------------
        */

        if (!$sendEmail || !$user->email) {
            return;
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Build Email Link
            |--------------------------------------------------------------------------
            | A relative website route is converted into an absolute URL
            | so the email recipient can open it outside the website
            |--------------------------------------------------------------------------
            */

            $emailLink = $this->getEmailLink($link);

            Mail::to($user->email)->send(
                new NsBeautyNotificationMail(
                    $title,
                    $message,
                    $emailLink
                )
            );

        } catch (\Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Record Email Delivery Failure
            |--------------------------------------------------------------------------
            | The in-app notification remains saved
            | The error is reported for administrator troubleshooting
            |--------------------------------------------------------------------------
            */

            Log::error(
                'NS Beauty notification email failed',
                [
                    'recipient_user_id' => $user->id,
                    'recipient_role' => $user->role,
                    'notification_type' => $type,
                    'error' => $exception->getMessage(),
                ]
            );

            report($exception);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notify Administrators
    |--------------------------------------------------------------------------
    | Sends notifications to active administrator accounts
    |--------------------------------------------------------------------------
    */

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

            $this->notifyUser(
                $admin,
                $title,
                $message,
                $link,
                $type,
                $sendEmail
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notify Staff
    |--------------------------------------------------------------------------
    | Finds the linked user account
    | Uses user_id first and email as a fallback
    |--------------------------------------------------------------------------
    */

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
            $user = User::where(
                'email',
                $staff->email
            )->first();
        }

        $this->notifyUser(
            $user,
            $title,
            $message,
            $link,
            $type,
            $sendEmail
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notify Customer
    |--------------------------------------------------------------------------
    | Finds the customer's linked login account
    | Saves a website notification and attempts email delivery
    |--------------------------------------------------------------------------
    */

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
            $user = User::where(
                'email',
                $customer->email
            )->first();
        }

        $this->notifyUser(
            $user,
            $title,
            $message,
            $link,
            $type,
            $sendEmail
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Convert Website Links for Email
    |--------------------------------------------------------------------------
    | In-app notifications keep the original relative link
    | Email notifications receive a complete website URL
    |--------------------------------------------------------------------------
    */

    private function getEmailLink(?string $link): ?string
    {
        if ($link === null || trim($link) === '') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Already an Absolute URL
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with($link, 'https://') ||
            str_starts_with($link, 'http://')
        ) {
            return $link;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Convert Relative Application Route
        |--------------------------------------------------------------------------
        | Example:
        | /customer/booking-details/15
        |
        | Becomes:
        | https://your-domain.com/customer/booking-details/15
        |--------------------------------------------------------------------------
        */

        return url($link);
    }
}
