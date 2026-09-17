@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Profile View
|
| Purpose:
| - Shows dynamic staff profile information.
| - Shows assigned services from Service Management.
| - Shows active/upcoming day-off records.
| - Allows staff to update contact number.
| - Allows staff to change password securely.
|
| Defense explanation:
| The profile page no longer uses static placeholder data. It now displays
| the logged-in staff member's real database records.
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

.profile-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 24px;
}

.profile-card,
.form-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 28px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.avatar-box {
    text-align: center;
}

.avatar-circle {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    margin: 0 auto 18px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    font-weight: 900;
}

.avatar-box h3 {
    margin-bottom: 6px;
}

.avatar-box p {
    color: var(--muted);
    font-size: 14px;
}

.badge {
    display: inline-block;
    margin-top: 12px;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--brown);
    font-size: 12px;
    font-weight: 900;
}

.badge.active {
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.badge.inactive {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.info-list {
    margin-top: 28px;
}

.info-item {
    padding: 16px;
    border-radius: 18px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    margin-bottom: 12px;
}

.info-item small {
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
}

.info-item p {
    margin-top: 6px;
    font-size: 14px;
    font-weight: 700;
    word-break: break-word;
}

.form-card h3 {
    margin-bottom: 18px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 800;
}

input,
textarea {
    width: 100%;
    padding: 14px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    outline: none;
    margin-bottom: 18px;
}

input[readonly] {
    background: rgba(255,255,255,.35);
    color: var(--muted);
    cursor: not-allowed;
}

.help {
    display: block;
    margin-top: -10px;
    margin-bottom: 18px;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.5;
}

.save-btn {
    padding: 14px 24px;
    border: none;
    border-radius: 999px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 900;
    cursor: pointer;
}

.skills {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.skill {
    padding: 9px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--dark);
    font-size: 13px;
    font-weight: 800;
}

.dayoff-skill {
    background: rgba(255,196,87,.16);
    color: #9a6a00;
    border: 1px solid rgba(255,196,87,.25);
}

.password-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid rgba(255,255,255,.65);
}

.empty-note {
    color: var(--muted);
    font-size: 13px;
    font-weight: 700;
}

@media(max-width: 900px) {
    .profile-grid,
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Dynamic initials.
    |--------------------------------------------------------------------------
    | Creates initials based on the staff full name.
    |--------------------------------------------------------------------------
    */
    $staffName = $staffProfile?->full_name ?? auth()->user()->name ?? 'Staff';

    $initials = collect(explode(' ', $staffName))
        ->filter()
        ->take(2)
        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
        ->implode('');

    $statusKey = strtolower($staffProfile?->status ?? 'inactive');
@endphp

<div class="page-header">
    <h1>Staff Profile</h1>
    <p>View assigned services, day-off records, account information, and password security.</p>
</div>

@if (!$staffProfile)
    <div class="alert alert-error">
        Your staff profile was not found. Make sure your staff user email matches a record in the staff table.
    </div>
@endif

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

<div class="profile-grid">
    <section class="profile-card">
        <div class="avatar-box">
            <div class="avatar-circle">
                {{ $initials ?: 'S' }}
            </div>

            <h3>{{ $staffName }}</h3>

            <p>{{ auth()->user()->role === 'staff' ? 'Staff Account' : ucfirst(auth()->user()->role) }}</p>

            <span class="badge {{ $statusKey }}">
                {{ $staffProfile?->status ?? 'No Staff Profile' }}
            </span>
        </div>

        <div class="info-list">
            <div class="info-item">
                <small>EMAIL ADDRESS</small>
                <p>{{ $staffProfile?->email ?? auth()->user()->email }}</p>
            </div>

            <div class="info-item">
                <small>PHONE NUMBER</small>
                <p>{{ $staffProfile?->phone_number ?: 'Not recorded' }}</p>
            </div>

            <div class="info-item">
                <small>ASSIGNED SERVICES</small>
                <p>{{ $staffProfile?->services?->count() ?? 0 }} service(s)</p>
            </div>

            <div class="info-item">
                <small>TOTAL BOOKINGS</small>
                <p>{{ $totalAppointments }} booking(s)</p>
            </div>

            <div class="info-item">
                <small>FINISHED BOOKINGS</small>
                <p>{{ $finishedAppointments }} booking(s)</p>
            </div>

            <div class="info-item">
                <small>PENDING BOOKINGS</small>
                <p>{{ $pendingAppointments }} booking(s)</p>
            </div>
        </div>
    </section>

    <section class="form-card">
        <h3>Assigned Services from Service Management</h3>

        <div class="skills">
            @forelse ($staffProfile?->services ?? [] as $service)
                <span class="skill">{{ $service->service_name }}</span>
            @empty
                <span class="empty-note">No services assigned yet.</span>
            @endforelse
        </div>

        <h3>Active / Upcoming Day Offs</h3>

        <div class="skills">
            @forelse ($staffProfile?->activeDayOffs ?? [] as $dayOff)
                <span class="skill dayoff-skill">
                    {{ $dayOff->start_date->format('M d, Y') }}
                    -
                    {{ $dayOff->end_date->format('M d, Y') }}
                </span>
            @empty
                <span class="empty-note">No active or upcoming day off.</span>
            @endforelse
        </div>

        <div class="password-section" style="border-top:0; padding-top:0;">
            <h3>Update Contact Number</h3>

            <form method="POST" action="{{ route('staff.profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="form-row">
                    <div>
                        <label>Full Name</label>
                        <input type="text" value="{{ $staffProfile?->full_name ?? auth()->user()->name }}" readonly>
                    </div>

                    <div>
                        <label>Email Address</label>
                        <input type="email" value="{{ $staffProfile?->email ?? auth()->user()->email }}" readonly>
                    </div>
                </div>

                <label>Phone Number</label>
                <input
                    type="text"
                    name="phone_number"
                    value="{{ old('phone_number', $staffProfile?->phone_number) }}"
                    maxlength="30"
                    placeholder="Enter contact number"
                    {{ !$staffProfile ? 'disabled' : '' }}
                >

                <small class="help">
                    Name, email, assigned services, and day-off schedules are managed by the admin.
                </small>

                <button type="submit" class="save-btn" {{ !$staffProfile ? 'disabled' : '' }}>
                    Save Contact Number
                </button>
            </form>
        </div>

        <div class="password-section">
            <h3>Change Password</h3>

            <form method="POST" action="{{ route('staff.password.update') }}">
                @csrf
                @method('PATCH')

                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Enter current password" required>

                <label>New Password</label>
                <input type="password" name="password" placeholder="Enter new password" required>

                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" placeholder="Re-enter new password" required>

                <button type="submit" class="save-btn">
                    Update Password
                </button>
            </form>
        </div>
    </section>
</div>

@endsection