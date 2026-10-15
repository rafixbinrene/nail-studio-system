<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffDayOff;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Booking Controller
|--------------------------------------------------------------------------
| Purpose:
| - Display the customer booking page
| - Enforce a seven-day booking window using Malaysian time
| - Enforce customer booking limits
| - Validate regular and follow-up services
| - Validate staff skills and day-offs
| - Prevent overlapping bookings
| - Provide database-backed appointment availability
| - Preserve audit logging and user notifications
|--------------------------------------------------------------------------
*/

class BookingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Business Timezone
    |--------------------------------------------------------------------------
    | All customer appointment dates and times use Malaysian time
    | The hosting server may be located in another timezone
    |--------------------------------------------------------------------------
    */

    private const BUSINESS_TIMEZONE = 'Asia/Kuala_Lumpur';

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Available Booking Start Times
    |--------------------------------------------------------------------------
    | These start times match the existing booking page
    | Changes to studio operating hours should be applied consistently
    | to the frontend and backend
    |--------------------------------------------------------------------------
    */

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
    | NS BEAUTY COMMENT: Customer Booking Page
    |--------------------------------------------------------------------------
    | Preserve existing view data and customer matching
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $user = $request->user();

        $customer = Customer::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('email', $user->email);
        })->first();

        $customerCompletedBookings = 0;
        $customerActiveBookings = 0;
        $eligibleFollowUps = collect();

        if ($customer) {
            $customerCompletedBookings = Appointment::where(
                'customer_id',
                $customer->id
            )
                ->whereIn('status', ['finished', 'Finished'])
                ->count();

            $customerActiveBookings = Appointment::where(
                'customer_id',
                $customer->id
            )
                ->whereIn('status', Appointment::ACTIVE_STATUSES)
                ->count();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Eligible Follow-up Appointments
            |--------------------------------------------------------------------------
            */

            $eligibleFollowUps = Appointment::with([
                    'staff',
                    'services',
                ])
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['finished', 'Finished'])
                ->orderByDesc('appointment_date')
                ->orderByDesc('start_time')
                ->limit(10)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Live Available Services
        |--------------------------------------------------------------------------
        */

        $services = Service::whereIn(
                'status',
                ['Available', 'available']
            )
            ->orderBy('service_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Active Beauticians
        |--------------------------------------------------------------------------
        */

        $staffMembers = Staff::with([
                'services',
                'activeDayOffs',
            ])
            ->whereIn('status', ['Active', 'active'])
            ->orderBy('full_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Day-offs
        |--------------------------------------------------------------------------
        | Query relative to today's date in Malaysia
        |--------------------------------------------------------------------------
        */

        $staffDayOffs = StaffDayOff::with('staff')
            ->whereDate(
                'end_date',
                '>=',
                Carbon::today(self::BUSINESS_TIMEZONE)->toDateString()
            )
            ->orderBy('start_date')
            ->get();

        return view('customer.booking', [
            'customer' => $customer,
            'services' => $services,
            'staffMembers' => $staffMembers,
            'staffDayOffs' => $staffDayOffs,
            'customerCompletedBookings' => $customerCompletedBookings,
            'customerActiveBookings' => $customerActiveBookings,
            'eligibleFollowUps' => $eligibleFollowUps,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Database-backed Slot Availability
    |--------------------------------------------------------------------------
    | Purpose:
    | - Provides actual availability for the selected beautician
    | - Considers total duration of all selected services
    | - Disables past or already-started times in Malaysia
    | - Disables dates when the staff member is on day off
    | - Detects overlapping active appointments
    |
    | IMPORTANT:
    | The route and frontend request will be connected in the next files
    | The store method also independently validates booking availability
    |--------------------------------------------------------------------------
    */

    public function availability(Request $request): JsonResponse
    {
        $today = Carbon::today(self::BUSINESS_TIMEZONE);
        $maxDate = $today->copy()->addDays(6);

        $validated = $request->validate([
            'appointment_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . $today->toDateString(),
                'before_or_equal:' . $maxDate->toDateString(),
            ],
            'staff_id' => [
                'required',
                'integer',
                'exists:staff,id',
            ],
            'booking_type' => [
                'required',
                Rule::in(['regular', 'followup']),
            ],
            'selected_service_ids' => [
                'required',
                'string',
            ],
            'follow_up_appointment_id' => [
                'nullable',
                'integer',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Account Eligibility
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (in_array(
            strtolower($user->status ?? 'active'),
            ['blocked', 'inactive'],
            true
        )) {
            return response()->json([
                'message' => 'Your account cannot create appointments',
            ], 403);
        }

        $customer = Customer::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('email', $user->email);
        })->first();

        if (
            $customer &&
            in_array(
                strtolower($customer->status ?? 'active'),
                ['blocked', 'inactive'],
                true
            )
        ) {
            return response()->json([
                'message' => 'Your customer account cannot create appointments',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Selected Services
        |--------------------------------------------------------------------------
        */

        $serviceIds = $this->parseServiceIds(
            $validated['selected_service_ids']
        );

        if ($serviceIds->isEmpty() || $serviceIds->count() > 3) {
            return response()->json([
                'message' => 'Select between 1 and 3 services',
            ], 422);
        }

        if ($validated['booking_type'] === 'followup') {
            if (
                !$customer ||
                empty($validated['follow_up_appointment_id'])
            ) {
                return response()->json([
                    'message' => 'Select an eligible finished booking',
                ], 422);
            }

            $originalBooking = Appointment::with('services')
                ->where(
                    'id',
                    $validated['follow_up_appointment_id']
                )
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['finished', 'Finished'])
                ->first();

            if (!$originalBooking) {
                return response()->json([
                    'message' => 'The selected follow-up history is invalid',
                ], 422);
            }

            $originalServiceIds = $originalBooking->services
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            if ($serviceIds->diff($originalServiceIds)->isNotEmpty()) {
                return response()->json([
                    'message' => 'Select services from the linked finished booking',
                ], 422);
            }

            $selectedServices = $originalBooking->services
                ->filter(function ($service) use ($serviceIds) {
                    return $serviceIds->contains((int) $service->id);
                });

            $totalDuration = $selectedServices->sum(
                fn ($service) => $this->getOriginalServiceDuration($service)
            );
        } else {
            $selectedServices = Service::whereIn('id', $serviceIds)
                ->whereIn('status', ['Available', 'available'])
                ->get();

            if ($selectedServices->count() !== $serviceIds->count()) {
                return response()->json([
                    'message' => 'One or more selected services are unavailable',
                ], 422);
            }

            $totalDuration = $selectedServices->sum(
                fn ($service) => (int) $service->duration
            );
        }

        if ($totalDuration <= 0) {
            return response()->json([
                'message' => 'The selected service duration is invalid',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Beautician Validation
        |--------------------------------------------------------------------------
        */

        $staff = Staff::with('services')
            ->where('id', $validated['staff_id'])
            ->whereIn('status', ['Active', 'active'])
            ->first();

        if (!$staff) {
            return response()->json([
                'message' => 'The selected beautician is unavailable',
            ], 422);
        }

        if (!$this->staffCanPerformServices($staff, $serviceIds)) {
            return response()->json([
                'message' => 'The selected beautician is not assigned to all selected services',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Calculate Each Slot from Actual Records
        |--------------------------------------------------------------------------
        */

        $slots = $this->buildAvailabilitySlots(
            (int) $staff->id,
            $validated['appointment_date'],
            (int) $totalDuration
        );

        return response()->json([
            'timezone' => self::BUSINESS_TIMEZONE,
            'date' => $validated['appointment_date'],
            'staff_id' => (int) $staff->id,
            'duration' => (int) $totalDuration,
            'slots' => $slots,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Store Customer Booking
    |--------------------------------------------------------------------------
    | Preserves the original booking creation process
    | Adds Malaysian-time and slot availability enforcement
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $today = Carbon::today(self::BUSINESS_TIMEZONE);
        $maxDate = $today->copy()->addDays(6);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validate Input and Booking Window
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'booking_type' => [
                'required',
                Rule::in(['regular', 'followup']),
            ],
            'selected_service_ids' => ['nullable', 'string'],
            'selected_services' => ['nullable', 'string'],
            'follow_up_appointment_id' => [
                'nullable',
                'integer',
                'exists:appointments,id',
            ],
            'staff_id' => [
                'required',
                'integer',
                'exists:staff,id',
            ],
            'appointment_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . $today->toDateString(),
                'before_or_equal:' . $maxDate->toDateString(),
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::in(self::BOOKING_TIMES),
            ],
            'customer_name' => [
                'required',
                'string',
                'max:255',
            ],
            'customer_phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'customer_email' => [
                'required',
                'email',
                'max:255',
            ],
            'follow_up_reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Email Ownership Validation
        |--------------------------------------------------------------------------
        */

        if (
            strtolower($validated['customer_email']) !==
            strtolower($user->email)
        ) {
            return back()
                ->withErrors([
                    'customer_email' => 'The booking email must match your account email',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Profile
        |--------------------------------------------------------------------------
        */

        $customer = Customer::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('email', $user->email);
        })->first();

        $isNewCustomer = !$customer;

        if (!$customer) {
            $customer = new Customer();
        }

        $customerStatus = strtolower(
            $customer->status ?? 'active'
        );

        $userStatus = strtolower(
            $user->status ?? 'active'
        );

        if (in_array($userStatus, ['blocked', 'inactive'], true)) {
            return back()
                ->withErrors([
                    'booking' => 'Your account cannot create bookings',
                ])
                ->withInput();
        }

        if (
            !$isNewCustomer &&
            in_array($customerStatus, ['blocked', 'inactive'], true)
        ) {
            return back()
                ->withErrors([
                    'booking' => 'Your customer account cannot create bookings',
                ])
                ->withInput();
        }

        $customer->fill([
            'user_id' => $user->id,
            'full_name' => $validated['customer_name'],
            'email' => $user->email,
            'phone_number' => $validated['customer_phone'],
        ]);

        if ($isNewCustomer || empty($customer->status)) {
            $customer->status = 'active';
        }

        $customer->save();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Limit
        |--------------------------------------------------------------------------
        | New customer: 1 active booking
        | Customer with finished booking: 2 active bookings
        |--------------------------------------------------------------------------
        */

        $completedBookings = Appointment::where(
            'customer_id',
            $customer->id
        )
            ->whereIn('status', ['finished', 'Finished'])
            ->count();

        $activeBookingLimit = $completedBookings >= 1 ? 2 : 1;

        $activeBookings = Appointment::where(
            'customer_id',
            $customer->id
        )
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->count();

        if ($activeBookings >= $activeBookingLimit) {
            return back()
                ->withErrors([
                    'booking' => 'You have reached your active booking limit',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Maximum Three Services
        |--------------------------------------------------------------------------
        */

        $selectedServiceIds = $this->parseServiceIds(
            $validated['selected_service_ids'] ?? ''
        );

        if ($selectedServiceIds->isEmpty()) {
            return back()
                ->withErrors([
                    'selected_services' => 'Please select at least one service',
                ])
                ->withInput();
        }

        if ($selectedServiceIds->count() > 3) {
            return back()
                ->withErrors([
                    'selected_services' => 'Maximum of 3 services per booking',
                ])
                ->withInput();
        }

        $followUpAppointment = null;

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Follow-up Booking Validation
        |--------------------------------------------------------------------------
        */

        if ($validated['booking_type'] === 'followup') {
            if (empty($validated['follow_up_appointment_id'])) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' => 'Select a finished booking for the follow-up',
                    ])
                    ->withInput();
            }

            $followUpAppointment = Appointment::with([
                    'services',
                    'staff',
                ])
                ->where(
                    'id',
                    $validated['follow_up_appointment_id']
                )
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['finished', 'Finished'])
                ->first();

            if (!$followUpAppointment) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' => 'The selected finished booking is invalid',
                    ])
                    ->withInput();
            }

            if ($followUpAppointment->services->isEmpty()) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' => 'The selected booking has no recorded services',
                    ])
                    ->withInput();
            }

            $originalServiceIds = $followUpAppointment->services
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            if (
                $selectedServiceIds
                    ->diff($originalServiceIds)
                    ->isNotEmpty()
            ) {
                return back()
                    ->withErrors([
                        'selected_services' => 'Select services from the linked finished booking only',
                    ])
                    ->withInput();
            }

            $services = $followUpAppointment->services
                ->filter(function ($service) use ($selectedServiceIds) {
                    return $selectedServiceIds->contains(
                        (int) $service->id
                    );
                })
                ->values();

            $totalPrice = 0;

            $totalDuration = $services->sum(
                fn ($service) => $this->getOriginalServiceDuration($service)
            );
        } else {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Regular Booking Services
            |--------------------------------------------------------------------------
            */

            $services = Service::whereIn(
                    'id',
                    $selectedServiceIds
                )
                ->whereIn(
                    'status',
                    ['Available', 'available']
                )
                ->get();

            if ($services->count() !== $selectedServiceIds->count()) {
                return back()
                    ->withErrors([
                        'selected_services' => 'One or more selected services are unavailable',
                    ])
                    ->withInput();
            }

            $totalPrice = $services->sum('price');

            $totalDuration = $services->sum(
                fn ($service) => (int) $service->duration
            );
        }

        if ($services->isEmpty() || $totalDuration <= 0) {
            return back()
                ->withErrors([
                    'selected_services' => 'The selected services or duration are invalid',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Status and Skill Validation
        |--------------------------------------------------------------------------
        */

        $staff = Staff::with('services')
            ->where('id', $validated['staff_id'])
            ->whereIn('status', ['Active', 'active'])
            ->first();

        if (!$staff) {
            return back()
                ->withErrors([
                    'staff_id' => 'The selected beautician is unavailable',
                ])
                ->withInput();
        }

        if (!$this->staffCanPerformServices(
            $staff,
            $selectedServiceIds
        )) {
            return back()
                ->withErrors([
                    'staff_id' => 'The selected beautician is not assigned to all selected services',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Malaysian Appointment Time
        |--------------------------------------------------------------------------
        | Explicit timezone prevents dependence on server settings
        |--------------------------------------------------------------------------
        */

        $startDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['appointment_date'] . ' ' . $validated['start_time'],
            self::BUSINESS_TIMEZONE
        )->second(0);

        $endDateTime = $startDateTime->copy()
            ->addMinutes($totalDuration);

        if (
            $startDateTime->lessThanOrEqualTo(
                Carbon::now(self::BUSINESS_TIMEZONE)
            )
        ) {
            return back()
                ->withErrors([
                    'start_time' => 'The selected appointment time has already started or passed',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Staff Day-off Validation
        |--------------------------------------------------------------------------
        */

        if ($this->staffHasDayOff(
            $staff->id,
            $validated['appointment_date']
        )) {
            return back()
                ->withErrors([
                    'staff_id' => 'The selected beautician is on day off',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Transaction and Double Booking Protection
        |--------------------------------------------------------------------------
        | Lock the staff record to serialize bookings for the same staff
        | Lock the customer record and recheck the active booking limit
        | Recheck the selected time before saving
        |--------------------------------------------------------------------------
        */

        try {
            $appointment = DB::transaction(function () use (
                $validated,
                $request,
                $user,
                $customer,
                $services,
                $selectedServiceIds,
                $totalPrice,
                $totalDuration,
                $startDateTime,
                $endDateTime,
                $followUpAppointment
            ) {
                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Lock Selected Staff
                |--------------------------------------------------------------------------
                */

                $lockedStaff = Staff::where(
                        'id',
                        $validated['staff_id']
                    )
                    ->lockForUpdate()
                    ->first();

                if (
                    !$lockedStaff ||
                    !in_array(
                        strtolower($lockedStaff->status ?? ''),
                        ['active'],
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'The selected beautician is no longer available'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Lock Customer and Recheck Limit
                |--------------------------------------------------------------------------
                */

                Customer::whereKey($customer->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $finishedCount = Appointment::where(
                        'customer_id',
                        $customer->id
                    )
                    ->whereIn('status', ['finished', 'Finished'])
                    ->count();

                $bookingLimit = $finishedCount >= 1 ? 2 : 1;

                $activeCount = Appointment::where(
                        'customer_id',
                        $customer->id
                    )
                    ->whereIn('status', Appointment::ACTIVE_STATUSES)
                    ->count();

                if ($activeCount >= $bookingLimit) {
                    throw new \RuntimeException(
                        'You have reached your active booking limit'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Final Malaysia Time Check
                |--------------------------------------------------------------------------
                */

                $transactionToday = Carbon::today(
                    self::BUSINESS_TIMEZONE
                );

                $transactionMaxDate = $transactionToday
                    ->copy()
                    ->addDays(6);

                if (
                    $startDateTime->lessThanOrEqualTo(
                        Carbon::now(self::BUSINESS_TIMEZONE)
                    ) ||
                    $startDateTime->toDateString() <
                        $transactionToday->toDateString() ||
                    $startDateTime->toDateString() >
                        $transactionMaxDate->toDateString()
                ) {
                    throw new \RuntimeException(
                        'The selected appointment date or time is no longer available'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Final Staff Day-off Check
                |--------------------------------------------------------------------------
                */

                $staffOnDayOff = StaffDayOff::where(
                        'staff_id',
                        $validated['staff_id']
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

                if ($staffOnDayOff) {
                    throw new \RuntimeException(
                        'The selected beautician is on day off for this date'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Final Staff Skill Check
                |--------------------------------------------------------------------------
                */

                if (Schema::hasTable('staff_services')) {
                    $assignedServiceIds = DB::table('staff_services')
                        ->where(
                            'staff_id',
                            $validated['staff_id']
                        )
                        ->pluck('service_id')
                        ->map(fn ($id) => (int) $id);

                    if (
                        $selectedServiceIds
                            ->diff($assignedServiceIds)
                            ->isNotEmpty()
                    ) {
                        throw new \RuntimeException(
                            'The beautician is no longer assigned to the selected services'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Actual Appointment Overlap Check
                |--------------------------------------------------------------------------
                | Existing start < requested end
                | Existing end > requested start
                |--------------------------------------------------------------------------
                */

                $hasConflict = Appointment::where(
                        'staff_id',
                        $validated['staff_id']
                    )
                    ->whereDate(
                        'appointment_date',
                        $validated['appointment_date']
                    )
                    ->whereIn(
                        'status',
                        Appointment::ACTIVE_STATUSES
                    )
                    ->where(function ($query) use (
                        $startDateTime,
                        $endDateTime
                    ) {
                        $query->where(
                                'start_time',
                                '<',
                                $endDateTime->format('H:i:s')
                            )
                            ->where(
                                'end_time',
                                '>',
                                $startDateTime->format('H:i:s')
                            );
                    })
                    ->exists();

                if ($hasConflict) {
                    throw new \RuntimeException(
                        'The selected time is fully booked for this beautician'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Save Appointment
                |--------------------------------------------------------------------------
                */

                $appointment = Appointment::create([
                    'customer_id' => $customer->id,
                    'staff_id' => $validated['staff_id'],
                    'appointment_date' => $validated['appointment_date'],
                    'start_time' => $startDateTime->format('H:i:s'),
                    'end_time' => $endDateTime->format('H:i:s'),
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                    'booking_type' => $validated['booking_type'],
                    'follow_up_appointment_id' => $followUpAppointment?->id,
                    'follow_up_from' => $followUpAppointment
                        ? 'BK-' . str_pad(
                            $followUpAppointment->id,
                            4,
                            '0',
                            STR_PAD_LEFT
                        )
                        : null,
                    'follow_up_reason' =>
                        $validated['follow_up_reason'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Attach Actual Services
                |--------------------------------------------------------------------------
                */

                foreach ($services as $service) {
                    if ($validated['booking_type'] === 'followup') {
                        $appointment->services()->attach(
                            $service->id,
                            [
                                'price' => 0,
                                'duration' =>
                                    $this->getOriginalServiceDuration($service),
                            ]
                        );
                    } else {
                        $appointment->services()->attach(
                            $service->id,
                            [
                                'price' => $service->price,
                                'duration' => $service->duration,
                            ]
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Preserve Audit Logging
                |--------------------------------------------------------------------------
                */

                AuditLog::create([
                    'user_id' => $user->id,
                    'user_type' => 'customer',
                    'action' =>
                        $validated['booking_type'] === 'followup'
                            ? 'create_followup_booking'
                            : 'create_booking',
                    'module' => 'Customer Booking',
                    'description' =>
                        $validated['booking_type'] === 'followup'
                            ? 'Customer created follow-up booking #BK-' .
                                str_pad(
                                    $appointment->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                ) .
                                ' from original booking #BK-' .
                                str_pad(
                                    $followUpAppointment->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                )
                            : 'Customer created booking #BK-' .
                                str_pad(
                                    $appointment->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                ),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $appointment;
            });
        } catch (\RuntimeException $exception) {
            return back()
                ->withErrors([
                    'booking' => $exception->getMessage(),
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Existing Notification Integration
        |--------------------------------------------------------------------------
        | Retains customer, admin, and staff notifications
        |--------------------------------------------------------------------------
        */

        $appointment->load([
            'customer',
            'staff',
            'services',
        ]);

        $notificationService = app(NotificationService::class);

        $bookingCode = 'BK-' . str_pad(
            $appointment->id,
            4,
            '0',
            STR_PAD_LEFT
        );

        $mainService = $appointment->services
            ->first()?->service_name ?? 'Beauty Service';

        $bookingTypeLabel =
            $validated['booking_type'] === 'followup'
                ? 'backjob / follow-up booking'
                : 'booking';

        $notificationService->notifyCustomer(
            $appointment->customer,
            'Booking Created Successfully',
            'Your ' . $bookingTypeLabel . ' #' . $bookingCode .
                ' for ' . $mainService . ' has been created successfully',
            route(
                'customer.booking.details',
                $appointment->id,
                false
            ),
            'booking_created'
        );

        $notificationService->notifyAdmins(
            'New Customer Booking',
            $customer->full_name . ' created ' .
                $bookingTypeLabel . ' #' . $bookingCode .
                ' for ' . $mainService,
            route('admin.bookings', [], false),
            'new_booking'
        );

        $notificationService->notifyStaff(
            $appointment->staff,
            $validated['booking_type'] === 'followup'
                ? 'New Backjob / Follow-up Assigned'
                : 'New Appointment Assigned',
            'Appointment #' . $bookingCode .
                ' for ' . $mainService .
                ' has been assigned to you',
            route('staff.appointments', [], false),
            $validated['booking_type'] === 'followup'
                ? 'followup_assigned'
                : 'appointment_assigned'
        );

        return redirect()
            ->route('customer.booking')
            ->with('booking_success', true)
            ->with('booking_id', $bookingCode)
            ->with(
                'booking_message',
                $validated['booking_type'] === 'followup'
                    ? 'Your backjob / follow-up booking has been recorded and linked to your previous appointment'
                    : 'Your booking has been recorded successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Build Real Availability Slots
    |--------------------------------------------------------------------------
    | The same calculation is used by the availability endpoint
    | to check staff day-offs, elapsed times, and overlaps
    |--------------------------------------------------------------------------
    */

    private function buildAvailabilitySlots(
        int $staffId,
        string $date,
        int $duration
    ): array {
        $now = Carbon::now(self::BUSINESS_TIMEZONE);

        $onDayOff = $this->staffHasDayOff(
            $staffId,
            $date
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Load Actual Active Appointments
        |--------------------------------------------------------------------------
        */

        $existingAppointments = Appointment::where(
                'staff_id',
                $staffId
            )
            ->whereDate('appointment_date', $date)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->get([
                'id',
                'start_time',
                'end_time',
            ]);

        $slots = [];

        foreach (self::BOOKING_TIMES as $time) {
            $start = Carbon::createFromFormat(
                'Y-m-d H:i',
                $date . ' ' . $time,
                self::BUSINESS_TIMEZONE
            )->second(0);

            $end = $start->copy()
                ->addMinutes($duration);

            $status = 'available';

            if ($start->lessThanOrEqualTo($now)) {
                $status = 'past';
            } elseif ($onDayOff) {
                $status = 'day_off';
            } else {
                foreach ($existingAppointments as $appointment) {
                    $existingStart = Carbon::parse(
                        $date . ' ' . $appointment->start_time,
                        self::BUSINESS_TIMEZONE
                    );

                    $existingEnd = Carbon::parse(
                        $date . ' ' . $appointment->end_time,
                        self::BUSINESS_TIMEZONE
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Duration-based Overlap
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $existingStart->lessThan($end) &&
                        $existingEnd->greaterThan($start)
                    ) {
                        $status = 'booked';
                        break;
                    }
                }
            }

            $slots[] = [
                'time' => $time,
                'available' => $status === 'available',
                'status' => $status,
            ];
        }

        return $slots;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Staff Skill Validation Helper
    |--------------------------------------------------------------------------
    | Preserve compatibility with the existing staff_services table
    |--------------------------------------------------------------------------
    */

    private function staffCanPerformServices(
        Staff $staff,
        $selectedServiceIds
    ): bool {
        if (!Schema::hasTable('staff_services')) {
            return true;
        }

        $assignedServiceIds = $staff->services
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        return $selectedServiceIds
            ->diff($assignedServiceIds)
            ->isEmpty();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Staff Day-off Helper
    |--------------------------------------------------------------------------
    */

    private function staffHasDayOff(
        int $staffId,
        string $date
    ): bool {
        return StaffDayOff::where('staff_id', $staffId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Selected Service ID Parser
    |--------------------------------------------------------------------------
    */

    private function parseServiceIds(?string $value)
    {
        return collect(explode(',', $value ?? ''))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Follow-up Original Duration
    |--------------------------------------------------------------------------
    | Uses recorded appointment service duration when available
    |--------------------------------------------------------------------------
    */

    private function getOriginalServiceDuration($service): int
    {
        $pivotDuration = (int) (
            $service->pivot->duration ?? 0
        );

        if ($pivotDuration > 0) {
            return $pivotDuration;
        }

        return (int) ($service->duration ?? 0);
    }
}
