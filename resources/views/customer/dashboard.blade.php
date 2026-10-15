
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Customer Dashboard
|--------------------------------------------------------------------------
| Purpose:
| - Displays the nearest upcoming appointment
| - Displays notifications supplied by the controller
| - Displays booking history summary
| - Displays services supplied by the controller
|
| Frontend updates:
| - Removes the six dashboard statistic cards
| - Uses consistent border-radius values
| - Removes all service images
| - Preserves existing booking routes and functionality
|
| IMPORTANT:
| Personalized recommendations and real notification queries
| must be implemented or verified in the dashboard controller
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Dashboard Design Tokens
    |--------------------------------------------------------------------------
    | Matches the Login and Registration UI
    |--------------------------------------------------------------------------
    */

    .customer-dashboard {
        --dashboard-card-radius: 24px;
        --dashboard-element-radius: 14px;
        --dashboard-brown: #6f4e37;
        --dashboard-dark: #2f2118;
        --dashboard-muted: #7d6d60;
    }

    .customer-dashboard *,
    .customer-dashboard *::before,
    .customer-dashboard *::after {
        box-sizing: border-box;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Dashboard Header
    |--------------------------------------------------------------------------
    */

    .customer-dashboard .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        margin-bottom: 28px;
    }

    .customer-dashboard .topbar h1 {
        font-size: 34px;
        line-height: 1.2;
        margin-bottom: 8px;
        color: var(--dashboard-dark);
    }

    .customer-dashboard .topbar p {
        color: var(--muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .customer-dashboard .book-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 14px 22px;
        min-height: 48px;
        border-radius: var(--dashboard-element-radius);
        background: linear-gradient(135deg, #8a7158, #5d3f2c);
        color: white;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
        white-space: nowrap;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .customer-dashboard .book-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(47, 33, 24, .15);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Main Dashboard Layout
    |--------------------------------------------------------------------------
    | Six statistic cards have been intentionally removed
    |--------------------------------------------------------------------------
    */

    .customer-dashboard .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 24px;
        margin-bottom: 24px;
    }

    .customer-dashboard .lower-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .customer-dashboard .dashboard-card {
        min-width: 0;
        background: var(--glass, rgba(255, 255, 255, .54));
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid var(--border, rgba(255, 255, 255, .7));
        border-radius: var(--dashboard-card-radius);
        overflow: hidden;
        box-shadow:
            0 20px 50px rgba(47, 33, 24, .08),
            inset 0 1px 0 rgba(255, 255, 255, .7);
    }

    .customer-dashboard .card-header {
        padding: 22px 24px;
        border-bottom: 1px solid rgba(111, 78, 55, .10);
        color: var(--dashboard-dark);
        font-size: 16px;
        font-weight: 900;
    }

    .customer-dashboard .card-body {
        padding: 24px;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Upcoming Appointment
    |--------------------------------------------------------------------------
    */

    .customer-dashboard .appointment-box {
        padding: 18px;
        border-radius: var(--dashboard-element-radius);
        background: rgba(255, 255, 255, .45);
        border: 1px solid rgba(255, 255, 255, .65);
    }

    .customer-dashboard .appointment-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 18px;
    }

    .customer-dashboard .appointment-top h3 {
        font-size: 18px;
        margin-bottom: 6px;
        color: var(--dashboard-dark);
    }

    .customer-dashboard .appointment-top p {
        color: var(--dashboard-muted);
        font-size: 13px;
    }

    .customer-dashboard .status,
    .customer-dashboard .type-badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: var(--dashboard-element-radius);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .customer-dashboard .type-badge.regular {
        background: rgba(255, 255, 255, .55);
        color: var(--dashboard-dark);
        border: 1px solid rgba(111, 78, 55, .12);
    }

    .customer-dashboard .type-badge.followup {
        background: rgba(138, 113, 88, .18);
        color: #6f4e37;
    }

    .customer-dashboard .status.pending {
        background: rgba(255, 196, 87, .22);
        color: #9a6a00;
    }

    .customer-dashboard .status.approved {
        background: rgba(82, 139, 255, .15);
        color: #2f5fb8;
    }

    .customer-dashboard .status.ongoing {
        background: rgba(138, 113, 88, .18);
        color: #6f4e37;
    }

    .customer-dashboard .status.finished {
        background: rgba(81, 148, 91, .18);
        color: #2f7d3c;
    }

    .customer-dashboard .status.cancelled,
    .customer-dashboard .status.no-show {
        background: rgba(184, 77, 77, .15);
        color: #b84d4d;
    }

    .customer-dashboard .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .customer-dashboard .detail-box {
        min-width: 0;
        padding: 14px;
        border-radius: var(--dashboard-element-radius);
        background: rgba(255, 255, 255, .45);
        border: 1px solid rgba(255, 255, 255, .65);
    }

    .customer-dashboard .detail-box small {
        display: block;
        color: var(--dashboard-muted);
        font-size: 11px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .customer-dashboard .detail-box p {
        color: var(--dashboard-dark);
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Action Buttons
    |--------------------------------------------------------------------------
    */

    .customer-dashboard .action-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .customer-dashboard .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        padding: 11px 18px;
        min-height: 42px;
        border-radius: var(--dashboard-element-radius);
        font-size: 13px;
        font-weight: 800;
        transition: transform .2s ease;
    }

    .customer-dashboard .action-btn:hover {
        transform: translateY(-2px);
    }

    .customer-dashboard .primary-btn {
        background: linear-gradient(135deg, #8a7158, #5d3f2c);
        color: white;
    }

    .customer-dashboard .light-btn {
        background: rgba(255, 255, 255, .60);
        color: var(--dashboard-dark);
        border: 1px solid rgba(111, 78, 55, .13);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Empty States
    |--------------------------------------------------------------------------
    */

    .customer-dashboard .empty-box {
        text-align: center;
        padding: 34px 16px;
        color: var(--dashboard-muted);
    }

    .customer-dashboard .empty-icon {
        font-size: 36px;
        margin-bottom: 12px;
    }

    .customer-dashboard .empty-box h3 {
        color: var(--dashboard-dark);
        margin-bottom: 8px;
        font-size: 17px;
    }

    .customer-dashboard .empty-box p {
        font-size: 13px;
        line-height: 1.6;
    }

    .customer-dashboard .empty-box a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 18px;
        padding: 12px 18px;
        border-radius: var(--dashboard-element-radius);
        background: linear-gradient(135deg, #8a7158, #5d3f2c);
        color: white;
        text-decoration: none;
        font-size: 13px;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Notifications
    |--------------------------------------------------------------------------
    | This view shows data supplied by the dashboard controller
    | The controller must query actual customer notifications
    |--------------------------------------------------------------------------
    */

    .customer-dashboard .notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        border-radius: var(--dashboard-element-radius);
        background: rgba(255, 255, 255, .45);
        border: 1px solid rgba(255, 255, 255, .65);
        margin-bottom: 12px;
    }

    .customer-dashboard .notice:last-child {
        margin-bottom: 0;
    }

    .customer-dashboard .notice-dot {
        width: 9px;
        height: 9px;
        margin-top: 6px;
        border-radius: 50%;
        background: var(--dashboard-brown);
        flex-shrink: 0;
    }

    .customer-dashboard .notice-content {
        min-width: 0;
    }

    .customer-dashboard .notice h4 {
        margin-bottom: 5px;
        font-size: 14px;
        color: var(--dashboard-dark);
    }

    .customer-dashboard .notice p {
        color: var(--dashboard-muted);
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Booking History Summary
    |--------------------------------------------------------------------------
    */

    .customer-dashboard .history-section {
        padding: 16px;
        border-radius: var(--dashboard-element-radius);
        background: rgba(255, 255, 255, .45);
        border: 1px solid rgba(255, 255, 255, .65);
        margin-bottom: 12px;
    }

    .customer-dashboard .history-section h4 {
        font-size: 12px;
        color: var(--dashboard-muted);
        margin-bottom: 6px;
        font-weight: 800;
    }

    .customer-dashboard .history-section p {
        color: var(--dashboard-dark);
        font-size: 14px;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Favorite / Recommended Services
    |--------------------------------------------------------------------------
    | Text-only cards
    | No images, stock pictures, or image fallback
    |--------------------------------------------------------------------------
    */

    .customer-dashboard .service-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 18px;
        border-radius: var(--dashboard-element-radius);
        background: rgba(255, 255, 255, .45);
        border: 1px solid rgba(255, 255, 255, .65);
        margin-bottom: 12px;
    }

    .customer-dashboard .service-item:last-child {
        margin-bottom: 0;
    }

    .customer-dashboard .service-info {
        min-width: 0;
        flex: 1;
    }

    .customer-dashboard .service-info h4 {
        margin-bottom: 7px;
        font-size: 15px;
        font-weight: 800;
        color: var(--dashboard-dark);
    }

    .customer-dashboard .service-info p {
        color: var(--dashboard-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .customer-dashboard .service-price {
        color: var(--dashboard-brown);
        font-size: 14px;
        font-weight: 900;
        white-space: nowrap;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Accessibility
    |--------------------------------------------------------------------------
    */

    .customer-dashboard a:focus-visible {
        outline: 3px solid rgba(111, 78, 55, .45);
        outline-offset: 3px;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Mobile Responsiveness
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1000px) {
        .customer-dashboard .dashboard-grid,
        .customer-dashboard .lower-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 650px) {
        .customer-dashboard .topbar {
            flex-direction: column;
            align-items: stretch;
            gap: 16px;
        }

        .customer-dashboard .topbar h1 {
            font-size: 28px;
        }

        .customer-dashboard .book-btn {
            width: 100%;
        }

        .customer-dashboard .dashboard-grid,
        .customer-dashboard .lower-grid {
            gap: 16px;
        }

        .customer-dashboard .card-header {
            padding: 18px;
        }

        .customer-dashboard .card-body {
            padding: 18px;
        }

        .customer-dashboard .appointment-top {
            flex-direction: column;
        }

        .customer-dashboard .detail-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .customer-dashboard .service-item {
            align-items: flex-start;
            flex-direction: column;
        }

        .customer-dashboard .service-price {
            white-space: normal;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .customer-dashboard .book-btn,
        .customer-dashboard .action-btn {
            transition: none;
        }
    }
</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Authenticated Customer
    |--------------------------------------------------------------------------
    | Keep existing authenticated customer data
    |--------------------------------------------------------------------------
    */

    $user = auth()->user();
@endphp

<div class="customer-dashboard">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Dashboard Header
    |--------------------------------------------------------------------------
    --}}

    <div class="topbar">
        <div>
            <h1>
                Welcome Back, {{ $customer?->full_name ?? $user->name }}!
            </h1>

            <p>
                Ready for your next pampering session?
            </p>
        </div>

        <a
            href="{{ route('customer.booking') }}"
            class="book-btn"
        >
            Book Appointment
        </a>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Dashboard Statistics Removed
    |--------------------------------------------------------------------------
    | Removed:
    | - Total Bookings
    | - Active Bookings
    | - Finished
    | - Cancelled
    | - Backjob / Follow-up
    | - Remaining Active Slots
    |
    | Existing backend booking limits remain unchanged
    |--------------------------------------------------------------------------
    --}}

    <div class="dashboard-grid">

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Upcoming Appointment
        |--------------------------------------------------------------------------
        | Uses the existing upcoming appointment object
        |--------------------------------------------------------------------------
        --}}

        <section class="dashboard-card">

            <div class="card-header">
                Upcoming Appointment
            </div>

            <div class="card-body">

                @if ($upcomingAppointment)

                    @php
                        $statusKey = strtolower(
                            str_replace(' ', '-', $upcomingAppointment->status)
                        );

                        $statusLabel = ucwords(
                            str_replace('-', ' ', $statusKey)
                        );

                        $bookingType = $upcomingAppointment->booking_type === 'followup'
                            ? 'followup'
                            : 'regular';

                        $bookingTypeLabel = $bookingType === 'followup'
                            ? 'Backjob / Follow-up'
                            : 'Regular Booking';

                        $mainService = $upcomingAppointment->services
                            ->first()?->service_name ?? 'Beauty Service';

                        $appointmentDate = $upcomingAppointment->appointment_date
                            ? \Carbon\Carbon::parse(
                                $upcomingAppointment->appointment_date
                              )->format('F d, Y')
                            : 'Not set';

                        $startTime = $upcomingAppointment->start_time
                            ? \Carbon\Carbon::parse(
                                $upcomingAppointment->start_time
                              )->format('h:i A')
                            : 'Not set';

                        $endTime = $upcomingAppointment->end_time
                            ? \Carbon\Carbon::parse(
                                $upcomingAppointment->end_time
                              )->format('h:i A')
                            : null;
                    @endphp

                    <div class="appointment-box">

                        <div class="appointment-top">

                            <div>
                                <h3>{{ $mainService }}</h3>

                                <p>
                                    Booking ID:
                                    #BK-{{ str_pad($upcomingAppointment->id, 4, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>

                            <div class="action-row">

                                <span class="type-badge {{ $bookingType }}">
                                    {{ $bookingTypeLabel }}
                                </span>

                                <span class="status {{ $statusKey }}">
                                    {{ $statusLabel }}
                                </span>

                            </div>

                        </div>

                        <div class="detail-grid">

                            <div class="detail-box">
                                <small>DATE</small>
                                <p>{{ $appointmentDate }}</p>
                            </div>

                            <div class="detail-box">
                                <small>TIME</small>

                                <p>
                                    {{ $startTime }}

                                    @if ($endTime)
                                        – {{ $endTime }}
                                    @endif
                                </p>
                            </div>

                            <div class="detail-box">
                                <small>STAFF</small>

                                <p>
                                    {{ $upcomingAppointment->staff?->full_name ?? 'Not assigned' }}
                                </p>
                            </div>

                            <div class="detail-box">
                                <small>TOTAL</small>

                                <p>
                                    RM {{ number_format($upcomingAppointment->total_price, 2) }}
                                </p>
                            </div>

                        </div>

                        <div class="action-row">

                            <a
                                href="{{ route('customer.booking.details', $upcomingAppointment->id) }}"
                                class="action-btn primary-btn"
                            >
                                View Details
                            </a>

                            <a
                                href="{{ route('customer.history') }}"
                                class="action-btn light-btn"
                            >
                                View History
                            </a>

                        </div>

                    </div>

                @else

                    <div class="empty-box">

                        <div class="empty-icon" aria-hidden="true">
                            🗓️
                        </div>

                        <h3>No upcoming appointments</h3>

                        <p>
                            You don't have any active appointments yet
                        </p>

                        <a href="{{ route('customer.booking') }}">
                            Book an Appointment
                        </a>

                    </div>

                @endif

            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Notifications
        |--------------------------------------------------------------------------
        | Reads the notifications collection provided by the controller
        | No placeholder notifications are generated in this view
        |--------------------------------------------------------------------------
        --}}

        <section class="dashboard-card">

            <div class="card-header">
                Notifications
            </div>

            <div class="card-body">

                @forelse ($notifications as $notification)

                    <div class="notice">

                        <div
                            class="notice-dot"
                            aria-hidden="true"
                        ></div>

                        <div class="notice-content">

                            <h4>
                                {{ data_get($notification, 'title', 'Notification') }}
                            </h4>

                            <p>
                                {{ data_get($notification, 'message', '') }}
                            </p>

                        </div>

                    </div>

                @empty

                    <div class="empty-box">

                        <div class="empty-icon" aria-hidden="true">
                            🔔
                        </div>

                        <h3>No notifications yet</h3>

                        <p>
                            Your appointment updates and reminders
                            will appear here
                        </p>

                    </div>

                @endforelse

            </div>

        </section>

    </div>

    <div class="lower-grid">

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking History Summary
        |--------------------------------------------------------------------------
        | Preserves existing database-derived booking collections
        |--------------------------------------------------------------------------
        --}}

        <section class="dashboard-card">

            <div class="card-header">
                Booking History Summary
            </div>

            <div class="card-body">

                <div class="history-section">
                    <h4>ACTIVE BOOKINGS</h4>

                    <p>
                        {{ $activeBookings->count() }}
                        active {{ $activeBookings->count() === 1 ? 'booking' : 'bookings' }}
                    </p>
                </div>

                <div class="history-section">
                    <h4>FINISHED BOOKINGS</h4>

                    <p>
                        {{ $finishedBookings->count() }}
                        finished {{ $finishedBookings->count() === 1 ? 'booking' : 'bookings' }}
                    </p>
                </div>

                <div class="history-section">
                    <h4>CANCELLED BOOKINGS</h4>

                    <p>
                        {{ $cancelledBookings->count() }}
                        cancelled {{ $cancelledBookings->count() === 1 ? 'booking' : 'bookings' }}
                    </p>
                </div>

                <div class="history-section">
                    <h4>NO-SHOW BOOKINGS</h4>

                    <p>
                        {{ $noShowBookings->count() }}
                        no-show {{ $noShowBookings->count() === 1 ? 'booking' : 'bookings' }}
                    </p>
                </div>

                <div class="history-section">
                    <h4>BACKJOB / FOLLOW-UP</h4>

                    <p>
                        {{ $followUpBookings->count() }}
                        follow-up {{ $followUpBookings->count() === 1 ? 'booking' : 'bookings' }}
                    </p>
                </div>

                <div
                    class="action-row"
                    style="margin-top: 16px;"
                >

                    <a
                        href="{{ route('customer.history') }}"
                        class="action-btn primary-btn"
                    >
                        Open Booking History
                    </a>

                </div>

            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Favorite / Recommended Services
        |--------------------------------------------------------------------------
        | Text-only service presentation
        |
        | Data source:
        | $favoriteServices supplied by the dashboard controller
        |
        | Controller must use:
        | - Real service records
        | - Actual customer appointment history
        | - Available service statuses
        |
        | No images are rendered or requested
        |--------------------------------------------------------------------------
        --}}

        <section class="dashboard-card">

            <div class="card-header">
                Favorite / Recommended Services
            </div>

            <div class="card-body">

                @forelse ($favoriteServices as $service)

                    <div class="service-item">

                        <div class="service-info">

                            <h4>
                                {{ $service->service_name }}
                            </h4>

                            <p>
                                @if (isset($service->times_booked) && $service->times_booked > 0)

                                    Booked {{ $service->times_booked }}
                                    {{ $service->times_booked == 1 ? 'time' : 'times' }}

                                @else

                                    {{ $service->duration }} minutes

                                @endif
                            </p>

                        </div>

                        <div class="service-price">
                            RM {{ number_format($service->price, 2) }}
                        </div>

                    </div>

                @empty

                    <div class="empty-box">

                        <div class="empty-icon" aria-hidden="true">
                            ♡
                        </div>

                        <h3>No favorite services yet</h3>

                        <p>
                            Your favorite services will appear here
                            based on your booking history
                        </p>

                        <a href="{{ route('customer.booking') }}">
                            Explore Services
                        </a>

                    </div>

                @endforelse

            </div>

        </section>

    </div>

</div>

@endsection
