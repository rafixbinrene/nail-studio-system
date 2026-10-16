
@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Sidebar User and Notification Setup
    |--------------------------------------------------------------------------
    | Purpose:
    | - Detect the logged-in user's role.
    | - Display the correct role-based navigation.
    | - Show the unread notification count.
    | - Use the universal NS & Beauty circular logo.
    |
    | Logo:
    | public/images/nsbeauty-logo.png
    |--------------------------------------------------------------------------
    */

    $authUser = auth()->user();

    $roleName = $authUser?->role ?? strtolower($role ?? 'customer');
    $userDisplayName = $authUser?->name ?? ($userName ?? 'User');

    $roleLabel = match ($roleName) {
        'admin' => 'Admin',
        'staff' => 'Staff',
        default => 'Customer',
    };

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Unread Notification Count
    |--------------------------------------------------------------------------
    | Preserve the existing database-backed notification badge.
    |--------------------------------------------------------------------------
    */

    $unreadNotificationCount = 0;

    if ($authUser && class_exists(\App\Models\AppNotification::class)) {
        $unreadNotificationCount = \App\Models\AppNotification::where(
            'recipient_user_id',
            $authUser->id
        )
            ->whereNull('read_at')
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Role-Based Notification Route
    |--------------------------------------------------------------------------
    */

    $notificationRoute = match ($roleName) {
        'admin' => route('admin.notifications'),
        'staff' => route('staff.notifications'),
        default => route('customer.notifications'),
    };
@endphp

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Shared Sidebar
|--------------------------------------------------------------------------
|
| Used by:
| - Admin
| - Staff
| - Customer
|
| Existing sidebar ID is preserved for mobile navigation.
|--------------------------------------------------------------------------
--}}

<aside class="sidebar" id="mobileSidebar">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Universal Circular NS & Beauty Logo
    |--------------------------------------------------------------------------
    |
    | Uses the same logo as the Welcome page and shared layout.
    |
    | The logo is served from Laravel's public directory,
    | so it also works on Laravel Cloud after deployment.
    |--------------------------------------------------------------------------
    --}}

    <div
        class="logo ns-sidebar-brand"
        style="
            display:flex;
            align-items:center;
            gap:12px;
        "
    >

        <span
            class="ns-brand-logo"
            style="
                width:42px;
                height:42px;
                min-width:42px;
                min-height:42px;
                border-radius:50%;
                overflow:hidden;
                display:inline-flex;
                align-items:center;
                justify-content:center;
                flex-shrink:0;
                background:#f8f6f2;
                border:1px solid rgba(111,78,55,.12);
            "
        >

            <img
                src="{{ asset('images/nsbeauty-logo.png') }}"
                alt="NS & Beauty Logo"
                width="42"
                height="42"
                style="
                    width:100%;
                    height:100%;
                    object-fit:cover;
                    border-radius:50%;
                    display:block;
                "
            >

        </span>

        <span class="ns-sidebar-brand-name">
            NS &amp; Beauty
        </span>

    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Signed-In User Information
    |--------------------------------------------------------------------------
    | Existing name and role displays are preserved.
    |--------------------------------------------------------------------------
    --}}

    <div class="user-card">
        <small>SIGNED IN AS</small>
        <h3>{{ $userDisplayName }}</h3>
        <p>{{ $roleLabel }}</p>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Role-Based Navigation
    |--------------------------------------------------------------------------
    | All existing routes are preserved.
    |--------------------------------------------------------------------------
    --}}

    <nav class="menu">

        @if ($roleName === 'admin')

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Admin Navigation
            |--------------------------------------------------------------------------
            --}}

            <a
                href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Dashboard
            </a>

            <a
                href="{{ route('admin.bookings') }}"
                class="{{ request()->routeIs('admin.bookings') || request()->routeIs('admin.bookings.cancel') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Booking Management
            </a>

            <a
                href="{{ route('admin.services') }}"
                class="{{ request()->routeIs('admin.services') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Service Management
            </a>

            <a
                href="{{ route('admin.staff') }}"
                class="{{ request()->routeIs('admin.staff') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Staff Management
            </a>

            <a
                href="{{ route('admin.content') }}"
                class="{{ request()->routeIs('admin.content') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Content Management
            </a>

            <a
                href="{{ route('admin.customers') }}"
                class="{{ request()->routeIs('admin.customers') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Customer Management
            </a>

            <a
                href="{{ $notificationRoute }}"
                class="{{ request()->routeIs('admin.notifications') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Notifications

                @if ($unreadNotificationCount > 0)
                    <span class="notification-badge">
                        {{ $unreadNotificationCount }}
                    </span>
                @endif
            </a>

            <a
                href="{{ route('admin.auditlogs') }}"
                class="{{ request()->routeIs('admin.auditlogs') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Audit Logs
            </a>

            <a
                href="{{ route('admin.settings') }}"
                class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                System Settings
            </a>

        @elseif ($roleName === 'staff')

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Staff Navigation
            |--------------------------------------------------------------------------
            --}}

            <a
                href="{{ route('staff.dashboard') }}"
                class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Dashboard
            </a>

            <a
                href="{{ route('staff.appointments') }}"
                class="{{ request()->routeIs('staff.appointments') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Appointments
            </a>

            <a
                href="{{ $notificationRoute }}"
                class="{{ request()->routeIs('staff.notifications') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Notifications

                @if ($unreadNotificationCount > 0)
                    <span class="notification-badge">
                        {{ $unreadNotificationCount }}
                    </span>
                @endif
            </a>

            <a
                href="{{ route('staff.profile') }}"
                class="{{ request()->routeIs('staff.profile') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Profile
            </a>

        @else

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Customer Navigation
            |--------------------------------------------------------------------------
            --}}

            <a
                href="{{ route('customer.dashboard') }}"
                class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Dashboard
            </a>

            <a
                href="{{ route('customer.booking') }}"
                class="{{ request()->routeIs('customer.booking') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Book Appointment
            </a>

            <a
                href="{{ route('customer.history') }}"
                class="{{ request()->routeIs('customer.history') || request()->routeIs('customer.booking.details') || request()->routeIs('customer.feedback') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Booking History
            </a>

            <a
                href="{{ $notificationRoute }}"
                class="{{ request()->routeIs('customer.notifications') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Notifications

                @if ($unreadNotificationCount > 0)
                    <span class="notification-badge">
                        {{ $unreadNotificationCount }}
                    </span>
                @endif
            </a>

            <a
                href="{{ route('customer.profile') }}"
                class="{{ request()->routeIs('customer.profile') ? 'active' : '' }}"
                onclick="closeMobileSidebar()"
            >
                Profile
            </a>

        @endif

    </nav>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Sign Out
    |--------------------------------------------------------------------------
    | Existing POST logout route and CSRF protection are preserved.
    |--------------------------------------------------------------------------
    --}}

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button
            type="submit"
            class="signout"
            style="
                border:none;
                width:100%;
                text-align:left;
                cursor:pointer;
            "
        >
            Sign Out
        </button>
    </form>

</aside>
