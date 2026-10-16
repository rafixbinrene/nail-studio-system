
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SHARED NOTIFICATIONS MODULE
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Applies to:
| - Administrator
| - Staff
| - Customer
|
| Features preserved:
| - Mark as Read without leaving the Notifications page
| - Mark All as Read without redirecting
| - AJAX requests using existing Laravel PATCH routes
| - CSRF protection
| - Read and unread notification states
| - Notification titles, messages and timestamps
| - Database persistence through existing controllers
| - Pagination
|
| Frontend improvements:
| - Fixed oversized Laravel pagination arrow
| - Main notification cards: 24px radius
| - Buttons and alerts: 14px radius
| - Responsive pagination and mobile layout
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification Page Header
|--------------------------------------------------------------------------
*/

.ns-notifications {
    width: 100%;
    min-width: 0;
}

.ns-notifications .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 28px;
}

.ns-notifications .page-header h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.ns-notifications .page-header p {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Alerts
|--------------------------------------------------------------------------
*/

.ns-notifications .alert {
    padding: 14px 18px;
    border-radius: 14px;
    margin-bottom: 20px;
    font-size: 13px;
    font-weight: 700;
}

.ns-notifications .alert-success {
    background: rgba(81,148,91,.12);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.24);
}

.ns-notifications .alert-error {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.24);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Mark All as Read Button
|--------------------------------------------------------------------------
*/

.ns-notifications .read-all-btn {
    padding: 12px 20px;
    min-height: 44px;
    border-radius: 14px;
    border: none;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-size: 12px;
    font-weight: 900;
    cursor: pointer;
}

