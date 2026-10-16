<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffDayOff;
use App\Models\StudioClosure;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CUSTOMER BOOKING CONTROLLER
|--------------------------------------------------------------------------
| - Regular and follow-up appointments
| - Seven-day advance booking window, including today
| - Malaysia business timezone
| - Sunday closing day
| - Studio-wide emergency closures
| - Staff qualifications and scheduled day-offs
| - Appointment overlap prevention
| - Customer active booking limits
| - Notifications and audit logging
|--------------------------------------------------------------------------
*/

class BookingController extends Controller
{
    private const BUSINESS_TIMEZONE = 'Asia/Kuala_Lumpur';

    private const BOOKING_WINDOW_DAYS = 6;

    private const CLOSED_WEEKDAY = Carbon::SUNDAY;

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
    | NS BEAUTY COMMENT: DISPLAY CUSTOMER BOOKING PAGE
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $user = $request->user();

        $customer = $this->findCustomer($user);

        $customerCompletedBookings = 0;
        $customerActiveBookings = 0;
        $eligibleFollowUps = collect();

        if ($customer) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Completed Booking History
            |--------------------------------------------------------------------------
            */

            $customerCompletedBookings = Appointment::where(
                'customer_id',
                $customer->id
            )
                ->whereIn('status', ['finished', 'Finished'])
                ->count();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Booking Limit Occupancy
            |--------------------------------------------------------------------------
            */

            $customerActiveBookings = Appointment::where(
                'customer_id',
                $customer->id
            )
                ->whereIn(
                    'status',
                    $this->customerOccupiedStatuses()
                )
                ->count();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Eligible Follow-Up Appointments
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
        | NS BEAUTY COMMENT: Available Services
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
        | NS BEAUTY COMMENT: Active Staff and Skills
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
        | NS BEAUTY COMMENT: Staff Day-Off Records
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today(
            self::BUSINESS_TIMEZONE
        );

