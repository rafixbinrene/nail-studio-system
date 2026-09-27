<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    {{-- 
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Mobile Responsive Viewport
    |--------------------------------------------------------------------------
    | Purpose:
    | - Makes the system fit properly on iPhone and Android screens.
    | - Prevents the dashboard content from being squeezed by the sidebar
    |   when the user is logged in on mobile.
    |--------------------------------------------------------------------------
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'NS & Beauty' }}</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    @yield('styles')
</head>

<body>

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Mobile Sidebar Overlay
|--------------------------------------------------------------------------
| Purpose:
| - Shows a dark transparent background when the sidebar is open on mobile.
| - Allows users to tap outside the sidebar to close it.
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
    | NS BEAUTY COMMENT:
    | Sidebar Area
    |--------------------------------------------------------------------------
    | Purpose:
    | - Sidebar stays fixed on desktop.
    | - Sidebar becomes hidden by default on phones.
    | - Sidebar opens only when the mobile menu button is clicked.
    |--------------------------------------------------------------------------
    --}}
    @include('partials.sidebar')

    <main class="main">

        {{-- 
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Mobile Top Bar
        |--------------------------------------------------------------------------
        | Purpose:
        | - Appears only on mobile screens.
        | - Gives users a menu button to open/fold the sidebar.
        | - Keeps admin, staff, and customer content full-width on phones.
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

        @yield('content')
    </main>
</div>

@yield('scripts')

<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Mobile Sidebar Toggle Script
|--------------------------------------------------------------------------
| Purpose:
| - Opens and closes the sidebar on iPhone and Android.
| - Does not affect desktop sidebar behavior.
| - Works for Admin, Staff, and Customer pages because it is placed in
|   the main layout file.
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