.ns-notifications .read-all-btn:disabled {
    opacity: .65;
    cursor: wait;
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
| Main card radius: 24px.
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-card {
    min-width: 0;
    background: var(--glass);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 22px;

    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.ns-notifications .notification-card.unread {
    border-color: rgba(138,113,88,.38);
    background: rgba(255,255,255,.64);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification Content
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 8px;
}

.ns-notifications .notification-top h3 {
    font-size: 16px;
    overflow-wrap: anywhere;
}

.ns-notifications .notification-time {
    color: var(--muted);
    font-size: 12px;
    white-space: nowrap;
    font-weight: 700;
}

.ns-notifications .notification-message {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 14px;
    overflow-wrap: anywhere;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Notification Actions
|--------------------------------------------------------------------------
*/

.ns-notifications .notification-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ns-notifications .read-btn,
.ns-notifications .read-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    min-height: 40px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 900;
}

.ns-notifications .read-btn {
    background: rgba(255,255,255,.65);
    color: var(--dark);
    border: 1px solid var(--border);
    cursor: pointer;
}

.ns-notifications .read-btn:hover {
    background: rgba(255,255,255,.9);
}

.ns-notifications .read-btn:disabled {
    opacity: .65;
    cursor: wait;
}

.ns-notifications .read-label {
    background: rgba(138,113,88,.10);
    color: var(--muted);
    border: 1px solid transparent;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Empty Notification State
|--------------------------------------------------------------------------
*/

.ns-notifications .empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--glass);
    border: 1px solid var(--border);
    border-radius: 24px;
    color: var(--muted);
}

.ns-notifications .empty-state h3 {
    color: var(--dark);
    margin-bottom: 8px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: FIX OVERSIZED PAGINATION ICONS
|--------------------------------------------------------------------------
|
| Problem:
| Laravel's default pagination uses SVG arrow icons.
| Without appropriate SVG sizing, a global stylesheet or
| missing Tailwind utility classes can make the arrows huge.
|
| Solution:
| Limit the pagination arrow dimensions to 20px.
|
| Scope:
| Only the shared Notifications page.
| Does not affect any other website icons or charts.
|--------------------------------------------------------------------------
*/

.ns-notifications .pagination-wrap {
    margin-top: 22px;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Pagination SVG Size
|--------------------------------------------------------------------------
| Prevent previous and next arrows from stretching.
|--------------------------------------------------------------------------
*/

.ns-notifications .pagination-wrap svg {
    display: inline-block !important;

    width: 20px !important;
    height: 20px !important;

    min-width: 20px !important;
    min-height: 20px !important;

    max-width: 20px !important;
    max-height: 20px !important;

    flex: 0 0 20px !important;

    vertical-align: middle;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Pagination Navigation Container
|--------------------------------------------------------------------------
*/

.ns-notifications .pagination-wrap nav {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    font-size: 13px;
    color: var(--muted);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Pagination Alignment
|--------------------------------------------------------------------------
*/

.ns-notifications .pagination-wrap a,
.ns-notifications .pagination-wrap span {
    box-sizing: border-box;
}

.ns-notifications .pagination-wrap a svg,
.ns-notifications .pagination-wrap span svg {
    width: 20px !important;
    height: 20px !important;
    max-width: 20px !important;
    max-height: 20px !important;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Pagination Button Appearance
|--------------------------------------------------------------------------
| Preserve Laravel's pagination links and current-page state.
|--------------------------------------------------------------------------
*/

.ns-notifications .pagination-wrap a {
    border-radius: 14px;
    text-decoration: none;
    transition: background .2s ease;
}

.ns-notifications .pagination-wrap a:hover {
    background-color: rgba(138,113,88,.08);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Pagination Focus State
|--------------------------------------------------------------------------
*/

.ns-notifications .pagination-wrap a:focus-visible {
    outline: 2px solid rgba(138,113,88,.4);
    outline-offset: 2px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Responsive Layout
|--------------------------------------------------------------------------
*/

@media(max-width:900px) {
    .ns-notifications .page-header,
    .ns-notifications .notification-top {
        flex-direction: column;
    }

    .ns-notifications .notification-time {
        white-space: normal;
    }
}

@media(max-width:550px) {
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

    .ns-notifications .pagination-wrap {
        margin-top: 18px;
    }
}

</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Shared Role-Based Notification Routes
    |--------------------------------------------------------------------------
    | Original Admin, Staff and Customer routes preserved.
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

<div class="ns-notifications" id="nsNotificationsPage">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Page Header
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
        | Keep existing Laravel PATCH route.
        | JavaScript prevents browser navigation.
        |--------------------------------------------------------------------------
        --}}

        <form
            method="POST"
            action="{{ $readAllRoute }}"
            id="nsMarkAllReadForm"
        >
            @csrf
            @method('PATCH')

            <button
                type="submit"
                class="read-all-btn"
                id="nsMarkAllReadButton"
            >
                Mark All as Read
            </button>
        </form>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Feedback Messages
    |--------------------------------------------------------------------------
    --}}

    @if (session('success'))
        <div class="alert alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div
        id="nsNotificationFeedback"
        role="status"
        aria-live="polite"
        hidden
    ></div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Notification Records
    |--------------------------------------------------------------------------
    --}}

    <div class="notification-list">

        @forelse ($notifications as $notification)

            <div
                class="notification-card {{ !$notification->read_at ? 'unread' : '' }}"
                data-notification-id="{{ $notification->id }}"
            >

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

                    @if (!$notification->read_at)

                        {{--
                        |--------------------------------------------------------------------------
                        | NS BEAUTY COMMENT: Individual Mark as Read
                        |--------------------------------------------------------------------------
                        | No Open button.
                        | AJAX submission without leaving the page.
                        |--------------------------------------------------------------------------
                        --}}

                        <form
                            method="POST"
                            action="{{ route($readRouteName, $notification->id) }}"
                            class="ns-mark-read-form"
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
                <p>Your important updates will appear here.</p>
            </div>

        @endforelse

    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Pagination
    |--------------------------------------------------------------------------
    | SVG arrows are constrained by the CSS rules above.
    | Original Laravel pagination functionality is preserved.
    |--------------------------------------------------------------------------
    --}}

    <div class="pagination-wrap">
        {{ $notifications->links() }}
    </div>

</div>

@endsection

