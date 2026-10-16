
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Appointment Booking
|--------------------------------------------------------------------------
| Purpose:
| - Four-step customer appointment booking
| - Live service and staff information from Laravel
| - Regular and backjob / follow-up booking
| - Database-backed time slot availability
| - Malaysia timezone
| - Mobile, tablet, and desktop responsiveness
| - Consistent 24px main card and 14px control radius
|
| Security:
| - Availability is checked using an authenticated Laravel endpoint
| - Unavailable or unknown slots cannot be selected
| - Availability is rechecked before confirmation and submission
| - BookingController remains responsible for final validation
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
.ns-booking {
    --ns-brown: #6f4e37;
    --ns-dark: #2f2118;
    --ns-muted: #7d6d60;
    --ns-border: rgba(111,78,55,.13);
    --ns-card: rgba(255,255,255,.54);
    --ns-gradient: linear-gradient(135deg,#8a7158,#5d3f2c);
    --ns-radius-large: 24px;
    --ns-radius-small: 14px;

    width: 100%;
    min-width: 0;
    color: var(--ns-dark);
}

.ns-booking *,
.ns-booking *::before,
.ns-booking *::after {
    box-sizing: border-box;
}

.ns-booking button,
.ns-booking input,
.ns-booking textarea {
    font: inherit;
}

.ns-booking button {
    -webkit-tap-highlight-color: transparent;
}

.ns-booking .booking-header {
    text-align: center;
    margin-bottom: 28px;
}

.ns-booking .booking-header h1 {
    font-size: 34px;
    line-height: 1.2;
    margin-bottom: 8px;
}

.ns-booking .booking-header p,
.ns-booking .booking-intro,
.ns-booking .section-desc {
    font-size: 14px;
    line-height: 1.7;
    color: var(--ns-muted);
}

.ns-booking .booking-intro {
    margin-bottom: 24px;
}

.ns-booking .rule-box,
.ns-booking .booking-panel,
.ns-booking .booking-error {
    width: 100%;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.ns-booking .rule-box {
    padding: 20px 24px;
    margin-bottom: 24px;
    border-radius: var(--ns-radius-large);
    border: 1px solid var(--ns-border);
    background: var(--ns-card);
    font-size: 13px;
    line-height: 1.7;
    color: var(--ns-muted);
}

.ns-booking .rule-box h2 {
    font-size: 16px;
    margin-bottom: 12px;
    color: var(--ns-dark);
}

.ns-booking .rule-box strong {
    color: var(--ns-dark);
}

.ns-booking .rule-list {
    margin-bottom: 16px;
    padding-left: 20px;
}

.ns-booking .rule-list li {
    margin-bottom: 7px;
}

.ns-booking .rule-foot {
    border-top: 1px solid var(--ns-border);
    padding-top: 12px;
    margin-top: 14px;
}

.ns-booking .booking-error {
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: var(--ns-radius-small);
    background: rgba(184,77,77,.10);
    border: 1px solid rgba(184,77,77,.22);
    color: #963b35;
    font-size: 13px;
    line-height: 1.6;
}

.ns-booking #alertBox {
    display: none;
}

.ns-booking .booking-steps {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    max-width: 850px;
    margin: 0 auto 28px;
    gap: 12px;
}

.ns-booking .booking-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
    min-width: 0;
}

.ns-booking .step-circle {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: rgba(255,255,255,.65);
    border: 1px solid var(--ns-border);
    font-size: 13px;
    font-weight: 800;
    color: var(--ns-muted);
}

.ns-booking .booking-step.active .step-circle,
.ns-booking .booking-step.done .step-circle {
    background: var(--ns-gradient);
    color: #fff;
}

.ns-booking .booking-step span {
    font-size: 12px;
    font-weight: 700;
}

.ns-booking .booking-panel {
    display: none;
    padding: 32px;
    min-width: 0;
    background: var(--glass,rgba(255,255,255,.54));
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid var(--border,rgba(255,255,255,.7));
    border-radius: var(--ns-radius-large);
    box-shadow: 0 20px 55px rgba(47,33,24,.08);
}

.ns-booking .booking-panel.active {
    display: block;
}

.ns-booking .booking-panel h2 {
    font-size: 21px;
    margin-bottom: 8px;
}

.ns-booking .booking-panel h3 {
    font-size: 16px;
    margin-bottom: 8px;
}

.ns-booking .section-desc {
    display: block;
    margin-bottom: 24px;
}

.ns-booking .booking-type-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 14px;
    margin-bottom: 30px;
}

.ns-booking .type-card,
.ns-booking .service-card,
.ns-booking .staff-card,
.ns-booking .history-card,
.ns-booking .calendar-card,
.ns-booking .summary-card,
.ns-booking .details-card,
.ns-booking .followup-container {
    min-width: 0;
    background: rgba(255,255,255,.46);
    border: 1px solid rgba(255,255,255,.72);
    border-radius: var(--ns-radius-small);
}

.ns-booking .type-card {
    display: block;
    width: 100%;
    padding: 20px;
    text-align: left;
    color: var(--ns-dark);
    cursor: pointer;
}

.ns-booking .type-card h3 {
    font-size: 15px;
    margin-bottom: 8px;
}

.ns-booking .type-card p,
.ns-booking .service-description,
.ns-booking .staff-card p,
.ns-booking .history-card p {
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.6;
}

.ns-booking .type-card.active,
.ns-booking .service-card.selected,
.ns-booking .staff-card.selected,
.ns-booking .history-card.selected {
    background: rgba(255,255,255,.82);
    border-color: var(--ns-brown);
    box-shadow: inset 0 0 0 1px var(--ns-brown);
}

.ns-booking #regularServicesBox > h2 {
    margin-bottom: 8px;
}

.ns-booking #regularServicesBox > .section-desc {
    margin-bottom: 24px;
}

.ns-booking .services-grid {
    display: grid;
    grid-template-columns: repeat(3,minmax(0,1fr));
    align-items: stretch;
    gap: 16px;
    clear: both;
}

.ns-booking .service-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    min-height: 185px;
    padding: 18px;
    gap: 12px;
    text-align: left;
    color: var(--ns-dark);
    cursor: pointer;
    transform: none;
}

.ns-booking .service-card:hover {
    background: rgba(255,255,255,.70);
    transform: none;
}

.ns-booking .service-card h4 {
    font-size: 15px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.ns-booking .service-description {
    margin: 0;
    white-space: pre-line;
    overflow-wrap: anywhere;
    flex: 1;
}

.ns-booking .service-badges,
.ns-booking .history-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ns-booking .service-badges span,
.ns-booking .history-meta span {
    display: inline-flex;
    padding: 7px 10px;
    border-radius: var(--ns-radius-small);
    background: rgba(255,255,255,.72);
    color: var(--ns-brown);
    font-size: 11px;
    font-weight: 800;
}

.ns-booking .service-hint {
    color: var(--ns-brown);
    font-size: 12px;
    font-weight: 800;
}

.ns-booking .followup-container {
    display: none;
    padding: 20px;
    margin-bottom: 24px;
}

.ns-booking .followup-container.show {
    display: block;
}

.ns-booking .history-grid {
    display: grid;
    gap: 14px;
    margin: 18px 0;
}

.ns-booking .history-card {
    padding: 18px;
}

.ns-booking .history-card h4 {
    margin-bottom: 10px;
}

.ns-booking .history-card p {
    margin-bottom: 7px;
}

.ns-booking .history-meta {
    margin-top: 12px;
}

.ns-booking .follow-service-list {
    display: grid;
    gap: 10px;
    margin-top: 18px;
}

.ns-booking .follow-service-option {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border: 1px solid var(--ns-border);
    background: rgba(255,255,255,.58);
    border-radius: var(--ns-radius-small);
    cursor: pointer;
}

.ns-booking .follow-service-option:has(input:checked) {
    background: rgba(255,255,255,.82);
    border-color: var(--ns-brown);
}

.ns-booking .follow-service-option input {
    flex: 0 0 auto;
    width: 17px;
    height: 17px;
    margin: 4px 0 0;
    accent-color: var(--ns-brown);
}

.ns-booking .follow-service-option small {
    display: block;
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.6;
    margin-top: 5px;
}

.ns-booking .empty-box {
    grid-column: 1/-1;
    padding: 24px;
    background: rgba(255,255,255,.46);
    border-radius: var(--ns-radius-small);
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
    text-align: center;
}

.ns-booking .staff-list {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 14px;
}

.ns-booking .staff-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    text-align: left;
    color: var(--ns-dark);
    cursor: pointer;
}

