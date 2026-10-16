<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffActivationToken;
use App\Models\StaffDayOff;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Staff Management Controller
|--------------------------------------------------------------------------
| Features:
| - Manage staff accounts, assigned services and activation emails
| - Update staff status, profiles and service assignments
| - Delete, restore and permanently delete staff
| - Manage staff day-off records
| - Automatically reassign bookings affected by new staff day-offs
| - Mark appointments as rescheduling_required when necessary
| - Preserve original appointment details
| - Send customer, staff and admin notifications
| - Record important changes in Audit Logs
|
| Emergency Scheduling:
| A new staff day-off triggers a review of future active appointments
| The system first attempts to find an available qualified replacement
| If none exists, the appointment becomes rescheduling_required
|
| Important:
| - Customer-selected rescheduling is implemented separately
| - Studio-wide closures are implemented separately
|--------------------------------------------------------------------------
*/

class StaffManagementController extends Controller
{
    private const TIMEZONE = 'Asia/Kuala_Lumpur';

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Staff Management Page
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $this->deleteExpiredDayOffs();
        $this->deleteExpiredTrashedStaff();

        $filters = [
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
        ];

        $query = Staff::with([
            'user',
            'services',
            'activeDayOffs',
        ])
            ->withCount('appointments')
            ->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($filters['status'] !== 'all') {
            $query->where(
                'status',
                $filters['status'] === 'active'
                    ? 'Active'
                    : 'Inactive'
            );
        }

        $staffMembers = $query
            ->paginate(10)
            ->withQueryString();

        $deletedStaff = Staff::onlyTrashed()
            ->with('services')
            ->withCount('appointments')
            ->latest('deleted_at')
            ->get();

