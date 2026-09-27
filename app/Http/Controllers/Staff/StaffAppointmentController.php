<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Staff;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Appointment Controller
|
| Purpose:
| - Loads staff dashboard data.
| - Loads staff assigned appointments.
| - Allows staff to update assigned appointment status.
| - Loads dynamic staff profile data.
| - Allows staff to update phone number and password.
| - Sends in-app and email notifications when staff updates booking status.
| - Loads customer feedback connected to assigned appointments.
|
| Defense explanation:
| This controller connects the staff account to the staff profile through
| the logged-in user's email address. Staff members can only view and update
| appointments assigned to them, which protects booking records from
| unauthorized access. Feedback is also loaded so staff can review customer
| satisfaction after completed services.
|--------------------------------------------------------------------------
*/

class StaffAppointmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Staff Profile Resolver
    |--------------------------------------------------------------------------
    | Purpose:
    | - Finds the staff profile connected to the logged-in staff user.
    | - The system matches the user email to the staff email.
    |--------------------------------------------------------------------------
    */
    private function getStaffProfile(Request $request): ?Staff
    {
        return Staff::with(['services', 'activeDayOffs'])
            ->where('email', $request->user()->email)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Staff Dashboard
    |--------------------------------------------------------------------------
    | Purpose:
    | - Shows dashboard counts and today's/upcoming appointments.
    |--------------------------------------------------------------------------
    */
    public function dashboard(Request $request)
    {
        $staffProfile = $this->getStaffProfile($request);

        $todayAppointments = collect();
        $upcomingAppointments = collect();

        $todayCount = 0;
        $pendingCount = 0;
        $ongoingCount = 0;
        $finishedThisWeek = 0;
        $assignedServicesCount = 0;
        $activeDayOffCount = 0;

        if ($staffProfile) {
            $todayAppointments = Appointment::with([
                    'customer',
                    'services',
                    'feedback',
                    'feedback.customer',
                    'followUpAppointment',
                    'followUpAppointment.services',
                    'followUpAppointment.staff',
                    'followUpAppointment.feedback',
                ])
                ->where('staff_id', $staffProfile->id)
                ->whereDate('appointment_date', now()->toDateString())
                ->orderBy('start_time')
                ->get();

            $upcomingAppointments = Appointment::with([
                    'customer',
                    'services',
                    'feedback',
                    'feedback.customer',
                    'followUpAppointment',
                    'followUpAppointment.services',
                    'followUpAppointment.staff',
                    'followUpAppointment.feedback',
                ])
                ->where('staff_id', $staffProfile->id)
                ->whereDate('appointment_date', '>', now()->toDateString())
                ->whereIn('status', Appointment::ACTIVE_STATUSES)
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->limit(5)
                ->get();

            $todayCount = $todayAppointments->count();

            $pendingCount = Appointment::where('staff_id', $staffProfile->id)
                ->whereIn('status', ['pending', 'Pending'])
                ->count();

            $ongoingCount = Appointment::where('staff_id', $staffProfile->id)
                ->whereIn('status', ['ongoing', 'Ongoing'])
                ->count();

            $finishedThisWeek = Appointment::where('staff_id', $staffProfile->id)
                ->whereIn('status', ['finished', 'Finished'])
                ->whereBetween('appointment_date', [
                    now()->startOfWeek()->toDateString(),
                    now()->endOfWeek()->toDateString(),
                ])
                ->count();

            $assignedServicesCount = $staffProfile->services->count();
            $activeDayOffCount = $staffProfile->activeDayOffs->count();
        }

        return view('staff.dashboard', [
            'staffProfile' => $staffProfile,
            'todayAppointments' => $todayAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'todayCount' => $todayCount,
            'pendingCount' => $pendingCount,
            'ongoingCount' => $ongoingCount,
            'finishedThisWeek' => $finishedThisWeek,
            'assignedServicesCount' => $assignedServicesCount,
            'activeDayOffCount' => $activeDayOffCount,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Staff Appointments Page
    |--------------------------------------------------------------------------
    | Purpose:
    | - Loads all appointments assigned to the logged-in staff member.
    | - Loads submitted feedback so staff can view customer rating/comment.
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $staffProfile = $this->getStaffProfile($request);

        $appointments = collect();

        if ($staffProfile) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Staff Appointment Query
            |--------------------------------------------------------------------------
            | Purpose:
            | - Loads assigned appointments only.
            | - Loads customer, services, follow-up data, status/cancellation data,
            |   and submitted customer feedback.
            |--------------------------------------------------------------------------
            */
            $appointments = Appointment::with([
                    'customer',
                    'services',

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT:
                    | Feedback Relationship
                    |--------------------------------------------------------------------------
                    | Required in Appointment model:
                    | public function feedback()
                    | {
                    |     return $this->hasOne(Feedback::class);
                    | }
                    |--------------------------------------------------------------------------
                    */
                    'feedback',
                    'feedback.customer',
                    'feedback.staff',

                    'followUpAppointment',
                    'followUpAppointment.services',
                    'followUpAppointment.staff',
                    'followUpAppointment.customer',
                    'followUpAppointment.feedback',

                    'statusUpdatedBy',
                    'cancelledBy',
                ])
                ->where('staff_id', $staffProfile->id)
                ->orderByDesc('appointment_date')
                ->orderByDesc('start_time')
                ->get();
        }

        $pendingAppointments = $appointments->filter(function ($appointment) {
            return strtolower(str_replace(' ', '-', $appointment->status)) === 'pending';
        });

        $ongoingAppointments = $appointments->filter(function ($appointment) {
            return strtolower(str_replace(' ', '-', $appointment->status)) === 'ongoing';
        });

        $finishedAppointments = $appointments->filter(function ($appointment) {
            return strtolower(str_replace(' ', '-', $appointment->status)) === 'finished';
        });

        $cancelledAppointments = $appointments->filter(function ($appointment) {
            return strtolower(str_replace(' ', '-', $appointment->status)) === 'cancelled';
        });

        $noShowAppointments = $appointments->filter(function ($appointment) {
            return strtolower(str_replace(' ', '-', $appointment->status)) === 'no-show';
        });

        $followUpAppointments = $appointments->filter(function ($appointment) {
            return $appointment->booking_type === 'followup';
        });

        return view('staff.appointments', [
            'staffProfile' => $staffProfile,
            'appointments' => $appointments,
            'pendingAppointments' => $pendingAppointments,
            'ongoingAppointments' => $ongoingAppointments,
            'finishedAppointments' => $finishedAppointments,
            'cancelledAppointments' => $cancelledAppointments,
            'noShowAppointments' => $noShowAppointments,
            'followUpAppointments' => $followUpAppointments,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Update Appointment Status
    |--------------------------------------------------------------------------
    | Purpose:
    | - Allows staff to update assigned bookings only.
    | - Allows status changes to ongoing, finished, or no-show.
    | - Prevents changing closed bookings.
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['ongoing', 'finished', 'no-show'])],
        ]);

        $staffProfile = $this->getStaffProfile($request);

        if (!$staffProfile) {
            return back()->withErrors([
                'staff' => 'Your staff profile was not found. Please contact the administrator.',
            ]);
        }

        if ((int) $appointment->staff_id !== (int) $staffProfile->id) {
            abort(403, 'You are not allowed to update this appointment.');
        }

        $currentStatus = strtolower(str_replace(' ', '-', $appointment->status));

        if (in_array($currentStatus, ['finished', 'cancelled', 'no-show'], true)) {
            return back()->withErrors([
                'status' => 'This appointment status can no longer be updated.',
            ]);
        }

        DB::transaction(function () use ($request, $appointment, $validated) {
            $appointment->update([
                'status' => $validated['status'],
                'status_updated_by' => $request->user()->id,
                'status_updated_at' => now(),
            ]);

            $this->createAuditLog(
                userId: $request->user()->id,
                userType: 'staff',
                action: 'update_booking_status',
                module: 'Staff Appointments',
                description: 'Staff updated booking #BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT) . ' to ' . $validated['status'],
                request: $request
            );
        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Staff Status Update Notification + Email
        |--------------------------------------------------------------------------
        | Purpose:
        | - Notifies customer when the booking status changes.
        | - Notifies admin for monitoring.
        | - Sends feedback notice when the appointment becomes finished.
        |--------------------------------------------------------------------------
        */
        $appointment->refresh();
        $appointment->load(['customer', 'staff', 'services']);

        $notificationService = app(NotificationService::class);

        $bookingCode = 'BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT);
        $newStatus = ucwords(str_replace('-', ' ', $validated['status']));
        $mainService = $appointment->services->first()?->service_name ?? 'Beauty Service';

        $notificationService->notifyCustomer(
            $appointment->customer,
            'Booking Status Updated',
            'Your booking #' . $bookingCode . ' for ' . $mainService . ' has been updated to ' . $newStatus . '.',
            route('customer.booking.details', $appointment->id, false),
            'booking_status_updated'
        );

        $notificationService->notifyAdmins(
            'Staff Updated Booking Status',
            $staffProfile->full_name . ' updated booking #' . $bookingCode . ' to ' . $newStatus . '.',
            route('admin.bookings', [
                'search' => $bookingCode,
            ], false),
            'staff_status_update'
        );

        if ($validated['status'] === 'finished') {
            $notificationService->notifyCustomer(
                $appointment->customer,
                'Feedback Now Available',
                'Your booking #' . $bookingCode . ' is finished. You may now submit feedback.',
                route('customer.feedback', $appointment->id, false),
                'feedback_available'
            );
        }

        return back()->with('success', 'Appointment status updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Staff Profile Page
    |--------------------------------------------------------------------------
    */
    public function profile(Request $request)
    {
        $staffProfile = $this->getStaffProfile($request);

        $totalAppointments = 0;
        $finishedAppointments = 0;
        $pendingAppointments = 0;

        if ($staffProfile) {
            $totalAppointments = Appointment::where('staff_id', $staffProfile->id)->count();

            $finishedAppointments = Appointment::where('staff_id', $staffProfile->id)
                ->whereIn('status', ['finished', 'Finished'])
                ->count();

            $pendingAppointments = Appointment::where('staff_id', $staffProfile->id)
                ->whereIn('status', ['pending', 'Pending'])
                ->count();
        }

        return view('staff.profile', [
            'staffProfile' => $staffProfile,
            'totalAppointments' => $totalAppointments,
            'finishedAppointments' => $finishedAppointments,
            'pendingAppointments' => $pendingAppointments,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Update Staff Profile
    |--------------------------------------------------------------------------
    */
    public function updateProfile(Request $request)
    {
        $staffProfile = $this->getStaffProfile($request);

        if (!$staffProfile) {
            return back()->withErrors([
                'staff' => 'Your staff profile was not found. Please contact the administrator.',
            ]);
        }

        $validated = $request->validate([
            'phone_number' => ['nullable', 'string', 'max:30'],
        ]);

        $staffProfile->update([
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        $this->createAuditLog(
            userId: $request->user()->id,
            userType: 'staff',
            action: 'update_staff_profile',
            module: 'Staff Profile',
            description: 'Staff updated own profile contact number.',
            request: $request
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Change Staff Password
    |--------------------------------------------------------------------------
    */
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
            userType: 'staff',
            action: 'change_staff_password',
            module: 'Staff Profile',
            description: 'Staff changed own account password.',
            request: $request
        );

        return back()->with('success', 'Password updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Safe Audit Log Creation
    |--------------------------------------------------------------------------
    | Purpose:
    | - Saves audit logs only using columns that exist in the database.
    | - Prevents errors if the audit_logs table structure is still being updated.
    |--------------------------------------------------------------------------
    */
    private function createAuditLog(
        int $userId,
        string $userType,
        string $action,
        string $module,
        string $description,
        Request $request
    ): void {
        $data = [
            'user_type' => $userType,
            'action' => $action,
            'description' => $description,
        ];

        if (Schema::hasColumn('audit_logs', 'user_id')) {
            $data['user_id'] = $userId;
        }

        if (Schema::hasColumn('audit_logs', 'module')) {
            $data['module'] = $module;
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