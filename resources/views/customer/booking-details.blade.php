
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CUSTOMER BOOKING DETAILS
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Features:
| - Display complete customer-owned booking information
| - Preserve regular and follow-up booking details
| - Show services, duration, staff and original schedule
| - Show cancellation and emergency rescheduling records
| - Display booking timeline
| - Allow customers to reschedule eligible appointments
| - Display a seven-day appointment calendar
| - Disable Sundays and studio-wide emergency closure dates
| - Check appointment availability through Laravel
| - Preserve original booking ID, services and total price
|
| Security:
| - Appointment ownership is checked in the controller
| - Studio closure dates are enforced in the backend
| - CSRF protection applies to rescheduling
| - Live availability is verified before submission
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: DESIGN VARIABLES
|--------------------------------------------------------------------------
*/

.ns-booking-details {
    --ns-brown: #6f4e37;
    --ns-dark: #2f2118;
    --ns-muted: #7d6d60;
    --ns-border: rgba(111,78,55,.14);
    --ns-glass: rgba(255,255,255,.56);
    --ns-gradient: linear-gradient(135deg,#8a7158,#5d3f2c);
    --ns-large-radius: 24px;
    --ns-small-radius: 14px;

    width: 100%;
    min-width: 0;
    color: var(--ns-dark);
}

.ns-booking-details *,
.ns-booking-details *::before,
.ns-booking-details *::after {
    box-sizing: border-box;
}

.ns-booking-details button,
.ns-booking-details input,
.ns-booking-details select {
    font: inherit;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: PAGE HEADER
|--------------------------------------------------------------------------
*/

.ns-booking-details .page-header {
    margin-bottom: 28px;
}

.ns-booking-details .page-header h1 {
    margin: 0 0 8px;
    font-size: 34px;
    line-height: 1.2;
}

.ns-booking-details .page-header p {
    margin: 0;
    color: var(--ns-muted);
    font-size: 14px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: MAIN GLASS CARD
|--------------------------------------------------------------------------
*/

.ns-booking-details .details-card {
    padding: 28px;
    border-radius: var(--ns-large-radius);
    background: var(--glass,var(--ns-glass));
    border: 1px solid var(--border,rgba(255,255,255,.7));
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.ns-booking-details .booking-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 22px;
}

.ns-booking-details .booking-header h2 {
    margin: 0 0 8px;
    font-size: 22px;
}

.ns-booking-details .booking-header p {
    margin: 5px 0;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.6;
}

.ns-booking-details .badge-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: STATUS BADGES
|--------------------------------------------------------------------------
*/

.ns-booking-details .status,
.ns-booking-details .type-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.ns-booking-details .type-badge.regular {
    color: var(--ns-dark);
    border: 1px solid var(--ns-border);
    background: rgba(255,255,255,.55);
}

.ns-booking-details .type-badge.followup {
    background: rgba(138,113,88,.18);
    color: var(--ns-brown);
}

.ns-booking-details .status.pending {
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.ns-booking-details .status.approved {
    background: rgba(82,139,255,.15);
    color: #2f5fb8;
}

.ns-booking-details .status.ongoing {
    background: rgba(138,113,88,.18);
    color: var(--ns-brown);
}

.ns-booking-details .status.finished {
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
}

.ns-booking-details .status.cancelled,
.ns-booking-details .status.no-show {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

.ns-booking-details .status.rescheduling-required {
    background: rgba(197,126,46,.17);
    color: #93611f;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BOOKING INFORMATION GRID
|--------------------------------------------------------------------------
*/

.ns-booking-details .details-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 16px;
    margin: 22px 0;
}

.ns-booking-details .detail-box {
    min-width: 0;
    padding: 17px;
    border-radius: var(--ns-small-radius);
    background: rgba(255,255,255,.46);
    border: 1px solid rgba(255,255,255,.68);
}

.ns-booking-details .detail-box small {
    color: var(--ns-muted);
    font-size: 11px;
    font-weight: 800;
}

.ns-booking-details .detail-box p {
    margin: 7px 0 0;
    font-size: 14px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: DETAIL SECTIONS
|--------------------------------------------------------------------------
*/

.ns-booking-details .section-title {
    margin: 28px 0 14px;
    font-size: 18px;
}

.ns-booking-details .service-box,
.ns-booking-details .followup-box,
.ns-booking-details .warning-box,
.ns-booking-details .original-schedule,
.ns-booking-details .reschedule-panel {
    padding: 20px;
    margin-bottom: 13px;
    border-radius: var(--ns-small-radius);
    background: rgba(255,255,255,.46);
    border: 1px solid rgba(255,255,255,.68);
}

.ns-booking-details .service-box p,
.ns-booking-details .followup-box p,
.ns-booking-details .warning-box p,
.ns-booking-details .original-schedule p {
    margin: 7px 0 0;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
}

.ns-booking-details .followup-box {
    background: rgba(138,113,88,.09);
    border-color: rgba(138,113,88,.20);
}

.ns-booking-details .warning-box {
    background: rgba(184,77,77,.07);
    border-color: rgba(184,77,77,.17);
}

.ns-booking-details .warning-box h4 {
    margin: 0 0 8px;
    color: #b84d4d;
}

.ns-booking-details .original-schedule {
    background: rgba(138,113,88,.08);
    border-color: rgba(138,113,88,.18);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SUCCESS AND ERROR MESSAGES
|--------------------------------------------------------------------------
*/

.ns-booking-details .message-success,
.ns-booking-details .message-error {
    padding: 15px 18px;
    margin-bottom: 20px;
    border-radius: var(--ns-small-radius);
    font-size: 13px;
    line-height: 1.7;
}

.ns-booking-details .message-success {
    color: #386e3e;
    border: 1px solid rgba(81,148,91,.22);
    background: rgba(81,148,91,.10);
}

.ns-booking-details .message-error {
    color: #963b35;
    border: 1px solid rgba(184,77,77,.22);
    background: rgba(184,77,77,.09);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: EMERGENCY RESCHEDULING
|--------------------------------------------------------------------------
*/

.ns-booking-details .reschedule-notice {
    padding: 20px;
    margin: 22px 0;
    border: 1px solid rgba(197,126,46,.25);
    background: rgba(197,126,46,.09);
    border-radius: var(--ns-large-radius);
}

.ns-booking-details .reschedule-notice h3 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #795021;
}

.ns-booking-details .reschedule-notice p {
    margin: 0;
    color: #816947;
    font-size: 13px;
    line-height: 1.7;
}

.ns-booking-details .reschedule-panel {
    margin: 20px 0 25px;
    padding: 24px;
    border-radius: var(--ns-large-radius);
    background: rgba(255,255,255,.52);
    border: 1px solid var(--ns-border);
}

.ns-booking-details .reschedule-panel h3 {
    margin: 0 0 9px;
    font-size: 19px;
}

.ns-booking-details .reschedule-description {
    margin-bottom: 22px;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
}

.ns-booking-details .reschedule-label {
    display: block;
    margin-bottom: 10px;
    font-size: 13px;
    font-weight: 800;
}

.ns-booking-details .reschedule-select {
    width: 100%;
    min-height: 46px;
    padding: 12px 14px;
    margin-bottom: 20px;
    border-radius: var(--ns-small-radius);
    border: 1px solid var(--ns-border);
    background: rgba(255,255,255,.80);
    color: var(--ns-dark);
    outline: none;
    font-size: 13px;
}

.ns-booking-details .reschedule-select:focus {
    border-color: var(--ns-brown);
    box-shadow: 0 0 0 3px rgba(111,78,55,.10);
}

.ns-booking-details .reschedule-calendar-layout {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 20px;
}

.ns-booking-details .reschedule-inner-card {
    min-width: 0;
    padding: 18px;
    border-radius: var(--ns-small-radius);
    background: rgba(255,255,255,.50);
    border: 1px solid rgba(111,78,55,.10);
}

.ns-booking-details .reschedule-inner-card h4 {
    margin: 0 0 8px;
    font-size: 15px;
}

.ns-booking-details .reschedule-inner-card p {
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.6;
}

.ns-booking-details .reschedule-date-grid,
.ns-booking-details .reschedule-time-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 10px;
    margin-top: 16px;
}

.ns-booking-details .reschedule-date,
.ns-booking-details .reschedule-time {
    width: 100%;
    min-width: 0;
    min-height: 55px;
    padding: 12px 8px;
    border-radius: var(--ns-small-radius);
    border: 1px solid var(--ns-border);
    background: rgba(255,255,255,.75);
    color: var(--ns-dark);
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
}

.ns-booking-details .reschedule-date span,
.ns-booking-details .reschedule-time span,
.ns-booking-details .reschedule-date small,
.ns-booking-details .reschedule-time small {
    display: block;
}

.ns-booking-details .reschedule-date small,
.ns-booking-details .reschedule-time small {
    margin-top: 5px;
    font-size: 10px;
}

.ns-booking-details .reschedule-date.selected,
.ns-booking-details .reschedule-time.selected {
    background: var(--ns-gradient);
    color: white;
    border-color: transparent;
}

.ns-booking-details .reschedule-date:disabled,
.ns-booking-details .reschedule-time:disabled {
    cursor: not-allowed;
    opacity: .75;
    background: rgba(130,130,130,.12);
    color: #99928c;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CLOSED STUDIO DATES
|--------------------------------------------------------------------------
| Applies to Sundays and emergency closure dates
|--------------------------------------------------------------------------
*/

.ns-booking-details .reschedule-date.closed,
.ns-booking-details .reschedule-time.closed {
    border-color: rgba(184,77,77,.25);
    background: rgba(184,77,77,.10);
    color: #a33f3f;
}

.ns-booking-details .reschedule-date.closed:disabled {
    opacity: 1;
}

.ns-booking-details .reschedule-date.closed small {
    font-weight: 800;
    color: #a33f3f;
}

.ns-booking-details .reschedule-availability-note {
    margin-top: 15px;
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.7;
}

.ns-booking-details .reschedule-availability-note.error {
    color: #963b35;
}

.ns-booking-details .reschedule-availability-note.success {
    color: #386e3e;
}

.ns-booking-details .reschedule-summary {
    padding: 17px;
    margin-top: 22px;
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-small-radius);
    background: rgba(255,255,255,.56);
}

.ns-booking-details .reschedule-summary h4 {
    margin: 0 0 12px;
    font-size: 15px;
}

.ns-booking-details .reschedule-summary-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 0;
    font-size: 13px;
    border-bottom: 1px solid var(--ns-border);
}

.ns-booking-details .reschedule-summary-row:last-child {
    border-bottom: none;
}

.ns-booking-details .reschedule-summary-row strong {
    text-align: right;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BUTTONS
|--------------------------------------------------------------------------
*/

.ns-booking-details .booking-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 46px;
    padding: 12px 22px;
    border: 0;
    border-radius: var(--ns-small-radius);
    background: var(--ns-gradient);
    color: white;
    text-decoration: none;
    cursor: pointer;
    font-size: 13px;
    font-weight: 800;
}

.ns-booking-details .booking-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.ns-booking-details .reschedule-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.ns-booking-details .back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 24px;
    padding: 12px 20px;
    border-radius: var(--ns-small-radius);
    background: var(--ns-gradient);
    color: white;
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BOOKING TIMELINE
|--------------------------------------------------------------------------
*/

.ns-booking-details .timeline {
    margin-top: 18px;
}

.ns-booking-details .timeline-item {
    display: grid;
    grid-template-columns: 170px minmax(0,1fr);
    gap: 18px;
    padding: 18px;
    margin-bottom: 14px;
    border-radius: var(--ns-small-radius);
    background: rgba(255,255,255,.40);
    border: 1px solid rgba(255,255,255,.65);
}

.ns-booking-details .timeline-time small {
    color: var(--ns-muted);
    font-size: 11px;
    font-weight: 800;
}

.ns-booking-details .timeline-time p {
    margin: 6px 0 0;
    color: var(--ns-brown);
    font-size: 13px;
    font-weight: 800;
}

.ns-booking-details .timeline-content h4 {
    margin: 0 0 7px;
    font-size: 15px;
}

.ns-booking-details .timeline-content p {
    margin: 0;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: RESPONSIVE DESIGN
|--------------------------------------------------------------------------
*/

@media (max-width:1000px) {
    .ns-booking-details .details-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }
}

@media (max-width:750px) {
    .ns-booking-details .booking-header {
        flex-direction: column;
    }

    .ns-booking-details .badge-row {
        justify-content: flex-start;
    }

    .ns-booking-details .reschedule-calendar-layout {
        grid-template-columns: 1fr;
    }

    .ns-booking-details .timeline-item {
        grid-template-columns: 1fr;
        gap: 10px;
    }
}

@media (max-width:550px) {
    .ns-booking-details .page-header h1 {
        font-size: 28px;
    }

    .ns-booking-details .details-card {
        padding: 20px 16px;
    }

    .ns-booking-details .details-grid {
        grid-template-columns: 1fr;
    }

    .ns-booking-details .reschedule-panel {
        padding: 18px 14px;
    }

    .ns-booking-details .reschedule-summary-row {
        flex-direction: column;
    }

    .ns-booking-details .reschedule-summary-row strong {
        text-align: left;
    }

    .ns-booking-details .reschedule-actions .booking-btn,
    .ns-booking-details .back-btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking Status and Type
    |--------------------------------------------------------------------------
    */

    $normalizedStatus = strtolower(
        str_replace(
            [' ', '_'],
            '-',
            (string) $appointment->status
        )
    );

    $statusLabel = ucwords(
        str_replace('-', ' ', $normalizedStatus)
    );

    $requiresRescheduling =
        $appointment->requiresRescheduling();

    $bookingType =
        $appointment->booking_type === 'followup'
            ? 'followup'
            : 'regular';

    $bookingTypeLabel =
        $bookingType === 'followup'
            ? 'Backjob / Follow-up'
            : 'Regular Booking';

    $mainService =
        $appointment->services->first()?->service_name
        ?? 'Beauty Service';

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Current Appointment Details
    |--------------------------------------------------------------------------
    */

    $appointmentDate = $appointment->appointment_date
        ? \Carbon\Carbon::parse(
            $appointment->appointment_date
        )->format('F d, Y')
        : 'Not set';

    $startTime = $appointment->start_time
        ? \Carbon\Carbon::parse(
            $appointment->start_time
        )->format('h:i A')
        : 'Not set';

    $endTime = $appointment->end_time
        ? \Carbon\Carbon::parse(
            $appointment->end_time
        )->format('h:i A')
        : null;

    $durationMinutes = $appointment->services->sum(
        function ($service) {
            $pivotDuration = (int) (
                $service->pivot->duration ?? 0
            );

            return $pivotDuration > 0
                ? $pivotDuration
                : (int) ($service->duration ?? 0);
        }
    );

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Timeline Information
    |--------------------------------------------------------------------------
    */

    $createdAt = $appointment->created_at
        ? \Carbon\Carbon::parse(
            $appointment->created_at
        )->format('F d, Y h:i A')
        : 'Not recorded';

    $updatedAt = $appointment->status_updated_at
        ? \Carbon\Carbon::parse(
            $appointment->status_updated_at
        )->format('F d, Y h:i A')
        : null;

    $cancelledAt = $appointment->cancelled_at
        ? \Carbon\Carbon::parse(
            $appointment->cancelled_at
        )->format('F d, Y h:i A')
        : null;

    $reschedulingRequestedAt =
        $appointment->rescheduling_requested_at
            ? \Carbon\Carbon::parse(
                $appointment->rescheduling_requested_at
            )->format('F d, Y h:i A')
            : null;

    $rescheduledAt = $appointment->rescheduled_at
        ? \Carbon\Carbon::parse(
            $appointment->rescheduled_at
        )->format('F d, Y h:i A')
        : null;

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Original Schedule
    |--------------------------------------------------------------------------
    */

    $originalDate = $appointment->original_appointment_date
        ? \Carbon\Carbon::parse(
            $appointment->original_appointment_date
        )->format('F d, Y')
        : $appointmentDate;

    $originalTime = $appointment->original_start_time
        ? \Carbon\Carbon::parse(
            $appointment->original_start_time
        )->format('h:i A')
        : $startTime;

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Follow-Up Booking Information
    |--------------------------------------------------------------------------
    */

    $original = $appointment->followUpAppointment;

    $originalServices = $original
        ? $original->services
            ->pluck('service_name')
            ->join(', ')
        : null;

    $originalDuration = $original
        ? $original->services->sum(
            function ($service) {
                $pivotDuration = (int) (
                    $service->pivot->duration ?? 0
                );

                return $pivotDuration > 0
                    ? $pivotDuration
                    : (int) ($service->duration ?? 0);
            }
        )
        : 0;

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Rescheduling Data
    |--------------------------------------------------------------------------
    */

    $reschedulingServiceIds = $appointment->services
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();

    $reschedulingBookingType = $bookingType;

    $reschedulingFollowUpId =
        $bookingType === 'followup'
            ? $appointment->follow_up_appointment_id
            : null;

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: ACTIVE STUDIO CLOSURE DATES
    |--------------------------------------------------------------------------
    | These records are supplied by BookingDetailsController
    | Convert dates to YYYY-MM-DD for safe JavaScript comparison
    |--------------------------------------------------------------------------
    */

    $reschedulingClosureRanges = collect(
        $reschedulingStudioClosures ?? []
    )
        ->filter(
            fn ($closure) =>
                $closure->status === 'active'
        )
        ->map(
            function ($closure) {
                return [
                    'start' => $closure->start_date
                        ->format('Y-m-d'),
                    'end' => $closure->end_date
                        ->format('Y-m-d'),
                    'reason' => (string) $closure->reason,
                    'title' => (string) $closure->title,
                ];
            }
        )
        ->values()
        ->all();
@endphp

<div class="ns-booking-details">

    {{-- NS BEAUTY COMMENT: Page Header --}}

    <header class="page-header">
        <h1>Booking Details</h1>

        <p>
            Review your appointment information,
            services, schedule, and transaction history
        </p>
    </header>

    {{-- NS BEAUTY COMMENT: Success Message --}}

    @if (session('success'))
        <div class="message-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    {{-- NS BEAUTY COMMENT: Validation Errors --}}

    @if ($errors->any())
        <div class="message-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="details-card">

        {{-- NS BEAUTY COMMENT: Booking Header --}}

        <div class="booking-header">

            <div>
                <h2>{{ $mainService }}</h2>

                <p>
                    Booking ID:
                    <strong>
                        #BK-{{ str_pad(
                            $appointment->id,
                            4,
                            '0',
                            STR_PAD_LEFT
                        ) }}
                    </strong>
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

        {{-- NS BEAUTY COMMENT: Main Booking Information --}}

        <div class="details-grid">

            <div class="detail-box">
                <small>CUSTOMER</small>
                <p>
                    {{ $appointment->customer?->full_name
                        ?? $customer?->full_name
                        ?? 'Customer' }}
                </p>
            </div>

            <div class="detail-box">
                <small>STAFF ASSIGNED</small>
                <p>
                    {{ $appointment->staff?->full_name
                        ?? 'Not assigned' }}
                </p>
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
                        – {{ $endTime }}
                    @endif

                    MYT
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
                <p>
                    RM {{ number_format(
                        (float) $appointment->total_price,
                        2
                    ) }}
                </p>
            </div>

        </div>

        {{-- NS BEAUTY COMMENT: Selected Services --}}

        <h3 class="section-title">
            {{ $bookingType === 'followup'
                ? 'Selected Backjob Services'
                : 'Selected Services' }}
        </h3>

        @forelse ($appointment->services as $service)

            <div class="service-box">

                <strong>
                    {{ $service->service_name }}
                </strong>

                <p>
                    Duration:
                    {{ $service->pivot->duration
                        ?? $service->duration
                        ?? 0 }}
                    minutes

                    · Price:
                    RM {{ number_format(
                        (float) (
                            $service->pivot->price
                            ?? $service->price
                            ?? 0
                        ),
                        2
                    ) }}
                </p>

                @if ($service->description)
                    <p>{{ $service->description }}</p>
                @endif

            </div>

        @empty

            <div class="service-box">
                <strong>No service recorded</strong>

                <p>
                    This booking has no attached service record
                </p>
            </div>

        @endforelse

        {{-- NS BEAUTY COMMENT: Follow-Up Information --}}

        @if ($bookingType === 'followup')

            <h3 class="section-title">
                Backjob / Follow-up Connection
            </h3>

            <div class="followup-box">

                @if ($original)

                    <strong>Linked Original Booking</strong>

                    <p>
                        Original Booking:
                        #BK-{{ str_pad(
                            $original->id,
                            4,
                            '0',
                            STR_PAD_LEFT
                        ) }}
                    </p>

                    <p>
                        Original Services:
                        {{ $originalServices
                            ?: 'No service recorded' }}
                    </p>

                    <p>
                        Original Staff:
                        {{ $original->staff?->full_name
                            ?? 'Not assigned' }}
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
                        {{ $appointment->follow_up_reason
                            ?? 'No reason provided' }}
                    </p>

                @else

                    <strong>No linked original booking</strong>

                    <p>
                        This backjob booking has no linked
                        original booking record
                    </p>

                @endif

            </div>

        @endif

        {{-- NS BEAUTY COMMENT: Emergency Rescheduling Notice --}}

        @if ($requiresRescheduling)

            <div class="reschedule-notice" role="status">

                <h3>Appointment Rescheduling Required</h3>

                <p>
                    Your existing appointment needs a new schedule
                    because its original schedule is unavailable

                    Choose another available date, time, and qualified
                    beautician below

                    Your booking ID, services, and total amount
                    will remain unchanged
                </p>

                @if ($appointment->rescheduling_reason)
                    <p style="margin-top:12px">
                        <strong>Reason:</strong>
                        {{ $appointment->rescheduling_reason }}
                    </p>
                @endif

            </div>

        @endif

        {{-- NS BEAUTY COMMENT: Original Appointment Information --}}

        @if (
            $appointment->original_appointment_date ||
            $appointment->original_start_time
        )

            <h3 class="section-title">
                Original Appointment Information
            </h3>

            <div class="original-schedule">

                <strong>Original Schedule</strong>

                <p>
                    Date: {{ $originalDate }}
                </p>

                <p>
                    Time: {{ $originalTime }} MYT
                </p>

                @if ($appointment->originalStaff)
                    <p>
                        Original Staff:
                        {{ $appointment->originalStaff->full_name }}
                    </p>
                @elseif ($appointment->original_staff_id)
                    <p>
                        Original Staff ID:
                        {{ $appointment->original_staff_id }}
                    </p>
                @endif

                <p>
                    These original appointment details are
                    retained for transaction history
                </p>

            </div>

        @endif

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: EMERGENCY CUSTOMER RESCHEDULING
        |--------------------------------------------------------------------------
        --}}

        @if ($requiresRescheduling)

            <section
                class="reschedule-panel"
                id="emergencyReschedulingSection"
            >

                <h3>Select a New Appointment Schedule</h3>

                <p class="reschedule-description">
                    Choose a qualified beautician, date, and time

                    Appointments are available within seven days,
                    including today

                    Sundays and dates covered by studio-wide
                    emergency closures are unavailable

                    Your existing services and total amount
                    cannot be changed
                </p>

                <div
                    id="rescheduleError"
                    class="message-error"
                    role="alert"
                    style="display:none"
                ></div>

                <form
                    id="emergencyRescheduleForm"
                    method="POST"
                    action="{{ route(
                        'customer.booking.reschedule',
                        $appointment->id
                    ) }}"
                >
                    @csrf
                    @method('PATCH')

                    {{-- NS BEAUTY COMMENT: Selected Date and Time --}}

                    <input
                        type="hidden"
                        name="appointment_date"
                        id="rescheduleDateInput"
                    >

                    <input
                        type="hidden"
                        name="start_time"
                        id="rescheduleTimeInput"
                    >

                    {{-- NS BEAUTY COMMENT: Qualified Beautician --}}

                    <label
                        for="rescheduleStaffInput"
                        class="reschedule-label"
                    >
                        Replacement Beautician
                    </label>

                    <select
                        name="staff_id"
                        id="rescheduleStaffInput"
                        class="reschedule-select"
                        required
                    >
                        <option value="">
                            Select a qualified beautician
                        </option>

                        @foreach (
                            ($reschedulingStaff ?? collect())
                            as $staff
                        )
                            <option value="{{ $staff->id }}">
                                {{ $staff->full_name }}
                            </option>
                        @endforeach

                    </select>

                    @if (
                        ($reschedulingStaff ?? collect())
                            ->isEmpty()
                    )

                        <div class="message-error">
                            No qualified beauticians are currently
                            available for these services

                            Please contact Nail Studio & Beauty
                            for assistance
                        </div>

                    @endif

                    {{-- NS BEAUTY COMMENT: Date and Time Selection --}}

                    <div class="reschedule-calendar-layout">

                        <div class="reschedule-inner-card">

                            <h4>Choose Date</h4>

                            <p>
                                Monday to Saturday · Malaysia Time
                            </p>

                            <div
                                id="rescheduleDateGrid"
                                class="reschedule-date-grid"
                            ></div>

                            <p
                                class="reschedule-availability-note"
                            >
                                Closed dates cannot be selected
                            </p>

                        </div>

                        <div class="reschedule-inner-card">

                            <h4>Choose Time</h4>

                            <p>
                                Available times depend on
                                your beautician and service duration
                            </p>

                            <div
                                id="rescheduleTimeGrid"
                                class="reschedule-time-grid"
                                aria-live="polite"
                            ></div>

                            <p
                                id="rescheduleAvailabilityNote"
                                class="reschedule-availability-note"
                                role="status"
                                aria-live="polite"
                            >
                                Select a beautician and date
                                to check availability
                            </p>

                        </div>

                    </div>

                    {{-- NS BEAUTY COMMENT: New Schedule Summary --}}

                    <div class="reschedule-summary">

                        <h4>New Appointment Summary</h4>

                        <div class="reschedule-summary-row">
                            <span>Booking ID</span>
                            <strong>
                                BK-{{ str_pad(
                                    $appointment->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                            </strong>
                        </div>

                        <div class="reschedule-summary-row">
                            <span>Beautician</span>
                            <strong id="rescheduleSummaryStaff">
                                Not selected
                            </strong>
                        </div>

                        <div class="reschedule-summary-row">
                            <span>Appointment Date</span>
                            <strong id="rescheduleSummaryDate">
                                Not selected
                            </strong>
                        </div>

                        <div class="reschedule-summary-row">
                            <span>Appointment Time</span>
                            <strong id="rescheduleSummaryTime">
                                Not selected
                            </strong>
                        </div>

                        <div class="reschedule-summary-row">
                            <span>Service Duration</span>
                            <strong>
                                {{ $durationMinutes }} minutes
                            </strong>
                        </div>

                        <div class="reschedule-summary-row">
                            <span>Total Amount</span>
                            <strong>
                                RM {{ number_format(
                                    (float) $appointment->total_price,
                                    2
                                ) }}
                            </strong>
                        </div>

                    </div>

                    <div class="reschedule-actions">

                        <button
                            type="submit"
                            id="rescheduleSubmitButton"
                            class="booking-btn"
                            disabled
                        >
                            Confirm New Schedule
                        </button>

                    </div>

                </form>

            </section>

        @endif

        {{-- NS BEAUTY COMMENT: Cancellation Information --}}

        @if ($normalizedStatus === 'cancelled')

            <h3 class="section-title">
                Cancellation Information
            </h3>

            <div class="warning-box">

                <h4>Booking Cancelled</h4>

                <p>
                    Reason:
                    {{ $appointment->cancellation_reason
                        ?? 'No cancellation reason recorded' }}
                </p>

                @if ($cancelledAt)
                    <p>
                        Cancelled At:
                        {{ $cancelledAt }}
                    </p>
                @endif

            </div>

        @endif

        {{-- NS BEAUTY COMMENT: Booking Timeline --}}

        <h3 class="section-title">
            Booking Timeline
        </h3>

        <div class="timeline">

            <div class="timeline-item">
                <div class="timeline-time">
                    <small>DATE &amp; TIME</small>
                    <p>{{ $createdAt }}</p>
                </div>

                <div class="timeline-content">
                    <h4>Booking Created</h4>
                    <p>
                        The appointment was recorded in the system

                        @if ($bookingType === 'followup')
                            This backjob was linked to a
                            finished service history
                        @else
                            The customer selected the service,
                            beautician, date, and time
                        @endif
                    </p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-time">
                    <small>DATE &amp; TIME</small>
                    <p>{{ $createdAt }}</p>
                </div>

                <div class="timeline-content">
                    <h4>Slot Reserved</h4>
                    <p>
                        The original appointment schedule
                        was reserved for the assigned beautician
                    </p>
                </div>
            </div>

            @if ($appointment->rescheduling_requested_at)

                <div class="timeline-item">
                    <div class="timeline-time">
                        <small>DATE &amp; TIME</small>
                        <p>
                            {{ $reschedulingRequestedAt
                                ?? 'Not recorded' }}
                        </p>
                    </div>

                    <div class="timeline-content">
                        <h4>Rescheduling Requested</h4>
                        <p>
                            The original appointment was affected
                            and a replacement schedule was requested
                        </p>

                        @if ($appointment->rescheduling_reason)
                            <p style="margin-top:7px">
                                Reason:
                                {{ $appointment->rescheduling_reason }}
                            </p>
                        @endif
                    </div>
                </div>

            @endif

            @if ($appointment->rescheduled_at)

                <div class="timeline-item">
                    <div class="timeline-time">
                        <small>DATE &amp; TIME</small>
                        <p>{{ $rescheduledAt }}</p>
                    </div>

                    <div class="timeline-content">
                        <h4>Appointment Rescheduled</h4>
                        <p>
                            The schedule was updated while preserving
                            the booking ID, services, and total amount
                        </p>
                    </div>
                </div>

            @endif

            @if ($requiresRescheduling)

                <div class="timeline-item">
                    <div class="timeline-time">
                        <small>CURRENT STATUS</small>
                        <p>Awaiting Selection</p>
                    </div>

                    <div class="timeline-content">
                        <h4>Customer Rescheduling Required</h4>
                        <p>
                            Please select a new appointment date,
                            time, and qualified beautician
                        </p>
                    </div>
                </div>

            @endif

            @if ($normalizedStatus === 'ongoing')

                <div class="timeline-item">
                    <div class="timeline-time">
                        <small>DATE &amp; TIME</small>
                        <p>{{ $updatedAt ?? 'Recently updated' }}</p>
                    </div>

                    <div class="timeline-content">
                        <h4>Service Ongoing</h4>
                        <p>
                            The appointment status was updated
                            to ongoing
                        </p>
                    </div>
                </div>

            @endif

            @if ($normalizedStatus === 'finished')

                <div class="timeline-item">
                    <div class="timeline-time">
                        <small>DATE &amp; TIME</small>
                        <p>{{ $updatedAt ?? 'Recently updated' }}</p>
                    </div>

                    <div class="timeline-content">
                        <h4>Service Finished</h4>
                        <p>
                            The service was completed successfully
                            and the appointment was marked as finished
                        </p>
                    </div>
                </div>

            @endif

            @if ($normalizedStatus === 'cancelled')

                <div class="timeline-item">
                    <div class="timeline-time">
                        <small>DATE &amp; TIME</small>
                        <p>
                            {{ $cancelledAt
                                ?? $updatedAt
                                ?? 'Recently updated' }}
                        </p>
                    </div>

                    <div class="timeline-content">
                        <h4>Booking Cancelled</h4>
                        <p>
                            The appointment was cancelled
                            and its reserved slot was released
                        </p>
                    </div>
                </div>

            @endif

            @if ($normalizedStatus === 'no-show')

                <div class="timeline-item">
                    <div class="timeline-time">
                        <small>DATE &amp; TIME</small>
                        <p>{{ $updatedAt ?? 'Recently updated' }}</p>
                    </div>

                    <div class="timeline-content">
                        <h4>No Show Recorded</h4>
                        <p>
                            The appointment was marked as no-show
                        </p>
                    </div>
                </div>

            @endif

        </div>

        <a
            href="{{ route('customer.history') }}"
            class="back-btn"
        >
            Back to Booking History
        </a>

    </section>

</div>

@endsection

@section('scripts')

@if ($requiresRescheduling)

<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CUSTOMER EMERGENCY RESCHEDULING
|--------------------------------------------------------------------------
| - Seven-day Malaysian appointment calendar
| - Sunday closure
| - Emergency studio closure dates
| - Live availability
| - Qualified beautician selection
| - Protected Laravel PATCH submission
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Laravel Data
    |--------------------------------------------------------------------------
    */

    const availabilityEndpoint = @js(
        route('customer.booking.availability')
    );

    const malaysiaTimezone = "Asia/Kuala_Lumpur";

    const bookingType = @js($reschedulingBookingType);

    const linkedFollowUpId = @js(
        $reschedulingFollowUpId
    );

    const serviceIds = @js(
        $reschedulingServiceIds
    );

    const recordedDuration = {{ (int) $durationMinutes }};

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Studio-Wide Closure Records
    |--------------------------------------------------------------------------
    | Only active closures are supplied by the controller
    |
    | Example:
    | {
    |   start: "2026-10-16",
    |   end: "2026-10-18",
    |   title: "Emergency Closure",
    |   reason: "Flooding"
    | }
    |--------------------------------------------------------------------------
    */

    const studioClosures = @js(
        $reschedulingClosureRanges
    );

    const bookingTimes = [
        "10:00",
        "10:30",
        "11:00",
        "11:30",
        "12:00",
        "12:30",
        "13:00",
        "13:30",
        "14:00",
        "14:30",
        "15:00",
        "15:30",
        "16:00"
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Form Elements
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById(
        "emergencyRescheduleForm"
    );

    const staffInput = document.getElementById(
        "rescheduleStaffInput"
    );

    const dateInput = document.getElementById(
        "rescheduleDateInput"
    );

    const timeInput = document.getElementById(
        "rescheduleTimeInput"
    );

    const dateGrid = document.getElementById(
        "rescheduleDateGrid"
    );

    const timeGrid = document.getElementById(
        "rescheduleTimeGrid"
    );

    const note = document.getElementById(
        "rescheduleAvailabilityNote"
    );

    const submitButton = document.getElementById(
        "rescheduleSubmitButton"
    );

    const errorBox = document.getElementById(
        "rescheduleError"
    );

    if (
        !form ||
        !staffInput ||
        !dateInput ||
        !timeInput ||
        !dateGrid ||
        !timeGrid ||
        !note ||
        !submitButton ||
        !errorBox
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Rescheduling State
    |--------------------------------------------------------------------------
    */

    let selectedDate = "";
    let selectedTime = "";

    let availabilitySlots = [];
    let availabilityVerified = false;
    let availabilityLoading = false;
    let availabilityFingerprint = "";

    let requestVersion = 0;
    let requestController = null;
    let submissionStarted = false;

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Display Error
    |--------------------------------------------------------------------------
    */

    function showError(message) {
        errorBox.textContent = message;
        errorBox.style.display = "block";

        errorBox.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });
    }

    function hideError() {
        errorBox.style.display = "none";
        errorBox.textContent = "";
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Current Malaysia Date and Time
    |--------------------------------------------------------------------------
    */

    function malaysiaParts() {
        const parts = new Intl.DateTimeFormat(
            "en-GB",
            {
                timeZone: malaysiaTimezone,
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
                hourCycle: "h23"
            }
        ).formatToParts(new Date());

        const values = {};

        parts.forEach(part => {
            if (part.type !== "literal") {
                values[part.type] = part.value;
            }
        });

        return {
            date:
                `${values.year}-${values.month}-${values.day}`,
            time:
                `${values.hour}:${values.minute}`
        };
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Date Utilities
    |--------------------------------------------------------------------------
    */

    function addDays(dateString, days) {
        const [year, month, day] = dateString
            .split("-")
            .map(Number);

        return new Date(
            Date.UTC(
                year,
                month - 1,
                day + days,
                12
            )
        ).toISOString().slice(0, 10);
    }

    function getBookingDates() {
        const today = malaysiaParts().date;

        return Array.from(
            { length: 7 },
            (_, index) => addDays(today, index)
        );
    }

    function isDateAllowed(dateString) {
        return getBookingDates().includes(
            dateString
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Sunday Closure Check
    |--------------------------------------------------------------------------
    */

    function isSunday(dateString) {
        if (
            !/^\d{4}-\d{2}-\d{2}$/.test(
                dateString || ""
            )
        ) {
            return false;
        }

        const [year, month, day] = dateString
            .split("-")
            .map(Number);

        return new Date(
            Date.UTC(
                year,
                month - 1,
                day,
                12
            )
        ).getUTCDay() === 0;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Emergency Closure Lookup
    |--------------------------------------------------------------------------
    | Closure date boundaries are inclusive
    |--------------------------------------------------------------------------
    */

    function getEmergencyClosure(dateString) {
        return studioClosures.find(closure => {
            return (
                closure.start <= dateString &&
                closure.end >= dateString
            );
        }) || null;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Combined Studio Closure Check
    |--------------------------------------------------------------------------
    */

    function isStudioClosed(dateString) {
        return (
            isSunday(dateString) ||
            getEmergencyClosure(dateString) !== null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Closed Date Explanation
    |--------------------------------------------------------------------------
    */

    function getClosureMessage(dateString) {
        if (isSunday(dateString)) {
            return "The studio is closed every Sunday";
        }

        const closure = getEmergencyClosure(
            dateString
        );

        if (closure) {
            return (
                "Studio temporarily closed — " +
                (closure.reason || closure.title ||
                    "Emergency closure")
            );
        }

        return "";
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Date Formatting
    |--------------------------------------------------------------------------
    */

    function displayDate(dateString) {
        if (!dateString) {
            return "Not selected";
        }

        const [year, month, day] = dateString
            .split("-")
            .map(Number);

        return new Intl.DateTimeFormat(
            "en-MY",
            {
                timeZone: "UTC",
                weekday: "short",
                month: "short",
                day: "numeric",
                year: "numeric"
            }
        ).format(
            new Date(
                Date.UTC(
                    year,
                    month - 1,
                    day,
                    12
                )
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Time Formatting
    |--------------------------------------------------------------------------
    */

    function displayTime(timeString) {
        if (!timeString) {
            return "Not selected";
        }

        const [hour, minute] = timeString
            .split(":")
            .map(Number);

        const period = hour >= 12
            ? "PM"
            : "AM";

        const hour12 = hour % 12 || 12;

        return (
            `${hour12}:` +
            `${String(minute).padStart(2, "0")} ` +
            period
        );
    }

    function isPastOrStarted(dateString, timeString) {
        const now = malaysiaParts();

        return (
            dateString < now.date ||
            (
                dateString === now.date &&
                timeString <= now.time
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Availability Selection Fingerprint
    |--------------------------------------------------------------------------
    */

    function currentFingerprint() {
        return [
            selectedDate,
            staffInput.value,
            bookingType,
            linkedFollowUpId || "",
            serviceIds.join(",")
        ].join("|");
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Clear Selected Time
    |--------------------------------------------------------------------------
    */

    function clearSelectedTime() {
        selectedTime = "";
        timeInput.value = "";
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Invalidate Availability
    |--------------------------------------------------------------------------
    */

    function invalidateAvailability() {
        requestVersion++;

        if (requestController) {
            requestController.abort();
            requestController = null;
        }

        availabilitySlots = [];
        availabilityVerified = false;
        availabilityLoading = false;
        availabilityFingerprint = "";

        clearSelectedTime();
        renderTimes();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Update Schedule Summary
    |--------------------------------------------------------------------------
    */

    function updateSummary() {
        const selectedOption =
            staffInput.options[
                staffInput.selectedIndex
            ];

        document.getElementById(
            "rescheduleSummaryStaff"
        ).textContent =
            staffInput.value
                ? selectedOption.textContent.trim()
                : "Not selected";

        document.getElementById(
            "rescheduleSummaryDate"
        ).textContent =
            displayDate(selectedDate);

        document.getElementById(
            "rescheduleSummaryTime"
        ).textContent =
            selectedTime
                ? displayTime(selectedTime) + " MYT"
                : "Not selected";
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Render Seven-Day Calendar
    |--------------------------------------------------------------------------
    | Sundays and emergency closure dates are disabled
    |--------------------------------------------------------------------------
    */

    function renderDates() {
        dateGrid.innerHTML = "";

        getBookingDates().forEach(
            dateString => {
                const closed = isStudioClosed(
                    dateString
                );

                const emergencyClosure =
                    getEmergencyClosure(dateString);

                const button =
                    document.createElement("button");

                button.type = "button";
                button.className = "reschedule-date";
                button.disabled = closed;

                if (closed) {
                    button.classList.add("closed");

                    button.title =
                        getClosureMessage(dateString);
                }

                if (
                    selectedDate === dateString &&
                    !closed
                ) {
                    button.classList.add("selected");
                }

                const dateLabel =
                    document.createElement("span");

                dateLabel.textContent =
                    displayDate(dateString);

                const statusLabel =
                    document.createElement("small");

                if (isSunday(dateString)) {
                    statusLabel.textContent =
                        "Sunday Closed";
                } else if (emergencyClosure) {
                    statusLabel.textContent =
                        "Emergency Closed";
                } else {
                    statusLabel.textContent =
                        "Select";
                }

                button.appendChild(dateLabel);
                button.appendChild(statusLabel);

                if (!closed) {
                    button.addEventListener(
                        "click",
                        () => selectDate(dateString)
                    );
                }

                dateGrid.appendChild(button);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Invalidate Selected Closed Dates
        |--------------------------------------------------------------------------
        */

        if (
            selectedDate &&
            (
                !isDateAllowed(selectedDate) ||
                isStudioClosed(selectedDate)
            )
        ) {
            selectedDate = "";
            dateInput.value = "";

            invalidateAvailability();
            updateSummary();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Select Date
    |--------------------------------------------------------------------------
    */

    function selectDate(dateString) {
        hideError();

        if (
            !isDateAllowed(dateString) ||
            isStudioClosed(dateString)
        ) {
            showError(
                getClosureMessage(dateString) ||
                "The selected date is unavailable"
            );

            return;
        }

        selectedDate = dateString;
        dateInput.value = dateString;

        invalidateAvailability();
        renderDates();
        updateSummary();
        refreshAvailability();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Check Verified Slot
    |--------------------------------------------------------------------------
    */

    function slotAvailable(timeString) {
        if (
            !availabilityVerified ||
            availabilityLoading ||
            availabilityFingerprint !==
                currentFingerprint()
        ) {
            return false;
        }

        if (
            !isDateAllowed(selectedDate) ||
            isStudioClosed(selectedDate) ||
            isPastOrStarted(
                selectedDate,
                timeString
            )
        ) {
            return false;
        }

        return availabilitySlots.some(slot => (
            slot.time === timeString &&
            slot.status === "available" &&
            slot.available === true
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Render Available Appointment Times
    |--------------------------------------------------------------------------
    */

    function renderTimes() {
        timeGrid.innerHTML = "";

        if (!selectedDate || !staffInput.value) {
            note.className =
                "reschedule-availability-note";

            note.textContent =
                "Select a beautician and date to check availability";

            submitButton.disabled = true;
            return;
        }

        const dateClosed =
            isStudioClosed(selectedDate);

        bookingTimes.forEach(time => {
            const slot = availabilitySlots.find(
                item => item.time === time
            );

            const button =
                document.createElement("button");

            button.type = "button";
            button.className = "reschedule-time";

            const timeLabel =
                document.createElement("span");

            timeLabel.textContent =
                displayTime(time);

            const statusLabel =
                document.createElement("small");

            let status = "loading";
            let statusText = "Checking";

            if (dateClosed) {
                status = "closed";
                statusText = "Studio closed";
            } else if (
                isPastOrStarted(selectedDate, time)
            ) {
                status = "past";
                statusText = "Past";
            } else if (
                availabilityVerified &&
                availabilityFingerprint ===
                    currentFingerprint() &&
                slot
            ) {
                status = slot.status;

                const labels = {
                    available: "Available",
                    booked: "Fully booked",
                    past: "Past",
                    day_off: "Staff day off",
                    closed: "Studio closed"
                };

                statusText =
                    labels[status] || "Unavailable";
            } else if (!availabilityLoading) {
                status = "error";
                statusText = "Unavailable";
            }

            const canSelect =
                status === "available" &&
                slotAvailable(time);

            button.disabled = !canSelect;

            if (
                canSelect &&
                selectedTime === time
            ) {
                button.classList.add("selected");
            }

            if (status === "closed") {
                button.classList.add("closed");
            }

            statusLabel.textContent = statusText;

            button.appendChild(timeLabel);
            button.appendChild(statusLabel);

            if (canSelect) {
                button.addEventListener(
                    "click",
                    () => selectTime(time)
                );
            }

            timeGrid.appendChild(button);
        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Availability Feedback
        |--------------------------------------------------------------------------
        */

        if (dateClosed) {
            note.className =
                "reschedule-availability-note error";

            note.textContent =
                getClosureMessage(selectedDate);
        } else if (availabilityLoading) {
            note.className =
                "reschedule-availability-note";

            note.textContent =
                "Checking live appointment availability";
        } else if (availabilityVerified) {
            note.className =
                "reschedule-availability-note success";

            note.textContent =
                "Availability verified using current appointments";
        } else {
            note.className =
                "reschedule-availability-note error";

            note.textContent =
                "Availability has not been verified";
        }

        submitButton.disabled =
            !selectedTime ||
            !slotAvailable(selectedTime);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Select Appointment Time
    |--------------------------------------------------------------------------
    */

    function selectTime(time) {
        if (!slotAvailable(time)) {
            showError(
                "This appointment time is unavailable"
            );

            return;
        }

        selectedTime = time;
        timeInput.value = time;

        updateSummary();
        renderTimes();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Live Availability Request
    |--------------------------------------------------------------------------
    | Existing Laravel availability endpoint
    |--------------------------------------------------------------------------
    */

    async function refreshAvailability(
        preserveSelectedTime = false
    ) {
        if (
            !staffInput.value ||
            !selectedDate ||
            !isDateAllowed(selectedDate) ||
            isStudioClosed(selectedDate) ||
            serviceIds.length === 0 ||
            recordedDuration <= 0
        ) {
            invalidateAvailability();
            return false;
        }

        const previousTime =
            preserveSelectedTime ? selectedTime : "";

        requestVersion++;

        const currentVersion = requestVersion;

        if (requestController) {
            requestController.abort();
        }

        requestController = new AbortController();

        const fingerprint =
            currentFingerprint();

        availabilityLoading = true;
        availabilityVerified = false;
        availabilitySlots = [];
        availabilityFingerprint = "";

        if (!preserveSelectedTime) {
            clearSelectedTime();
        }

        renderTimes();

        const params = new URLSearchParams({
            appointment_date: selectedDate,
            staff_id: staffInput.value,
            booking_type: bookingType,
            selected_service_ids: serviceIds.join(","),
            follow_up_appointment_id:
                bookingType === "followup"
                    ? (linkedFollowUpId || "")
                    : ""
        });

        try {
            const response = await fetch(
                availabilityEndpoint +
                    "?" +
                    params.toString(),
                {
                    method: "GET",
                    credentials: "same-origin",
                    cache: "no-store",
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With":
                            "XMLHttpRequest"
                    },
                    signal: requestController.signal
                }
            );

            if (!response.ok) {
                let message =
                    "Unable to verify appointment availability";

                try {
                    const errorData =
                        await response.json();

                    message =
                        errorData.message || message;
                } catch (error) {
                    // NS BEAUTY COMMENT: Use generic error
                }

                throw new Error(message);
            }

            const data = await response.json();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Reject Outdated Responses
            |--------------------------------------------------------------------------
            */

            if (
                currentVersion !== requestVersion ||
                fingerprint !== currentFingerprint()
            ) {
                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Validate Response Structure
            |--------------------------------------------------------------------------
            */

            if (
                !Array.isArray(data.slots) ||
                data.timezone !== malaysiaTimezone ||
                data.date !== selectedDate ||
                Number(data.staff_id) !==
                    Number(staffInput.value) ||
                Number(data.duration) !==
                    Number(recordedDuration)
            ) {
                throw new Error(
                    "The server returned inconsistent availability data"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Server Closure Confirmation
            |--------------------------------------------------------------------------
            */

            if (data.studio_closed === true) {
                availabilitySlots = [];
                availabilityLoading = false;
                availabilityVerified = false;

                clearSelectedTime();
                renderTimes();

                note.className =
                    "reschedule-availability-note error";

                note.textContent =
                    data.closure_reason
                        ? "Studio closed — " +
                            data.closure_reason
                        : "The studio is closed on this date";

                return false;
            }

            const validStatuses = [
                "available",
                "booked",
                "past",
                "day_off",
                "closed"
            ];

            const slots = data.slots.filter(
                slot => (
                    slot &&
                    bookingTimes.includes(slot.time) &&
                    validStatuses.includes(slot.status) &&
                    typeof slot.available === "boolean" &&
                    (
                        slot.status === "available"
                            ? slot.available === true
                            : slot.available === false
                    )
                )
            );

            const uniqueTimes = new Set(
                slots.map(slot => slot.time)
            );

            if (
                slots.length !== bookingTimes.length ||
                uniqueTimes.size !== bookingTimes.length
            ) {
                throw new Error(
                    "The server returned incomplete availability data"
                );
            }

            availabilitySlots = slots;
            availabilityLoading = false;
            availabilityVerified = true;
            availabilityFingerprint = fingerprint;

            if (previousTime) {
                if (slotAvailable(previousTime)) {
                    selectedTime = previousTime;
                    timeInput.value = previousTime;
                } else {
                    clearSelectedTime();
                }
            }

            renderTimes();
            updateSummary();

            return true;

        } catch (error) {
            if (
                currentVersion !== requestVersion ||
                fingerprint !== currentFingerprint()
            ) {
                return false;
            }

            availabilityLoading = false;
            availabilityVerified = false;
            availabilitySlots = [];
            availabilityFingerprint = "";

            clearSelectedTime();
            renderTimes();
            updateSummary();

            note.className =
                "reschedule-availability-note error";

            note.textContent =
                error.name === "AbortError"
                    ? "Availability check cancelled"
                    : error.message;

            return false;

        } finally {
            if (currentVersion === requestVersion) {
                requestController = null;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Beautician Changed
    |--------------------------------------------------------------------------
    */

    staffInput.addEventListener(
        "change",
        function () {
            hideError();

            invalidateAvailability();
            updateSummary();

            if (
                selectedDate &&
                staffInput.value
            ) {
                refreshAvailability();
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Final Form Validation
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        "submit",
        async function (event) {
            event.preventDefault();

            if (submissionStarted) {
                return;
            }

            hideError();

            if (!staffInput.value) {
                showError(
                    "Select a qualified replacement beautician"
                );

                return;
            }

            if (
                !selectedDate ||
                !isDateAllowed(selectedDate) ||
                isStudioClosed(selectedDate)
            ) {
                showError(
                    getClosureMessage(selectedDate) ||
                    "Select an available appointment date"
                );

                return;
            }

            if (
                !selectedTime ||
                !slotAvailable(selectedTime)
            ) {
                showError(
                    "Select an available appointment time"
                );

                return;
            }

            if (!form.reportValidity()) {
                return;
            }

            submissionStarted = true;
            submitButton.disabled = true;
            submitButton.textContent =
                "Checking Availability";

            const chosenTime = selectedTime;

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Final Live Availability Check
            |--------------------------------------------------------------------------
            */

            const verified =
                await refreshAvailability(true);

            if (
                !verified ||
                !slotAvailable(chosenTime) ||
                selectedTime !== chosenTime
            ) {
                submissionStarted = false;
                submitButton.disabled = false;
                submitButton.textContent =
                    "Confirm New Schedule";

                showError(
                    "The selected schedule is no longer available — please choose another time"
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Submit to Laravel
            |--------------------------------------------------------------------------
            */

            submitButton.textContent =
                "Saving New Schedule";

            form.submit();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Initialize Rescheduling Interface
    |--------------------------------------------------------------------------
    */

    renderDates();
    renderTimes();
    updateSummary();

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Periodic Availability Refresh
    |--------------------------------------------------------------------------
    | Recheck availability while the customer selects a schedule
    |--------------------------------------------------------------------------
    */

    setInterval(
        function () {
            if (
                submissionStarted ||
                !selectedDate ||
                !staffInput.value
            ) {
                return;
            }

            renderDates();

            if (
                selectedTime &&
                isPastOrStarted(
                    selectedDate,
                    selectedTime
                )
            ) {
                invalidateAvailability();
            }

            if (!availabilityLoading) {
                refreshAvailability(true);
            }
        },
        30000
    );
});
</script>

@endif

@endsection
