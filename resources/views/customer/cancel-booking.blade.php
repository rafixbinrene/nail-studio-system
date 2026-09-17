@extends('layouts.app')

@section('styles')
<style>
.page-header{
    margin-bottom:28px;
}

.page-header h1{
    font-size:34px;
    margin-bottom:8px;
}

.page-header p{
    color:var(--muted);
    font-size:14px;
}

.cancel-card{
    max-width:950px;
    background:var(--glass);
    backdrop-filter:blur(28px);
    border:1px solid var(--border);
    border-radius:30px;
    padding:28px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.booking-summary{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:24px;
}

.summary-box{
    background:rgba(255,255,255,.45);
    border:1px solid rgba(255,255,255,.65);
    border-radius:20px;
    padding:16px;
}

.summary-box small{
    color:var(--muted);
    font-size:11px;
    font-weight:800;
}

.summary-box p{
    margin-top:6px;
    font-weight:700;
    font-size:14px;
}

.status{
    display:inline-block;
    padding:7px 13px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
}

.status.pending{
    background:rgba(255,196,87,.22);
    color:#9a6a00;
}

.status.approved{
    background:rgba(82,139,255,.15);
    color:#2f5fb8;
}

.status.ongoing{
    background:rgba(138,113,88,.18);
    color:#6f4e37;
}

.status.finished{
    background:rgba(81,148,91,.18);
    color:#2f7d3c;
}

.status.cancelled,
.status.no-show{
    background:rgba(184,77,77,.15);
    color:#b84d4d;
}

.warning-box{
    background:rgba(184,77,77,.08);
    border:1px solid rgba(184,77,77,.2);
    border-radius:22px;
    padding:18px;
    margin-bottom:24px;
}

.warning-box h3{
    color:#b84d4d;
    margin-bottom:8px;
}

.warning-box p{
    color:#7d6d60;
    line-height:1.6;
    font-size:14px;
}

.info-box{
    background:rgba(255,255,255,.45);
    border:1px solid rgba(255,255,255,.65);
    border-radius:22px;
    padding:18px;
    margin-bottom:24px;
}

.info-box h3{
    margin-bottom:12px;
}

.service-pill{
    display:inline-block;
    margin:4px 6px 4px 0;
    padding:8px 12px;
    border-radius:999px;
    background:rgba(255,255,255,.6);
    border:1px solid var(--border);
    font-size:13px;
    font-weight:700;
}

.alert{
    max-width:950px;
    margin-bottom:20px;
    padding:14px 18px;
    border-radius:18px;
    font-size:14px;
    font-weight:700;
    line-height:1.6;
}

.alert-error{
    background:rgba(184,77,77,.12);
    border:1px solid rgba(184,77,77,.25);
    color:#b84d4d;
}

.alert-success{
    background:rgba(81,148,91,.16);
    border:1px solid rgba(81,148,91,.25);
    color:#2f7d3c;
}

label{
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:700;
}

select,
textarea{
    width:100%;
    padding:14px;
    border-radius:16px;
    border:1px solid var(--border);
    background:rgba(255,255,255,.65);
    outline:none;
    margin-bottom:18px;
}

textarea{
    height:120px;
    resize:none;
}

.button-row{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.cancel-btn{
    padding:14px 24px;
    border:none;
    border-radius:999px;
    background:#b84d4d;
    color:white;
    font-weight:800;
    cursor:pointer;
}

.cancel-btn:disabled{
    opacity:.55;
    cursor:not-allowed;
}

.back-btn,
.details-btn{
    padding:14px 24px;
    border-radius:999px;
    text-decoration:none;
    background:rgba(255,255,255,.55);
    border:1px solid var(--border);
    color:var(--dark);
    font-weight:800;
}

.success-modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(47,33,24,.35);
    backdrop-filter:blur(10px);
    -webkit-backdrop-filter:blur(10px);
    z-index:9999;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

.success-modal{
    width:100%;
    max-width:480px;
    background:rgba(255,255,255,.9);
    border:1px solid rgba(255,255,255,.9);
    border-radius:34px;
    padding:34px;
    text-align:center;
    box-shadow:
        0 25px 80px rgba(47,33,24,.20),
        inset 0 1px 0 rgba(255,255,255,.8);
}

.success-icon{
    width:76px;
    height:76px;
    border-radius:50%;
    margin:0 auto 18px;
    background:#b84d4d;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:34px;
    font-weight:900;
}

.success-modal h2{
    margin-bottom:10px;
    color:var(--dark);
}

.success-modal p{
    color:var(--muted);
    font-size:14px;
    line-height:1.6;
    margin-bottom:24px;
}

.success-actions{
    display:flex;
    justify-content:center;
    gap:12px;
    flex-wrap:wrap;
}

.dashboard-btn{
    display:inline-block;
    text-decoration:none;
    padding:13px 22px;
    border-radius:999px;
    background:linear-gradient(135deg,#8a7158,#5d3f2c);
    color:white;
    font-weight:800;
}

.history-btn{
    display:inline-block;
    text-decoration:none;
    padding:13px 22px;
    border-radius:999px;
    background:rgba(255,255,255,.65);
    border:1px solid var(--border);
    color:var(--dark);
    font-weight:800;
}

@media(max-width:900px){
    .booking-summary{
        grid-template-columns:1fr;
    }

    .button-row{
        flex-direction:column;
    }

    .cancel-btn,
    .back-btn,
    .details-btn{
        width:100%;
        text-align:center;
    }
}
</style>
@endsection

@section('content')

@php
    $normalizedStatus = strtolower(str_replace(' ', '-', $appointment->status));
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

    $canCancel = !in_array($normalizedStatus, ['finished', 'cancelled', 'no-show'], true);
@endphp

<div class="page-header">
    <h1>Cancel Booking</h1>
    <p>Review your appointment before cancellation.</p>
</div>

@if ($errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<section class="cancel-card">

    <div class="booking-summary">

        <div class="summary-box">
            <small>BOOKING ID</small>
            <p>#BK-{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="summary-box">
            <small>MAIN SERVICE</small>
            <p>{{ $mainService }}</p>
        </div>

        <div class="summary-box">
            <small>DATE</small>
            <p>{{ $appointmentDate }}</p>
        </div>

        <div class="summary-box">
            <small>TIME</small>
            <p>
                {{ $startTime }}
                @if ($endTime)
                    - {{ $endTime }}
                @endif
            </p>
        </div>

        <div class="summary-box">
            <small>STAFF</small>
            <p>{{ $appointment->staff?->full_name ?? 'Not assigned' }}</p>
        </div>

        <div class="summary-box">
            <small>TOTAL</small>
            <p>RM {{ number_format($appointment->total_price, 2) }}</p>
        </div>

        <div class="summary-box">
            <small>TYPE</small>
            <p>{{ $appointment->booking_type === 'followup' ? 'Follow-up Session' : 'Regular Booking' }}</p>
        </div>

        <div class="summary-box">
            <small>STATUS</small>
            <p>
                <span class="status {{ $normalizedStatus }}">
                    {{ $statusLabel }}
                </span>
            </p>
        </div>

    </div>

    <div class="info-box">
        <h3>Selected Services</h3>

        @forelse ($appointment->services as $service)
            <span class="service-pill">
                {{ $service->service_name }}
            </span>
        @empty
            <span class="service-pill">
                No service recorded
            </span>
        @endforelse
    </div>

    @if ($canCancel)
        <div class="warning-box">
            <h3>⚠ Cancellation Notice</h3>

            <p>
                Cancelling this booking will immediately release the reserved slot and update your appointment
                status to Cancelled. This action will be recorded in the system audit logs and will be visible to the
                customer, staff, and admin sides once they view the booking records.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('customer.booking.cancel', $appointment->id) }}"
            onsubmit="return confirm('Are you sure you want to cancel this booking?');"
        >
            @csrf
            @method('PATCH')

            <label>Reason for Cancellation</label>

            <select name="cancellation_reason" required>
                <option value="">Select reason</option>
                <option value="Schedule Conflict" {{ old('cancellation_reason') === 'Schedule Conflict' ? 'selected' : '' }}>
                    Schedule Conflict
                </option>
                <option value="Personal Emergency" {{ old('cancellation_reason') === 'Personal Emergency' ? 'selected' : '' }}>
                    Personal Emergency
                </option>
                <option value="Found Another Schedule" {{ old('cancellation_reason') === 'Found Another Schedule' ? 'selected' : '' }}>
                    Found Another Schedule
                </option>
                <option value="Service No Longer Needed" {{ old('cancellation_reason') === 'Service No Longer Needed' ? 'selected' : '' }}>
                    Service No Longer Needed
                </option>
                <option value="Transportation Issue" {{ old('cancellation_reason') === 'Transportation Issue' ? 'selected' : '' }}>
                    Transportation Issue
                </option>
                <option value="Other" {{ old('cancellation_reason') === 'Other' ? 'selected' : '' }}>
                    Other
                </option>
            </select>

            <label>Additional Details Optional</label>

            <textarea
                name="additional_details"
                placeholder="Provide additional information if necessary..."
            >{{ old('additional_details') }}</textarea>

            <div class="button-row">
                <a href="{{ route('customer.history') }}" class="back-btn">
                    Back to Booking History
                </a>

                <a href="{{ route('customer.booking.details', $appointment->id) }}" class="details-btn">
                    View Details
                </a>

                <button type="submit" class="cancel-btn">
                    Confirm Cancellation
                </button>
            </div>
        </form>
    @else
        <div class="warning-box">
            <h3>This booking can no longer be cancelled</h3>

            <p>
                This appointment is already marked as
                <strong>{{ $statusLabel }}</strong>.
                Finished, cancelled, and no-show bookings cannot be cancelled again.
            </p>
        </div>

        @if ($normalizedStatus === 'cancelled')
            <div class="info-box">
                <h3>Cancellation Record</h3>
                <p style="color:var(--muted); font-size:14px; line-height:1.7;">
                    <strong>Reason:</strong>
                    {{ $appointment->cancellation_reason ?? 'No cancellation reason recorded.' }}
                    <br>

                    <strong>Cancelled At:</strong>
                    {{ $appointment->cancelled_at ? \Carbon\Carbon::parse($appointment->cancelled_at)->format('F d, Y h:i A') : 'Not recorded' }}
                </p>
            </div>
        @endif

        <div class="button-row">
            <a href="{{ route('customer.history') }}" class="back-btn">
                Back to Booking History
            </a>

            <a href="{{ route('customer.booking.details', $appointment->id) }}" class="details-btn">
                View Details
            </a>
        </div>
    @endif

</section>

@if (session('cancel_success'))
    <div class="success-modal-overlay">
        <div class="success-modal">
            <div class="success-icon">!</div>

            <h2>Booking Cancelled</h2>

            <p>
                {{ session('cancel_message', 'Your booking has been cancelled successfully.') }}
            </p>

            <div class="success-actions">
                <a href="{{ route('customer.dashboard') }}" class="dashboard-btn">
                    Go to Dashboard
                </a>

                <a href="{{ route('customer.history') }}" class="history-btn">
                    View Booking History
                </a>
            </div>
        </div>
    </div>
@endif

@endsection