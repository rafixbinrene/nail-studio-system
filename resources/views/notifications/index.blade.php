@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Notifications Page
|
| Purpose:
| - Shows all in-app notifications for the logged-in user.
| - Separates unread and read states visually.
| - Allows users to open or mark notifications as read.
|
| Defense explanation:
| This page supports communication between the system and users by showing
| important booking, account, feedback, and security updates.
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
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
    background: rgba(81,148,91,.16);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.read-all-btn {
    padding: 12px 18px;
    border-radius: 999px;
    border: none;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 900;
    cursor: pointer;
}

.notification-list {
    display: grid;
    gap: 16px;
}

.notification-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 26px;
    padding: 20px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.notification-card.unread {
    border-color: rgba(138,113,88,.38);
    background: rgba(255,255,255,.64);
}

.notification-top {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 8px;
}

.notification-top h3 {
    font-size: 16px;
}

.notification-time {
    color: var(--muted);
    font-size: 12px;
    white-space: nowrap;
    font-weight: 700;
}

.notification-message {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 14px;
}

.notification-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.open-btn,
.read-btn {
    display: inline-block;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 900;
}

.open-btn {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.read-btn {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
    cursor: pointer;
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

.pagination-wrap {
    margin-top: 22px;
}

@media(max-width:900px) {
    .page-header,
    .notification-top {
        flex-direction: column;
    }
}
</style>
@endsection

@section('content')

@php
    $role = auth()->user()->role;

    $readAllRoute = match ($role) {
        'admin' => route('admin.notifications.read-all'),
        'staff' => route('staff.notifications.read-all'),
        default => route('customer.notifications.read-all'),
    };

    $readRouteName = match ($role) {
        'admin' => 'admin.notifications.read',
        'staff' => 'staff.notifications.read',
        default => 'customer.notifications.read',
    };
@endphp

<div class="page-header">
    <div>
        <h1>Notifications</h1>
        <p>View your latest system updates, booking alerts, and account messages.</p>
    </div>

    <form method="POST" action="{{ $readAllRoute }}">
        @csrf
        @method('PATCH')

        <button type="submit" class="read-all-btn">
            Mark All as Read
        </button>
    </form>
</div>

@if (session('success'))
    <div class="alert">
        {{ session('success') }}
    </div>
@endif

<div class="notification-list">
    @forelse ($notifications as $notification)
        <div class="notification-card {{ !$notification->read_at ? 'unread' : '' }}">
            <div class="notification-top">
                <h3>{{ $notification->title }}</h3>

                <span class="notification-time">
                    {{ $notification->created_at->format('M d, Y h:i A') }}
                </span>
            </div>

            <p class="notification-message">
                {{ $notification->message }}
            </p>

            <div class="notification-actions">
                @if ($notification->link)
                    <form method="POST" action="{{ route($readRouteName, $notification->id) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="read-btn">
                            Open
                        </button>
                    </form>
                @elseif (!$notification->read_at)
                    <form method="POST" action="{{ route($readRouteName, $notification->id) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="read-btn">
                            Mark as Read
                        </button>
                    </form>
                @else
                    <span class="open-btn" style="opacity:.65;">
                        Read
                    </span>
                @endif
            </div>
        </div>
    @empty
        <div class="empty-state">
            <h3>No notifications yet</h3>
            <p>Your important updates will appear here.</p>
        </div>
    @endforelse
</div>

<div class="pagination-wrap">
    {{ $notifications->links() }}
</div>

@endsection