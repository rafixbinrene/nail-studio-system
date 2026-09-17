@extends('layouts.app')

@section('styles')
<style>
.page-header {
    margin-bottom: 22px;
}

.page-header h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.page-header p {
    color: var(--muted);
    font-size: 14px;
}

.period-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.period-tabs {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.period-tabs a,
.nav-btn {
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 900;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.period-tabs a.active {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    border: none;
}

.period-navigation {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.period-note {
    margin-bottom: 24px;
    color: var(--muted);
    font-size: 13px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.stat-card,
.chart-card,
.table-card,
.list-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 28px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.stat-card {
    padding: 24px;
}

.stat-card h2 {
    font-size: 32px;
    color: var(--brown);
    margin-bottom: 8px;
}

.stat-card p {
    color: var(--muted);
    font-size: 13px;
    font-weight: 900;
}

.stat-card small {
    display: block;
    margin-top: 10px;
    color: #7d6d60;
    font-size: 12px;
    line-height: 1.5;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

.status-box {
    padding: 18px;
    border-radius: 24px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
}

.status-box h3 {
    font-size: 24px;
    color: var(--brown);
}

.status-box p {
    color: var(--muted);
    font-size: 12px;
    margin-top: 6px;
    font-weight: 900;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 22px;
    margin-bottom: 24px;
}

.dashboard-grid-equal {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    margin-bottom: 24px;
}

.chart-card,
.table-card,
.list-card {
    padding: 24px;
}

.card-title {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    margin-bottom: 18px;
}

.card-title h2 {
    font-size: 20px;
}

.card-title span {
    font-size: 12px;
    color: var(--muted);
    font-weight: 900;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    font-size: 12px;
    color: var(--muted);
    padding: 12px;
    border-bottom: 1px solid var(--border);
}

td {
    padding: 14px 12px;
    font-size: 13px;
    border-bottom: 1px solid rgba(47,33,24,.08);
}

td strong {
    color: var(--dark);
}

.badge {
    display: inline-block;
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 900;
    white-space: nowrap;
}

.badge.regular {
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--dark);
}

.badge.followup {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
}

.badge.pending {
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.badge.ongoing {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
}

.badge.finished {
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
}

.badge.cancelled,
.badge.no-show {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

.service-row {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid rgba(47,33,24,.08);
}

.service-row:last-child {
    border-bottom: none;
}

.service-row h3 {
    font-size: 14px;
    margin-bottom: 4px;
}

.service-row p {
    color: var(--muted);
    font-size: 12px;
    line-height: 1.5;
}

.service-row strong {
    color: var(--brown);
    white-space: nowrap;
}

.empty-box {
    padding: 28px;
    text-align: center;
    color: var(--muted);
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
    border-radius: 22px;
}

.chart-box {
    position: relative;
    min-height: 320px;
}

.chart-box.chart-box-tall {
    min-height: 360px;
}

canvas {
    width: 100% !important;
    height: 100% !important;
}

@media(max-width:1200px) {
    .stats-grid,
    .status-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .dashboard-grid,
    .dashboard-grid-equal {
        grid-template-columns: 1fr;
    }
}

@media(max-width:700px) {
    .stats-grid,
    .status-grid {
        grid-template-columns: 1fr;
    }

    .period-tabs a,
    .nav-btn {
        width: 100%;
        text-align: center;
    }

    .period-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .period-navigation,
    .period-tabs {
        width: 100%;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Overview of revenue, bookings, services, peak hours, staff performance, and recent transactions.</p>
</div>

<div class="period-toolbar">
    <div class="period-tabs">
        <a
            href="{{ route('admin.dashboard', ['period' => 'daily', 'anchor_date' => $anchorDate->toDateString()]) }}"
            class="{{ $period === 'daily' ? 'active' : '' }}"
        >
            Daily
        </a>

        <a
            href="{{ route('admin.dashboard', ['period' => 'weekly', 'anchor_date' => $anchorDate->toDateString()]) }}"
            class="{{ $period === 'weekly' ? 'active' : '' }}"
        >
            Weekly
        </a>

        <a
            href="{{ route('admin.dashboard', ['period' => 'monthly', 'anchor_date' => $anchorDate->toDateString()]) }}"
            class="{{ $period === 'monthly' ? 'active' : '' }}"
        >
            Monthly
        </a>

        <a
            href="{{ route('admin.dashboard', ['period' => 'yearly', 'anchor_date' => $anchorDate->toDateString()]) }}"
            class="{{ $period === 'yearly' ? 'active' : '' }}"
        >
            Yearly
        </a>
    </div>

    <div class="period-navigation">
        <a
            href="{{ route('admin.dashboard', ['period' => $period, 'anchor_date' => $previousAnchorDate]) }}"
            class="nav-btn"
        >
            ← Previous
        </a>

        <a
            href="{{ route('admin.dashboard', ['period' => $period, 'anchor_date' => $nextAnchorDate]) }}"
            class="nav-btn"
        >
            Next →
        </a>
    </div>
</div>

<p class="period-note">
    Showing data for:
    <strong>{{ $periodLabel }}</strong>
    —
    {{ $startDate->format('M d, Y') }}
    to
    {{ $endDate->format('M d, Y') }}
</p>

<div class="stats-grid">
    <div class="stat-card">
        <h2>RM {{ number_format($stats['total_revenue'], 2) }}</h2>
        <p>Total Revenue</p>
        <small>Based on finished services only.</small>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['total_bookings'] }}</h2>
        <p>Total Bookings</p>
        <small>All bookings in the selected period.</small>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['finished'] }}</h2>
        <p>Completed Services</p>
        <small>{{ $stats['completion_rate'] }}% completion rate.</small>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['followup_bookings'] }}</h2>
        <p>Backjob / Follow-up</p>
        <small>Linked to finished customer history.</small>
    </div>
</div>

<div class="status-grid">
    <div class="status-box">
        <h3>{{ $stats['pending'] }}</h3>
        <p>Pending</p>
    </div>

    <div class="status-box">
        <h3>{{ $stats['ongoing'] }}</h3>
        <p>Ongoing</p>
    </div>

    <div class="status-box">
        <h3>{{ $stats['finished'] }}</h3>
        <p>Finished</p>
    </div>

    <div class="status-box">
        <h3>{{ $stats['cancelled'] }}</h3>
        <p>Cancelled</p>
    </div>

    <div class="status-box">
        <h3>{{ $stats['no_show'] }}</h3>
        <p>No Show</p>
    </div>
</div>

<div class="dashboard-grid">
    <section class="chart-card">
        <div class="card-title">
            <h2>Revenue & Bookings Trend</h2>
            <span>Finished services only · {{ $periodLabel }}</span>
        </div>

        <div class="chart-box">
            <canvas id="trendChart"></canvas>
        </div>
    </section>

    <section class="list-card">
        <div class="card-title">
            <h2>Popular Services</h2>
            <span>{{ $periodLabel }}</span>
        </div>

        @forelse ($popularServices as $service)
            <div class="service-row">
                <div>
                    <h3>{{ $service->service_name }}</h3>
                    <p>
                        {{ $service->total_booked }} finished booking(s)
                        · {{ $service->total_duration ?? 0 }} minutes
                    </p>
                </div>

                <strong>
                    RM {{ number_format($service->total_revenue ?? 0, 2) }}
                </strong>
            </div>
        @empty
            <div class="empty-box">
                No service booking records for this period.
            </div>
        @endforelse
    </section>
</div>

<div class="dashboard-grid-equal">
    <section class="chart-card">
        <div class="card-title">
            <h2>Peak Business Hours</h2>
            <span>Finished services only · {{ $periodLabel }}</span>
        </div>

        @if ($peakHours->sum('total') > 0)
            <div class="chart-box chart-box-tall">
                <canvas id="peakHoursChart"></canvas>
            </div>
        @else
            <div class="empty-box">
                No peak hour data for this period.
            </div>
        @endif
    </section>

    <section class="table-card">
        <div class="card-title">
            <h2>Staff Performance</h2>
            <span>{{ $periodLabel }}</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Finished</th>
                        <th>Ongoing</th>
                        <th>Revenue</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($staffPerformance as $staff)
                        <tr>
                            <td><strong>{{ $staff['name'] }}</strong></td>
                            <td>{{ $staff['status'] }}</td>
                            <td>{{ $staff['total_bookings'] }}</td>
                            <td>{{ $staff['finished_bookings'] }}</td>
                            <td>{{ $staff['ongoing_bookings'] }}</td>
                            <td>RM {{ number_format($staff['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No staff records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<section class="table-card">
    <div class="card-title">
        <h2>Recent Bookings</h2>
        <span>{{ $periodLabel }}</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Services</th>
                    <th>Staff</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($recentBookings as $booking)
                    @php
                        $statusKey = strtolower(str_replace(' ', '-', $booking->status));
                        $statusLabel = ucwords(str_replace('-', ' ', $statusKey));
                        $bookingType = $booking->booking_type === 'followup' ? 'followup' : 'regular';
                        $bookingTypeLabel = $bookingType === 'followup' ? 'Backjob' : 'Regular';
                        $serviceNames = $booking->services->pluck('service_name')->join(', ');
                    @endphp

                    <tr>
                        <td>
                            <strong>#BK-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</strong>
                        </td>

                        <td>
                            {{ $booking->customer?->full_name ?? 'Customer record missing' }}
                        </td>

                        <td>
                            {{ $serviceNames ?: 'No service recorded' }}
                        </td>

                        <td>
                            {{ $booking->staff?->full_name ?? 'Not assigned' }}
                        </td>

                        <td>
                            {{ $booking->appointment_date ? \Carbon\Carbon::parse($booking->appointment_date)->format('M d, Y') : 'Not set' }}
                        </td>

                        <td>
                            <span class="badge {{ $bookingType }}">
                                {{ $bookingTypeLabel }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $statusKey }}">
                                {{ $statusLabel }}
                            </span>
                        </td>

                        <td>
                            RM {{ number_format($booking->total_price, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No recent bookings found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const trendLabels = @json($chartLabels);
const bookingCounts = @json($chartBookingCounts);
const revenueData = @json($chartRevenue);

const peakHours = @json($peakHours);
const peakHourLabels = peakHours.map(item => item.hour_label);
const peakHourCounts = peakHours.map(item => item.total);

const highestPeakValue = peakHourCounts.length > 0
    ? Math.max(...peakHourCounts)
    : 0;

const highestPeakIndex = peakHourCounts.indexOf(highestPeakValue);

const peakBarColors = peakHourCounts.map((value, index) => {
    if (highestPeakValue > 0 && index === highestPeakIndex) {
        return 'rgba(111, 66, 193, 0.90)';
    }

    return 'rgba(54, 162, 235, 0.85)';
});

const peakBorderColors = peakHourCounts.map((value, index) => {
    if (highestPeakValue > 0 && index === highestPeakIndex) {
        return 'rgba(111, 66, 193, 1)';
    }

    return 'rgba(54, 162, 235, 1)';
});

const peakHourHighlightPlugin = {
    id: 'peakHourHighlightPlugin',

    afterDatasetsDraw(chart) {
        if (highestPeakValue <= 0 || highestPeakIndex < 0) {
            return;
        }

        const ctx = chart.ctx;
        const meta = chart.getDatasetMeta(0);
        const bar = meta.data[highestPeakIndex];

        if (!bar) {
            return;
        }

        const x = bar.x;
        const y = bar.y;

        ctx.save();

        ctx.fillStyle = 'rgba(111, 66, 193, 0.95)';
        ctx.strokeStyle = 'rgba(111, 66, 193, 0.95)';
        ctx.lineWidth = 2;

        const labelText = 'Peak Hour';
        const countText = highestPeakValue + ' booking(s)';

        ctx.font = 'bold 12px Arial';
        const labelWidth = Math.max(
            ctx.measureText(labelText).width,
            ctx.measureText(countText).width
        ) + 24;

        const labelHeight = 44;
        const labelX = x - labelWidth / 2;
        const labelY = y - 68;

        roundRect(ctx, labelX, labelY, labelWidth, labelHeight, 10);
        ctx.fill();

        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        ctx.fillText(labelText, x, labelY + 17);
        ctx.font = '11px Arial';
        ctx.fillText(countText, x, labelY + 33);

        ctx.beginPath();
        ctx.moveTo(x, labelY + labelHeight);
        ctx.lineTo(x - 8, labelY + labelHeight + 12);
        ctx.lineTo(x + 8, labelY + labelHeight + 12);
        ctx.closePath();
        ctx.fillStyle = 'rgba(111, 66, 193, 0.95)';
        ctx.fill();

        ctx.restore();
    }
};

function roundRect(ctx, x, y, width, height, radius) {
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
}

const trendCanvas = document.getElementById('trendChart');

if (trendCanvas) {
    new Chart(trendCanvas, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Finished Bookings',
                    data: bookingCounts,
                    tension: 0.35,
                    borderWidth: 3,
                    fill: false,
                    yAxisID: 'y'
                },
                {
                    label: 'Revenue RM',
                    data: revenueData,
                    tension: 0.35,
                    borderWidth: 3,
                    fill: false,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        precision: 0
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

const peakHoursCanvas = document.getElementById('peakHoursChart');

if (peakHoursCanvas && peakHourCounts.some(value => value > 0)) {
    new Chart(peakHoursCanvas, {
        type: 'bar',
        data: {
            labels: peakHourLabels,
            datasets: [
                {
                    label: 'Finished Bookings',
                    data: peakHourCounts,
                    backgroundColor: peakBarColors,
                    borderColor: peakBorderColors,
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.78,
                    categoryPercentage: 0.88
                }
            ]
        },
        plugins: [peakHourHighlightPlugin],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 75
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' finished booking(s)';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}
</script>
@endsection