.ns-booking .staff-card:disabled {
    opacity: .42;
    cursor: not-allowed;
    background: rgba(180,180,180,.15);
}

.ns-booking .staff-avatar {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    min-width: 48px;
    background: rgba(255,255,255,.8);
    border-radius: 50%;
    font-size: 22px;
}

.ns-booking .staff-card small {
    font-size: 11px;
    color: var(--ns-muted);
}

.ns-booking .calendar-time {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 22px;
}

.ns-booking .calendar-card {
    padding: 20px;
}

.ns-booking .calendar-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 10px;
    margin-top: 16px;
}

.ns-booking .calendar-day,
.ns-booking .time-btn {
    width: 100%;
    min-width: 0;
    padding: 13px 8px;
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-radius-small);
    background: rgba(255,255,255,.65);
    color: var(--ns-dark);
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
}

.ns-booking .calendar-day.selected,
.ns-booking .time-btn.selected {
    background: var(--ns-gradient);
    color: #fff;
    border-color: transparent;
}

.ns-booking .calendar-day:disabled,
.ns-booking .time-btn:disabled {
    background: rgba(130,130,130,.14);
    border-color: rgba(130,130,130,.12);
    color: #99928c;
    opacity: .78;
    cursor: not-allowed;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Live Slot Status
|--------------------------------------------------------------------------
| Status values returned by BookingController:
| available, booked, past, day_off
|--------------------------------------------------------------------------
*/

.ns-booking .time-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 10px;
}

.ns-booking .time-btn {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    min-height: 62px;
}

.ns-booking .time-btn small {
    font-size: 10px;
    line-height: 1.4;
    font-weight: 600;
}

.ns-booking .time-btn.available {
    background: rgba(255,255,255,.72);
}

.ns-booking .time-btn.available:not(:disabled):hover {
    border-color: var(--ns-brown);
}

.ns-booking .time-btn.selected {
    background: var(--ns-gradient);
    color: #fff;
}

.ns-booking .time-btn.booked,
.ns-booking .time-btn.past,
.ns-booking .time-btn.day_off,
.ns-booking .time-btn.loading,
.ns-booking .time-btn.error {
    background: rgba(135,135,135,.13);
    color: #958e88;
    border-color: rgba(130,130,130,.13);
}

.ns-booking .availability-note,
.ns-booking .timezone-note {
    margin: 12px 0 16px;
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.6;
}

.ns-booking .availability-note.error {
    color: #963b35;
}

.ns-booking .availability-note.success {
    color: #55745a;
}

.ns-booking .availability-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 11px;
    color: var(--ns-muted);
    margin-top: 14px;
}

.ns-booking .availability-legend span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.ns-booking .legend-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--ns-brown);
}

.ns-booking .legend-dot.unavailable {
    background: #aaa4a0;
}

.ns-booking .confirmation-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 20px;
}

.ns-booking .summary-card,
.ns-booking .details-card {
    padding: 22px;
}

.ns-booking .summary-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 9px 0;
    font-size: 13px;
    border-bottom: 1px solid rgba(111,78,55,.07);
}

.ns-booking .summary-row strong {
    text-align: right;
    overflow-wrap: anywhere;
    max-width: 65%;
}

.ns-booking .summary-row.total {
    margin-top: 10px;
    padding-top: 15px;
    border-top: 1px solid var(--ns-border);
    border-bottom: none;
    color: var(--ns-brown);
    font-weight: 900;
    font-size: 15px;
}

.ns-booking .form-label {
    display: block;
    margin-bottom: 8px;
    color: var(--ns-dark);
    font-size: 13px;
    font-weight: 800;
}

.ns-booking .form-input {
    display: block;
    width: 100%;
    min-height: 46px;
    padding: 13px 15px;
    margin-bottom: 16px;
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-radius-small);
    background: rgba(255,255,255,.72);
    color: var(--ns-dark);
    outline: none;
    font-size: 14px;
}

.ns-booking .form-input::placeholder {
    color: #aaa096;
    opacity: .75;
}

.ns-booking .form-input:focus {
    border-color: var(--ns-brown);
    box-shadow: 0 0 0 3px rgba(111,78,55,.10);
}

.ns-booking .form-input[readonly] {
    background: rgba(220,215,209,.25);
    color: var(--ns-muted);
    cursor: not-allowed;
}

.ns-booking textarea.form-input {
    resize: vertical;
    min-height: 90px;
}

.ns-booking .nav-buttons {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-top: 30px;
}

.ns-booking .nav-buttons.first-step {
    justify-content: flex-end;
}

.ns-booking .booking-btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    min-height: 46px;
    padding: 12px 22px;
    border: none;
    border-radius: var(--ns-radius-small);
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
}

.ns-booking .booking-btn-primary {
    background: var(--ns-gradient);
    color: #fff;
}

.ns-booking .booking-btn-secondary {
    background: rgba(255,255,255,.60);
    color: var(--ns-brown);
    border: 1px solid var(--ns-border);
}

.ns-booking .booking-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.ns-booking button:focus-visible,
.ns-booking a:focus-visible {
    outline: 3px solid rgba(111,78,55,.4);
    outline-offset: 3px;
}

.ns-booking .success-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background: rgba(47,33,24,.35);
    backdrop-filter: blur(9px);
}

.ns-booking .success-modal {
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 32px;
    border-radius: var(--ns-radius-large);
    background: rgba(255,255,255,.95);
    text-align: center;
    box-shadow: 0 25px 65px rgba(47,33,24,.20);
}

.ns-booking .success-icon {
    display: grid;
    place-items: center;
    width: 65px;
    height: 65px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: var(--ns-gradient);
    color: #fff;
    font-size: 30px;
}

.ns-booking .success-modal p {
    margin: 12px 0;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
}

.ns-booking .success-actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
}

@media (max-width:900px) {
    .ns-booking .services-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }

    .ns-booking .calendar-time,
    .ns-booking .confirmation-grid {
        grid-template-columns: minmax(0,1fr);
    }
}

