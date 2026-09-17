@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Booking Details View
|
| Purpose:
| - Shows complete appointment transaction details.
| - Displays selected services, assigned staff, schedule, total amount,
|   booking type, cancellation information, and timeline.
| - Shows backjob/follow-up connection when applicable.
|
| Defense explanation:
| This page improves transparency because customers can verify all important
| information about a booking in one organized page.
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

.card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 28px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.booking-header {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: flex-start;
    margin-bottom: 22px;
}

.booking-header p {
    color: var(--muted);
    margin-top: 6px;
    font-size: 14px;
}

.badge-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.status,
.type-badge {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
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

.details-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 18px;
    margin: 22px 0;
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
}

.section-title {
    margin: 26px 0 14px;
}

.service-box,
.followup-box,
.warning-box {
    padding: 18px;
    border-radius: 22px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    margin-bottom: 12px;
}

.service-box p,
.followup-box p,
.warning-box p {
    color: var(--muted);
    font-size: 13px;
    margin-top: 6px;
    line-height: 1.6;
}

.followup-box {
    background: rgba(138,113,88,.10);
    border: 1px solid rgba(138,113,88,.22);
}

.warning-box {
    background: rgba(184,77,77,.08);
    border: 1px solid rgba(184,77,77,.2);
}

.warning-box h4 {
    color: #b84d4d;
    margin-bottom: 6px;
}

.timeline {
    margin-top: 18px;
}

.timeline-item {
    display: grid;
    grid-template-columns: 170px 1fr;
    gap: 18px;
    padding: 18px;
    border-radius: 22px;
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
    margin-bottom: 14px;
}

.timeline-time small {
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
}

.timeline-time p {
    font-weight: 800;
    margin-top: 6px;
    color: var(--brown);
}

.timeline-content h4 {
    margin-bottom: 6px;
}

.timeline-content p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.6;
}

.back-btn {
    display: inline-block;
    margin-top: 22px;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 999px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 800;
}

