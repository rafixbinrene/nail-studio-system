<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer History Controller
|
| Purpose:
| - Loads all booking records owned by the logged-in customer.
| - Separates bookings into active, finished, cancelled, no-show, and follow-up.
| - Supports the Booking History page tabs.
| - Loads submitted feedback for each appointment.
|
| Defense explanation:
| This controller helps customers review their complete transaction history
| while ensuring they can only access their own booking records. Feedback is
| loaded so customers can see the rating and comment they already submitted.
|--------------------------------------------------------------------------
*/

class CustomerHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Customer Ownership Check
        |--------------------------------------------------------------------------
        | Purpose:
        | - Finds the customer profile connected to the logged-in user.
        | - Supports both user_id and email matching.
        |--------------------------------------------------------------------------
        */
        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $appointments = collect();

        if ($customer) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Customer Booking History Query
            |--------------------------------------------------------------------------
            | Purpose:
            | - Loads customer bookings.
            | - Loads staff and services.
            | - Loads follow-up/backjob connection.
            | - Loads feedback so submitted rating/comment appears in history.
            |--------------------------------------------------------------------------
            */
            $appointments = Appointment::with([
                    'staff',
                    'services',
                    'feedback',
                    'feedback.staff',
                    'feedback.customer',

                    'followUpAppointment',
                    'followUpAppointment.services',
                    'followUpAppointment.staff',
                    'followUpAppointment.customer',
                    'followUpAppointment.feedback',

                    'followUpBookings',
                    'followUpBookings.feedback',
                ])
                ->where('customer_id', $customer->id)
                ->orderByDesc('appointment_date')
                ->orderByDesc('start_time')
                ->get();
        }

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

        return view('customer.history', [
            'appointments' => $appointments,
            'activeBookings' => $activeBookings,
            'finishedBookings' => $finishedBookings,
            'cancelledBookings' => $cancelledBookings,
            'noShowBookings' => $noShowBookings,
            'followUpBookings' => $followUpBookings,
            'customer' => $customer,
        ]);
    }
}