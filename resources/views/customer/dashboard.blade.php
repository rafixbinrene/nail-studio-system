@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Dashboard View
|
| Purpose:
| - Shows customer booking summary.
| - Shows nearest upcoming appointment.
| - Shows notifications and booking limit.
| - Shows favorite or recommended services.
| - Shows backjob/follow-up availability.
|
| Defense explanation:
| This dashboard gives customers a clear view of their current appointment
| status, booking history summary, and next possible actions.
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    margin-bottom: 28px;
}

.topbar h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.topbar p {
    color: var(--muted);
    font-size: 14px;
}

.book-btn {
    text-decoration: none;
    padding: 14px 22px;
    border-radius: 999px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 900;
    white-space: nowrap;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.summary-card,
.card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 30px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.summary-card {
    padding: 22px;
}

.summary-card h2 {
    font-size: 28px;
    color: var(--brown);
}

.summary-card p {
    color: var(--muted);
    font-size: 13px;
    margin-top: 6px;
    font-weight: 700;
}

.grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.lower-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.card-header {
    padding: 22px 24px;
    border-bottom: 1px solid rgba(255,255,255,.55);
    font-weight: 900;
}

.card-body {
    padding: 24px;
}

.appointment-box {
    padding: 18px;
    border-radius: 22px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
}

.appointment-top {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 16px;
}

.appointment-top h3 {
    font-size: 18px;
    margin-bottom: 5px;
}

.appointment-top p {
    color: var(--muted);
    font-size: 13px;
}

.status,
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

.status.pending {
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.status.approved {
    background: rgba(82,139,255,.15);
    color: #2f5fb8;
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

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
    margin-bottom: 18px;
}

.detail-box {
    padding: 14px;
    border-radius: 18px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
}

.detail-box small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
    margin-bottom: 6px;
}

.detail-box p {
    font-size: 14px;
    font-weight: 700;
}

.action-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-block;
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 900;
}

.primary-btn {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.light-btn {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.empty-box {
    text-align: center;
    padding: 38px 20px;
    color: var(--muted);
}

.empty-icon {
    font-size: 42px;
    margin-bottom: 12px;
}

.empty-box h3 {
    color: var(--dark);
    margin-bottom: 8px;
}

.empty-box a {
    display: inline-block;
    margin-top: 16px;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 999px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 900;
}

.notice {
    display: flex;
    gap: 12px;
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    margin-bottom: 12px;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--brown);
    margin-top: 6px;
    flex-shrink: 0;
}

.notice h4 {
    margin-bottom: 5px;
}

.notice p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
}

.history-section {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    margin-bottom: 12px;
}

.history-section h4 {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 6px;
    font-weight: 900;
}

.history-section p {
    font-weight: 900;
}

.service-item {
    display: flex;
    gap: 14px;
    align-items: center;
    padding: 14px;
    border-radius: 20px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    margin-bottom: 12px;
}

.service-item img {
    width: 58px;
    height: 58px;
    object-fit: cover;
    border-radius: 16px;
}

.service-item h4 {
    margin-bottom: 5px;
}

.service-item p {
    color: var(--muted);
    font-size: 13px;
}