@media(max-width:900px) {
    .booking-header,
    .timeline-item {
        grid-template-columns: 1fr;
        flex-direction: column;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .badge-row {
        justify-content: flex-start;
    }
}
</style>
@endsection

@section('content')

@php
    $normalizedStatus = strtolower(str_replace(' ', '-', $appointment->status));
    $statusLabel = ucwords(str_replace('-', ' ', $normalizedStatus));

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

    $createdAt = $appointment->created_at
        ? \Carbon\Carbon::parse($appointment->created_at)->format('F d, Y h:i A')
        : 'Not recorded';

    $updatedAt = $appointment->status_updated_at
        ? \Carbon\Carbon::parse($appointment->status_updated_at)->format('F d, Y h:i A')
        : null;

    $cancelledAt = $appointment->cancelled_at
        ? \Carbon\Carbon::parse($appointment->cancelled_at)->format('F d, Y h:i A')
        : null;

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

<div class="page-header">
    <h1>Booking Details</h1>
    <p>View complete information about this appointment transaction.</p>
</div>

<section class="card">

    <div class="booking-header">
        <div>
            <h2>{{ $mainService }}</h2>

            <p>
                Booking ID: #BK-{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}
            </p>

            <p>
                Transaction Type:
                {{ $bookingTypeLabel }}
            </p>
        </div>

        <div class="badge-row">
            <span class="type-badge {{ $bookingType }}">
                {{ $bookingTypeLabel }}
            </span>

            <span class="status {{ $normalizedStatus }}">
                {{ $statusLabel }}
            </span>
        </div>
    </div>

    <div class="details-grid">
        <div class="detail-box">
            <small>CUSTOMER</small>
            <p>{{ $appointment->customer?->full_name ?? $customer?->full_name ?? 'Customer' }}</p>
        </div>

        <div class="detail-box">
            <small>STAFF ASSIGNED</small>
            <p>{{ $appointment->staff?->full_name ?? 'Not assigned' }}</p>
        </div>

        <div class="detail-box">
            <small>APPOINTMENT DATE</small>
            <p>{{ $appointmentDate }}</p>
        </div>

        <div class="detail-box">
            <small>APPOINTMENT TIME</small>
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
            <small>BOOKING STATUS</small>
            <p>{{ $statusLabel }}</p>
        </div>

        <div class="detail-box">
            <small>PAYMENT METHOD</small>
            <p>Manual Payment</p>
        </div>

        <div class="detail-box">
            <small>TOTAL AMOUNT</small>
            <p>RM {{ number_format($appointment->total_price, 2) }}</p>
        </div>
    </div>

    <h3 class="section-title">
        {{ $bookingType === 'followup' ? 'Selected Backjob Services' : 'Selected Services' }}
    </h3>

    @forelse ($appointment->services as $service)
        <div class="service-box">
            <strong>{{ $service->service_name }}</strong>

            <p>
                Duration:
                {{ $service->pivot->duration ?? $service->duration ?? 0 }}
                minutes · Price:
                RM {{ number_format($service->pivot->price ?? $service->price ?? 0, 2) }}
            </p>

            @if ($service->description)
                <p>{{ $service->description }}</p>
            @endif
        </div>
    @empty
        <div class="service-box">
            <strong>No service recorded</strong>
            <p>This booking has no attached service record yet.</p>
        </div>
    @endforelse

    @if ($bookingType === 'followup')
        <h3 class="section-title">Backjob / Follow-up Connection</h3>

        <div class="followup-box">
            @if ($original)
                <strong>Linked Original Booking</strong>

                <p>
                    Original Booking:
                    #BK-{{ str_pad($original->id, 4, '0', STR_PAD_LEFT) }}
                </p>

                <p>
                    Original Services:
                    {{ $originalServices ?: 'No service recorded' }}
                </p>

                <p>
                    Original Staff:
                    {{ $original->staff?->full_name ?? 'Not assigned' }}
                </p>

                <p>
                    Original Duration:
                    {{ $originalDuration }} minutes
                </p>

                <p>
                    Selected Backjob Duration:
                    {{ $durationMinutes }} minutes
                </p>

                <p>
                    Reason:
                    {{ $appointment->follow_up_reason ?? 'No reason provided.' }}
                </p>
            @else
                <strong>No linked original booking</strong>

                <p>
                    This backjob booking has no linked original booking record.
                </p>
            @endif
        </div>
    @endif

    @if ($normalizedStatus === 'cancelled')
        <div class="warning-box">
            <h4>Cancellation Information</h4>

            <p>
                Reason:
                {{ $appointment->cancellation_reason ?? 'No cancellation reason recorded.' }}
            </p>

            @if ($cancelledAt)
                <p>
                    Cancelled At:
                    {{ $cancelledAt }}
                </p>
            @endif
        </div>
    @endif

    <h3 class="section-title">Booking Timeline</h3>

    <div class="timeline">

        <div class="timeline-item">
            <div class="timeline-time">
                <small>DATE & TIME</small>
                <p>{{ $createdAt }}</p>
            </div>

            <div class="timeline-content">
                <h4>Booking Created</h4>
                <p>
                    The appointment was recorded in the system.
                    @if ($bookingType === 'followup')
                        This backjob booking was linked to a finished service history.
                    @else
                        The customer selected the service, staff, date, and time.
                    @endif
                </p>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-time">
                <small>DATE & TIME</small>
                <p>{{ $createdAt }}</p>
            </div>

            <div class="timeline-content">
                <h4>Slot Reserved</h4>
                <p>
                    The selected schedule was reserved to prevent double booking with the assigned staff.
                </p>
            </div>
        </div>

        @if ($normalizedStatus === 'ongoing')
            <div class="timeline-item">
                <div class="timeline-time">
                    <small>DATE & TIME</small>
                    <p>{{ $updatedAt ?? 'Recently updated' }}</p>
                </div>

                <div class="timeline-content">
                    <h4>Service Ongoing</h4>
                    <p>The appointment status has been updated to ongoing.</p>
                </div>
            </div>
        @endif

        @if ($normalizedStatus === 'finished')
            <div class="timeline-item">
                <div class="timeline-time">
                    <small>DATE & TIME</small>
                    <p>{{ $updatedAt ?? 'Recently updated' }}</p>
                </div>

                <div class="timeline-content">
                    <h4>Service Finished</h4>
                    <p>The service was completed successfully and the appointment was marked as finished.</p>
                </div>
            </div>
        @endif

        @if ($normalizedStatus === 'cancelled')
            <div class="timeline-item">
                <div class="timeline-time">
                    <small>DATE & TIME</small>
                    <p>{{ $cancelledAt ?? $updatedAt ?? 'Recently updated' }}</p>
                </div>

                <div class="timeline-content">
                    <h4>Booking Cancelled</h4>
                    <p>The appointment was cancelled and the reserved slot was released.</p>
                </div>
            </div>
        @endif

        @if ($normalizedStatus === 'no-show')
            <div class="timeline-item">
                <div class="timeline-time">
                    <small>DATE & TIME</small>
                    <p>{{ $updatedAt ?? 'Recently updated' }}</p>
                </div>

                <div class="timeline-content">
                    <h4>No Show Recorded</h4>
                    <p>The appointment was marked as no-show.</p>
                </div>
            </div>
        @endif

    </div>

    <a href="{{ route('customer.history') }}" class="back-btn">
        Back to Booking History
    </a>

</section>

@endsection