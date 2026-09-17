<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Profile Controller
|
| Purpose:
| - Shows logged-in customer profile.
| - Allows customer to update name, phone number, and address.
| - Allows customer to change password securely.
|
| Defense explanation:
| This supports account management while keeping email and role protected
| from customer-side editing.
|--------------------------------------------------------------------------
*/

class CustomerProfileController extends Controller
{
    private function getCustomer(Request $request): ?Customer
    {
        return Customer::where('user_id', $request->user()->id)
            ->orWhere('email', $request->user()->email)
            ->first();
    }

    public function show(Request $request)
    {
        $customer = $this->getCustomer($request);

        $appointments = collect();
        $activeBookings = collect();
        $finishedBookings = collect();

        if ($customer) {
            $appointments = Appointment::where('customer_id', $customer->id)->get();

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
        }

        $bookingLimit = $finishedBookings->count() >= 1 ? 2 : 1;

        return view('customer.profile', [
            'customer' => $customer,
            'appointments' => $appointments,
            'activeBookings' => $activeBookings,
            'finishedBookings' => $finishedBookings,
            'bookingLimit' => $bookingLimit,
        ]);
    }

    public function update(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (!$customer) {
            return back()->withErrors([
                'profile' => 'Customer profile was not found. Please contact the administrator.',
            ]);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $customer, $validated) {
            $customer->update([
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            $request->user()->update([
                'name' => $validated['full_name'],
            ]);

            $this->createAuditLog(
                userId: $request->user()->id,
                action: 'update_customer_profile',
                description: 'Customer updated own profile information.',
                request: $request
            );
        });

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $request->user()->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->createAuditLog(
            userId: $request->user()->id,
            action: 'change_customer_password',
            description: 'Customer changed own account password.',
            request: $request
        );

        return back()->with('success', 'Password updated successfully.');
    }

    private function createAuditLog(
        int $userId,
        string $action,
        string $description,
        Request $request
    ): void {
        $data = [
            'user_type' => 'customer',
            'action' => $action,
            'description' => $description,
        ];

        if (Schema::hasColumn('audit_logs', 'user_id')) {
            $data['user_id'] = $userId;
        }

        if (Schema::hasColumn('audit_logs', 'module')) {
            $data['module'] = 'Customer Profile';
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