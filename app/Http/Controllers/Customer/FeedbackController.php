<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Feedback;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Feedback Controller
|
| Purpose:
| - Shows feedback form for finished appointments.
| - Saves customer rating and comment.
| - Prevents feedback for unfinished, cancelled, or no-show bookings.
| - Prevents duplicate feedback for the same appointment.
| - Notifies admin and staff after feedback is submitted.
|
| Defense explanation:
| The system only allows feedback after a completed appointment, making
| feedback records more valid and connected to actual service experience.
|--------------------------------------------------------------------------
*/

class FeedbackController extends Controller
{
    private function getCustomer(Request $request): ?Customer
    {
        return Customer::where('user_id', $request->user()->id)
            ->orWhere('email', $request->user()->email)
            ->first();
    }

    public function show(Request $request, int $id)
    {
        $customer = $this->getCustomer($request);

        if (!$customer) {
            return redirect()
                ->route('customer.history')
                ->withErrors(['feedback' => 'Customer profile was not found.']);
        }

        $appointment = Appointment::with(['customer', 'staff', 'services'])
            ->where('id', $id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $status = strtolower(str_replace(' ', '-', $appointment->status));

        if ($status !== 'finished') {
            return redirect()
                ->route('customer.history')
                ->withErrors(['feedback' => 'Feedback is only available for finished appointments.']);
        }

        $existingFeedback = Feedback::where('appointment_id', $appointment->id)->first();

        return view('customer.feedback', [
            'customer' => $customer,
            'appointment' => $appointment,
            'existingFeedback' => $existingFeedback,
        ]);
    }

    public function store(Request $request, int $id)
    {
        $customer = $this->getCustomer($request);

        if (!$customer) {
            return redirect()
                ->route('customer.history')
                ->withErrors(['feedback' => 'Customer profile was not found.']);
        }

        $appointment = Appointment::with(['customer', 'staff', 'services'])
            ->where('id', $id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $status = strtolower(str_replace(' ', '-', $appointment->status));

        if ($status !== 'finished') {
            return redirect()
                ->route('customer.history')
                ->withErrors(['feedback' => 'Feedback is only available for finished appointments.']);
        }

        $existingFeedback = Feedback::where('appointment_id', $appointment->id)->first();

        if ($existingFeedback) {
            return redirect()
                ->route('customer.history')
                ->withErrors(['feedback' => 'You already submitted feedback for this appointment.']);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Feedback::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'staff_id' => $appointment->staff_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        $this->createAuditLog(
            userId: $request->user()->id,
            action: 'submit_feedback',
            description: 'Customer submitted feedback for booking #BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT) . '.',
            request: $request
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Feedback Notification + Email
        |--------------------------------------------------------------------------
        | Purpose:
        | - Notifies admin that customer feedback was submitted.
        | - Notifies assigned staff that feedback was received.
        |--------------------------------------------------------------------------
        */
        $notificationService = app(NotificationService::class);

        $bookingCode = 'BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT);
        $mainService = $appointment->services->first()?->service_name ?? 'Beauty Service';

        $notificationService->notifyAdmins(
            'New Customer Feedback',
            $customer->full_name . ' submitted a ' . $validated['rating'] . '-star feedback for booking #' . $bookingCode . ' (' . $mainService . ').',
            route('admin.dashboard', [], false),
            'new_feedback'
        );

        $notificationService->notifyStaff(
            $appointment->staff,
            'New Feedback Received',
            $customer->full_name . ' submitted feedback for booking #' . $bookingCode . ' (' . $mainService . ').',
            route('staff.appointments', [], false),
            'staff_feedback'
        );

        return redirect()
            ->route('customer.history')
            ->with('success', 'Feedback submitted successfully.');
    }

    private function createAuditLog(
        int $userId,
        string $action,
        string $description,
        Request $request
    ): void {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Safe Audit Log Creation
        |--------------------------------------------------------------------------
        | Purpose:
        | - Saves audit log data only using columns that exist.
        |--------------------------------------------------------------------------
        */
        $data = [
            'user_type' => 'customer',
            'action' => $action,
            'description' => $description,
        ];

        if (Schema::hasColumn('audit_logs', 'user_id')) {
            $data['user_id'] = $userId;
        }

        if (Schema::hasColumn('audit_logs', 'module')) {
            $data['module'] = 'Customer Feedback';
        }

        if (Schema::hasColumn('audit_logs', 'ip_address')) {
            $data['ip_address'] = $request->ip();
        }

        if (Schema::hasColumn('audit_logs', 'user_agent')) {
            $data['user_agent'] = $request->userAgent();
        }

        AuditLog::create($data);
    }
}