@media (max-width:600px) {
    .ns-booking .booking-header h1 {
        font-size: 28px;
    }

    .ns-booking .booking-panel {
        padding: 20px 16px;
    }

    .ns-booking .rule-box {
        padding: 18px;
    }

    .ns-booking .booking-type-grid,
    .ns-booking .services-grid,
    .ns-booking .staff-list {
        grid-template-columns: minmax(0,1fr);
    }

    .ns-booking .booking-steps {
        gap: 5px;
    }

    .ns-booking .booking-step span {
        font-size: 10px;
    }

    .ns-booking .step-circle {
        width: 30px;
        height: 30px;
    }

    .ns-booking .service-card {
        min-height: 0;
    }

    .ns-booking .summary-row {
        flex-direction: column;
        gap: 5px;
    }

    .ns-booking .summary-row strong {
        max-width: 100%;
        text-align: left;
    }

    .ns-booking .nav-buttons {
        flex-direction: column;
    }

    .ns-booking .booking-btn {
        width: 100%;
    }

    .ns-booking .success-modal {
        padding: 24px 18px;
    }
}

@media (prefers-reduced-motion:reduce) {
    .ns-booking *,
    .ns-booking *::before,
    .ns-booking *::after {
        transition: none !important;
    }
}
</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Profile Defaults
    |--------------------------------------------------------------------------
    */

    $firstStaff = $staffMembers->first();

    $customerName = old(
        'customer_name',
        $customer?->full_name ?? auth()->user()->name
    );

    $customerPhone = old(
        'customer_phone',
        $customer?->phone_number ?? ''
    );

    $customerEmail = old(
        'customer_email',
        auth()->user()->email
    );

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Staff Service Assignment Data
    |--------------------------------------------------------------------------
    | Frontend staff eligibility uses actual assigned services
    |--------------------------------------------------------------------------
    */

    $staffSkills = $staffMembers->mapWithKeys(function ($staff) {
        return [
            (string) $staff->id => $staff->services
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
        ];
    })->all();

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Staff Day-off Data
    |--------------------------------------------------------------------------
    */

    $staffDaysOff = $staffDayOffs->map(function ($dayOff) {
        return [
            'staff_id' => (int) $dayOff->staff_id,
            'start_date' => \Carbon\Carbon::parse(
                $dayOff->start_date
            )->format('Y-m-d'),
            'end_date' => \Carbon\Carbon::parse(
                $dayOff->end_date
            )->format('Y-m-d'),
        ];
    })->values()->all();
@endphp

