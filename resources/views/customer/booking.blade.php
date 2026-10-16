
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CUSTOMER BOOKING PAGE
|--------------------------------------------------------------------------
| System: Nail Studio & Beauty
|
| Features:
| 1. Regular and follow-up appointments
| 2. Maximum three services per booking
| 3. Qualified beautician selection
| 4. Seven-day booking calendar
| 5. Sunday studio closure
| 6. Real-time appointment availability
| 7. Staff day-off detection
| 8. Booking confirmation
| 9. Existing customer booking limits
| 10. Booking success popup
|
| Important:
| The Laravel BookingController is responsible for final
| server-side validation, overlapping booking protection,
| staff skills, customer eligibility and Sunday closure
|--------------------------------------------------------------------------
--}}

@section('styles')

<style>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: MAIN DESIGN VARIABLES
|--------------------------------------------------------------------------
*/

.ns-booking {
    --ns-brown: #6f4e37;
    --ns-brown-light: #8a7158;
    --ns-dark: #2f2118;
    --ns-muted: #7d6d60;
    --ns-border: rgba(111, 78, 55, 0.14);
    --ns-card: rgba(255, 255, 255, 0.58);
    --ns-gradient: linear-gradient(
        135deg,
        #8a7158,
        #5d3f2c
    );
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: PAGE HEADER
|--------------------------------------------------------------------------
*/

.ns-booking .booking-header {
    text-align: center;
    margin-bottom: 28px;
}

.ns-booking .booking-header h1 {
    font-size: 34px;
    line-height: 1.2;
    margin: 0 0 8px;
}

.ns-booking .booking-header p {
    color: var(--ns-muted);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: MAIN CONTAINERS
|--------------------------------------------------------------------------
*/

.ns-booking .booking-rules,
.ns-booking .booking-panel,
.ns-booking .booking-error {
    width: 100%;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.ns-booking .booking-rules {
    padding: 22px 24px;
    margin-bottom: 25px;
    background: var(--ns-card);
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-radius-large);
}

.ns-booking .booking-rules h2 {
    font-size: 16px;
    margin: 0 0 12px;
}

.ns-booking .booking-rules ul {
    margin: 0;
    padding-left: 20px;
}

.ns-booking .booking-rules li {
    margin-bottom: 7px;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
}

.ns-booking .booking-rules strong {
    color: var(--ns-dark);
}

.ns-booking .booking-rule-footer {
    border-top: 1px solid var(--ns-border);
    padding-top: 14px;
    margin-top: 14px;
    color: var(--ns-muted);
    font-size: 13px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: ERROR ALERT
|--------------------------------------------------------------------------
*/

.ns-booking .booking-error {
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: var(--ns-radius-small);
    border: 1px solid rgba(184, 77, 77, 0.22);
    background: rgba(184, 77, 77, 0.10);
    color: #963b35;
    font-size: 13px;
    line-height: 1.6;
}

.ns-booking #alertBox {
    display: none;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: FOUR-STEP INDICATOR
|--------------------------------------------------------------------------
*/

.ns-booking .booking-steps {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    max-width: 850px;
    gap: 12px;
    margin: 0 auto 28px;
}

.ns-booking .booking-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    min-width: 0;
    color: var(--ns-muted);
    font-size: 12px;
    font-weight: 700;
}

.ns-booking .step-circle {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid var(--ns-border);
    font-size: 13px;
    font-weight: 800;
}

.ns-booking .booking-step.active .step-circle,
.ns-booking .booking-step.done .step-circle {
    background: var(--ns-gradient);
    color: #fff;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BOOKING PANELS
|--------------------------------------------------------------------------
*/

.ns-booking .booking-panel {
    display: none;
    min-width: 0;
    padding: 32px;
    background: var(--glass, rgba(255, 255, 255, 0.58));
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid var(--border, rgba(255, 255, 255, 0.72));
    border-radius: var(--ns-radius-large);
    box-shadow: 0 20px 55px rgba(47, 33, 24, 0.08);
}

.ns-booking .booking-panel.active {
    display: block;
}

.ns-booking .booking-panel h2 {
    font-size: 21px;
    margin: 0 0 8px;
}

.ns-booking .booking-panel h3 {
    font-size: 16px;
    margin: 0 0 10px;
}

.ns-booking .section-desc {
    margin: 0 0 24px;
    color: var(--ns-muted);
    font-size: 14px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BOOKING TYPE
|--------------------------------------------------------------------------
*/

.ns-booking .booking-type-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 28px;
}

.ns-booking .type-card,
.ns-booking .service-card,
.ns-booking .staff-card,
.ns-booking .history-card,
.ns-booking .calendar-card,
.ns-booking .summary-card,
.ns-booking .details-card {
    min-width: 0;
    background: rgba(255, 255, 255, 0.48);
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-radius-small);
}

.ns-booking .type-card {
    width: 100%;
    padding: 20px;
    text-align: left;
    cursor: pointer;
    color: var(--ns-dark);
}

.ns-booking .type-card h3 {
    font-size: 15px;
    margin-bottom: 8px;
}

.ns-booking .type-card p,
.ns-booking .service-card p,
.ns-booking .staff-card p,
.ns-booking .history-card p {
    font-size: 13px;
    line-height: 1.6;
    color: var(--ns-muted);
    margin: 0;
}

.ns-booking .type-card.active,
.ns-booking .service-card.selected,
.ns-booking .staff-card.selected {
    background: rgba(255, 255, 255, 0.86);
    border-color: var(--ns-brown);
    box-shadow: inset 0 0 0 1px var(--ns-brown);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SERVICE SELECTION
|--------------------------------------------------------------------------
*/

.ns-booking .services-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.ns-booking .service-card {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    min-height: 180px;
    padding: 18px;
    text-align: left;
    cursor: pointer;
    color: var(--ns-dark);
}

.ns-booking .service-card h4 {
    font-size: 15px;
    margin: 0;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.ns-booking .service-card p {
    flex: 1;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.ns-booking .service-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ns-booking .service-badges span {
    display: inline-flex;
    padding: 7px 10px;
    background: rgba(255, 255, 255, 0.78);
    border-radius: var(--ns-radius-small);
    color: var(--ns-brown);
    font-size: 11px;
    font-weight: 800;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: FOLLOW-UP HISTORY
|--------------------------------------------------------------------------
*/

.ns-booking .followup-container {
    display: none;
}

.ns-booking .followup-container.show {
    display: block;
}

.ns-booking .history-grid {
    display: grid;
    gap: 14px;
    margin-bottom: 22px;
}

.ns-booking .history-card {
    padding: 18px;
}

.ns-booking .history-card h4 {
    margin: 0 0 10px;
    font-size: 15px;
}

.ns-booking .history-card p {
    margin-bottom: 7px;
}

.ns-booking .history-card.selected {
    border-color: var(--ns-brown);
}

.ns-booking .follow-service-list {
    display: grid;
    gap: 10px;
    margin-top: 16px;
}

.ns-booking .follow-service-option {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-radius-small);
    background: rgba(255, 255, 255, 0.58);
    cursor: pointer;
}

.ns-booking .follow-service-option input {
    width: 17px;
    height: 17px;
    margin-top: 3px;
    flex-shrink: 0;
    accent-color: var(--ns-brown);
}

.ns-booking .follow-service-option small {
    display: block;
    margin-top: 5px;
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.5;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BEAUTICIAN CARDS
|--------------------------------------------------------------------------
*/

.ns-booking .staff-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.ns-booking .staff-card {
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
    padding: 18px;
    color: var(--ns-dark);
    text-align: left;
    cursor: pointer;
}

.ns-booking .staff-card:disabled {
    opacity: 0.42;
    cursor: not-allowed;
}

.ns-booking .staff-avatar {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.8);
    font-size: 22px;
}

.ns-booking .staff-card small {
    font-size: 11px;
    color: var(--ns-muted);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: DATE AND TIME
|--------------------------------------------------------------------------
*/

.ns-booking .calendar-time {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.ns-booking .calendar-card {
    padding: 20px;
}

.ns-booking .calendar-grid,
.ns-booking .time-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 16px;
}

.ns-booking .calendar-day,
.ns-booking .time-btn {
    width: 100%;
    min-width: 0;
    min-height: 55px;
    padding: 12px 8px;
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-radius-small);
    background: rgba(255, 255, 255, 0.68);
    color: var(--ns-dark);
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
}

.ns-booking .calendar-day span,
.ns-booking .calendar-day small,
.ns-booking .time-btn span,
.ns-booking .time-btn small {
    display: block;
}

.ns-booking .calendar-day small,
.ns-booking .time-btn small {
    margin-top: 5px;
    font-size: 10px;
    line-height: 1.4;
    font-weight: 600;
}

.ns-booking .calendar-day.selected,
.ns-booking .time-btn.selected {
    background: var(--ns-gradient);
    color: #fff;
    border-color: transparent;
}

.ns-booking .calendar-day:disabled,
.ns-booking .time-btn:disabled {
    background: rgba(130, 130, 130, 0.14);
    color: #99928c;
    border-color: rgba(130, 130, 130, 0.12);
    opacity: 0.78;
    cursor: not-allowed;
}

.ns-booking .calendar-day.closed {
    background: rgba(166, 95, 81, 0.12);
    color: #a45c52;
    border-color: rgba(166, 95, 81, 0.20);
}

.ns-booking .time-btn.closed {
    background: rgba(166, 95, 81, 0.10);
    color: #a45c52;
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CONFIRMATION
|--------------------------------------------------------------------------
*/

.ns-booking .confirmation-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
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
    padding: 10px 0;
    font-size: 13px;
    border-bottom: 1px solid rgba(111, 78, 55, 0.08);
}

.ns-booking .summary-row strong {
    max-width: 65%;
    text-align: right;
    overflow-wrap: anywhere;
}

.ns-booking .summary-row.total {
    margin-top: 10px;
    padding-top: 15px;
    border-top: 1px solid var(--ns-border);
    border-bottom: none;
    color: var(--ns-brown);
    font-size: 15px;
    font-weight: 900;
}

.ns-booking .form-label {
    display: block;
    margin-bottom: 8px;
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
    background: rgba(255, 255, 255, 0.72);
    color: var(--ns-dark);
    font-size: 14px;
    outline: none;
}

.ns-booking .form-input:focus {
    border-color: var(--ns-brown);
    box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.10);
}

.ns-booking .form-input[readonly] {
    background: rgba(220, 215, 209, 0.25);
    color: var(--ns-muted);
}

.ns-booking textarea.form-input {
    min-height: 90px;
    resize: vertical;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BUTTONS
|--------------------------------------------------------------------------
*/

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
    background: rgba(255, 255, 255, 0.65);
    color: var(--ns-brown);
    border: 1px solid var(--ns-border);
}

.ns-booking .booking-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.ns-booking button:focus-visible,
.ns-booking a:focus-visible {
    outline: 3px solid rgba(111, 78, 55, 0.40);
    outline-offset: 3px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: EMPTY STATE
|--------------------------------------------------------------------------
*/

.ns-booking .empty-box {
    grid-column: 1 / -1;
    padding: 24px;
    background: rgba(255, 255, 255, 0.46);
    border-radius: var(--ns-radius-small);
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
    text-align: center;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BOOKING SUCCESS POPUP
|--------------------------------------------------------------------------
*/

.ns-booking .success-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background: rgba(47, 33, 24, 0.35);
    backdrop-filter: blur(9px);
}

.ns-booking .success-modal {
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 32px;
    border-radius: var(--ns-radius-large);
    background: rgba(255, 255, 255, 0.96);
    text-align: center;
    box-shadow: 0 25px 65px rgba(47, 33, 24, 0.20);
}

.ns-booking .success-icon {
    display: grid;
    place-items: center;
    width: 65px;
    height: 65px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: var(--ns-gradient);
    color: white;
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: RESPONSIVENESS
|--------------------------------------------------------------------------
*/

@media (max-width: 900px) {
    .ns-booking .services-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ns-booking .calendar-time,
    .ns-booking .confirmation-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .ns-booking .booking-header h1 {
        font-size: 28px;
    }

    .ns-booking .booking-panel {
        padding: 20px 16px;
    }

    .ns-booking .booking-rules {
        padding: 18px;
    }

    .ns-booking .booking-type-grid,
    .ns-booking .services-grid,
    .ns-booking .staff-list {
        grid-template-columns: 1fr;
    }

    .ns-booking .booking-steps {
        gap: 5px;
    }

    .ns-booking .booking-step {
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

@media (prefers-reduced-motion: reduce) {
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
    | NS BEAUTY COMMENT: CUSTOMER DEFAULTS
    |--------------------------------------------------------------------------
    */

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
    | NS BEAUTY COMMENT: STAFF SKILLS FOR FRONTEND ELIGIBILITY
    |--------------------------------------------------------------------------
    */

    $staffSkills = $staffMembers->mapWithKeys(function ($staff) {
        return [
            (string) $staff->id => $staff->services
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all()
        ];
    })->all();

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: UPCOMING STAFF DAY-OFF DATA
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
            )->format('Y-m-d')
        ];
    })->values()->all();
@endphp

<div class="ns-booking">

    {{-- NS BEAUTY COMMENT: PAGE HEADER --}}

    <header class="booking-header">
        <h1>Book Appointment</h1>
        <p>Schedule your next visit in four simple steps</p>
    </header>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: BOOKING RULES
    |--------------------------------------------------------------------------
    --}}

    <div class="booking-rules">

        <h2>Booking Rules</h2>

        <ul>
            <li>
                Maximum of <strong>3 services</strong> per booking
            </li>

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
                <strong>Closed every Sunday</strong>
                — Sunday appointments cannot be selected
            </li>

            <li>
                Appointments awaiting emergency rescheduling
                count toward your booking limit
            </li>

            <li>
                Beauticians must be active and qualified
                for all selected services
            </li>

            <li>
                Availability depends on existing appointments
                and beautician day-offs
            </li>
        </ul>

        <div class="booking-rule-footer">
            Active / awaiting rescheduling bookings:
            <strong>{{ $customerActiveBookings }}</strong>

            <span aria-hidden="true"> · </span>

            Booking limit:
            <strong>
                {{ $customerCompletedBookings >= 1 ? 2 : 1 }}
            </strong>
        </div>

    </div>

    {{-- NS BEAUTY COMMENT: SERVER VALIDATION ERRORS --}}

    @if ($errors->any())
        <div class="booking-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div
        id="alertBox"
        class="booking-error"
        role="alert"
        aria-live="assertive"
    ></div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: FOUR BOOKING STEPS
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

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: MAIN LARAVEL BOOKING FORM
    |--------------------------------------------------------------------------
    --}}

    <form
        id="bookingForm"
        method="POST"
        action="{{ route('customer.booking.store') }}"
    >
        @csrf

        {{-- NS BEAUTY COMMENT: BACKEND INPUT FIELDS --}}

        <input
            type="hidden"
            name="booking_type"
            id="bookingTypeInput"
            value="regular"
        >

        <input
            type="hidden"
            name="selected_service_ids"
            id="selectedServiceIdsInput"
            value=""
        >

        <input
            type="hidden"
            name="selected_services"
            id="selectedServicesInput"
            value=""
        >

        <input
            type="hidden"
            name="follow_up_appointment_id"
            id="followUpAppointmentInput"
            value=""
        >

        <input
            type="hidden"
            name="staff_id"
            id="staffInput"
            value=""
        >

        <input
            type="hidden"
            name="appointment_date"
            id="dateInput"
            value=""
        >

        <input
            type="hidden"
            name="start_time"
            id="timeInput"
            value=""
        >

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: STEP 1 — SERVICES
        |--------------------------------------------------------------------------
        --}}

        <section class="booking-panel active" id="step1">

            <h2>Choose Booking Type</h2>

            <p class="section-desc">
                Select a regular booking or a backjob / follow-up
                from your completed appointment history
            </p>

            <div class="booking-type-grid">

                <button
                    type="button"
                    class="type-card active"
                    data-booking-type="regular"
                    aria-pressed="true"
                    onclick="setBookingType('regular', this)"
                >
                    <h3>Regular Booking</h3>
                    <p>For a new appointment or beauty treatment</p>
                </button>

                <button
                    type="button"
                    class="type-card"
                    data-booking-type="followup"
                    aria-pressed="false"
                    onclick="setBookingType('followup', this)"
                >
                    <h3>Backjob / Follow-up</h3>
                    <p>Select services from a previously finished booking</p>
                </button>

            </div>

            {{-- NS BEAUTY COMMENT: REGULAR SERVICES --}}

            <div id="regularServicesBox">

                <h2>Choose Services</h2>

                <p class="section-desc">
                    Select up to three treatments for your visit
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

                            <p>
                                {{ $service->description ?: 'No description available' }}
                            </p>

                            <div class="service-badges">
                                <span>
                                    {{ $service->duration }} minutes
                                </span>

                                <span>
                                    RM {{ number_format((float) $service->price, 2) }}
                                </span>
                            </div>

                        </button>

                    @empty

                        <div class="empty-box">
                            No available services found
                        </div>

                    @endforelse

                </div>

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: FOLLOW-UP SERVICE HISTORY
            |--------------------------------------------------------------------------
            --}}

            <div class="followup-container" id="followupBox">

                <h2>Choose Services From Finished History</h2>

                <p class="section-desc">
                    Select one finished appointment and choose
                    the services requiring a follow-up
                </p>

                <div class="history-grid">

                    @forelse ($eligibleFollowUps as $history)

                        @php
                            $historyStaffName =
                                $history->staff?->full_name
                                ?? 'Not assigned';

                            $historyDate = $history->appointment_date
                                ? \Carbon\Carbon::parse(
                                    $history->appointment_date
                                )->format('F d, Y')
                                : 'Not available';
                        @endphp

                        <div
                            class="history-card"
                            id="historyCard{{ $history->id }}"
                        >

                            <h4>
                                Booking #BK-{{ str_pad($history->id, 4, '0', STR_PAD_LEFT) }}
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
                                            <strong>
                                                {{ $service->service_name }}
                                            </strong>

                                            <small>
                                                {{ $duration }} minutes · RM 0.00
                                            </small>
                                        </span>

                                    </label>

                                @empty

                                    <div class="empty-box">
                                        No recorded services for this appointment
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

                <label
                    class="form-label"
                    for="followUpReason"
                >
                    Backjob / Follow-up Reason
                </label>

                <textarea
                    class="form-input"
                    id="followUpReason"
                    name="follow_up_reason"
                    maxlength="500"
                    placeholder="Describe the repair or adjustment needed"
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
        | NS BEAUTY COMMENT: STEP 2 — BEAUTICIAN
        |--------------------------------------------------------------------------
        --}}

        <section class="booking-panel" id="step2">

            <h2>Choose Beautician</h2>

            <p class="section-desc">
                Choose an active beautician qualified
                to perform every selected service
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

                        <span class="staff-avatar" aria-hidden="true">
                            👤
                        </span>

                        <span>
                            <strong>{{ $staff->full_name }}</strong>

                            <p>Beautician</p>

                            <small>
                                Status: {{ $staff->status }}
                            </small>
                        </span>

                    </button>

                @empty

                    <div class="empty-box">
                        No active beauticians available
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
        | NS BEAUTY COMMENT: STEP 3 — DATE AND TIME
        |--------------------------------------------------------------------------
        --}}

        <section class="booking-panel" id="step3">

            <h2>Date &amp; Time</h2>

            <p class="section-desc">
                Choose a date within the next seven days
                The studio is closed every Sunday
                All appointment times are in Malaysia Time
            </p>

            <div class="calendar-time">

                <div class="calendar-card">

                    <h3>Available Dates</h3>

                    <p class="timezone-note">
                        Monday to Saturday · Malaysia Time (MYT, UTC+8)
                    </p>

                    <div
                        class="calendar-grid"
                        id="dateGrid"
                    ></div>

                </div>

                <div class="calendar-card">

                    <h3>Select Time</h3>

                    <p class="timezone-note">
                        Times depend on your selected beautician
                        and total service duration
                    </p>

                    <div
                        class="time-grid"
                        id="timeGrid"
                        aria-live="polite"
                    ></div>

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
                    disabled
                >
                    Continue →
                </button>

            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: STEP 4 — CONFIRMATION
        |--------------------------------------------------------------------------
        --}}

        <section class="booking-panel" id="step4">

            <h2>Confirm Booking</h2>

            <p class="section-desc">
                Review your appointment details before submitting
            </p>

            <div class="confirmation-grid">

                <div class="summary-card">

                    <h3>Booking Summary</h3>

                    <div class="summary-row">
                        <span>Booking Type</span>
                        <strong id="summaryType">
                            Regular Booking
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Selected Services</span>
                        <strong id="summaryServices">
                            None
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Linked History</span>
                        <strong id="summaryHistory">
                            Not applicable
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Beautician</span>
                        <strong id="summaryStaff">
                            Not selected
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Date</span>
                        <strong id="summaryDate">
                            Not selected
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Time</span>
                        <strong id="summaryTime">
                            Not selected
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Duration</span>
                        <strong id="summaryDuration">
                            0 minutes
                        </strong>
                    </div>

                    <div class="summary-row total">
                        <span>Total</span>
                        <strong id="summaryTotal">
                            RM 0.00
                        </strong>
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
                        maxlength="255"
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
                        maxlength="30"
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
    | NS BEAUTY COMMENT: SUCCESS POPUP
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

                <div class="success-icon">
                    ✓
                </div>

                <h2 id="successTitle">
                    Booking Recorded
                </h2>

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
| NS BEAUTY COMMENT: CUSTOMER BOOKING STATE
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

