<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffDayOff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Management Controller
|
| Purpose:
| - Displays staff records.
| - Adds and edits staff.
| - Assigns existing services from Service Management to staff.
| - Adds and removes staff day-off records.
| - Enables, disables, deletes, restores staff.
| - Records important actions in Audit Logs.
|
| Defense explanation:
| This supports business scheduling because staff skills are directly based
| on the official services created in Service Management. This prevents
| duplicate service lists and keeps service assignment consistent.
|--------------------------------------------------------------------------
*/

class StaffManagementController extends Controller
{
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
        |
        | Example:
        | - Admin adds "Nail Art" in Service Management.
        | - "Nail Art" automatically appears in Add Staff and Edit Staff.
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:staff,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $data = collect($validated)
                ->except(['service_ids'])
                ->toArray();

            $linkedUser = User::where('email', $validated['email'])->first();

            if ($linkedUser && Schema::hasColumn('staff', 'user_id')) {
                $data['user_id'] = $linkedUser->id;
            }

            $staff = Staff::create($data);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Staff service assignment.
            |--------------------------------------------------------------------------
            | Staff skills are assigned from the existing services created in
            | Service Management.
            |--------------------------------------------------------------------------
            */
            $staff->services()->sync($validated['service_ids'] ?? []);

            $this->syncStaffUserStatus($staff);

            $this->createAuditLog(
                action: 'create_staff',
                description: 'Admin created staff profile: ' . $staff->full_name
            );
        });

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff added successfully.');
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('staff', 'email')->ignore($staff->id),
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

            $linkedUser = User::where('email', $validated['email'])->first();

            if ($linkedUser && Schema::hasColumn('staff', 'user_id')) {
                $data['user_id'] = $linkedUser->id;
            }

            $staff->update($data);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Staff service sync.
            |--------------------------------------------------------------------------
            | The selected services are synced from the services table.
            | These services are created and managed only in Service Management.
            |--------------------------------------------------------------------------
            */
            $staff->services()->sync($validated['service_ids'] ?? []);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Staff day-off record.
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

            $this->syncStaffUserStatus($staff);

            $this->createAuditLog(
                action: 'update_staff',
                description: 'Admin updated staff profile: ' . $staff->full_name
            );
        });

        return redirect()
            ->route('admin.staff')
            ->with('success', 'Staff updated successfully.');
    }

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

    public function destroy(Staff $staff)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Soft delete only.
        |--------------------------------------------------------------------------
        | Staff is moved to Deleted Staff first and can be restored within 30 days.
        |--------------------------------------------------------------------------
        */
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

    private function deleteExpiredDayOffs(): void
    {
        StaffDayOff::whereDate('end_date', '<', today())->delete();
    }

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

    private function findStaffUser(Staff $staff): ?User
    {
        if ($staff->user_id) {
            return User::find($staff->user_id);
        }

        return User::where('email', $staff->email)->first();
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