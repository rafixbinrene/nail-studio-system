@extends('layouts.app')

@section('styles')
<style>
.header{
    text-align:center;
    margin-bottom:32px;
}

.header h1{
    font-size:34px;
    margin-bottom:8px;
}

.header p{
    color:var(--muted);
    font-size:14px;
}

.rule-box{
    max-width:900px;
    margin:0 auto 24px;
    padding:18px 22px;
    border-radius:24px;
    background:rgba(255,255,255,.50);
    border:1px solid var(--border);
    color:var(--muted);
    font-size:13px;
    line-height:1.7;
}

.rule-box strong{
    color:var(--dark);
}

.alert{
    display:none;
    max-width:900px;
    margin:0 auto 20px;
    padding:14px 18px;
    border-radius:18px;
    background:rgba(184,77,77,.12);
    border:1px solid rgba(184,77,77,.25);
    color:var(--danger);
    font-size:14px;
    font-weight:700;
}

.server-alert{
    max-width:900px;
    margin:0 auto 20px;
    padding:14px 18px;
    border-radius:18px;
    background:rgba(184,77,77,.12);
    border:1px solid rgba(184,77,77,.25);
    color:#b84d4d;
    font-size:14px;
    font-weight:700;
    line-height:1.6;
}

.steps{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
    margin:0 auto 34px;
    max-width:850px;
}

.step{
    text-align:center;
}

.circle{
    width:34px;
    height:34px;
    border-radius:50%;
    margin:0 auto 8px;
    background:rgba(255,255,255,.65);
    border:1px solid var(--border);
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:800;
    color:var(--muted);
}

