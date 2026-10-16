
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SHARED NOTIFICATIONS PAGE
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Frontend improvements:
| - Notification cards use 24px rounded corners
| - Buttons use 14px rounded corners
| - Removed the "Open" button
| - Unread notifications show "Mark as Read"
| - Read notifications show a simple "Read" label
|
| Existing functionality preserved:
| - Admin, Staff and Customer notification routes
| - Mark All as Read
| - Individual Mark as Read
| - Read/unread notification states
| - Notification titles, messages and timestamps
| - Pagination
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification Design Variables
|--------------------------------------------------------------------------
*/

.ns-notifications {
    --ns-brown: #6f4e37;
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

.ns-notifications *,
.ns-notifications *::before,
.ns-notifications *::after {
    box-sizing: border-box;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Page Header
|--------------------------------------------------------------------------
*/

.ns-notifications .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 28px;
}

.ns-notifications .page-header h1 {
    margin: 0 0 8px;
    font-size: 34px;
    line-height: 1.2;
    color: var(--ns-dark);
}

.ns-notifications .page-header p {
    margin: 0;
    color: var(--ns-muted);
    font-size: 14px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Success Alert
|--------------------------------------------------------------------------
*/

.ns-notifications .alert {
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: var(--ns-radius-small);

    font-size: 13px;
    font-weight: 700;
    line-height: 1.6;

    background: rgba(81,148,91,.10);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.22);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Mark All as Read Button
|--------------------------------------------------------------------------
| Existing POST form and PATCH method are unchanged.
|--------------------------------------------------------------------------
*/

.ns-notifications .read-all-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 44px;
    padding: 12px 20px;

    border: none;
    border-radius: var(--ns-radius-small);

    background: var(--ns-gradient);
    color: #fff;

    font: inherit;
    font-size: 12px;
    font-weight: 800;

    cursor: pointer;
    transition: opacity .2s ease;
}

.ns-notifications .read-all-btn:hover {
    opacity: .9;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification List
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-list {
    display: grid;
    gap: 16px;
    min-width: 0;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification Cards
|--------------------------------------------------------------------------
| Main card radius: 24px
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-card {
    min-width: 0;
    padding: 22px;

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
| NS BEAUTY COMMENT: Unread Notification Appearance
|--------------------------------------------------------------------------
| Unread notifications retain the stronger border.
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-card.unread {
    border-color: rgba(138,113,88,.38);
    background: rgba(255,255,255,.64);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification Header
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 9px;
}

.ns-notifications .notification-top h3 {
    margin: 0;
    color: var(--ns-dark);
    font-size: 15px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.ns-notifications .notification-time {
    flex-shrink: 0;
    color: var(--ns-muted);
    font-size: 11px;
    font-weight: 700;
    line-height: 1.5;
    white-space: nowrap;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification Message
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-message {
    margin: 0 0 14px;

    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification Actions
|--------------------------------------------------------------------------
| The "Open" button has been removed.
| Unread notifications can still be marked as read.
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.ns-notifications .read-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 42px;
    padding: 10px 17px;

    border-radius: var(--ns-radius-small);
    border: 1px solid var(--ns-border);

    background: rgba(255,255,255,.70);
    color: var(--ns-dark);

    font: inherit;
    font-size: 12px;
    font-weight: 800;

    cursor: pointer;
}

.ns-notifications .read-btn:hover {
    background: rgba(255,255,255,.95);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Already Read Indicator
|--------------------------------------------------------------------------
*/

.ns-notifications .read-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 38px;
    padding: 9px 15px;

    border-radius: var(--ns-radius-small);
    background: rgba(138,113,88,.10);
    color: var(--ns-muted);

    font-size: 12px;
    font-weight: 700;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Empty Notification State
|--------------------------------------------------------------------------
*/

.ns-notifications .empty-state {
    padding: 55px 24px;
    border-radius: var(--ns-radius-large);

    background: var(--glass,var(--ns-glass));
    border: 1px solid var(--border,rgba(255,255,255,.72));

    color: var(--ns-muted);
    text-align: center;
}

.ns-notifications .empty-state h3 {
    margin: 0 0 9px;
    color: var(--ns-dark);
    font-size: 18px;
}

.ns-notifications .empty-state p {
    margin: 0;
    font-size: 13px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Pagination
|--------------------------------------------------------------------------
*/

.ns-notifications .pagination-wrap {
    margin-top: 22px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Accessibility
|--------------------------------------------------------------------------
*/

.ns-notifications button:focus-visible {
    outline: 3px solid rgba(111,78,55,.35);
    outline-offset: 3px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Responsive Design
|--------------------------------------------------------------------------
*/

@media (max-width:900px) {
    .ns-notifications .page-header,
    .ns-notifications .notification-top {
        flex-direction: column;
    }

    .ns-notifications .notification-time {
        white-space: normal;
    }
}

@media (max-width:550px) {
    .ns-notifications .page-header h1 {
        font-size: 28px;
    }

    .ns-notifications .notification-card {
        padding: 18px 16px;
    }

    .ns-notifications .page-header form {
        width: 100%;
    }

    .ns-notifications .read-all-btn {
        width: 100%;
    }
}

@media (prefers-reduced-motion:reduce) {
    .ns-notifications *,
    .ns-notifications *::before,
    .ns-notifications *::after {
        transition: none !important;
    }
}

</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Role-Based Notification Routes
    |--------------------------------------------------------------------------
    | Existing route logic preserved.
    |--------------------------------------------------------------------------
    */

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

<div class="ns-notifications">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notifications Page Header
    |--------------------------------------------------------------------------
    --}}

    <div class="page-header">
        <div>
            <h1>Notifications</h1>

            <p>
                View your latest system updates,
                booking alerts, and account messages.
            </p>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Mark All as Read
        |--------------------------------------------------------------------------
        | Existing form action and HTTP method preserved.
        |--------------------------------------------------------------------------
        --}}

        <form method="POST" action="{{ $readAllRoute }}">
            @csrf
            @method('PATCH')

            <button type="submit" class="read-all-btn">
                Mark All as Read
            </button>
        </form>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Success Message
    |--------------------------------------------------------------------------
    --}}

    @if (session('success'))
        <div class="alert" role="status">
            {{ session('success') }}
        </div>
    @endif

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notifications List
    |--------------------------------------------------------------------------
    --}}

    <div class="notification-list">

        @forelse ($notifications as $notification)

            <div
                class="notification-card {{ !$notification->read_at ? 'unread' : '' }}"
            >

                <div class="notification-top">
                    <h3>{{ $notification->title }}</h3>

                    <span class="notification-time">
                        {{ $notification->created_at->format(
                            'M d, Y h:i A'
                        ) }}
                    </span>
                </div>

                <p class="notification-message">
                    {{ $notification->message }}
                </p>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Notification Read Status
                |--------------------------------------------------------------------------
                | Removed the Open button.
                |
                | Unread notifications:
                | Show Mark as Read using the existing PATCH route.
                |
                | Already-read notifications:
                | Display Read without creating a new action.
                |--------------------------------------------------------------------------
                --}}

                <div class="notification-actions">

                    @if (!$notification->read_at)

                        <form
                            method="POST"
                            action="{{ route(
                                $readRouteName,
                                $notification->id
                            ) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="read-btn"
                            >
                                Mark as Read
                            </button>
                        </form>

                    @else

                        <span class="read-label">
                            Read
                        </span>

                    @endif

                </div>

            </div>

        @empty

            <div class="empty-state">
                <h3>No notifications yet</h3>

                <p>
                    Your important updates will appear here.
                </p>
            </div>

        @endforelse

    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Existing Pagination
    |--------------------------------------------------------------------------
    --}}

    <div class="pagination-wrap">
        {{ $notifications->links() }}
    </div>

</div>

@endsection
