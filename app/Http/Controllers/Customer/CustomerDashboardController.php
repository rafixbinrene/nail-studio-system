<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Dashboard Controller
|
| Purpose:
| - Loads the logged-in customer's dashboard data.
| - Shows total, active, finished, cancelled, no-show, and follow-up bookings.
| - Shows the customer's nearest upcoming appointment.
| - Shows booking limit and remaining active booking slots.
| - Shows recommended/favorite services.
| - Shows simple customer notifications.
|
| Defense explanation:
| This controller helps customers monitor their appointment status and
| understand whether they can still create another active booking.
|--------------------------------------------------------------------------
*/

class CustomerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Customer Profile Matching
        |--------------------------------------------------------------------------
        | Purpose:
        | - Finds the customer profile connected to the logged-in account.
        | - Uses user_id first, then email as fallback.
        |--------------------------------------------------------------------------
        */
        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $appointments = collect();
        $activeBookings = collect();
        $finishedBookings = collect();
        $cancelledBookings = collect();
        $noShowBookings = collect();
        $followUpBookings = collect();
        $eligibleFollowUpBookings = collect();
        $upcomingAppointment = null;

        if ($customer) {
            $appointments = Appointment::with([
                    'staff',
                    'services',
                    'followUpAppointment',
                    'followUpBookings',
                ])
                ->where('customer_id', $customer->id)
                ->orderByDesc('appointment_date')
                ->orderByDesc('start_time')
                ->get();

            $activeBookings = $appointments->filter(function ($appointment) {
                return in_array(
                    strtolower(str_replace(' ', '-', $appointment->status)),
                    ['pending', 'approved', 'ongoing'],
                    true
                );
            });

            $finishedBookings = $appointments->filter(function ($appointment) {
                return strtolower(str_replace(' ', '-', $appointment->status)) === 'finished';
            });

            $cancelledBookings = $appointments->filter(function ($appointment) {
                return strtolower(str_replace(' ', '-', $appointment->status)) === 'cancelled';
            });

            $noShowBookings = $appointments->filter(function ($appointment) {
                return strtolower(str_replace(' ', '-', $appointment->status)) === 'no-show';
            });

            $followUpBookings = $appointments->filter(function ($appointment) {
                return $appointment->booking_type === 'followup';
            });

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Backjob / Follow-up Eligibility
            |--------------------------------------------------------------------------
            | Purpose:
            | - Finished regular bookings are shown as possible follow-up references.
            | - Follow-up bookings are excluded from becoming another follow-up source.
            |--------------------------------------------------------------------------
            */
            $eligibleFollowUpBookings = $finishedBookings->filter(function ($appointment) {
                return $appointment->booking_type !== 'followup';
            });

            $upcomingAppointment = Appointment::with(['staff', 'services'])
                ->where('customer_id', $customer->id)
                ->whereDate('appointment_date', '>=', now()->toDateString())
                ->whereIn('status', Appointment::ACTIVE_STATUSES)
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Booking Limit Rule
        |--------------------------------------------------------------------------
        | Purpose:
        | - New customer: 1 active booking allowed.
        | - Customer with at least 1 finished booking: 2 active bookings allowed.
        |--------------------------------------------------------------------------
        */
        $bookingLimit = $finishedBookings->count() >= 1 ? 2 : 1;
        $remainingBookings = max(0, $bookingLimit - $activeBookings->count());

        $favoriteServices = collect();

        if ($customer) {
            $favoriteServices = DB::table('appointment_services')
                ->join('services', 'appointment_services.service_id', '=', 'services.id')
                ->join('appointments', 'appointment_services.appointment_id', '=', 'appointments.id')
                ->where('appointments.customer_id', $customer->id)
                ->select(
                    'services.id',
                    'services.service_name',
                    'services.price',
                    'services.duration',
                    'services.image_path',
                    DB::raw('COUNT(*) as times_booked')
                )
                ->groupBy(
                    'services.id',
                    'services.service_name',
                    'services.price',
                    'services.duration',
                    'services.image_path'
                )
                ->orderByDesc('times_booked')
                ->limit(3)
                ->get();
        }

        if ($favoriteServices->isEmpty()) {
            $favoriteServices = Service::whereIn('status', ['Available', 'available'])
                ->orderBy('service_name')
                ->limit(3)
                ->get();
        }

        $notifications = [];

        if ($upcomingAppointment) {
            $mainService = $upcomingAppointment->services->first()?->service_name ?? 'Beauty Service';

            $appointmentDate = $upcomingAppointment->appointment_date
                ? $upcomingAppointment->appointment_date->format('F d, Y')
                : 'selected date';

            $appointmentTime = $upcomingAppointment->start_time
                ? \Carbon\Carbon::parse($upcomingAppointment->start_time)->format('h:i A')
                : 'selected time';

            $notifications[] = [
                'title' => 'Upcoming Appointment',
                'message' => $mainService . ' is scheduled on ' . $appointmentDate . ' at ' . $appointmentTime . '.',
            ];

            $notifications[] = [
                'title' => 'Reminder',
                'message' => 'Please arrive on time to avoid delays in your appointment schedule.',
            ];
        } else {
            $notifications[] = [
                'title' => 'No Upcoming Appointment',
                'message' => 'You currently have no upcoming appointment.',
            ];
        }

        $notifications[] = [
            'title' => 'Booking Limit',
            'message' => 'You may still create ' . $remainingBookings . ' active booking(s).',
        ];

        if ($eligibleFollowUpBookings->count() > 0) {
            $notifications[] = [
                'title' => 'Follow-up Available',
                'message' => 'You have finished booking(s) that may be used for a backjob or follow-up request.',
            ];
        }

        return view('customer.dashboard', [
            'customer' => $customer,
            'appointments' => $appointments,
            'activeBookings' => $activeBookings,
            'finishedBookings' => $finishedBookings,
            'cancelledBookings' => $cancelledBookings,
            'noShowBookings' => $noShowBookings,
            'followUpBookings' => $followUpBookings,
            'eligibleFollowUpBookings' => $eligibleFollowUpBookings,
            'upcomingAppointment' => $upcomingAppointment,
            'bookingLimit' => $bookingLimit,
            'remainingBookings' => $remainingBookings,
            'favoriteServices' => $favoriteServices,
            'notifications' => $notifications,
        ]);
    }
}