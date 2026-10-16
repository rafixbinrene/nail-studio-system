
@extends('layouts.app')

{{
--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CUSTOMER BOOKING HISTORY
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Frontend improvements:
| - Removed six statistics cards above the filters
| - Standardized outer card radius to 24px
| - Standardized inner card radius to 14px
| - Standardized button radius to 14px
| - Preserved Muji brown, cream and glass styling
| - Improved responsive card layout
|
| Preserved functionality:
| - All, Active, Finished, Cancelled, No Show and Backjob filters
| - Booking records and status badges
| - View Details
| - Cancel Booking
| - Give Feedback
| - Submitted feedback display
| - Book Follow-up
| - Follow-up booking information
| - Existing Laravel routes and data
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking History Design Variables
|--------------------------------------------------------------------------
| These values match the rounded customer booking pages.
|--------------------------------------------------------------------------
*/

.ns-history {
    --ns-brown: #6f4e37;
    --ns-brown-light: #8a7158;
    --ns-dark: #2f2118;
    --ns-muted: #7d6d60;
    --ns-border: rgba(111,78,55,.14);
    --ns-glass: rgba(255,255,255,.56);
    --ns-gradient: linear-gradient(135deg,#8a7158,#5d3f2c);
    --ns-radius-large: 24px;
    --ns-radius-small: 14px;

    width: 100%;
    min-width: 0;
    color: var(--ns-dark);
}

.ns-history *,
.ns-history *::before,
.ns-history *::after {
    box-sizing: border-box;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Page Header
|--------------------------------------------------------------------------
*/

.ns-history .page-header {
    margin-bottom: 28px;
}

.ns-history .page-header h1 {
    margin: 0 0 8px;
    font-size: 34px;
    line-height: 1.2;
    color: var(--ns-dark);
}

.ns-history .page-header p {
    margin: 0;
    color: var(--ns-muted);
    font-size: 14px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Success and Error Messages
|--------------------------------------------------------------------------
*/

.ns-history .alert {
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: var(--ns-radius-small);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.6;
}

.ns-history .alert-success {
    background: rgba(81,148,91,.10);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.22);
}

.ns-history .alert-error {
    background: rgba(184,77,77,.10);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking History Filters
|--------------------------------------------------------------------------
| Functional JavaScript filtering is unchanged.
| Buttons now use the same 14px radius as customer booking.
|--------------------------------------------------------------------------
*/

.ns-history .history-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 24px;
}

.ns-history .tab-btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;

    min-height: 44px;
    padding: 11px 19px;

    border-radius: var(--ns-radius-small);
    border: 1px solid var(--ns-border);

    background: rgba(255,255,255,.55);
    color: var(--ns-dark);

    font: inherit;
    font-size: 12px;
    font-weight: 800;

    cursor: pointer;
    transition:
        background .2s ease,
        border-color .2s ease,
        box-shadow .2s ease;
}

.ns-history .tab-btn:hover {
    background: rgba(255,255,255,.84);
    border-color: rgba(111,78,55,.28);
}

.ns-history .tab-btn.active {
    background: var(--ns-gradient);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 6px 18px rgba(111,78,55,.14);
}

.ns-history .tab-btn:focus-visible {
    outline: 3px solid rgba(111,78,55,.35);
    outline-offset: 3px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Main Booking Cards
|--------------------------------------------------------------------------
| Outer card radius: 24px
|--------------------------------------------------------------------------
*/

.ns-history .history-grid {
    display: grid;
    gap: 20px;
    min-width: 0;
}

.ns-history .history-card {
    min-width: 0;
    padding: 26px;

    background: var(--glass,var(--ns-glass));
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);

    border: 1px solid var(--border,rgba(255,255,255,.72));
    border-radius: var(--ns-radius-large);

    box-shadow:
        0 20px 55px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.55);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking Card Header
|--------------------------------------------------------------------------
*/

.ns-history .history-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 20px;
}

.ns-history .history-top > div:first-child {
    min-width: 0;
}

.ns-history .history-top h3 {
    margin: 0 0 8px;
    font-size: 18px;
    line-height: 1.4;
    color: var(--ns-dark);
    overflow-wrap: anywhere;
}

.ns-history .booking-id,
.ns-history .booking-type {
    margin: 5px 0 0;
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.5;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking Type and Status Badges
|--------------------------------------------------------------------------
| Keep the original status color meanings.
|--------------------------------------------------------------------------
*/

.ns-history .badge-row {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 8px;
}

.ns-history .status,
.ns-history .type-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 8px 13px;

    border-radius: 999px;

    font-size: 11px;
    font-weight: 800;
    line-height: 1.3;
    white-space: nowrap;
}

.ns-history .type-badge.regular {
    background: rgba(255,255,255,.65);
    color: var(--ns-dark);
    border: 1px solid var(--ns-border);
}

.ns-history .type-badge.followup {
    background: rgba(138,113,88,.18);
    color: var(--ns-brown);
}

.ns-history .status.pending {
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.ns-history .status.approved {
    background: rgba(82,139,255,.15);
    color: #2f5fb8;
}

.ns-history .status.ongoing {
    background: rgba(138,113,88,.18);
    color: var(--ns-brown);
}

.ns-history .status.finished {
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
}

.ns-history .status.cancelled,
.ns-history .status.no-show {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Emergency Rescheduling Badge
|--------------------------------------------------------------------------
| Styling only; original status and filtering logic are preserved.
|--------------------------------------------------------------------------
*/

.ns-history .status.rescheduling-required {
    background: rgba(197,126,46,.17);
    color: #93611f;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking Information Grid
|--------------------------------------------------------------------------
| Date, Time, Staff and Total
|--------------------------------------------------------------------------
*/

.ns-history .history-details {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 14px;
    margin-bottom: 16px;
}

.ns-history .detail-box {
    min-width: 0;
    padding: 18px;

    background: rgba(255,255,255,.48);
    border: 1px solid rgba(255,255,255,.72);
    border-radius: var(--ns-radius-small);
}

.ns-history .detail-box small {
    display: block;
    color: var(--ns-muted);
    font-size: 10px;
    font-weight: 800;
    line-height: 1.5;
}

.ns-history .detail-box p {
    margin: 7px 0 0;
    color: var(--ns-dark);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Shared Inner Information Cards
|--------------------------------------------------------------------------
| All boxes use 14px radius.
|--------------------------------------------------------------------------
*/

.ns-history .service-list,
.ns-history .feedback-box,
.ns-history .followup-box {
    min-width: 0;
    margin-bottom: 16px;
    padding: 18px;

    background: rgba(255,255,255,.44);
    border: 1px solid rgba(255,255,255,.70);
    border-radius: var(--ns-radius-small);
}

.ns-history .feedback-box {
    background: rgba(81,148,91,.08);
    border-color: rgba(81,148,91,.20);
}

.ns-history .followup-box {
    background: rgba(138,113,88,.09);
    border-color: rgba(138,113,88,.20);
}

.ns-history .service-list small,
.ns-history .feedback-box small,
.ns-history .followup-box small {
    display: block;
    margin-bottom: 10px;

    color: var(--ns-muted);
    font-size: 10px;
    font-weight: 800;
    line-height: 1.5;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Selected Service Labels
|--------------------------------------------------------------------------
*/

.ns-history .service-list span {
    display: inline-flex;
    align-items: center;

    margin: 4px 6px 4px 0;
    padding: 8px 12px;

    border-radius: var(--ns-radius-small);
    background: rgba(255,255,255,.72);
    border: 1px solid var(--ns-border);

    color: var(--ns-dark);
    font-size: 12px;
    font-weight: 700;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Submitted Feedback and Follow-up Text
|--------------------------------------------------------------------------
*/

.ns-history .feedback-box p,
.ns-history .followup-box p {
    margin: 0 0 8px;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.ns-history .feedback-box p:last-child,
.ns-history .followup-box p:last-child {
    margin-bottom: 0;
}

.ns-history .feedback-box strong,
.ns-history .followup-box strong {
    color: var(--ns-dark);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Booking Action Buttons
|--------------------------------------------------------------------------
| Visual styles only. Links and routes are unchanged.
|--------------------------------------------------------------------------
*/

.ns-history .actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 4px;
}

.ns-history .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 43px;
    padding: 11px 18px;

    border-radius: var(--ns-radius-small);

    text-decoration: none;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.4;

    transition:
        background .2s ease,
        border-color .2s ease;
}

.ns-history .view-btn {
    background: rgba(255,255,255,.72);
    color: var(--ns-dark);
    border: 1px solid var(--ns-border);
}

.ns-history a.view-btn:hover {
    background: rgba(255,255,255,.94);
    border-color: rgba(111,78,55,.28);
}

.ns-history .feedback-btn {
    background: var(--ns-gradient);
    color: #fff;
    border: 1px solid transparent;
}

.ns-history .cancel-btn {
    background: rgba(184,77,77,.10);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.ns-history .cancel-btn:hover {
    background: rgba(184,77,77,.17);
}

.ns-history .action-btn:focus-visible,
.ns-history .empty-state a:focus-visible {
    outline: 3px solid rgba(111,78,55,.35);
    outline-offset: 3px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Empty Booking History
|--------------------------------------------------------------------------
*/

.ns-history .empty-state {
    padding: 55px 24px;
    background: var(--glass,var(--ns-glass));
    border: 1px solid var(--border,rgba(255,255,255,.72));
    border-radius: var(--ns-radius-large);
    color: var(--ns-muted);
    text-align: center;
}

.ns-history .empty-state h3 {
    margin: 0 0 10px;
    color: var(--ns-dark);
    font-size: 18px;
}

.ns-history .empty-state p {
    margin: 0;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
}

.ns-history .empty-state a {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 44px;
    margin-top: 20px;
    padding: 12px 20px;

    border-radius: var(--ns-radius-small);

    background: var(--ns-gradient);
    color: #fff;

    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Responsive Layouts
|--------------------------------------------------------------------------
| Preserve readable cards on tablets and phones.
|--------------------------------------------------------------------------
*/

@media (max-width:1100px) {
    .ns-history .history-details {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }
}

@media (max-width:700px) {
    .ns-history .history-top {
        flex-direction: column;
    }

    .ns-history .badge-row {
        justify-content: flex-start;
    }

    .ns-history .history-card {
        padding: 20px 16px;
    }
}

@media (max-width:550px) {
    .ns-history .page-header h1 {
        font-size: 28px;
    }

    .ns-history .history-details {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .ns-history .history-tabs {
        gap: 8px;
    }

    .ns-history .tab-btn {
        min-height: 42px;
        padding: 10px 14px;
        font-size: 11px;
    }

    .ns-history .actions {
        flex-direction: column;
    }

    .ns-history .action-btn {
        width: 100%;
    }

    .ns-history .history-card {
        padding: 18px 14px;
    }

    .ns-history .detail-box,
    .ns-history .service-list,
    .ns-history .feedback-box,
    .ns-history .followup-box {
        padding: 15px;
    }
}

@media (prefers-reduced-motion:reduce) {
    .ns-history *,
    .ns-history *::before,
    .ns-history *::after {
        transition: none !important;
    }
}
</style>
@endsection

@section('content')

<div class="ns-history">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Page Header
    |--------------------------------------------------------------------------
    --}}

    <div class="page-header">
        <h1>Booking History</h1>

        <p>
            View your active bookings, finished appointments,
            cancelled bookings, no-show records, and backjob sessions.
        </p>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Flash Messages
    |--------------------------------------------------------------------------
    --}}

    @if (session('success'))
        <div class="alert alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking Statistics Removed
    |--------------------------------------------------------------------------
    | The Total, Active, Finished, Cancelled, No Show
    | and Backjob statistic cards have been removed.
    |
    | No controller calculations or database queries were modified.
    |--------------------------------------------------------------------------
    --}}

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking History Filters
    |--------------------------------------------------------------------------
    | IMPORTANT:
    | Filter values and showHistory() function calls are unchanged.
    |--------------------------------------------------------------------------
    --}}

    <div class="history-tabs">

        <button
            type="button"
            class="tab-btn active"
            onclick="showHistory('all', this)"
        >
            All
        </button>

        <button
            type="button"
            class="tab-btn"
            onclick="showHistory('active', this)"
        >
            Active
        </button>

        <button
            type="button"
            class="tab-btn"
            onclick="showHistory('finished', this)"
        >
            Finished
        </button>

        <button
            type="button"
            class="tab-btn"
            onclick="showHistory('cancelled', this)"
        >
            Cancelled
        </button>

        <button
            type="button"
            class="tab-btn"
            onclick="showHistory('no-show', this)"
        >
            No Show
        </button>

        <button
            type="button"
            class="tab-btn"
            onclick="showHistory('followup', this)"
        >
            Backjob
        </button>

    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking History Records
    |--------------------------------------------------------------------------
    | Existing booking data and filtering classes are preserved.
    |--------------------------------------------------------------------------
    --}}

    <div class="history-grid">

        @forelse ($appointments as $appointment)

            @php
                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Booking Display Preparation
                |--------------------------------------------------------------------------
                | Existing status categorization is preserved.
                |--------------------------------------------------------------------------
                */

                $normalizedStatus = strtolower(
                    str_replace(
                        ' ',
                        '-',
                        $appointment->status
                    )
                );

                $historyType = match (true) {
                    in_array(
                        $normalizedStatus,
                        ['pending', 'approved', 'ongoing'],
                        true
                    ) => 'active',

                    $normalizedStatus === 'finished'
                        => 'finished',

                    $normalizedStatus === 'cancelled'
                        => 'cancelled',

                    $normalizedStatus === 'no-show'
                        => 'no-show',

                    default => 'other',
                };

                $bookingType =
                    $appointment->booking_type === 'followup'
                        ? 'followup'
                        : 'regular';

                $bookingTypeLabel =
                    $bookingType === 'followup'
                        ? 'Backjob / Follow-up'
                        : 'Regular Booking';

                $statusLabel = ucwords(
                    str_replace(
                        '-',
                        ' ',
                        $normalizedStatus
                    )
                );

                $mainService =
                    $appointment->services
                        ->first()?->service_name
                    ?? 'Beauty Service';

                $appointmentDate =
                    $appointment->appointment_date
                        ? \Carbon\Carbon::parse(
                            $appointment->appointment_date
                        )->format('F d, Y')
                        : 'Not set';

                $startTime =
                    $appointment->start_time
                        ? \Carbon\Carbon::parse(
                            $appointment->start_time
                        )->format('h:i A')
                        : 'Not set';

                $endTime =
                    $appointment->end_time
                        ? \Carbon\Carbon::parse(
                            $appointment->end_time
                        )->format('h:i A')
                        : null;

                $original =
                    $appointment->followUpAppointment;

                $originalServices = $original
                    ? $original->services
                        ->pluck('service_name')
                        ->join(', ')
                    : null;
            @endphp

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Individual Booking Card
            |--------------------------------------------------------------------------
            --}}

            <div
                class="history-card history-item {{ $historyType }} {{ $bookingType }}"
            >

                <div class="history-top">

                    <div>
                        <h3>{{ $mainService }}</h3>

                        <p class="booking-id">
                            Booking ID:
                            #BK-{{ str_pad(
                                $appointment->id,
                                4,
                                '0',
                                STR_PAD_LEFT
                            ) }}
                        </p>

                        <p class="booking-type">
                            Transaction Type:
                            {{ $bookingTypeLabel }}
                        </p>
                    </div>

                    {{--
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Booking Type and Status
                    |--------------------------------------------------------------------------
                    --}}

                    <div class="badge-row">

                        <span
                            class="type-badge {{ $bookingType }}"
                        >
                            {{ $bookingTypeLabel }}
                        </span>

                        <span
                            class="status {{ $normalizedStatus }}"
                        >
                            {{ $statusLabel }}
                        </span>

                    </div>

                </div>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Booking Information
                |--------------------------------------------------------------------------
                --}}

                <div class="history-details">

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

                        <p>
                            {{ $appointment->staff?->full_name
                                ?? 'Not assigned' }}
                        </p>
                    </div>

                    <div class="detail-box">
                        <small>TOTAL</small>

                        <p>
                            RM {{ number_format(
                                $appointment->total_price,
                                2
                            ) }}
                        </p>
                    </div>

                </div>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Selected Services
                |--------------------------------------------------------------------------
                --}}

                <div class="service-list">

                    <small>
                        {{ $bookingType === 'followup'
                            ? 'SELECTED BACKJOB SERVICES'
                            : 'SELECTED SERVICES' }}
                    </small>

                    @forelse ($appointment->services as $service)

                        <span>
                            {{ $service->service_name }}
                        </span>

                    @empty

                        <span>No service recorded</span>

                    @endforelse

                </div>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Submitted Feedback
                |--------------------------------------------------------------------------
                | Customer feedback display is preserved.
                |--------------------------------------------------------------------------
                --}}

                @if ($appointment->feedback)

                    <div class="feedback-box">

                        <small>
                            YOUR SUBMITTED FEEDBACK
                        </small>

                        <p>
                            <strong>Rating:</strong>

                            {{ str_repeat(
                                '★',
                                (int) $appointment->feedback->rating
                            ) }}

                            {{ str_repeat(
                                '☆',
                                5 - (int) $appointment->feedback->rating
                            ) }}

                            ({{ $appointment->feedback->rating }}/5)
                        </p>

                        <p>
                            <strong>Comment:</strong>

                            {{ $appointment->feedback->comment
                                ?: 'No comment provided.' }}
                        </p>

                        <p>
                            <strong>Submitted At:</strong>

                            {{ $appointment->feedback->created_at
                                ? \Carbon\Carbon::parse(
                                    $appointment->feedback->created_at
                                )->format('F d, Y h:i A')
                                : 'Not recorded' }}
                        </p>

                    </div>

                @endif

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Backjob / Follow-up Details
                |--------------------------------------------------------------------------
                --}}

                @if ($bookingType === 'followup')

                    <div class="followup-box">

                        <small>
                            BACKJOB / FOLLOW-UP CONNECTION
                        </small>

                        @if ($original)

                            <p>
                                <strong>
                                    Original Booking:
                                </strong>

                                #BK-{{ str_pad(
                                    $original->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                            </p>

                            <p>
                                <strong>
                                    Original Services:
                                </strong>

                                {{ $originalServices
                                    ?: 'No service recorded' }}
                            </p>

                            <p>
                                <strong>
                                    Original Staff:
                                </strong>

                                {{ $original->staff?->full_name
                                    ?? 'Not assigned' }}
                            </p>

                        @else

                            <p>
                                This backjob booking has no linked
                                original booking record.
                            </p>

                        @endif

                    </div>

                @endif

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Booking Action Buttons
                |--------------------------------------------------------------------------
                | All existing routes and status conditions are unchanged.
                |--------------------------------------------------------------------------
                --}}

                <div class="actions">

                    <a
                        href="{{ route(
                            'customer.booking.details',
                            $appointment->id
                        ) }}"
                        class="action-btn view-btn"
                    >
                        View Details
                    </a>

                    @if ($historyType === 'active')

                        <a
                            href="{{ route(
                                'customer.booking.cancel.form',
                                $appointment->id
                            ) }}"
                            class="action-btn cancel-btn"
                        >
                            Cancel Booking
                        </a>

                    @endif

                    @if ($historyType === 'finished')

                        @if ($appointment->feedback)

                            <span class="action-btn view-btn">
                                Feedback Submitted
                            </span>

                        @else

                            <a
                                href="{{ route(
                                    'customer.feedback',
                                    $appointment->id
                                ) }}"
                                class="action-btn feedback-btn"
                            >
                                Give Feedback
                            </a>

                        @endif

                        @if ($bookingType !== 'followup')

                            <a
                                href="{{ route('customer.booking', [
                                    'type' => 'followup',
                                    'booking' =>
                                        'BK-' . str_pad(
                                            $appointment->id,
                                            4,
                                            '0',
                                            STR_PAD_LEFT
                                        ),
                                    'service' => $mainService
                                ]) }}"
                                class="action-btn view-btn"
                            >
                                Book Follow-up
                            </a>

                        @endif

                    @endif

                </div>

            </div>

        @empty

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Empty Booking History
            |--------------------------------------------------------------------------
            --}}

            <div class="empty-state">

                <h3>No booking history yet</h3>

                <p>
                    You have not created any appointment yet.
                </p>

                <a href="{{ route('customer.booking') }}">
                    Book Appointment
                </a>

            </div>

        @endforelse

    </div>

</div>

@endsection

@section('scripts')

<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Booking History Tab Filter
|--------------------------------------------------------------------------
| Existing function preserved without changing its filtering behavior.
|
| Filters:
| - All
| - Active
| - Finished
| - Cancelled
| - No Show
| - Backjob / Follow-up
|--------------------------------------------------------------------------
*/

function showHistory(type, button) {
    const buttons = document.querySelectorAll('.tab-btn');
    const items = document.querySelectorAll('.history-item');

    buttons.forEach(btn => btn.classList.remove('active'));

    button.classList.add('active');

    items.forEach(item => {
        if (type === 'all') {
            item.style.display = 'block';
        } else {
            item.style.display =
                item.classList.contains(type)
                    ? 'block'
                    : 'none';
        }
    });
}
</script>

@endsection
