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
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Admin Booking Management Controller
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Existing features preserved:
| - View regular and follow-up appointments
| - Filter bookings by status, type, date and keyword
| - View customer feedback and related bookings
| - Cancel bookings with admin password verification
| - Request individual emergency rescheduling
| - Preserve original appointment information
| - Send customer notifications
| - Record booking changes in Audit Logs
|
| New integrated studio closure features:
| - Display closures inside Booking Management
| - Declare single-day or date-range closures
| - Require admin password confirmation
| - Reject overlapping active closure records
| - Flag affected future bookings for rescheduling
| - Preserve original appointment information
| - Notify affected customers after database commit
| - Lift active closures with admin password
| - Preserve historical closure records
| - Record closure actions in Audit Logs
|
| Important:
| Studio closure checks must also be added to
| the customer BookingController before the closure
| feature is considered operational
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
        | NS BEAUTY COMMENT: Status Filter
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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Type Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('booking_type') &&
            $request->booking_type !== 'all'
        ) {
            $query->where(
                'booking_type',
                $request->booking_type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Appointment Date Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            $query->whereDate(
                'appointment_date',
                $request->date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Search Bookings
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
        | NS BEAUTY COMMENT: Paginated Appointments
        |--------------------------------------------------------------------------
        */

        $bookings = $query
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
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
        | NS BEAUTY COMMENT: Integrated Studio Closure Statistics
        |--------------------------------------------------------------------------
        | Used by the Studio Closures popup inside Booking Management
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

            'currently_closed' => StudioClosure::isClosedOn(
                $today
            ),

            'total' => StudioClosure::count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Return Existing and New Page Data
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
    | - Admin password is required
    | - Closed bookings cannot be cancelled again
    | - Cancellation is recorded in Audit Logs
    | - Customer receives a notification
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
        | NS BEAUTY COMMENT: Lock and Cancel Appointment
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
        | NS BEAUTY COMMENT: Notify Customer After Cancellation
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
    | Existing functionality is preserved
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
        | NS BEAUTY COMMENT: Preserve Original Appointment Details
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
                $appointmentStart->lessThanOrEqualTo(
                    $now
                )
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
        | NS BEAUTY COMMENT: Customer Notification
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
    | This action will be triggered by the Studio Closures
    | popup inside the existing Booking Management page
    |--------------------------------------------------------------------------
    */

    public function declareStudioClosure(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validate Closure Details
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
        | NS BEAUTY COMMENT: Confirm Administrator Identity
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
        | NS BEAUTY COMMENT: Validate Date Range
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
        | Lock administrator row to serialize this admin's requests
        | Lock affected appointments before updating their statuses
        |--------------------------------------------------------------------------
        */

        $outcome = DB::transaction(function () use (
            $request,
            $admin,
            $validated
        ) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Serialize Administrative Closure Actions
            |--------------------------------------------------------------------------
            */

            DB::table('users')
                ->where('id', $admin->id)
                ->lockForUpdate()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Verify Dates Have Not Become Past
            |--------------------------------------------------------------------------
            */

            $currentToday = Carbon::today(
                self::BUSINESS_TIMEZONE
            )->toDateString();

            if (
                $validated['start_date'] <
                $currentToday
            ) {
                throw ValidationException::withMessages([
                    'start_date' =>
                        'Studio closure dates cannot begin in the past',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Reject Overlapping Active Closures
            |--------------------------------------------------------------------------
            | A new closure cannot overlap an existing active closure
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
            | NS BEAUTY COMMENT: Save New Closure
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
            | NS BEAUTY COMMENT: Find Affected Active Appointments
            |--------------------------------------------------------------------------
            | Only future pending or approved appointments are eligible
            |
            | Finished, cancelled, ongoing and no-show appointments
            | are not changed
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
            | NS BEAUTY COMMENT: Update Each Future Appointment
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
                | NS BEAUTY COMMENT: Do Not Alter Started Appointments
                |--------------------------------------------------------------------------
                */

                if (
                    $appointmentStart->lessThanOrEqualTo(
                        $now
                    )
                ) {
                    continue;
                }

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
                | NS BEAUTY COMMENT: Record Affected Booking
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
            | NS BEAUTY COMMENT: Record Closure Declaration
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
        | NS BEAUTY COMMENT: Notify After Database Commit
        |--------------------------------------------------------------------------
        | Notifications must not roll back successfully saved closures
        |--------------------------------------------------------------------------
        */

        if (!empty($outcome['affected_ids'])) {
            Appointment::with('customer')
                ->whereIn(
                    'id',
                    $outcome['affected_ids']
                )
                ->orderBy('id')
                ->chunkById(
                    100,
                    function ($bookings) use ($outcome) {
                        foreach ($bookings as $booking) {
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
                        }
                    }
                );
        }

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
    | Lifting a closure restores the date for new bookings
    | after booking restrictions are integrated
    |
    | Previously affected appointments stay in their current
    | rescheduling state and are not automatically restored
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
        | NS BEAUTY COMMENT: Lock and Lift Closure
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
            | NS BEAUTY COMMENT: Record Closure Reopening
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
    | NS BEAUTY COMMENT: Flag Appointment for Emergency Rescheduling
    |--------------------------------------------------------------------------
    | Shared by individual and studio-wide emergency actions
    |
    | Preserves:
    | - Existing appointment ID
    | - Original appointment date
    | - Original appointment start time
    | - Original staff ID
    | - Selected services
    | - Recorded service prices
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
    | NS BEAUTY COMMENT: Confirm Authenticated Administrator
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
    | NS BEAUTY COMMENT: Admin Password Verification
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
    | NS BEAUTY COMMENT: Normalize Booking Status
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
    | Uses existing NotificationService
    | In-app delivery and email are handled by the service
    |--------------------------------------------------------------------------
    */

    private function notifyCustomerSafely(
        Appointment $booking,
        string $title,
        string $message,
        string $type
    ): void {
        try {
            if (!$booking->customer) {
                return;
            }

            app(NotificationService::class)
                ->notifyCustomer(
                    $booking->customer,
                    $title,
                    $message,
                    route(
                        'customer.booking.details',
                        $booking->id,
                        false
                    ),
                    $type,
                    true
                );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking Code Helper
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
