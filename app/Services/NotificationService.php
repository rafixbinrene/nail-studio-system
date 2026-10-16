<?php

namespace App\Services;

use App\Mail\NsBeautyNotificationMail;
use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Centralized Notification Service
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Purpose:
| - Create website notifications
| - Send email notifications
| - Support administrator, staff and customer accounts
| - Support booking confirmations
| - Support emergency staff reassignment
| - Support emergency studio closure notifications
| - Support appointment rescheduling notifications
| - Support 24-hour and 2-hour appointment reminders
| - Report email delivery attempt results
| - Prevent email failures from blocking website notifications
|
| Notification process:
| 1. Identify the recipient
| 2. Create the website notification
| 3. Attempt email delivery when enabled
| 4. Return the notification processing result
| 5. Log email errors for troubleshooting
|
| Important:
| email_sent=true means the mail transport accepted
| the send operation without an exception.
| It does not guarantee inbox delivery or that the
| customer has read the email.
|--------------------------------------------------------------------------
*/

class NotificationService
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notify a Specific User
    |--------------------------------------------------------------------------
    |
    | Used by:
    | - Customer notifications
    | - Staff notifications
    | - Administrator notifications
    | - Booking confirmation
    | - Emergency closure notification
    | - Emergency rescheduling notification
    | - Appointment reminders
    |
    | Return values:
    | notification_created = website notification was saved
    | email_attempted      = an email send was attempted
    | email_sent           = mail transport accepted the send
    | email_error          = reason for email failure, if any
    | recipient_user_id    = account that received notification
    |
    | Existing callers can ignore this return value.
    |--------------------------------------------------------------------------
    */

    public function notifyUser(
        ?User $user,
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'general',
        bool $sendEmail = true,
        bool $createWebsiteNotification = true
    ): array {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Initialize Result
        |--------------------------------------------------------------------------
        */

        $result = [
            'notification_created' => false,
            'email_attempted' => false,
            'email_sent' => false,
            'email_error' => null,
            'recipient_user_id' => $user?->id,
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Verify Recipient
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            Log::warning(
                'NS Beauty notification skipped: User not found',
                [
                    'notification_type' => $type,
                    'notification_title' => $title,
                ]
            );

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Create Website Notification
        |--------------------------------------------------------------------------
        |
        | Save the notification in app_notifications so
        | the user can see it on the NS Beauty website.
        |
        | For future email-only retries, the caller can set
        | createWebsiteNotification=false.
        |
        | Existing controllers continue creating website
        | notifications by default.
        |--------------------------------------------------------------------------
        */

        if ($createWebsiteNotification) {

            AppNotification::create([
                'recipient_user_id' => $user->id,
                'recipient_role' => $user->role,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'type' => $type,
            ]);

            $result['notification_created'] = true;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Check Email Preference
        |--------------------------------------------------------------------------
        |
        | If email delivery is disabled, the website
        | notification remains available.
        |--------------------------------------------------------------------------
        */

        if (!$sendEmail) {
            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Check Email Address
        |--------------------------------------------------------------------------
        */

        $emailAddress = trim(
            (string) ($user->email ?? '')
        );

        if ($emailAddress === '') {

            $result['email_error'] =
                'Recipient has no email address.';

            Log::warning(
                'NS Beauty notification email skipped: Missing address',
                [
                    'recipient_user_id' => $user->id,
                    'notification_type' => $type,
                ]
            );

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Send Email
        |--------------------------------------------------------------------------
        |
        | Email sending is handled separately from
        | website notification creation.
        |
        | An SMTP failure will not delete the saved
        | website notification.
        |--------------------------------------------------------------------------
        */

        $result['email_attempted'] = true;

        try {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Prepare Email Link
            |--------------------------------------------------------------------------
            */

            $emailLink = $this->getEmailLink(
                $link
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Send Mail
            |--------------------------------------------------------------------------
            |
            | Uses the existing NS Beauty email template:
            | App\Mail\NsBeautyNotificationMail
            |--------------------------------------------------------------------------
            */

            Mail::to($emailAddress)->send(
                new NsBeautyNotificationMail(
                    $title,
                    $message,
                    $emailLink
                )
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Record Successful Transport
            |--------------------------------------------------------------------------
            |
            | Success means Laravel completed the mail
            | transport operation without an exception.
            |
            | Final inbox delivery remains dependent
            | on the recipient's email provider.
            |--------------------------------------------------------------------------
            */

            $result['email_sent'] = true;

            Log::info(
                'NS Beauty notification email accepted by mail transport',
                [
                    'recipient_user_id' => $user->id,
                    'notification_type' => $type,
                    'notification_title' => $title,
                ]
            );

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Handle Email Failure
            |--------------------------------------------------------------------------
            |
            | Preserve the website notification.
            | Return the error to the calling controller
            | or automatic reminder command.
            |--------------------------------------------------------------------------
            */

            $result['email_sent'] = false;

            $result['email_error'] =
                $exception->getMessage();

            Log::error(
                'NS Beauty notification email failed',
                [
                    'recipient_user_id' => $user->id,
                    'recipient_role' => $user->role,
                    'notification_type' => $type,
                    'notification_title' => $title,
                    'error' => $exception->getMessage(),
                ]
            );

            report($exception);
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notify Administrators
    |--------------------------------------------------------------------------
    |
    | Send notifications to active administrator accounts.
    |
    | Preserve the existing method arguments and behavior.
    |--------------------------------------------------------------------------
    */

    public function notifyAdmins(
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'admin',
        bool $sendEmail = true
    ): void {

        $admins = User::query()
            ->where('role', 'admin')
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
    |
    | Identify staff account by:
    | 1. Linked user_id
    | 2. Email address fallback
    |
    | Returns the notification result for tracking.
    |--------------------------------------------------------------------------
    */

    public function notifyStaff(
        ?Staff $staff,
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'staff',
        bool $sendEmail = true,
        bool $createWebsiteNotification = true
    ): array {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Missing Staff Record
        |--------------------------------------------------------------------------
        */

        if (!$staff) {

            return $this->notifyUser(
                null,
                $title,
                $message,
                $link,
                $type,
                $sendEmail,
                $createWebsiteNotification
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Find Linked User
        |--------------------------------------------------------------------------
        */

        $user = null;

        if ($staff->user_id) {

            $user = User::find(
                $staff->user_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Email Fallback
        |--------------------------------------------------------------------------
        */

        if (!$user && $staff->email) {

            $user = User::query()
                ->where(
                    'email',
                    $staff->email
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Process Staff Notification
        |--------------------------------------------------------------------------
        */

        return $this->notifyUser(
            $user,
            $title,
            $message,
            $link,
            $type,
            $sendEmail,
            $createWebsiteNotification
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notify Customer
    |--------------------------------------------------------------------------
    |
    | Used by:
    | - Booking confirmation
    | - Booking cancellation
    | - Emergency staff absence
    | - Emergency studio closure
    | - Rescheduling requirement
    | - Appointment reminders
    |
    | Identify customer account by:
    | 1. Linked user_id
    | 2. Email address fallback
    |--------------------------------------------------------------------------
    */

    public function notifyCustomer(
        ?Customer $customer,
        string $title,
        string $message,
        ?string $link = null,
        string $type = 'customer',
        bool $sendEmail = true,
        bool $createWebsiteNotification = true
    ): array {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Missing Customer Record
        |--------------------------------------------------------------------------
        */

        if (!$customer) {

            return $this->notifyUser(
                null,
                $title,
                $message,
                $link,
                $type,
                $sendEmail,
                $createWebsiteNotification
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Find Customer User Account
        |--------------------------------------------------------------------------
        */

        $user = null;

        if ($customer->user_id) {

            $user = User::find(
                $customer->user_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Email Fallback
        |--------------------------------------------------------------------------
        */

        if (!$user && $customer->email) {

            $user = User::query()
                ->where(
                    'email',
                    $customer->email
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Customer
        |--------------------------------------------------------------------------
        |
        | The result tells the calling code whether
        | the website notification was created and
        | whether the email transport accepted the send.
        |--------------------------------------------------------------------------
        */

        return $this->notifyUser(
            $user,
            $title,
            $message,
            $link,
            $type,
            $sendEmail,
            $createWebsiteNotification
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Convert Website Links for Email
    |--------------------------------------------------------------------------
    |
    | Website notifications keep their original links.
    |
    | Emails require complete URLs so that customers
    | can open them outside the NS Beauty website.
    |--------------------------------------------------------------------------
    */

    private function getEmailLink(
        ?string $link
    ): ?string {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Empty Link
        |--------------------------------------------------------------------------
        */

        if (
            $link === null ||
            trim($link) === ''
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Existing Absolute URL
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
        | NS BEAUTY COMMENT: Convert Relative Route
        |--------------------------------------------------------------------------
        |
        | Example:
        | /customer/booking-details/15
        |
        | Converted using the application's APP_URL.
        |--------------------------------------------------------------------------
        */

        return url($link);
    }
}
