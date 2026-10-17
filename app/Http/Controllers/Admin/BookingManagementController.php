<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\StudioClosure;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Admin Booking Management Controller
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Preserved features:
| - Regular and follow-up appointment listing
| - Status, booking type, date and keyword filtering
| - Customer and staff details
| - Feedback relationships
| - Booking statistics
| - Cancellation with administrator password verification
| - Individual emergency rescheduling
| - Studio-wide emergency closure declaration
| - Date-range studio closures
| - Overlapping closure prevention
| - Affected appointment rescheduling
| - Closure lifting
| - Audit logging
|
| Notification improvements:
| - Preserve website notifications
| - Attempt customer email notifications
| - Read NotificationService delivery results
| - Record successful and failed email attempts
| - Record unresolved customer accounts
| - Keep successful booking changes when email fails
|
| Note:
| This controller does not manage the email transport.
| SMTP configuration is handled by Laravel.
|--------------------------------------------------------------------------
*/

class BookingManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Business Configuration
    |--------------------------------------------------------------------------
    */

    private const BUSINESS_TIMEZONE = 'Asia/Kuala_Lumpur';

    private const MAX_CLOSURE_DAYS = 30;

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Admin Booking Management Page
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Load Booking Relationships
        |--------------------------------------------------------------------------
        */

        $query = Appointment::with([
            'customer',
            'staff',
            'services',
            'feedback',
            'feedback.customer',
            'feedback.staff',
            'cancelledBy',
            'statusUpdatedBy',
            'followUpAppointment',
            'followUpAppointment.services',
            'followUpAppointment.staff',
            'followUpAppointment.customer',
            'followUpAppointment.feedback',
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Filters
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {
            $query->where(
                'status',
                $request->status
            );
        }

        if (
            $request->filled('booking_type') &&
            $request->booking_type !== 'all'
        ) {
            $query->where(
                'booking_type',
                $request->booking_type
            );
        }

        if ($request->filled('date')) {
            $query->whereDate(
                'appointment_date',
                $request->date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Search Booking Records
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->search
            );

            $query->where(function ($q) use ($search) {

                if (ctype_digit($search)) {
                    $q->orWhere(
                        'id',
                        (int) $search
                    );
                }

                $q->orWhere(
                    'follow_up_from',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhere(
                    'follow_up_reason',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhereHas(
                    'customer',
                    function ($customerQuery) use ($search) {
                        $customerQuery
                            ->where(
                                'full_name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'email',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'phone_number',
                                'like',
                                '%' . $search . '%'
                            );
                    }
                )
                ->orWhereHas(
                    'staff',
                    function ($staffQuery) use ($search) {
                        $staffQuery
                            ->where(
                                'full_name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'email',
                                'like',
                                '%' . $search . '%'
                            );
                    }
                )
                ->orWhereHas(
                    'services',
                    function ($serviceQuery) use ($search) {
                        $serviceQuery->where(
                            'service_name',
                            'like',
                            '%' . $search . '%'
                        );
                    }
                )
                ->orWhereHas(
                    'followUpAppointment.services',
                    function ($serviceQuery) use ($search) {
                        $serviceQuery->where(
                            'service_name',
                            'like',
                            '%' . $search . '%'
                        );
                    }
                )
                ->orWhereHas(
                    'feedback',
                    function ($feedbackQuery) use ($search) {

                        $feedbackQuery->where(
                            'comment',
                            'like',
                            '%' . $search . '%'
                        );

                        if (ctype_digit($search)) {
                            $feedbackQuery->orWhere(
                                'rating',
                                (int) $search
                            );
                        }
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: BOOKING RECORD ORDER
        |--------------------------------------------------------------------------
        | Display the newest created bookings first.
        | If two bookings share the same timestamp, sort by booking ID.
        |--------------------------------------------------------------------------
        */

        $bookings = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total' => Appointment::count(),

            'regular' => Appointment::where(
                'booking_type',
                'regular'
            )->count(),

            'followup' => Appointment::where(
                'booking_type',
                'followup'
            )->count(),

            'pending' => Appointment::whereIn(
                'status',
                ['pending', 'Pending']
            )->count(),

            'ongoing' => Appointment::whereIn(
                'status',
                ['ongoing', 'Ongoing']
            )->count(),

            'finished' => Appointment::whereIn(
                'status',
                ['finished', 'Finished']
            )->count(),

            'cancelled' => Appointment::whereIn(
                'status',
                ['cancelled', 'Cancelled']
            )->count(),

            'no_show' => Appointment::whereIn(
                'status',
                ['no-show', 'No Show']
            )->count(),

            'rescheduling_required' => Appointment::whereIn(
                'status',
                Appointment::RESCHEDULING_STATUSES
            )->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Studio Closure Statistics
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today(
            self::BUSINESS_TIMEZONE
        )->toDateString();

        $studioClosures = StudioClosure::with([
            'createdBy',
            'liftedBy',
        ])
            ->orderByDesc('created_at')
            ->paginate(
                10,
                ['*'],
                'closures_page'
            );

        $closureStats = [
            'active' => StudioClosure::query()
                ->active()
                ->count(),

            'lifted' => StudioClosure::where(
                'status',
                StudioClosure::STATUS_LIFTED
            )->count(),

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Current Closure Check
            |--------------------------------------------------------------------------
            | Uses the existing active/affectingDate scopes.
            |--------------------------------------------------------------------------
            */

            'currently_closed' => StudioClosure::query()
                ->active()
                ->affectingDate($today)
                ->exists(),

            'total' => StudioClosure::count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Return Booking Management View
        |--------------------------------------------------------------------------
        */

        return view('admin.bookings', [
            'bookings' => $bookings,
            'stats' => $stats,

            'filters' => [
                'status' => $request->status ?? 'all',
                'booking_type' =>
                    $request->booking_type ?? 'all',
                'date' => $request->date,
                'search' => $request->search,
            ],

            'studioClosures' => $studioClosures,
            'closureStats' => $closureStats,
            'studioToday' => $today,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Cancel Appointment
    |--------------------------------------------------------------------------
    | Administrator password and cancellation reason are required.
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        Appointment $booking
    ) {
        $validated = $request->validate([
            'admin_password' => [
                'required',
                'string',
            ],

            'cancellation_reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $admin = $this->authenticatedAdmin(
            $request
        );

        $this->verifyAdminPassword(
            $admin,
            $validated['admin_password']
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Transactional Booking Cancellation
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $booking,
            $validated,
            $request,
            $admin
        ) {

            $lockedBooking = Appointment::whereKey(
                $booking->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            $status = $this->normalizeStatus(
                $lockedBooking->status
            );

            if (
                in_array(
                    $status,
                    ['finished', 'cancelled', 'no-show'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'booking' =>
                        'This booking can no longer be cancelled',
                ]);
            }

            $now = Carbon::now(
                self::BUSINESS_TIMEZONE
            );

            $lockedBooking->update([
                'status' => 'cancelled',

                'cancellation_reason' =>
                    $validated['cancellation_reason'],

                'cancelled_by' => $admin->id,
                'cancelled_at' => $now,

                'status_updated_by' => $admin->id,
                'status_updated_at' => $now,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Cancellation Audit Log
            |--------------------------------------------------------------------------
            */

            $this->createAuditLog(
                $request,
                $admin->id,
                'cancel_booking',
                'Admin cancelled booking ' .
                $this->bookingCode($lockedBooking) .
                ' — reason: ' .
                $validated['cancellation_reason']
            );

        }, 3);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Customer After Commit
        |--------------------------------------------------------------------------
        */

        $booking->refresh()->load('customer');

        $this->notifyCustomerSafely(
            $booking,
            'Appointment Cancelled',
            'Your booking ' .
            $this->bookingCode($booking) .
            ' has been cancelled by the studio — reason: ' .
            $validated['cancellation_reason'],
            'booking_cancelled'
        );

        return redirect()
            ->route('admin.bookings')
            ->with(
                'success',
                'Booking has been cancelled successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Individual Emergency Rescheduling
    |--------------------------------------------------------------------------
    */

    public function requestRescheduling(
        Request $request,
        Appointment $booking
    ) {
        $validated = $request->validate([
            'admin_password' => [
                'required',
                'string',
            ],

            'rescheduling_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $admin = $this->authenticatedAdmin(
            $request
        );

        $this->verifyAdminPassword(
            $admin,
            $validated['admin_password']
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Preserve Existing Rescheduling Rules
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $booking,
            $validated,
            $request,
            $admin
        ) {

            $lockedBooking = Appointment::whereKey(
                $booking->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            $currentStatus = $this->normalizeStatus(
                $lockedBooking->status
            );

            if (
                !in_array(
                    $currentStatus,
                    ['pending', 'approved'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'booking' =>
                        'Only upcoming pending or approved bookings can be marked for rescheduling',
                ]);
            }

            $bookingDate = Carbon::parse(
                $lockedBooking->appointment_date
            )->format('Y-m-d');

            $startTime = substr(
                (string) $lockedBooking->start_time,
                0,
                5
            );

            $appointmentStart = Carbon::createFromFormat(
                '!Y-m-d H:i',
                $bookingDate . ' ' . $startTime,
                self::BUSINESS_TIMEZONE
            );

            $now = Carbon::now(
                self::BUSINESS_TIMEZONE
            );

            if (
                $appointmentStart->lessThanOrEqualTo($now)
            ) {
                throw ValidationException::withMessages([
                    'booking' =>
                        'Past or already-started bookings cannot be marked for rescheduling',
                ]);
            }

            $this->flagAppointmentForRescheduling(
                $lockedBooking,
                $validated['rescheduling_reason'],
                $admin->id,
                $now
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Rescheduling Audit Log
            |--------------------------------------------------------------------------
            */

            $this->createAuditLog(
                $request,
                $admin->id,
                'appointment_rescheduling_requested',
                'Admin requested rescheduling for booking ' .
                $this->bookingCode($lockedBooking) .
                ' — reason: ' .
                $validated['rescheduling_reason']
            );

        }, 3);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Rescheduling Notification
        |--------------------------------------------------------------------------
        */

        $booking->refresh()->load('customer');

        $this->notifyCustomerSafely(
            $booking,
            'Appointment Rescheduling Required',
            'Your booking ' .
            $this->bookingCode($booking) .
            ' has been affected by a schedule change — reason: ' .
            $validated['rescheduling_reason'] .
            ' — please open Booking Details to select a new schedule',
            'rescheduling_required'
        );

        return redirect()
            ->route('admin.bookings')
            ->with(
                'success',
                'Rescheduling has been requested successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Declare Studio-Wide Emergency Closure
    |--------------------------------------------------------------------------
    |
    | A closure can cover one or multiple consecutive dates.
    |
    | Affected upcoming bookings are marked for rescheduling.
    |
    | Customer notifications are attempted after commit.
    |--------------------------------------------------------------------------
    */

    public function declareStudioClosure(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validate Closure Request
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today(
            self::BUSINESS_TIMEZONE
        );

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:150',
            ],

            'start_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' .
                    $today->toDateString(),
            ],

            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],

            'reason' => [
                'required',
                'string',
                'max:2000',
            ],

            'admin_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'admin_password' => [
                'required',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Verify Administrator
        |--------------------------------------------------------------------------
        */

        $admin = $this->authenticatedAdmin(
            $request
        );

        $this->verifyAdminPassword(
            $admin,
            $validated['admin_password']
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validate Closure Duration
        |--------------------------------------------------------------------------
        */

        $startDate = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['start_date'],
            self::BUSINESS_TIMEZONE
        );

        $endDate = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['end_date'],
            self::BUSINESS_TIMEZONE
        );

        $totalDays = (int) $startDate
            ->diffInDays($endDate) + 1;

        if (
            $totalDays < 1 ||
            $totalDays > self::MAX_CLOSURE_DAYS
        ) {
            throw ValidationException::withMessages([
                'end_date' =>
                    'Studio closures may cover a maximum of 30 consecutive days',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Atomic Closure Declaration
        |--------------------------------------------------------------------------
        */

        $outcome = DB::transaction(function () use (
            $request,
            $admin,
            $validated
        ) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Serialize Administrator Actions
            |--------------------------------------------------------------------------
            */

            DB::table('users')
                ->where('id', $admin->id)
                ->lockForUpdate()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Prevent Past Closure Dates
            |--------------------------------------------------------------------------
            */

            $currentToday = Carbon::today(
                self::BUSINESS_TIMEZONE
            )->toDateString();

            if (
                $validated['start_date'] < $currentToday
            ) {
                throw ValidationException::withMessages([
                    'start_date' =>
                        'Studio closure dates cannot begin in the past',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Prevent Overlapping Active Closures
            |--------------------------------------------------------------------------
            */

            $overlap = StudioClosure::query()
                ->active()
                ->whereDate(
                    'start_date',
                    '<=',
                    $validated['end_date']
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $validated['start_date']
                )
                ->lockForUpdate()
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'start_date' =>
                        'An active studio closure already covers part of this date range',
                ]);
            }

            $now = Carbon::now(
                self::BUSINESS_TIMEZONE
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Create Closure Record
            |--------------------------------------------------------------------------
            */

            $closure = StudioClosure::create([
                'title' => $validated['title'],

                'start_date' =>
                    $validated['start_date'],

                'end_date' =>
                    $validated['end_date'],

                'reason' =>
                    $validated['reason'],

                'admin_notes' =>
                    $validated['admin_notes'] ?? null,

                'status' =>
                    StudioClosure::STATUS_ACTIVE,

                'created_by' => $admin->id,

                'declared_at' => $now,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Retrieve Affected Appointments
            |--------------------------------------------------------------------------
            |
            | Only pending or approved appointments
            | inside the closure date range are considered.
            |--------------------------------------------------------------------------
            */

            $affectedBookings = Appointment::query()
                ->whereBetween(
                    'appointment_date',
                    [
                        $validated['start_date'],
                        $validated['end_date'],
                    ]
                )
                ->whereIn(
                    'status',
                    [
                        'pending',
                        'Pending',
                        'approved',
                        'Approved',
                    ]
                )
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $affectedIds = [];

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Mark Affected Future Bookings
            |--------------------------------------------------------------------------
            */

            foreach ($affectedBookings as $booking) {

                $bookingDate = Carbon::parse(
                    $booking->appointment_date
                )->format('Y-m-d');

                $bookingTime = substr(
                    (string) $booking->start_time,
                    0,
                    5
                );

                if (
                    !preg_match(
                        '/^\d{2}:\d{2}$/',
                        $bookingTime
                    )
                ) {
                    continue;
                }

                $appointmentStart =
                    Carbon::createFromFormat(
                        '!Y-m-d H:i',
                        $bookingDate . ' ' . $bookingTime,
                        self::BUSINESS_TIMEZONE
                    );

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Do Not Change Started Bookings
                |--------------------------------------------------------------------------
                */

                if (
                    $appointmentStart->lessThanOrEqualTo($now)
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Preserve Original Booking Details
                |--------------------------------------------------------------------------
                */

                $this->flagAppointmentForRescheduling(
                    $booking,
                    'Studio-wide emergency closure: ' .
                    $validated['reason'],
                    $admin->id,
                    $now
                );

                $affectedIds[] = $booking->id;

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Log Affected Booking
                |--------------------------------------------------------------------------
                */

                $this->createAuditLog(
                    $request,
                    $admin->id,
                    'studio_closure_booking_affected',
                    'Booking ' .
                    $this->bookingCode($booking) .
                    ' requires rescheduling due to studio closure #' .
                    $closure->id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Log Closure Declaration
            |--------------------------------------------------------------------------
            */

            $this->createAuditLog(
                $request,
                $admin->id,
                'studio_closure_declared',
                'Admin declared studio closure #' .
                $closure->id .
                ' (' . $validated['title'] . ')' .
                ' from ' .
                $validated['start_date'] .
                ' to ' .
                $validated['end_date'] .
                ' — reason: ' .
                $validated['reason'] .
                ' — affected future bookings: ' .
                count($affectedIds)
            );

            return [
                'closure_id' => $closure->id,
                'affected_ids' => $affectedIds,
                'affected_count' => count($affectedIds),
                'reason' => $validated['reason'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ];

        }, 3);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Customers After Database Commit
        |--------------------------------------------------------------------------
        |
        | Sending notifications outside the closure transaction
        | prevents SMTP failures from reversing the saved closure.
        |
        | Collect notification statistics for server-side logging.
        |--------------------------------------------------------------------------
        */

        $deliveryStats = [
            'notifications_created' => 0,
            'emails_accepted' => 0,
            'emails_failed' => 0,
            'customers_unresolved' => 0,
            'notification_failures' => 0,
        ];

        if (!empty($outcome['affected_ids'])) {

            Appointment::with('customer')
                ->whereIn(
                    'id',
                    $outcome['affected_ids']
                )
                ->orderBy('id')
                ->chunkById(
                    100,
                    function ($bookings) use (
                        $outcome,
                        &$deliveryStats
                    ) {

                        foreach ($bookings as $booking) {

                            /*
                            |--------------------------------------------------------------------------
                            | NS BEAUTY COMMENT: Emergency Closure Message
                            |--------------------------------------------------------------------------
                            */

                            $notificationResult =
                                $this->notifyCustomerSafely(
                                    $booking,
                                    'Studio Emergency Closure — Rescheduling Required',
                                    'Your appointment ' .
                                    $this->bookingCode($booking) .
                                    ' has been affected by a studio closure from ' .
                                    $outcome['start_date'] .
                                    ' to ' .
                                    $outcome['end_date'] .
                                    ' — reason: ' .
                                    $outcome['reason'] .
                                    ' — please open Booking Details to choose a replacement appointment schedule',
                                    'rescheduling_required'
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | NS BEAUTY COMMENT: Summarize Delivery Results
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $notificationResult[
                                    'notification_created'
                                ] ?? false
                            ) {
                                $deliveryStats[
                                    'notifications_created'
                                ]++;
                            }

                            if (
                                $notificationResult[
                                    'email_sent'
                                ] ?? false
                            ) {
                                $deliveryStats[
                                    'emails_accepted'
                                ]++;
                            } elseif (
                                $notificationResult[
                                    'email_attempted'
                                ] ?? false
                            ) {
                                $deliveryStats[
                                    'emails_failed'
                                ]++;
                            }

                            if (
                                empty(
                                    $notificationResult[
                                        'recipient_user_id'
                                    ]
                                )
                            ) {
                                $deliveryStats[
                                    'customers_unresolved'
                                ]++;
                            }

                            if (
                                !empty(
                                    $notificationResult[
                                        'notification_error'
                                    ]
                                )
                            ) {
                                $deliveryStats[
                                    'notification_failures'
                                ]++;
                            }
                        }
                    }
                );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Closure Notification Summary
        |--------------------------------------------------------------------------
        |
        | The summary helps identify whether a missing email
        | was caused by:
        | - Missing customer account
        | - Missing email address
        | - SMTP error
        | - Website notification failure
        |--------------------------------------------------------------------------
        */

        Log::info(
            'NS Beauty studio closure notification summary',
            [
                'closure_id' => $outcome['closure_id'],
                'affected_bookings' =>
                    $outcome['affected_count'],
                'website_notifications_created' =>
                    $deliveryStats['notifications_created'],
                'emails_accepted_by_transport' =>
                    $deliveryStats['emails_accepted'],
                'email_attempts_failed' =>
                    $deliveryStats['emails_failed'],
                'customers_unresolved' =>
                    $deliveryStats['customers_unresolved'],
                'notification_failures' =>
                    $deliveryStats['notification_failures'],
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Return to Booking Management
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('admin.bookings')
            ->with(
                'success',
                'Studio closure declared successfully — ' .
                $outcome['affected_count'] .
                ' future booking(s) marked for rescheduling'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Lift Studio Closure
    |--------------------------------------------------------------------------
    |
    | Previously affected appointments are not automatically
    | restored to their original statuses.
    |--------------------------------------------------------------------------
    */

    public function liftStudioClosure(
        Request $request,
        StudioClosure $closure
    ) {
        $validated = $request->validate([
            'admin_password' => [
                'required',
                'string',
            ],

            'lift_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $admin = $this->authenticatedAdmin(
            $request
        );

        $this->verifyAdminPassword(
            $admin,
            $validated['admin_password']
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Transactional Closure Lifting
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $closure,
            $admin,
            $request,
            $validated
        ) {

            $lockedClosure = StudioClosure::whereKey(
                $closure->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedClosure->status !==
                StudioClosure::STATUS_ACTIVE
            ) {
                throw ValidationException::withMessages([
                    'closure' =>
                        'This studio closure has already been lifted',
                ]);
            }

            $now = Carbon::now(
                self::BUSINESS_TIMEZONE
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Preserve Closure History
            |--------------------------------------------------------------------------
            */

            $lockedClosure->update([
                'status' =>
                    StudioClosure::STATUS_LIFTED,

                'lifted_by' => $admin->id,
                'lifted_at' => $now,

                'lift_reason' =>
                    $validated['lift_reason'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Audit Closure Lifting
            |--------------------------------------------------------------------------
            */

            $this->createAuditLog(
                $request,
                $admin->id,
                'studio_closure_lifted',
                'Admin lifted studio closure #' .
                $lockedClosure->id .
                ' (' . $lockedClosure->title . ')' .
                ' — reason: ' .
                $validated['lift_reason']
            );

        }, 3);

        return redirect()
            ->route('admin.bookings')
            ->with(
                'success',
                'Studio closure lifted successfully — previously affected appointments still require rescheduling'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Flag Appointment for Rescheduling
    |--------------------------------------------------------------------------
    |
    | Shared between individual emergency rescheduling
    | and studio-wide closures.
    |
    | Preserves the original appointment information.
    |--------------------------------------------------------------------------
    */

    private function flagAppointmentForRescheduling(
        Appointment $booking,
        string $reason,
        int $adminId,
        Carbon $now
    ): void {

        $bookingDate = Carbon::parse(
            $booking->appointment_date
        )->format('Y-m-d');

        $booking->update([
            'status' => 'rescheduling_required',

            'rescheduling_reason' => $reason,

            'rescheduling_requested_at' => $now,

            'original_appointment_date' =>
                $booking->original_appointment_date
                    ?? $bookingDate,

            'original_start_time' =>
                $booking->original_start_time
                    ?? $booking->start_time,

            'original_staff_id' =>
                $booking->original_staff_id
                    ?? $booking->staff_id,

            'status_updated_by' => $adminId,

            'status_updated_at' => $now,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Verify Administrator Account
    |--------------------------------------------------------------------------
    */

    private function authenticatedAdmin(
        Request $request
    ) {
        $admin = $request->user();

        if (
            !$admin ||
            $admin->role !== 'admin'
        ) {
            abort(
                403,
                'Only administrators can perform this action'
            );
        }

        return $admin;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Verify Administrator Password
    |--------------------------------------------------------------------------
    */

    private function verifyAdminPassword(
        $admin,
        string $password
    ): void {

        if (
            !Hash::check(
                $password,
                $admin->password
            )
        ) {
            throw ValidationException::withMessages([
                'admin_password' =>
                    'Incorrect admin password',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Normalize Appointment Status
    |--------------------------------------------------------------------------
    */

    private function normalizeStatus(
        ?string $status
    ): string {
        return strtolower(
            str_replace(
                [' ', '_'],
                '-',
                (string) $status
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Notification Helper
    |--------------------------------------------------------------------------
    |
    | Important changes:
    | - Returns actual NotificationService result
    | - Explicitly requests email delivery
    | - Logs unresolved customer accounts
    | - Logs unsuccessful email attempts
    | - Preserves website notifications when SMTP fails
    | - Does not expose email credentials in logs
    |
    | Existing booking actions still succeed even if
    | notification delivery encounters an error.
    |--------------------------------------------------------------------------
    */

    private function notifyCustomerSafely(
        Appointment $booking,
        string $title,
        string $message,
        string $type
    ): array {

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Default Delivery Result
        |--------------------------------------------------------------------------
        */

        $result = [
            'notification_created' => false,
            'email_attempted' => false,
            'email_sent' => false,
            'email_error' => null,
            'recipient_user_id' => null,
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Confirm Customer Record
        |--------------------------------------------------------------------------
        */

        try {

            if (!$booking->customer) {

                Log::warning(
                    'NS Beauty customer notification skipped: Customer missing',
                    [
                        'appointment_id' => $booking->id,
                        'notification_type' => $type,
                    ]
                );

                return $result;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Customer Notification Link
            |--------------------------------------------------------------------------
            */

            $link = route(
                'customer.booking.details',
                $booking->id,
                false
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Request Website and Email Delivery
            |--------------------------------------------------------------------------
            |
            | Argument true:
            | Attempt SMTP email delivery.
            |
            | Final argument true:
            | Create a website notification.
            |--------------------------------------------------------------------------
            */

            $result = app(
                NotificationService::class
            )->notifyCustomer(
                $booking->customer,
                $title,
                $message,
                $link,
                $type,
                true,
                true
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Log Missing Recipient Account
            |--------------------------------------------------------------------------
            */

            if (
                empty($result['recipient_user_id'])
            ) {

                Log::warning(
                    'NS Beauty notification: Customer user account unresolved',
                    [
                        'appointment_id' => $booking->id,
                        'customer_id' =>
                            $booking->customer->id,
                        'notification_type' => $type,
                    ]
                );

                return $result;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Log Website Notification Result
            |--------------------------------------------------------------------------
            */

            if (
                !($result['notification_created'] ?? false)
            ) {

                Log::warning(
                    'NS Beauty website notification was not created',
                    [
                        'appointment_id' => $booking->id,
                        'customer_id' =>
                            $booking->customer->id,
                        'notification_type' => $type,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Log Email Transport Result
            |--------------------------------------------------------------------------
            */

            if ($result['email_sent'] ?? false) {

                Log::info(
                    'NS Beauty customer email accepted by transport',
                    [
                        'appointment_id' => $booking->id,
                        'customer_id' =>
                            $booking->customer->id,
                        'recipient_user_id' =>
                            $result['recipient_user_id'],
                        'notification_type' => $type,
                    ]
                );

            } else {

                Log::warning(
                    'NS Beauty customer email not sent',
                    [
                        'appointment_id' => $booking->id,
                        'customer_id' =>
                            $booking->customer->id,
                        'recipient_user_id' =>
                            $result['recipient_user_id'],
                        'notification_type' => $type,
                        'email_attempted' =>
                            $result['email_attempted'] ?? false,
                        'email_error' =>
                            $result['email_error'] ?? null,
                    ]
                );
            }

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Unexpected Notification Error
            |--------------------------------------------------------------------------
            |
            | Do not roll back an already committed booking
            | or studio closure due to a notification error.
            |--------------------------------------------------------------------------
            */

            $result['notification_error'] =
                $exception->getMessage();

            Log::error(
                'NS Beauty customer notification processing failed',
                [
                    'appointment_id' => $booking->id,
                    'notification_type' => $type,
                    'error' => $exception->getMessage(),
                ]
            );

            report($exception);
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking Reference Code
    |--------------------------------------------------------------------------
    */

    private function bookingCode(
        Appointment $booking
    ): string {
        return 'BK-' .
            str_pad(
                (string) $booking->id,
                4,
                '0',
                STR_PAD_LEFT
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Audit Log Helper
    |--------------------------------------------------------------------------
    |
    | Existing audit log fields are preserved.
    |--------------------------------------------------------------------------
    */

    private function createAuditLog(
        Request $request,
        int $adminId,
        string $action,
        string $description
    ): void {

        AuditLog::create([
            'user_id' => $adminId,
            'user_type' => 'admin',
            'action' => $action,
            'module' => 'Booking Management',
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
