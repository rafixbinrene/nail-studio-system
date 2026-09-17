<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Cancel Booking Controller
|
| Purpose:
| - Shows the customer booking cancellation form.
| - Allows customers to cancel their own active bookings.
| - Saves cancellation reason and audit log.
| - Sends in-app and email notifications to customer, admin, and staff.
|
| Defense explanation:
| This controller protects booking records because customers can only cancel
| their own bookings, closed bookings cannot be cancelled again, and all
| affected users are notified when the cancellation happens.
|--------------------------------------------------------------------------
*/

class CustomerCancelBookingController extends Controller
{
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->firstOrFail();

        $appointment = Appointment::with(['customer', 'staff', 'services'])
            ->where('customer_id', $customer->id)
            ->where('id', $id)
            ->firstOrFail();

        return view('customer.cancel-booking', [
            'appointment' => $appointment,
            'customer' => $customer,
        ]);
    }

    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
            'additional_details' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->firstOrFail();

        $appointment = Appointment::with(['customer', 'staff', 'services'])
            ->where('customer_id', $customer->id)
            ->where('id', $id)
            ->firstOrFail();

        $currentStatus = strtolower(str_replace(' ', '-', $appointment->status));

        if (in_array($currentStatus, ['finished', 'cancelled', 'no-show'], true)) {
            return back()->withErrors([
                'booking' => 'This booking can no longer be cancelled.',
            ]);
        }

        $reason = $validated['cancellation_reason'];

        if (!empty($validated['additional_details'])) {
            $reason .= ' - ' . $validated['additional_details'];
        }

        DB::transaction(function () use ($appointment, $reason, $request, $user) {
            $appointment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'status_updated_by' => $user->id,
                'status_updated_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'user_type' => 'customer',
                'action' => 'cancel_booking',
                'module' => 'Customer Booking',
                'description' => 'Customer cancelled booking #BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Cancellation Notification + Email
        |--------------------------------------------------------------------------
        | Purpose:
        | - Customer receives cancellation confirmation.
        | - Admin receives cancellation monitoring notification.
        | - Assigned staff receives schedule update notification.
        |--------------------------------------------------------------------------
        */
        $appointment->refresh();
        $appointment->load(['customer', 'staff', 'services']);

        $notificationService = app(NotificationService::class);

        $bookingCode = 'BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT);
        $mainService = $appointment->services->first()?->service_name ?? 'Beauty Service';

        $notificationService->notifyCustomer(
            $appointment->customer,
            'Booking Cancelled',
            'Your booking #' . $bookingCode . ' for ' . $mainService . ' has been cancelled successfully.',
            route('customer.history', [], false),
            'booking_cancelled'
        );

        $notificationService->notifyAdmins(
            'Customer Cancelled Booking',
            $customer->full_name . ' cancelled booking #' . $bookingCode . ' for ' . $mainService . '. Reason: ' . $reason,
            route('admin.bookings', [], false),
            'customer_cancelled_booking'
        );

        $notificationService->notifyStaff(
            $appointment->staff,
            'Assigned Appointment Cancelled',
            'Booking #' . $bookingCode . ' for ' . $mainService . ' assigned to you has been cancelled.',
            route('staff.appointments', [], false),
            'staff_booking_cancelled'
        );

        return redirect()
            ->route('customer.booking.cancel.form', $appointment->id)
            ->with('cancel_success', true)
            ->with('cancel_message', 'Your booking has been cancelled successfully.');
    }
}