.step.active .circle,
.step.done .circle{
    background:linear-gradient(135deg,#8a7158,#5d3f2c);
    color:white;
}

.step span{
    font-size:12px;
    font-weight:700;
}

.panel{
    max-width:900px;
    margin:auto;
    background:var(--glass);
    backdrop-filter:blur(28px);
    border:1px solid var(--border);
    border-radius:34px;
    padding:32px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.panel h2{
    margin-bottom:8px;
}

.panel > p{
    color:var(--muted);
    font-size:14px;
    margin-bottom:24px;
}

.booking-type{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
    margin-bottom:28px;
}

.type-card{
    padding:20px;
    border-radius:24px;
    background:rgba(255,255,255,.45);
    border:1px solid var(--border);
    cursor:pointer;
}

.type-card.active{
    outline:2px solid var(--brown);
}

.type-card h3{
    margin-bottom:8px;
}

.type-card p{
    font-size:13px;
    color:var(--muted);
}

.services-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
}

.service-card{
    background:rgba(255,255,255,.48);
    border:1px solid var(--border);
    border-radius:26px;
    padding:18px;
    text-align:center;
    cursor:pointer;
    transition:.25s;
}

.service-card:hover{
    transform:translateY(-5px);
}

.service-card.selected{
    outline:2px solid var(--brown);
    background:rgba(255,255,255,.72);
}

.service-card img{
    width:70px;
    height:70px;
    border-radius:50%;
    object-fit:cover;
    margin-bottom:12px;
}

.service-card h4{
    font-size:14px;
    margin-bottom:8px;
}

.service-card p{
    font-size:12px;
    color:var(--muted);
}

.badges{
    display:flex;
    justify-content:center;
    gap:8px;
    margin-top:8px;
}

.badges span{
    background:rgba(255,255,255,.7);
    border-radius:999px;
    padding:5px 9px;
    font-size:11px;
    color:var(--brown);
    font-weight:700;
}

.followup-box{
    display:none;
    margin-bottom:24px;
    padding:20px;
    border-radius:24px;
    background:rgba(255,255,255,.45);
    border:1px solid var(--border);
}

.followup-box.show{
    display:block;
}

.history-grid{
    display:grid;
    gap:16px;
    margin-top:16px;
}

.history-card{
    padding:18px;
    border-radius:22px;
    background:rgba(255,255,255,.48);
    border:1px solid var(--border);
    transition:.25s;
}

.history-card.selected{
    outline:2px solid var(--brown);
    background:rgba(255,255,255,.72);
}

.history-card h4{
    margin-bottom:6px;
}

.history-card p{
    color:var(--muted);
    font-size:13px;
    line-height:1.6;
}

.history-meta{
    margin-top:12px;
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.history-meta span{
    padding:7px 10px;
    border-radius:999px;
    background:rgba(255,255,255,.65);
    border:1px solid var(--border);
    font-size:12px;
    font-weight:800;
    color:var(--brown);
}

.follow-service-list{
    margin-top:16px;
    display:grid;
    gap:10px;
}

.follow-service-option{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px;
    border-radius:18px;
    background:rgba(255,255,255,.50);
    border:1px solid rgba(255,255,255,.65);
    cursor:pointer;
}

.follow-service-option input{
    width:auto;
    margin:0;
    transform:scale(1.15);
}

.follow-service-option strong{
    font-size:14px;
}

.follow-service-option small{
    display:block;
    color:var(--muted);
    margin-top:4px;
    font-size:12px;
}

.follow-service-option:has(input:checked){
    outline:2px solid var(--brown);
    background:rgba(255,255,255,.78);
}

.empty-service{
    grid-column:1 / -1;
    padding:24px;
    border-radius:24px;
    background:rgba(255,255,255,.45);
    border:1px solid var(--border);
    color:var(--muted);
    text-align:center;
    font-weight:700;
}

.beautician-list{
    display:grid;
    gap:14px;
}

.beautician-card{
    padding:20px;
    border-radius:26px;
    background:rgba(255,255,255,.5);
    border:1px solid var(--border);
    display:flex;
    align-items:center;
    gap:18px;
    cursor:pointer;
    max-width:420px;
}

.beautician-card.selected{
    outline:2px solid var(--brown);
}

.avatar{
    width:52px;
    height:52px;
    border-radius:50%;
    background:rgba(255,255,255,.8);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
}

.calendar-time{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:28px;
}

.calendar{
    background:rgba(255,255,255,.45);
    border:1px solid var(--border);
    border-radius:26px;
    padding:22px;
}

.calendar-grid{
    display:grid;
    grid-template-columns:repeat(7,1fr);
    gap:10px;
    margin-top:18px;
    text-align:center;
}

.day{
    padding:12px 6px;
    border-radius:18px;
    cursor:pointer;
    font-size:12px;
    background:rgba(255,255,255,.45);
    border:1px solid rgba(255,255,255,.55);
}

.day:hover,
.day.selected{
    background:var(--brown);
    color:white;
}

.time-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
}

.time-btn{
    padding:13px;
    border-radius:999px;
    background:rgba(255,255,255,.55);
    border:1px solid var(--border);
    cursor:pointer;
    text-align:center;
    font-size:13px;
}

.time-btn.selected{
    background:linear-gradient(135deg,#8a7158,#5d3f2c);
    color:white;
}

.confirm-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px;
}

.summary,
.details{
    background:rgba(255,255,255,.45);
    border:1px solid var(--border);
    border-radius:26px;
    padding:24px;
}

.summary-row{
    display:flex;
    justify-content:space-between;
    gap:16px;
    margin-bottom:12px;
    font-size:14px;
}

.summary-row strong{
    text-align:right;
}

.total{
    border-top:1px solid rgba(47,33,24,.15);
    padding-top:14px;
    margin-top:14px;
    font-weight:800;
}

input, textarea{
    width:100%;
    padding:14px;
    border-radius:16px;
    border:1px solid var(--border);
    background:rgba(255,255,255,.65);
    outline:none;
    margin-bottom:14px;
}

input[readonly]{
    color:var(--muted);
    cursor:not-allowed;
}

textarea{
    resize:none;
    height:90px;
}

.nav-buttons{
    margin-top:32px;
    display:flex;
    justify-content:space-between;
    gap:12px;
}

.btn{
    padding:13px 24px;
    border-radius:999px;
    border:none;
    cursor:pointer;
    font-weight:800;
}

.btn-back{
    background:transparent;
    color:var(--muted);
}

.btn-next{
    background:linear-gradient(135deg,#8a7158,#5d3f2c);
    color:white;
}

.form-step{
    display:none;
}

.form-step.active{
    display:block;
}

.success-modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(47,33,24,.35);
    backdrop-filter:blur(10px);
    -webkit-backdrop-filter:blur(10px);
    z-index:9999;
    display:none;
    align-items:center;
    justify-content:center;
    padding:20px;
}

.success-modal-overlay.show{
    display:flex;
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
    background:linear-gradient(135deg,#8a7158,#5d3f2c);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:36px;
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
    margin-bottom:10px;
}

.success-booking-id{
    display:inline-block;
    margin:10px 0 24px;
    padding:9px 14px;
    border-radius:999px;
    background:rgba(255,255,255,.65);
    border:1px solid var(--border);
    color:var(--brown);
    font-size:13px;
    font-weight:900;
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
    .services-grid,
    .calendar-time,
    .confirm-grid,
    .booking-type{
        grid-template-columns:1fr;
    }

    .steps{
        grid-template-columns:repeat(2,1fr);
    }

    .summary-row{
        flex-direction:column;
    }

    .summary-row strong{
        text-align:left;
    }

    .nav-buttons{
        flex-direction:column;
    }

    .btn{
        width:100%;
    }
}
</style>
@endsection

@section('content')

@php
    $imageMap = [
        'Manicure & Pedicure' => 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=200&q=80',
        'Nail Art Design' => 'https://images.unsplash.com/photo-1610992015732-2449b76344bc?auto=format&fit=crop&w=200&q=80',
        'Nail Extension' => 'https://images.unsplash.com/photo-1632345031435-8727f6897d53?auto=format&fit=crop&w=200&q=80',
        'Eyelash Extension' => 'https://images.unsplash.com/photo-1519014816548-bf5fe059798b?auto=format&fit=crop&w=200&q=80',
        'Lash Lift & Tint' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=200&q=80',
        'Basic Facial' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=200&q=80',
    ];

    $firstStaff = $staffMembers->first();

    $customerName = old('customer_name', $customer?->full_name ?? auth()->user()->name);
    $customerPhone = old('customer_phone', $customer?->phone_number ?? '');
    $customerEmail = old('customer_email', auth()->user()->email);
@endphp

<div class="header">
    <h1>Book Appointment</h1>
    <p>Schedule your next visit in four simple steps.</p>
</div>

<div class="rule-box">
    <strong>Booking Rules:</strong><br>
    Maximum of <strong>3 services</strong> per regular transaction.
    New customers may only have <strong>1 active booking</strong>.
    After the first completed booking, customers may have up to <strong>2 active bookings</strong>.
    Bookings are only allowed within the next <strong>7 days</strong>.
    <br><br>
    <strong>Backjob / Follow-up Rule:</strong>
    Backjob services must be selected from a finished booking history. You may select one service, partial services, or all services from that finished booking. The time duration is calculated only from the selected service/s.
    <br><br>
    Your current active bookings:
    <strong>{{ $customerActiveBookings }}</strong>
</div>

@if ($errors->any())
    <div class="server-alert">
        {{ $errors->first() }}
    </div>
@endif

<div class="alert" id="alertBox"></div>

<div class="steps">
    <div class="step active" id="stepIndicator1">
        <div class="circle">1</div>
        <span>Services</span>
    </div>

    <div class="step" id="stepIndicator2">
        <div class="circle">2</div>
        <span>Beautician</span>
    </div>

    <div class="step" id="stepIndicator3">
        <div class="circle">3</div>
        <span>Date & Time</span>
    </div>

    <div class="step" id="stepIndicator4">
        <div class="circle">4</div>
        <span>Confirm</span>
    </div>
</div>

<form method="POST" action="{{ route('customer.booking.store') }}" onsubmit="return validateSubmit()">
    @csrf

    <input type="hidden" name="booking_type" id="bookingTypeInput" value="regular">
    <input type="hidden" name="selected_service_ids" id="selectedServiceIdsInput" value="{{ old('selected_service_ids') }}">
    <input type="hidden" name="selected_services" id="selectedServicesInput" value="{{ old('selected_services') }}">
    <input type="hidden" name="follow_up_appointment_id" id="followUpAppointmentInput" value="{{ old('follow_up_appointment_id') }}">
    <input type="hidden" name="staff_id" id="staffInput" value="{{ old('staff_id', $firstStaff?->id) }}">
    <input type="hidden" name="appointment_date" id="dateInput" value="{{ old('appointment_date') }}">
    <input type="hidden" name="start_time" id="timeInput" value="{{ old('start_time') }}">

    <section class="panel form-step active" id="step1">
        <h2>Choose Booking Type</h2>
        <p>Select regular booking or backjob/follow-up from your finished service history.</p>

        <div class="booking-type">
            <div class="type-card active" onclick="setBookingType('regular', this)">
                <h3>Regular Booking</h3>
                <p>For a new appointment or beauty service session.</p>
            </div>

            <div class="type-card" onclick="setBookingType('followup', this)">
                <h3>Backjob / Follow-up</h3>
                <p>Select individual, partial, or all services from a finished booking.</p>
            </div>
        </div>

        <div id="regularServicesBox">
            <h2>Choose Services</h2>
            <p>Select up to 3 treatments for your regular session.</p>

            <div class="services-grid">
                @forelse ($services as $service)
                    @php
                        $serviceImage = $imageMap[$service->service_name]
                            ?? 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=200&q=80';
                    @endphp

                    <div
                        class="service-card"
                        onclick="selectRegularService(this, {{ $service->id }}, @js($service->service_name), {{ (float) $service->price }}, {{ (int) $service->duration }})"
                    >
                        <img src="{{ $serviceImage }}" alt="{{ $service->service_name }}">

                        <h4>{{ $service->service_name }}</h4>

                        <p>Regular Service</p>

                        <div class="badges">
                            <span>{{ $service->duration }}m</span>
                            <span>RM {{ number_format($service->price, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-service">
                        No available services found. Please add services from the admin service management module.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="followup-box" id="followupBox">
            <h3>Select Backjob Services From Finished History</h3>

            <p style="color:#7d6d60; font-size:13px; margin:8px 0 14px;">
                Choose one finished booking, then tick the exact service/s that need backjob.
                The total time will be based only on the selected service/s.
            </p>

            <div class="history-grid">
                @forelse ($eligibleFollowUps as $history)
                    @php
                        $historyServiceNames = $history->services->pluck('service_name')->join(', ');

                        $historyDate = $history->appointment_date
                            ? \Carbon\Carbon::parse($history->appointment_date)->format('F d, Y')
                            : 'No date';

                        $historyStart = $history->start_time
                            ? \Carbon\Carbon::parse($history->start_time)->format('h:i A')
                            : 'No time';

                        $historyStaffId = $history->staff_id;
                        $historyStaffName = $history->staff?->full_name ?? 'Not assigned';

                        $historyTotalDuration = $history->services->sum(function ($service) {
                            return (int) ($service->pivot->duration ?? $service->duration ?? 0);
                        });
                    @endphp

                    <div class="history-card" id="historyCard{{ $history->id }}">
                        <h4>
                            Previous Booking #BK-{{ str_pad($history->id, 4, '0', STR_PAD_LEFT) }}
                        </h4>

                        <p>
                            <strong>Finished Date:</strong>
                            {{ $historyDate }}
                        </p>

                        <p>
                            <strong>Original Time:</strong>
                            {{ $historyStart }}
                        </p>

                        <p>
                            <strong>Original Staff:</strong>
                            {{ $historyStaffName }}
                        </p>

                        <p>
                            <strong>Original Services:</strong>
                            {{ $historyServiceNames ?: 'No service recorded' }}
                        </p>

                        <div class="history-meta">
                            <span>Total original duration: {{ $historyTotalDuration }} minutes</span>
                            <span>Backjob price: RM 0.00</span>
                        </div>

                        <div class="follow-service-list">
                            @forelse ($history->services as $service)
                                @php
                                    $serviceDuration = (int) ($service->pivot->duration ?? $service->duration ?? 0);
                                @endphp

                                <label class="follow-service-option">
                                    <input
                                        type="checkbox"
                                        class="follow-service-checkbox history{{ $history->id }}"
                                        onchange="selectFollowUpService(
                                            this,
                                            {{ $history->id }},
                                            {{ (int) $historyStaffId }},
                                            @js($historyStaffName),
                                            {{ $service->id }},
                                            @js($service->service_name),
                                            {{ $serviceDuration }}
                                        )"
                                    >

                                    <span>
                                        <strong>{{ $service->service_name }}</strong>
                                        <small>
                                            {{ $serviceDuration }} minutes · Backjob price RM 0.00
                                        </small>
                                    </span>
                                </label>
                            @empty
                                <div class="empty-service">
                                    No services recorded for this finished booking.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="empty-service">
                        You do not have any finished booking history yet. Complete a regular booking first before creating a backjob/follow-up.
                    </div>
                @endforelse
            </div>

            <br>

            <label style="display:block; margin-bottom:8px; font-size:13px; font-weight:800;">
                Backjob / Follow-up Reason
            </label>

            <textarea
                name="follow_up_reason"
                id="followUpReason"
                placeholder="Example: Manicure polish repair, eyelash adjustment, pedicure retouch..."
            >{{ old('follow_up_reason') }}</textarea>
        </div>

        <div class="nav-buttons">
            <button type="button" class="btn btn-back" disabled>...</button>
            <button type="button" class="btn btn-next" onclick="nextStep()">Continue →</button>
        </div>
    </section>

    <section class="panel form-step" id="step2">
        <h2>Choose Beautician</h2>
        <p>For backjob, the original staff is selected automatically when available. You may choose another active staff if needed.</p>

        <div class="beautician-list">
            @forelse ($staffMembers as $staff)
                <div
                    class="beautician-card {{ $loop->first ? 'selected' : '' }}"
                    data-staff-id="{{ $staff->id }}"
                    onclick="selectBeautician(this, {{ $staff->id }}, @js($staff->full_name))"
                >
                    <div class="avatar">👤</div>

                    <div>
                        <h3>{{ $staff->full_name }}</h3>
                        <p>Beautician / Staff</p>
                        <small>Status: {{ $staff->status }}</small>
                    </div>
                </div>
            @empty
                <div class="empty-service">
                    No active beautician found. Please add an active staff member first.
                </div>
            @endforelse
        </div>

        <div class="nav-buttons">
            <button type="button" class="btn btn-back" onclick="prevStep()">← Back</button>
            <button type="button" class="btn btn-next" onclick="nextStep()">Continue →</button>
        </div>
    </section>

    <section class="panel form-step" id="step3">
        <h2>Date & Time</h2>
        <p>Select when you would like to visit. Only the next 7 days are available.</p>

        <div class="calendar-time">
            <div class="calendar">
                <h3>Available Dates</h3>
                <div class="calendar-grid" id="dateGrid"></div>
            </div>

            <div>
                <h3 style="margin-bottom:18px;">Select Time</h3>

                <div class="time-grid">
                    <div class="time-btn" onclick="selectTime(this, '10:00')">10:00 AM</div>
                    <div class="time-btn" onclick="selectTime(this, '10:30')">10:30 AM</div>
                    <div class="time-btn" onclick="selectTime(this, '11:00')">11:00 AM</div>
                    <div class="time-btn" onclick="selectTime(this, '11:30')">11:30 AM</div>
                    <div class="time-btn" onclick="selectTime(this, '12:00')">12:00 PM</div>
                    <div class="time-btn" onclick="selectTime(this, '13:00')">1:00 PM</div>
                    <div class="time-btn" onclick="selectTime(this, '14:00')">2:00 PM</div>
                    <div class="time-btn" onclick="selectTime(this, '15:00')">3:00 PM</div>
                    <div class="time-btn" onclick="selectTime(this, '16:00')">4:00 PM</div>
                </div>
            </div>
        </div>

        <div class="nav-buttons">
            <button type="button" class="btn btn-back" onclick="prevStep()">← Back</button>
            <button type="button" class="btn btn-next" onclick="nextStep()">Continue →</button>
        </div>
    </section>

    <section class="panel form-step" id="step4">
        <h2>Confirm Booking</h2>
        <p>Please review your details before submitting.</p>

        <div class="confirm-grid">
            <div class="summary">
                <h3 style="margin-bottom:16px;">Booking Summary</h3>

                <div class="summary-row">
                    <span>Booking Type</span>
                    <strong id="summaryType">Regular</strong>
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
                    <strong id="summaryStaff">{{ $firstStaff?->full_name ?? 'Not selected' }}</strong>
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

            <div class="details">
                <h3 style="margin-bottom:16px;">Your Details</h3>

                <input
                    type="text"
                    name="customer_name"
                    value="{{ $customerName }}"
                    placeholder="Enter your full name"
                    required
                >

                <input
                    type="text"
                    name="customer_phone"
                    value="{{ $customerPhone }}"
                    placeholder="Enter your phone number"
                    required
                >

                <input
                    type="email"
                    name="customer_email"
                    value="{{ $customerEmail }}"
                    placeholder="Enter your email address"
                    readonly
                    required
                >
            </div>
        </div>

        <div class="nav-buttons">
            <button type="button" class="btn btn-back" onclick="prevStep()">← Back</button>
            <button type="submit" class="btn btn-next">Confirm Booking</button>
        </div>
    </section>
</form>

@if (session('booking_success'))
    <div class="success-modal-overlay show" id="successModal">
        <div class="success-modal">
            <div class="success-icon">✓</div>

            <h2>Booking Confirmed</h2>

            <p>
                {{ session('booking_message', 'Your booking has been confirmed and recorded successfully.') }}
            </p>

            @if (session('booking_id'))
                <div class="success-booking-id">
                    Booking ID: #{{ session('booking_id') }}
                </div>
            @endif

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

@section('scripts')
<script>
let currentStep = 1;
let selectedServices = [];
let totalPrice = 0;
let selectedStaff = @js($firstStaff?->full_name ?? '');
let selectedDate = "";
let selectedTime = "";
let selectedBookingType = "regular";
let selectedFollowUpId = "";
let selectedFollowUpDuration = 0;

let customerCompletedBookings = {{ (int) $customerCompletedBookings }};
let customerActiveBookings = {{ (int) $customerActiveBookings }};

function showAlert(message){
    const alertBox = document.getElementById("alertBox");
    alertBox.textContent = message;
    alertBox.style.display = "block";

    setTimeout(() => {
        alertBox.style.display = "none";
    }, 3500);
}

function getBookingLimit(){
    return customerCompletedBookings >= 1 ? 2 : 1;
}

function checkActiveBookingLimit(){
    const allowedLimit = getBookingLimit();

    if(customerActiveBookings >= allowedLimit){
        showAlert("You have reached your active booking limit.");
        return false;
    }

    return true;
}

function showStep(step){
    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    document.getElementById('step' + step).classList.add('active');

    for(let i = 1; i <= 4; i++){
        let indicator = document.getElementById('stepIndicator' + i);
        indicator.classList.remove('active', 'done');

        if(i < step){
            indicator.classList.add('done');
        }else if(i === step){
            indicator.classList.add('active');
        }
    }

    if(step === 4){
        updateSummary();
    }
}

function nextStep(){
    if(!checkActiveBookingLimit()){
        return;
    }

    if(currentStep === 1){
        if(selectedServices.length === 0){
            showAlert("Please select at least 1 service.");
            return;
        }

        if(selectedServices.length > 3){
            showAlert("Only 3 services are allowed per transaction.");
            return;
        }

        if(selectedBookingType === "followup" && selectedFollowUpId === ""){
            showAlert("Please select service/s from one finished booking history.");
            return;
        }
    }

    if(currentStep === 2){
        if(document.getElementById('staffInput').value === ""){
            showAlert("Please select a beautician.");
            return;
        }
    }

    if(currentStep === 3){
        if(selectedDate === ""){
            showAlert("Please select an appointment date.");
            return;
        }

        if(selectedTime === ""){
            showAlert("Please select an appointment time.");
            return;
        }
    }

    if(currentStep < 4){
        currentStep++;
        showStep(currentStep);
    }
}

function prevStep(){
    if(currentStep > 1){
        currentStep--;
        showStep(currentStep);
    }
}

function setBookingType(type, element){
    selectedBookingType = type;

    document.querySelectorAll('.type-card').forEach(card => card.classList.remove('active'));
    element.classList.add('active');

    document.getElementById('bookingTypeInput').value = type;

    const regularBox = document.getElementById('regularServicesBox');
    const followupBox = document.getElementById('followupBox');

    resetServiceSelections();

    if(type === 'followup'){
        regularBox.style.display = 'none';
        followupBox.classList.add('show');
    }else{
        regularBox.style.display = 'block';
        followupBox.classList.remove('show');
    }
}

function resetServiceSelections(){
    selectedServices = [];
    totalPrice = 0;
    selectedFollowUpId = "";
    selectedFollowUpDuration = 0;

    document.getElementById('selectedServiceIdsInput').value = "";
    document.getElementById('selectedServicesInput').value = "";
    document.getElementById('followUpAppointmentInput').value = "";

    document.querySelectorAll('.service-card').forEach(card => card.classList.remove('selected'));
    document.querySelectorAll('.history-card').forEach(card => card.classList.remove('selected'));
    document.querySelectorAll('.follow-service-checkbox').forEach(box => box.checked = false);
}

function selectRegularService(card, id, name, price, duration){
    if(selectedBookingType !== "regular"){
        return;
    }

    let existingIndex = selectedServices.findIndex(service => service.id === id);

    if(existingIndex >= 0){
        card.classList.remove('selected');
        selectedServices.splice(existingIndex, 1);
    }else{
        if(selectedServices.length >= 3){
            showAlert("Maximum of 3 services only per transaction.");
            return;
        }

        card.classList.add('selected');
        selectedServices.push({
            id: id,
            name: name,
            price: parseFloat(price),
            duration: parseInt(duration) || 0
        });
    }

    calculateTotal();
    updateHiddenServiceInputs();
}

function selectFollowUpService(input, appointmentId, staffId, staffName, serviceId, serviceName, duration){
    if(selectedBookingType !== "followup"){
        input.checked = false;
        return;
    }

    appointmentId = parseInt(appointmentId);
    serviceId = parseInt(serviceId);
    duration = parseInt(duration) || 0;

    const isChangingHistory = selectedFollowUpId !== "" && parseInt(selectedFollowUpId) !== appointmentId;

    if(input.checked && isChangingHistory){
        selectedServices = [];
        totalPrice = 0;
        selectedFollowUpDuration = 0;

        document.querySelectorAll('.history-card').forEach(card => card.classList.remove('selected'));
        document.querySelectorAll('.follow-service-checkbox').forEach(box => {
            if(box !== input){
                box.checked = false;
            }
        });
    }

    if(input.checked){
        if(selectedServices.length >= 3){
            input.checked = false;
            showAlert("Maximum of 3 backjob services only per transaction.");
            return;
        }

        selectedFollowUpId = appointmentId;

        const existing = selectedServices.find(service => service.id === serviceId);

        if(!existing){
            selectedServices.push({
                id: serviceId,
                name: serviceName,
                price: 0,
                duration: duration
            });
        }
    }else{
        selectedServices = selectedServices.filter(service => service.id !== serviceId);

        if(selectedServices.length === 0){
            selectedFollowUpId = "";
        }
    }

    updateFollowUpCardSelection();
    updateFollowUpStaffSelection(staffId, staffName);
    calculateTotal();
    calculateFollowUpDuration();
    updateHiddenServiceInputs();

    document.getElementById('followUpAppointmentInput').value = selectedFollowUpId;
}

function updateFollowUpCardSelection(){
    document.querySelectorAll('.history-card').forEach(card => card.classList.remove('selected'));

    if(selectedFollowUpId !== ""){
        const selectedCard = document.getElementById('historyCard' + selectedFollowUpId);

        if(selectedCard){
            selectedCard.classList.add('selected');
        }
    }
}

function updateFollowUpStaffSelection(staffId, staffName){
    let matchedStaff = false;

    document.querySelectorAll('.beautician-card').forEach(card => {
        card.classList.remove('selected');

        if(parseInt(card.getAttribute('data-staff-id')) === parseInt(staffId)){
            matchedStaff = true;
            card.classList.add('selected');
        }
    });

    if(matchedStaff){
        selectedStaff = staffName;
        document.getElementById('staffInput').value = staffId;
    }
}

function calculateTotal(){
    totalPrice = selectedServices.reduce((sum, service) => {
        return sum + parseFloat(service.price);
    }, 0);
}

function calculateFollowUpDuration(){
    selectedFollowUpDuration = selectedServices.reduce((sum, service) => {
        return sum + (parseInt(service.duration) || 0);
    }, 0);
}

function updateHiddenServiceInputs(){
    document.getElementById('selectedServiceIdsInput').value = selectedServices.map(s => s.id).join(',');
    document.getElementById('selectedServicesInput').value = selectedServices.map(s => s.name).join(', ');
}

function selectBeautician(card, id, name){
    document.querySelectorAll('.beautician-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');

    selectedStaff = name;
    document.getElementById('staffInput').value = id;
}

function generateNext7Days(){
    const dateGrid = document.getElementById("dateGrid");
    dateGrid.innerHTML = "";

    for(let i = 0; i < 7; i++){
        let date = new Date();
        date.setDate(date.getDate() + i);

        let year = date.getFullYear();
        let month = String(date.getMonth() + 1).padStart(2, "0");
        let day = String(date.getDate()).padStart(2, "0");
        let dateValue = `${year}-${month}-${day}`;

        let button = document.createElement("div");
        button.className = "day";
        button.innerHTML = date.toLocaleDateString("en-US", {
            weekday: "short",
            month: "short",
            day: "numeric"
        });

        button.onclick = function(){
            selectDate(button, dateValue);
        };

        dateGrid.appendChild(button);
    }
}

function selectDate(day, date){
    document.querySelectorAll('.day').forEach(d => d.classList.remove('selected'));
    day.classList.add('selected');

    selectedDate = date;
    document.getElementById('dateInput').value = date;
}

function selectTime(btn, time){
    document.querySelectorAll('.time-btn').forEach(t => t.classList.remove('selected'));
    btn.classList.add('selected');

    selectedTime = time;
    document.getElementById('timeInput').value = time;
}

function getRegularDuration(){
    return selectedServices.reduce((sum, service) => {
        return sum + (parseInt(service.duration) || 0);
    }, 0);
}

function updateSummary(){
    let type = document.getElementById('bookingTypeInput').value;
    let duration = type === 'followup' ? selectedFollowUpDuration : getRegularDuration();

    document.getElementById('summaryType').textContent = type === 'followup' ? 'Backjob / Follow-up' : 'Regular';
    document.getElementById('summaryServices').textContent = selectedServices.length ? selectedServices.map(s => s.name).join(', ') : 'None';
    document.getElementById('summaryHistory').textContent = type === 'followup' && selectedFollowUpId
        ? '#BK-' + String(selectedFollowUpId).padStart(4, '0')
        : 'Not applicable';
    document.getElementById('summaryStaff').textContent = selectedStaff || 'Not selected';
    document.getElementById('summaryDate').textContent = selectedDate || 'Not selected';
    document.getElementById('summaryTime').textContent = selectedTime || 'Not selected';
    document.getElementById('summaryDuration').textContent = duration + ' minutes';
    document.getElementById('summaryTotal').textContent = 'RM ' + totalPrice.toFixed(2);
}

function validateSubmit(){
    if(!checkActiveBookingLimit()){
        return false;
    }

    if(selectedServices.length === 0){
        showAlert("Please select at least 1 service.");
        return false;
    }

    if(selectedServices.length > 3){
        showAlert("Only 3 services are allowed per transaction.");
        return false;
    }

    if(selectedBookingType === "followup"){
        if(selectedFollowUpId === ""){
            showAlert("Please select service/s from one finished booking history.");
            return false;
        }

        if(selectedFollowUpDuration <= 0){
            showAlert("The selected backjob service duration is invalid.");
            return false;
        }
    }

    if(document.getElementById('staffInput').value === ""){
        showAlert("Please select a beautician.");
        return false;
    }

    if(selectedDate === ""){
        showAlert("Please select an appointment date.");
        return false;
    }

    if(selectedTime === ""){
        showAlert("Please select an appointment time.");
        return false;
    }

    return true;
}

setBookingType('regular', document.querySelector('.type-card'));
generateNext7Days();
</script>
@endsection