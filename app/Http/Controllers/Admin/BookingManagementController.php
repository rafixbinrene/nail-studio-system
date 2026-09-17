<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BookingManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with([
            'customer',
            'staff',
            'services',
            'cancelledBy',
            'statusUpdatedBy',
            'followUpAppointment',
            'followUpAppointment.services',
            'followUpAppointment.staff',
            'followUpAppointment.customer',
        ]);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('booking_type') && $request->booking_type !== 'all') {
            $query->where('booking_type', $request->booking_type);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

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
                    });
            });
        }

        $bookings = $query
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

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

        if (!Hash::check($validated['admin_password'], $admin->password)) {
            return back()
                ->withErrors([
                    'admin_password' => 'Incorrect admin password. Booking cancellation is not allowed.',
                ])
                ->withInput();
        }

        $currentStatus = strtolower(str_replace(' ', '-', $booking->status));

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