<div class="ns-booking">

    <header class="booking-header">
        <h1>Book Appointment</h1>
        <p>Schedule your next visit in four simple steps</p>
    </header>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking Rules
    |--------------------------------------------------------------------------
    --}}

    <div class="rule-box">
        <h2>Booking Rules</h2>

        <ul class="rule-list">
            <li>Maximum of <strong>3 services</strong> per booking</li>

            <li>
                New customers can have
                <strong>1 active booking</strong>
            </li>

            <li>
                After the first completed booking, customers can have up to
                <strong>2 active bookings</strong>
            </li>

            <li>
                Bookings are allowed within the next
                <strong>7 days</strong>, including today
            </li>

            <li>
                Beauticians must be active and assigned to the selected services
            </li>

            <li>
                Appointment times cannot overlap another active booking
                or a staff day off
            </li>
        </ul>

        <h2>Backjob / Follow-up Rules</h2>

        <p>
            Follow-up services must come from one finished booking
            You may select one, several, or all recorded services,
            subject to the maximum of 3 services per booking
        </p>

        <div class="rule-foot">
            Active bookings:
            <strong>{{ $customerActiveBookings }}</strong>

            <span aria-hidden="true"> · </span>

            Booking limit:
            <strong>{{ $customerCompletedBookings >= 1 ? 2 : 1 }}</strong>
        </div>
    </div>

    @if ($errors->any())
        <div class="booking-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div
        id="alertBox"
        class="booking-error"
        role="alert"
        aria-live="polite"
    ></div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Four-Step Progress
    |--------------------------------------------------------------------------
    --}}

    <div class="booking-steps">
        <div class="booking-step active" id="stepIndicator1">
            <div class="step-circle">1</div>
            <span>Services</span>
        </div>

        <div class="booking-step" id="stepIndicator2">
            <div class="step-circle">2</div>
            <span>Beautician</span>
        </div>

        <div class="booking-step" id="stepIndicator3">
            <div class="step-circle">3</div>
            <span>Date &amp; Time</span>
        </div>

        <div class="booking-step" id="stepIndicator4">
            <div class="step-circle">4</div>
            <span>Confirm</span>
        </div>
    </div>

    <form
        id="bookingForm"
        method="POST"
        action="{{ route('customer.booking.store') }}"
    >
        @csrf

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Existing Booking Input Names
        |--------------------------------------------------------------------------
        | Preserve these fields for BookingController::store
        |--------------------------------------------------------------------------
        --}}

        <input
            type="hidden"
            id="bookingTypeInput"
            name="booking_type"
            value="regular"
        >

        <input
            type="hidden"
            id="selectedServiceIdsInput"
            name="selected_service_ids"
            value="{{ old('selected_service_ids') }}"
        >

        <input
            type="hidden"
            id="selectedServicesInput"
            name="selected_services"
            value="{{ old('selected_services') }}"
        >

        <input
            type="hidden"
            id="followUpAppointmentInput"
            name="follow_up_appointment_id"
            value="{{ old('follow_up_appointment_id') }}"
        >

        <input
            type="hidden"
            id="staffInput"
            name="staff_id"
            value=""
        >

        <input
            type="hidden"
            id="dateInput"
            name="appointment_date"
            value=""
        >

        <input
            type="hidden"
            id="timeInput"
            name="start_time"
            value=""
        >

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Step 1 - Services
        |--------------------------------------------------------------------------
        --}}

        <section class="booking-panel active" id="step1">

            <h2>Choose Booking Type</h2>

            <p class="booking-intro">
                Select a regular booking or a backjob / follow-up
                from your finished service history
            </p>

            <div class="booking-type-grid">

                <button
                    type="button"
                    class="type-card active"
                    data-booking-type="regular"
                    aria-pressed="true"
                    onclick="setBookingType('regular',this)"
                >
                    <h3>Regular Booking</h3>
                    <p>For a new appointment or beauty service session</p>
                </button>

                <button
                    type="button"
                    class="type-card"
                    data-booking-type="followup"
                    aria-pressed="false"
                    onclick="setBookingType('followup',this)"
                >
                    <h3>Backjob / Follow-up</h3>
                    <p>Choose services from a finished booking</p>
                </button>

            </div>

            <div id="regularServicesBox">

                <h2>Choose Services</h2>

                <p class="section-desc">
                    Select up to 3 treatments for your regular session
                </p>

                <div class="services-grid">

                    @forelse ($services as $service)

                        <button
                            type="button"
                            class="service-card"
                            aria-pressed="false"
                            onclick="selectRegularService(
                                this,
                                {{ (int) $service->id }},
                                @js($service->service_name),
                                {{ (float) $service->price }},
                                {{ (int) $service->duration }}
                            )"
                        >
                            <h4>{{ $service->service_name }}</h4>

                            <p class="service-description">
                                {{ $service->description ?: 'No description available' }}
                            </p>

                            <div class="service-badges">
                                <span>{{ $service->duration }} minutes</span>
                                <span>RM {{ number_format((float) $service->price, 2) }}</span>
                            </div>

                            <span class="service-hint">Select service</span>
                        </button>

                    @empty

                        <div class="empty-box">
                            No available services found
                            Please contact the studio or try again later
                        </div>

                    @endforelse

                </div>
            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Follow-up Service Selection
            |--------------------------------------------------------------------------
            --}}

            <div class="followup-container" id="followupBox">

                <h3>Choose Services From Finished History</h3>

                <p class="section-desc">
                    Select one finished booking, then choose the services
                    requiring a follow-up
                </p>

                <div class="history-grid">

                    @forelse ($eligibleFollowUps as $history)

                        @php
                            $historyDate = $history->appointment_date
                                ? \Carbon\Carbon::parse(
                                    $history->appointment_date
                                )->format('F d, Y')
                                : 'Not available';

                            $historyStaffName =
                                $history->staff?->full_name ?? 'Not assigned';

                            $historyDuration = $history->services->sum(
                                function ($service) {
                                    return (int) (
                                        $service->pivot->duration
                                        ?? $service->duration
                                        ?? 0
                                    );
                                }
                            );
                        @endphp

                        <div
                            class="history-card"
                            id="historyCard{{ $history->id }}"
                        >

                            <h4>
                                Booking #BK-{{ str_pad($history->id,4,'0',STR_PAD_LEFT) }}
                            </h4>

                            <p>
                                <strong>Finished Date:</strong>
                                {{ $historyDate }}
                            </p>

                            <p>
                                <strong>Original Beautician:</strong>
                                {{ $historyStaffName }}
                            </p>

                            <p>
                                <strong>Services:</strong>
                                {{ $history->services->pluck('service_name')->join(', ') }}
                            </p>

                            <div class="history-meta">
                                <span>{{ $historyDuration }} minutes</span>
                                <span>Follow-up RM 0.00</span>
                            </div>

                            <div class="follow-service-list">

                                @forelse ($history->services as $service)

                                    @php
                                        $duration = (int) (
                                            $service->pivot->duration
                                            ?? $service->duration
                                            ?? 0
                                        );
                                    @endphp

                                    <label class="follow-service-option">

                                        <input
                                            type="checkbox"
                                            class="follow-service-checkbox"
                                            onchange="selectFollowUpService(
                                                this,
                                                {{ (int) $history->id }},
                                                {{ (int) $history->staff_id }},
                                                @js($historyStaffName),
                                                {{ (int) $service->id }},
                                                @js($service->service_name),
                                                {{ $duration }}
                                            )"
                                        >

                                        <span>
                                            <strong>{{ $service->service_name }}</strong>

                                            <small>
                                                {{ $service->description ?: 'No description available' }}
                                            </small>

                                            <small>
                                                {{ $duration }} minutes · RM 0.00
                                            </small>
                                        </span>

                                    </label>

                                @empty

                                    <div class="empty-box">
                                        No recorded services for this booking
                                    </div>

                                @endforelse

                            </div>

                        </div>

                    @empty

                        <div class="empty-box">
                            You do not have any finished bookings yet
                            Complete a regular booking before requesting a follow-up
                        </div>

                    @endforelse

                </div>

                <label class="form-label" for="followUpReason">
                    Backjob / Follow-up Reason
                </label>

                <textarea
                    class="form-input"
                    id="followUpReason"
                    name="follow_up_reason"
                    maxlength="500"
                    placeholder="Describe the adjustment or repair needed"
                >{{ old('follow_up_reason') }}</textarea>

            </div>

            <div class="nav-buttons first-step">
                <button
                    type="button"
                    class="booking-btn booking-btn-primary"
                    onclick="nextStep()"
                >
                    Continue →
                </button>
            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Step 2 - Beautician
        |--------------------------------------------------------------------------
        --}}

        <section class="booking-panel" id="step2">

            <h2>Choose Beautician</h2>

            <p class="booking-intro">
                Choose an active beautician assigned to your selected services
            </p>

            <div class="staff-list">

                @forelse ($staffMembers as $staff)

                    <button
                        type="button"
                        class="staff-card"
                        data-staff-id="{{ $staff->id }}"
                        onclick="selectBeautician(
                            this,
                            {{ (int) $staff->id }},
                            @js($staff->full_name)
                        )"
                    >
                        <span class="staff-avatar" aria-hidden="true">👤</span>

                        <span>
                            <strong>{{ $staff->full_name }}</strong>
                            <p>Beautician</p>
                            <small>Status: {{ $staff->status }}</small>
                        </span>
                    </button>

                @empty

                    <div class="empty-box">
                        No active beauticians found
                        Please contact the studio
                    </div>

                @endforelse

            </div>

            <div class="nav-buttons">

                <button
                    type="button"
                    class="booking-btn booking-btn-secondary"
                    onclick="prevStep()"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    class="booking-btn booking-btn-primary"
                    onclick="nextStep()"
                >
                    Continue →
                </button>

            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Step 3 - Real Availability
        |--------------------------------------------------------------------------
        --}}

        <section class="booking-panel" id="step3">

            <h2>Date &amp; Time</h2>

            <p class="booking-intro">
                Select a date within the next 7 days
                All times use Malaysia time
            </p>

            <div class="calendar-time">

                <div class="calendar-card">

                    <h3>Available Dates</h3>

                    <p class="timezone-note">
                        Malaysia Time (MYT, UTC+8)
                    </p>

                    <div
                        class="calendar-grid"
                        id="dateGrid"
                    ></div>

                </div>

                <div class="calendar-card">

                    <h3>Select Time</h3>

                    <p class="timezone-note">
                        Available times depend on the beautician
                        and total service duration
                    </p>

                    <div
                        class="time-grid"
                        id="timeGrid"
                        aria-live="polite"
                    ></div>

                    <div class="availability-legend">
                        <span>
                            <i class="legend-dot"></i>
                            Available
                        </span>

                        <span>
                            <i class="legend-dot unavailable"></i>
                            Unavailable
                        </span>
                    </div>

                    <p
                        class="availability-note"
                        id="availabilityNote"
                        role="status"
                        aria-live="polite"
                    >
                        Select a date to check appointment availability
                    </p>

                </div>

            </div>

            <div class="nav-buttons">

                <button
                    type="button"
                    class="booking-btn booking-btn-secondary"
                    onclick="prevStep()"
                >
                    ← Back
                </button>

                <button
                    type="button"
                    class="booking-btn booking-btn-primary"
                    id="dateContinueBtn"
                    onclick="nextStep()"
                >
                    Continue →
                </button>

            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Step 4 - Confirmation
        |--------------------------------------------------------------------------
        --}}

        <section class="booking-panel" id="step4">

            <h2>Confirm Booking</h2>

            <p class="booking-intro">
                Review your appointment details before submitting
            </p>

            <div class="confirmation-grid">

                <div class="summary-card">

                    <h3>Booking Summary</h3>

                    <div class="summary-row">
                        <span>Booking Type</span>
                        <strong id="summaryType">Regular Booking</strong>
                    </div>

                    <div class="summary-row">
                        <span>Selected Services</span>
                        <strong id="summaryServices">None</strong>
                    </div>

                    <div class="summary-row">
                        <span>Linked History</span>
                        <strong id="summaryHistory">Not applicable</strong>
                    </div>

                    <div class="summary-row">
                        <span>Beautician</span>
                        <strong id="summaryStaff">Not selected</strong>
                    </div>

                    <div class="summary-row">
                        <span>Date</span>
                        <strong id="summaryDate">Not selected</strong>
                    </div>

                    <div class="summary-row">
                        <span>Time</span>
                        <strong id="summaryTime">Not selected</strong>
                    </div>

                    <div class="summary-row">
                        <span>Duration</span>
                        <strong id="summaryDuration">0 minutes</strong>
                    </div>

                    <div class="summary-row total">
                        <span>Total</span>
                        <strong id="summaryTotal">RM 0.00</strong>
                    </div>

                </div>

                <div class="details-card">

                    <h3>Your Details</h3>

                    <label class="form-label" for="customerName">
                        Full Name
                    </label>

                    <input
                        class="form-input"
                        id="customerName"
                        type="text"
                        name="customer_name"
                        value="{{ $customerName }}"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        required
                    >

                    <label class="form-label" for="customerPhone">
                        Phone Number
                    </label>

                    <input
                        class="form-input"
                        id="customerPhone"
                        type="tel"
                        name="customer_phone"
                        value="{{ $customerPhone }}"
                        placeholder="Enter your phone number"
                        autocomplete="tel"
                        required
                    >

                    <label class="form-label" for="customerEmail">
                        Email Address
                    </label>

                    <input
                        class="form-input"
                        id="customerEmail"
                        type="email"
                        name="customer_email"
                        value="{{ $customerEmail }}"
                        readonly
                        required
                    >

                </div>

            </div>

            <div class="nav-buttons">

                <button
                    type="button"
                    class="booking-btn booking-btn-secondary"
                    onclick="prevStep()"
                >
                    ← Back
                </button>

                <button
                    type="submit"
                    class="booking-btn booking-btn-primary"
                    id="confirmBookingBtn"
                >
                    Confirm Booking
                </button>

            </div>

        </section>

    </form>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Existing Success Popup
    |--------------------------------------------------------------------------
    --}}

    @if (session('booking_success'))

        <div class="success-overlay" id="successModal">

            <div
                class="success-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="successTitle"
            >

                <div class="success-icon">✓</div>

                <h2 id="successTitle">Booking Recorded</h2>

                <p>
                    {{ session(
                        'booking_message',
                        'Your booking has been recorded successfully'
                    ) }}
                </p>

                @if (session('booking_id'))
                    <p>
                        <strong>
                            Booking ID: #{{ session('booking_id') }}
                        </strong>
                    </p>
                @endif

                <div class="success-actions">

                    <a
                        href="{{ route('customer.dashboard') }}"
                        class="booking-btn booking-btn-primary"
                    >
                        Go to Dashboard
                    </a>

                    <a
                        href="{{ route('customer.history') }}"
                        class="booking-btn booking-btn-secondary"
                    >
                        View Booking History
                    </a>

                </div>

            </div>

        </div>

    @endif