let availabilitySlots = [];
let availabilityLoading = false;
let availabilityVerified = false;
let availabilityFingerprint = "";

let availabilityRequestVersion = 0;
let availabilityAbortController = null;

let bookingSubmissionStarted = false;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: LARAVEL DATA
|--------------------------------------------------------------------------
*/

const customerCompletedBookings = {{ (int) $customerCompletedBookings }};
const customerActiveBookings = {{ (int) $customerActiveBookings }};

const staffSkills = @json($staffSkills);
const staffDaysOff = @json($staffDaysOff);

const availabilityEndpoint = @js(
    route('customer.booking.availability')
);

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
| NS BEAUTY COMMENT: GENERAL HELPERS
|--------------------------------------------------------------------------
*/

function bookingElement(id) {
    return document.getElementById(id);
}

function showAlert(message) {
    const box = bookingElement("alertBox");

    if (!box) {
        return;
    }

    box.textContent = message;
    box.style.display = "block";

    box.scrollIntoView({
        behavior: "smooth",
        block: "center"
    });
}

function hideAlert() {
    const box = bookingElement("alertBox");

    if (box) {
        box.style.display = "none";
    }
}

function getBookingLimit() {
    return customerCompletedBookings >= 1 ? 2 : 1;
}

function checkActiveBookingLimit() {
    if (customerActiveBookings >= getBookingLimit()) {
        showAlert(
            "You have reached your active booking limit — appointments awaiting rescheduling are included"
        );

        return false;
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: MALAYSIAN DATE AND TIME
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
    const [year, month, day] = dateString
        .split("-")
        .map(Number);

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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SUNDAY STUDIO CLOSURE
|--------------------------------------------------------------------------
| Sunday is identified from the selected calendar date
| using UTC at noon to avoid browser timezone shifts
|--------------------------------------------------------------------------
*/

function isSundayClosed(dateString) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString || "")) {
        return false;
    }

    const [year, month, day] = dateString
        .split("-")
        .map(Number);

    const date = new Date(
        Date.UTC(year, month - 1, day, 12)
    );

    return date.getUTCDay() === 0;
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

