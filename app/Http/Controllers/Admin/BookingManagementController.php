<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Admin Booking Management Controller
|
| Purpose:
| - Loads all regular and backjob/follow-up bookings for Admin.
| - Allows Admin to filter bookings by status, type, date, and search keyword.
| - Allows Admin to cancel bookings with password confirmation.
| - Loads customer feedback connected to finished bookings.
|
| Defense explanation:
| This controller supports appointment monitoring by allowing Admin to view
| complete booking details, cancellation records, follow-up relationships,
| and submitted customer feedback in one centralized booking management page.
|--------------------------------------------------------------------------
*/

class BookingManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Booking Management Page
    |--------------------------------------------------------------------------
    | Purpose:
    | - Loads all appointment records.
    | - Loads related customer, staff, services, status updater, cancellation user,
    |   follow-up details, and submitted feedback.
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Admin Booking Management Query
        |--------------------------------------------------------------------------
        | Purpose:
        | - Loads all bookings for admin monitoring.
        | - Loads feedback so Admin can see customer rating/comment directly
        |   inside Booking Management.
        |--------------------------------------------------------------------------
        */
        $query = Appointment::with([
            'customer',
            'staff',
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

            'cancelledBy',
            'statusUpdatedBy',

            'followUpAppointment',
            'followUpAppointment.services',
            'followUpAppointment.staff',
            'followUpAppointment.customer',
            'followUpAppointment.feedback',
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Status Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Booking Type Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('booking_type') && $request->booking_type !== 'all') {
            $query->where('booking_type', $request->booking_type);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Date Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Search Filter
        |--------------------------------------------------------------------------
        | Purpose:
        | - Allows Admin to search by booking ID, customer, staff, services,
        |   follow-up reason, and feedback content.
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('follow_up_from', 'like', '%' . $search . '%')
                    ->orWhere('follow_up_reason', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('full_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('staff', function ($staffQuery) use ($search) {
                        $staffQuery->where('full_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('services', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('service_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('followUpAppointment.services', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('service_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('feedback', function ($feedbackQuery) use ($search) {
                        $feedbackQuery->where('comment', 'like', '%' . $search . '%')
                            ->orWhere('rating', $search);
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Paginated Booking List
        |--------------------------------------------------------------------------
        */
        $bookings = $query
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Booking Statistics
        |--------------------------------------------------------------------------
        */
        $stats = [
            'total' => Appointment::count(),
            'regular' => Appointment::where('booking_type', 'regular')->count(),
            'followup' => Appointment::where('booking_type', 'followup')->count(),
            'pending' => Appointment::whereIn('status', ['pending', 'Pending'])->count(),
            'ongoing' => Appointment::whereIn('status', ['ongoing', 'Ongoing'])->count(),
            'finished' => Appointment::whereIn('status', ['finished', 'Finished'])->count(),
            'cancelled' => Appointment::whereIn('status', ['cancelled', 'Cancelled'])->count(),
            'no_show' => Appointment::whereIn('status', ['no-show', 'No Show'])->count(),
        ];

        return view('admin.bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
            'filters' => [
                'status' => $request->status ?? 'all',
                'booking_type' => $request->booking_type ?? 'all',
                'date' => $request->date,
                'search' => $request->search,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Cancel Booking
    |--------------------------------------------------------------------------
    | Purpose:
    | - Allows Admin to cancel active bookings only.
    | - Requires Admin password before cancellation.
    | - Saves cancellation reason, cancelled by, and cancellation timestamp.
    | - Saves cancellation activity in Audit Logs.
    |--------------------------------------------------------------------------
    */
    public function cancel(Request $request, Appointment $booking)
    {
        $validated = $request->validate([
            'admin_password' => ['required', 'string'],
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $admin = $request->user();

        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Only admins can cancel bookings from this page.');
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Admin Password Confirmation
        |--------------------------------------------------------------------------
        | Purpose:
        | - Prevents unauthorized cancellation if Admin leaves account open.
        |--------------------------------------------------------------------------
        */
        if (!Hash::check($validated['admin_password'], $admin->password)) {
            return back()
                ->withErrors([
                    'admin_password' => 'Incorrect admin password. Booking cancellation is not allowed.',
                ])
                ->withInput();
        }

        $currentStatus = strtolower(str_replace(' ', '-', $booking->status));

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Closed Booking Protection
        |--------------------------------------------------------------------------
        | Purpose:
        | - Finished, cancelled, and no-show bookings cannot be cancelled again.
        |--------------------------------------------------------------------------
        */
        if (in_array($currentStatus, ['finished', 'cancelled', 'no-show'], true)) {
            return back()->withErrors([
                'booking' => 'This booking can no longer be cancelled.',
            ]);
        }

        DB::transaction(function () use ($booking, $validated, $request, $admin) {
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['cancellation_reason'],
                'cancelled_by' => $admin->id,
                'cancelled_at' => now(),
                'status_updated_by' => $admin->id,
                'status_updated_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'user_type' => 'admin',
                'action' => 'cancel_booking',
                'module' => 'Booking Management',
                'description' => 'Admin cancelled booking #BK-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()
            ->route('admin.bookings')
            ->with('success', 'Booking has been cancelled successfully.');
    }
}