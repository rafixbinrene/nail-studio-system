@extends('layouts.app')

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
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card,
.filter-card,
.booking-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 26px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.stat-card {
    padding: 20px;
}

.stat-card h2 {
    font-size: 28px;
    color: var(--brown);
}

.stat-card p {
    color: var(--muted);
    font-size: 12px;
    margin-top: 6px;
    font-weight: 700;
}

.filter-card {
    padding: 22px;
    margin-bottom: 24px;
}

.filter-row {
    display: grid;
    grid-template-columns: 1.3fr 1fr 1fr 1fr auto auto;
    gap: 14px;
    align-items: end;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 800;
}

input,
select,
textarea {
    width: 100%;
    padding: 13px 14px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    outline: none;
}

textarea {
    resize: none;
    height: 95px;
}

.filter-btn,
.reset-btn {
    padding: 13px 18px;
    border-radius: 999px;
    border: none;
    text-decoration: none;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
}

.filter-btn {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.reset-btn {
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--dark);
}

.booking-list {
    display: grid;
    gap: 20px;
}

.booking-card {
    padding: 24px;
}

.booking-top {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.booking-top h3 {
    font-size: 18px;
    margin-bottom: 5px;
}

.booking-top p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
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

.details-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
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
| Booking Information Boxes
|--------------------------------------------------------------------------
| Purpose:
| - Uses consistent styling for services, feedback, cancellation details,
|   and follow-up/backjob details.
|--------------------------------------------------------------------------
*/
.service-list,
.feedback-info,
.cancel-info,
.followup-info {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
    margin-bottom: 18px;
}

.feedback-info {
    background: rgba(81,148,91,.10);
    border: 1px solid rgba(81,148,91,.22);
}

.followup-info {
    background: rgba(138,113,88,.10);
    border: 1px solid rgba(138,113,88,.22);
}

.cancel-info {
    background: rgba(255,255,255,.38);
}

.service-list small,
.feedback-info small,
.cancel-info small,
.followup-info small {
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

.feedback-info p,
.cancel-info p,
.followup-info p {
    color: #7d6d60;
    font-size: 13px;
    line-height: 1.6;
}

.actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.action-btn {
    border: none;
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
}

.view-btn {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.cancel-btn {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.locked-note {
    color: var(--muted);
    font-size: 13px;
    font-weight: 700;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(47,33,24,.35);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-overlay.show {
    display: flex;
}

.modal {
    width: 100%;
    max-width: 540px;
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(255,255,255,.9);
    border-radius: 30px;
    padding: 28px;
    box-shadow:
        0 25px 80px rgba(47,33,24,.20),
        inset 0 1px 0 rgba(255,255,255,.8);
}

.modal h3 {
    margin-bottom: 10px;
}

.modal p {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 18px;
}

.modal-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.modal-cancel {
    padding: 13px 20px;
    border-radius: 999px;
    border: none;
    background: #b84d4d;
    color: white;
    font-weight: 900;
    cursor: pointer;
}

.modal-back {
    padding: 13px 20px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    color: var(--dark);
    font-weight: 900;
    cursor: pointer;
}

.pagination {
    margin-top: 24px;
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

@media(max-width:1100px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .filter-row,
    .details-grid {
        grid-template-columns: 1fr;
    }

    .badge-row {
        justify-content: flex-start;
    }
}

@media(max-width:700px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .booking-top {
        flex-direction: column;
    }

    .filter-btn,
    .reset-btn,
    .action-btn {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>Booking Management</h1>
    <p>View regular bookings, backjob/follow-up bookings, staff assignments, customer feedback, and cancellation records.</p>
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

<div class="stats-grid">
    <div class="stat-card">
        <h2>{{ $stats['total'] }}</h2>
        <p>Total Bookings</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['regular'] }}</h2>
        <p>Regular Bookings</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['followup'] }}</h2>
        <p>Backjob / Follow-up</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['pending'] }}</h2>
        <p>Pending</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['ongoing'] }}</h2>
        <p>Ongoing</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['finished'] }}</h2>
        <p>Finished</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['cancelled'] }}</h2>
        <p>Cancelled</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['no_show'] }}</h2>
        <p>No Show</p>
    </div>
</div>