function displayTime(timeString) {
    const [hour, minute] = timeString
        .split(":")
        .map(Number);

    const period = hour >= 12 ? "PM" : "AM";
    const twelveHour = hour % 12 || 12;

    return `${twelveHour}:${String(minute).padStart(2, "0")} ${period}`;
}

function displayDate(dateString) {
    if (!dateString) {
        return "Not selected";
    }

    const [year, month, day] = dateString
        .split("-")
        .map(Number);

    const date = new Date(
        Date.UTC(year, month - 1, day, 12)
    );

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
| NS BEAUTY COMMENT: BOOKING STEP NAVIGATION
|--------------------------------------------------------------------------
*/

function showStep(step) {
    currentStep = step;

    document.querySelectorAll(".ns-booking .booking-panel")
        .forEach(panel => {
            panel.classList.remove("active");
        });

    const panel = bookingElement("step" + step);

    if (!panel) {
        return;
    }

    panel.classList.add("active");

    for (let i = 1; i <= 4; i++) {
        const indicator = bookingElement("stepIndicator" + i);

        indicator.classList.remove("active", "done");

        if (i < step) {
            indicator.classList.add("done");
        } else if (i === step) {
            indicator.classList.add("active");
        }
    }

    if (step === 3) {
        generateNext7Days();

        if (selectedDate) {
            refreshAvailability(true);
        } else {
            renderTimeSlots();
        }
    }

    if (step === 4) {
        updateSummary();
    }

    panel.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
}

async function nextStep() {
    hideAlert();

    if (!checkActiveBookingLimit()) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: STEP 1 VALIDATION
    |--------------------------------------------------------------------------
    */

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
            showAlert(
                "Select services from one finished appointment"
            );

            return;
        }

        if (getTotalDuration() <= 0) {
            showAlert(
                "The selected services have an invalid duration"
            );

            return;
        }

        updateStaffEligibility();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: STEP 2 VALIDATION
    |--------------------------------------------------------------------------
    */

    if (currentStep === 2) {
        const staffId = bookingElement("staffInput").value;

        if (!staffId || !isStaffEligible(staffId)) {
            showAlert("Please select an eligible beautician");
            return;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: STEP 3 VALIDATION
    |--------------------------------------------------------------------------
    */

    if (currentStep === 3) {
        if (
            !selectedDate ||
            !isDateWithinBookingWindow(selectedDate)
        ) {
            showAlert(
                "Select a date within the seven-day booking window"
            );

            return;
        }

        if (isSundayClosed(selectedDate)) {
            showAlert(
                "Nail Studio & Beauty is closed every Sunday"
            );

            return;
        }

        if (!selectedTime) {
            showAlert("Please select an available time");
            return;
        }

        const chosenTime = selectedTime;

        const verified = await refreshAvailability(true);

        if (!verified || !isSlotAvailable(chosenTime)) {
            showAlert(
                "The selected appointment time is no longer available"
            );

            return;
        }

        selectVerifiedTime(chosenTime);
    }

    if (currentStep < 4) {
        showStep(currentStep + 1);
    }
}

function prevStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BOOKING TYPE SELECTION
|--------------------------------------------------------------------------
*/

function setBookingType(type, element) {
    selectedBookingType = type;

    bookingElement("bookingTypeInput").value = type;

    document.querySelectorAll(".type-card")
        .forEach(card => {
            const active = card === element;

            card.classList.toggle("active", active);

            card.setAttribute(
                "aria-pressed",
                active ? "true" : "false"
            );
        });

    bookingElement("regularServicesBox").style.display =
        type === "regular" ? "block" : "none";

    bookingElement("followupBox").classList.toggle(
        "show",
        type === "followup"
    );

    resetServiceSelections();
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SERVICE CALCULATIONS
|--------------------------------------------------------------------------
*/

function getTotalDuration() {
    return selectedServices.reduce(
        (sum, service) =>
            sum + Number(service.duration || 0),
        0
    );
}

function calculateTotal() {
    totalPrice = selectedServices.reduce(
        (sum, service) =>
            sum + Number(service.price || 0),
        0
    );
}

function updateHiddenServiceInputs() {
    bookingElement("selectedServiceIdsInput").value =
        selectedServices
            .map(service => service.id)
            .join(",");

    bookingElement("selectedServicesInput").value =
        selectedServices
            .map(service => service.name)
            .join(", ");
}

function resetServiceSelections() {
    selectedServices = [];
    selectedFollowUpId = "";
    totalPrice = 0;

    bookingElement("followUpAppointmentInput").value = "";

    document.querySelectorAll(".service-card")
        .forEach(card => {
            card.classList.remove("selected");
            card.setAttribute("aria-pressed", "false");
        });

    document.querySelectorAll(".history-card")
        .forEach(card => {
            card.classList.remove("selected");
        });

    document.querySelectorAll(".follow-service-checkbox")
        .forEach(input => {
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: REGULAR SERVICE SELECTION
|--------------------------------------------------------------------------
*/

function selectRegularService(
    card,
    id,
    name,
    price,
    duration
) {
    if (selectedBookingType !== "regular") {
        return;
    }

    const index = selectedServices.findIndex(
        service => service.id === Number(id)
    );

    if (index >= 0) {
        selectedServices.splice(index, 1);

        card.classList.remove("selected");

        card.setAttribute(
            "aria-pressed",
            "false"
        );
    } else {
        if (selectedServices.length >= 3) {
            showAlert("Maximum of 3 services per booking");
            return;
        }

        selectedServices.push({
            id: Number(id),
            name: name,
            price: Number(price),
            duration: Number(duration)
        });

        card.classList.add("selected");

        card.setAttribute(
            "aria-pressed",
            "true"
        );
    }

    selectionChanged();
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: FOLLOW-UP SERVICE SELECTION
|--------------------------------------------------------------------------
*/

function selectFollowUpService(
    input,
    appointmentId,
    originalStaffId,
    originalStaffName,
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

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: USE SERVICES FROM ONE HISTORY ONLY
    |--------------------------------------------------------------------------
    */

    const changingHistory =
        selectedFollowUpId !== "" &&
        Number(selectedFollowUpId) !== appointmentId;

    if (input.checked && changingHistory) {
        selectedServices = [];

        document.querySelectorAll(".follow-service-checkbox")
            .forEach(box => {
                if (box !== input) {
                    box.checked = false;
                }
            });
    }

    if (input.checked) {
        if (selectedServices.length >= 3) {
            input.checked = false;

            showAlert(
                "Maximum of 3 follow-up services per booking"
            );

            return;
        }

        selectedFollowUpId = appointmentId;

        if (
            !selectedServices.some(
                service => service.id === serviceId
            )
        ) {
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

    bookingElement("followUpAppointmentInput").value =
        selectedFollowUpId;

    document.querySelectorAll(".history-card")
        .forEach(card => {
            card.classList.toggle(
                "selected",
                card.id ===
                    "historyCard" + selectedFollowUpId
            );
        });

    selectionChanged();

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: PREFER ORIGINAL BEAUTICIAN
    |--------------------------------------------------------------------------
    */

    if (input.checked && selectedFollowUpId) {
        selectOriginalStaff(
            originalStaffId,
            originalStaffName
        );
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: STAFF ELIGIBILITY
|--------------------------------------------------------------------------
*/

function isStaffEligible(staffId) {
    if (!staffId || selectedServices.length === 0) {
        return false;
    }

    const assignedServices =
        staffSkills[String(staffId)] || [];

    const assignedIds = assignedServices.map(Number);

    return selectedServices.every(service =>
        assignedIds.includes(Number(service.id))
    );
}

function updateStaffEligibility() {
    const selectedStaffId =
        bookingElement("staffInput").value;

    document.querySelectorAll(".staff-card")
        .forEach(card => {
            const eligible = isStaffEligible(
                card.dataset.staffId
            );

            card.disabled = !eligible;

            card.classList.toggle(
                "selected",
                eligible &&
                selectedStaffId === card.dataset.staffId
            );
        });

    if (
        selectedStaffId &&
        !isStaffEligible(selectedStaffId)
    ) {
        bookingElement("staffInput").value = "";
        selectedStaff = "";
    }
}

function selectBeautician(card, id, name) {
    if (card.disabled || !isStaffEligible(id)) {
        showAlert(
            "This beautician is not qualified for all selected services"
        );

        return;
    }

    document.querySelectorAll(".staff-card")
        .forEach(item => {
            item.classList.remove("selected");
        });

    card.classList.add("selected");

    bookingElement("staffInput").value = id;

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

    if (
        card &&
        !card.disabled &&
        isStaffEligible(staffId)
    ) {
        selectBeautician(
            card,
            Number(staffId),
            staffName
        );
    }
}

function isStaffOnDayOff(staffId, dateString) {
    return staffDaysOff.some(dayOff =>
        Number(dayOff.staff_id) === Number(staffId) &&
        dateString >= dayOff.start_date &&
        dateString <= dayOff.end_date
    );
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: GENERATE SEVEN-DAY CALENDAR
|--------------------------------------------------------------------------
| Sundays are visible but disabled
|--------------------------------------------------------------------------
*/

function generateNext7Days() {
    const grid = bookingElement("dateGrid");

    grid.innerHTML = "";

    getBookingDates().forEach(dateString => {
        const sundayClosed = isSundayClosed(dateString);

        const button = document.createElement("button");

        button.type = "button";
        button.className = "calendar-day";

        if (sundayClosed) {
            button.classList.add("closed");
        }

        button.disabled = sundayClosed;

        button.title = sundayClosed
            ? "Studio closed every Sunday"
            : "Choose appointment date";

        button.classList.toggle(
            "selected",
            !sundayClosed &&
            selectedDate === dateString
        );

        const dateLabel = document.createElement("span");

        dateLabel.textContent = displayDate(dateString);

        const statusLabel = document.createElement("small");

        statusLabel.textContent = sundayClosed
            ? "Closed"
            : "Select";

        button.appendChild(dateLabel);
        button.appendChild(statusLabel);

        if (!sundayClosed) {
            button.addEventListener("click", () => {
                selectDate(button, dateString);
            });
        }

        grid.appendChild(button);
    });

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: CLEAR EXPIRED OR CLOSED SELECTIONS
    |--------------------------------------------------------------------------
    */

    if (
        selectedDate &&
        (
            !isDateWithinBookingWindow(selectedDate) ||
            isSundayClosed(selectedDate)
        )
    ) {
        selectedDate = "";

        bookingElement("dateInput").value = "";

        invalidateAvailability();
    }
}

function selectDate(button, dateString) {
    if (!isDateWithinBookingWindow(dateString)) {
        showAlert(
            "The selected date is outside the seven-day booking window"
        );

        return;
    }

    if (isSundayClosed(dateString)) {
        showAlert(
            "Nail Studio & Beauty is closed every Sunday"
        );

        return;
    }

    document.querySelectorAll(".calendar-day")
        .forEach(day => {
            day.classList.remove("selected");
        });

    button.classList.add("selected");

    selectedDate = dateString;

    bookingElement("dateInput").value = dateString;

    invalidateAvailability();
    refreshAvailability();
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: AVAILABILITY REQUEST FINGERPRINT
|--------------------------------------------------------------------------
*/

function getAvailabilityFingerprint() {
    const staffId =
        bookingElement("staffInput").value;

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
        staff_id: bookingElement("staffInput").value,
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
| NS BEAUTY COMMENT: INVALIDATE OLD AVAILABILITY
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

    const input = bookingElement("timeInput");

    if (input) {
        input.value = "";
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CHECK VERIFIED SLOT AVAILABILITY
|--------------------------------------------------------------------------
*/

function isSlotAvailable(time) {
    if (
        !availabilityVerified ||
        availabilityLoading ||
        availabilityFingerprint !==
            getAvailabilityFingerprint()
    ) {
        return false;
    }

    if (
        !isDateWithinBookingWindow(selectedDate) ||
        isSundayClosed(selectedDate)
    ) {
        return false;
    }

    if (isPastOrStarted(selectedDate, time)) {
        return false;
    }

    return availabilitySlots.some(slot =>
        slot.time === time &&
        slot.available === true &&
        slot.status === "available"
    );
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: RENDER APPOINTMENT TIME SLOTS
|--------------------------------------------------------------------------
| Accepted status values:
| available, booked, past, day_off, closed
|--------------------------------------------------------------------------
*/

function renderTimeSlots() {
    const grid = bookingElement("timeGrid");
    const note = bookingElement("availabilityNote");
    const continueButton = bookingElement("dateContinueBtn");

    if (!grid || !note) {
        return;
    }

    grid.innerHTML = "";

    if (!selectedDate) {
        note.className = "availability-note";

        note.textContent =
            "Select a date to check appointment availability";

        if (continueButton) {
            continueButton.disabled = true;
        }

        return;
    }

    const staffId =
        bookingElement("staffInput").value;

    const sundayClosed = isSundayClosed(selectedDate);

    const onDayOff =
        staffId &&
        isStaffOnDayOff(staffId, selectedDate);

    bookingTimes.forEach(time => {
        const slot = availabilitySlots.find(
            item => item.time === time
        );

        const button = document.createElement("button");

        const label = document.createElement("span");

        const statusLabel = document.createElement("small");

        button.type = "button";
        button.className = "time-btn";

        label.textContent = displayTime(time);

        let status = "loading";
        let statusText = "Checking";

        if (sundayClosed) {
            status = "closed";
            statusText = "Studio closed";
        } else if (isPastOrStarted(selectedDate, time)) {
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
            availabilityFingerprint ===
                getAvailabilityFingerprint() &&
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

                case "closed":
                    statusText = "Studio closed";
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

        if (
            selectedTime === time &&
            available
        ) {
            button.classList.add("selected");
        }

        statusLabel.textContent = statusText;

        button.appendChild(label);
        button.appendChild(statusLabel);

        if (available) {
            button.addEventListener("click", () => {
                selectVerifiedTime(time);
            });
        }

        grid.appendChild(button);
    });

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: SHOW APPROPRIATE AVAILABILITY MESSAGE
    |--------------------------------------------------------------------------
    */

    if (sundayClosed) {
        note.className = "availability-note error";

        note.textContent =
            "The studio is closed every Sunday — please select Monday to Saturday";
    } else if (availabilityLoading) {
        note.className = "availability-note";

        note.textContent =
            "Checking live appointment availability";
    } else if (
        availabilityVerified &&
        availabilityFingerprint ===
            getAvailabilityFingerprint()
    ) {
        note.className = "availability-note success";

        note.textContent =
            "Availability checked using current appointments · Malaysia Time";
    } else {
        note.className = "availability-note error";

        note.textContent =
            "Availability has not been verified — please try selecting the date again";
    }

    if (continueButton) {
        continueButton.disabled =
            availabilityLoading ||
            !availabilityVerified ||
            !selectedTime ||
            !isSlotAvailable(selectedTime);
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SELECT VERIFIED APPOINTMENT TIME
|--------------------------------------------------------------------------
*/

function selectVerifiedTime(time) {
    if (!isSlotAvailable(time)) {
        clearSelectedTime();
        renderTimeSlots();

        return false;
    }

    selectedTime = time;

    bookingElement("timeInput").value = time;

    renderTimeSlots();

    return true;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: FETCH LIVE AVAILABILITY
|--------------------------------------------------------------------------
| Handles:
| - Authentication cookies
| - Real-time database response
| - Stale requests
| - Cancelled requests
| - Invalid or incomplete slot data
|--------------------------------------------------------------------------
*/

async function refreshAvailability(preserveSelection = false) {
    const staffId =
        bookingElement("staffInput").value;

    if (
        !selectedDate ||
        !staffId ||
        selectedServices.length === 0 ||
        !isDateWithinBookingWindow(selectedDate) ||
        isSundayClosed(selectedDate)
    ) {
        invalidateAvailability();
        return false;
    }

    const previousTime =
        preserveSelection ? selectedTime : "";

    availabilityRequestVersion++;

    const requestVersion =
        availabilityRequestVersion;

    if (availabilityAbortController) {
        availabilityAbortController.abort();
    }

    availabilityAbortController =
        new AbortController();

    const requestFingerprint =
        getAvailabilityFingerprint();

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
            let errorMessage =
                "Unable to check appointment availability";

            try {
                const errorData = await response.json();

                errorMessage =
                    errorData.message || errorMessage;
            } catch (error) {
                // NS BEAUTY COMMENT: Keep generic error message
            }

            throw new Error(errorMessage);
        }

        const data = await response.json();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: DISCARD OUTDATED RESPONSES
        |--------------------------------------------------------------------------
        */

        if (
            requestVersion !== availabilityRequestVersion ||
            requestFingerprint !==
                getAvailabilityFingerprint()
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
            throw new Error(
                "The server returned invalid availability data"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: VALIDATE SLOT STATUSES
        |--------------------------------------------------------------------------
        */

        const validStatuses = [
            "available",
            "booked",
            "past",
            "day_off",
            "closed"
        ];

        const normalizedSlots = data.slots.filter(slot => {
            return (
                slot &&
                bookingTimes.includes(slot.time) &&
                validStatuses.includes(slot.status) &&
                typeof slot.available === "boolean" &&
                (
                    slot.status === "available"
                        ? slot.available === true
                        : slot.available === false
                )
            );
        });

        const uniqueTimes = new Set(
            normalizedSlots.map(slot => slot.time)
        );

        if (
            normalizedSlots.length !== bookingTimes.length ||
            uniqueTimes.size !== bookingTimes.length
        ) {
            throw new Error(
                "The server returned incomplete availability data"
            );
        }

        availabilitySlots = normalizedSlots;
        availabilityFingerprint = requestFingerprint;
        availabilityLoading = false;
        availabilityVerified = true;

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: RESTORE PREVIOUS TIME IF STILL AVAILABLE
        |--------------------------------------------------------------------------
        */

        if (previousTime) {
            if (isSlotAvailable(previousTime)) {
                selectedTime = previousTime;

                bookingElement("timeInput").value =
                    previousTime;
            } else {
                clearSelectedTime();
            }
        }

        renderTimeSlots();

        return true;
    } catch (error) {
        if (
            requestVersion !== availabilityRequestVersion ||
            requestFingerprint !==
                getAvailabilityFingerprint()
        ) {
            return false;
        }

        availabilityLoading = false;
        availabilityVerified = false;
        availabilitySlots = [];
        availabilityFingerprint = "";

        clearSelectedTime();
        renderTimeSlots();

        const note =
            bookingElement("availabilityNote");

        if (note) {
            note.className = "availability-note error";

            note.textContent =
                error.name === "AbortError"
                    ? "Availability check cancelled"
                    : error.message;
        }

        return false;
    } finally {
        if (
            requestVersion === availabilityRequestVersion
        ) {
            availabilityAbortController = null;
        }
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CONFIRMATION SUMMARY
|--------------------------------------------------------------------------
*/

function updateSummary() {
    bookingElement("summaryType").textContent =
        selectedBookingType === "followup"
            ? "Backjob / Follow-up"
            : "Regular Booking";

    bookingElement("summaryServices").textContent =
        selectedServices.length
            ? selectedServices
                .map(service => service.name)
                .join(", ")
            : "None";

    bookingElement("summaryHistory").textContent =
        selectedBookingType === "followup" &&
        selectedFollowUpId
            ? "BK-" +
                String(selectedFollowUpId).padStart(4, "0")
            : "Not applicable";

    bookingElement("summaryStaff").textContent =
        selectedStaff || "Not selected";

    bookingElement("summaryDate").textContent =
        displayDate(selectedDate);

    bookingElement("summaryTime").textContent =
        selectedTime
            ? displayTime(selectedTime) + " MYT"
            : "Not selected";

    bookingElement("summaryDuration").textContent =
        getTotalDuration() + " minutes";

    bookingElement("summaryTotal").textContent =
        "RM " + totalPrice.toFixed(2);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: FINAL FRONTEND VALIDATION
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
        showAlert(
            "Select one finished appointment for the follow-up"
        );

        return false;
    }

    const staffId =
        bookingElement("staffInput").value;

    if (
        !staffId ||
        !isStaffEligible(staffId)
    ) {
        showAlert(
            "Please select an eligible beautician"
        );

        return false;
    }

    if (
        !selectedDate ||
        !isDateWithinBookingWindow(selectedDate)
    ) {
        showAlert("Select a valid appointment date");
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: BLOCK SUNDAY CONFIRMATION
    |--------------------------------------------------------------------------
    */

    if (isSundayClosed(selectedDate)) {
        showAlert(
            "The studio is closed every Sunday — choose Monday to Saturday"
        );

        return false;
    }

    if (
        !selectedTime ||
        !isSlotAvailable(selectedTime)
    ) {
        showAlert(
            "Select an available future appointment time"
        );

        return false;
    }

    if (isStaffOnDayOff(staffId, selectedDate)) {
        showAlert(
            "The selected beautician is on day off"
        );

        return false;
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: FINAL FORM SUBMISSION
|--------------------------------------------------------------------------
| Check live availability immediately before submitting
| Laravel independently validates the booking again
|--------------------------------------------------------------------------
*/

async function handleBookingSubmit(event) {
    event.preventDefault();

    if (bookingSubmissionStarted) {
        return;
    }

    hideAlert();

    if (!validateBookingSelections()) {
        return;
    }

    const form = bookingElement("bookingForm");

    if (!form.reportValidity()) {
        return;
    }

    const chosenTime = selectedTime;

    const button =
        bookingElement("confirmBookingBtn");

    bookingSubmissionStarted = true;

    button.disabled = true;
    button.textContent = "Checking Availability";

    const verified =
        await refreshAvailability(true);

    if (
        !verified ||
        !chosenTime ||
        !isSlotAvailable(chosenTime) ||
        selectedTime !== chosenTime
    ) {
        bookingSubmissionStarted = false;

        button.disabled = false;
        button.textContent = "Confirm Booking";

        showAlert(
            "The selected time is no longer available — please choose another time"
        );

        showStep(3);

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: SUBMIT NORMAL LARAVEL FORM
    |--------------------------------------------------------------------------
    */

    button.textContent = "Submitting Booking";

    form.submit();
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: INITIALIZE BOOKING PAGE
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
    const form = bookingElement("bookingForm");

    if (form) {
        form.addEventListener(
            "submit",
            handleBookingSubmit
        );
    }

    const regularButton = document.querySelector(
        '.type-card[data-booking-type="regular"]'
    );

    if (regularButton) {
        setBookingType(
            "regular",
            regularButton
        );
    }

    generateNext7Days();
    renderTimeSlots();

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: PERIODIC AVAILABILITY REFRESH
    |--------------------------------------------------------------------------
    */

    setInterval(function () {
        if (
            currentStep !== 3 ||
            !selectedDate
        ) {
            return;
        }

        generateNext7Days();

        if (
            selectedTime &&
            isPastOrStarted(selectedDate, selectedTime)
        ) {
            invalidateAvailability();
        }

        if (!availabilityLoading) {
            refreshAvailability(true);
        }
    }, 30000);
});

</script>

@endsection
