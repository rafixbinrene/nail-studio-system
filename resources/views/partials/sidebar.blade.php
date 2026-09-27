@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Sidebar User and Notification Setup
    |--------------------------------------------------------------------------
    | Purpose:
    | - Detects logged-in user role.
    | - Displays role-based menu.
    | - Shows unread notification count.
    |--------------------------------------------------------------------------
    */

    $authUser = auth()->user();

    $roleName = $authUser?->role ?? strtolower($role ?? 'customer');
    $userDisplayName = $authUser?->name ?? ($userName ?? 'User');

    $roleLabel = match($roleName) {
        'admin' => 'Admin',
        'staff' => 'Staff',
        default => 'Customer',
    };

    $unreadNotificationCount = 0;

    if ($authUser && class_exists(\App\Models\AppNotification::class)) {
        $unreadNotificationCount = \App\Models\AppNotification::where('recipient_user_id', $authUser->id)
            ->whereNull('read_at')
            ->count();
    }

    $notificationRoute = match($roleName) {
        'admin' => route('admin.notifications'),
        'staff' => route('staff.notifications'),
        default => route('customer.notifications'),
    };
@endphp

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Sidebar Navigation
|--------------------------------------------------------------------------
| Purpose:
| - Shows role-based navigation for Admin, Staff, and Customer.
| - Uses id="mobileSidebar" so the mobile menu button can open/fold it.
| - Sidebar remains fixed on desktop and slides in only on mobile.
|--------------------------------------------------------------------------
--}}
<aside class="sidebar" id="mobileSidebar">
    <div class="logo">NS &amp; Beauty</div>

    <div class="user-card">
        <small>SIGNED IN AS</small>
        <h3>{{ $userDisplayName }}</h3>
        <p>{{ $roleLabel }}</p>
    </div>

    <nav class="menu">
        @if($roleName === 'admin')

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
                    <span class="notification-badge">{{ $unreadNotificationCount }}</span>
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

        @elseif($roleName === 'staff')

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
                    <span class="notification-badge">{{ $unreadNotificationCount }}</span>
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
                    <span class="notification-badge">{{ $unreadNotificationCount }}</span>
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

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        {{-- 
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Sign Out Button
        |--------------------------------------------------------------------------
        | Purpose:
        | - Removed the logout emoji.
        | - Keeps the button clean and professional for defense/demo.
        |--------------------------------------------------------------------------
        --}}
        <button
            type="submit"
            class="signout"
            style="border:none;width:100%;text-align:left;cursor:pointer;"
        >
            Sign Out
        </button>
    </form>
</aside>