@media(max-width:1200px) {
    .summary-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media(max-width:900px) {
    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .summary-grid,
    .grid,
    .lower-grid,
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .book-btn {
        width: 100%;
        text-align: center;
    }

    .appointment-top {
        flex-direction: column;
    }
}
</style>
@endsection

@section('content')

@php
    $user = auth()->user();

    $defaultServiceImage = 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=200&q=80';
@endphp

<div class="topbar">
    <div>
        <h1>Welcome Back, {{ $customer?->full_name ?? $user->name }}!</h1>
        <p>Ready for your next pampering session?</p>
    </div>

    <a href="{{ route('customer.booking') }}" class="book-btn">
        Book Appointment
    </a>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <h2>{{ $appointments->count() }}</h2>
        <p>Total Bookings</p>
    </div>

    <div class="summary-card">
        <h2>{{ $activeBookings->count() }}</h2>
        <p>Active Bookings</p>
    </div>

    <div class="summary-card">
        <h2>{{ $finishedBookings->count() }}</h2>
        <p>Finished</p>
    </div>

    <div class="summary-card">
        <h2>{{ $cancelledBookings->count() }}</h2>
        <p>Cancelled</p>
    </div>

    <div class="summary-card">
        <h2>{{ $followUpBookings->count() }}</h2>
        <p>Backjob / Follow-up</p>
    </div>

    <div class="summary-card">
        <h2>{{ $remainingBookings }}</h2>
        <p>Remaining Active Slots</p>
    </div>
</div>

<div class="grid">
    <section class="card">
        <div class="card-header">Upcoming Appointment</div>

        <div class="card-body">
            @if ($upcomingAppointment)
                @php
                    $statusKey = strtolower(str_replace(' ', '-', $upcomingAppointment->status));
                    $statusLabel = ucwords(str_replace('-', ' ', $statusKey));

                    $bookingType = $upcomingAppointment->booking_type === 'followup' ? 'followup' : 'regular';
                    $bookingTypeLabel = $bookingType === 'followup' ? 'Backjob / Follow-up' : 'Regular Booking';

                    $mainService = $upcomingAppointment->services->first()?->service_name ?? 'Beauty Service';

                    $appointmentDate = $upcomingAppointment->appointment_date
                        ? \Carbon\Carbon::parse($upcomingAppointment->appointment_date)->format('F d, Y')
                        : 'Not set';

                    $startTime = $upcomingAppointment->start_time
                        ? \Carbon\Carbon::parse($upcomingAppointment->start_time)->format('h:i A')
                        : 'Not set';

                    $endTime = $upcomingAppointment->end_time
                        ? \Carbon\Carbon::parse($upcomingAppointment->end_time)->format('h:i A')
                        : null;
                @endphp

                <div class="appointment-box">
                    <div class="appointment-top">
                        <div>
                            <h3>{{ $mainService }}</h3>
                            <p>
                                Booking ID:
                                #BK-{{ str_pad($upcomingAppointment->id, 4, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>

                        <div class="action-row">
                            <span class="type-badge {{ $bookingType }}">
                                {{ $bookingTypeLabel }}
                            </span>

                            <span class="status {{ $statusKey }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="detail-grid">
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
                            <small>STAFF</small>
                            <p>{{ $upcomingAppointment->staff?->full_name ?? 'Not assigned' }}</p>
                        </div>

                        <div class="detail-box">
                            <small>TOTAL</small>
                            <p>RM {{ number_format($upcomingAppointment->total_price, 2) }}</p>
                        </div>
                    </div>

                    <div class="action-row">
                        <a
                            href="{{ route('customer.booking.details', $upcomingAppointment->id) }}"
                            class="action-btn primary-btn"
                        >
                            View Details
                        </a>

                        <a href="{{ route('customer.history') }}" class="action-btn light-btn">
                            View History
                        </a>
                    </div>
                </div>
            @else
                <div class="empty-box">
                    <div class="empty-icon">🗓️</div>
                    <h3>No upcoming appointments</h3>
                    <p>You do not have any active appointment yet.</p>
                    <a href="{{ route('customer.booking') }}">Book one now</a>
                </div>
            @endif
        </div>
    </section>

    <section class="card">
        <div class="card-header">Notifications</div>

        <div class="card-body">
            @foreach ($notifications as $notification)
                <div class="notice">
                    <div class="dot"></div>
                    <div>
                        <h4>{{ $notification['title'] }}</h4>
                        <p>{{ $notification['message'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>

<div class="lower-grid">
    <section class="card">
        <div class="card-header">Booking History Summary</div>

        <div class="card-body">
            <div class="history-section">
                <h4>ACTIVE BOOKINGS</h4>
                <p>{{ $activeBookings->count() }} active booking(s)</p>
            </div>

            <div class="history-section">
                <h4>FINISHED BOOKINGS</h4>
                <p>{{ $finishedBookings->count() }} finished booking(s)</p>
            </div>

            <div class="history-section">
                <h4>CANCELLED BOOKINGS</h4>
                <p>{{ $cancelledBookings->count() }} cancelled booking(s)</p>
            </div>

            <div class="history-section">
                <h4>NO-SHOW BOOKINGS</h4>
                <p>{{ $noShowBookings->count() }} no-show booking(s)</p>
            </div>

            <div class="history-section">
                <h4>BACKJOB / FOLLOW-UP</h4>
                <p>{{ $followUpBookings->count() }} follow-up booking(s)</p>
            </div>

            <div class="action-row" style="margin-top:16px;">
                <a href="{{ route('customer.history') }}" class="action-btn primary-btn">
                    Open Booking History
                </a>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">Favorite / Recommended Services</div>

        <div class="card-body">
            @forelse ($favoriteServices as $service)
                @php
                    $image = $service->image_path
                        ? asset('storage/' . $service->image_path)
                        : $defaultServiceImage;
                @endphp

                <div class="service-item">
                    <img src="{{ $image }}" alt="{{ $service->service_name }}">

                    <div>
                        <h4>{{ $service->service_name }}</h4>

                        <p>
                            RM {{ number_format($service->price, 2) }}

                            @if (isset($service->times_booked))
                                · Booked {{ $service->times_booked }} time(s)
                            @else
                                · {{ $service->duration }} minutes
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="empty-box">
                    <h3>No services available</h3>
                    <p>Please add services first in admin service management.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

@endsection