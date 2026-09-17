@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Dashboard View
|
| Purpose:
| - Shows today’s assigned appointments.
| - Shows upcoming assigned appointments.
| - Shows assigned services from Service Management.
| - Shows active/upcoming staff day-off records.
|
| Defense explanation:
| This dashboard gives each staff member a quick overview of their own
| schedule and assigned responsibilities. Data is loaded from the database,
| not static placeholder text.
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
.page-header {
    margin-bottom: 28px;
}

.page-header h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.page-header p {
    color: var(--muted);
    font-size: 14px;
}

.alert {
    padding: 14px 16px;
    border-radius: 18px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 700;
}

.alert-success {
    background: rgba(81,148,91,.16);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.alert-error {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.stat-card,
.card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 30px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.stat-card {
    padding: 22px;
}

.stat-card h2 {
    font-size: 28px;
    color: var(--brown);
}

.stat-card p {
    color: var(--muted);
    font-size: 13px;
    margin-top: 6px;
    font-weight: 700;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.card-header {
    padding: 22px 24px;
    border-bottom: 1px solid rgba(255,255,255,.55);
    font-weight: 900;
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: center;
}

.card-header span {
    color: var(--muted);
    font-size: 12px;
    font-weight: 800;
}

.card-body {
    padding: 24px;
}

.appointment {
    padding: 18px;
    border-radius: 22px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    margin-bottom: 14px;
}

.appointment h4 {
    margin-bottom: 6px;
}

.appointment p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.6;
}

.status,
.type-badge {
    display: inline-block;
    margin-top: 10px;
    padding: 7px 13px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
}

.type-badge {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
    margin-right: 6px;
}

.type-badge.followup {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
}

.status.pending {
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.status.ongoing {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
}

.status.finished {
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
}

.status.cancelled,
.status.no-show {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 12px;
    margin-right: 8px;
    padding: 10px 16px;
    border-radius: 999px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 900;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.light-btn {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.chip-wrap {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.chip {
    display: inline-block;
    padding: 9px 13px;
    border-radius: 999px;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--dark);
    font-size: 13px;
    font-weight: 800;
}

.dayoff-chip {
    background: rgba(255,196,87,.16);
    color: #9a6a00;
    border: 1px solid rgba(255,196,87,.25);
}

.side-section {
    margin-bottom: 24px;
}

.side-section h3 {
    font-size: 16px;
    margin-bottom: 12px;
}

.empty-box {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted);
}

.empty-box h3 {
    color: var(--dark);
    margin-bottom: 8px;
}

@media(max-width:1200px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media(max-width:900px) {
    .stats-grid,
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>Staff Dashboard</h1>
    <p>View today’s appointments, upcoming bookings, assigned services, and day-off schedule.</p>
</div>

@if (!$staffProfile)
    <div class="alert alert-error">
        Your staff profile was not found. Make sure your staff user email matches a record in the staff table.
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
    </div>
@endif

<div class="stats-grid">
    <div class="stat-card">
        <h2>{{ $todayCount }}</h2>
        <p>Today’s Appointments</p>
    </div>

    <div class="stat-card">
        <h2>{{ $pendingCount }}</h2>
        <p>Pending Bookings</p>
    </div>

    <div class="stat-card">
        <h2>{{ $ongoingCount }}</h2>
        <p>Ongoing Service</p>
    </div>

    <div class="stat-card">
        <h2>{{ $finishedThisWeek }}</h2>
        <p>Finished This Week</p>
    </div>

    <div class="stat-card">
        <h2>{{ $assignedServicesCount }}</h2>
        <p>Assigned Services</p>
    </div>

    <div class="stat-card">
        <h2>{{ $activeDayOffCount }}</h2>
        <p>Active Day Offs</p>
    </div>
</div>

<div class="dashboard-grid">
    <section class="card">
        <div class="card-header">
            Today’s Schedule
            <span>{{ now()->format('F d, Y') }}</span>
        </div>

        <div class="card-body">
            @forelse ($todayAppointments as $appointment)
                @php
                    $statusKey = strtolower(str_replace(' ', '-', $appointment->status));
                    $statusLabel = ucwords(str_replace('-', ' ', $statusKey));

                    $mainService = $appointment->services->first()?->service_name ?? 'Beauty Service';

                    $startTime = $appointment->start_time
                        ? \Carbon\Carbon::parse($appointment->start_time)->format('h:i A')
                        : 'Not set';

                    $endTime = $appointment->end_time
                        ? \Carbon\Carbon::parse($appointment->end_time)->format('h:i A')
                        : null;

                    $bookingType = $appointment->booking_type === 'followup' ? 'followup' : 'regular';
                    $bookingTypeLabel = $bookingType === 'followup' ? 'Backjob' : 'Regular';
                @endphp

                <div class="appointment">
                    <h4>{{ $mainService }}</h4>

                    <p>
                        Booking ID:
                        #BK-{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}
                    </p>

                    <p>
                        Customer:
                        {{ $appointment->customer?->full_name ?? 'Customer record missing' }}
                    </p>

                    <p>
                        Time:
                        {{ $startTime }}
                        @if ($endTime)
                            - {{ $endTime }}
                        @endif
                    </p>

                    <span class="type-badge {{ $bookingType }}">
                        {{ $bookingTypeLabel }}
                    </span>

                    <span class="status {{ $statusKey }}">
                        {{ $statusLabel }}
                    </span>

                    <br>

                    <a href="{{ route('staff.appointments') }}" class="action-btn">
                        Update Status
                    </a>
                </div>
            @empty
                <div class="empty-box">
                    <h3>No appointments today</h3>
                    <p>You currently have no assigned appointment for today.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            Staff Summary
            <span>{{ $staffProfile?->status ?? 'No profile' }}</span>
        </div>

        <div class="card-body">
            <div class="side-section">
                <h3>Assigned Services</h3>

                <div class="chip-wrap">
                    @forelse ($staffProfile?->services ?? [] as $service)
                        <span class="chip">{{ $service->service_name }}</span>
                    @empty
                        <span class="chip">No services assigned</span>
                    @endforelse
                </div>
            </div>

            <div class="side-section">
                <h3>Active / Upcoming Day Offs</h3>

                <div class="chip-wrap">
                    @forelse ($staffProfile?->activeDayOffs ?? [] as $dayOff)
                        <span class="chip dayoff-chip">
                            {{ $dayOff->start_date->format('M d, Y') }}
                            -
                            {{ $dayOff->end_date->format('M d, Y') }}
                        </span>
                    @empty
                        <span class="chip">No active day off</span>
                    @endforelse
                </div>
            </div>

            <div class="side-section">
                <h3>Quick Actions</h3>

                <a href="{{ route('staff.appointments') }}" class="action-btn">
                    View Appointments
                </a>

                <a href="{{ route('staff.profile') }}" class="action-btn light-btn">
                    View Profile
                </a>
            </div>
        </div>
    </section>
</div>

@if ($upcomingAppointments->count() > 0)
    <br>

    <section class="card">
        <div class="card-header">
            Upcoming Assigned Appointments
            <span>Next 5 active bookings</span>
        </div>

        <div class="card-body">
            @foreach ($upcomingAppointments as $appointment)
                @php
                    $statusKey = strtolower(str_replace(' ', '-', $appointment->status));
                    $statusLabel = ucwords(str_replace('-', ' ', $statusKey));

                    $mainService = $appointment->services->first()?->service_name ?? 'Beauty Service';

                    $appointmentDate = $appointment->appointment_date
                        ? \Carbon\Carbon::parse($appointment->appointment_date)->format('F d, Y')
                        : 'Not set';

                    $startTime = $appointment->start_time
                        ? \Carbon\Carbon::parse($appointment->start_time)->format('h:i A')
                        : 'Not set';

                    $endTime = $appointment->end_time
                        ? \Carbon\Carbon::parse($appointment->end_time)->format('h:i A')
                        : null;

                    $bookingType = $appointment->booking_type === 'followup' ? 'followup' : 'regular';
                    $bookingTypeLabel = $bookingType === 'followup' ? 'Backjob' : 'Regular';
                @endphp

                <div class="appointment">
                    <h4>{{ $mainService }}</h4>

                    <p>
                        Booking ID:
                        #BK-{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}
                    </p>

                    <p>
                        Customer:
                        {{ $appointment->customer?->full_name ?? 'Customer record missing' }}
                    </p>

                    <p>Date: {{ $appointmentDate }}</p>

                    <p>
                        Time:
                        {{ $startTime }}
                        @if ($endTime)
                            - {{ $endTime }}
                        @endif
                    </p>

                    <span class="type-badge {{ $bookingType }}">
                        {{ $bookingTypeLabel }}
                    </span>

                    <span class="status {{ $statusKey }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            @endforeach
        </div>
    </section>
@endif

@endsection