<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\StaffDayOff;
use App\Models\StudioClosure;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Booking Details Controller
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Responsibilities:
| - Display customer-owned appointment details
| - Preserve existing booking history relationships
| - Support emergency customer rescheduling
| - Verify appointment ownership and eligibility
| - Preserve original booking details and selected services
| - Enforce Sunday closure and seven-day booking window
| - Block active studio-wide emergency closure dates
| - Validate staff skills, status and day-offs
| - Prevent overlapping appointments
| - Record successful rescheduling in Audit Logs
| - Notify customer, assigned staff and administrators
|
| Important:
| - Only rescheduling_required appointments are eligible
| - Rescheduling updates the existing appointment
| - Appointment ID, services and price are preserved
|--------------------------------------------------------------------------
*/

class BookingDetailsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Studio Booking Configuration
    |--------------------------------------------------------------------------
    */

    private const BUSINESS_TIMEZONE = 'Asia/Kuala_Lumpur';

    private const BOOKING_WINDOW_DAYS = 6;

    private const BOOKING_TIMES = [
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Display Booking Details
    |--------------------------------------------------------------------------
    | Only the customer who owns the booking may view it
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, int $id)
    {
        $customer = $this->getAuthenticatedCustomer(
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Load Existing Booking Relationships
        |--------------------------------------------------------------------------
        */

        $appointment = Appointment::with([
            'customer',
            'staff',
            'services',
            'cancelledBy',
            'statusUpdatedBy',
            'originalStaff',
            'rescheduledBy',
            'followUpAppointment',
            'followUpAppointment.services',
            'followUpAppointment.staff',
            'followUpAppointment.customer',
        ])
            ->where('customer_id', $customer->id)
            ->whereKey($id)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Eligible Replacement Beauticians
        |--------------------------------------------------------------------------
        | Only staff qualified for all selected services are shown
        |--------------------------------------------------------------------------
        */

        $reschedulingStaff = collect();

        if ($appointment->requiresRescheduling()) {
            $serviceIds = $appointment->services
                ->pluck('id')
                ->map(
                    fn ($serviceId) => (int) $serviceId
                )
                ->all();

            if (
                count($serviceIds) > 0 &&
                Schema::hasTable('staff_services')
            ) {
                $reschedulingStaff = Staff::with('services')
                    ->whereIn(
                        'status',
                        ['Active', 'active']
                    )
                    ->orderBy('full_name')
                    ->get()
                    ->filter(
                        function ($staff) use ($serviceIds) {
                            $assignedIds = $staff->services
                                ->pluck('id')
                                ->map(
                                    fn ($serviceId) =>
                                        (int) $serviceId
                                )
                                ->all();

                            return empty(
                                array_diff(
                                    $serviceIds,
                                    $assignedIds
                                )
                            );
                        }
                    )
                    ->values();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Active Studio Closures
        |--------------------------------------------------------------------------
        | Make closure dates available to the Booking Details view
        | for displaying closed days in the rescheduling interface
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today(
            self::BUSINESS_TIMEZONE
        );

        $reschedulingStudioClosures = StudioClosure::query()
            ->active()
            ->whereDate(
                'end_date',
                '>=',
                $today->toDateString()
            )
            ->whereDate(
                'start_date',
                '<=',
                $today->copy()
                    ->addDays(self::BOOKING_WINDOW_DAYS)
                    ->toDateString()
            )
            ->orderBy('start_date')
            ->get();

        return view('customer.booking-details', [
            'appointment' => $appointment,
            'customer' => $customer,
            'reschedulingStaff' => $reschedulingStaff,
            'reschedulingStudioClosures' =>
                $reschedulingStudioClosures,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Emergency Rescheduling
    |--------------------------------------------------------------------------
    | Updates the SAME appointment record
    |
    | Does not:
    | - Create a second appointment
    | - Change selected services
    | - Change total price
    | - Remove original booking history
    |--------------------------------------------------------------------------
    */

    public function reschedule(Request $request, int $id)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Authenticated Customer
        |--------------------------------------------------------------------------
        */

        $customer = $this->getAuthenticatedCustomer(
            $request
        );

        $today = Carbon::today(
            self::BUSINESS_TIMEZONE
        );

        $maximumDate = $today->copy()
            ->addDays(self::BOOKING_WINDOW_DAYS);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validate Customer Inputs
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'appointment_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . $today->toDateString(),
                'before_or_equal:' . $maximumDate->toDateString(),
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::in(self::BOOKING_TIMES),
            ],

            'staff_id' => [
                'required',
                'integer',
                'exists:staff,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Check Sunday Closure
        |--------------------------------------------------------------------------
        | Sundays are permanently unavailable
        |--------------------------------------------------------------------------
        */

        $selectedDate = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['appointment_date'],
            self::BUSINESS_TIMEZONE
        );

        if ($selectedDate->isSunday()) {
            throw ValidationException::withMessages([
                'appointment_date' =>
                    'The studio is closed every Sunday — please select another date',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Check Studio-Wide Emergency Closure
        |--------------------------------------------------------------------------
        | A customer cannot reschedule onto a date covered
        | by an active studio closure
        |--------------------------------------------------------------------------
        */

        $activeClosure = StudioClosure::activeClosureOn(
            $validated['appointment_date']
        );

        if ($activeClosure) {
            throw ValidationException::withMessages([
                'appointment_date' =>
                    'The studio is temporarily closed on this date — ' .
                    $activeClosure->reason .
                    ' — please select another date',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Build Malaysian Appointment Time
        |--------------------------------------------------------------------------
        */

        $newStart = Carbon::createFromFormat(
            '!Y-m-d H:i',
            $validated['appointment_date'] . ' ' .
            $validated['start_time'],
            self::BUSINESS_TIMEZONE
        );

        if (
            $newStart->lessThanOrEqualTo(
                Carbon::now(self::BUSINESS_TIMEZONE)
            )
        ) {
            throw ValidationException::withMessages([
                'start_time' =>
                    'The selected appointment time has already passed',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Verify Selected Staff
        |--------------------------------------------------------------------------
        */

        $staff = Staff::whereKey(
            $validated['staff_id']
        )
            ->whereIn(
                'status',
                ['Active', 'active']
            )
            ->first();

        if (!$staff) {
            throw ValidationException::withMessages([
                'staff_id' =>
                    'The selected beautician is unavailable',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Atomic Rescheduling Transaction
        |--------------------------------------------------------------------------
        | Lock staff to coordinate with ordinary booking creation
        | Lock appointment to prevent concurrent rescheduling
        |--------------------------------------------------------------------------
        */

        $outcome = DB::transaction(
            function () use (
                $customer,
                $id,
                $validated,
                $newStart
            ) {
                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Lock Selected Staff
                |--------------------------------------------------------------------------
                */

                $lockedStaff = Staff::whereKey(
                    $validated['staff_id']
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    strtolower(
                        (string) $lockedStaff->status
                    ) !== 'active'
                ) {
                    throw ValidationException::withMessages([
                        'staff_id' =>
                            'The selected beautician is no longer available',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Verify Ownership and Lock Appointment
                |--------------------------------------------------------------------------
                */

                $appointment = Appointment::whereKey($id)
                    ->where(
                        'customer_id',
                        $customer->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Allow Emergency Rescheduling Only
                |--------------------------------------------------------------------------
                */

                if (!$appointment->requiresRescheduling()) {
                    throw ValidationException::withMessages([
                        'booking' =>
                            'This appointment is not eligible for emergency rescheduling',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Validate Date Again
                |--------------------------------------------------------------------------
                */

                $currentToday = Carbon::today(
                    self::BUSINESS_TIMEZONE
                );

                $currentMaximum = $currentToday->copy()
                    ->addDays(
                        self::BOOKING_WINDOW_DAYS
                    );

                if (
                    $newStart->lessThanOrEqualTo(
                        Carbon::now(self::BUSINESS_TIMEZONE)
                    ) ||
                    $newStart->toDateString() <
                        $currentToday->toDateString() ||
                    $newStart->toDateString() >
                        $currentMaximum->toDateString() ||
                    $newStart->isSunday()
                ) {
                    throw ValidationException::withMessages([
                        'appointment_date' =>
                            'The selected appointment date is no longer available',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Recheck Studio Emergency Closure
                |--------------------------------------------------------------------------
                | Recheck against the database inside the transaction
                | to reject requests when closure status has changed
                |--------------------------------------------------------------------------
                */

                $closureDuringRescheduling =
                    StudioClosure::query()
                        ->active()
                        ->affectingDate(
                            $validated['appointment_date']
                        )
                        ->lockForUpdate()
                        ->first();

                if ($closureDuringRescheduling) {
                    throw ValidationException::withMessages([
                        'appointment_date' =>
                            'The studio is closed on the selected date — ' .
                            $closureDuringRescheduling->reason .
                            ' — please choose another date',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Preserve Existing Services
                |--------------------------------------------------------------------------
                */

                $appointment->load('services');

                if ($appointment->services->isEmpty()) {
                    throw ValidationException::withMessages([
                        'booking' =>
                            'This appointment has no recorded services',
                    ]);
                }

                $serviceIds = $appointment->services
                    ->pluck('id')
                    ->map(
                        fn ($serviceId) =>
                            (int) $serviceId
                    );

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Use Recorded Service Durations
                |--------------------------------------------------------------------------
                | Preserve the duration of original services
                |--------------------------------------------------------------------------
                */

                $duration = $appointment->services->sum(
                    function ($service) {
                        $recordedDuration = (int) (
                            $service->pivot->duration ?? 0
                        );

                        return $recordedDuration > 0
                            ? $recordedDuration
                            : (int) (
                                $service->duration ?? 0
                            );
                    }
                );

                if ($duration <= 0) {
                    throw ValidationException::withMessages([
                        'booking' =>
                            'The appointment duration could not be verified',
                    ]);
                }

                $newEnd = $newStart->copy()
                    ->addMinutes($duration);

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Verify Staff Qualifications
                |--------------------------------------------------------------------------
                */

                if (
                    !Schema::hasTable('staff_services')
                ) {
                    throw ValidationException::withMessages([
                        'staff_id' =>
                            'Staff service assignments are unavailable',
                    ]);
                }

                $assignedServices = DB::table(
                    'staff_services'
                )
                    ->where(
                        'staff_id',
                        $lockedStaff->id
                    )
                    ->pluck('service_id')
                    ->map(
                        fn ($serviceId) =>
                            (int) $serviceId
                    );

                if (
                    $serviceIds
                        ->diff($assignedServices)
                        ->isNotEmpty()
                ) {
                    throw ValidationException::withMessages([
                        'staff_id' =>
                            'The selected beautician cannot perform all services in this appointment',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Reject Staff Day-Off Dates
                |--------------------------------------------------------------------------
                */

                $onDayOff = StaffDayOff::where(
                    'staff_id',
                    $lockedStaff->id
                )
                    ->whereDate(
                        'start_date',
                        '<=',
                        $validated['appointment_date']
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $validated['appointment_date']
                    )
                    ->exists();

                if ($onDayOff) {
                    throw ValidationException::withMessages([
                        'staff_id' =>
                            'The selected beautician is on day off for this date',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Prevent Overlapping Appointments
                |--------------------------------------------------------------------------
                | Existing start < requested end
                | Existing end > requested start
                |
                | Exclude the booking being rescheduled
                |--------------------------------------------------------------------------
                */

                $hasConflict = Appointment::where(
                    'staff_id',
                    $lockedStaff->id
                )
                    ->where(
                        'appointment_date',
                        $validated['appointment_date']
                    )
                    ->whereKeyNot(
                        $appointment->id
                    )
                    ->whereIn(
                        'status',
                        Appointment::ACTIVE_STATUSES
                    )
                    ->where(
                        'start_time',
                        '<',
                        $newEnd->format('H:i:s')
                    )
                    ->where(
                        function ($query) use ($newStart) {
                            $query
                                ->whereNull('end_time')
                                ->orWhere(
                                    'end_time',
                                    '>',
                                    $newStart->format('H:i:s')
                                );
                        }
                    )
                    ->exists();

                if ($hasConflict) {
                    throw ValidationException::withMessages([
                        'start_time' =>
                            'The selected appointment time is already occupied',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Preserve Original Booking Information
                |--------------------------------------------------------------------------
                | Never overwrite original information
                |--------------------------------------------------------------------------
                */

                $originalDate =
                    $appointment->original_appointment_date
                    ?? $appointment->appointment_date;

                $originalTime =
                    $appointment->original_start_time
                    ?? $appointment->start_time;

                $originalStaff =
                    $appointment->original_staff_id
                    ?? $appointment->staff_id;

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Update Existing Appointment
                |--------------------------------------------------------------------------
                | Appointment ID, services and total price stay unchanged
                |--------------------------------------------------------------------------
                */

                $now = Carbon::now(
                    self::BUSINESS_TIMEZONE
                );

                $appointment->update([
                    'staff_id' => $lockedStaff->id,

                    'appointment_date' =>
                        $validated['appointment_date'],

                    'start_time' =>
                        $newStart->format('H:i:s'),

                    'end_time' =>
                        $newEnd->format('H:i:s'),

                    'status' => 'pending',

                    'original_appointment_date' =>
                        $originalDate,

                    'original_start_time' =>
                        $originalTime,

                    'original_staff_id' =>
                        $originalStaff,

                    'rescheduled_at' => $now,

                    'rescheduled_by' =>
                        auth()->id(),

                    'status_updated_by' =>
                        auth()->id(),

                    'status_updated_at' => $now,
                ]);

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Audit Successful Rescheduling
                |--------------------------------------------------------------------------
                */

                $bookingCode = 'BK-' . str_pad(
                    (string) $appointment->id,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

                AuditLog::create([
                    'user_id' => auth()->id(),

                    'user_type' => 'customer',

                    'action' =>
                        'customer_emergency_rescheduled',

                    'module' => 'Customer Booking',

                    'description' =>
                        'Customer rescheduled booking ' .
                        $bookingCode .
                        ' to ' .
                        $validated['appointment_date'] .
                        ' at ' .
                        $validated['start_time'] .
                        ' MYT with staff ID ' .
                        $lockedStaff->id,

                    'ip_address' => request()->ip(),

                    'user_agent' =>
                        request()->userAgent(),
                ]);

                return [
                    'appointment_id' =>
                        $appointment->id,

                    'booking_code' =>
                        $bookingCode,

                    'staff_id' =>
                        $lockedStaff->id,

                    'date' =>
                        $validated['appointment_date'],

                    'time' =>
                        $validated['start_time'],
                ];
            },
            3
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notifications After Successful Commit
        |--------------------------------------------------------------------------
        */

        $this->sendReschedulingNotifications(
            $outcome['appointment_id'],
            $outcome['booking_code'],
            $outcome['date'],
            $outcome['time']
        );

        return redirect()
            ->route(
                'customer.booking.details',
                $outcome['appointment_id']
            )
            ->with(
                'success',
                'Your appointment has been rescheduled successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Identity Helper
    |--------------------------------------------------------------------------
    | Only retrieve a customer matching the authenticated account
    |--------------------------------------------------------------------------
    */

    private function getAuthenticatedCustomer(
        Request $request
    ): Customer {
        $user = $request->user();

        abort_unless($user, 401);

        abort_if(
            in_array(
                strtolower(
                    (string) (
                        $user->status ?? 'active'
                    )
                ),
                ['blocked', 'inactive'],
                true
            ),
            403
        );

        $customer = Customer::where(
            function ($query) use ($user) {
                $query
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->orWhere(
                        'email',
                        $user->email
                    );
            }
        )->firstOrFail();

        abort_if(
            in_array(
                strtolower(
                    (string) (
                        $customer->status ?? 'active'
                    )
                ),
                ['blocked', 'inactive'],
                true
            ),
            403
        );

        return $customer;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Send Rescheduling Notifications
    |--------------------------------------------------------------------------
    | Changes stay saved even when external email delivery fails
    |--------------------------------------------------------------------------
    */

    private function sendReschedulingNotifications(
        int $appointmentId,
        string $bookingCode,
        string $date,
        string $time
    ): void {
        $appointment = Appointment::with([
            'customer',
            'staff',
        ])->find($appointmentId);

        if (!$appointment) {
            return;
        }

        $notificationService = app(
            NotificationService::class
        );

        $dateTime = $date . ' at ' . $time . ' MYT';

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Customer
        |--------------------------------------------------------------------------
        */

        if ($appointment->customer) {
            try {
                $notificationService->notifyCustomer(
                    $appointment->customer,

                    'Appointment Rescheduled Successfully',

                    'Your booking ' .
                    $bookingCode .
                    ' has been rescheduled to ' .
                    $dateTime .
                    ' — please review your updated appointment details',

                    route(
                        'customer.booking.details',
                        $appointmentId,
                        false
                    ),

                    'appointment_rescheduled'
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Assigned Beautician
        |--------------------------------------------------------------------------
        */

        if ($appointment->staff) {
            try {
                $notificationService->notifyStaff(
                    $appointment->staff,

                    'Rescheduled Appointment Assigned',

                    'Booking ' .
                    $bookingCode .
                    ' has been scheduled for ' .
                    $dateTime,

                    route(
                        'staff.appointments',
                        [],
                        false
                    ),

                    'appointment_rescheduled'
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Administrators
        |--------------------------------------------------------------------------
        */

        try {
            $notificationService->notifyAdmins(
                'Customer Appointment Rescheduled',

                'Customer rescheduled booking ' .
                $bookingCode .
                ' to ' .
                $dateTime,

                route(
                    'admin.bookings',
                    [],
                    false
                ),

                'appointment_rescheduled'
            );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
