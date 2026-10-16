<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\StudioClosure;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: AUTOMATIC APPOINTMENT REMINDERS
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Purpose:
| - Send reminders 24 hours before an appointment
| - Send reminders 2 hours before an appointment
| - Create customer website notifications
| - Send customer email notifications
| - Track website and email results separately
| - Retry failed email attempts without duplicating website alerts
| - Prevent duplicate reminder processing
| - Respect emergency studio closures
| - Respect appointment cancellations and rescheduling
| - Use Malaysia Time (Asia/Kuala_Lumpur)
|
| Artisan command:
| php artisan nsbeauty:send-appointment-reminders
|
| Scheduled through:
| routes/console.php
|
| Required database table:
| appointment_reminder_logs
|
| Required notification service:
| App\Services\NotificationService
|--------------------------------------------------------------------------
*/

class SendAppointmentReminders extends Command
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: COMMAND CONFIGURATION
    |--------------------------------------------------------------------------
    */

    protected $signature =
        'nsbeauty:send-appointment-reminders';

    protected $description =
        'Process 24-hour and 2-hour NS Beauty appointment reminders';

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: BUSINESS TIMEZONE
    |--------------------------------------------------------------------------
    */

    private const TIMEZONE = 'Asia/Kuala_Lumpur';

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: REMINDER TYPES
    |--------------------------------------------------------------------------
    | Values are minutes before the appointment.
    |--------------------------------------------------------------------------
    */

    private const REMINDER_TYPES = [
        '24h' => 1440,
        '2h' => 120,
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: ELIGIBLE APPOINTMENT STATUSES
    |--------------------------------------------------------------------------
    | Emergency rescheduling, finished, cancelled, no-show,
    | and ongoing appointments are intentionally excluded.
    |--------------------------------------------------------------------------
    */

    private const ELIGIBLE_STATUSES = [
        'pending',
        'approved',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: REMINDER DELIVERY WINDOW
    |--------------------------------------------------------------------------
    | A reminder can be processed when it becomes due,
    | with a limited grace period for scheduler delays.
    |
    | Failed emails may be retried within 10 minutes
    | after their original due time.
    |
    | Reminder messages say "approximately" because
    | scheduler execution can be slightly delayed.
    |--------------------------------------------------------------------------
    */

    private const DELIVERY_WINDOW_MINUTES = 10;

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: EMAIL RETRY INTERVAL
    |--------------------------------------------------------------------------
    | Do not retry the same failed email more often
    | than once per minute.
    |--------------------------------------------------------------------------
    */

    private const RETRY_INTERVAL_SECONDS = 60;

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: MAIN COMMAND
    |--------------------------------------------------------------------------
    */

    public function handle(
        NotificationService $notificationService
    ): int {
        $now = Carbon::now(self::TIMEZONE);

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        $this->info(
            'NS Beauty reminder check: ' .
            $now->format('Y-m-d H:i:s') .
            ' MYT'
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: RETRIEVE UPCOMING APPOINTMENTS
        |--------------------------------------------------------------------------
        |
        | Reminders can apply to appointments today
        | or tomorrow.
        |
        | Existing customer relationships are preserved.
        |--------------------------------------------------------------------------
        */

        Appointment::query()
            ->with('customer')
            ->whereBetween(
                'appointment_date',
                [
                    $now->copy()->toDateString(),
                    $now->copy()->addDay()->toDateString(),
                ]
            )
            ->whereIn(
                'status',
                [
                    'pending',
                    'approved',
                    'Pending',
                    'Approved',
                ]
            )
            ->chunkById(
                100,
                function ($appointments) use (
                    $now,
                    $notificationService,
                    &$processed,
                    &$skipped,
                    &$failed
                ) {
                    foreach ($appointments as $appointment) {

                        /*
                        |--------------------------------------------------------------------------
                        | NS BEAUTY COMMENT: CHECK APPOINTMENT STATUS
                        |--------------------------------------------------------------------------
                        */

                        if (!$this->isEligible($appointment)) {
                            $skipped++;
                            continue;
                        }

                        $date = Carbon::parse(
                            $appointment->appointment_date
                        )->format('Y-m-d');

                        /*
                        |--------------------------------------------------------------------------
                        | NS BEAUTY COMMENT: CHECK STUDIO CLOSURE
                        |--------------------------------------------------------------------------
                        */

                        if ($this->isStudioClosed($date)) {
                            $skipped++;
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NS BEAUTY COMMENT: NORMALIZE START TIME
                        |--------------------------------------------------------------------------
                        */

                        $time = $this->normalizeTime(
                            $appointment->start_time
                        );

                        if ($time === null) {

                            $failed++;

                            Log::warning(
                                'NS Beauty reminder: Invalid appointment time',
                                [
                                    'appointment_id' => $appointment->id,
                                ]
                            );

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NS BEAUTY COMMENT: CREATE APPOINTMENT DATETIME
                        |--------------------------------------------------------------------------
                        */

                        try {

                            $appointmentAt =
                                Carbon::createFromFormat(
                                    '!Y-m-d H:i:s',
                                    $date . ' ' . $time,
                                    self::TIMEZONE
                                );

                        } catch (Throwable $exception) {

                            $failed++;

                            Log::error(
                                'NS Beauty reminder: Could not parse appointment',
                                [
                                    'appointment_id' => $appointment->id,
                                    'error' => $exception->getMessage(),
                                ]
                            );

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NS BEAUTY COMMENT: PROCESS EACH REMINDER TYPE
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            self::REMINDER_TYPES as $type => $minutes
                        ) {

                            $dueAt = $appointmentAt
                                ->copy()
                                ->subMinutes($minutes);

                            $secondsSinceDue = $dueAt
                                ->diffInSeconds($now, false);

                            /*
                            |--------------------------------------------------------------------------
                            | NS BEAUTY COMMENT: CHECK DELIVERY WINDOW
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $secondsSinceDue < 0 ||
                                $secondsSinceDue >=
                                    self::DELIVERY_WINDOW_MINUTES * 60
                            ) {
                                continue;
                            }

                            $result = $this->processReminder(
                                $appointment,
                                $date,
                                $time,
                                $appointmentAt,
                                $type,
                                $notificationService
                            );

                            if ($result === 'sent') {
                                $processed++;
                            } elseif ($result === 'failed') {
                                $failed++;
                            } else {
                                $skipped++;
                            }
                        }
                    }
                }
            );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: OUTPUT SUMMARY
        |--------------------------------------------------------------------------
        */

        $this->info(
            "Reminder processing finished. " .
            "Processed: {$processed}, " .
            "Skipped: {$skipped}, " .
            "Failed: {$failed}"
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: APPOINTMENT ELIGIBILITY
    |--------------------------------------------------------------------------
    */

    private function isEligible(
        Appointment $appointment
    ): bool {
        $status = strtolower(
            trim((string) $appointment->status)
        );

        return (
            in_array(
                $status,
                self::ELIGIBLE_STATUSES,
                true
            ) &&
            $appointment->customer !== null &&
            $appointment->appointment_date !== null &&
            !empty($appointment->start_time)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: STUDIO CLOSURE CHECK
    |--------------------------------------------------------------------------
    |
    | Do not send an ordinary appointment reminder
    | for an appointment on a closed business date.
    |--------------------------------------------------------------------------
    */

    private function isStudioClosed(
        string $date
    ): bool {
        return StudioClosure::query()
            ->active()
            ->affectingDate($date)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: NORMALIZE TIME
    |--------------------------------------------------------------------------
    |
    | Accept:
    | 10:00
    | 10:00:00
    |
    | Return:
    | 10:00:00
    |--------------------------------------------------------------------------
    */

    private function normalizeTime(
        mixed $time
    ): ?string {
        if ($time === null || $time === '') {
            return null;
        }

        $value = trim((string) $time);

        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        if (!preg_match(
            '/^\d{2}:\d{2}:\d{2}$/',
            $value
        )) {
            return null;
        }

        try {

            $parsed = Carbon::createFromFormat(
                '!H:i:s',
                $value,
                self::TIMEZONE
            );

            if ($parsed->format('H:i:s') !== $value) {
                return null;
            }

        } catch (Throwable $exception) {
            return null;
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: PROCESS ONE REMINDER
    |--------------------------------------------------------------------------
    */

    private function processReminder(
        Appointment $appointment,
        string $date,
        string $time,
        Carbon $appointmentAt,
        string $type,
        NotificationService $notificationService
    ): string {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: RELOAD APPOINTMENT
        |--------------------------------------------------------------------------
        |
        | Check whether the booking has changed
        | since it was initially retrieved.
        |--------------------------------------------------------------------------
        */

        $current = Appointment::query()
            ->with('customer')
            ->find($appointment->id);

        if (
            !$current ||
            !$this->isEligible($current)
        ) {
            return 'skipped';
        }

        $currentDate = Carbon::parse(
            $current->appointment_date
        )->format('Y-m-d');

        $currentTime = $this->normalizeTime(
            $current->start_time
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: CHECK RESCHEDULING OR CLOSURE
        |--------------------------------------------------------------------------
        */

        if (
            $currentDate !== $date ||
            $currentTime !== $time ||
            $this->isStudioClosed($date)
        ) {
            return 'skipped';
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: REMINDER LOG IDENTIFIER
        |--------------------------------------------------------------------------
        |
        | One reminder per:
        | - Appointment ID
        | - Reminder type
        | - Appointment date
        | - Appointment time
        |--------------------------------------------------------------------------
        */

        $logQuery = function () use (
            $appointment,
            $type,
            $date,
            $time
        ) {
            return DB::table(
                'appointment_reminder_logs'
            )
                ->where(
                    'appointment_id',
                    $appointment->id
                )
                ->where(
                    'reminder_type',
                    $type
                )
                ->where(
                    'appointment_date',
                    $date
                )
                ->where(
                    'appointment_time',
                    $time
                );
        };

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: CLAIM REMINDER RECORD
        |--------------------------------------------------------------------------
        |
        | A unique database index prevents two different
        | scheduler processes from reserving the same
        | appointment reminder.
        |--------------------------------------------------------------------------
        */

        try {

            $inserted = DB::table(
                'appointment_reminder_logs'
            )->insertOrIgnore([
                'appointment_id' => $appointment->id,
                'reminder_type' => $type,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'status' => 'pending',
                'website_notification_sent' => false,
                'email_sent' => false,
                'attempted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: HANDLE EXISTING REMINDER
            |--------------------------------------------------------------------------
            |
            | If a previous attempt failed, it may
            | be retried once per minute.
            |
            | Successful reminders will not be repeated.
            |--------------------------------------------------------------------------
            */

            if ($inserted === 0) {

                $existing = $logQuery()->first();

                if (!$existing) {
                    return 'skipped';
                }

                if (
                    $existing->status === 'sent' ||
                    (bool) $existing->email_sent
                ) {
                    return 'skipped';
                }

                if ($existing->status !== 'failed') {
                    return 'skipped';
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: RESERVE RETRY ATOMICALLY
                |--------------------------------------------------------------------------
                */

                $retryBefore = now()
                    ->subSeconds(
                        self::RETRY_INTERVAL_SECONDS
                    );

                $claimed = $logQuery()
                    ->where('status', 'failed')
                    ->where(function ($query) use ($retryBefore) {
                        $query
                            ->whereNull('attempted_at')
                            ->orWhere(
                                'attempted_at',
                                '<=',
                                $retryBefore
                            );
                    })
                    ->update([
                        'status' => 'pending',
                        'attempted_at' => now(),
                        'error_message' => null,
                        'updated_at' => now(),
                    ]);

                if ($claimed === 0) {
                    return 'skipped';
                }
            }

        } catch (Throwable $exception) {

            Log::error(
                'NS Beauty reminder: Could not reserve reminder',
                [
                    'appointment_id' => $appointment->id,
                    'reminder_type' => $type,
                    'error' => $exception->getMessage(),
                ]
            );

            return 'failed';
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: READ REMINDER DELIVERY STATE
        |--------------------------------------------------------------------------
        */

        $reminderLog = $logQuery()->first();

        if (!$reminderLog) {
            return 'failed';
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: AVOID DUPLICATE WEBSITE NOTIFICATIONS
        |--------------------------------------------------------------------------
        |
        | If the first attempt created the website alert,
        | retry only the failed email.
        |--------------------------------------------------------------------------
        */

        $createWebsiteNotification =
            !(bool) $reminderLog->website_notification_sent;

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: BUILD REMINDER MESSAGE
        |--------------------------------------------------------------------------
        */

        $bookingId = 'BK-' . str_pad(
            (string) $appointment->id,
            4,
            '0',
            STR_PAD_LEFT
        );

        $formattedDate = $appointmentAt
            ->format('F d, Y');

        $formattedTime = $appointmentAt
            ->format('h:i A');

        if ($type === '24h') {

            $title = 'Appointment Reminder - 24 Hours';

            $message =
                "Your Nail Studio & Beauty appointment {$bookingId} " .
                "is scheduled for {$formattedDate} " .
                "at {$formattedTime} MYT. " .
                "Your appointment is approximately 24 hours away.";

        } else {

            $title = 'Appointment Reminder - 2 Hours';

            $message =
                "Your Nail Studio & Beauty appointment {$bookingId} " .
                "starts in approximately 2 hours. " .
                "Your schedule is {$formattedDate} " .
                "at {$formattedTime} MYT.";
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: CUSTOMER BOOKING DETAILS LINK
        |--------------------------------------------------------------------------
        */

        try {

            $link = route(
                'customer.booking.details',
                $appointment->id
            );

        } catch (Throwable $exception) {

            $this->markReminderFailed(
                $logQuery,
                $exception->getMessage()
            );

            Log::error(
                'NS Beauty reminder: Booking route unavailable',
                [
                    'appointment_id' => $appointment->id,
                    'error' => $exception->getMessage(),
                ]
            );

            return 'failed';
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: SEND WEBSITE AND EMAIL REMINDERS
        |--------------------------------------------------------------------------
        |
        | NotificationService now returns:
        |
        | notification_created
        | email_attempted
        | email_sent
        | email_error
        | recipient_user_id
        |--------------------------------------------------------------------------
        */

        try {

            $result = $notificationService->notifyCustomer(
                $current->customer,
                $title,
                $message,
                $link,
                'appointment_reminder',
                true,
                $createWebsiteNotification
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: READ NOTIFICATION RESULTS
            |--------------------------------------------------------------------------
            */

            $websiteSent =
                (bool) $reminderLog->website_notification_sent ||
                (bool) ($result['notification_created'] ?? false);

            $emailSent =
                (bool) ($result['email_sent'] ?? false);

            $emailError =
                $result['email_error'] ?? null;

            if (
                !($result['recipient_user_id'] ?? null)
            ) {
                $emailError = 'Customer user account not found.';
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: DETERMINE PROCESSING STATUS
            |--------------------------------------------------------------------------
            |
            | Both website notification and SMTP acceptance
            | must succeed to mark the complete reminder
            | as sent.
            |--------------------------------------------------------------------------
            */

            $fullySent = $websiteSent && $emailSent;

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: UPDATE REMINDER LOG
            |--------------------------------------------------------------------------
            */

            $logQuery()->update([
                'status' => $fullySent
                    ? 'sent'
                    : 'failed',
                'website_notification_sent' => $websiteSent,
                'email_sent' => $emailSent,
                'error_message' => $fullySent
                    ? null
                    : (
                        $emailError ??
                        'Reminder notification was not fully delivered.'
                    ),
                'completed_at' => $fullySent
                    ? now()
                    : null,
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: WRITE PROCESSING LOG
            |--------------------------------------------------------------------------
            */

            Log::info(
                'NS Beauty appointment reminder processed',
                [
                    'appointment_id' => $appointment->id,
                    'reminder_type' => $type,
                    'website_notification_sent' => $websiteSent,
                    'email_sent' => $emailSent,
                    'status' => $fullySent
                        ? 'sent'
                        : 'failed',
                ]
            );

            return $fullySent
                ? 'sent'
                : 'failed';

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: HANDLE UNEXPECTED FAILURE
            |--------------------------------------------------------------------------
            */

            $this->markReminderFailed(
                $logQuery,
                $exception->getMessage()
            );

            Log::error(
                'NS Beauty reminder processing failed',
                [
                    'appointment_id' => $appointment->id,
                    'reminder_type' => $type,
                    'error' => $exception->getMessage(),
                ]
            );

            return 'failed';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: RECORD FAILED REMINDER
    |--------------------------------------------------------------------------
    */

    private function markReminderFailed(
        callable $logQuery,
        string $error
    ): void {
        try {

            $logQuery()->update([
                'status' => 'failed',
                'error_message' => $error,
                'completed_at' => null,
                'updated_at' => now(),
            ]);

        } catch (Throwable $exception) {

            Log::error(
                'NS Beauty reminder: Failed to update reminder log',
                [
                    'original_error' => $error,
                    'database_error' => $exception->getMessage(),
                ]
            );
        }
    }
}
