<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Http\Request;

class BookingDetailsController extends Controller
{
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->firstOrFail();

        $appointment = Appointment::with([
                'customer',
                'staff',
                'services',
                'cancelledBy',
                'statusUpdatedBy',
                'followUpAppointment',
                'followUpAppointment.services',
                'followUpAppointment.staff',
                'followUpAppointment.customer',
            ])
            ->where('customer_id', $customer->id)
            ->where('id', $id)
            ->firstOrFail();

        return view('customer.booking-details', [
            'appointment' => $appointment,
            'customer' => $customer,
        ]);
    }
}