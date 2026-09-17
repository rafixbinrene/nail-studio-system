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

<aside class="sidebar">
    <div class="logo">NS &amp; Beauty</div>

    <div class="user-card">
        <small>SIGNED IN AS</small>
        <h3>{{ $userDisplayName }}</h3>
        <p>{{ $roleLabel }}</p>
    </div>

    <nav class="menu">
        @if($roleName === 'admin')

            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>

            <a href="{{ route('admin.bookings') }}"
                class="{{ request()->routeIs('admin.bookings') || request()->routeIs('admin.bookings.cancel') ? 'active' : '' }}">
                Booking Management
            </a>

            <a href="{{ route('admin.services') }}" class="{{ request()->routeIs('admin.services') ? 'active' : '' }}">Service Management</a>
            <a href="{{ route('admin.staff') }}" class="{{ request()->routeIs('admin.staff') ? 'active' : '' }}">Staff Management</a>
            <a href="{{ route('admin.content') }}" class="{{ request()->routeIs('admin.content') ? 'active' : '' }}">Content Management</a>
            <a href="{{ route('admin.customers') }}" class="{{ request()->routeIs('admin.customers') ? 'active' : '' }}">Customer Management</a>

            <a href="{{ $notificationRoute }}"
                class="{{ request()->routeIs('admin.notifications') ? 'active' : '' }}">
                Notifications
                @if ($unreadNotificationCount > 0)
                    <span class="notification-badge">{{ $unreadNotificationCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.auditlogs') }}" class="{{ request()->routeIs('admin.auditlogs') ? 'active' : '' }}">Audit Logs</a>
            <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">System Settings</a>

        @elseif($roleName === 'staff')

            <a href="{{ route('staff.dashboard') }}" class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('staff.appointments') }}" class="{{ request()->routeIs('staff.appointments') ? 'active' : '' }}">Appointments</a>

            <a href="{{ $notificationRoute }}"
                class="{{ request()->routeIs('staff.notifications') ? 'active' : '' }}">
                Notifications
                @if ($unreadNotificationCount > 0)
                    <span class="notification-badge">{{ $unreadNotificationCount }}</span>
                @endif
            </a>

            <a href="{{ route('staff.profile') }}" class="{{ request()->routeIs('staff.profile') ? 'active' : '' }}">Profile</a>

        @else

            <a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('customer.booking') }}" class="{{ request()->routeIs('customer.booking') ? 'active' : '' }}">Book Appointment</a>
            <a href="{{ route('customer.history') }}" class="{{ request()->routeIs('customer.history') || request()->routeIs('customer.booking.details') || request()->routeIs('customer.feedback') ? 'active' : '' }}">Booking History</a>

            <a href="{{ $notificationRoute }}"
                class="{{ request()->routeIs('customer.notifications') ? 'active' : '' }}">
                Notifications
                @if ($unreadNotificationCount > 0)
                    <span class="notification-badge">{{ $unreadNotificationCount }}</span>
                @endif
            </a>

            <a href="{{ route('customer.profile') }}" class="{{ request()->routeIs('customer.profile') ? 'active' : '' }}">Profile</a>

        @endif
    </nav>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button
            type="submit"
            class="signout"
            style="border:none;width:100%;text-align:left;cursor:pointer;"
        >
            ↪ Sign Out
        </button>
    </form>
</aside>