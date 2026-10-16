
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: ADMIN SYSTEM SETTINGS
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Purpose:
| - Display the documented booking policies
| - Display the configured notification policies
| - Display booking schedule information
| - Provide operational and configuration references
|
| Important:
| - Security Settings are excluded from this page.
| - Business Information is managed through Content Management.
| - Emergency closures are managed through Booking Management.
| - This page is intentionally read-only.
| - No database update or configuration changes occur here.
|
| Defense Explanation:
| System Settings provides administrators with an overview of
| business scheduling and notification policies. Security-related
| configurations remain under developer/IT control to prevent
| unauthorized changes to security restrictions.
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Page Header
|--------------------------------------------------------------------------
*/

.settings-page .page-header {
    margin-bottom: 28px;
}

.settings-page .page-header h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.settings-page .page-header p {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Settings Grid
|--------------------------------------------------------------------------
*/

.settings-page .settings-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    align-items: start;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Main Cards
| Border radius: 24px
|--------------------------------------------------------------------------
*/

.settings-page .settings-card {
    min-width: 0;
    padding: 28px;

    background: var(--glass);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);

    border: 1px solid var(--border);
    border-radius: 24px;

    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.settings-page .settings-card h3 {
    margin: 0 0 8px;
    font-size: 19px;
    color: var(--dark);
}

.settings-page .card-description {
    margin: 0 0 20px;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Inner Information Cards
| Border radius: 14px
|--------------------------------------------------------------------------
*/

.settings-page .setting-item {
    min-width: 0;
    padding: 16px;
    margin-bottom: 12px;

    border-radius: 14px;

    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
}

.settings-page .setting-item:last-child {
    margin-bottom: 0;
}

.settings-page .setting-item small {
    display: block;
    margin-bottom: 7px;

    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
}

.settings-page .setting-item p {
    margin: 0;
    color: var(--dark);
    font-size: 14px;
    font-weight: 700;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.settings-page .setting-item .secondary-text {
    margin-top: 5px;
    color: var(--muted);
    font-size: 12px;
    font-weight: 500;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Information Notes
|--------------------------------------------------------------------------
*/

.settings-page .setting-note {
    margin-top: 18px;
    padding: 16px;

    border-radius: 14px;

    background: rgba(138,113,88,.08);
    border: 1px solid rgba(138,113,88,.18);

    color: #6f4e37;
    font-size: 13px;
    line-height: 1.7;
}

.settings-page .setting-note strong {
    color: var(--dark);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Module Navigation
|--------------------------------------------------------------------------
*/

.settings-page .settings-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
}

.settings-page .settings-link {
    display: inline-flex;
    justify-content: center;
    align-items: center;

    min-height: 43px;
    padding: 11px 18px;

    border-radius: 14px;
    border: 1px solid var(--border);

    background: rgba(255,255,255,.65);
    color: var(--dark);

    font-size: 12px;
    font-weight: 800;
    text-decoration: none;

    transition: background .2s ease;
}

.settings-page .settings-link:hover {
    background: rgba(255,255,255,.95);
}

.settings-page .settings-link:focus-visible {
    outline: 3px solid rgba(138,113,88,.30);
    outline-offset: 3px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Responsive Layout
|--------------------------------------------------------------------------
*/

@media (max-width: 900px) {
    .settings-page .settings-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 550px) {
    .settings-page .page-header h1 {
        font-size: 28px;
    }

    .settings-page .settings-card {
        padding: 20px;
    }

    .settings-page .settings-link {
        width: 100%;
    }
}

</style>
@endsection

@section('content')

<div class="settings-page">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Page Header
    |--------------------------------------------------------------------------
    --}}

    <div class="page-header">
        <h1>System Settings</h1>

        <p>
            Review booking policies, notification configuration,
            scheduling rules, and operational information.
        </p>
    </div>

    <div class="settings-grid">

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Policies
        |--------------------------------------------------------------------------
        | Values are informational.
        | Changing policies requires updating the relevant
        | backend validation and booking configuration.
        |--------------------------------------------------------------------------
        --}}

        <section class="settings-card">

            <h3>Booking Policies</h3>

            <p class="card-description">
                Standard booking policies documented for
                Nail Studio & Beauty.
            </p>

            <div class="setting-item">
                <small>MAXIMUM SERVICES PER BOOKING</small>
                <p>3 Services</p>
            </div>

            <div class="setting-item">
                <small>NEW CUSTOMER ACTIVE BOOKING LIMIT</small>
                <p>1 Active Booking</p>
            </div>

            <div class="setting-item">
                <small>RETURNING CUSTOMER ACTIVE BOOKING LIMIT</small>
                <p>2 Active Bookings</p>
            </div>

            <div class="setting-item">
                <small>ADVANCE BOOKING WINDOW</small>
                <p>7 Calendar Days</p>
            </div>

            <div class="setting-item">
                <small>BOOKING SLOT HOLD DURATION</small>
                <p>5 Minutes</p>
            </div>

            <div class="setting-note">
                <strong>Booking Policy Notice</strong>

                These values are provided for reference only.
                Actual booking restrictions are controlled by
                the booking system and cannot be edited here.
            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notification Configuration
        |--------------------------------------------------------------------------
        | This section describes intended notification behavior.
        | It does not report live email delivery status.
        |--------------------------------------------------------------------------
        --}}

        <section class="settings-card">

            <h3>Notification Configuration</h3>

            <p class="card-description">
                Review the notification features configured
                for bookings and customer updates.
            </p>

            <div class="setting-item">
                <small>BOOKING CONFIRMATION</small>
                <p>Email and In-App Notifications</p>
            </div>

            <div class="setting-item">
                <small>APPOINTMENT REMINDERS</small>
                <p>24 Hours and 2 Hours Before</p>
            </div>

            <div class="setting-item">
                <small>BOOKING STATUS UPDATES</small>
                <p>In-App Notification System</p>
            </div>

            <div class="setting-item">
                <small>EMERGENCY CLOSURE NOTIFICATIONS</small>
                <p>Email and In-App Notifications</p>
            </div>

            <div class="setting-note">
                <strong>Notification Notice</strong>

                Reminder delivery depends on the email
                configuration and scheduled command.
                This page does not verify whether notifications
                were successfully delivered.
            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Schedule
        |--------------------------------------------------------------------------
        | Do not confuse public-facing opening hours with
        | booking availability enforced by the backend.
        |--------------------------------------------------------------------------
        --}}

        <section class="settings-card">

            <h3>Booking Schedule</h3>

            <p class="card-description">
                Reference information about appointment
                scheduling and studio availability.
            </p>

            <div class="setting-item">
                <small>BOOKABLE DAYS</small>
                <p>Monday to Saturday</p>
            </div>

            <div class="setting-item">
                <small>REGULAR CLOSED DAY</small>
                <p>Sunday</p>
            </div>

            <div class="setting-item">
                <small>APPOINTMENT START TIMES</small>
                <p>10:00 AM to 4:00 PM</p>

                <p class="secondary-text">
                    Based on the documented booking schedule.
                    Actual availability also depends on service
                    duration and staff availability.
                </p>
            </div>

            <div class="setting-item">
                <small>TIME ZONE</small>
                <p>Asia/Kuala_Lumpur (MYT)</p>
            </div>

            <div class="setting-item">
                <small>EMERGENCY STUDIO CLOSURES</small>
                <p>Managed through Booking Management</p>
            </div>

            <div class="setting-note">
                <strong>Scheduling Notice</strong>

                Public opening hours are managed separately
                through Content Management. The booking
                backend determines which appointment
                slots can actually be selected.
            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: System Status
        |--------------------------------------------------------------------------
        | Avoid displaying misleading "Online" or "Healthy"
        | labels without real backend health checks.
        |--------------------------------------------------------------------------
        --}}

        <section class="settings-card">

            <h3>System Status</h3>

            <p class="card-description">
                General information about system configuration
                and operational responsibilities.
            </p>

            <div class="setting-item">
                <small>WEBSITE CONTENT</small>
                <p>Managed through Content Management</p>
            </div>

            <div class="setting-item">
                <small>APPOINTMENT MANAGEMENT</small>
                <p>Managed through Booking Management</p>
            </div>

            <div class="setting-item">
                <small>EMAIL DELIVERY STATUS</small>
                <p>Not monitored on this page</p>

                <p class="secondary-text">
                    Requires verification of the production
                    email configuration and delivery logs.
                </p>
            </div>

            <div class="setting-item">
                <small>REMINDER SCHEDULER STATUS</small>
                <p>Not monitored on this page</p>

                <p class="secondary-text">
                    Requires a production scheduler check
                    and confirmation of actual reminder delivery.
                </p>
            </div>

            <div class="setting-item">
                <small>SECURITY CONFIGURATION</small>
                <p>Managed by authorized IT personnel</p>
            </div>

            <div class="setting-note">
                <strong>System Status Notice</strong>

                This section provides configuration
                information only. It does not perform
                real-time system health monitoring.
            </div>

        </section>

    </div>

</div>

@endsection