@section('scripts')
<script>

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: AJAX NOTIFICATION READ ACTIONS
|--------------------------------------------------------------------------
|
| Purpose:
| - Submit existing PATCH forms without redirecting.
| - Preserve CSRF protection.
| - Support Admin, Staff and Customer routes.
| - Update notification cards after successful responses.
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {

    const page = document.getElementById('nsNotificationsPage');

    if (!page) {
        return;
    }

    const feedback = document.getElementById(
        'nsNotificationFeedback'
    );

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Display Feedback Message
    |--------------------------------------------------------------------------
    */

    function showMessage(message, isError = false) {

        if (!feedback) {
            return;
        }

        feedback.hidden = false;
        feedback.textContent = message;

        feedback.className = isError
            ? 'alert alert-error'
            : 'alert alert-success';
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Update Read State
    |--------------------------------------------------------------------------
    */

    function updateCardAsRead(card) {

        if (!card) {
            return;
        }

        card.classList.remove('unread');

        const actions = card.querySelector(
            '.notification-actions'
        );

        if (!actions) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Replace Mark as Read Button
        |--------------------------------------------------------------------------
        */

        const form = actions.querySelector(
            '.ns-mark-read-form'
        );

        if (form) {
            form.remove();
        }

        if (!actions.querySelector('.read-label')) {

            const label = document.createElement('span');

            label.className = 'read-label';
            label.textContent = 'Read';

            actions.appendChild(label);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Submit Existing Laravel Form
    |--------------------------------------------------------------------------
    |
    | The existing POST form includes:
    | - CSRF token
    | - _method PATCH
    |
    | Fetch processes the response without changing pages.
    |--------------------------------------------------------------------------
    */

    async function submitReadForm(form) {

        const response = await fetch(form.action, {

            method: 'POST',

            body: new FormData(form),

            credentials: 'same-origin',

            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }

        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Authentication Error Handling
        |--------------------------------------------------------------------------
        */

        if (
            response.status === 401 ||
            response.status === 403 ||
            response.status === 419
        ) {
            throw new Error(
                'Your session may have expired. Refresh the page and sign in again.'
            );
        }

        if (!response.ok) {
            throw new Error(
                'The notification could not be updated.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Check Login Redirect
        |--------------------------------------------------------------------------
        */

        const destination = new URL(response.url);

        if (
            destination.pathname === '/login' ||
            destination.pathname.endsWith('/login')
        ) {
            throw new Error(
                'Please sign in again and retry.'
            );
        }

        return response;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Individual Mark as Read
    |--------------------------------------------------------------------------
    */

    page.querySelectorAll('.ns-mark-read-form')
        .forEach(function (form) {

            form.addEventListener('submit', async function (event) {

                event.preventDefault();

                const button = form.querySelector(
                    'button[type="submit"]'
                );

                const card = form.closest(
                    '.notification-card'
                );

                if (!button || button.disabled) {
                    return;
                }

                button.disabled = true;
                button.textContent = 'Saving...';

                try {

                    await submitReadForm(form);

                    updateCardAsRead(card);

                    showMessage(
                        'Notification marked as read.'
                    );

                } catch (error) {

                    button.disabled = false;
                    button.textContent = 'Mark as Read';

                    showMessage(
                        error.message ||
                        'Unable to mark notification as read.',
                        true
                    );

                }

            });

        });

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Mark All as Read
    |--------------------------------------------------------------------------
    */

    const markAllForm = document.getElementById(
        'nsMarkAllReadForm'
    );

    const markAllButton = document.getElementById(
        'nsMarkAllReadButton'
    );

    if (markAllForm && markAllButton) {

        markAllForm.addEventListener('submit', async function (event) {

            event.preventDefault();

            if (markAllButton.disabled) {
                return;
            }

            markAllButton.disabled = true;
            markAllButton.textContent = 'Saving...';

            try {

                await submitReadForm(markAllForm);

                page.querySelectorAll(
                    '.notification-card'
                ).forEach(function (card) {

                    updateCardAsRead(card);

                });

                showMessage(
                    'All notifications marked as read.'
                );

            } catch (error) {

                showMessage(
                    error.message ||
                    'Unable to mark all notifications as read.',
                    true
                );

            } finally {

                markAllButton.disabled = false;

                markAllButton.textContent = 'Mark All as Read';

            }

        });

    }

});

</script>
@endsection
