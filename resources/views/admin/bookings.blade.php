
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: ADMIN BOOKING MANAGEMENT
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Existing functions:
| - Booking statistics
| - Search and filters
| - Regular and follow-up bookings
| - Customer feedback
| - Cancellation records
| - Individual emergency rescheduling
| - Password-protected booking actions
| - Expandable full booking details
|
| New integrated functions:
| - Studio Closures button in Booking Management
| - Declare a studio-wide emergency closure
| - View active and historical closures
| - Lift existing studio closures
| - Admin password confirmation
| - Closure date range and reason
| - Closure statistics
|
| Design:
| - Muji-inspired brown and cream styling
| - Glass-effect cards
| - 24px large containers
| - 14px inner controls
| - Sidebar visible during popups
| - No unnecessary popup X buttons
|
| Important:
| Closure submission requires protected Laravel routes
| and server-side studio closure enforcement
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: MAIN DESIGN VARIABLES
|--------------------------------------------------------------------------
*/

.ns-bookings {
    --ns-main-radius: 24px;
    --ns-control-radius: 14px;
    --ns-brown: #6f4e37;
    --ns-dark: #2f2118;
    --ns-muted: #7d6d60;
    --ns-border: rgba(111,78,55,.13);
    --ns-glass: var(--glass,rgba(255,255,255,.58));
    --ns-gradient: linear-gradient(135deg,#8a7158,#5d3f2c);

    width: 100%;
    min-width: 0;
    color: var(--ns-dark);
}

.ns-bookings *,
.ns-bookings *::before,
.ns-bookings *::after {
    box-sizing: border-box;
}

.ns-bookings button,
.ns-bookings input,
.ns-bookings select,
.ns-bookings textarea {
    font: inherit;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: HEADER
|--------------------------------------------------------------------------
*/

.ns-bookings .page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 28px;
}

.ns-bookings .page-header h1 {
    margin: 0 0 8px;
    font-size: 34px;
    line-height: 1.2;
}

.ns-bookings .page-header p {
    margin: 0;
    color: var(--ns-muted);
    font-size: 14px;
    line-height: 1.7;
}

.ns-bookings .header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ns-bookings .closure-header-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 46px;
    padding: 12px 18px;
    background: var(--ns-gradient);
    color: white;
    border: 0;
    border-radius: var(--ns-control-radius);
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: ALERTS
|--------------------------------------------------------------------------
*/

.ns-bookings .alert {
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: var(--ns-control-radius);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.6;
}

.ns-bookings .alert-success {
    background: rgba(81,148,91,.12);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.ns-bookings .alert-error {
    background: rgba(184,77,77,.12);
    color: #a63e3e;
    border: 1px solid rgba(184,77,77,.22);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SHARED GLASS CARDS
|--------------------------------------------------------------------------
*/

.ns-bookings .stat-card,
.ns-bookings .filter-card,
.ns-bookings .booking-card,
.ns-bookings .empty-state {
    min-width: 0;
    background: var(--ns-glass);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid var(--border,rgba(111,78,55,.13));
    border-radius: var(--ns-main-radius);
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.65);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BOOKING STATISTICS
|--------------------------------------------------------------------------
*/

.ns-bookings .stats-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.ns-bookings .stat-card {
    padding: 20px;
}

.ns-bookings .stat-card h2 {
    margin: 0;
    color: var(--ns-brown);
    font-size: 28px;
    line-height: 1.2;
}

.ns-bookings .stat-card p {
    margin: 6px 0 0;
    color: var(--ns-muted);
    font-size: 12px;
    font-weight: 700;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: FILTERS
|--------------------------------------------------------------------------
*/

.ns-bookings .filter-card {
    padding: 22px;
    margin-bottom: 24px;
}

.ns-bookings .filter-row {
    display: grid;
    grid-template-columns: 1.3fr 1fr 1fr 1fr auto auto;
    gap: 14px;
    align-items: end;
}

.ns-bookings label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 800;
}

.ns-bookings input,
.ns-bookings select,
.ns-bookings textarea {
    width: 100%;
    min-width: 0;
    padding: 13px 14px;
    min-height: 46px;
    border-radius: var(--ns-control-radius);
    border: 1px solid var(--ns-border);
    background: rgba(255,255,255,.68);
    color: var(--ns-dark);
    outline: none;
    font-size: 13px;
}

.ns-bookings textarea {
    min-height: 100px;
    resize: vertical;
}

.ns-bookings input:focus,
.ns-bookings select:focus,
.ns-bookings textarea:focus {
    border-color: var(--ns-brown);
    box-shadow: 0 0 0 3px rgba(111,78,55,.10);
}

.ns-bookings input::placeholder,
.ns-bookings textarea::placeholder {
    color: #aaa096;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BUTTONS
|--------------------------------------------------------------------------
*/

.ns-bookings .filter-btn,
.ns-bookings .reset-btn,
.ns-bookings .action-btn,
.ns-bookings .modal-cancel,
.ns-bookings .modal-primary,
.ns-bookings .modal-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 18px;
    min-height: 44px;
    border-radius: var(--ns-control-radius);
    border: 1px solid transparent;
    font-size: 12px;
    font-weight: 800;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

.ns-bookings .filter-btn,
.ns-bookings .modal-primary {
    background: var(--ns-gradient);
    color: white;
}

.ns-bookings .reset-btn,
.ns-bookings .view-btn,
.ns-bookings .modal-back {
    background: rgba(255,255,255,.65);
    color: var(--ns-dark);
    border-color: var(--ns-border);
}

.ns-bookings .cancel-btn {
    background: rgba(184,77,77,.12);
    color: #a33f3f;
    border-color: rgba(184,77,77,.22);
}

.ns-bookings .reschedule-btn {
    background: rgba(138,113,88,.14);
    color: var(--ns-brown);
    border-color: rgba(138,113,88,.25);
}

.ns-bookings .modal-cancel {
    background: #b84d4d;
    color: white;
}

.ns-bookings button:disabled {
    opacity: .48;
    cursor: not-allowed;
}

.ns-bookings button:focus-visible,
.ns-bookings a:focus-visible {
    outline: 3px solid rgba(111,78,55,.35);
    outline-offset: 3px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: BOOKING CARDS
|--------------------------------------------------------------------------
*/

.ns-bookings .booking-list {
    display: grid;
    gap: 20px;
}

.ns-bookings .booking-card {
    padding: 24px;
}

.ns-bookings .booking-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 18px;
}

.ns-bookings .booking-top h3 {
    font-size: 18px;
    margin: 0 0 5px;
    overflow-wrap: anywhere;
}

.ns-bookings .booking-top p {
    margin: 3px 0;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.6;
}

.ns-bookings .badge-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
}

.ns-bookings .status,
.ns-bookings .type-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 11px;
    border-radius: var(--ns-control-radius);
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.ns-bookings .type-badge.regular {
    color: var(--ns-dark);
    background: rgba(255,255,255,.55);
    border: 1px solid var(--ns-border);
}

.ns-bookings .type-badge.followup {
    background: rgba(138,113,88,.18);
    color: var(--ns-brown);
}

.ns-bookings .status.pending {
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.ns-bookings .status.approved {
    background: rgba(82,139,255,.15);
    color: #2f5fb8;
}

.ns-bookings .status.ongoing {
    background: rgba(138,113,88,.18);
    color: var(--ns-brown);
}

.ns-bookings .status.finished {
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
}

.ns-bookings .status.cancelled,
.ns-bookings .status.no-show {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

.ns-bookings .status.rescheduling-required,
.ns-bookings .status.rescheduling_required {
    background: rgba(202,136,55,.16);
    color: #956019;
    border: 1px solid rgba(202,136,55,.25);
}

.ns-bookings .details-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 16px;
    margin-bottom: 18px;
}

.ns-bookings .detail-box {
    padding: 16px;
    min-width: 0;
    background: rgba(255,255,255,.46);
    border: 1px solid rgba(255,255,255,.65);
    border-radius: var(--ns-control-radius);
}

.ns-bookings .detail-box small {
    font-size: 10px;
    font-weight: 800;
    color: var(--ns-muted);
}

.ns-bookings .detail-box p {
    margin: 6px 0 0;
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.ns-bookings .service-list,
.ns-bookings .feedback-info,
.ns-bookings .cancel-info,
.ns-bookings .followup-info,
.ns-bookings .reschedule-info,
.ns-bookings .full-details {
    padding: 16px;
    margin-bottom: 18px;
    border-radius: var(--ns-control-radius);
    background: rgba(255,255,255,.42);
    border: 1px solid rgba(255,255,255,.65);
}

.ns-bookings .feedback-info {
    background: rgba(81,148,91,.08);
    border-color: rgba(81,148,91,.22);
}

.ns-bookings .followup-info {
    background: rgba(138,113,88,.09);
    border-color: rgba(138,113,88,.20);
}

.ns-bookings .reschedule-info {
    background: rgba(202,136,55,.09);
    border-color: rgba(202,136,55,.23);
}

.ns-bookings .service-list small,
.ns-bookings .feedback-info small,
.ns-bookings .cancel-info small,
.ns-bookings .followup-info small,
.ns-bookings .reschedule-info small,
.ns-bookings .full-details small {
    display: block;
    margin-bottom: 9px;
    color: var(--ns-muted);
    font-size: 10px;
    font-weight: 800;
}

.ns-bookings .service-list span {
    display: inline-flex;
    margin: 4px 6px 4px 0;
    padding: 8px 11px;
    border-radius: var(--ns-control-radius);
    background: rgba(255,255,255,.65);
    border: 1px solid var(--ns-border);
    font-size: 12px;
    font-weight: 700;
}

.ns-bookings .feedback-info p,
.ns-bookings .cancel-info p,
.ns-bookings .followup-info p,
.ns-bookings .reschedule-info p,
.ns-bookings .full-details p {
    margin: 0 0 8px;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.ns-bookings .feedback-info strong,
.ns-bookings .cancel-info strong,
.ns-bookings .followup-info strong,
.ns-bookings .reschedule-info strong,
.ns-bookings .full-details strong {
    color: var(--ns-dark);
}

.ns-bookings .actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.ns-bookings .locked-note {
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.6;
}

.ns-bookings .pagination {
    margin-top: 24px;
}

.ns-bookings .empty-state {
    padding: 50px 20px;
    text-align: center;
    color: var(--ns-muted);
}

.ns-bookings .empty-state h3 {
    margin: 0 0 8px;
    color: var(--ns-dark);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SHARED MODAL DESIGN
|--------------------------------------------------------------------------
*/

.ns-bookings .modal-overlay {
    position: fixed;
    inset: 0 0 0 280px;
    z-index: 999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
    background: rgba(47,33,24,.35);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.ns-bookings .modal-overlay.show {
    display: flex;
}

.ns-bookings .modal {
    width: 100%;
    max-width: 560px;
    max-height: calc(100dvh - 40px);
    overflow-y: auto;
    padding: 28px;
    background: rgba(255,255,255,.96);
    border: 1px solid rgba(255,255,255,.9);
    border-radius: var(--ns-main-radius);
    box-shadow:
        0 25px 80px rgba(47,33,24,.20),
        inset 0 1px 0 rgba(255,255,255,.8);
}

.ns-bookings .modal.modal-wide {
    max-width: 800px;
}

.ns-bookings .modal h3 {
    margin: 0 0 10px;
    font-size: 22px;
}

.ns-bookings .modal h4 {
    margin: 0 0 9px;
    font-size: 16px;
}

.ns-bookings .modal p {
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.7;
    margin: 0 0 18px;
}

.ns-bookings .modal label {
    margin-top: 14px;
}

.ns-bookings .modal-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 22px;
}

.ns-bookings .warning-box,
.ns-bookings .info-box {
    padding: 16px;
    margin: 16px 0;
    border-radius: var(--ns-control-radius);
    border: 1px solid rgba(184,77,77,.20);
    background: rgba(184,77,77,.08);
}

.ns-bookings .info-box {
    border-color: rgba(138,113,88,.20);
    background: rgba(138,113,88,.08);
}

.ns-bookings .warning-box strong,
.ns-bookings .info-box strong {
    display: block;
    margin-bottom: 6px;
    color: var(--ns-dark);
}

.ns-bookings .warning-box span,
.ns-bookings .info-box span {
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.7;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: STUDIO CLOSURE MANAGEMENT
|--------------------------------------------------------------------------
*/

.ns-bookings .closure-summary-grid {
    display: grid;
    grid-template-columns: repeat(3,minmax(0,1fr));
    gap: 12px;
    margin: 20px 0;
}

.ns-bookings .closure-summary-card {
    padding: 18px;
    background: rgba(138,113,88,.08);
    border: 1px solid rgba(138,113,88,.15);
    border-radius: var(--ns-control-radius);
}

.ns-bookings .closure-summary-card strong {
    display: block;
    font-size: 24px;
    color: var(--ns-brown);
}

.ns-bookings .closure-summary-card small {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    color: var(--ns-muted);
    font-weight: 700;
}

.ns-bookings .closure-section {
    padding: 20px;
    margin-top: 18px;
    background: rgba(255,255,255,.42);
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-control-radius);
}

.ns-bookings .closure-form-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 14px;
}

.ns-bookings .closure-form-grid > div {
    min-width: 0;
}

.ns-bookings .closure-records {
    display: grid;
    gap: 12px;
    margin-top: 15px;
}

.ns-bookings .closure-record {
    padding: 17px;
    background: rgba(255,255,255,.75);
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-control-radius);
}

.ns-bookings .closure-record-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
}

.ns-bookings .closure-record h4 {
    margin: 0 0 5px;
    font-size: 14px;
    overflow-wrap: anywhere;
}

.ns-bookings .closure-record p {
    margin: 4px 0;
    font-size: 12px;
    overflow-wrap: anywhere;
}

.ns-bookings .closure-status {
    display: inline-flex;
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}

.ns-bookings .closure-status.active {
    background: rgba(184,77,77,.12);
    color: #a33f3f;
}

.ns-bookings .closure-status.lifted {
    background: rgba(81,148,91,.12);
    color: #2f7d3c;
}

.ns-bookings .closure-record-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 14px;
}

.ns-bookings .closure-empty {
    padding: 22px;
    text-align: center;
    background: rgba(255,255,255,.54);
    border-radius: var(--ns-control-radius);
    color: var(--ns-muted);
    font-size: 13px;
}

.ns-bookings .closure-route-warning {
    padding: 13px 15px;
    margin-top: 12px;
    border-radius: var(--ns-control-radius);
    background: rgba(202,136,55,.12);
    border: 1px solid rgba(202,136,55,.22);
    color: #956019;
    font-size: 12px;
    line-height: 1.6;
}

.ns-bookings .closure-pagination {
    margin-top: 14px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: SIDEBAR VISIBILITY
|--------------------------------------------------------------------------
*/

.sidebar,
.admin-sidebar,
.app-sidebar,
aside.sidebar,
#sidebar {
    z-index: 10000 !important;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: RESPONSIVE LAYOUT
|--------------------------------------------------------------------------
*/

@media (max-width:1100px) {
    .ns-bookings .stats-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }

    .ns-bookings .filter-row,
    .ns-bookings .details-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }
}

@media (max-width:900px) {
    .ns-bookings .modal-overlay {
        left: 0;
    }

    .ns-bookings .page-header {
        flex-direction: column;
    }
}

@media (max-width:700px) {
    .ns-bookings .page-header h1 {
        font-size: 28px;
    }

    .ns-bookings .stats-grid,
    .ns-bookings .filter-row,
    .ns-bookings .details-grid,
    .ns-bookings .closure-summary-grid,
    .ns-bookings .closure-form-grid {
        grid-template-columns: minmax(0,1fr);
    }

    .ns-bookings .booking-top {
        flex-direction: column;
    }

    .ns-bookings .badge-row {
        justify-content: flex-start;
    }

    .ns-bookings .booking-card,
    .ns-bookings .filter-card {
        padding: 18px;
    }

    .ns-bookings .modal {
        padding: 22px 18px;
        max-height: calc(100dvh - 24px);
    }

    .ns-bookings .modal-overlay {
        padding: 12px;
    }

    .ns-bookings .actions > *,
    .ns-bookings .modal-actions > *,
    .ns-bookings .filter-btn,
    .ns-bookings .reset-btn,
    .ns-bookings .closure-header-btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Closure Route Availability
    |--------------------------------------------------------------------------
    | These checks prevent Blade route errors before routes are added
    |--------------------------------------------------------------------------
    */

    $closureDeclareRouteReady =
        \Illuminate\Support\Facades\Route::has(
            'admin.bookings.closures.store'
        );

    $closureLiftRouteReady =
        \Illuminate\Support\Facades\Route::has(
            'admin.bookings.closures.lift'
        );

    $closureRecords = $studioClosures ?? null;

    $studioToday = $studioToday
        ?? now('Asia/Kuala_Lumpur')->toDateString();
@endphp

<div class="ns-bookings">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: PAGE HEADER
    |--------------------------------------------------------------------------
    --}}

    <div class="page-header">

        <div>
            <h1>Booking Management</h1>

            <p>
                Manage appointments, staff assignments, feedback,
                cancellations, emergency rescheduling and studio closures
            </p>
        </div>

        <div class="header-actions">

            <button
                type="button"
                class="closure-header-btn"
                onclick="openBookingModal('studioClosuresModal')"
            >
                <span aria-hidden="true">▣</span>
                Studio Closures

                @if (($closureStats['currently_closed'] ?? false))
                    <span aria-label="Studio currently closed">
                        · Closed Today
                    </span>
                @endif

            </button>

        </div>

    </div>

    {{-- NS BEAUTY COMMENT: SUCCESS ALERT --}}

    @if (session('success'))
        <div class="alert alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    {{-- NS BEAUTY COMMENT: ERROR ALERT --}}

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: BOOKING STATISTICS
    |--------------------------------------------------------------------------
    --}}

    <div class="stats-grid">

        <div class="stat-card">
            <h2>{{ $stats['total'] }}</h2>
            <p>Total Bookings</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['regular'] }}</h2>
            <p>Regular Bookings</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['followup'] }}</h2>
            <p>Backjob / Follow-up</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['pending'] }}</h2>
            <p>Pending</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['ongoing'] }}</h2>
            <p>Ongoing</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['finished'] }}</h2>
            <p>Finished</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['cancelled'] }}</h2>
            <p>Cancelled</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['no_show'] }}</h2>
            <p>No Show</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['rescheduling_required'] ?? 0 }}</h2>
            <p>Rescheduling Required</p>
        </div>

    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: BOOKING FILTERS
    |--------------------------------------------------------------------------
    --}}

    <section class="filter-card">

        <form method="GET" action="{{ route('admin.bookings') }}">

            <div class="filter-row">

                <div>
                    <label for="bookingSearch">Search</label>

                    <input
                        type="text"
                        id="bookingSearch"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search booking ID, customer, staff or service"
                    >
                </div>

                <div>
                    <label for="bookingTypeFilter">Booking Type</label>

                    <select
                        id="bookingTypeFilter"
                        name="booking_type"
                    >
                        <option
                            value="all"
                            @selected(($filters['booking_type'] ?? 'all') === 'all')
                        >
                            All
                        </option>

                        <option
                            value="regular"
                            @selected(($filters['booking_type'] ?? '') === 'regular')
                        >
                            Regular
                        </option>

                        <option
                            value="followup"
                            @selected(($filters['booking_type'] ?? '') === 'followup')
                        >
                            Backjob / Follow-up
                        </option>
                    </select>
                </div>

                <div>
                    <label for="bookingStatusFilter">Status</label>

                    <select
                        id="bookingStatusFilter"
                        name="status"
                    >
                        <option value="all">All</option>

                        @foreach ([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'ongoing' => 'Ongoing',
                            'finished' => 'Finished',
                            'cancelled' => 'Cancelled',
                            'no-show' => 'No Show',
                            'rescheduling_required' => 'Rescheduling Required'
                        ] as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(
                                    ($filters['status'] ?? '') === $value
                                )
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>
                </div>

                <div>
                    <label for="bookingDateFilter">Date</label>

                    <input
                        type="date"
                        id="bookingDateFilter"
                        name="date"
                        value="{{ $filters['date'] ?? '' }}"
                    >
                </div>

                <button
                    type="submit"
                    class="filter-btn"
                >
                    Filter
                </button>

                <a
                    href="{{ route('admin.bookings') }}"
                    class="reset-btn"
                >
                    Reset
                </a>

            </div>

        </form>

    </section>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: EXISTING BOOKING RECORDS
    |--------------------------------------------------------------------------
    --}}

    <div class="booking-list">

        @forelse ($bookings as $booking)

            @php
                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Booking Display Values
                |--------------------------------------------------------------------------
                */

                $statusKey = strtolower(
                    str_replace(
                        [' ', '_'],
                        '-',
                        (string) $booking->status
                    )
                );

                $statusLabel = ucwords(
                    str_replace('-', ' ', $statusKey)
                );

                $bookingType =
                    $booking->booking_type === 'followup'
                        ? 'followup'
                        : 'regular';

                $bookingTypeLabel =
                    $bookingType === 'followup'
                        ? 'Backjob / Follow-up'
                        : 'Regular Booking';

                $mainService =
                    $booking->services->first()?->service_name
                    ?? 'Beauty Service';

                $appointmentDate = $booking->appointment_date
                    ? \Carbon\Carbon::parse(
                        $booking->appointment_date
                    )->format('F d, Y')
                    : 'Not set';

                $startTime = $booking->start_time
                    ? \Carbon\Carbon::parse(
                        $booking->start_time
                    )->format('h:i A')
                    : 'Not set';

                $endTime = $booking->end_time
                    ? \Carbon\Carbon::parse(
                        $booking->end_time
                    )->format('h:i A')
                    : null;

                $durationMinutes = 0;

                if (
                    $booking->start_time &&
                    $booking->end_time
                ) {
                    $durationMinutes = (int) \Carbon\Carbon::parse(
                        $booking->start_time
                    )->diffInMinutes(
                        \Carbon\Carbon::parse(
                            $booking->end_time
                        )
                    );
                }

                if ($durationMinutes <= 0) {
                    $durationMinutes = $booking->services->sum(
                        fn ($service) => (int) (
                            $service->pivot->duration
                            ?? $service->duration
                            ?? 0
                        )
                    );
                }

                $createdAt = $booking->created_at
                    ? \Carbon\Carbon::parse(
                        $booking->created_at
                    )->format('F d, Y h:i A')
                    : 'Not recorded';

                $updatedAt = $booking->status_updated_at
                    ? \Carbon\Carbon::parse(
                        $booking->status_updated_at
                    )->format('F d, Y h:i A')
                    : 'Not updated';

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Booking Action Eligibility
                |--------------------------------------------------------------------------
                */

                $canCancel = !in_array(
                    $statusKey,
                    ['finished', 'cancelled', 'no-show'],
                    true
                );

                $canRequestRescheduling = in_array(
                    $statusKey,
                    ['pending', 'approved'],
                    true
                );

                if (
                    $canRequestRescheduling &&
                    $booking->appointment_date &&
                    $booking->start_time
                ) {
                    $dateText = \Carbon\Carbon::parse(
                        $booking->appointment_date
                    )->format('Y-m-d');

                    $timeText = substr(
                        (string) $booking->start_time,
                        0,
                        5
                    );

                    $bookingStartsAt =
                        \Carbon\Carbon::createFromFormat(
                            '!Y-m-d H:i',
                            $dateText . ' ' . $timeText,
                            'Asia/Kuala_Lumpur'
                        );

                    $canRequestRescheduling =
                        $bookingStartsAt->isFuture();
                } else {
                    $canRequestRescheduling = false;
                }

                $reschedulingRouteReady =
                    \Illuminate\Support\Facades\Route::has(
                        'admin.bookings.reschedule'
                    );

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Follow-up Relationship
                |--------------------------------------------------------------------------
                */

                $original = $booking->followUpAppointment;

                $originalServices = $original
                    ? $original->services
                        ->pluck('service_name')
                        ->join(', ')
                    : null;

                $originalDuration = 0;

                if (
                    $original &&
                    $original->start_time &&
                    $original->end_time
                ) {
                    $originalDuration = (int) \Carbon\Carbon::parse(
                        $original->start_time
                    )->diffInMinutes(
                        \Carbon\Carbon::parse(
                            $original->end_time
                        )
                    );
                }

                if ($original && $originalDuration <= 0) {
                    $originalDuration = $original->services->sum(
                        fn ($service) => (int) (
                            $service->pivot->duration
                            ?? $service->duration
                            ?? 0
                        )
                    );
                }
            @endphp

            <section class="booking-card">

                {{-- NS BEAUTY COMMENT: Booking Header --}}

                <div class="booking-top">

                    <div>
                        <h3>{{ $mainService }}</h3>

                        <p>
                            Booking ID:
                            #BK-{{ str_pad(
                                $booking->id,
                                4,
                                '0',
                                STR_PAD_LEFT
                            ) }}
                        </p>

                        <p>
                            Created: {{ $createdAt }}
                        </p>
                    </div>

                    <div class="badge-row">

                        <span class="type-badge {{ $bookingType }}">
                            {{ $bookingTypeLabel }}
                        </span>

                        <span class="status {{ $statusKey }}">
                            {{ $statusLabel }}
                        </span>

                    </div>

                </div>

                {{-- NS BEAUTY COMMENT: Appointment Information --}}

                <div class="details-grid">

                    <div class="detail-box">
                        <small>CUSTOMER</small>
                        <p>
                            {{ $booking->customer?->full_name
                                ?? 'Customer record missing' }}
                        </p>
                    </div>

                    <div class="detail-box">
                        <small>CUSTOMER EMAIL</small>
                        <p>
                            {{ $booking->customer?->email
                                ?? 'Not recorded' }}
                        </p>
                    </div>

                    <div class="detail-box">
                        <small>BEAUTICIAN</small>
                        <p>
                            {{ $booking->staff?->full_name
                                ?? 'Not assigned' }}
                        </p>
                    </div>

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
                        <small>DURATION</small>
                        <p>{{ $durationMinutes }} minutes</p>
                    </div>

                    <div class="detail-box">
                        <small>TOTAL</small>
                        <p>
                            RM {{ number_format(
                                (float) $booking->total_price,
                                2
                            ) }}
                        </p>
                    </div>

                    <div class="detail-box">
                        <small>UPDATED BY</small>
                        <p>
                            {{ $booking->statusUpdatedBy?->name
                                ?? 'System / Not updated' }}
                        </p>
                    </div>

                </div>

                {{-- NS BEAUTY COMMENT: Selected Services --}}

                <div class="service-list">

                    <small>SELECTED SERVICES</small>

                    @forelse ($booking->services as $service)

                        <span>
                            {{ $service->service_name }}

                            · {{ $service->pivot->duration
                                ?? $service->duration
                                ?? 0 }}m

                            · RM {{ number_format(
                                (float) (
                                    $service->pivot->price
                                    ?? $service->price
                                    ?? 0
                                ),
                                2
                            ) }}
                        </span>

                    @empty

                        <span>No services recorded</span>

                    @endforelse

                </div>

                {{-- NS BEAUTY COMMENT: Customer Feedback --}}

                @if ($booking->feedback)

                    <div class="feedback-info">

                        <small>CUSTOMER FEEDBACK</small>

                        @php
                            $rating = max(
                                0,
                                min(
                                    5,
                                    (int) $booking->feedback->rating
                                )
                            );
                        @endphp

                        <p>
                            <strong>Rating:</strong>

                            {{ str_repeat('★', $rating) }}
                            {{ str_repeat('☆', 5 - $rating) }}

                            ({{ $rating }}/5)
                        </p>

                        <p>
                            <strong>Comment:</strong>
                            {{ $booking->feedback->comment
                                ?: 'No comment provided' }}
                        </p>

                        <p>
                            <strong>Submitted By:</strong>

                            {{ $booking->feedback->customer?->full_name
                                ?? $booking->customer?->full_name
                                ?? 'Unknown customer' }}
                        </p>

                        <p>
                            <strong>Submitted At:</strong>

                            {{ $booking->feedback->created_at
                                ? \Carbon\Carbon::parse(
                                    $booking->feedback->created_at
                                )->format('F d, Y h:i A')
                                : 'Not recorded' }}
                        </p>

                    </div>

                @endif

                {{-- NS BEAUTY COMMENT: Follow-up Booking Information --}}

                @if ($bookingType === 'followup')

                    <div class="followup-info">

                        <small>BACKJOB / FOLLOW-UP CONNECTION</small>

                        @if ($original)

                            <p>
                                <strong>Original Booking:</strong>
                                #BK-{{ str_pad(
                                    $original->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                            </p>

                            <p>
                                <strong>Original Services:</strong>
                                {{ $originalServices
                                    ?: 'No services recorded' }}
                            </p>

                            <p>
                                <strong>Original Staff:</strong>
                                {{ $original->staff?->full_name
                                    ?? 'Not assigned' }}
                            </p>

                            <p>
                                <strong>Original Duration:</strong>
                                {{ $originalDuration }} minutes
                            </p>

                            <p>
                                <strong>Follow-up Reason:</strong>
                                {{ $booking->follow_up_reason
                                    ?: 'No reason provided' }}
                            </p>

                        @else

                            <p>
                                No linked original booking record was found
                            </p>

                        @endif

                    </div>

                @endif

                {{-- NS BEAUTY COMMENT: Cancellation Record --}}

                @if ($statusKey === 'cancelled')

                    <div class="cancel-info">

                        <small>CANCELLATION RECORD</small>

                        <p>
                            <strong>Reason:</strong>
                            {{ $booking->cancellation_reason
                                ?: 'No reason recorded' }}
                        </p>

                        <p>
                            <strong>Cancelled By:</strong>
                            {{ $booking->cancelledBy?->name
                                ?? 'Unknown user' }}
                        </p>

                        <p>
                            <strong>Cancelled At:</strong>

                            {{ $booking->cancelled_at
                                ? \Carbon\Carbon::parse(
                                    $booking->cancelled_at
                                )->format('F d, Y h:i A')
                                : 'Not recorded' }}
                        </p>

                    </div>

                @endif

                {{-- NS BEAUTY COMMENT: Emergency Rescheduling Details --}}

                @if ($statusKey === 'rescheduling-required')

                    <div class="reschedule-info">

                        <small>RESCHEDULING REQUIRED</small>

                        <p>
                            <strong>Reason:</strong>
                            {{ $booking->rescheduling_reason
                                ?: 'Emergency schedule change' }}
                        </p>

                        <p>
                            <strong>Original Date:</strong>

                            {{ $booking->original_appointment_date
                                ? \Carbon\Carbon::parse(
                                    $booking->original_appointment_date
                                )->format('F d, Y')
                                : $appointmentDate }}
                        </p>

                        <p>
                            <strong>Original Time:</strong>

                            {{ $booking->original_start_time
                                ? \Carbon\Carbon::parse(
                                    $booking->original_start_time
                                )->format('h:i A')
                                : $startTime }}
                        </p>

                        <p>
                            <strong>Requested At:</strong>

                            {{ $booking->rescheduling_requested_at
                                ? \Carbon\Carbon::parse(
                                    $booking->rescheduling_requested_at
                                )->format('F d, Y h:i A')
                                : 'Not recorded' }}
                        </p>

                        <p>
                            The customer can select a replacement
                            schedule in their Booking Details page
                        </p>

                    </div>

                @endif

                {{-- NS BEAUTY COMMENT: Existing Booking Actions --}}

                <div class="actions">

                    <button
                        type="button"
                        class="action-btn view-btn"
                        onclick="toggleBookingDetails(
                            'bookingDetails{{ $booking->id }}'
                        )"
                    >
                        View Full Details
                    </button>

                    @if ($canRequestRescheduling)

                        @if ($reschedulingRouteReady)

                            <button
                                type="button"
                                class="action-btn reschedule-btn"
                                onclick="openBookingModal(
                                    'rescheduleModal{{ $booking->id }}'
                                )"
                            >
                                Request Rescheduling
                            </button>

                        @else

                            <button
                                type="button"
                                class="action-btn reschedule-btn"
                                disabled
                            >
                                Request Rescheduling
                            </button>

                        @endif

                    @endif

                    @if ($canCancel)

                        <button
                            type="button"
                            class="action-btn cancel-btn"
                            onclick="openBookingModal(
                                'cancelModal{{ $booking->id }}'
                            )"
                        >
                            Cancel Booking
                        </button>

                    @else

                        <span class="locked-note">
                            This booking is
                            {{ strtolower($statusLabel) }}
                            and cannot be cancelled
                        </span>

                    @endif

                </div>

                {{-- NS BEAUTY COMMENT: Expandable Booking Details --}}

                <div
                    id="bookingDetails{{ $booking->id }}"
                    class="full-details"
                    style="display:none;margin-top:18px"
                >

                    <small>FULL BOOKING DETAILS</small>

                    <p>
                        <strong>Customer Phone:</strong>

                        {{ $booking->customer?->phone_number
                            ?? 'Not recorded' }}
                    </p>

                    <p>
                        <strong>Customer Address:</strong>

                        {{ $booking->customer?->address
                            ?? 'Not recorded' }}
                    </p>

                    <p>
                        <strong>Booking Type:</strong>
                        {{ $bookingTypeLabel }}
                    </p>

                    <p>
                        <strong>Follow-up From:</strong>

                        {{ $booking->follow_up_from
                            ?? 'Not applicable' }}
                    </p>

                    <p>
                        <strong>Follow-up Reason:</strong>

                        {{ $booking->follow_up_reason
                            ?? 'Not applicable' }}
                    </p>

                    <p>
                        <strong>Feedback Rating:</strong>

                        {{ $booking->feedback
                            ? $booking->feedback->rating . '/5'
                            : 'No feedback submitted' }}
                    </p>

                    <p>
                        <strong>Feedback Comment:</strong>

                        {{ $booking->feedback?->comment
                            ?: 'No feedback submitted' }}
                    </p>

                    <p>
                        <strong>Last Updated:</strong>
                        {{ $updatedAt }}
                    </p>

                </div>

            </section>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: CANCELLATION POPUP
            |--------------------------------------------------------------------------
            --}}

            @if ($canCancel)

                <div
                    class="modal-overlay"
                    id="cancelModal{{ $booking->id }}"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Cancel Booking"
                >

                    <div class="modal">

                        <h3>Cancel Booking</h3>

                        <p>
                            Cancel booking
                            <strong>
                                #BK-{{ str_pad(
                                    $booking->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                            </strong>
                        </p>

                        <div class="warning-box">

                            <strong>Confirmation Required</strong>

                            <span>
                                This action will cancel the appointment
                                and record the cancellation in Audit Logs
                            </span>

                        </div>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.bookings.cancel',
                                $booking->id
                            ) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <label
                                for="cancelPassword{{ $booking->id }}"
                            >
                                Admin Password
                            </label>

                            <input
                                type="password"
                                id="cancelPassword{{ $booking->id }}"
                                name="admin_password"
                                autocomplete="current-password"
                                placeholder="Enter your admin password"
                                required
                            >

                            <label
                                for="cancelReason{{ $booking->id }}"
                            >
                                Cancellation Reason
                            </label>

                            <textarea
                                id="cancelReason{{ $booking->id }}"
                                name="cancellation_reason"
                                maxlength="500"
                                placeholder="Enter the cancellation reason"
                                required
                            ></textarea>

                            <div class="modal-actions">

                                <button
                                    type="submit"
                                    class="modal-cancel"
                                >
                                    Confirm Cancellation
                                </button>

                                <button
                                    type="button"
                                    class="modal-back"
                                    onclick="closeBookingModal(
                                        'cancelModal{{ $booking->id }}'
                                    )"
                                >
                                    Back
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            @endif

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: INDIVIDUAL RESCHEDULING POPUP
            |--------------------------------------------------------------------------
            --}}

            @if ($canRequestRescheduling && $reschedulingRouteReady)

                <div
                    class="modal-overlay"
                    id="rescheduleModal{{ $booking->id }}"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Request Rescheduling"
                >

                    <div class="modal">

                        <h3>Request Rescheduling</h3>

                        <p>
                            Booking
                            <strong>
                                #BK-{{ str_pad(
                                    $booking->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                            </strong>
                        </p>

                        <div class="info-box">

                            <strong>Emergency Schedule Change</strong>

                            <span>
                                The original appointment information
                                will be preserved

                                The customer will be notified to
                                select a new schedule through
                                their Booking Details page
                            </span>

                        </div>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.bookings.reschedule',
                                $booking->id
                            ) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <label
                                for="reschedulePassword{{ $booking->id }}"
                            >
                                Admin Password
                            </label>

                            <input
                                type="password"
                                id="reschedulePassword{{ $booking->id }}"
                                name="admin_password"
                                autocomplete="current-password"
                                placeholder="Enter your admin password"
                                required
                            >

                            <label
                                for="rescheduleReason{{ $booking->id }}"
                            >
                                Rescheduling Reason
                            </label>

                            <textarea
                                id="rescheduleReason{{ $booking->id }}"
                                name="rescheduling_reason"
                                maxlength="1000"
                                placeholder="Describe the emergency or operational issue"
                                required
                            ></textarea>

                            <div class="modal-actions">

                                <button
                                    type="submit"
                                    class="modal-primary"
                                >
                                    Request Rescheduling
                                </button>

                                <button
                                    type="button"
                                    class="modal-back"
                                    onclick="closeBookingModal(
                                        'rescheduleModal{{ $booking->id }}'
                                    )"
                                >
                                    Back
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            @endif

        @empty

            <div class="empty-state">

                <h3>No Bookings Found</h3>

                <p>
                    No bookings match the selected filters
                </p>

            </div>

        @endforelse

    </div>

    {{-- NS BEAUTY COMMENT: Booking Pagination --}}

    <div class="pagination">
        {{ $bookings->links() }}
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: STUDIO CLOSURES MANAGEMENT POPUP
    |--------------------------------------------------------------------------
    | Located inside Admin Booking Management
    | No separate sidebar navigation or module is required
    |--------------------------------------------------------------------------
    --}}

    <div
        class="modal-overlay"
        id="studioClosuresModal"
        role="dialog"
        aria-modal="true"
        aria-label="Studio Closures Management"
    >

        <div class="modal modal-wide">

            <h3>Studio Closure Management</h3>

            <p>
                Declare temporary studio closures, review closure history,
                and reopen the studio when operations can resume
            </p>

            {{-- NS BEAUTY COMMENT: Closure Statistics --}}

            <div class="closure-summary-grid">

                <div class="closure-summary-card">
                    <strong>
                        {{ $closureStats['active'] ?? 0 }}
                    </strong>
                    <small>Active Closures</small>
                </div>

                <div class="closure-summary-card">
                    <strong>
                        {{ $closureStats['lifted'] ?? 0 }}
                    </strong>
                    <small>Lifted Closures</small>
                </div>

                <div class="closure-summary-card">
                    <strong>
                        {{ $closureStats['total'] ?? 0 }}
                    </strong>
                    <small>Total Closure Records</small>
                </div>

            </div>

            {{-- NS BEAUTY COMMENT: Currently Closed Indicator --}}

            @if ($closureStats['currently_closed'] ?? false)

                <div class="warning-box">

                    <strong>Studio Closed Today</strong>

                    <span>
                        An active studio closure covers
                        {{ \Carbon\Carbon::parse(
                            $studioToday
                        )->format('F d, Y') }}
                    </span>

                </div>

            @else

                <div class="info-box">

                    <strong>No Active Closure for Today</strong>

                    <span>
                        No active studio-wide closure is recorded
                        for the current Malaysia date

                        Future closure dates may still be scheduled
                    </span>

                </div>

            @endif

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: DECLARE STUDIO CLOSURE
            |--------------------------------------------------------------------------
            --}}

            <div class="closure-section">

                <h4>Declare Studio Closure</h4>

                <p>
                    Use this form when the studio must close due to
                    an emergency or unexpected operational disruption
                </p>

                <div class="info-box">

                    <strong>What Happens After Declaration</strong>

                    <span>
                        Eligible future appointments within the closure
                        date range will be marked for rescheduling

                        Original appointment information and
                        transaction records will be preserved

                        Customers will be notified to select
                        replacement schedules

                        New bookings will be blocked on closure dates
                        once the booking restrictions are connected
                    </span>

                </div>

                @if (!$closureDeclareRouteReady)

                    <div class="closure-route-warning">
                        The studio closure submission route is not
                        registered yet

                        The form is displayed for integration,
                        but submission is disabled until the backend
                        route is connected
                    </div>

                @endif

                <form
                    method="POST"
                    action="{{ $closureDeclareRouteReady
                        ? route('admin.bookings.closures.store')
                        : '#' }}"
                    id="studioClosureCreateForm"
                >
                    @csrf

                    <label for="closureTitle">
                        Closure Title
                    </label>

                    <input
                        type="text"
                        id="closureTitle"
                        name="title"
                        maxlength="150"
                        value="{{ old('title') }}"
                        placeholder="Example: Emergency Flood Closure"
                        required
                    >

                    <div class="closure-form-grid">

                        <div>
                            <label for="closureStartDate">
                                Start Date
                            </label>

                            <input
                                type="date"
                                id="closureStartDate"
                                name="start_date"
                                min="{{ $studioToday }}"
                                value="{{ old('start_date') }}"
                                required
                            >
                        </div>

                        <div>
                            <label for="closureEndDate">
                                End Date
                            </label>

                            <input
                                type="date"
                                id="closureEndDate"
                                name="end_date"
                                min="{{ $studioToday }}"
                                value="{{ old('end_date') }}"
                                required
                            >
                        </div>

                    </div>

                    <label for="closureReason">
                        Closure Reason
                    </label>

                    <textarea
                        id="closureReason"
                        name="reason"
                        maxlength="2000"
                        placeholder="Describe why the studio must close"
                        required
                    >{{ old('reason') }}</textarea>

                    <label for="closureAdminNotes">
                        Internal Admin Notes
                        <span style="font-weight:400">
                            (Optional)
                        </span>
                    </label>

                    <textarea
                        id="closureAdminNotes"
                        name="admin_notes"
                        maxlength="2000"
                        placeholder="Additional internal information"
                    >{{ old('admin_notes') }}</textarea>

                    <label for="closureAdminPassword">
                        Admin Password
                    </label>

                    <input
                        type="password"
                        id="closureAdminPassword"
                        name="admin_password"
                        autocomplete="current-password"
                        placeholder="Enter your administrator password"
                        required
                    >

                    <div class="modal-actions">

                        <button
                            type="submit"
                            class="modal-primary"
                            @disabled(!$closureDeclareRouteReady)
                        >
                            Declare Studio Closure
                        </button>

                    </div>

                </form>

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: STUDIO CLOSURE HISTORY
            |--------------------------------------------------------------------------
            --}}

            <div class="closure-section">

                <h4>Studio Closure History</h4>

                <p>
                    Review declared closures and lift active
                    closures when necessary
                </p>

                <div class="closure-records">

                    @if (
                        $closureRecords &&
                        $closureRecords->count() > 0
                    )

                        @foreach ($closureRecords as $closure)

                            @php
                                $closureIsActive =
                                    $closure->status === 'active';

                                $closureStart =
                                    $closure->start_date
                                        ? $closure->start_date->format(
                                            'F d, Y'
                                        )
                                        : 'Not set';

                                $closureEnd =
                                    $closure->end_date
                                        ? $closure->end_date->format(
                                            'F d, Y'
                                        )
                                        : 'Not set';
                            @endphp

                            <div class="closure-record">

                                <div class="closure-record-top">

                                    <div>
                                        <h4>
                                            {{ $closure->title }}
                                        </h4>

                                        <p>
                                            {{ $closureStart }}
                                            –
                                            {{ $closureEnd }}
                                        </p>
                                    </div>

                                    <span
                                        class="closure-status {{ $closure->status }}"
                                    >
                                        {{ ucfirst($closure->status) }}
                                    </span>

                                </div>

                                <p>
                                    <strong>Reason:</strong>
                                    {{ $closure->reason }}
                                </p>

                                <p>
                                    <strong>Declared By:</strong>

                                    {{ $closure->createdBy?->name
                                        ?? 'Unknown Administrator' }}
                                </p>

                                <p>
                                    <strong>Declared At:</strong>

                                    {{ $closure->declared_at
                                        ? $closure->declared_at->format(
                                            'F d, Y h:i A'
                                        )
                                        : 'Not recorded' }}
                                </p>

                                @if ($closure->admin_notes)

                                    <p>
                                        <strong>Internal Notes:</strong>
                                        {{ $closure->admin_notes }}
                                    </p>

                                @endif

                                @if (!$closureIsActive)

                                    <p>
                                        <strong>Lifted By:</strong>

                                        {{ $closure->liftedBy?->name
                                            ?? 'Unknown Administrator' }}
                                    </p>

                                    <p>
                                        <strong>Lifted At:</strong>

                                        {{ $closure->lifted_at
                                            ? $closure->lifted_at->format(
                                                'F d, Y h:i A'
                                            )
                                            : 'Not recorded' }}
                                    </p>

                                    <p>
                                        <strong>Reopening Reason:</strong>

                                        {{ $closure->lift_reason
                                            ?: 'No reason recorded' }}
                                    </p>

                                @endif

                                @if ($closureIsActive)

                                    <div class="closure-record-actions">

                                        <button
                                            type="button"
                                            class="action-btn reschedule-btn"
                                            onclick="openBookingModal(
                                                'liftClosureModal{{ $closure->id }}'
                                            )"
                                            @disabled(!$closureLiftRouteReady)
                                        >
                                            Lift Closure
                                        </button>

                                    </div>

                                @endif

                            </div>

                        @endforeach

                    @else

                        <div class="closure-empty">
                            No studio closures have been recorded yet
                        </div>

                    @endif

                </div>

                @if ($closureRecords && $closureRecords->hasPages())

                    <div class="closure-pagination">
                        {{ $closureRecords->links() }}
                    </div>

                @endif

            </div>

            <div class="modal-actions">

                <button
                    type="button"
                    class="modal-back"
                    onclick="closeBookingModal('studioClosuresModal')"
                >
                    Back to Booking Management
                </button>

            </div>

        </div>

    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: LIFT CLOSURE CONFIRMATION POPUPS
    |--------------------------------------------------------------------------
    --}}

    @if ($closureRecords)

        @foreach ($closureRecords as $closure)

            @if ($closure->status === 'active')

                <div
                    class="modal-overlay"
                    id="liftClosureModal{{ $closure->id }}"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Lift Studio Closure"
                >

                    <div class="modal">

                        <h3>Lift Studio Closure</h3>

                        <p>
                            You are about to lift the closure:
                            <strong>{{ $closure->title }}</strong>
                        </p>

                        <div class="info-box">

                            <strong>Reopening Information</strong>

                            <span>
                                Lifting the closure will make affected
                                dates eligible for new bookings once
                                backend closure restrictions are active

                                Appointments already marked for
                                rescheduling will not automatically
                                return to their original schedules
                            </span>

                        </div>

                        @if (!$closureLiftRouteReady)

                            <div class="closure-route-warning">
                                The lift-closure route has not
                                been registered yet
                            </div>

                        @endif

                        <form
                            method="POST"
                            action="{{ $closureLiftRouteReady
                                ? route(
                                    'admin.bookings.closures.lift',
                                    $closure->id
                                )
                                : '#' }}"
                        >
                            @csrf
                            @method('PATCH')

                            <label
                                for="liftReason{{ $closure->id }}"
                            >
                                Reopening Reason
                            </label>

                            <textarea
                                id="liftReason{{ $closure->id }}"
                                name="lift_reason"
                                maxlength="1000"
                                placeholder="Explain why studio operations can resume"
                                required
                            ></textarea>

                            <label
                                for="liftPassword{{ $closure->id }}"
                            >
                                Admin Password
                            </label>

                            <input
                                type="password"
                                id="liftPassword{{ $closure->id }}"
                                name="admin_password"
                                autocomplete="current-password"
                                placeholder="Enter your administrator password"
                                required
                            >

                            <div class="modal-actions">

                                <button
                                    type="submit"
                                    class="modal-primary"
                                    @disabled(!$closureLiftRouteReady)
                                >
                                    Confirm Reopening
                                </button>

                                <button
                                    type="button"
                                    class="modal-back"
                                    onclick="closeBookingModal(
                                        'liftClosureModal{{ $closure->id }}'
                                    )"
                                >
                                    Back
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            @endif

        @endforeach

    @endif

