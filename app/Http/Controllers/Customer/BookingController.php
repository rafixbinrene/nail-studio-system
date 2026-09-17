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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Booking Controller
|
| Purpose:
| - Shows the customer booking page.
| - Creates regular bookings.
| - Creates backjob / follow-up bookings.
| - Checks customer booking limit.
| - Checks service availability.
| - Checks staff availability.
| - Blocks booking if selected staff is on day off.
| - Blocks booking if selected staff is not assigned to the selected service.
| - Sends in-app and email notifications after booking creation.
|
| Defense explanation:
| This controller connects Customer Booking, Staff Management, Service
| Management, Audit Logs, In-App Notifications, and Email Notifications.
| It prevents double booking, respects staff day-off records, validates staff
| service skills, and notifies all affected users.
|--------------------------------------------------------------------------
*/

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $customerCompletedBookings = 0;
        $customerActiveBookings = 0;
        $eligibleFollowUps = collect();

        if ($customer) {
            $customerCompletedBookings = Appointment::where('customer_id', $customer->id)
                ->whereIn('status', ['finished', 'Finished'])
                ->count();

            $customerActiveBookings = Appointment::where('customer_id', $customer->id)
                ->whereIn('status', Appointment::ACTIVE_STATUSES)
                ->count();

            $eligibleFollowUps = Appointment::with(['staff', 'services'])
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['finished', 'Finished'])
                ->orderByDesc('appointment_date')
                ->orderByDesc('start_time')
                ->limit(10)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Available services only.
        |--------------------------------------------------------------------------
        | Purpose:
        | - Customers should only see services currently available for booking.
        |--------------------------------------------------------------------------
        */
        $services = Service::whereIn('status', ['Available', 'available'])
            ->orderBy('service_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Active staff only.
        |--------------------------------------------------------------------------
        | Purpose:
        | - Customers should only see active staff members.
        | - Services and active day-offs are loaded for service/staff validation.
        |--------------------------------------------------------------------------
        */
        $staffMembers = Staff::with(['services', 'activeDayOffs'])
            ->whereIn('status', ['Active', 'active'])
            ->orderBy('full_name')
            ->get();

        $staffDayOffs = StaffDayOff::with('staff')
            ->whereDate('end_date', '>=', today())
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

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | 7-Day Booking Window
        |--------------------------------------------------------------------------
        | Purpose:
        | - Customers can book from today up to 6 days after today.
        | - This gives a total 7-day advance booking window including today.
        |--------------------------------------------------------------------------
        */
        $maxDate = now()->addDays(6)->toDateString();

        $validated = $request->validate([
            'booking_type' => ['required', Rule::in(['regular', 'followup'])],
            'selected_service_ids' => ['nullable', 'string'],
            'selected_services' => ['nullable', 'string'],
            'follow_up_appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . $maxDate],
            'start_time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'customer_email' => ['required', 'email', 'max:255'],
            'follow_up_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Email Ownership Validation
        |--------------------------------------------------------------------------
        | Purpose:
        | - Prevents customers from booking using another email address.
        |--------------------------------------------------------------------------
        */
        if (strtolower($validated['customer_email']) !== strtolower($user->email)) {
            return back()
                ->withErrors([
                    'customer_email' => 'The booking email must match your logged-in account email.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Customer Profile Matching / Creation
        |--------------------------------------------------------------------------
        | Purpose:
        | - Finds the customer by user_id or email.
        | - Creates a customer profile if missing.
        | - Does not reset blocked/inactive customers to active.
        |--------------------------------------------------------------------------
        */
        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $isNewCustomer = false;

        if (!$customer) {
            $customer = new Customer();
            $isNewCustomer = true;
        }

        $customerStatus = strtolower($customer->status ?? 'active');
        $userStatus = strtolower($user->status ?? 'active');

        if (in_array($userStatus, ['blocked', 'inactive'], true)) {
            return back()
                ->withErrors([
                    'booking' => 'Your account is currently restricted and cannot create bookings.',
                ])
                ->withInput();
        }

        if (!$isNewCustomer && in_array($customerStatus, ['blocked', 'inactive'], true)) {
            return back()
                ->withErrors([
                    'booking' => 'Your customer account is currently restricted and cannot create bookings.',
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
        | NS BEAUTY COMMENT:
        | Booking Limit Rule
        |--------------------------------------------------------------------------
        | Purpose:
        | - New customer: 1 active booking.
        | - Customer with at least 1 finished booking: 2 active bookings.
        |--------------------------------------------------------------------------
        */
        $completedBookings = Appointment::where('customer_id', $customer->id)
            ->whereIn('status', ['finished', 'Finished'])
            ->count();

        $activeBookingLimit = $completedBookings >= 1 ? 2 : 1;

        $activeBookings = Appointment::where('customer_id', $customer->id)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->count();

        if ($activeBookings >= $activeBookingLimit) {
            return back()
                ->withErrors([
                    'booking' => 'You have reached your active booking limit.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Selected Services Parser
        |--------------------------------------------------------------------------
        | Purpose:
        | - Converts comma-separated selected service IDs into a clean collection.
        | - Limits booking to maximum 3 services.
        |--------------------------------------------------------------------------
        */
        $selectedServiceIds = collect(explode(',', $validated['selected_service_ids'] ?? ''))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        if ($selectedServiceIds->count() < 1) {
            return back()
                ->withErrors([
                    'selected_services' => 'Please select at least one service.',
                ])
                ->withInput();
        }

        if ($selectedServiceIds->count() > 3) {
            return back()
                ->withErrors([
                    'selected_services' => 'Only 3 services are allowed per booking.',
                ])
                ->withInput();
        }

        $followUpAppointment = null;

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Follow-up / Backjob Booking Validation
        |--------------------------------------------------------------------------
        | Purpose:
        | - Customers can only select service/s from their own finished booking.
        | - Follow-up booking price is set to zero.
        |--------------------------------------------------------------------------
        */
        if ($validated['booking_type'] === 'followup') {
            if (empty($validated['follow_up_appointment_id'])) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' => 'Please select a finished booking history for the backjob/follow-up.',
                    ])
                    ->withInput();
            }

            $followUpAppointment = Appointment::with(['services', 'staff'])
                ->where('id', $validated['follow_up_appointment_id'])
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['finished', 'Finished'])
                ->first();

            if (!$followUpAppointment) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' => 'The selected booking history is not valid for backjob/follow-up.',
                    ])
                    ->withInput();
            }

            if ($followUpAppointment->services->isEmpty()) {
                return back()
                    ->withErrors([
                        'follow_up_appointment_id' => 'The selected previous booking has no recorded services.',
                    ])
                    ->withInput();
            }

            $originalServiceIds = $followUpAppointment->services
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            $invalidSelectedServices = $selectedServiceIds->diff($originalServiceIds);

            if ($invalidSelectedServices->isNotEmpty()) {
                return back()
                    ->withErrors([
                        'selected_services' => 'You can only select services from the chosen finished booking history.',
                    ])
                    ->withInput();
            }

            $services = $followUpAppointment->services
                ->filter(fn ($service) => $selectedServiceIds->contains((int) $service->id))
                ->values();

            $totalPrice = 0;

            $totalDuration = $services->sum(function ($service) {
                return $this->getOriginalServiceDuration($service);
            });
        } else {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Regular Booking Validation
            |--------------------------------------------------------------------------
            | Purpose:
            | - Only available services can be booked.
            |--------------------------------------------------------------------------
            */
            $services = Service::whereIn('id', $selectedServiceIds)
                ->whereIn('status', ['Available', 'available'])
                ->get();

            if ($services->count() !== $selectedServiceIds->count()) {
                return back()
                    ->withErrors([
                        'selected_services' => 'One or more selected services are unavailable.',
                    ])
                    ->withInput();
            }

            $totalPrice = $services->sum('price');

            $totalDuration = $services->sum(function ($service) {
                return (int) $service->duration;
            });
        }

        if ($services->isEmpty()) {
            return back()
                ->withErrors([
                    'selected_services' => 'No valid services were selected.',
                ])
                ->withInput();
        }

        if ($totalDuration <= 0) {
            return back()
                ->withErrors([
                    'duration' => 'The selected service duration is invalid.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Staff Validation
        |--------------------------------------------------------------------------
        | Purpose:
        | - Selected staff must be active.
        |--------------------------------------------------------------------------
        */
        $staff = Staff::with(['services'])
            ->where('id', $validated['staff_id'])
            ->whereIn('status', ['Active', 'active'])
            ->first();

        if (!$staff) {
            return back()
                ->withErrors([
                    'staff_id' => 'The selected beautician is not available.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Staff Skill / Service Protection
        |--------------------------------------------------------------------------
        | Purpose:
        | - Ensures customers can only book a staff member who is assigned to
        |   the selected service/s.
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('staff_services')) {
            $assignedServiceIds = $staff->services
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($assignedServiceIds->isEmpty()) {
                return back()
                    ->withErrors([
                        'staff_id' => 'The selected beautician has no assigned services yet. Please choose another beautician.',
                    ])
                    ->withInput();
            }

            $servicesNotAssignedToStaff = $selectedServiceIds->diff($assignedServiceIds);

            if ($servicesNotAssignedToStaff->isNotEmpty()) {
                return back()
                    ->withErrors([
                        'staff_id' => 'The selected beautician is not assigned to one or more selected services. Please choose another beautician.',
                    ])
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Staff Day-Off Protection
        |--------------------------------------------------------------------------
        | Purpose:
        | - Prevents customers from booking staff on day-off dates.
        |--------------------------------------------------------------------------
        */
        $staffDayOff = StaffDayOff::where('staff_id', $staff->id)
            ->whereDate('start_date', '<=', $validated['appointment_date'])
            ->whereDate('end_date', '>=', $validated['appointment_date'])
            ->first();

        if ($staffDayOff) {
            $dayOffStart = Carbon::parse($staffDayOff->start_date)->format('M d, Y');
            $dayOffEnd = Carbon::parse($staffDayOff->end_date)->format('M d, Y');

            return back()
                ->withErrors([
                    'staff_id' => 'The selected beautician is on day off from ' . $dayOffStart . ' to ' . $dayOffEnd . '. Please choose another beautician or date.',
                ])
                ->withInput();
        }

        $startDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['appointment_date'] . ' ' . $validated['start_time']
        );

        $endDateTime = $startDateTime->copy()->addMinutes($totalDuration);

        try {
            $appointment = DB::transaction(function () use (
                $validated,
                $request,
                $user,
                $customer,
                $services,
                $totalPrice,
                $startDateTime,
                $endDateTime,
                $followUpAppointment
            ) {
                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT:
                | Final Day-Off Check Inside Transaction
                |--------------------------------------------------------------------------
                | Purpose:
                | - Prevents race condition if staff day-off is added while booking.
                |--------------------------------------------------------------------------
                */
                $staffOnDayOff = StaffDayOff::where('staff_id', $validated['staff_id'])
                    ->whereDate('start_date', '<=', $validated['appointment_date'])
                    ->whereDate('end_date', '>=', $validated['appointment_date'])
                    ->lockForUpdate()
                    ->exists();

                if ($staffOnDayOff) {
                    throw new \RuntimeException('The selected beautician is now marked as on day off for this date. Please choose another beautician or date.');
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT:
                | Double-Booking Protection
                |--------------------------------------------------------------------------
                | Purpose:
                | - Selected staff cannot have overlapping active bookings.
                |--------------------------------------------------------------------------
                */
                $hasConflict = Appointment::where('staff_id', $validated['staff_id'])
                    ->whereDate('appointment_date', $validated['appointment_date'])
                    ->whereIn('status', Appointment::ACTIVE_STATUSES)
                    ->where(function ($query) use ($startDateTime, $endDateTime) {
                        $query->where('start_time', '<', $endDateTime->format('H:i:s'))
                            ->where('end_time', '>', $startDateTime->format('H:i:s'));
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($hasConflict) {
                    throw new \RuntimeException('The selected beautician is no longer available for this time slot.');
                }

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
                        ? 'BK-' . str_pad($followUpAppointment->id, 4, '0', STR_PAD_LEFT)
                        : null,
                    'follow_up_reason' => $validated['follow_up_reason'] ?? null,
                ]);

                foreach ($services as $service) {
                    if ($validated['booking_type'] === 'followup') {
                        $appointment->services()->attach($service->id, [
                            'price' => 0,
                            'duration' => $this->getOriginalServiceDuration($service),
                        ]);
                    } else {
                        $appointment->services()->attach($service->id, [
                            'price' => $service->price,
                            'duration' => $service->duration,
                        ]);
                    }
                }

                AuditLog::create([
                    'user_id' => $user->id,
                    'user_type' => 'customer',
                    'action' => $validated['booking_type'] === 'followup'
                        ? 'create_followup_booking'
                        : 'create_booking',
                    'module' => 'Customer Booking',
                    'description' => $validated['booking_type'] === 'followup'
                        ? 'Customer created partial backjob/follow-up booking #BK-' .
                            str_pad($appointment->id, 4, '0', STR_PAD_LEFT) .
                            ' from original booking #BK-' .
                            str_pad($followUpAppointment->id, 4, '0', STR_PAD_LEFT)
                        : 'Customer created booking #BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT),
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
        | NS BEAUTY COMMENT:
        | Booking Notification + Email
        |--------------------------------------------------------------------------
        | Purpose:
        | - Notifies the customer that the booking was created.
        | - Notifies admin that a new booking was created.
        | - Notifies assigned staff that a booking was assigned.
        |
        | Defense explanation:
        | This supports real-time communication by saving an in-app notification
        | and sending an email copy using Laravel mail settings from .env.
        |--------------------------------------------------------------------------
        */
        $appointment->load(['customer', 'staff', 'services']);

        $notificationService = app(NotificationService::class);

        $bookingCode = 'BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT);
        $mainService = $appointment->services->first()?->service_name ?? 'Beauty Service';

        $bookingTypeLabel = $validated['booking_type'] === 'followup'
            ? 'backjob/follow-up booking'
            : 'booking';

        $notificationService->notifyCustomer(
            $appointment->customer,
            'Booking Created Successfully',
            'Your ' . $bookingTypeLabel . ' #' . $bookingCode . ' for ' . $mainService . ' has been created successfully.',
            route('customer.booking.details', $appointment->id, false),
            'booking_created'
        );

        $notificationService->notifyAdmins(
            'New Customer Booking',
            $customer->full_name . ' created ' . $bookingTypeLabel . ' #' . $bookingCode . ' for ' . $mainService . '.',
            route('admin.bookings', [], false),
            'new_booking'
        );

        $notificationService->notifyStaff(
            $appointment->staff,
            $validated['booking_type'] === 'followup'
                ? 'New Backjob / Follow-up Assigned'
                : 'New Appointment Assigned',
            'Appointment #' . $bookingCode . ' for ' . $mainService . ' has been assigned to you.',
            route('staff.appointments', [], false),
            $validated['booking_type'] === 'followup'
                ? 'followup_assigned'
                : 'appointment_assigned'
        );

        return redirect()
            ->route('customer.booking')
            ->with('booking_success', true)
            ->with('booking_id', 'BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT))
            ->with('booking_message', $validated['booking_type'] === 'followup'
                ? 'Your backjob/follow-up booking has been confirmed and linked to your selected previous service/s.'
                : 'Your booking has been confirmed and recorded successfully.');
    }

    private function getOriginalServiceDuration($service): int
    {
        $pivotDuration = (int) ($service->pivot->duration ?? 0);

        if ($pivotDuration > 0) {
            return $pivotDuration;
        }

        return (int) ($service->duration ?? 0);
    }
}