<section class="filter-card">
    <form method="GET" action="{{ route('admin.bookings') }}">
        <div class="filter-row">
            <div>
                <label>Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search booking ID, customer, staff, service, feedback..."
                >
            </div>

            <div>
                <label>Booking Type</label>
                <select name="booking_type">
                    <option value="all" {{ $filters['booking_type'] === 'all' ? 'selected' : '' }}>All</option>
                    <option value="regular" {{ $filters['booking_type'] === 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="followup" {{ $filters['booking_type'] === 'followup' ? 'selected' : '' }}>Backjob / Follow-up</option>
                </select>
            </div>

            <div>
                <label>Status</label>
                <select name="status">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>All</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="ongoing" {{ $filters['status'] === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="finished" {{ $filters['status'] === 'finished' ? 'selected' : '' }}>Finished</option>
                    <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no-show" {{ $filters['status'] === 'no-show' ? 'selected' : '' }}>No Show</option>
                </select>
            </div>

            <div>
                <label>Date</label>
                <input type="date" name="date" value="{{ $filters['date'] }}">
            </div>

            <button type="submit" class="filter-btn">
                Filter
            </button>

            <a href="{{ route('admin.bookings') }}" class="reset-btn">
                Reset
            </a>
        </div>
    </form>
</section>

<div class="booking-list">
    @forelse ($bookings as $booking)

        @php
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Booking Display Preparation
            |--------------------------------------------------------------------------
            */
            $statusKey = strtolower(str_replace(' ', '-', $booking->status));
            $statusLabel = ucwords(str_replace('-', ' ', $statusKey));

            $bookingType = $booking->booking_type === 'followup' ? 'followup' : 'regular';
            $bookingTypeLabel = $bookingType === 'followup' ? 'Backjob / Follow-up' : 'Regular Booking';

            $mainService = $booking->services->first()?->service_name ?? 'Beauty Service';

            $appointmentDate = $booking->appointment_date
                ? \Carbon\Carbon::parse($booking->appointment_date)->format('F d, Y')
                : 'Not set';

            $startTime = $booking->start_time
                ? \Carbon\Carbon::parse($booking->start_time)->format('h:i A')
                : 'Not set';

            $endTime = $booking->end_time
                ? \Carbon\Carbon::parse($booking->end_time)->format('h:i A')
                : null;

            $durationMinutes = 0;

            if ($booking->start_time && $booking->end_time) {
                $durationMinutes = \Carbon\Carbon::parse($booking->start_time)
                    ->diffInMinutes(\Carbon\Carbon::parse($booking->end_time));
            }

            if ($durationMinutes <= 0) {
                $durationMinutes = $booking->services->sum(function ($service) {
                    return (int) ($service->pivot->duration ?? $service->duration ?? 0);
                });
            }

            $createdAt = $booking->created_at
                ? \Carbon\Carbon::parse($booking->created_at)->format('F d, Y h:i A')
                : 'Not recorded';

            $updatedAt = $booking->status_updated_at
                ? \Carbon\Carbon::parse($booking->status_updated_at)->format('F d, Y h:i A')
                : 'Not updated';

            $canCancel = !in_array($statusKey, ['finished', 'cancelled', 'no-show'], true);

            $original = $booking->followUpAppointment;

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

        <section class="booking-card">
            <div class="booking-top">
                <div>
                    <h3>{{ $mainService }}</h3>

                    <p>
                        Booking ID:
                        #BK-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}
                    </p>

                    <p>
                        Created:
                        {{ $createdAt }}
                    </p>
                </div>

                <div class="badge-row">
                    <span class="type-badge {{ $bookingType }}">
                        {{ $bookingTypeLabel }}
                    </span>

                    <span class="status {{ $statusKey }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-box">
                    <small>CUSTOMER</small>
                    <p>{{ $booking->customer?->full_name ?? 'Customer record missing' }}</p>
                </div>

                <div class="detail-box">
                    <small>CUSTOMER EMAIL</small>
                    <p>{{ $booking->customer?->email ?? 'Not recorded' }}</p>
                </div>

                <div class="detail-box">
                    <small>STAFF</small>
                    <p>{{ $booking->staff?->full_name ?? 'Not assigned' }}</p>
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
                    <p>RM {{ number_format($booking->total_price, 2) }}</p>
                </div>

                <div class="detail-box">
                    <small>UPDATED BY</small>
                    <p>{{ $booking->statusUpdatedBy?->name ?? 'System / Not updated' }}</p>
                </div>
            </div>

            <div class="service-list">
                <small>SELECTED SERVICES</small>

                @forelse ($booking->services as $service)
                    <span>
                        {{ $service->service_name }}
                        · {{ $service->pivot->duration ?? $service->duration }}m
                        · RM {{ number_format($service->pivot->price ?? $service->price, 2) }}
                    </span>
                @empty
                    <span>No service recorded</span>
                @endforelse
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Admin Feedback Display
            |--------------------------------------------------------------------------
            | Purpose:
            | - Allows admin to see customer feedback directly in Booking Management.
            | - Feedback is connected to the finished appointment record.
            |--------------------------------------------------------------------------
            --}}
            @if ($booking->feedback)
                <div class="feedback-info">
                    <small>CUSTOMER FEEDBACK</small>

                    <p>
                        <strong>Rating:</strong>
                        {{ str_repeat('★', (int) $booking->feedback->rating) }}
                        {{ str_repeat('☆', 5 - (int) $booking->feedback->rating) }}
                        ({{ $booking->feedback->rating }}/5)
                    </p>

                    <p>
                        <strong>Comment:</strong>
                        {{ $booking->feedback->comment ?: 'No comment provided.' }}
                    </p>

                    <p>
                        <strong>Submitted By:</strong>
                        {{ $booking->feedback->customer?->full_name ?? $booking->customer?->full_name ?? 'Unknown customer' }}
                    </p>

                    <p>
                        <strong>Submitted At:</strong>
                        {{ $booking->feedback->created_at ? \Carbon\Carbon::parse($booking->feedback->created_at)->format('F d, Y h:i A') : 'Not recorded' }}
                    </p>
                </div>
            @endif

            @if ($bookingType === 'followup')
                <div class="followup-info">
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
                            <strong>Follow-up Reason:</strong>
                            {{ $booking->follow_up_reason ?? 'No reason provided.' }}
                        </p>
                    @else
                        <p>
                            This follow-up booking has no linked original booking record.
                        </p>
                    @endif
                </div>
            @endif

            @if ($statusKey === 'cancelled')
                <div class="cancel-info">
                    <small>CANCELLATION RECORD</small>

                    <p>
                        <strong>Reason:</strong>
                        {{ $booking->cancellation_reason ?? 'No reason recorded.' }}
                    </p>

                    <p>
                        <strong>Cancelled By:</strong>
                        {{ $booking->cancelledBy?->name ?? 'Unknown user' }}
                    </p>

                    <p>
                        <strong>Cancelled At:</strong>
                        {{ $booking->cancelled_at ? \Carbon\Carbon::parse($booking->cancelled_at)->format('F d, Y h:i A') : 'Not recorded' }}
                    </p>
                </div>
            @endif

            <div class="actions">
                <button
                    type="button"
                    class="action-btn view-btn"
                    onclick="toggleDetails('details{{ $booking->id }}')"
                >
                    View Full Details
                </button>

                @if ($canCancel)
                    <button
                        type="button"
                        class="action-btn cancel-btn"
                        onclick="openCancelModal('{{ $booking->id }}')"
                    >
                        Cancel Booking
                    </button>
                @else
                    <span class="locked-note">
                        This booking is already {{ $statusLabel }} and cannot be cancelled.
                    </span>
                @endif
            </div>

            <div
                id="details{{ $booking->id }}"
                class="cancel-info"
                style="display:none; margin-top:18px;"
            >
                <small>FULL BOOKING DETAILS</small>

                <p>
                    <strong>Customer Phone:</strong>
                    {{ $booking->customer?->phone_number ?? 'Not recorded' }}
                </p>

                <p>
                    <strong>Customer Address:</strong>
                    {{ $booking->customer?->address ?? 'Not recorded' }}
                </p>

                <p>
                    <strong>Booking Type:</strong>
                    {{ $bookingTypeLabel }}
                </p>

                <p>
                    <strong>Follow-up From:</strong>
                    {{ $booking->follow_up_from ?? 'Not applicable' }}
                </p>

                <p>
                    <strong>Follow-up Reason:</strong>
                    {{ $booking->follow_up_reason ?? 'Not applicable' }}
                </p>

                <p>
                    <strong>Feedback Rating:</strong>
                    {{ $booking->feedback ? $booking->feedback->rating . '/5' : 'No feedback submitted' }}
                </p>

                <p>
                    <strong>Feedback Comment:</strong>
                    {{ $booking->feedback?->comment ?: 'No feedback submitted' }}
                </p>

                <p>
                    <strong>Last Updated:</strong>
                    {{ $updatedAt }}
                </p>
            </div>
        </section>

        @if ($canCancel)
            <div class="modal-overlay" id="cancelModal{{ $booking->id }}">
                <div class="modal">
                    <h3>Cancel Booking</h3>

                    <p>
                        You are about to cancel booking
                        <strong>#BK-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</strong>.
                        This action will update the database and will be visible to the customer and staff sides.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('admin.bookings.cancel', $booking->id) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <label>Admin Password</label>
                        <input
                            type="password"
                            name="admin_password"
                            placeholder="Enter your admin password"
                            required
                        >

                        <label>Cancellation Reason</label>
                        <textarea
                            name="cancellation_reason"
                            placeholder="Enter the reason for cancelling this booking"
                            required
                        ></textarea>

                        <div class="modal-actions">
                            <button type="submit" class="modal-cancel">
                                Confirm Cancellation
                            </button>

                            <button
                                type="button"
                                class="modal-back"
                                onclick="closeCancelModal('{{ $booking->id }}')"
                            >
                                Back
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    @empty
        <div class="empty-state">
            <h3>No bookings found</h3>
            <p>There are no bookings matching the current filters.</p>
        </div>
    @endforelse
</div>

<div class="pagination">
    {{ $bookings->links() }}
</div>

@endsection

@section('scripts')
<script>
function openCancelModal(id) {
    document.getElementById('cancelModal' + id).classList.add('show');
}

function closeCancelModal(id) {
    document.getElementById('cancelModal' + id).classList.remove('show');
}

function toggleDetails(id) {
    const box = document.getElementById(id);

    if (box.style.display === 'none' || box.style.display === '') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
}
</script>
@endsection