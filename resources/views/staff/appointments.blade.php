@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Appointments View
|
| Purpose:
| - Shows appointments assigned only to the logged-in staff member.
| - Shows customer details, selected services, booking type, and status.
| - Allows staff to update appointment status.
| - Shows backjob/follow-up connection when applicable.
| - Locks finished, cancelled, and no-show bookings from further updates.
|
| Defense explanation:
| This page supports staff workflow because each staff member can monitor
| assigned bookings and update appointment progress without accessing
| admin-only booking controls.
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

.stat-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 26px;
    padding: 22px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
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

.tabs {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 12px 20px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.45);
    color: var(--dark);
    font-weight: 900;
    cursor: pointer;
}

.tab-btn.active {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.appointment-list {
    display: grid;
    gap: 20px;
}

.appointment-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 24px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.appointment-top {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.appointment-top h3 {
    font-size: 18px;
}

.booking-id {
    color: var(--muted);
    font-size: 13px;
    margin-top: 4px;
}

.badge-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.status-badge,
.type-badge {
    padding: 7px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    height: fit-content;
    white-space: nowrap;
}

.type-badge.regular {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.type-badge.followup {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
}

.status-badge.pending {
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.status-badge.ongoing {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
}

.status-badge.finished {
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
}

.status-badge.cancelled,
.status-badge.no-show {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 18px;
}

.detail-box {
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    border-radius: 20px;
    padding: 16px;
}

.detail-box small {
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
}

.detail-box p {
    margin-top: 6px;
    font-size: 14px;
    font-weight: 700;
    word-break: break-word;
}

.service-list,
.followup-box,
.cancel-box {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
    margin-bottom: 18px;
}

.followup-box {
    background: rgba(138,113,88,.10);
    border: 1px solid rgba(138,113,88,.22);
}

.cancel-box {
    background: rgba(184,77,77,.08);
    border: 1px solid rgba(184,77,77,.18);
}

.service-list small,
.followup-box small,
.cancel-box small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
    margin-bottom: 8px;
}

.service-list span {
    display: inline-block;
    margin: 4px 6px 4px 0;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    font-size: 13px;
    font-weight: 700;
}

.followup-box p,
.cancel-box p {
    color: #7d6d60;
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 6px;
}

.status-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.status-actions form {
    display: inline-block;
}

.status-btn {
    border: none;
    padding: 11px 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.status-btn.primary {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.status-btn.danger {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.locked-note {
    color: var(--muted);
    font-size: 13px;
    font-weight: 800;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--glass);
    border: 1px solid var(--border);
    border-radius: 30px;
    color: var(--muted);
}

.empty-state h3 {
    color: var(--dark);
    margin-bottom: 8px;
}

@media(max-width:1200px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media(max-width:1100px) {
    .details-grid {
        grid-template-columns: 1fr;
    }
}

@media(max-width:700px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .appointment-top {
        flex-direction: column;
    }

    .badge-row {
        justify-content: flex-start;
    }

    .status-actions form,
    .status-btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>Appointments</h1>
    <p>View assigned customer bookings, backjob requests, and update appointment status.</p>
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
        <h2>{{ $appointments->count() }}</h2>
        <p>Total Assigned</p>
    </div>

    <div class="stat-card">
        <h2>{{ $pendingAppointments->count() }}</h2>
        <p>Pending</p>
    </div>

    <div class="stat-card">
        <h2>{{ $ongoingAppointments->count() }}</h2>
        <p>Ongoing</p>
    </div>

    <div class="stat-card">
        <h2>{{ $finishedAppointments->count() }}</h2>
        <p>Finished</p>
    </div>

    <div class="stat-card">
        <h2>{{ $cancelledAppointments->count() }}</h2>
        <p>Cancelled</p>
    </div>

    <div class="stat-card">
        <h2>{{ $followUpAppointments->count() }}</h2>
        <p>Backjob / Follow-up</p>
    </div>
</div>

<div class="tabs">
    <button class="tab-btn active" onclick="showAppointments('all', this)">All</button>
    <button class="tab-btn" onclick="showAppointments('pending', this)">Pending</button>
    <button class="tab-btn" onclick="showAppointments('ongoing', this)">Ongoing</button>
    <button class="tab-btn" onclick="showAppointments('finished', this)">Finished</button>
    <button class="tab-btn" onclick="showAppointments('cancelled', this)">Cancelled</button>
    <button class="tab-btn" onclick="showAppointments('no-show', this)">No Show</button>
    <button class="tab-btn" onclick="showAppointments('followup', this)">Backjob</button>
</div>

<div class="appointment-list">
    @forelse ($appointments as $appointment)
        @php
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Appointment display preparation.
            |--------------------------------------------------------------------------
            | Purpose:
            | - Normalizes appointment status for CSS and filtering.
            | - Calculates duration using start/end time or selected services.
            | - Detects regular booking vs backjob/follow-up booking.
            |--------------------------------------------------------------------------
            */
            $statusKey = strtolower(str_replace(' ', '-', $appointment->status));
            $statusLabel = ucwords(str_replace('-', ' ', $statusKey));

            $bookingType = $appointment->booking_type === 'followup' ? 'followup' : 'regular';
            $bookingTypeLabel = $bookingType === 'followup' ? 'Backjob / Follow-up' : 'Regular Booking';

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

            $durationMinutes = 0;

            if ($appointment->start_time && $appointment->end_time) {
                $durationMinutes = \Carbon\Carbon::parse($appointment->start_time)
                    ->diffInMinutes(\Carbon\Carbon::parse($appointment->end_time));
            }

            if ($durationMinutes <= 0) {
                $durationMinutes = $appointment->services->sum(function ($service) {
                    return (int) ($service->pivot->duration ?? $service->duration ?? 0);
                });
            }

            $canUpdate = !in_array($statusKey, ['finished', 'cancelled', 'no-show'], true);

            $original = $appointment->followUpAppointment;

            $originalServices = $original
                ? $original->services->pluck('service_name')->join(', ')
                : null;

            $originalDuration = 0;

            if ($original && $original->start_time && $original->end_time) {
                $originalDuration = \Carbon\Carbon::parse($original->start_time)
                    ->diffInMinutes(\Carbon\Carbon::parse($original->end_time));
            }

            if ($original && $originalDuration <= 0) {
                $originalDuration = $original->services->sum(function ($service) {
                    return (int) ($service->pivot->duration ?? $service->duration ?? 0);
                });
            }
        @endphp

        <div class="appointment-card appointment-item {{ $statusKey }} {{ $bookingType }}">
            <div class="appointment-top">
                <div>
                    <h3>{{ $mainService }}</h3>

                    <p class="booking-id">
                        Booking ID:
                        #BK-{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}
                    </p>

                    <p class="booking-id">
                        Customer:
                        {{ $appointment->customer?->full_name ?? 'Customer record missing' }}
                    </p>
                </div>

                <div class="badge-row">
                    <span class="type-badge {{ $bookingType }}">
                        {{ $bookingTypeLabel }}
                    </span>

                    <span class="status-badge {{ $statusKey }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-box">
                    <small>CUSTOMER</small>
                    <p>{{ $appointment->customer?->full_name ?? 'Customer record missing' }}</p>
                </div>

                <div class="detail-box">
                    <small>DATE</small>
                    <p>{{ $appointmentDate }}</p>
                </div>

                <div class="detail-box">
                    <small>TIME</small>
                    <p>
                        {{ $startTime }}
                        @if ($endTime)
                            - {{ $endTime }}
                        @endif
                    </p>
                </div>

                <div class="detail-box">
                    <small>DURATION</small>
                    <p>{{ $durationMinutes }} minutes</p>
                </div>

                <div class="detail-box">
                    <small>TOTAL</small>
                    <p>RM {{ number_format($appointment->total_price, 2) }}</p>
                </div>

                <div class="detail-box">
                    <small>BOOKING TYPE</small>
                    <p>{{ $bookingTypeLabel }}</p>
                </div>

                <div class="detail-box">
                    <small>UPDATED BY</small>
                    <p>{{ $appointment->statusUpdatedBy?->name ?? 'System / Not updated' }}</p>
                </div>

                <div class="detail-box">
                    <small>UPDATED AT</small>
                    <p>
                        {{ $appointment->status_updated_at ? \Carbon\Carbon::parse($appointment->status_updated_at)->format('M d, Y h:i A') : 'Not updated' }}
                    </p>
                </div>
            </div>

            <div class="service-list">
                <small>
                    {{ $bookingType === 'followup' ? 'SELECTED BACKJOB SERVICES' : 'SELECTED SERVICES' }}
                </small>

                @forelse ($appointment->services as $service)
                    <span>
                        {{ $service->service_name }}
                        · {{ $service->pivot->duration ?? $service->duration }}m
                        · RM {{ number_format($service->pivot->price ?? $service->price, 2) }}
                    </span>
                @empty
                    <span>No service recorded</span>
                @endforelse
            </div>

            @if ($bookingType === 'followup')
                <div class="followup-box">
                    <small>BACKJOB / FOLLOW-UP CONNECTION</small>

                    @if ($original)
                        <p>
                            <strong>Original Booking:</strong>
                            #BK-{{ str_pad($original->id, 4, '0', STR_PAD_LEFT) }}
                        </p>

                        <p>
                            <strong>Original Services:</strong>
                            {{ $originalServices ?: 'No service recorded' }}
                        </p>

                        <p>
                            <strong>Original Staff:</strong>
                            {{ $original->staff?->full_name ?? 'Not assigned' }}
                        </p>

                        <p>
                            <strong>Original Duration:</strong>
                            {{ $originalDuration }} minutes
                        </p>

                        <p>
                            <strong>Selected Backjob Duration:</strong>
                            {{ $durationMinutes }} minutes
                        </p>

                        <p>
                            <strong>Reason:</strong>
                            {{ $appointment->follow_up_reason ?? 'No reason provided.' }}
                        </p>
                    @else
                        <p>This backjob booking has no linked original booking record.</p>
                    @endif
                </div>
            @endif

            @if ($statusKey === 'cancelled')
                <div class="cancel-box">
                    <small>CANCELLATION RECORD</small>

                    <p>
                        <strong>Reason:</strong>
                        {{ $appointment->cancellation_reason ?? 'No cancellation reason recorded.' }}
                    </p>

                    <p>
                        <strong>Cancelled By:</strong>
                        {{ $appointment->cancelledBy?->name ?? 'Unknown user' }}
                    </p>

                    <p>
                        <strong>Cancelled At:</strong>
                        {{ $appointment->cancelled_at ? \Carbon\Carbon::parse($appointment->cancelled_at)->format('F d, Y h:i A') : 'Not recorded' }}
                    </p>
                </div>
            @endif

            <div class="status-actions">
                @if ($canUpdate)
                    @if ($statusKey !== 'ongoing')
                        <form method="POST" action="{{ route('staff.appointments.status', $appointment->id) }}">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status" value="ongoing">

                            <button type="submit" class="status-btn primary">
                                Start / Ongoing
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('staff.appointments.status', $appointment->id) }}">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="status" value="finished">

                        <button
                            type="submit"
                            class="status-btn"
                            onclick="return confirm('Mark this appointment as finished?');"
                        >
                            Mark Finished
                        </button>
                    </form>

                    <form method="POST" action="{{ route('staff.appointments.status', $appointment->id) }}">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="status" value="no-show">

                        <button
                            type="submit"
                            class="status-btn danger"
                            onclick="return confirm('Mark this appointment as no-show?');"
                        >
                            No Show
                        </button>
                    </form>
                @else
                    <p class="locked-note">
                        This appointment is already {{ $statusLabel }} and can no longer be updated by staff.
                    </p>
                @endif
            </div>
        </div>
    @empty
        <div class="empty-state">
            <h3>No assigned appointments</h3>
            <p>There are no bookings assigned to this staff account yet.</p>
        </div>
    @endforelse
</div>

@endsection

@section('scripts')
<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Staff Appointment Tab Filter
|--------------------------------------------------------------------------
| Purpose:
| - Filters appointment cards by status or booking type without page reload.
| - Helps staff quickly view pending, ongoing, finished, cancelled,
|   no-show, or backjob bookings.
|--------------------------------------------------------------------------
*/
function showAppointments(type, button) {
    const buttons = document.querySelectorAll('.tab-btn');
    const items = document.querySelectorAll('.appointment-item');

    buttons.forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');

    items.forEach(item => {
        if (type === 'all') {
            item.style.display = 'block';
        } else {
            item.style.display = item.classList.contains(type) ? 'block' : 'none';
        }
    });
}
</script>
@endsection