<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffActivationToken;
use App\Models\StaffDayOff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Management Controller
|--------------------------------------------------------------------------
| Purpose:
| - Displays staff records.
| - Allows Admin to create staff accounts.
| - Creates both users table login account and staff table profile.
| - Sends staff activation email so staff can set their own password.
| - Assigns existing services from Service Management to staff.
| - Adds and removes staff day-off records.
| - Enables, disables, deletes, restores staff.
| - Records important actions in Audit Logs.
|
| Defense explanation:
| Staff accounts are created securely. The Admin creates the staff profile,
| but the staff member sets their own password through an activation email.
| This avoids sharing manual passwords and keeps staff login connected to OTP.
|--------------------------------------------------------------------------
*/

class StaffManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Staff Management Page
    |--------------------------------------------------------------------------
    | Purpose:
    | - Shows staff list.
    | - Shows deleted staff.
    | - Loads available services from Service Management.
    | - Shows staff statistics.
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Automatic cleanup.
        |--------------------------------------------------------------------------
        | Past day-off records are removed automatically.
        | Deleted staff older than 30 days are permanently removed only if no
        | booking records are connected.
        |--------------------------------------------------------------------------
        */
        $this->deleteExpiredDayOffs();
        $this->deleteExpiredTrashedStaff();

        $filters = [
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
        ];

        $query = Staff::with(['user', 'services', 'activeDayOffs'])
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
            $query->where('status', $filters['status'] === 'active' ? 'Active' : 'Inactive');
        }

        $staffMembers = $query->paginate(10)->withQueryString();

        $deletedStaff = Staff::onlyTrashed()
            ->with(['services'])
            ->withCount('appointments')
            ->latest('deleted_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Service Management connection.
        |--------------------------------------------------------------------------
        | The staff skills/services checkbox list is automatically loaded from
        | the official services created in Service Management.
        |--------------------------------------------------------------------------
        */
        $services = Service::whereIn('status', ['Available', 'Unavailable', 'available', 'unavailable'])
            ->orderBy('service_name')
            ->get();

        $stats = [
            'total' => Staff::count(),
            'active' => Staff::where('status', 'Active')->count(),
            'inactive' => Staff::where('status', 'Inactive')->count(),
            'on_day_off' => Staff::whereHas('activeDayOffs')->count(),
        ];

        return view('admin.staff', compact(
            'staffMembers',
            'deletedStaff',
            'services',
            'stats',
            'filters'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Add New Staff
    |--------------------------------------------------------------------------
    | New real workflow:
    | 1. Admin enters staff name, email, phone, status, and services.
    | 2. System creates user login account with role staff.
    | 3. System creates staff profile connected through staff.user_id.
    | 4. System creates activation token.
    | 5. System sends activation email to staff.
    | 6. Staff opens email link and sets their own password.
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Email must be unique in both staff and users table.
            |--------------------------------------------------------------------------
            | Purpose:
            | - Prevents duplicate staff profile emails.
            | - Prevents duplicate login account emails.
            |--------------------------------------------------------------------------
            */
            'email' => [
                'required',
                'email',
                'max:150',
                'unique:staff,email',
                'unique:users,email',
            ],

            'phone_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Activation Token
        |--------------------------------------------------------------------------
        | Purpose:
        | - Plain token is sent through email link.
        | - Hashed token is stored in database.
        |--------------------------------------------------------------------------
        */
        $plainToken = Str::random(64);
        $activationUrl = null;
        $staffName = $validated['full_name'];
        $staffEmail = $validated['email'];

        DB::transaction(function () use ($validated, $plainToken, &$activationUrl) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Create Staff Login Account
            |--------------------------------------------------------------------------
            | Purpose:
            | - Creates staff account in users table.
            | - Account is inactive until staff sets password through email link.
            | - Random password is stored temporarily and cannot be used by staff.
            |--------------------------------------------------------------------------
            */
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(64)),
                'role' => 'staff',
                'status' => 'inactive',
                'email_verified_at' => null,
                'otp_code' => null,
                'otp_purpose' => null,
                'otp_expires_at' => null,
                'otp_verified_at' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Create Staff Profile
            |--------------------------------------------------------------------------
            | Purpose:
            | - Creates staff profile in staff table.
            | - Connects staff profile to users table using user_id.
            |--------------------------------------------------------------------------
            */
            $staff = Staff::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'status' => $validated['status'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Staff Service Assignment
            |--------------------------------------------------------------------------
            | Purpose:
            | - Staff skills are assigned from the existing services created in
            |   Service Management.
            |--------------------------------------------------------------------------
            */
            $staff->services()->sync($validated['service_ids'] ?? []);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Create Activation Token Record
            |--------------------------------------------------------------------------
            | Purpose:
            | - Allows staff to activate account and set password.
            | - Link expires after 3 days for security.
            |--------------------------------------------------------------------------
            */
            StaffActivationToken::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => now()->addDays(3),
                'used_at' => null,
            ]);

            $activationUrl = route('staff.activate.form', $plainToken);

            $this->createAuditLog(
                action: 'create_staff',
                description: 'Admin created staff account and activation email for: ' . $staff->full_name
            );
        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Send Staff Activation Email
        |--------------------------------------------------------------------------
        | Purpose:
        | - Staff receives a real activation email.
        | - Staff sets their own password.
        |--------------------------------------------------------------------------
        */
        $emailSent = $this->sendStaffActivationEmail(
            staffName: $staffName,
            staffEmail: $staffEmail,
            activationUrl: $activationUrl
        );

        if (!$emailSent) {
            return redirect()
                ->route('admin.staff')
                ->withErrors([
                    'staff_email' => 'Staff account was created, but activation email could not be sent. Please check mail configuration.',
                ]);
        }

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff added successfully. Activation email has been sent to the staff.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Update Staff
    |--------------------------------------------------------------------------
    | Purpose:
    | - Updates staff profile.
    | - Updates connected user account name/email/status.
    | - Syncs assigned services.
    | - Adds day-off schedule if provided.
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Staff $staff)
    {
        $linkedUser = $this->findStaffUser($staff);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],

            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('staff', 'email')->ignore($staff->id),
                Rule::unique('users', 'email')->ignore($linkedUser?->id),
            ],

            'phone_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],

            'off_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'off_end_date' => ['nullable', 'date', 'after_or_equal:off_start_date'],
            'off_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (
            (!empty($validated['off_start_date']) && empty($validated['off_end_date'])) ||
            (empty($validated['off_start_date']) && !empty($validated['off_end_date']))
        ) {
            return redirect()
                ->route('admin.staff')
                ->withErrors(['off_start_date' => 'Please provide both start date and end date for day off.']);
        }

        DB::transaction(function () use ($staff, $validated) {
            $data = collect($validated)
                ->except([
                    'service_ids',
                    'off_start_date',
                    'off_end_date',
                    'off_reason',
                ])
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Update Staff Profile
            |--------------------------------------------------------------------------
            */
            $staff->update($data);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Sync Staff User Account
            |--------------------------------------------------------------------------
            | Purpose:
            | - Keeps users table and staff table connected.
            | - Staff login email always matches staff profile email.
            |--------------------------------------------------------------------------
            */
            $user = $this->findStaffUser($staff);

            if ($user) {
                $user->update([
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'status' => $validated['status'] === 'Active' ? 'active' : 'inactive',
                ]);

                if (Schema::hasColumn('staff', 'user_id') && $staff->user_id !== $user->id) {
                    $staff->update([
                        'user_id' => $user->id,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Staff Service Sync
            |--------------------------------------------------------------------------
            */
            $staff->services()->sync($validated['service_ids'] ?? []);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Staff Day-Off Record
            |--------------------------------------------------------------------------
            | Customer Booking checks staff_day_offs before allowing a booking.
            |--------------------------------------------------------------------------
            */
            if (!empty($validated['off_start_date']) && !empty($validated['off_end_date'])) {
                StaffDayOff::create([
                    'staff_id' => $staff->id,
                    'start_date' => $validated['off_start_date'],
                    'end_date' => $validated['off_end_date'],
                    'reason' => $validated['off_reason'] ?? null,
                ]);
            }

            $this->createAuditLog(
                action: 'update_staff',
                description: 'Admin updated staff profile: ' . $staff->full_name
            );
        });

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Toggle Staff Status
    |--------------------------------------------------------------------------
    */
    public function toggleStatus(Staff $staff)
    {
        $newStatus = $staff->status === 'Active' ? 'Inactive' : 'Active';

        $staff->update([
            'status' => $newStatus,
        ]);

        $this->syncStaffUserStatus($staff);

        $this->createAuditLog(
            action: 'change_staff_status',
            description: 'Admin changed staff status: ' . $staff->full_name . ' to ' . $newStatus
        );

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff status updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Soft Delete Staff
    |--------------------------------------------------------------------------
    | Purpose:
    | - Staff is moved to Deleted Staff first.
    | - Staff can be restored within 30 days.
    | - Connected user account is deactivated.
    |--------------------------------------------------------------------------
    */
    public function destroy(Staff $staff)
    {
        $staffName = $staff->full_name;

        $staff->update([
            'status' => 'Inactive',
        ]);

        $this->syncStaffUserStatus($staff);

        $staff->delete();

        $this->createAuditLog(
            action: 'delete_staff',
            description: 'Admin moved staff to Deleted Staff: ' . $staffName
        );

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff moved to Deleted Staff. It can be restored within 30 days.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Restore Staff
    |--------------------------------------------------------------------------
    */
    public function restore(int $id)
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);

        $staff->restore();

        $staff->update([
            'status' => 'Active',
        ]);

        $this->syncStaffUserStatus($staff);

        $this->createAuditLog(
            action: 'restore_staff',
            description: 'Admin restored staff: ' . $staff->full_name
        );

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff restored successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Permanent Delete Staff
    |--------------------------------------------------------------------------
    | Purpose:
    | - Only allowed if staff has no connected appointment records.
    |--------------------------------------------------------------------------
    */
    public function forceDelete(int $id)
    {
        $staff = Staff::onlyTrashed()
            ->withCount('appointments')
            ->findOrFail($id);

        if ($staff->appointments_count > 0) {
            return redirect()
                ->route('admin.staff')
                ->withErrors([
                    'staff_delete' => 'This staff cannot be permanently deleted because booking records are connected to this staff.',
                ]);
        }

        $staffName = $staff->full_name;

        $staff->services()->detach();
        $staff->dayOffs()->delete();
        $staff->forceDelete();

        $this->createAuditLog(
            action: 'permanent_delete_staff',
            description: 'Admin permanently deleted staff: ' . $staffName
        );

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff permanently deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Remove Staff Day-Off
    |--------------------------------------------------------------------------
    */
    public function destroyDayOff(StaffDayOff $dayOff)
    {
        $staffName = $dayOff->staff->full_name ?? 'Unknown staff';

        $dayOff->delete();

        $this->createAuditLog(
            action: 'remove_staff_day_off',
            description: 'Admin removed day off record for: ' . $staffName
        );

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff day off removed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Delete Expired Day-Offs
    |--------------------------------------------------------------------------
    */
    private function deleteExpiredDayOffs(): void
    {
        StaffDayOff::whereDate('end_date', '<', today())->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Delete Expired Trashed Staff
    |--------------------------------------------------------------------------
    */
    private function deleteExpiredTrashedStaff(): void
    {
        $expiredStaff = Staff::onlyTrashed()
            ->withCount('appointments')
            ->where('deleted_at', '<=', now()->subDays(30))
            ->get();

        foreach ($expiredStaff as $staff) {
            if ($staff->appointments_count === 0) {
                $staff->services()->detach();
                $staff->dayOffs()->delete();
                $staff->forceDelete();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Sync Staff User Status
    |--------------------------------------------------------------------------
    | Purpose:
    | - Staff Active = user active
    | - Staff Inactive = user inactive
    |--------------------------------------------------------------------------
    */
    private function syncStaffUserStatus(Staff $staff): void
    {
        $user = $this->findStaffUser($staff);

        if (!$user) {
            return;
        }

        $user->update([
            'status' => $staff->status === 'Active' ? 'active' : 'inactive',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Find Staff User
    |--------------------------------------------------------------------------
    | Purpose:
    | - Finds linked user through user_id first.
    | - Falls back to email if user_id is missing.
    |--------------------------------------------------------------------------
    */
    private function findStaffUser(Staff $staff): ?User
    {
        if ($staff->user_id) {
            return User::find($staff->user_id);
        }

        return User::where('email', $staff->email)->first();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Send Staff Activation Email
    |--------------------------------------------------------------------------
    */
    private function sendStaffActivationEmail(
        string $staffName,
        string $staffEmail,
        string $activationUrl
    ): bool {
        try {
            Mail::raw(
                "Hello {$staffName},\n\n" .
                "Your staff account for Nail Studio & Beauty has been created.\n\n" .
                "Please activate your account and set your password using this link:\n\n" .
                "{$activationUrl}\n\n" .
                "This link will expire in 3 days.\n\n" .
                "After activation, you can login using your email, password, and Email OTP.\n\n" .
                "If you did not expect this email, please ignore it.",
                function ($message) use ($staffEmail) {
                    $message->to($staffEmail)
                        ->subject('Nail Studio & Beauty - Staff Account Activation');
                }
            );

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Create Audit Log
    |--------------------------------------------------------------------------
    */
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
            $data['module'] = 'Staff Management';
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