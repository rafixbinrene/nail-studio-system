
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
    | NS BEAUTY COMMENT: Universal Brand Logo and Favicon
    |--------------------------------------------------------------------------
    |
    | Logo file:
    | public/images/nsbeauty-logo.png
    |
    | asset() generates the correct URL both locally and
    | on the live Laravel Cloud website.
    |--------------------------------------------------------------------------
    --}}

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('images/nsbeauty-logo.png') }}"
    >

    <link
        rel="apple-touch-icon"
        href="{{ asset('images/nsbeauty-logo.png') }}"
    >

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
    | NS BEAUTY COMMENT: Global Design System
    |--------------------------------------------------------------------------
    |
    | Preserves the standardized UI radii for all roles.
    | Adds universal circular logo styling.
    |--------------------------------------------------------------------------
    --}}

    <style>

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Global Design Tokens
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
        | NS BEAUTY COMMENT: Universal Circular Business Logo
        |--------------------------------------------------------------------------
        |
        | Use this styling in shared navigation and sidebar branding.
        |
        | Logo image source:
        | asset('images/nsbeauty-logo.png')
        |
        | The image is cropped into a perfect circle, regardless
        | of the original image's outer dimensions.
        |--------------------------------------------------------------------------
        */

        .ns-brand-logo {
            width: 38px;
            height: 38px;
            min-width: 38px;
            min-height: 38px;

            flex-shrink: 0;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;
            overflow: hidden;

            background: #f8f6f2;
            border: 1px solid rgba(111,78,55,.12);

            box-shadow:
                0 5px 12px rgba(47,33,24,.08);
        }

        .ns-brand-logo img {
            display: block;
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;

            border-radius: 50%;
            object-fit: cover;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Reusable Brand Row
        |--------------------------------------------------------------------------
        */

        .ns-brand-row {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .ns-brand-name {
            color: #2f2118;
            font-size: 14px;
            font-weight: 850;
            line-height: 1.3;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Smaller Logo Variant
        |--------------------------------------------------------------------------
        */

        .ns-brand-logo.ns-logo-small {
            width: 32px;
            height: 32px;
            min-width: 32px;
            min-height: 32px;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Larger Logo Variant
        |--------------------------------------------------------------------------
        */

        .ns-brand-logo.ns-logo-large {
            width: 48px;
            height: 48px;
            min-width: 48px;
            min-height: 48px;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Mobile Top Bar Brand
        |--------------------------------------------------------------------------
        |
        | Preserves the existing mobile menu and sidebar toggles.
        | Adds a circular logo beside the business name.
        |--------------------------------------------------------------------------
        */

        .mobile-topbar .mobile-brand {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .mobile-topbar .mobile-brand .ns-brand-logo {
            width: 32px;
            height: 32px;
            min-width: 32px;
            min-height: 32px;
        }

        .mobile-topbar .mobile-brand .ns-brand-name {
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
        |
        | Sidebar branding will be updated in its own file
        | without changing any navigation permissions.
        |--------------------------------------------------------------------------
        --}}

        @include('partials.sidebar')

        <main class="main">

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Mobile Top Bar
            |--------------------------------------------------------------------------
            |
            | Displays the official NS & Beauty round logo.
            | All roles share this layout.
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

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Universal Mobile Brand
                |--------------------------------------------------------------------------
                --}}

                <span class="mobile-brand">

                    <span class="ns-brand-logo">

                        <img
                            src="{{ asset('images/nsbeauty-logo.png') }}"
                            alt="NS & Beauty Logo"
                            width="32"
                            height="32"
                        >

                    </span>

                    <span class="ns-brand-name">
                        NS &amp; Beauty
                    </span>

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

            const sidebar = document.getElementById(
                'mobileSidebar'
            );

            const overlay = document.getElementById(
                'mobileSidebarOverlay'
            );

            if (!sidebar || !overlay) {
                return;
            }

            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show');
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Close Mobile Sidebar
        |--------------------------------------------------------------------------
        */

        function closeMobileSidebar() {

            const sidebar = document.getElementById(
                'mobileSidebar'
            );

            const overlay = document.getElementById(
                'mobileSidebarOverlay'
            );

            if (!sidebar || !overlay) {
                return;
            }

            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
        }
    </script>

</body>
</html>
