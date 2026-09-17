<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Staff;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'anchor_date' => ['nullable', 'date'],
        ]);

        $period = $validated['period'] ?? 'monthly';
        $anchorDate = isset($validated['anchor_date'])
            ? Carbon::parse($validated['anchor_date'])
            : now();

        [$startDate, $endDate, $periodLabel] = $this->getPeriodRange($period, $anchorDate);

        $appointments = Appointment::with([
                'customer',
                'staff',
                'services',
                'followUpAppointment',
                'followUpAppointment.services',
            ])
            ->whereBetween('appointment_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->get();

        $finishedStatuses = ['finished', 'Finished'];
        $cancelledStatuses = ['cancelled', 'Cancelled'];
        $noShowStatuses = ['no-show', 'No Show'];
        $pendingStatuses = ['pending', 'Pending'];
        $ongoingStatuses = ['ongoing', 'Ongoing'];

        $finishedAppointments = $appointments->filter(function ($appointment) use ($finishedStatuses) {
            return in_array($appointment->status, $finishedStatuses, true);
        });

        $totalBookings = $appointments->count();
        $finishedCount = $finishedAppointments->count();

        $stats = [
            'total_revenue' => $finishedAppointments->sum(function ($appointment) {
                return (float) $appointment->total_price;
            }),

            'total_bookings' => $totalBookings,

            'finished' => $finishedCount,

            'completion_rate' => $totalBookings > 0
                ? round(($finishedCount / $totalBookings) * 100, 1)
                : 0,

            'regular_bookings' => $appointments->filter(function ($appointment) {
                return $appointment->booking_type !== 'followup';
            })->count(),

            'followup_bookings' => $appointments->filter(function ($appointment) {
                return $appointment->booking_type === 'followup';
            })->count(),

            'pending' => $appointments->filter(function ($appointment) use ($pendingStatuses) {
                return in_array($appointment->status, $pendingStatuses, true);
            })->count(),

            'ongoing' => $appointments->filter(function ($appointment) use ($ongoingStatuses) {
                return in_array($appointment->status, $ongoingStatuses, true);
            })->count(),

            'cancelled' => $appointments->filter(function ($appointment) use ($cancelledStatuses) {
                return in_array($appointment->status, $cancelledStatuses, true);
            })->count(),

            'no_show' => $appointments->filter(function ($appointment) use ($noShowStatuses) {
                return in_array($appointment->status, $noShowStatuses, true);
            })->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Revenue & Bookings Trend
        | IMPORTANT:
        | Only FINISHED services are included in both revenue and bookings trend.
        |--------------------------------------------------------------------------
        */
        $trendData = $this->buildTrendData(
            $finishedAppointments,
            $period,
            $startDate,
            $endDate
        );

        /*
        |--------------------------------------------------------------------------
        | Popular Services
        |--------------------------------------------------------------------------
        */
        $popularServices = DB::table('appointment_services')
            ->join('services', 'appointment_services.service_id', '=', 'services.id')
            ->join('appointments', 'appointment_services.appointment_id', '=', 'appointments.id')
            ->whereBetween('appointments.appointment_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->whereIn('appointments.status', ['finished', 'Finished'])
            ->select(
                'services.id',
                'services.service_name',
                DB::raw('COUNT(appointment_services.service_id) as total_booked'),
                DB::raw('SUM(appointment_services.price) as total_revenue'),
                DB::raw('SUM(appointment_services.duration) as total_duration')
            )
            ->groupBy('services.id', 'services.service_name')
            ->orderByDesc('total_booked')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Peak Business Hours
        | Uses finished appointments only so it reflects completed business flow.
        |--------------------------------------------------------------------------
        */
        $hourRange = collect(range(9, 18));

            $finishedGroupedByHour = $finishedAppointments
                ->filter(function ($appointment) {
                    return !empty($appointment->start_time);
                })
                ->groupBy(function ($appointment) {
                    return (int) Carbon::parse($appointment->start_time)->format('H');
                });

            $highestPeakCount = $finishedGroupedByHour
                ->map(function ($items) {
                    return $items->count();
                })
                ->max() ?? 0;

            $peakHours = $hourRange
                ->map(function ($hour) use ($finishedGroupedByHour, $highestPeakCount) {
                    $total = $finishedGroupedByHour->has($hour)
                        ? $finishedGroupedByHour[$hour]->count()
                        : 0;

                    return [
                        'hour_24' => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
                        'hour_label' => Carbon::createFromTime($hour)->format('h A'),
                        'total' => $total,
                        'is_peak' => $highestPeakCount > 0 && $total === $highestPeakCount,
                    ];
                })
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Staff Performance
        |--------------------------------------------------------------------------
        */
        $staffMembers = Staff::orderBy('full_name')->get();

        $staffPerformance = $staffMembers
            ->map(function ($staff) use ($appointments, $finishedStatuses) {
                $assignedAppointments = $appointments->filter(function ($appointment) use ($staff) {
                    return (int) $appointment->staff_id === (int) $staff->id;
                });

                $finished = $assignedAppointments->filter(function ($appointment) use ($finishedStatuses) {
                    return in_array($appointment->status, $finishedStatuses, true);
                });

                return [
                    'id' => $staff->id,
                    'name' => $staff->full_name,
                    'status' => $staff->status,
                    'total_bookings' => $assignedAppointments->count(),
                    'finished_bookings' => $finished->count(),
                    'ongoing_bookings' => $assignedAppointments->filter(function ($appointment) {
                        return in_array($appointment->status, ['ongoing', 'Ongoing'], true);
                    })->count(),
                    'revenue' => $finished->sum(function ($appointment) {
                        return (float) $appointment->total_price;
                    }),
                ];
            })
            ->sortByDesc('finished_bookings')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Recent Bookings
        |--------------------------------------------------------------------------
        */
        $recentBookings = Appointment::with([
                'customer',
                'staff',
                'services',
                'followUpAppointment',
            ])
            ->whereBetween('appointment_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Previous / Next Period Navigation
        |--------------------------------------------------------------------------
        */
        $navigation = $this->buildNavigationDates($period, $anchorDate);

        return view('admin.dashboard', [
            'period' => $period,
            'anchorDate' => $anchorDate,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $stats,
            'chartLabels' => $trendData['labels'],
            'chartBookingCounts' => $trendData['bookings'],
            'chartRevenue' => $trendData['revenue'],
            'popularServices' => $popularServices,
            'peakHours' => $peakHours,
            'staffPerformance' => $staffPerformance,
            'recentBookings' => $recentBookings,
            'previousAnchorDate' => $navigation['previous'],
            'nextAnchorDate' => $navigation['next'],
        ]);
    }

    private function getPeriodRange(string $period, Carbon $anchorDate): array
    {
        if ($period === 'daily') {
            return [
                $anchorDate->copy()->startOfDay(),
                $anchorDate->copy()->endOfDay(),
                'Daily View',
            ];
        }

        if ($period === 'weekly') {
            return [
                $anchorDate->copy()->startOfWeek(),
                $anchorDate->copy()->endOfWeek(),
                'Weekly View',
            ];
        }

        if ($period === 'yearly') {
            return [
                $anchorDate->copy()->startOfYear(),
                $anchorDate->copy()->endOfYear(),
                'Yearly View',
            ];
        }

        return [
            $anchorDate->copy()->startOfMonth(),
            $anchorDate->copy()->endOfMonth(),
            'Monthly View',
        ];
    }

    private function buildNavigationDates(string $period, Carbon $anchorDate): array
    {
        if ($period === 'daily') {
            return [
                'previous' => $anchorDate->copy()->subDay()->toDateString(),
                'next' => $anchorDate->copy()->addDay()->toDateString(),
            ];
        }

        if ($period === 'weekly') {
            return [
                'previous' => $anchorDate->copy()->subWeek()->toDateString(),
                'next' => $anchorDate->copy()->addWeek()->toDateString(),
            ];
        }

        if ($period === 'yearly') {
            return [
                'previous' => $anchorDate->copy()->subYear()->toDateString(),
                'next' => $anchorDate->copy()->addYear()->toDateString(),
            ];
        }

        return [
            'previous' => $anchorDate->copy()->subMonth()->toDateString(),
            'next' => $anchorDate->copy()->addMonth()->toDateString(),
        ];
    }

    private function buildTrendData($finishedAppointments, string $period, Carbon $startDate, Carbon $endDate): array
    {
        if ($period === 'daily') {
            $hours = collect(range(9, 18));

            return [
                'labels' => $hours->map(function ($hour) {
                    return Carbon::createFromTime($hour)->format('h A');
                })->values(),

                'bookings' => $hours->map(function ($hour) use ($finishedAppointments) {
                    return $finishedAppointments->filter(function ($appointment) use ($hour) {
                        return $appointment->start_time
                            && (int) Carbon::parse($appointment->start_time)->format('H') === $hour;
                    })->count();
                })->values(),

                'revenue' => $hours->map(function ($hour) use ($finishedAppointments) {
                    return $finishedAppointments->filter(function ($appointment) use ($hour) {
                        return $appointment->start_time
                            && (int) Carbon::parse($appointment->start_time)->format('H') === $hour;
                    })->sum(function ($appointment) {
                        return (float) $appointment->total_price;
                    });
                })->values(),
            ];
        }

        if ($period === 'yearly') {
            $months = collect(range(1, 12));

            return [
                'labels' => $months->map(function ($month) use ($startDate) {
                    return Carbon::create($startDate->year, $month, 1)->format('M');
                })->values(),

                'bookings' => $months->map(function ($month) use ($finishedAppointments, $startDate) {
                    return $finishedAppointments->filter(function ($appointment) use ($month, $startDate) {
                        return $appointment->appointment_date
                            && (int) Carbon::parse($appointment->appointment_date)->format('Y') === (int) $startDate->year
                            && (int) Carbon::parse($appointment->appointment_date)->format('m') === (int) $month;
                    })->count();
                })->values(),

                'revenue' => $months->map(function ($month) use ($finishedAppointments, $startDate) {
                    return $finishedAppointments->filter(function ($appointment) use ($month, $startDate) {
                        return $appointment->appointment_date
                            && (int) Carbon::parse($appointment->appointment_date)->format('Y') === (int) $startDate->year
                            && (int) Carbon::parse($appointment->appointment_date)->format('m') === (int) $month;
                    })->sum(function ($appointment) {
                        return (float) $appointment->total_price;
                    });
                })->values(),
            ];
        }

        $dates = collect(CarbonPeriod::create(
            $startDate->toDateString(),
            $endDate->toDateString()
        ));

        return [
            'labels' => $dates->map(function ($date) use ($period) {
                return $period === 'weekly'
                    ? $date->format('D')
                    : $date->format('M d');
            })->values(),

            'bookings' => $dates->map(function ($date) use ($finishedAppointments) {
                return $finishedAppointments->filter(function ($appointment) use ($date) {
                    return $appointment->appointment_date
                        && Carbon::parse($appointment->appointment_date)->toDateString() === $date->toDateString();
                })->count();
            })->values(),

            'revenue' => $dates->map(function ($date) use ($finishedAppointments) {
                return $finishedAppointments->filter(function ($appointment) use ($date) {
                    return $appointment->appointment_date
                        && Carbon::parse($appointment->appointment_date)->toDateString() === $date->toDateString();
                })->sum(function ($appointment) {
                    return (float) $appointment->total_price;
                });
            })->values(),
        ];
    }
}