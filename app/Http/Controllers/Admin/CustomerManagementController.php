<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This controller handles Admin Customer Management.
|
| Purpose:
| - Displays customer accounts.
| - Allows searching and filtering customers.
| - Shows customer profile and booking history.
| - Blocks and unblocks customer accounts with admin password.
| - Records actions in audit logs.
|
| Defense explanation:
| This improves security and accountability because account blocking requires
| admin password confirmation and is recorded in Audit Logs.
|--------------------------------------------------------------------------
*/

class CustomerManagementController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
        ];

        $query = Customer::with([
                'user',
                'blockedBy',
                'appointments' => function ($query) {
                    $query->with(['staff', 'services'])
                        ->latest('appointment_date')
                        ->latest('start_time');
                },
            ])
            ->withCount('appointments')
            ->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        $customers = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('status', 'active')->count(),
            'inactive' => Customer::where('status', 'inactive')->count(),
            'blocked' => Customer::where('status', 'blocked')->count(),
        ];

        return view('admin.customers', compact('customers', 'stats', 'filters'));
    }

    public function block(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'admin_password' => ['required', 'string'],
            'block_reason' => ['required', 'string', 'max:1000'],
        ]);

        if (!Hash::check($validated['admin_password'], auth()->user()->password)) {
            return redirect()
                ->route('admin.customers')
                ->withErrors(['admin_password' => 'Admin password is incorrect.']);
        }

        DB::transaction(function () use ($customer, $validated) {
            $customer->update([
                'status' => 'blocked',
                'block_reason' => $validated['block_reason'],
                'blocked_at' => now(),
                'blocked_by' => auth()->id(),
            ]);

            $user = $this->findCustomerUser($customer);

            if ($user) {
                $user->update([
                    'status' => 'blocked',
                ]);
            }

            $this->createAuditLog(
                action: 'block_customer_account',
                description: 'Admin blocked customer account: ' . $customer->full_name
            );
        });

        return redirect()
            ->route('admin.customers')
            ->with('success', 'Customer account blocked successfully.');
    }

    public function unblock(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'admin_password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['admin_password'], auth()->user()->password)) {
            return redirect()
                ->route('admin.customers')
                ->withErrors(['admin_password' => 'Admin password is incorrect.']);
        }

        DB::transaction(function () use ($customer) {
            $customer->update([
                'status' => 'active',
                'block_reason' => null,
                'blocked_at' => null,
                'blocked_by' => null,
            ]);

            $user = $this->findCustomerUser($customer);

            if ($user) {
                $user->update([
                    'status' => 'active',
                ]);
            }

            $this->createAuditLog(
                action: 'unblock_customer_account',
                description: 'Admin unblocked customer account: ' . $customer->full_name
            );
        });

        return redirect()
            ->route('admin.customers')
            ->with('success', 'Customer account unblocked successfully.');
    }

    private function findCustomerUser(Customer $customer): ?User
    {
        if ($customer->user_id) {
            return User::find($customer->user_id);
        }

        return User::where('email', $customer->email)->first();
    }

    private function createAuditLog(string $action, string $description): void
    {
        $data = [
            'user_type' => auth()->user()->role ?? 'admin',
            'action' => $action,
            'description' => $description,
        ];

        if (Schema::hasColumn('audit_logs', 'user_id')) {
            $data['user_id'] = auth()->id();
        }

        if (Schema::hasColumn('audit_logs', 'module')) {
            $data['module'] = 'Customer Management';
        }

        if (Schema::hasColumn('audit_logs', 'ip_address')) {
            $data['ip_address'] = request()->ip();
        }

        if (Schema::hasColumn('audit_logs', 'user_agent')) {
            $data['user_agent'] = request()->userAgent();
        }

        AuditLog::create($data);
    }
}