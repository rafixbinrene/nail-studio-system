
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CUSTOMER PROFILE VIEW
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Frontend-only update:
| - Main profile cards use 24px border radius
| - Information boxes use 14px border radius
| - Form inputs use 14px border radius
| - Action buttons use 14px border radius
| - Alerts use 14px border radius
|
| Preserved functionality:
| - Customer profile information
| - Profile editing
| - Password updates
| - Account status
| - Booking limit and booking statistics
| - Existing Laravel routes and CSRF protection
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Page Header
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Success and Error Alerts
|--------------------------------------------------------------------------
| Updated radius: 14px
|--------------------------------------------------------------------------
*/

.alert {
    padding: 14px 16px;
    border-radius: 14px;
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Profile Grid
|--------------------------------------------------------------------------
| Original layout preserved.
|--------------------------------------------------------------------------
*/

.profile-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 24px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Main Profile Cards
|--------------------------------------------------------------------------
| Updated radius: 24px
|--------------------------------------------------------------------------
*/

.profile-card,
.form-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 28px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Avatar
|--------------------------------------------------------------------------
| Remains circular.
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Account Status Badge
|--------------------------------------------------------------------------
| Original pill-shaped appearance preserved.
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Information List
|--------------------------------------------------------------------------
*/

.info-list {
    margin-top: 28px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Information Boxes
|--------------------------------------------------------------------------
| Updated radius: 14px
|--------------------------------------------------------------------------
*/

.info-item {
    padding: 16px;
    border-radius: 14px;
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Edit Profile Form
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Form Inputs
|--------------------------------------------------------------------------
| Updated radius: 14px
|--------------------------------------------------------------------------
*/

input,
textarea {
    width: 100%;
    padding: 14px;
    border-radius: 14px;
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

textarea {
    height: 100px;
    resize: none;
}

.help {
    display: block;
    margin-top: -10px;
    margin-bottom: 18px;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.5;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Save and Password Buttons
|--------------------------------------------------------------------------
| Updated radius: 14px
|--------------------------------------------------------------------------
*/

.save-btn {
    padding: 14px 24px;
    border: none;
    border-radius: 14px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 900;
    cursor: pointer;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Change Password Section
|--------------------------------------------------------------------------
*/

.password-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid rgba(255,255,255,.65);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Responsive Layout
|--------------------------------------------------------------------------
*/

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
    | NS BEAUTY COMMENT: Customer Profile Data
    |--------------------------------------------------------------------------
    | Original data preparation preserved.
    |--------------------------------------------------------------------------
    */

    $user = auth()->user();

    $customerName = $customer?->full_name ?? $user->name ?? 'Customer';

    $initials = collect(explode(' ', $customerName))
        ->filter()
        ->take(2)
        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
        ->implode('');
@endphp

<div class="page-header">
    <h1>Profile</h1>
    <p>Manage your personal information and account password.</p>
</div>

@if (!$customer)
    <div class="alert alert-error">
        Your customer profile was not found. Please contact the administrator.
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

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Information Card
    |--------------------------------------------------------------------------
    --}}

    <section class="profile-card">
        <div class="avatar-box">
            <div class="avatar-circle">
                {{ $initials ?: 'C' }}
            </div>

            <h3>{{ $customerName }}</h3>
            <p>Customer Account</p>

            <span class="badge">
                {{ ucfirst($customer?->status ?? $user->status ?? 'active') }}
            </span>
        </div>

        <div class="info-list">

            <div class="info-item">
                <small>EMAIL ADDRESS</small>
                <p>{{ $customer?->email ?? $user->email }}</p>
            </div>

            <div class="info-item">
                <small>PHONE NUMBER</small>
                <p>{{ $customer?->phone_number ?: 'Not recorded' }}</p>
            </div>

            <div class="info-item">
                <small>ADDRESS</small>
                <p>{{ $customer?->address ?: 'Not recorded' }}</p>
            </div>

            <div class="info-item">
                <small>BOOKING LIMIT</small>
                <p>{{ $bookingLimit }} active booking(s)</p>
            </div>

            <div class="info-item">
                <small>CURRENT ACTIVE BOOKINGS</small>
                <p>{{ $activeBookings->count() }} active booking(s)</p>
            </div>

            <div class="info-item">
                <small>FINISHED BOOKINGS</small>
                <p>{{ $finishedBookings->count() }} finished booking(s)</p>
            </div>

        </div>
    </section>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Profile Update and Password Forms
    |--------------------------------------------------------------------------
    | All original routes, fields and validation preserved.
    |--------------------------------------------------------------------------
    --}}

    <section class="form-card">
        <h3>Edit Profile</h3>

        <form method="POST" action="{{ route('customer.profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="form-row">
                <div>
                    <label>Full Name</label>
                    <input
                        type="text"
                        name="full_name"
                        value="{{ old('full_name', $customer?->full_name ?? $user->name) }}"
                        maxlength="255"
                        required
                        {{ !$customer ? 'disabled' : '' }}
                    >
                </div>

                <div>
                    <label>Phone Number</label>
                    <input
                        type="text"
                        name="phone_number"
                        value="{{ old('phone_number', $customer?->phone_number) }}"
                        maxlength="30"
                        placeholder="Enter phone number"
                        {{ !$customer ? 'disabled' : '' }}
                    >
                </div>
            </div>

            <label>Email Address</label>
            <input
                type="email"
                value="{{ $customer?->email ?? $user->email }}"
                readonly
            >

            <label>Address</label>
            <textarea
                name="address"
                maxlength="500"
                placeholder="Enter address"
                {{ !$customer ? 'disabled' : '' }}
            >{{ old('address', $customer?->address) }}</textarea>

            <small class="help">
                Email address is protected because it is used for login and account matching.
            </small>

            <button
                type="submit"
                class="save-btn"
                {{ !$customer ? 'disabled' : '' }}
            >
                Save Changes
            </button>
        </form>

        <div class="password-section">
            <h3>Change Password</h3>

            <form method="POST" action="{{ route('customer.password.update') }}">
                @csrf
                @method('PATCH')

                <label>Current Password</label>
                <input
                    type="password"
                    name="current_password"
                    placeholder="Enter current password"
                    required
                >

                <label>New Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter new password"
                    required
                >

                <label>Confirm New Password</label>
                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Re-enter new password"
                    required
                >

                <button type="submit" class="save-btn">
                    Update Password
                </button>
            </form>
        </div>
    </section>

</div>

@endsection
