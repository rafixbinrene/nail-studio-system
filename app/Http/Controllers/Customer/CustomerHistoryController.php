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
|
| Defense explanation:
| This controller helps customers review their complete transaction history
| while ensuring they can only access their own booking records.
|--------------------------------------------------------------------------
*/

class CustomerHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $appointments = collect();

        if ($customer) {
            $appointments = Appointment::with([
                    'staff',
                    'services',
                    'followUpAppointment',
                    'followUpAppointment.services',
                    'followUpAppointment.staff',
                    'followUpAppointment.customer',
                    'followUpBookings',
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