        $staffDayOffs = StaffDayOff::with('staff')
            ->whereDate(
                'end_date',
                '>=',
                $today->toDateString()
            )
            ->orderBy('start_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Active Studio Closure Records
        |--------------------------------------------------------------------------
        | Expose closures within the seven-day booking window
        | for optional use by the customer booking interface
        |--------------------------------------------------------------------------
        */

        $studioClosures = StudioClosure::query()
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

        return view('customer.booking', [
            'customer' => $customer,
            'services' => $services,
            'staffMembers' => $staffMembers,
            'staffDayOffs' => $staffDayOffs,
            'studioClosures' => $studioClosures,
            'customerCompletedBookings' =>
                $customerCompletedBookings,
            'customerActiveBookings' =>
                $customerActiveBookings,
            'eligibleFollowUps' => $eligibleFollowUps,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: REAL-TIME APPOINTMENT AVAILABILITY
    |--------------------------------------------------------------------------
    | Returns actual appointment slots from database records
    | Closed dates return unavailable slots
    |--------------------------------------------------------------------------
    */

    public function availability(Request $request): JsonResponse
    {
        $today = Carbon::today(
            self::BUSINESS_TIMEZONE
        );

        $maxDate = $today->copy()
            ->addDays(self::BOOKING_WINDOW_DAYS);

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
        | NS BEAUTY COMMENT: Account Status
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (
            $this->isBlockedOrInactive(
                $user->status ?? 'active'
            )
        ) {
            return response()->json([
                'message' =>
                    'Your account cannot create appointments',
            ], 403);
        }

        $customer = $this->findCustomer($user);

        if (
            $customer &&
            $this->isBlockedOrInactive(
                $customer->status ?? 'active'
            )
        ) {
            return response()->json([
                'message' =>
                    'Your customer account cannot create appointments',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Selected Service IDs
        |--------------------------------------------------------------------------
        */

        $serviceIds = $this->parseServiceIds(
            $validated['selected_service_ids']
        );

        if (
            $serviceIds->isEmpty() ||
            $serviceIds->count() > 3
        ) {
            return response()->json([
                'message' => 'Select between 1 and 3 services',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Follow-Up Service Validation
        |--------------------------------------------------------------------------
        */

        if ($validated['booking_type'] === 'followup') {
            if (
                !$customer ||
                empty($validated['follow_up_appointment_id'])
            ) {
                return response()->json([
                    'message' =>
                        'Select an eligible finished booking',
                ], 422);
            }

            $originalBooking = Appointment::with('services')
                ->whereKey(
                    $validated['follow_up_appointment_id']
                )
                ->where('customer_id', $customer->id)
                ->whereIn(
                    'status',
                    ['finished', 'Finished']
                )
                ->first();

            if (!$originalBooking) {
                return response()->json([
                    'message' =>
                        'The selected follow-up history is invalid',
                ], 422);
            }

            $originalServiceIds = $originalBooking
                ->services
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            if (
                $serviceIds
                    ->diff($originalServiceIds)
                    ->isNotEmpty()
            ) {
                return response()->json([
                    'message' =>
                        'Select services from the linked finished booking',
                ], 422);
            }

            $selectedServices = $originalBooking
                ->services
                ->filter(
                    fn ($service) =>
                        $serviceIds->contains(
                            (int) $service->id
                        )
                );

            $totalDuration = $selectedServices->sum(
                fn ($service) =>
                    $this->getOriginalServiceDuration(
                        $service
                    )
            );
        } else {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Regular Booking Services
            |--------------------------------------------------------------------------
            */

            $selectedServices = Service::whereIn(
                'id',
                $serviceIds
            )
                ->whereIn(
                    'status',
                    ['Available', 'available']
                )
                ->get();

            if (
                $selectedServices->count() !==
                $serviceIds->count()
            ) {
                return response()->json([
                    'message' =>
                        'One or more selected services are unavailable',
                ], 422);
            }

            $totalDuration = $selectedServices->sum(
                fn ($service) =>
                    (int) $service->duration
            );
        }

        if ($totalDuration <= 0) {
            return response()->json([
                'message' =>
                    'The selected service duration is invalid',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Beautician Qualifications
        |--------------------------------------------------------------------------
        */

        $staff = Staff::with('services')
            ->whereKey(
                $validated['staff_id']
            )
            ->whereIn(
                'status',
                ['Active', 'active']
            )
            ->first();

        if (!$staff) {
            return response()->json([
                'message' =>
                    'The selected beautician is unavailable',
            ], 422);
        }

        if (
            !$this->staffCanPerformServices(
                $staff,
                $serviceIds
            )
        ) {
            return response()->json([
                'message' =>
                    'The selected beautician is not assigned to all selected services',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Studio Closure Information
        |--------------------------------------------------------------------------
        */

        $date = $validated['appointment_date'];

        $activeClosure = StudioClosure::activeClosureOn(
            $date
        );

        $studioClosed = $this->isStudioClosedOnDate(
            $date
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Return Availability JSON
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'timezone' => self::BUSINESS_TIMEZONE,

            'date' => $date,

            'staff_id' => (int) $staff->id,

            'duration' => (int) $totalDuration,

            'studio_closed' => $studioClosed,

            'closure_reason' =>
                $activeClosure?->reason,

            'slots' => $this->buildAvailabilitySlots(
                (int) $staff->id,
                $date,
                (int) $totalDuration
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: STORE CUSTOMER BOOKING
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $today = Carbon::today(
            self::BUSINESS_TIMEZONE
        );

        $maxDate = $today->copy()
            ->addDays(self::BOOKING_WINDOW_DAYS);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validate Booking Submission
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'booking_type' => [
                'required',
                Rule::in(['regular', 'followup']),
            ],

            'selected_service_ids' => [
                'nullable',
                'string',
            ],

            'selected_services' => [
                'nullable',
                'string',
            ],

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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Block Sunday and Emergency Closures
        |--------------------------------------------------------------------------
        | Validate on the server, not only in the date picker
        |--------------------------------------------------------------------------
        */

        $requestedDate = $validated['appointment_date'];

        if ($this->isStudioClosedOnDate($requestedDate)) {
            $closure = StudioClosure::activeClosureOn(
                $requestedDate
            );

            $message = $closure
                ? 'The studio is closed on this date — ' .
                    $closure->reason .
                    ' — please choose another date'
                : 'Nail Studio & Beauty is closed every Sunday — please choose another date';

            return back()
                ->withErrors([
                    'appointment_date' => $message,
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Authenticated Customer
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (
            strtolower(
                $validated['customer_email']
            ) !== strtolower($user->email)
        ) {
            return back()
                ->withErrors([
                    'customer_email' =>
                        'The booking email must match your account email',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Profile
        |--------------------------------------------------------------------------
        */

        $customer = $this->findCustomer(
            $user
        );

        $isNewCustomer = !$customer;

        if (!$customer) {
            $customer = new Customer();
        }

        if (
            $this->isBlockedOrInactive(
                $user->status ?? 'active'
            ) ||
            (
                !$isNewCustomer &&
                $this->isBlockedOrInactive(
                    $customer->status ?? 'active'
                )
            )
        ) {
            return back()
                ->withErrors([
                    'booking' =>
                        'Your account cannot create bookings',
                ])
                ->withInput();
        }

        $customer->fill([
            'user_id' => $user->id,
            'full_name' =>
                $validated['customer_name'],
            'email' => $user->email,
            'phone_number' =>
                $validated['customer_phone'],
        ]);

        if (
            $isNewCustomer ||
            empty($customer->status)
        ) {
            $customer->status = 'active';
        }

        $customer->save();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Active Booking Limit
        |--------------------------------------------------------------------------
        | First-time customers: 1 active appointment
        | After one finished appointment: 2 active appointments
        | Rescheduling-required appointments count toward limit
        |--------------------------------------------------------------------------
        */

        $completedBookings = Appointment::where(
            'customer_id',
            $customer->id
        )
            ->whereIn(
                'status',
                ['finished', 'Finished']
            )
            ->count();

        $activeBookingLimit =
            $completedBookings >= 1 ? 2 : 1;

        $activeBookings = Appointment::where(
            'customer_id',
            $customer->id
        )
            ->whereIn(
                'status',
                $this->customerOccupiedStatuses()
            )
            ->count();

        if (
            $activeBookings >=
            $activeBookingLimit
        ) {
            return back()
                ->withErrors([
                    'booking' =>
                        'You have reached your active booking limit — appointments awaiting rescheduling are included',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Selected Services
        |--------------------------------------------------------------------------
        */

        $selectedServiceIds = $this->parseServiceIds(
            $validated['selected_service_ids'] ?? ''
        );

        if ($selectedServiceIds->isEmpty()) {
            return back()
                ->withErrors([
                    'selected_services' =>
                        'Please select at least one service',
                ])
                ->withInput();
        }

        if (
            $selectedServiceIds->count() > 3
        ) {
            return back()
                ->withErrors([
                    'selected_services' =>
                        'Maximum of 3 services per booking',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Regular or Follow-Up Booking
        |--------------------------------------------------------------------------
        */

        $followUpAppointment = null;

        if (
            $validated['booking_type'] === 'followup'
        ) {
            if (
                empty(
                    $validated['follow_up_appointment_id']
                )
            ) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' =>
                            'Select a finished booking for the follow-up',
                    ])
                    ->withInput();
            }

            $followUpAppointment = Appointment::with([
                'services',
                'staff',
            ])
                ->whereKey(
                    $validated['follow_up_appointment_id']
                )
                ->where(
                    'customer_id',
                    $customer->id
                )
                ->whereIn(
                    'status',
                    ['finished', 'Finished']
                )
                ->first();

            if (!$followUpAppointment) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' =>
                            'The selected finished booking is invalid',
                    ])
                    ->withInput();
            }

            if (
                $followUpAppointment
                    ->services
                    ->isEmpty()
            ) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' =>
                            'The selected booking has no recorded services',
                    ])
                    ->withInput();
            }

            $originalServiceIds =
                $followUpAppointment
                    ->services
                    ->pluck('id')
                    ->map(
                        fn ($id) => (int) $id
                    );

            if (
                $selectedServiceIds
                    ->diff($originalServiceIds)
                    ->isNotEmpty()
            ) {
                return back()
                    ->withErrors([
                        'selected_services' =>
                            'Select services from the linked finished booking only',
                    ])
                    ->withInput();
            }

            $services = $followUpAppointment
                ->services
                ->filter(
                    fn ($service) =>
                        $selectedServiceIds->contains(
                            (int) $service->id
                        )
                )
                ->values();

            $totalPrice = 0;

            $totalDuration = $services->sum(
                fn ($service) =>
                    $this->getOriginalServiceDuration(
                        $service
                    )
            );
        } else {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Regular Services
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

            if (
                $services->count() !==
                $selectedServiceIds->count()
            ) {
                return back()
                    ->withErrors([
                        'selected_services' =>
                            'One or more selected services are unavailable',
                    ])
                    ->withInput();
            }

            $totalPrice = $services->sum(
                'price'
            );

            $totalDuration = $services->sum(
                fn ($service) =>
                    (int) $service->duration
            );
        }

        if (
            $services->isEmpty() ||
            $totalDuration <= 0
        ) {
            return back()
                ->withErrors([
                    'selected_services' =>
                        'The selected services or duration are invalid',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validate Selected Beautician
        |--------------------------------------------------------------------------
        */

        $staff = Staff::with('services')
            ->whereKey(
                $validated['staff_id']
            )
            ->whereIn(
                'status',
                ['Active', 'active']
            )
            ->first();

        if (!$staff) {
            return back()
                ->withErrors([
                    'staff_id' =>
                        'The selected beautician is unavailable',
                ])
                ->withInput();
        }

        if (
            !$this->staffCanPerformServices(
                $staff,
                $selectedServiceIds
            )
        ) {
            return back()
                ->withErrors([
                    'staff_id' =>
                        'The selected beautician is not assigned to all selected services',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Appointment Start and End
        |--------------------------------------------------------------------------
        */

        $startDateTime = Carbon::createFromFormat(
            '!Y-m-d H:i',
            $validated['appointment_date'] . ' ' .
            $validated['start_time'],
            self::BUSINESS_TIMEZONE
        );

        $endDateTime = $startDateTime
            ->copy()
            ->addMinutes(
                (int) $totalDuration
            );

        if (
            $startDateTime->lessThanOrEqualTo(
                Carbon::now(
                    self::BUSINESS_TIMEZONE
                )
            )
        ) {
            return back()
                ->withErrors([
                    'start_time' =>
                        'The selected appointment time has already started or passed',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Check Staff Day-Offs
        |--------------------------------------------------------------------------
        */

        if (
            $this->staffHasDayOff(
                $staff->id,
                $requestedDate
            )
        ) {
            return back()
                ->withErrors([
                    'staff_id' =>
                        'The selected beautician is on day off',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Database Booking Transaction
        |--------------------------------------------------------------------------
        | All critical conditions are checked again before saving
        |--------------------------------------------------------------------------
        */

        try {
            $appointment = DB::transaction(
                function () use (
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
                    | NS BEAUTY COMMENT: Lock Staff Record
                    |--------------------------------------------------------------------------
                    */

                    $lockedStaff = Staff::whereKey(
                        $validated['staff_id']
                    )
                        ->lockForUpdate()
                        ->first();

                    if (
                        !$lockedStaff ||
                        strtolower(
                            $lockedStaff->status ?? ''
                        ) !== 'active'
                    ) {
                        throw new \RuntimeException(
                            'The selected beautician is no longer available'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Lock Customer Record
                    |--------------------------------------------------------------------------
                    */

                    Customer::whereKey(
                        $customer->id
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Recheck Booking Limits
                    |--------------------------------------------------------------------------
                    */

                    $finishedCount = Appointment::where(
                        'customer_id',
                        $customer->id
                    )
                        ->whereIn(
                            'status',
                            ['finished', 'Finished']
                        )
                        ->count();

                    $bookingLimit =
                        $finishedCount >= 1 ? 2 : 1;

                    $activeCount = Appointment::where(
                        'customer_id',
                        $customer->id
                    )
                        ->whereIn(
                            'status',
                            $this->customerOccupiedStatuses()
                        )
                        ->count();

                    if (
                        $activeCount >= $bookingLimit
                    ) {
                        throw new \RuntimeException(
                            'You have reached your active booking limit'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Recheck Date and Time
                    |--------------------------------------------------------------------------
                    */

                    $transactionToday = Carbon::today(
                        self::BUSINESS_TIMEZONE
                    );

                    $transactionMaxDate =
                        $transactionToday
                            ->copy()
                            ->addDays(
                                self::BOOKING_WINDOW_DAYS
                            );

                    if (
                        $startDateTime->lessThanOrEqualTo(
                            Carbon::now(
                                self::BUSINESS_TIMEZONE
                            )
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
                    | NS BEAUTY COMMENT: Final Studio Closure Check
                    |--------------------------------------------------------------------------
                    | Lock matching closure records so a lift cannot
                    | change them while this booking is being created
                    |--------------------------------------------------------------------------
                    */

                    $closureDuringSave = StudioClosure::query()
                        ->active()
                        ->affectingDate(
                            $validated['appointment_date']
                        )
                        ->lockForUpdate()
                        ->first();

                    if ($closureDuringSave) {
                        throw new \RuntimeException(
                            'The studio is closed on this date — ' .
                            $closureDuringSave->reason
                        );
                    }

                    if (
                        $this->isSundayClosed(
                            $validated['appointment_date']
                        )
                    ) {
                        throw new \RuntimeException(
                            'Nail Studio & Beauty is closed every Sunday'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Final Staff Day-Off Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $this->staffHasDayOff(
                            (int) $lockedStaff->id,
                            $validated['appointment_date']
                        )
                    ) {
                        throw new \RuntimeException(
                            'The selected beautician is on day off for this date'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Final Skills Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !Schema::hasTable(
                            'staff_services'
                        )
                    ) {
                        throw new \RuntimeException(
                            'Staff service assignments are temporarily unavailable'
                        );
                    }

                    $assignedServiceIds = DB::table(
                        'staff_services'
                    )
                        ->where(
                            'staff_id',
                            $lockedStaff->id
                        )
                        ->pluck('service_id')
                        ->map(
                            fn ($id) => (int) $id
                        );

                    if (
                        $selectedServiceIds
                            ->diff($assignedServiceIds)
                            ->isNotEmpty()
                    ) {
                        throw new \RuntimeException(
                            'The beautician is no longer assigned to the selected services'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Appointment Overlap Check
                    |--------------------------------------------------------------------------
                    */

                    $hasConflict = Appointment::where(
                        'staff_id',
                        $lockedStaff->id
                    )
                        ->whereDate(
                            'appointment_date',
                            $validated['appointment_date']
                        )
                        ->whereIn(
                            'status',
                            Appointment::ACTIVE_STATUSES
                        )
                        ->where(
                            'start_time',
                            '<',
                            $endDateTime->format(
                                'H:i:s'
                            )
                        )
                        ->where(
                            'end_time',
                            '>',
                            $startDateTime->format(
                                'H:i:s'
                            )
                        )
                        ->exists();

                    if ($hasConflict) {
                        throw new \RuntimeException(
                            'The selected time is fully booked for this beautician'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Create Appointment
                    |--------------------------------------------------------------------------
                    */

                    $appointment = Appointment::create([
                        'customer_id' => $customer->id,
                        'staff_id' => $lockedStaff->id,
                        'appointment_date' =>
                            $validated['appointment_date'],
                        'start_time' =>
                            $startDateTime->format(
                                'H:i:s'
                            ),
                        'end_time' =>
                            $endDateTime->format(
                                'H:i:s'
                            ),
                        'total_price' => $totalPrice,
                        'status' => 'pending',
                        'booking_type' =>
                            $validated['booking_type'],
                        'follow_up_appointment_id' =>
                            $followUpAppointment?->id,
                        'follow_up_from' =>
                            $followUpAppointment
                                ? 'BK-' . str_pad(
                                    $followUpAppointment->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                )
                                : null,
                        'follow_up_reason' =>
                            $validated['follow_up_reason']
                            ?? null,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Attach Selected Services
                    |--------------------------------------------------------------------------
                    */

                    foreach ($services as $service) {
                        if (
                            $validated['booking_type'] ===
                            'followup'
                        ) {
                            $appointment
                                ->services()
                                ->attach(
                                    $service->id,
                                    [
                                        'price' => 0,
                                        'duration' =>
                                            $this->getOriginalServiceDuration(
                                                $service
                                            ),
                                    ]
                                );
                        } else {
                            $appointment
                                ->services()
                                ->attach(
                                    $service->id,
                                    [
                                        'price' =>
                                            $service->price,
                                        'duration' =>
                                            $service->duration,
                                    ]
                                );
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Booking Audit Log
                    |--------------------------------------------------------------------------
                    */

                    $bookingCode = 'BK-' . str_pad(
                        (string) $appointment->id,
                        4,
                        '0',
                        STR_PAD_LEFT
                    );

                    $isFollowUp =
                        $validated['booking_type'] ===
                        'followup';

                    $description = $isFollowUp
                        ? 'Customer created follow-up booking #' .
                            $bookingCode .
                            ' from original booking #BK-' .
                            str_pad(
                                (string) $followUpAppointment->id,
                                4,
                                '0',
                                STR_PAD_LEFT
                            )
                        : 'Customer created booking #' .
                            $bookingCode;

                    AuditLog::create([
                        'user_id' => $user->id,
                        'user_type' => 'customer',
                        'action' => $isFollowUp
                            ? 'create_followup_booking'
                            : 'create_booking',
                        'module' => 'Customer Booking',
                        'description' => $description,
                        'ip_address' => $request->ip(),
                        'user_agent' =>
                            $request->userAgent(),
                    ]);

                    return $appointment;
                },
                3
            );
        } catch (\RuntimeException $exception) {
            return back()
                ->withErrors([
                    'booking' =>
                        $exception->getMessage(),
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Notifications
        |--------------------------------------------------------------------------
        | Notifications are sent after transaction completion
        |--------------------------------------------------------------------------
        */

        $appointment->load([
            'customer',
            'staff',
            'services',
        ]);

        $notificationService = app(
            NotificationService::class
        );

        $bookingCode = 'BK-' . str_pad(
            (string) $appointment->id,
            4,
            '0',
            STR_PAD_LEFT
        );

        $mainService = $appointment
            ->services
            ->first()?->service_name
            ?? 'Beauty Service';

        $bookingTypeLabel =
            $validated['booking_type'] === 'followup'
                ? 'backjob / follow-up booking'
                : 'booking';

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Customer
        |--------------------------------------------------------------------------
        */

        $this->sendNotificationSafely(
            function () use (
                $notificationService,
                $appointment,
                $bookingCode,
                $bookingTypeLabel,
                $mainService
            ) {
                $notificationService->notifyCustomer(
                    $appointment->customer,
                    'Booking Created Successfully',
                    'Your ' .
                    $bookingTypeLabel .
                    ' #' .
                    $bookingCode .
                    ' for ' .
                    $mainService .
                    ' has been created successfully',
                    route(
                        'customer.booking.details',
                        $appointment->id,
                        false
                    ),
                    'booking_created'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Administrators
        |--------------------------------------------------------------------------
        */

        $this->sendNotificationSafely(
            function () use (
                $notificationService,
                $customer,
                $bookingCode,
                $bookingTypeLabel,
                $mainService
            ) {
                $notificationService->notifyAdmins(
                    'New Customer Booking',
                    $customer->full_name .
                    ' created ' .
                    $bookingTypeLabel .
                    ' #' .
                    $bookingCode .
                    ' for ' .
                    $mainService,
                    route(
                        'admin.bookings',
                        [],
                        false
                    ),
                    'new_booking'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notify Assigned Staff
        |--------------------------------------------------------------------------
        */

        $this->sendNotificationSafely(
            function () use (
                $notificationService,
                $appointment,
                $bookingCode,
                $mainService,
                $validated
            ) {
                $isFollowUp =
                    $validated['booking_type'] ===
                    'followup';

                $notificationService->notifyStaff(
                    $appointment->staff,
                    $isFollowUp
                        ? 'New Backjob / Follow-up Assigned'
                        : 'New Appointment Assigned',
                    'Appointment #' .
                    $bookingCode .
                    ' for ' .
                    $mainService .
                    ' has been assigned to you',
                    route(
                        'staff.appointments',
                        [],
                        false
                    ),
                    $isFollowUp
                        ? 'followup_assigned'
                        : 'appointment_assigned'
                );
            }
        );

        return redirect()
            ->route('customer.booking')
            ->with(
                'booking_success',
                true
            )
            ->with(
                'booking_id',
                $bookingCode
            )
            ->with(
                'booking_message',
                $validated['booking_type'] ===
                'followup'
                    ? 'Your backjob / follow-up booking has been recorded and linked to your previous appointment'
                    : 'Your booking has been recorded successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: BUILD AVAILABLE TIME SLOTS
    |--------------------------------------------------------------------------
    | The frontend receives:
    | - available
    | - past
    | - booked
    | - day_off
    | - closed
    |--------------------------------------------------------------------------
    */

    private function buildAvailabilitySlots(
        int $staffId,
        string $date,
        int $duration
    ): array {
        $now = Carbon::now(
            self::BUSINESS_TIMEZONE
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Check Full Studio Closures
        |--------------------------------------------------------------------------
        */

        $closedOnDate = $this->isStudioClosedOnDate(
            $date
        );

        $onDayOff = $this->staffHasDayOff(
            $staffId,
            $date
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Existing Staff Appointments
        |--------------------------------------------------------------------------
        */

        $existingAppointments = Appointment::where(
            'staff_id',
            $staffId
        )
            ->whereDate(
                'appointment_date',
                $date
            )
            ->whereIn(
                'status',
                Appointment::ACTIVE_STATUSES
            )
            ->get([
                'id',
                'start_time',
                'end_time',
            ]);

        $slots = [];

        foreach (self::BOOKING_TIMES as $time) {
            $start = Carbon::createFromFormat(
                '!Y-m-d H:i',
                $date . ' ' . $time,
                self::BUSINESS_TIMEZONE
            );

            $end = $start
                ->copy()
                ->addMinutes($duration);

            $status = 'available';

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Slot Priority
            |--------------------------------------------------------------------------
            | 1. Studio closure or Sunday
            | 2. Past appointment time
            | 3. Staff day off
            | 4. Existing booking conflict
            |--------------------------------------------------------------------------
            */

            if ($closedOnDate) {
                $status = 'closed';
            } elseif (
                $start->lessThanOrEqualTo($now)
            ) {
                $status = 'past';
            } elseif ($onDayOff) {
                $status = 'day_off';
            } else {
                foreach (
                    $existingAppointments as
                    $appointment
                ) {
                    if (!$appointment->end_time) {
                        $status = 'booked';
                        break;
                    }

                    $existingStart = Carbon::parse(
                        $date . ' ' .
                        $appointment->start_time,
                        self::BUSINESS_TIMEZONE
                    );

                    $existingEnd = Carbon::parse(
                        $date . ' ' .
                        $appointment->end_time,
                        self::BUSINESS_TIMEZONE
                    );

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
                'available' =>
                    $status === 'available',
                'status' => $status,
            ];
        }

        return $slots;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: SUNDAY CLOSURE
    |--------------------------------------------------------------------------
    */

    private function isSundayClosed(
        string $date
    ): bool {
        return Carbon::createFromFormat(
            '!Y-m-d',
            $date,
            self::BUSINESS_TIMEZONE
        )->dayOfWeek === self::CLOSED_WEEKDAY;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: COMBINED STUDIO CLOSURE CHECK
    |--------------------------------------------------------------------------
    | Returns true when the date is either:
    | - Sunday
    | - Covered by an active emergency closure
    |--------------------------------------------------------------------------
    */

    private function isStudioClosedOnDate(
        string $date
    ): bool {
        if ($this->isSundayClosed($date)) {
            return true;
        }

        return StudioClosure::isClosedOn(
            $date
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: CUSTOMER BOOKING OCCUPANCY
    |--------------------------------------------------------------------------
    | A rescheduling-required booking still counts toward
    | the customer's active booking limit, even though
    | it does not reserve its original staff time slot
    |--------------------------------------------------------------------------
    */

    private function customerOccupiedStatuses(): array
    {
        return array_values(
            array_unique(
                array_merge(
                    Appointment::ACTIVE_STATUSES,
                    Appointment::RESCHEDULING_STATUSES
                )
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: CUSTOMER RECORD LOOKUP
    |--------------------------------------------------------------------------
    */

    private function findCustomer(
        $user
    ): ?Customer {
        return Customer::where(
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
        )->first();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: BLOCKED AND INACTIVE USERS
    |--------------------------------------------------------------------------
    */

    private function isBlockedOrInactive(
        ?string $status
    ): bool {
        return in_array(
            strtolower(
                $status ?? 'active'
            ),
            ['blocked', 'inactive'],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: STAFF QUALIFICATIONS
    |--------------------------------------------------------------------------
    | All selected services must be assigned to the beautician
    |--------------------------------------------------------------------------
    */

    private function staffCanPerformServices(
        Staff $staff,
        $selectedServiceIds
    ): bool {
        if (
            !Schema::hasTable(
                'staff_services'
            )
        ) {
            return false;
        }

        $assignedServiceIds = $staff
            ->services
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            );

        return $selectedServiceIds
            ->diff($assignedServiceIds)
            ->isEmpty();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: STAFF DAY-OFF VALIDATION
    |--------------------------------------------------------------------------
    */

    private function staffHasDayOff(
        int $staffId,
        string $date
    ): bool {
        return StaffDayOff::where(
            'staff_id',
            $staffId
        )
            ->whereDate(
                'start_date',
                '<=',
                $date
            )
            ->whereDate(
                'end_date',
                '>=',
                $date
            )
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: SELECTED SERVICE ID PARSER
    |--------------------------------------------------------------------------
    */

    private function parseServiceIds(
        ?string $value
    ) {
        return collect(
            explode(
                ',',
                $value ?? ''
            )
        )
            ->map(
                fn ($id) =>
                    (int) trim($id)
            )
            ->filter()
            ->unique()
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: ORIGINAL FOLLOW-UP SERVICE DURATION
    |--------------------------------------------------------------------------
    | Use the duration recorded in the original booking
    |--------------------------------------------------------------------------
    */

    private function getOriginalServiceDuration(
        $service
    ): int {
        $pivotDuration = (int) (
            $service->pivot->duration ?? 0
        );

        if ($pivotDuration > 0) {
            return $pivotDuration;
        }

        return (int) (
            $service->duration ?? 0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: SAFE NOTIFICATION DISPATCH
    |--------------------------------------------------------------------------
    | The saved booking remains valid even if email fails
    |--------------------------------------------------------------------------
    */

    private function sendNotificationSafely(
        callable $callback
    ): void {
        try {
            $callback();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