</div>
@endsection

@section('scripts')
<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking State
|--------------------------------------------------------------------------
*/

let currentStep = 1;

let selectedBookingType = "regular";
let selectedServices = [];
let selectedFollowUpId = "";

let selectedStaff = "";
let selectedDate = "";
let selectedTime = "";

let totalPrice = 0;

const customerCompletedBookings = {{ (int) $customerCompletedBookings }};
const customerActiveBookings = {{ (int) $customerActiveBookings }};

const staffSkills = @json($staffSkills);
const staffDaysOff = @json($staffDaysOff);

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Availability Endpoint
|--------------------------------------------------------------------------
| This route was added in routes/web.php
|--------------------------------------------------------------------------
*/

const availabilityEndpoint = @js(route('customer.booking.availability'));

const malaysiaTimezone = "Asia/Kuala_Lumpur";

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
| NS BEAUTY COMMENT: Live Availability State
|--------------------------------------------------------------------------
| No slot is considered available until Laravel confirms it
|--------------------------------------------------------------------------
*/

let availabilitySlots = [];

let availabilityLoading = false;
let availabilityVerified = false;
let availabilityRequestVersion = 0;

let availabilityAbortController = null;
let availabilityFingerprint = "";

let bookingSubmissionStarted = false;
let alertTimeout = null;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Malaysian Clock
|--------------------------------------------------------------------------
*/

function malaysiaParts(date = new Date()) {
    const parts = new Intl.DateTimeFormat("en-GB", {
        timeZone: malaysiaTimezone,
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        hourCycle: "h23"
    }).formatToParts(date);

    const values = {};

    parts.forEach(part => {
        if (part.type !== "literal") {
            values[part.type] = part.value;
        }
    });

    return {
        date: `${values.year}-${values.month}-${values.day}`,
        time: `${values.hour}:${values.minute}`
    };
}

function malaysiaToday() {
    return malaysiaParts().date;
}

function addCalendarDays(dateString, days) {
    const [year, month, day] = dateString.split("-").map(Number);

    return new Date(
        Date.UTC(year, month - 1, day + days, 12)
    ).toISOString().slice(0, 10);
}

function getBookingDates() {
    const today = malaysiaToday();

    return Array.from(
        { length: 7 },
        (_, index) => addCalendarDays(today, index)
    );
}

function isDateWithinBookingWindow(dateString) {
    return getBookingDates().includes(dateString);
}

function isPastOrStarted(dateString, timeString) {
    const now = malaysiaParts();

    return (
        dateString < now.date ||
        (dateString === now.date && timeString <= now.time)
    );
}

function displayTime(timeString) {
    const [hour, minute] = timeString.split(":").map(Number);

    const period = hour >= 12 ? "PM" : "AM";
    const twelveHour = hour % 12 || 12;

    return `${twelveHour}:${String(minute).padStart(2,"0")} ${period}`;
}

