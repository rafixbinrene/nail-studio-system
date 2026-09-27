@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Customer Booking History View
|
| Purpose:
| - Shows all customer booking records.
| - Filters bookings by all, active, finished, cancelled, no-show, and backjob.
| - Allows customers to view details, cancel active bookings, give feedback,
|   and request follow-up/backjob from finished bookings.
| - Shows submitted feedback after the customer gives feedback.
|
| Defense explanation:
| This page gives customers transparency over their booking history while
| keeping customer actions limited based on booking status. Submitted feedback
| is displayed directly under the finished booking so customers can confirm
| that their rating and comment were successfully recorded.
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

.history-summary {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.summary-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 26px;
    padding: 22px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
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

.history-tabs {
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

.history-grid {
    display: grid;
    gap: 20px;
}

.history-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 24px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.history-top {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.history-top h3 {
    font-size: 18px;
}

.booking-id,
.booking-type {
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

.history-details {
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
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Shared Information Boxes
|--------------------------------------------------------------------------
| Purpose:
| - Uses the same visual structure for selected services, submitted feedback,
|   and backjob/follow-up information.
|--------------------------------------------------------------------------
*/
.service-list,
.feedback-box,
.followup-box {
    margin-bottom: 18px;
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
}

.feedback-box {
    background: rgba(81,148,91,.10);
    border: 1px solid rgba(81,148,91,.22);
}

.followup-box {
    background: rgba(138,113,88,.10);
    border: 1px solid rgba(138,113,88,.22);
}

.service-list small,
.feedback-box small,
.followup-box small {
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

.feedback-box p,
.followup-box p {
    color: #7d6d60;
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 6px;
}

.actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-btn {
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 900;
}

.view-btn {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.feedback-btn {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.cancel-btn {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
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

.empty-state a {
    display: inline-block;
    margin-top: 18px;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 999px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 900;
}

@media(max-width:1200px) {
    .history-summary {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media(max-width: 900px) {
    .history-summary,
    .history-details {
        grid-template-columns: 1fr;
    }

    .history-top {
        flex-direction: column;
    }

    .badge-row {
        justify-content: flex-start;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>Booking History</h1>
    <p>View your active bookings, finished appointments, cancelled bookings, no-show records, and backjob sessions.</p>
</div>

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

<div class="history-summary">
    <div class="summary-card">
        <h2>{{ $appointments->count() }}</h2>
        <p>Total</p>
    </div>

    <div class="summary-card">
        <h2>{{ $activeBookings->count() }}</h2>
        <p>Active</p>
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
        <h2>{{ $noShowBookings->count() }}</h2>
        <p>No Show</p>
    </div>

    <div class="summary-card">
        <h2>{{ $followUpBookings->count() }}</h2>
        <p>Backjob</p>
    </div>
</div>

<div class="history-tabs">
    <button class="tab-btn active" onclick="showHistory('all', this)">All</button>
    <button class="tab-btn" onclick="showHistory('active', this)">Active</button>
    <button class="tab-btn" onclick="showHistory('finished', this)">Finished</button>
    <button class="tab-btn" onclick="showHistory('cancelled', this)">Cancelled</button>
    <button class="tab-btn" onclick="showHistory('no-show', this)">No Show</button>
    <button class="tab-btn" onclick="showHistory('followup', this)">Backjob</button>
</div>

<div class="history-grid">
    @forelse ($appointments as $appointment)
        @php
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Booking display preparation.
            |--------------------------------------------------------------------------
            | Purpose:
            | - Normalizes status for CSS.
            | - Groups booking into tab categories.
            | - Detects regular booking versus backjob/follow-up.
            |--------------------------------------------------------------------------
            */
            $normalizedStatus = strtolower(str_replace(' ', '-', $appointment->status));

            $historyType = match (true) {
                in_array($normalizedStatus, ['pending', 'approved', 'ongoing'], true) => 'active',
                $normalizedStatus === 'finished' => 'finished',
                $normalizedStatus === 'cancelled' => 'cancelled',
                $normalizedStatus === 'no-show' => 'no-show',
                default => 'other',
            };

            $bookingType = $appointment->booking_type === 'followup' ? 'followup' : 'regular';
            $bookingTypeLabel = $bookingType === 'followup' ? 'Backjob / Follow-up' : 'Regular Booking';

            $statusLabel = ucwords(str_replace('-', ' ', $normalizedStatus));

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

            $original = $appointment->followUpAppointment;

            $originalServices = $original
                ? $original->services->pluck('service_name')->join(', ')
                : null;
        @endphp

        <div class="history-card history-item {{ $historyType }} {{ $bookingType }}">
            <div class="history-top">
                <div>
                    <h3>{{ $mainService }}</h3>

                    <p class="booking-id">
                        Booking ID: #BK-{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}
                    </p>

                    <p class="booking-type">
                        Transaction Type: {{ $bookingTypeLabel }}
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
                    <p>{{ $appointment->staff?->full_name ?? 'Not assigned' }}</p>
                </div>

                <div class="detail-box">
                    <small>TOTAL</small>
                    <p>RM {{ number_format($appointment->total_price, 2) }}</p>
                </div>
            </div>

            <div class="service-list">
                <small>
                    {{ $bookingType === 'followup' ? 'SELECTED BACKJOB SERVICES' : 'SELECTED SERVICES' }}
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
            | NS BEAUTY COMMENT:
            | Submitted Feedback Display
            |--------------------------------------------------------------------------
            | Purpose:
            | - Shows the customer's submitted feedback directly in Booking History.
            | - Prevents confusion after feedback submission.
            |--------------------------------------------------------------------------
            --}}
            @if ($appointment->feedback)
                <div class="feedback-box">
                    <small>YOUR SUBMITTED FEEDBACK</small>

                    <p>
                        <strong>Rating:</strong>
                        {{ str_repeat('★', (int) $appointment->feedback->rating) }}
                        {{ str_repeat('☆', 5 - (int) $appointment->feedback->rating) }}
                        ({{ $appointment->feedback->rating }}/5)
                    </p>

                    <p>
                        <strong>Comment:</strong>
                        {{ $appointment->feedback->comment ?: 'No comment provided.' }}
                    </p>

                    <p>
                        <strong>Submitted At:</strong>
                        {{ $appointment->feedback->created_at ? \Carbon\Carbon::parse($appointment->feedback->created_at)->format('F d, Y h:i A') : 'Not recorded' }}
                    </p>
                </div>
            @endif

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
                    @else
                        <p>This backjob booking has no linked original booking record.</p>
                    @endif
                </div>
            @endif

            <div class="actions">
                <a href="{{ route('customer.booking.details', $appointment->id) }}" class="action-btn view-btn">
                    View Details
                </a>

                @if ($historyType === 'active')
                    <a href="{{ route('customer.booking.cancel.form', $appointment->id) }}" class="action-btn cancel-btn">
                        Cancel Booking
                    </a>
                @endif

                @if ($historyType === 'finished')
                    @if ($appointment->feedback)
                        <span class="action-btn view-btn">
                            Feedback Submitted
                        </span>
                    @else
                        <a href="{{ route('customer.feedback', $appointment->id) }}" class="action-btn feedback-btn">
                            Give Feedback
                        </a>
                    @endif

                    @if ($bookingType !== 'followup')
                        <a
                            href="{{ route('customer.booking', [
                                'type' => 'followup',
                                'booking' => 'BK-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT),
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
        <div class="empty-state">
            <h3>No booking history yet</h3>
            <p>You have not created any appointment yet.</p>
            <a href="{{ route('customer.booking') }}">Book Appointment</a>
        </div>
    @endforelse
</div>

@endsection

@section('scripts')
<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Customer History Tab Filter
|--------------------------------------------------------------------------
| Purpose:
| - Filters booking history cards without reloading the page.
| - Supports all, active, finished, cancelled, no-show, and backjob tabs.
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
            item.style.display = item.classList.contains(type) ? 'block' : 'none';
        }
    });
}
</script>
@endsection