
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Mobile Responsive Viewport
    |--------------------------------------------------------------------------
    | Preserves responsive rendering on iPhone and Android devices.
    |--------------------------------------------------------------------------
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'NS & Beauty' }}</title>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Main Layout Stylesheet
    |--------------------------------------------------------------------------
    --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Individual Module Styles
    |--------------------------------------------------------------------------
    | Original module styles remain supported.
    |--------------------------------------------------------------------------
    --}}
    @yield('styles')

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Global Border Radius Design System
    |--------------------------------------------------------------------------
    |
    | Purpose:
    | - Standardize UI corners across Admin, Staff and Customer modules
    | - Maintain the existing Muji brown/cream design
    | - Allow future radius changes from one central location
    |
    | Important:
    | This block appears AFTER module styles so it can override
    | most ordinary class-based border-radius declarations.
    |
    | Highly specific selectors and inline !important rules
    | may still require separate adjustments.
    |--------------------------------------------------------------------------
    --}}

    <style>
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Global Design Tokens
        |--------------------------------------------------------------------------
        | Change these values to adjust the system's corner styles.
        |--------------------------------------------------------------------------
        */

        :root {
            --ns-radius-card: 24px;
            --ns-radius-inner: 14px;
            --ns-radius-button: 14px;
            --ns-radius-input: 14px;
            --ns-radius-pill: 999px;
            --ns-radius-circle: 50%;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Main Cards, Panels and Popups
        |--------------------------------------------------------------------------
        */

        :where(.main) :is(
            .history-card,
            .notification-card,
            .profile-card,
            .form-card,
            .booking-panel,
            .booking-rules,
            .details-card,
            .calendar-card,
            .summary-card,
            .stat-card,
            .stats-card,
            .dashboard-card,
            .content-card,
            .glass-card,
            .service-card,
            .reschedule-panel,
            .reschedule-notice,
            .success-modal,
            .modal-content,
            .modal-panel,
            .popup-content,
            .popup-panel,
            .empty-state
        ) {
            border-radius: var(--ns-radius-card);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Inner Information Cards
        |--------------------------------------------------------------------------
        */

        :where(.main) :is(
            .detail-box,
            .info-item,
            .service-list,
            .feedback-box,
            .followup-box,
            .history-box,
            .service-box,
            .warning-box,
            .original-schedule,
            .reschedule-inner-card,
            .reschedule-summary,
            .booking-rule-footer,
            .alert,
            .booking-error,
            .message-success,
            .message-error
        ) {
            border-radius: var(--ns-radius-inner);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Buttons and Tabs
        |--------------------------------------------------------------------------
        | Applies to common action buttons, filter tabs
        | and form buttons without changing their behavior.
        |--------------------------------------------------------------------------
        */

        :where(.main) :is(
            button,
            .btn,
            .action-btn,
            .booking-btn,
            .tab-btn,
            .nav-btn,
            .save-btn,
            .read-btn,
            .read-all-btn,
            .view-btn,
            .cancel-btn,
            .feedback-btn,
            .back-btn,
            .calendar-day,
            .time-btn,
            .reschedule-date,
            .reschedule-time
        ):not(
            .avatar-circle,
            .step-circle,
            .icon-btn,
            .icon-button,
            .pagination-dot
        ) {
            border-radius: var(--ns-radius-button);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Form Fields
        |--------------------------------------------------------------------------
        */

        :where(.main) :is(
            input:not(
                [type="checkbox"],
                [type="radio"],
                [type="hidden"]
            ),
            select,
            textarea,
            .form-input,
            .reschedule-select
        ) {
            border-radius: var(--ns-radius-input);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Pill-Shaped Badges
        |--------------------------------------------------------------------------
        | These stay fully rounded for status readability.
        |--------------------------------------------------------------------------
        */

        :where(.main) :is(
            .status,
            .badge,
            .status-badge,
            .type-badge,
            .pill,
            .service-badge
        ) {
            border-radius: var(--ns-radius-pill);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Circular Avatars
        |--------------------------------------------------------------------------
        | Preserve profile and staff avatars.
        |--------------------------------------------------------------------------
        */

        :where(.main) :is(
            .avatar-circle,
            .staff-avatar,
            .profile-avatar,
            .step-circle
        ) {
            border-radius: var(--ns-radius-circle);
        }
    </style>
</head>

<body>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Mobile Sidebar Overlay
    |--------------------------------------------------------------------------
    | Click outside an open mobile sidebar to close it.
    |--------------------------------------------------------------------------
    --}}

    <div
        class="mobile-sidebar-overlay"
        id="mobileSidebarOverlay"
        onclick="closeMobileSidebar()"
    ></div>

    <div class="dashboard">

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Shared Sidebar
        |--------------------------------------------------------------------------
        | Preserves Admin, Staff and Customer navigation.
        |--------------------------------------------------------------------------
        --}}

        @include('partials.sidebar')

        <main class="main">

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Mobile Top Bar
            |--------------------------------------------------------------------------
            --}}

            <div class="mobile-topbar">
                <button
                    type="button"
                    class="mobile-menu-btn"
                    onclick="toggleMobileSidebar()"
                    aria-label="Open menu"
                >
                    ☰
                </button>

                <span class="mobile-brand">
                    NS & Beauty
                </span>
            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Existing Page Content
            |--------------------------------------------------------------------------
            --}}

            @yield('content')

        </main>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Existing Module Scripts
    |--------------------------------------------------------------------------
    --}}

    @yield('scripts')

    <script>
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Mobile Sidebar Toggle
    |--------------------------------------------------------------------------
    | Original sidebar behavior preserved.
    |--------------------------------------------------------------------------
    */

    function toggleMobileSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');

        if (!sidebar || !overlay) {
            return;
        }

        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('show');
    }

    function closeMobileSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');

        if (!sidebar || !overlay) {
            return;
        }

        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('show');
    }
    </script>

</body>
</html>