function displayDate(dateString) {
    if (!dateString) {
        return "Not selected";
    }

    const [year, month, day] = dateString.split("-").map(Number);

    const date = new Date(Date.UTC(year, month - 1, day, 12));

    return new Intl.DateTimeFormat("en-MY", {
        timeZone: "UTC",
        weekday: "short",
        month: "short",
        day: "numeric",
        year: "numeric"
    }).format(date);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Alert Messages
|--------------------------------------------------------------------------
*/

function showAlert(message) {
    const alertBox = document.getElementById("alertBox");

    alertBox.textContent = message;
    alertBox.style.display = "block";

    if (alertTimeout) {
        clearTimeout(alertTimeout);
    }

    alertTimeout = setTimeout(() => {
        alertBox.style.display = "none";
    }, 5000);

    alertBox.scrollIntoView({
        behavior: "smooth",
        block: "center"
    });
}

function getBookingLimit() {
    return customerCompletedBookings >= 1 ? 2 : 1;
}

function checkActiveBookingLimit() {
    if (customerActiveBookings >= getBookingLimit()) {
        showAlert("You have reached your active booking limit");
        return false;
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking Steps
|--------------------------------------------------------------------------
*/

function showStep(step) {
    document.querySelectorAll(".booking-panel").forEach(panel => {
        panel.classList.remove("active");
    });

    const activePanel = document.getElementById("step" + step);

    if (!activePanel) {
        return;
    }

    activePanel.classList.add("active");

    for (let i = 1; i <= 4; i++) {
        const indicator = document.getElementById(
            "stepIndicator" + i
        );

        indicator.classList.remove("active", "done");

        if (i < step) {
            indicator.classList.add("done");
        } else if (i === step) {
            indicator.classList.add("active");
        }
    }

    if (step === 3) {
        generateNext7Days();
        refreshAvailability();
    }

    if (step === 4) {
        updateSummary();
    }

    activePanel.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
}

async function nextStep() {
    if (!checkActiveBookingLimit()) {
        return;
    }

    if (currentStep === 1) {
        if (
            selectedServices.length < 1 ||
            selectedServices.length > 3
        ) {
            showAlert("Select between 1 and 3 services");
            return;
        }

        if (
            selectedBookingType === "followup" &&
            !selectedFollowUpId
        ) {
            showAlert("Select services from one finished booking");
            return;
        }

        if (getTotalDuration() <= 0) {
            showAlert("The selected services have an invalid duration");
            return;
        }

        updateStaffEligibility();
    }

    if (currentStep === 2) {
        const staffId = document.getElementById("staffInput").value;

        if (!staffId || !isStaffEligible(staffId)) {
            showAlert("Please select an eligible beautician");
            return;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Recheck Before Confirmation
    |--------------------------------------------------------------------------
    | A previously available slot might have been booked meanwhile
    |--------------------------------------------------------------------------
    */

    if (currentStep === 3) {
        if (!selectedDate || !isDateWithinBookingWindow(selectedDate)) {
            showAlert("Select a date within the 7-day booking window");
            return;
        }

        if (!selectedTime) {
            showAlert("Please select an available time");
            return;
        }

        const chosenTime = selectedTime;

        const verified = await refreshAvailability(true);

        if (!verified) {
            showAlert("Unable to verify appointment availability");
            return;
        }

        if (!isSlotAvailable(chosenTime)) {
            showAlert("The selected time is no longer available");
            return;
        }

        selectVerifiedTime(chosenTime);
    }

    if (currentStep < 4) {
        currentStep++;
        showStep(currentStep);
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking Type
|--------------------------------------------------------------------------
*/

function setBookingType(type, element) {
    selectedBookingType = type;

    document.getElementById("bookingTypeInput").value = type;

    document.querySelectorAll(".type-card").forEach(card => {
        const active = card === element;

        card.classList.toggle("active", active);
        card.setAttribute("aria-pressed", active ? "true" : "false");
    });

    document.getElementById("regularServicesBox").style.display =
        type === "regular" ? "block" : "none";

    document.getElementById("followupBox").classList.toggle(
        "show",
        type === "followup"
    );

    resetServiceSelections();
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Services and Prices
|--------------------------------------------------------------------------
*/

function getTotalDuration() {
    return selectedServices.reduce((sum, service) => {
        return sum + Number(service.duration || 0);
    }, 0);
}

function calculateTotal() {
    totalPrice = selectedServices.reduce((sum, service) => {
        return sum + Number(service.price || 0);
    }, 0);
}

function updateHiddenServiceInputs() {
    document.getElementById("selectedServiceIdsInput").value =
        selectedServices.map(service => service.id).join(",");

    document.getElementById("selectedServicesInput").value =
        selectedServices.map(service => service.name).join(", ");
}

function resetServiceSelections() {
    selectedServices = [];
    selectedFollowUpId = "";
    totalPrice = 0;

    document.getElementById("followUpAppointmentInput").value = "";

    document.querySelectorAll(".service-card").forEach(card => {
        card.classList.remove("selected");
        card.setAttribute("aria-pressed", "false");
    });

    document.querySelectorAll(".history-card").forEach(card => {
        card.classList.remove("selected");
    });

    document.querySelectorAll(".follow-service-checkbox").forEach(input => {
        input.checked = false;
    });

    updateHiddenServiceInputs();
    updateStaffEligibility();
    invalidateAvailability();
}

function selectionChanged() {
    calculateTotal();
    updateHiddenServiceInputs();
    updateStaffEligibility();
    invalidateAvailability();
}

function selectRegularService(card, id, name, price, duration) {
    if (selectedBookingType !== "regular") {
        return;
    }

    const index = selectedServices.findIndex(
        service => service.id === id
    );

    if (index >= 0) {
        selectedServices.splice(index, 1);
        card.classList.remove("selected");
        card.setAttribute("aria-pressed", "false");
    } else {
        if (selectedServices.length >= 3) {
            showAlert("Maximum of 3 services per booking");
            return;
        }

        selectedServices.push({
            id: Number(id),
            name,
            price: Number(price),
            duration: Number(duration)
        });

        card.classList.add("selected");
        card.setAttribute("aria-pressed", "true");
    }

    selectionChanged();
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Backjob and Follow-up Services
|--------------------------------------------------------------------------
*/

function selectFollowUpService(
    input,
    appointmentId,
    staffId,
    staffName,
    serviceId,
    serviceName,
    duration
) {
    if (selectedBookingType !== "followup") {
        input.checked = false;
        return;
    }

    appointmentId = Number(appointmentId);
    serviceId = Number(serviceId);

    const changingHistory =
        selectedFollowUpId !== "" &&
        Number(selectedFollowUpId) !== appointmentId;

    if (input.checked && changingHistory) {
        selectedServices = [];

        document.querySelectorAll(".follow-service-checkbox").forEach(box => {
            if (box !== input) {
                box.checked = false;
            }
        });
    }

    if (input.checked) {
        if (selectedServices.length >= 3) {
            input.checked = false;
            showAlert("Maximum of 3 follow-up services per booking");
            return;
        }

        selectedFollowUpId = appointmentId;

        if (!selectedServices.some(service => service.id === serviceId)) {
            selectedServices.push({
                id: serviceId,
                name: serviceName,
                price: 0,
                duration: Number(duration)
            });
        }
    } else {
        selectedServices = selectedServices.filter(
            service => service.id !== serviceId
        );

        if (selectedServices.length === 0) {
            selectedFollowUpId = "";
        }
    }

    document.getElementById("followUpAppointmentInput").value =
        selectedFollowUpId;

    document.querySelectorAll(".history-card").forEach(card => {
        card.classList.toggle(
            "selected",
            card.id === "historyCard" + selectedFollowUpId
        );
    });

    selectionChanged();

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Prefer Original Beautician
    |--------------------------------------------------------------------------
    */

    if (input.checked && selectedFollowUpId) {
        selectOriginalStaff(staffId, staffName);
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Staff Eligibility
|--------------------------------------------------------------------------
*/

function isStaffEligible(staffId) {
    if (!staffId || selectedServices.length === 0) {
        return false;
    }

    const assignedServices = staffSkills[String(staffId)] || [];
    const assignedIds = assignedServices.map(Number);

    return selectedServices.every(service => {
        return assignedIds.includes(Number(service.id));
    });
}

function updateStaffEligibility() {
    const selectedStaffId =
        document.getElementById("staffInput").value;

    document.querySelectorAll(".staff-card").forEach(card => {
        const eligible = isStaffEligible(card.dataset.staffId);

        card.disabled = !eligible;

        card.classList.toggle(
            "selected",
            eligible &&
            selectedStaffId === card.dataset.staffId
        );
    });

    if (selectedStaffId && !isStaffEligible(selectedStaffId)) {
        document.getElementById("staffInput").value = "";
        selectedStaff = "";
    }
}

function selectBeautician(card, id, name) {
    if (card.disabled || !isStaffEligible(id)) {
        showAlert("This beautician is not assigned to all selected services");
        return;
    }

    document.querySelectorAll(".staff-card").forEach(item => {
        item.classList.remove("selected");
    });

    card.classList.add("selected");

    document.getElementById("staffInput").value = id;

    selectedStaff = name;

    invalidateAvailability();

    if (currentStep === 3) {
        refreshAvailability();
    }
}

function selectOriginalStaff(staffId, staffName) {
    const card = document.querySelector(
        `.staff-card[data-staff-id="${Number(staffId)}"]`
    );

    if (card && !card.disabled) {
        selectBeautician(card, Number(staffId), staffName);
    }
}

function isStaffOnDayOff(staffId, dateString) {
    return staffDaysOff.some(dayOff => {
        return (
            Number(dayOff.staff_id) === Number(staffId) &&
            dateString >= dayOff.start_date &&
            dateString <= dayOff.end_date
        );
    });
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Malaysian Date Selection
|--------------------------------------------------------------------------
*/

function generateNext7Days() {
    const grid = document.getElementById("dateGrid");

    grid.innerHTML = "";

    getBookingDates().forEach(dateString => {
        const button = document.createElement("button");

        button.type = "button";
        button.className = "calendar-day";
        button.textContent = displayDate(dateString);

        button.classList.toggle(
            "selected",
            selectedDate === dateString
        );

        button.addEventListener("click", () => {
            selectDate(button, dateString);
        });

        grid.appendChild(button);
    });

    if (
        selectedDate &&
        !isDateWithinBookingWindow(selectedDate)
    ) {
        selectedDate = "";
        document.getElementById("dateInput").value = "";
        invalidateAvailability();
    }
}

function selectDate(button, dateString) {
    if (!isDateWithinBookingWindow(dateString)) {
        showAlert("The selected date is outside the booking window");
        return;
    }

    document.querySelectorAll(".calendar-day").forEach(day => {
        day.classList.remove("selected");
    });

    button.classList.add("selected");

    selectedDate = dateString;

    document.getElementById("dateInput").value = dateString;

    invalidateAvailability();
    refreshAvailability();
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Availability Request Fingerprint
|--------------------------------------------------------------------------
| Used to prevent stale responses from applying to changed selections
|--------------------------------------------------------------------------
*/

function getAvailabilityFingerprint() {
    const staffId = document.getElementById("staffInput").value;

    const serviceIds = selectedServices
        .map(service => Number(service.id))
        .sort((a, b) => a - b)
        .join(",");

    return [
        selectedDate,
        staffId,
        selectedBookingType,
        selectedFollowUpId,
        serviceIds
    ].join("|");
}

function getAvailabilityParams() {
    return {
        appointment_date: selectedDate,
        staff_id: document.getElementById("staffInput").value,
        booking_type: selectedBookingType,
        selected_service_ids: selectedServices
            .map(service => service.id)
            .join(","),
        follow_up_appointment_id:
            selectedBookingType === "followup"
                ? selectedFollowUpId
                : ""
    };
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Invalidate Old Availability
|--------------------------------------------------------------------------
| Any service, date, or staff change invalidates previously checked times
|--------------------------------------------------------------------------
*/

function invalidateAvailability() {
    availabilityRequestVersion++;

    if (availabilityAbortController) {
        availabilityAbortController.abort();
        availabilityAbortController = null;
    }

    availabilitySlots = [];
    availabilityLoading = false;
    availabilityVerified = false;
    availabilityFingerprint = "";

    clearSelectedTime();

    renderTimeSlots();
}

function clearSelectedTime() {
    selectedTime = "";

    const input = document.getElementById("timeInput");

    if (input) {
        input.value = "";
    }

    document.querySelectorAll(".time-btn").forEach(button => {
        button.classList.remove("selected");
    });
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Check Whether a Slot Is Verified Available
|--------------------------------------------------------------------------
*/

function isSlotAvailable(time) {
    if (
        !availabilityVerified ||
        availabilityLoading ||
        availabilityFingerprint !== getAvailabilityFingerprint()
    ) {
        return false;
    }

    if (!isDateWithinBookingWindow(selectedDate)) {
        return false;
    }

    if (isPastOrStarted(selectedDate, time)) {
        return false;
    }

    return availabilitySlots.some(slot => {
        return slot.time === time &&
            slot.available === true &&
            slot.status === "available";
    });
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Render Actual Slot States
|--------------------------------------------------------------------------
*/

function renderTimeSlots() {
    const grid = document.getElementById("timeGrid");
    const note = document.getElementById("availabilityNote");

    if (!grid || !note) {
        return;
    }

    grid.innerHTML = "";

    if (!selectedDate) {
        note.className = "availability-note";
        note.textContent = "Select a date to check appointment availability";
        return;
    }

    const staffId = document.getElementById("staffInput").value;

    const onDayOff = staffId &&
        isStaffOnDayOff(staffId, selectedDate);

    bookingTimes.forEach(time => {
        const slot = availabilitySlots.find(item => {
            return item.time === time;
        });

        const button = document.createElement("button");
        const label = document.createElement("span");
        const statusLabel = document.createElement("small");

        button.type = "button";
        button.className = "time-btn";

        label.textContent = displayTime(time);

        let status = "loading";
        let statusText = "Checking";

        if (isPastOrStarted(selectedDate, time)) {
            status = "past";
            statusText = "Past";
        } else if (onDayOff) {
            status = "day_off";
            statusText = "Staff day off";
        } else if (availabilityLoading) {
            status = "loading";
            statusText = "Checking";
        } else if (
            availabilityVerified &&
            availabilityFingerprint === getAvailabilityFingerprint() &&
            slot
        ) {
            status = slot.status;

            switch (status) {
                case "available":
                    statusText = "Available";
                    break;

                case "booked":
                    statusText = "Fully booked";
                    break;

                case "past":
                    statusText = "Past";
                    break;

                case "day_off":
                    statusText = "Staff day off";
                    break;

                default:
                    status = "error";
                    statusText = "Unavailable";
            }
        } else {
            status = "error";
            statusText = "Unavailable";
        }

        button.classList.add(status);

        const available =
            status === "available" &&
            isSlotAvailable(time);

        button.disabled = !available;

        label.textContent = displayTime(time);
        statusLabel.textContent = statusText;

        button.appendChild(label);
        button.appendChild(statusLabel);

        if (available) {
            button.addEventListener("click", () => {
                selectTime(button, time);
            });
        }

        if (selectedTime === time && available) {
            button.classList.add("selected");
        }

        grid.appendChild(button);
    });

    if (availabilityLoading) {
        note.className = "availability-note";
        note.textContent = "Checking live availability";
    } else if (
        availabilityVerified &&
        availabilityFingerprint === getAvailabilityFingerprint()
    ) {
        note.className = "availability-note success";
        note.textContent =
            "Availability checked using current bookings All times are in Malaysian time";
    } else {
        note.className = "availability-note error";
        note.textContent =
            "Availability has not been verified Please try selecting the date again";
    }

    const continueButton = document.getElementById("dateContinueBtn");

    if (continueButton) {
        continueButton.disabled =
            availabilityLoading ||
            !availabilityVerified ||
            !selectedTime;
    }
}

function selectVerifiedTime(time) {
    if (!isSlotAvailable(time)) {
        clearSelectedTime();
        renderTimeSlots();
        return false;
    }

    selectedTime = time;

    document.getElementById("timeInput").value = time;

    renderTimeSlots();

    return true;
}

function selectTime(button, time) {
    if (button.disabled || !isSlotAvailable(time)) {
        showAlert("This time is no longer available");
        return;
    }

    selectVerifiedTime(time);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Fetch Database-backed Availability
|--------------------------------------------------------------------------
| Security:
| - Uses authenticated customer route
| - Aborts previous requests
| - Ignores stale responses
| - Never treats failed requests as available
|--------------------------------------------------------------------------
*/

async function refreshAvailability(preserveSelection = false) {
    const staffId = document.getElementById("staffInput").value;

    if (
        !selectedDate ||
        !staffId ||
        selectedServices.length === 0 ||
        !isDateWithinBookingWindow(selectedDate)
    ) {
        invalidateAvailability();
        return false;
    }

    const previousTime = preserveSelection ? selectedTime : "";

    availabilityRequestVersion++;

    const requestVersion = availabilityRequestVersion;

    if (availabilityAbortController) {
        availabilityAbortController.abort();
    }

    availabilityAbortController = new AbortController();

    const requestFingerprint = getAvailabilityFingerprint();

    availabilityLoading = true;
    availabilityVerified = false;
    availabilitySlots = [];
    availabilityFingerprint = "";

    if (!preserveSelection) {
        clearSelectedTime();
    }

    renderTimeSlots();

    const params = new URLSearchParams(
        getAvailabilityParams()
    );

    try {
        const response = await fetch(
            availabilityEndpoint + "?" + params.toString(),
            {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                signal: availabilityAbortController.signal,
                cache: "no-store"
            }
        );

        if (!response.ok) {
            let errorMessage = "Unable to check appointment availability";

            try {
                const errorData = await response.json();

                errorMessage =
                    errorData.message ||
                    errorMessage;
            } catch (error) {
                // NS BEAUTY COMMENT: Keep a safe generic error message
            }

            throw new Error(errorMessage);
        }

        const data = await response.json();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Ignore Outdated Responses
        |--------------------------------------------------------------------------
        */

        if (
            requestVersion !== availabilityRequestVersion ||
            requestFingerprint !== getAvailabilityFingerprint()
        ) {
            return false;
        }

        if (
            !Array.isArray(data.slots) ||
            data.timezone !== malaysiaTimezone ||
            data.date !== selectedDate ||
            Number(data.staff_id) !== Number(staffId) ||
            Number(data.duration) !== getTotalDuration()
        ) {
            throw new Error("The server returned invalid availability data");
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Accept Only Recognized Slot Statuses
        |--------------------------------------------------------------------------
        */

        const validStatuses = [
            "available",
            "booked",
            "past",
            "day_off"
        ];

        const normalizedSlots = data.slots.filter(slot => {
            return (
                slot &&
                bookingTimes.includes(slot.time) &&
                validStatuses.includes(slot.status) &&
                typeof slot.available === "boolean"
            );
        });

        if (normalizedSlots.length !== bookingTimes.length) {
            throw new Error("The server returned incomplete availability data");
        }

        availabilitySlots = normalizedSlots;
        availabilityFingerprint = requestFingerprint;
        availabilityLoading = false;
        availabilityVerified = true;

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Retain Selection Only If Still Available
        |--------------------------------------------------------------------------
        */

        if (previousTime) {
            if (isSlotAvailable(previousTime)) {
                selectedTime = previousTime;
                document.getElementById("timeInput").value = previousTime;
            } else {
                clearSelectedTime();
            }
        }

        renderTimeSlots();

        return true;
    } catch (error) {
        if (
            requestVersion !== availabilityRequestVersion ||
            requestFingerprint !== getAvailabilityFingerprint()
        ) {
            return false;
        }

        availabilityLoading = false;
        availabilityVerified = false;
        availabilitySlots = [];
        availabilityFingerprint = "";

        clearSelectedTime();
        renderTimeSlots();

        const note = document.getElementById("availabilityNote");

        if (note) {
            note.className = "availability-note error";
            note.textContent =
                error.name === "AbortError"
                    ? "Availability check cancelled"
                    : error.message;
        }

        return false;
    } finally {
        if (requestVersion === availabilityRequestVersion) {
            availabilityAbortController = null;
        }
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Confirmation Summary
|--------------------------------------------------------------------------
*/

function updateSummary() {
    document.getElementById("summaryType").textContent =
        selectedBookingType === "followup"
            ? "Backjob / Follow-up"
            : "Regular Booking";

    document.getElementById("summaryServices").textContent =
        selectedServices.length
            ? selectedServices.map(service => service.name).join(", ")
            : "None";

    document.getElementById("summaryHistory").textContent =
        selectedBookingType === "followup" && selectedFollowUpId
            ? "#BK-" + String(selectedFollowUpId).padStart(4,"0")
            : "Not applicable";

    document.getElementById("summaryStaff").textContent =
        selectedStaff || "Not selected";

    document.getElementById("summaryDate").textContent =
        displayDate(selectedDate);

    document.getElementById("summaryTime").textContent =
        selectedTime
            ? displayTime(selectedTime) + " MYT"
            : "Not selected";

    document.getElementById("summaryDuration").textContent =
        getTotalDuration() + " minutes";

    document.getElementById("summaryTotal").textContent =
        "RM " + totalPrice.toFixed(2);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Validate Current Booking State
|--------------------------------------------------------------------------
*/

function validateBookingSelections() {
    if (!checkActiveBookingLimit()) {
        return false;
    }

    if (
        selectedServices.length === 0 ||
        selectedServices.length > 3
    ) {
        showAlert("Select between 1 and 3 services");
        return false;
    }

    if (getTotalDuration() <= 0) {
        showAlert("Invalid service duration");
        return false;
    }

    if (
        selectedBookingType === "followup" &&
        !selectedFollowUpId
    ) {
        showAlert("Select one finished booking for the follow-up");
        return false;
    }

    const staffId = document.getElementById("staffInput").value;

    if (!staffId || !isStaffEligible(staffId)) {
        showAlert("Please select an eligible beautician");
        return false;
    }

    if (
        !selectedDate ||
        !isDateWithinBookingWindow(selectedDate)
    ) {
        showAlert("Select a valid appointment date");
        return false;
    }

    if (!selectedTime || !isSlotAvailable(selectedTime)) {
        showAlert("Select an available future appointment time");
        return false;
    }

    if (isStaffOnDayOff(staffId, selectedDate)) {
        showAlert("The selected beautician is on day off");
        return false;
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Final Submission
|--------------------------------------------------------------------------
| Recheck live availability immediately before form submission
| BookingController::store independently verifies it again
|--------------------------------------------------------------------------
*/

async function handleBookingSubmit(event) {
    event.preventDefault();

    if (bookingSubmissionStarted) {
        return;
    }

    if (!validateBookingSelections()) {
        return;
    }

    const form = document.getElementById("bookingForm");

    if (!form.reportValidity()) {
        return;
    }

    const chosenTime = selectedTime;

    const submitButton = document.getElementById("confirmBookingBtn");

    bookingSubmissionStarted = true;

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = "Checking Availability";
    }

    const verified = await refreshAvailability(true);

    if (
        !verified ||
        !chosenTime ||
        !isSlotAvailable(chosenTime) ||
        selectedTime !== chosenTime
    ) {
        bookingSubmissionStarted = false;

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = "Confirm Booking";
        }

        showAlert("This booking needs a new availability check");

        currentStep = 3;
        showStep(3);

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Submit Normal Laravel POST
    |--------------------------------------------------------------------------
    | Existing CSRF protection and controller validation are preserved
    |--------------------------------------------------------------------------
    */

    if (submitButton) {
        submitButton.textContent = "Submitting Booking";
    }

    form.submit();
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Initialize Booking Page
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const bookingForm = document.getElementById("bookingForm");

    if (bookingForm) {
        bookingForm.addEventListener(
            "submit",
            handleBookingSubmit
        );
    }

    const regularButton = document.querySelector(
        '.type-card[data-booking-type="regular"]'
    );

    if (regularButton) {
        setBookingType("regular", regularButton);
    }

    generateNext7Days();
    renderTimeSlots();

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Refresh Availability While Page Remains Open
    |--------------------------------------------------------------------------
    */

    setInterval(function () {
        if (currentStep !== 3 || !selectedDate) {
            return;
        }

        generateNext7Days();

        if (selectedTime && isPastOrStarted(selectedDate, selectedTime)) {
            invalidateAvailability();
        }

        if (!availabilityLoading) {
            refreshAvailability(true);
        }
    }, 30000);
});
</script>
@endsection