</div>

@endsection

@section('scripts')

<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: ADMIN BOOKING MODAL CONTROLS
|--------------------------------------------------------------------------
*/

function openBookingModal(id) {
    const overlay = document.getElementById(id);

    if (!overlay) {
        return;
    }

    overlay.classList.add("show");

    overlay.scrollTop = 0;

    const modal = overlay.querySelector(".modal");

    if (modal) {
        modal.scrollTop = 0;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Support Nested Closure Popups
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(".modal-overlay.show")
        .forEach(function (item) {
            item.style.zIndex = item.id === id
                ? "1002"
                : "999";
        });
}

function closeBookingModal(id) {
    const overlay = document.getElementById(id);

    if (!overlay) {
        return;
    }

    overlay.classList.remove("show");
    overlay.style.zIndex = "";

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Restore Parent Studio Closure Popup
    |--------------------------------------------------------------------------
    */

    const parent = document.getElementById(
        "studioClosuresModal"
    );

    if (
        parent &&
        parent.classList.contains("show")
    ) {
        parent.style.zIndex = "999";
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: EXPAND BOOKING DETAILS
|--------------------------------------------------------------------------
*/

function toggleBookingDetails(id) {
    const box = document.getElementById(id);

    if (!box) {
        return;
    }

    box.style.display =
        box.style.display === "none" ||
        box.style.display === ""
            ? "block"
            : "none";
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: STUDIO CLOSURE DATE VALIDATION
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "DOMContentLoaded",
    function () {
        const startDate = document.getElementById(
            "closureStartDate"
        );

        const endDate = document.getElementById(
            "closureEndDate"
        );

        const form = document.getElementById(
            "studioClosureCreateForm"
        );

        if (startDate && endDate) {
            startDate.addEventListener(
                "change",
                function () {
                    endDate.min = startDate.value;

                    if (
                        endDate.value &&
                        endDate.value < startDate.value
                    ) {
                        endDate.value = startDate.value;
                    }
                }
            );
        }

        if (form) {
            form.addEventListener(
                "submit",
                function (event) {
                    if (
                        !startDate.value ||
                        !endDate.value
                    ) {
                        event.preventDefault();
                        return;
                    }

                    if (
                        endDate.value < startDate.value
                    ) {
                        event.preventDefault();

                        alert(
                            "The closure end date cannot be earlier than the start date"
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Password Is Checked by Laravel
                    |--------------------------------------------------------------------------
                    */

                    const confirmation = confirm(
                        "Declare this studio-wide closure? Affected future appointments will require rescheduling"
                    );

                    if (!confirmation) {
                        event.preventDefault();
                    }
                }
            );
        }
    }
);

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: CLICK OUTSIDE MODAL
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "click",
    function (event) {
        if (
            event.target.classList.contains(
                "modal-overlay"
            )
        ) {
            const overlay = event.target;

            closeBookingModal(overlay.id);
        }
    }
);

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: ESC KEY SUPPORT
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "keydown",
    function (event) {
        if (event.key !== "Escape") {
            return;
        }

        const opened = Array.from(
            document.querySelectorAll(
                ".modal-overlay.show"
            )
        );

        if (!opened.length) {
            return;
        }

        const topmost = opened.reduce(
            (top, current) => {
                const topZ = Number(
                    top.style.zIndex || 999
                );

                const currentZ = Number(
                    current.style.zIndex || 999
                );

                return currentZ >= topZ
                    ? current
                    : top;
            }
        );

        closeBookingModal(topmost.id);
    }
);
</script>

@endsection
