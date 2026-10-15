<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Dashboard Controller
|--------------------------------------------------------------------------
| Purpose:
| - Loads the authenticated customer's appointments
| - Preserves booking history and booking limit calculations
| - Displays the nearest upcoming appointment
| - Calculates favourite services from actual booking history
| - Uses real service information from Service Management
|
| IMPORTANT:
| - No random or unrelated services are displayed as favourites
| - No service images are retrieved
| - Placeholder notifications have been removed
| - Real notifications will be connected after reviewing the
|   existing notification model and database structure
|--------------------------------------------------------------------------
*/

class CustomerDashboardController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Authenticated User
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Profile Matching
        |--------------------------------------------------------------------------
        | Preserve the existing user_id matching and email fallback
        |--------------------------------------------------------------------------
        */

        $customer = Customer::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('email', $user->email);
        })->first();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Default Dashboard Collections
        |--------------------------------------------------------------------------
        */

        $appointments = collect();
        $activeBookings = collect();
        $finishedBookings = collect();
        $cancelledBookings = collect();
        $noShowBookings = collect();
        $followUpBookings = collect();
        $eligibleFollowUpBookings = collect();

        $upcomingAppointment = null;
        $favoriteServices = collect();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Appointment History
        |--------------------------------------------------------------------------
        | Retrieve appointments belonging to the current customer
        | Existing relationship loading is preserved
        |--------------------------------------------------------------------------
        */

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

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Active Bookings
            |--------------------------------------------------------------------------
            */

            $activeBookings = $appointments->filter(function ($appointment) {
                return in_array(
                    strtolower(str_replace(' ', '-', $appointment->status)),
                    ['pending', 'approved', 'ongoing'],
                    true
                );
            });

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Finished Bookings
            |--------------------------------------------------------------------------
            */

            $finishedBookings = $appointments->filter(function ($appointment) {
                return strtolower(
                    str_replace(' ', '-', $appointment->status)
                ) === 'finished';
            });

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Cancelled Bookings
            |--------------------------------------------------------------------------
            */

            $cancelledBookings = $appointments->filter(function ($appointment) {
                return strtolower(
                    str_replace(' ', '-', $appointment->status)
                ) === 'cancelled';
            });

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: No-show Bookings
            |--------------------------------------------------------------------------
            */

            $noShowBookings = $appointments->filter(function ($appointment) {
                return strtolower(
                    str_replace(' ', '-', $appointment->status)
                ) === 'no-show';
            });

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Follow-up Bookings
            |--------------------------------------------------------------------------
            */

            $followUpBookings = $appointments->filter(function ($appointment) {
                return $appointment->booking_type === 'followup';
            });

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Follow-up Eligibility
            |--------------------------------------------------------------------------
            | Preserve the existing dashboard's eligibility calculation
            |
            | Actual follow-up authorization remains the responsibility
            | of the existing booking backend
            |--------------------------------------------------------------------------
            */

            $eligibleFollowUpBookings = $finishedBookings->filter(
                function ($appointment) {
                    return $appointment->booking_type !== 'followup';
                }
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Upcoming Appointment
            |--------------------------------------------------------------------------
            | Retrieve the next active appointment for this customer
            |--------------------------------------------------------------------------
            */

            $upcomingAppointment = Appointment::with([
                    'staff',
                    'services',
                ])
                ->where('customer_id', $customer->id)
                ->whereDate(
                    'appointment_date',
                    '>=',
                    now()->toDateString()
                )
                ->whereIn(
                    'status',
                    Appointment::ACTIVE_STATUSES
                )
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Personalized Favourite Services
            |--------------------------------------------------------------------------
            | Data sources:
            | - appointment_services
            | - appointments
            | - services
            |
            | Rules:
            | 1. Only services actually booked by this customer
            | 2. Exclude cancelled and no-show appointments
            | 3. Only display currently available services
            | 4. Exclude soft-deleted services
            | 5. Count booking frequency
            | 6. Rank most frequently booked first
            | 7. Do not use random or alphabetical fallback services
            |
            | This supports repeat-booking suggestions based on
            | the customer's own service preferences
            |--------------------------------------------------------------------------
            */

            $favoriteServices = DB::table('appointment_services')
                ->join(
                    'appointments',
                    'appointment_services.appointment_id',
                    '=',
                    'appointments.id'
                )
                ->join(
                    'services',
                    'appointment_services.service_id',
                    '=',
                    'services.id'
                )
                ->where(
                    'appointments.customer_id',
                    $customer->id
                )
                ->whereRaw(
                    "LOWER(REPLACE(appointments.status, ' ', '-')) NOT IN (?, ?)",
                    ['cancelled', 'no-show']
                )
                ->whereIn(
                    'services.status',
                    ['Available', 'available']
                )
                ->whereNull('services.deleted_at')
                ->select(
                    'services.id',
                    'services.service_name',
                    'services.price',
                    'services.duration',
                    DB::raw('COUNT(*) AS times_booked')
                )
                ->groupBy(
                    'services.id',
                    'services.service_name',
                    'services.price',
                    'services.duration'
                )
                ->orderByDesc('times_booked')
                ->orderBy('services.service_name')
                ->limit(3)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Limit Rule
        |--------------------------------------------------------------------------
        | Existing business rules are preserved:
        |
        | New customer:
        | - Maximum 1 active booking
        |
        | Customer with at least one finished appointment:
        | - Maximum 2 active bookings
        |
        | This dashboard calculation does not modify booking enforcement
        |--------------------------------------------------------------------------
        */

        $bookingLimit = $finishedBookings->count() >= 1 ? 2 : 1;

        $remainingBookings = max(
            0,
            $bookingLimit - $activeBookings->count()
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Customer Notifications
        |--------------------------------------------------------------------------
        | Previous hardcoded messages have been removed.
        |
        | We will connect the existing app_notifications table after
        | reviewing its model, migration and relationships.
        |
        | For now:
        | - No fabricated notifications
        | - No cross-customer notification access
        | - Dashboard displays its "No notifications yet" empty state
        |
        | Do not deploy this intermediate version until the real
        | notification integration is completed and tested.
        |--------------------------------------------------------------------------
        */

        $notifications = collect();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Dashboard View Data
        |--------------------------------------------------------------------------
        | Existing view variable names are preserved
        |--------------------------------------------------------------------------
        */

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
