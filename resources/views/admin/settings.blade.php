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

.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 28px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.card h3 {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 700;
}

input,
select,
textarea {
    width: 100%;
    padding: 14px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    outline: none;
    margin-bottom: 18px;
}

textarea {
    height: 90px;
    resize: none;
}

.save-btn {
    padding: 14px 24px;
    border: none;
    border-radius: 999px;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 800;
    cursor: pointer;
}

.setting-note {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    color: var(--muted);
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 18px;
}

@media(max-width:900px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>System Settings</h1>
    <p>Manage booking rules, security settings, reminders, and business configuration.</p>
</div>

<div class="settings-grid">

    <section class="card">
        <h3>Booking Rules</h3>

        <form>
            <label>Maximum Services Per Booking</label>
            <input type="number" value="3">

            <label>New Customer Active Booking Limit</label>
            <input type="number" value="1">

            <label>Returning Customer Active Booking Limit</label>
            <input type="number" value="2">

            <label>Advance Booking Limit</label>
            <select>
                <option selected>7 Days</option>
                <option>14 Days</option>
                <option>30 Days</option>
            </select>

            <label>Slot Hold Duration</label>
            <select>
                <option selected>5 Minutes</option>
                <option>10 Minutes</option>
                <option>15 Minutes</option>
            </select>

            <button type="button" class="save-btn">Save Booking Rules</button>
        </form>
    </section>

    <section class="card">
        <h3>Security Settings</h3>

        <form>
            <label>Login Attempt Limit</label>
            <input type="number" value="5">

            <label>Account Lock Duration</label>
            <select>
                <option selected>5 Minutes</option>
                <option>10 Minutes</option>
                <option>30 Minutes</option>
            </select>

            <label>Session Timeout</label>
            <select>
                <option selected>15 Minutes</option>
                <option>30 Minutes</option>
                <option>1 Hour</option>
            </select>

            <label>Booking Request Rate Limit</label>
            <select>
                <option selected>1 request every 10 seconds</option>
                <option>1 request every 30 seconds</option>
                <option>1 request every 1 minute</option>
            </select>

            <button type="button" class="save-btn">Save Security Settings</button>
        </form>
    </section>

    <section class="card">
        <h3>Notification Settings</h3>

        <form>
            <label>Email Confirmation</label>
            <select>
                <option selected>Enabled</option>
                <option>Disabled</option>
            </select>

            <label>Reminder Before Appointment</label>
            <select>
                <option selected>24 Hours Before</option>
                <option>12 Hours Before</option>
                <option>6 Hours Before</option>
            </select>

            <label>Admin Alert for Suspicious Activity</label>
            <select>
                <option selected>Enabled</option>
                <option>Disabled</option>
            </select>

            <button type="button" class="save-btn">Save Notification Settings</button>
        </form>
    </section>

    <section class="card">
        <h3>Business Settings</h3>

        <form>
            <label>Business Name</label>
            <input type="text" value="Nail Studio & Beauty">

            <label>Business Location</label>
            <textarea>Kedai Sedco Block E, Lot 3, Ground Floor, Donggongon, Penampang, Sabah, Malaysia</textarea>

            <label>Default Currency</label>
            <select>
                <option selected>MYR / RM</option>
                <option>PHP / ₱</option>
                <option>USD / $</option>
            </select>

            <label>Operating Status</label>
            <select>
                <option selected>Open</option>
                <option>Temporarily Closed</option>
                <option>Maintenance Mode</option>
            </select>

            <button type="button" class="save-btn">Save Business Settings</button>
        </form>
    </section>

</div>

@endsection