        $services = Service::whereIn('status', [
            'Available',
            'Unavailable',
            'available',
            'unavailable',
        ])
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
    | NS BEAUTY COMMENT: Create Staff Account
    |--------------------------------------------------------------------------
    | Admin creates the account
    | Staff activates their account through an email link
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                'unique:staff,email',
                'unique:users,email',
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:30',
            ],
            'status' => [
                'required',
                Rule::in(['Active', 'Inactive']),
            ],
            'service_ids' => [
                'nullable',
                'array',
            ],
            'service_ids.*' => [
                'integer',
                'exists:services,id',
            ],
        ]);

        $plainToken = Str::random(64);
        $activationUrl = null;

        DB::transaction(function () use (
            $validated,
            $plainToken,
            &$activationUrl
        ) {
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

            $staff = Staff::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'status' => $validated['status'],
            ]);

            $staff->services()->sync(
                $validated['service_ids'] ?? []
            );

            StaffActivationToken::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => now()->addDays(3),
                'used_at' => null,
            ]);

            $activationUrl = route(
                'staff.activate.form',
                $plainToken
            );

            $this->createAuditLog(
                'create_staff',
                'Admin created staff account for ' . $staff->full_name
            );
        });

        $emailSent = $this->sendStaffActivationEmail(
            $validated['full_name'],
            $validated['email'],
            $activationUrl
        );

        if (!$emailSent) {
            return redirect()
                ->route('admin.staff')
                ->withErrors([
                    'staff_email' =>
                        'Staff created but activation email could not be sent',
                ]);
        }

        return redirect()
            ->route('admin.staff')
            ->with(
                'success',
                'Staff added successfully — activation email sent'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Update Staff and Record Day Off
    |--------------------------------------------------------------------------
    | When a new day off is saved:
    | 1 - Validate the date range
    | 2 - Save staff information and services
    | 3 - Record the day off
    | 4 - Find affected upcoming appointments
    | 5 - Attempt automatic staff reassignment
    | 6 - Save rescheduling status if no replacement exists
    | 7 - Send notifications after the transaction commits
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Staff $staff)
    {
        $linkedUser = $this->findStaffUser($staff);
        $today = Carbon::today(self::TIMEZONE)->toDateString();

        $validated = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('staff', 'email')->ignore($staff->id),
                Rule::unique('users', 'email')->ignore($linkedUser?->id),
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:30',
            ],
            'status' => [
                'required',
                Rule::in(['Active', 'Inactive']),
            ],
            'service_ids' => [
                'nullable',
                'array',
            ],
            'service_ids.*' => [
                'integer',
                'exists:services,id',
            ],
            'off_start_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:' . $today,
            ],
            'off_end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:off_start_date',
            ],
            'off_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $start = $validated['off_start_date'] ?? null;
        $end = $validated['off_end_date'] ?? null;

        if (($start && !$end) || (!$start && $end)) {
            return redirect()
                ->route('admin.staff')
                ->withErrors([
                    'off_start_date' =>
                        'Provide both the start and end dates for the day off',
                ]);
        }

        $outcomes = [];

        DB::transaction(function () use (
            $staff,
            $validated,
            $start,
            $end,
            &$outcomes
        ) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Lock Staff Record
            |--------------------------------------------------------------------------
            */

            $lockedStaff = Staff::whereKey($staff->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedStaff->update([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone_number' =>
                    $validated['phone_number'] ?? null,
                'status' => $validated['status'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Update Linked User
            |--------------------------------------------------------------------------
            */

            $user = $this->findStaffUser($lockedStaff);

            if ($user) {
                $user->update([
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'status' =>
                        $validated['status'] === 'Active'
                            ? 'active'
                            : 'inactive',
                ]);

                if (
                    Schema::hasColumn('staff', 'user_id') &&
                    $lockedStaff->user_id !== $user->id
                ) {
                    $lockedStaff->update([
                        'user_id' => $user->id,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Update Staff Services
            |--------------------------------------------------------------------------
            */

            $lockedStaff->services()->sync(
                $validated['service_ids'] ?? []
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Create Day Off
            |--------------------------------------------------------------------------
            */

            if ($start && $end) {
                StaffDayOff::create([
                    'staff_id' => $lockedStaff->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'reason' => $validated['off_reason'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Automatically Review Affected Bookings
                |--------------------------------------------------------------------------
                */

                $outcomes = $this->reassignAffectedAppointments(
                    $lockedStaff,
                    $start,
                    $end,
                    $validated['off_reason'] ?? null
                );
            }

            $this->createAuditLog(
                'update_staff',
                'Admin updated staff profile for ' .
                $lockedStaff->full_name
            );
        }, 3);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Send Notifications After Commit
        |--------------------------------------------------------------------------
        */

        $this->sendEmergencyNotifications($outcomes);

        $reassigned = collect($outcomes)
            ->where('outcome', 'reassigned')
            ->count();

        $rescheduling = collect($outcomes)
            ->where('outcome', 'reschedule')
            ->count();

        return redirect()
            ->route('admin.staff')
            ->with(
                'success',
                'Staff updated successfully — ' .
                $reassigned . ' bookings reassigned and ' .
                $rescheduling . ' require rescheduling'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Emergency Staff Reassignment
    |--------------------------------------------------------------------------
    | Only future appointments are processed
    | Finished, cancelled and no-show bookings are excluded
    |
    | The appointment ID, selected services and total price are preserved
    |--------------------------------------------------------------------------
    */

    private function reassignAffectedAppointments(
        Staff $absentStaff,
        string $startDate,
        string $endDate,
        ?string $dayOffReason = null
    ): array {
        $now = Carbon::now(self::TIMEZONE);
        $results = [];

        $appointments = Appointment::with([
            'customer',
            'services',
        ])
            ->where('staff_id', $absentStaff->id)
            ->whereBetween(
                'appointment_date',
                [$startDate, $endDate]
            )
            ->whereIn(
                'status',
                Appointment::ACTIVE_STATUSES
            )
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->lockForUpdate()
            ->get();

        foreach ($appointments as $appointment) {
            $date = Carbon::parse(
                $appointment->appointment_date
            )->format('Y-m-d');

            $startTime = substr(
                (string) $appointment->start_time,
                0,
                5
            );

            $appointmentStart = Carbon::createFromFormat(
                '!Y-m-d H:i',
                $date . ' ' . $startTime,
                self::TIMEZONE
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Do Not Change Past Appointments
            |--------------------------------------------------------------------------
            */

            if ($appointmentStart->lessThanOrEqualTo($now)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Find an Available Replacement
            |--------------------------------------------------------------------------
            */

            $replacement = $this->findReplacementStaff(
                $absentStaff,
                $appointment
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Preserve Original Details
            |--------------------------------------------------------------------------
            | Original fields are set only when empty
            | Existing history is never overwritten
            |--------------------------------------------------------------------------
            */

            $originalData = [
                'original_appointment_date' =>
                    $appointment->original_appointment_date
                        ?? $date,

                'original_start_time' =>
                    $appointment->original_start_time
                        ?? $appointment->start_time,

                'original_staff_id' =>
                    $appointment->original_staff_id
                        ?? $appointment->staff_id,
            ];

            if ($replacement) {
                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Automatic Beautician Reassignment
                |--------------------------------------------------------------------------
                | Keep the existing appointment date and time
                | Only change the assigned beautician
                |--------------------------------------------------------------------------
                */

                $appointment->update(array_merge(
                    $originalData,
                    [
                        'staff_id' => $replacement->id,
                    ]
                ));

                $this->createAuditLog(
                    'emergency_staff_reassigned',
                    'Booking ' .
                    $this->bookingCode($appointment->id) .
                    ' reassigned from ' .
                    $absentStaff->full_name .
                    ' to ' .
                    $replacement->full_name .
                    ' due to staff day off'
                );
            } else {
                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: No Qualified Replacement Found
                |--------------------------------------------------------------------------
                | Update appointment status in the database
                | Preserve original booking information
                | Do not cancel the appointment or mark it no-show
                |--------------------------------------------------------------------------
                */

                $reason =
                    'Assigned beautician unavailable due to staff day off';

                if ($dayOffReason && trim($dayOffReason) !== '') {
                    $reason .= ' — ' . trim($dayOffReason);
                }

                $reason = Str::limit($reason, 1000, '');

                $appointment->update(array_merge(
                    $originalData,
                    [
                        'status' => 'rescheduling_required',
                        'rescheduling_reason' => $reason,
                        'rescheduling_requested_at' => $now,
                        'status_updated_by' => auth()->id(),
                        'status_updated_at' => $now,
                    ]
                ));

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Record Rescheduling Requirement
                |--------------------------------------------------------------------------
                */

                $this->createAuditLog(
                    'emergency_rescheduling_needed',
                    'Booking ' .
                    $this->bookingCode($appointment->id) .
                    ' marked rescheduling_required because ' .
                    'no qualified available replacement was found'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Store Notification Outcome
            |--------------------------------------------------------------------------
            | Notifications are sent after the database transaction
            |--------------------------------------------------------------------------
            */

            $results[] = [
                'appointment_id' => $appointment->id,
                'outcome' =>
                    $replacement ? 'reassigned' : 'reschedule',
                'date' => $date,
                'time' => $startTime,
                'replacement_name' => $replacement?->full_name,
            ];
        }

        return $results;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Find Qualified Available Beautician
    |--------------------------------------------------------------------------
    | Requirements:
    | - Staff must be active
    | - Staff must offer every service in the appointment
    | - Staff must not be on day off
    | - Staff must not have an overlapping active appointment
    |
    | All checks must pass before automatic reassignment
    |--------------------------------------------------------------------------
    */

    private function findReplacementStaff(
        Staff $absentStaff,
        Appointment $appointment
    ): ?Staff {
        $date = Carbon::parse(
            $appointment->appointment_date
        )->format('Y-m-d');

        $serviceIds = $appointment->services
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Avoid Assigning Unqualified Staff
        |--------------------------------------------------------------------------
        */

        if ($serviceIds->isEmpty()) {
            return null;
        }

        if (!Schema::hasTable('staff_services')) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Determine Booking End Time
        |--------------------------------------------------------------------------
        | Fallback to recorded service durations when needed
        |--------------------------------------------------------------------------
        */

        $endTime = $appointment->end_time;

        if (!$endTime) {
            $duration = $appointment->services->sum(
                function ($service) {
                    return (int) (
                        $service->pivot->duration
                        ?? $service->duration
                        ?? 0
                    );
                }
            );

            if ($duration <= 0) {
                return null;
            }

            $endTime = Carbon::parse(
                $date . ' ' . $appointment->start_time,
                self::TIMEZONE
            )
                ->addMinutes($duration)
                ->format('H:i:s');
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Retrieve Potential Replacement Staff
        |--------------------------------------------------------------------------
        */

        $candidates = Staff::with('services')
            ->where('id', '!=', $absentStaff->id)
            ->whereIn('status', ['Active', 'active'])
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($candidates as $candidate) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Verify Every Required Service
            |--------------------------------------------------------------------------
            */

            $assignedIds = $candidate->services
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            if ($serviceIds->diff($assignedIds)->isNotEmpty()) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Check Staff Day Off
            |--------------------------------------------------------------------------
            */

            $onDayOff = StaffDayOff::where(
                'staff_id',
                $candidate->id
            )
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();

            if ($onDayOff) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Check Appointment Conflicts
            |--------------------------------------------------------------------------
            | Standard overlap rule:
            |
            | Existing start < requested end
            | AND
            | Existing end > requested start
            |--------------------------------------------------------------------------
            */

            $hasConflict = Appointment::where(
                'staff_id',
                $candidate->id
            )
                ->whereDate('appointment_date', $date)
                ->whereIn(
                    'status',
                    Appointment::ACTIVE_STATUSES
                )
                ->where(
                    'start_time',
                    '<',
                    $endTime
                )
                ->where(
                    'end_time',
                    '>',
                    $appointment->start_time
                )
                ->exists();

            if ($hasConflict) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Qualified Replacement Found
            |--------------------------------------------------------------------------
            */

            return $candidate;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Emergency Scheduling Notifications
    |--------------------------------------------------------------------------
    | NotificationService handles:
    | - In-app notification creation
    | - Email delivery attempts
    |
    | Each recipient is processed independently so one notification
    | failure does not prevent attempting notifications to others
    |--------------------------------------------------------------------------
    */

    private function sendEmergencyNotifications(array $outcomes): void
    {
        if (empty($outcomes)) {
            return;
        }

        $service = app(NotificationService::class);

        foreach ($outcomes as $outcome) {
            $appointment = Appointment::with([
                'customer',
                'staff',
            ])->find($outcome['appointment_id']);

            if (!$appointment) {
                continue;
            }

            $bookingCode = $this->bookingCode($appointment->id);

            $dateTime =
                $outcome['date'] .
                ' at ' .
                $outcome['time'] .
                ' MYT';

            $customerLink = route(
                'customer.booking.details',
                $appointment->id,
                false
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Notify Customer
            |--------------------------------------------------------------------------
            */

            if ($appointment->customer) {
                try {
                    if ($outcome['outcome'] === 'reassigned') {
                        $service->notifyCustomer(
                            $appointment->customer,
                            'Your Beautician Has Changed',
                            'Your booking ' .
                            $bookingCode .
                            ' on ' .
                            $dateTime .
                            ' has been reassigned to ' .
                            $outcome['replacement_name'] .
                            ' due to staff unavailability — your appointment date and time remain unchanged',
                            $customerLink,
                            'staff_reassigned',
                            true
                        );
                    } else {
                        $service->notifyCustomer(
                            $appointment->customer,
                            'Appointment Rescheduling Required',
                            'Your booking ' .
                            $bookingCode .
                            ' on ' .
                            $dateTime .
                            ' is affected by a staff emergency and no qualified replacement is available — please contact the studio to arrange a new schedule',
                            $customerLink,
                            'rescheduling_required',
                            true
                        );
                    }
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Notify Replacement Beautician
            |--------------------------------------------------------------------------
            */

            if (
                $outcome['outcome'] === 'reassigned' &&
                $appointment->staff
            ) {
                try {
                    $service->notifyStaff(
                        $appointment->staff,
                        'Emergency Appointment Assignment',
                        'Booking ' .
                        $bookingCode .
                        ' on ' .
                        $dateTime .
                        ' has been assigned to you',
                        route('staff.appointments', [], false),
                        'staff_reassigned',
                        true
                    );
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Notify Administrators
            |--------------------------------------------------------------------------
            */

            try {
                $service->notifyAdmins(
                    $outcome['outcome'] === 'reassigned'
                        ? 'Emergency Staff Reassignment'
                        : 'Appointment Rescheduling Required',

                    'Booking ' .
                    $bookingCode .
                    (
                        $outcome['outcome'] === 'reassigned'
                            ? ' reassigned to ' .
                                $outcome['replacement_name']
                            : ' requires customer rescheduling'
                    ),

                    route('admin.bookings', [], false),
                    'emergency_schedule_change',
                    true
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking Code Helper
    |--------------------------------------------------------------------------
    */

    private function bookingCode(int $id): string
    {
        return 'BK-' . str_pad(
            (string) $id,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Toggle Staff Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(Staff $staff)
    {
        $newStatus =
            $staff->status === 'Active'
                ? 'Inactive'
                : 'Active';

        DB::transaction(function () use ($staff, $newStatus) {
            $staff->update([
                'status' => $newStatus,
            ]);

            $this->syncStaffUserStatus($staff);

            $this->createAuditLog(
                'change_staff_status',
                'Admin changed staff status for ' .
                $staff->full_name .
                ' to ' .
                $newStatus
            );
        });

        return redirect()
            ->route('admin.staff')
            ->with(
                'success',
                'Staff status updated successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Soft Delete Staff
    |--------------------------------------------------------------------------
    */

    public function destroy(Staff $staff)
    {
        $staffName = $staff->full_name;

        DB::transaction(function () use ($staff, $staffName) {
            $staff->update([
                'status' => 'Inactive',
            ]);

            $this->syncStaffUserStatus($staff);

            $staff->delete();

            $this->createAuditLog(
                'delete_staff',
                'Admin moved staff to Deleted Staff: ' .
                $staffName
            );
        });

        return redirect()
            ->route('admin.staff')
            ->with(
                'success',
                'Staff moved to Deleted Staff — restore within 30 days'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Restore Staff
    |--------------------------------------------------------------------------
    */

    public function restore(int $id)
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($staff) {
            $staff->restore();

            $staff->update([
                'status' => 'Active',
            ]);

            $this->syncStaffUserStatus($staff);

            $this->createAuditLog(
                'restore_staff',
                'Admin restored staff: ' .
                $staff->full_name
            );
        });

        return redirect()
            ->route('admin.staff')
            ->with(
                'success',
                'Staff restored successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Permanently Delete Staff
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
                    'staff_delete' =>
                        'This staff cannot be permanently deleted while linked booking records exist',
                ]);
        }

        $staffName = $staff->full_name;

        DB::transaction(function () use ($staff, $staffName) {
            $staff->services()->detach();
            $staff->dayOffs()->delete();
            $staff->forceDelete();

            $this->createAuditLog(
                'permanent_delete_staff',
                'Admin permanently deleted staff: ' .
                $staffName
            );
        });

        return redirect()
            ->route('admin.staff')
            ->with(
                'success',
                'Staff permanently deleted'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Remove Staff Day Off
    |--------------------------------------------------------------------------
    */

    public function destroyDayOff(StaffDayOff $dayOff)
    {
        $staffName =
            $dayOff->staff?->full_name ?? 'Unknown staff';

        $dayOff->delete();

        $this->createAuditLog(
            'remove_staff_day_off',
            'Admin removed day off for ' .
            $staffName
        );

        return redirect()
            ->route('admin.staff')
            ->with(
                'success',
                'Staff day off removed successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Delete Expired Day-Off Records
    |--------------------------------------------------------------------------
    */

    private function deleteExpiredDayOffs(): void
    {
        StaffDayOff::whereDate(
            'end_date',
            '<',
            Carbon::today(self::TIMEZONE)->toDateString()
        )->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Clean Up Expired Deleted Staff
    |--------------------------------------------------------------------------
    | Staff with booking history must not be permanently deleted
    |--------------------------------------------------------------------------
    */

    private function deleteExpiredTrashedStaff(): void
    {
        $expiredStaff = Staff::onlyTrashed()
            ->withCount('appointments')
            ->where(
                'deleted_at',
                '<=',
                now()->subDays(30)
            )
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
    | NS BEAUTY COMMENT: Synchronize Staff Login Account
    |--------------------------------------------------------------------------
    */

    private function syncStaffUserStatus(Staff $staff): void
    {
        $user = $this->findStaffUser($staff);

        if (!$user) {
            return;
        }

        $user->update([
            'status' =>
                $staff->status === 'Active'
                    ? 'active'
                    : 'inactive',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Find Linked Staff User
    |--------------------------------------------------------------------------
    */

    private function findStaffUser(Staff $staff): ?User
    {
        if ($staff->user_id) {
            return User::find($staff->user_id);
        }

        return User::where(
            'email',
            $staff->email
        )->first();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Send Staff Activation Email
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
                "Your staff account for Nail Studio & Beauty has been created\n\n" .
                "Activate your account and set your password using the link below\n\n" .
                "{$activationUrl}\n\n" .
                "The activation link expires in 3 days\n\n" .
                "After activation, sign in using your email, password and OTP\n\n" .
                "If you did not expect this email, you may ignore it",
                function ($message) use ($staffEmail) {
                    $message
                        ->to($staffEmail)
                        ->subject(
                            'Nail Studio & Beauty - Staff Account Activation'
                        );
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
    | NS BEAUTY COMMENT: Audit Logging
    |--------------------------------------------------------------------------
    | Preserves compatibility with your existing audit_logs table
    |--------------------------------------------------------------------------
    */

    private function createAuditLog(
        string $action,
        string $description
    ): void {
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
