
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Admin Staff Management
|--------------------------------------------------------------------------
| Features:
| - Add staff with email account activation
| - Assign official services to staff
| - Edit staff information
| - Manage normal and emergency day-offs
| - Explain automatic reassignment and rescheduling
| - Enable and disable staff
| - Delete, restore and permanently delete staff
|
| Design:
| - Main containers: 24px radius
| - Inner controls: 14px radius
| - Muji-inspired brown, cream and glass theme
| - Sidebar remains visible during popups
| - No unnecessary popup close icons
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
.ns-staff {
    --ns-radius-main: 24px;
    --ns-radius-inner: 14px;
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

.ns-staff *,
.ns-staff *::before,
.ns-staff *::after {
    box-sizing: border-box;
}

.ns-staff .page-header {
    margin-bottom: 28px;
}

.ns-staff .page-header h1 {
    font-size: 34px;
    line-height: 1.2;
    margin-bottom: 8px;
}

.ns-staff .page-header p {
    color: var(--ns-muted);
    font-size: 14px;
    line-height: 1.7;
}

.ns-staff .alert {
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: var(--ns-radius-inner);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.6;
}

.ns-staff .alert-success {
    background: rgba(81,148,91,.12);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.ns-staff .alert-error {
    background: rgba(184,77,77,.12);
    color: #a63e3e;
    border: 1px solid rgba(184,77,77,.22);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Summary Cards
|--------------------------------------------------------------------------
*/

.ns-staff .stats-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.ns-staff .stat-card,
.ns-staff .filter-card,
.ns-staff .form-card,
.ns-staff .staff-card,
.ns-staff .deleted-card {
    background: var(--ns-glass);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid var(--border,rgba(111,78,55,.13));
    border-radius: var(--ns-radius-main);
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.65);
    min-width: 0;
}

.ns-staff .stat-card {
    padding: 22px;
}

.ns-staff .stat-card h2 {
    font-size: 28px;
    color: var(--ns-brown);
    line-height: 1.2;
}

.ns-staff .stat-card p {
    margin-top: 6px;
    color: var(--ns-muted);
    font-size: 12px;
    font-weight: 700;
}

.ns-staff .filter-card,
.ns-staff .form-card {
    padding: 24px;
    margin-bottom: 24px;
}

.ns-staff .card-title {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 20px;
}

.ns-staff .card-title h2 {
    font-size: 22px;
    margin-bottom: 6px;
}

.ns-staff .card-title p {
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.6;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Form Layouts
|--------------------------------------------------------------------------
*/

.ns-staff .filter-row {
    display: grid;
    grid-template-columns: 1.5fr 1fr auto auto;
    gap: 14px;
    align-items: end;
}

.ns-staff .form-grid {
    display: grid;
    grid-template-columns: 1.2fr 1.2fr .9fr .8fr;
    gap: 14px;
    align-items: end;
}

.ns-staff .form-group.full,
.ns-staff .full {
    grid-column: 1/-1;
}

.ns-staff label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 800;
}

.ns-staff input:not([type="checkbox"]),
.ns-staff select,
.ns-staff textarea {
    display: block;
    width: 100%;
    padding: 13px 14px;
    min-height: 46px;
    border-radius: var(--ns-radius-inner);
    border: 1px solid var(--ns-border);
    background: rgba(255,255,255,.68);
    color: var(--ns-dark);
    outline: none;
    font: inherit;
    font-size: 13px;
}

.ns-staff input:focus,
.ns-staff select:focus,
.ns-staff textarea:focus {
    border-color: var(--ns-brown);
    box-shadow: 0 0 0 3px rgba(111,78,55,.10);
}

.ns-staff input::placeholder,
.ns-staff textarea::placeholder {
    color: #aaa096;
}

.ns-staff textarea {
    min-height: 95px;
    resize: vertical;
}

.ns-staff .help {
    display: block;
    margin-top: 7px;
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.6;
}

.ns-staff .error-text {
    display: block;
    color: #b84d4d;
    font-size: 12px;
    margin-top: 6px;
    font-weight: 800;
}

.ns-staff .checkbox-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 10px;
}

.ns-staff .checkbox-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 12px;
    margin: 0;
    border-radius: var(--ns-radius-inner);
    background: rgba(255,255,255,.46);
    border: 1px solid var(--ns-border);
    color: var(--ns-dark);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.5;
    cursor: pointer;
}

.ns-staff .checkbox-pill:has(input:checked) {
    border-color: var(--ns-brown);
    background: rgba(255,255,255,.85);
}

.ns-staff .checkbox-pill input {
    width: 17px;
    height: 17px;
    flex: 0 0 auto;
    accent-color: var(--ns-brown);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Action Buttons
|--------------------------------------------------------------------------
*/

.ns-staff .filter-btn,
.ns-staff .reset-btn,
.ns-staff .primary-btn,
.ns-staff .light-btn,
.ns-staff .danger-btn,
.ns-staff .success-btn,
.ns-staff .status-btn,
.ns-staff .action-btn,
.ns-staff .modal-danger,
.ns-staff .modal-primary,
.ns-staff .modal-success,
.ns-staff .modal-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 18px;
    min-height: 43px;
    border-radius: var(--ns-radius-inner);
    border: 1px solid transparent;
    font: inherit;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    text-align: center;
    white-space: nowrap;
    cursor: pointer;
}

.ns-staff .filter-btn,
.ns-staff .primary-btn,
.ns-staff .modal-primary {
    background: var(--ns-gradient);
    color: white;
}

.ns-staff .reset-btn,
.ns-staff .light-btn,
.ns-staff .modal-back {
    background: rgba(255,255,255,.62);
    color: var(--ns-dark);
    border-color: var(--ns-border);
}

.ns-staff .danger-btn {
    background: rgba(184,77,77,.12);
    color: #a33f3f;
    border-color: rgba(184,77,77,.20);
}

.ns-staff .modal-danger {
    background: #b84d4d;
    color: white;
}

.ns-staff .success-btn,
.ns-staff .modal-success {
    background: rgba(81,148,91,.16);
    color: #2f7d3c;
    border-color: rgba(81,148,91,.25);
}

.ns-staff .status-btn {
    background: rgba(138,113,88,.16);
    color: var(--ns-brown);
    border-color: rgba(138,113,88,.20);
}

.ns-staff button:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.ns-staff button:focus-visible,
.ns-staff a:focus-visible {
    outline: 3px solid rgba(111,78,55,.35);
    outline-offset: 3px;
}

.ns-staff .form-actions,
.ns-staff .actions,
.ns-staff .modal-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.ns-staff .form-actions {
    justify-content: flex-end;
    margin-top: 18px;
}

.ns-staff .modal-actions {
    justify-content: flex-end;
    margin-top: 24px;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Staff List
|--------------------------------------------------------------------------
*/

.ns-staff .staff-list {
    display: grid;
    gap: 18px;
}

.ns-staff .staff-card {
    padding: 24px;
}

.ns-staff .staff-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 20px;
}

.ns-staff .staff-main {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    min-width: 0;
}

.ns-staff .staff-avatar {
    display: grid;
    place-items: center;
    width: 76px;
    height: 76px;
    flex: 0 0 76px;
    border-radius: var(--ns-radius-inner);
    background: var(--ns-gradient);
    color: white;
    font-size: 21px;
    font-weight: 900;
}

.ns-staff .staff-main h3 {
    margin-bottom: 6px;
    font-size: 18px;
    overflow-wrap: anywhere;
}

.ns-staff .staff-main p {
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.ns-staff .badge-row {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 8px;
}

.ns-staff .status,
.ns-staff .account-badge,
.ns-staff .dayoff-badge {
    display: inline-flex;
    align-items: center;
    padding: 7px 11px;
    border-radius: var(--ns-radius-inner);
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.ns-staff .status.active {
    background: rgba(81,148,91,.16);
    color: #2f7d3c;
}

.ns-staff .status.inactive {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

.ns-staff .account-badge {
    background: rgba(255,255,255,.55);
    color: var(--ns-dark);
    border: 1px solid var(--ns-border);
}

.ns-staff .dayoff-badge {
    background: rgba(255,196,87,.20);
    color: #906000;
}

.ns-staff .details-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.ns-staff .detail-box {
    padding: 16px;
    min-width: 0;
    border-radius: var(--ns-radius-inner);
    background: rgba(255,255,255,.46);
    border: 1px solid rgba(255,255,255,.65);
}

.ns-staff .detail-box small {
    color: var(--ns-muted);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .03em;
}

.ns-staff .detail-box p {
    margin-top: 6px;
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.ns-staff .skill-box,
.ns-staff .dayoff-box {
    padding: 16px;
    margin-bottom: 18px;
    border-radius: var(--ns-radius-inner);
    border: 1px solid rgba(255,255,255,.65);
    background: rgba(255,255,255,.40);
}

.ns-staff .dayoff-box {
    background: rgba(255,196,87,.10);
    border-color: rgba(255,196,87,.22);
}

.ns-staff .skill-box small,
.ns-staff .dayoff-box small {
    display: block;
    margin-bottom: 10px;
    font-size: 10px;
    font-weight: 800;
    color: var(--ns-muted);
}

.ns-staff .skill-chip,
.ns-staff .dayoff-chip {
    display: inline-flex;
    align-items: center;
    margin: 3px 6px 3px 0;
    padding: 8px 11px;
    border-radius: var(--ns-radius-inner);
    background: rgba(255,255,255,.65);
    border: 1px solid var(--ns-border);
    font-size: 12px;
    font-weight: 700;
}

.ns-staff .dayoff-chip {
    background: rgba(255,196,87,.16);
    color: #8d6100;
    border-color: rgba(255,196,87,.25);
}

.ns-staff .deleted-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin: 28px 0 18px;
}

.ns-staff .deleted-header h2 {
    font-size: 22px;
}

.ns-staff .deleted-header p {
    margin-top: 5px;
    color: var(--ns-muted);
    font-size: 13px;
}

.ns-staff .pagination {
    margin-top: 24px;
}

.ns-staff .empty-state {
    padding: 48px 20px;
    text-align: center;
    background: var(--ns-glass);
    border: 1px solid var(--ns-border);
    border-radius: var(--ns-radius-main);
    color: var(--ns-muted);
}

.ns-staff .empty-state h3 {
    margin-bottom: 8px;
    color: var(--ns-dark);
}

.ns-staff .empty-state p {
    font-size: 13px;
    line-height: 1.6;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Emergency Day Off Notice
|--------------------------------------------------------------------------
*/

.ns-staff .emergency-info {
    padding: 18px;
    margin: 12px 0 18px;
    border-radius: var(--ns-radius-inner);
    border: 1px solid rgba(138,113,88,.25);
    background: rgba(138,113,88,.08);
}

.ns-staff .emergency-info h4 {
    font-size: 14px;
    font-weight: 800;
    color: var(--ns-brown);
    margin-bottom: 9px;
}

.ns-staff .emergency-info p,
.ns-staff .emergency-info li {
    color: var(--ns-muted);
    font-size: 12px;
    line-height: 1.7;
}

.ns-staff .emergency-info ol {
    padding-left: 18px;
    margin: 10px 0 0;
}

.ns-staff .emergency-info li {
    margin-bottom: 5px;
}

.ns-staff .emergency-info strong {
    color: var(--ns-dark);
}

.ns-staff .emergency-confirm {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px;
    margin-top: 16px;
    border-radius: var(--ns-radius-inner);
    background: rgba(255,255,255,.58);
    border: 1px solid var(--ns-border);
}

.ns-staff .emergency-confirm input {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    flex: 0 0 18px;
    accent-color: var(--ns-brown);
}

.ns-staff .emergency-confirm span {
    font-size: 12px;
    line-height: 1.6;
    color: var(--ns-dark);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Popup Layout
|--------------------------------------------------------------------------
| Sidebar remains accessible during all popups
|--------------------------------------------------------------------------
*/

.ns-staff .modal-overlay {
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

.ns-staff .modal-overlay.show {
    display: flex;
}

.ns-staff .modal {
    width: 100%;
    max-width: 640px;
    max-height: calc(100dvh - 40px);
    overflow-y: auto;
    padding: 28px;
    border-radius: var(--ns-radius-main);
    background: rgba(255,255,255,.96);
    border: 1px solid rgba(255,255,255,.90);
    box-shadow:
        0 25px 80px rgba(47,33,24,.20),
        inset 0 1px 0 rgba(255,255,255,.7);
    scrollbar-width: thin;
}

.ns-staff .modal.large {
    max-width: 900px;
}

.ns-staff .modal h3 {
    margin-bottom: 10px;
    font-size: 22px;
}

.ns-staff .modal > p {
    margin-bottom: 18px;
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.6;
}

.ns-staff .modal-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 14px;
}

.ns-staff .modal-grid .full {
    grid-column: 1/-1;
}

.ns-staff .dayoff-list {
    display: grid;
    gap: 12px;
    margin-top: 18px;
}

.ns-staff .dayoff-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border-radius: var(--ns-radius-inner);
    background: rgba(255,255,255,.56);
    border: 1px solid var(--ns-border);
}

.ns-staff .dayoff-item p {
    margin: 0;
    font-size: 12px;
    line-height: 1.7;
    color: var(--ns-muted);
}

.ns-staff .dayoff-item strong {
    color: var(--ns-dark);
}

.ns-staff .warning-box {
    padding: 16px;
    margin: 18px 0;
    border-radius: var(--ns-radius-inner);
    background: rgba(184,77,77,.10);
    border: 1px solid rgba(184,77,77,.22);
}

.ns-staff .warning-box strong {
    display: block;
    margin-bottom: 6px;
    color: #a73e3e;
}

.ns-staff .warning-box span {
    color: var(--ns-muted);
    font-size: 13px;
    line-height: 1.6;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Keep Admin Sidebar Above Overlay
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
| NS BEAUTY COMMENT: Responsive Layout
|--------------------------------------------------------------------------
*/

@media (max-width:1100px) {
    .ns-staff .stats-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }

    .ns-staff .filter-row,
    .ns-staff .form-grid,
    .ns-staff .details-grid,
    .ns-staff .checkbox-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }
}

@media (max-width:900px) {
    .ns-staff .modal-overlay {
        left: 0;
    }

    .ns-staff .modal-grid {
        grid-template-columns: minmax(0,1fr);
    }
}

@media (max-width:700px) {
    .ns-staff .page-header h1 {
        font-size: 28px;
    }

    .ns-staff .stats-grid,
    .ns-staff .filter-row,
    .ns-staff .form-grid,
    .ns-staff .details-grid,
    .ns-staff .checkbox-grid {
        grid-template-columns: minmax(0,1fr);
    }

    .ns-staff .filter-card,
    .ns-staff .form-card,
    .ns-staff .staff-card {
        padding: 18px;
    }

    .ns-staff .staff-top,
    .ns-staff .staff-main,
    .ns-staff .deleted-header,
    .ns-staff .dayoff-item {
        flex-direction: column;
        align-items: stretch;
    }

    .ns-staff .staff-avatar {
        width: 70px;
        height: 70px;
        flex-basis: 70px;
    }

    .ns-staff .badge-row {
        justify-content: flex-start;
    }

    .ns-staff .modal {
        padding: 22px 16px;
    }

    .ns-staff .actions > *,
    .ns-staff .form-actions > *,
    .ns-staff .modal-actions > * {
        width: 100%;
    }

    .ns-staff .modal-overlay {
        padding: 12px;
    }

    .ns-staff .modal {
        max-height: calc(100dvh - 24px);
    }
}
</style>
@endsection

@section('content')

<div class="ns-staff">

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Page Header
    |--------------------------------------------------------------------------
    --}}

    <div class="page-header">
        <h1>Staff Management</h1>

        <p>
            Manage staff profiles, service assignments, account status,
            day-offs and emergency scheduling
        </p>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Laravel Feedback
    |--------------------------------------------------------------------------
    --}}

    @if (session('success'))
        <div class="alert alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Staff Statistics
    |--------------------------------------------------------------------------
    --}}

    <div class="stats-grid">

        <div class="stat-card">
            <h2>{{ $stats['total'] }}</h2>
            <p>Total Staff</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['active'] }}</h2>
            <p>Active Staff</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['inactive'] }}</h2>
            <p>Inactive Staff</p>
        </div>

        <div class="stat-card">
            <h2>{{ $stats['on_day_off'] }}</h2>
            <p>On Day Off</p>
        </div>

    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Search and Filter
    |--------------------------------------------------------------------------
    --}}

    <section class="filter-card">

        <form method="GET" action="{{ route('admin.staff') }}">

            <div class="filter-row">

                <div>
                    <label for="staffSearch">Search</label>

                    <input
                        type="text"
                        id="staffSearch"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Search name, email or phone number"
                    >
                </div>

                <div>
                    <label for="staffStatusFilter">Status</label>

                    <select id="staffStatusFilter" name="status">
                        <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>
                            All
                        </option>

                        <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>

                <button type="submit" class="filter-btn">
                    Filter
                </button>

                <a href="{{ route('admin.staff') }}" class="reset-btn">
                    Reset
                </a>

            </div>
        </form>

    </section>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Add Staff
    |--------------------------------------------------------------------------
    | Staff activation email is handled by StaffManagementController
    |--------------------------------------------------------------------------
    --}}

    <section class="form-card">

        <div class="card-title">
            <div>
                <h2>Add New Staff</h2>

                <p>
                    Create a staff account and assign services from Service Management
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.staff.store') }}">
            @csrf

            <div class="form-grid">

                <div>
                    <label for="newStaffName">Full Name</label>

                    <input
                        type="text"
                        id="newStaffName"
                        name="full_name"
                        value="{{ old('full_name') }}"
                        maxlength="150"
                        required
                    >
                </div>

                <div>
                    <label for="newStaffEmail">Email</label>

                    <input
                        type="email"
                        id="newStaffEmail"
                        name="email"
                        value="{{ old('email') }}"
                        maxlength="150"
                        required
                    >
                </div>

                <div>
                    <label for="newStaffPhone">Phone Number</label>

                    <input
                        type="text"
                        id="newStaffPhone"
                        name="phone_number"
                        value="{{ old('phone_number') }}"
                        maxlength="30"
                    >
                </div>

                <div>
                    <label for="newStaffStatus">Status</label>

                    <select id="newStaffStatus" name="status" required>
                        <option
                            value="Active"
                            {{ old('status','Active') === 'Active' ? 'selected' : '' }}
                        >
                            Active
                        </option>

                        <option
                            value="Inactive"
                            {{ old('status') === 'Inactive' ? 'selected' : '' }}
                        >
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="form-group full">

                    <label>Assigned Services</label>

                    <div class="checkbox-grid">

                        @forelse ($services as $service)

                            <label class="checkbox-pill">

                                <input
                                    type="checkbox"
                                    name="service_ids[]"
                                    value="{{ $service->id }}"
                                    {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}
                                >

                                <span>{{ $service->service_name }}</span>

                            </label>

                        @empty

                            <small class="help">
                                No services found — add services in Service Management first
                            </small>

                        @endforelse

                    </div>

                    <small class="help">
                        Staff will only be eligible for bookings that match their assigned services
                    </small>

                </div>

            </div>

            <div class="form-actions">

                <button type="reset" class="light-btn">
                    Clear
                </button>

                <button type="submit" class="primary-btn">
                    Add Staff
                </button>

            </div>

        </form>

    </section>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Staff List Heading
    |--------------------------------------------------------------------------
    --}}

    <div class="deleted-header">

        <div>
            <h2>Staff List</h2>

            <p>
                Manage existing staff and their day-off schedules
            </p>
        </div>

        <button
            type="button"
            class="light-btn"
            onclick="openStaffModal('deletedStaffModal')"
        >
            Deleted Staff

            @if ($deletedStaff->count() > 0)
                ({{ $deletedStaff->count() }})
            @endif
        </button>

    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Staff Cards
    |--------------------------------------------------------------------------
    --}}

    <div class="staff-list">

        @forelse ($staffMembers as $staff)

            @php
                $statusKey = strtolower($staff->status ?? 'Active');

                $initials = collect(explode(' ', $staff->full_name))
                    ->filter()
                    ->take(2)
                    ->map(fn ($word) => strtoupper(substr($word,0,1)))
                    ->implode('');

                $hasDayOff = $staff->activeDayOffs->count() > 0;
            @endphp

            <section class="staff-card">

                <div class="staff-top">

                    <div class="staff-main">

                        <div class="staff-avatar" aria-hidden="true">
                            {{ $initials ?: 'S' }}
                        </div>

                        <div>
                            <h3>{{ $staff->full_name }}</h3>

                            <p>
                                Staff ID:
                                #ST-{{ str_pad($staff->id,4,'0',STR_PAD_LEFT) }}
                            </p>

                            <p>
                                Created:
                                {{ $staff->created_at?->format('F d, Y h:i A') ?? 'Not recorded' }}
                            </p>
                        </div>

                    </div>

                    <div class="badge-row">

                        @if ($hasDayOff)
                            <span class="dayoff-badge">
                                Has Day Off
                            </span>
                        @endif

                        <span class="status {{ $statusKey }}">
                            {{ $staff->status }}
                        </span>

                    </div>

                </div>

                <div class="details-grid">

                    <div class="detail-box">
                        <small>EMAIL</small>
                        <p>{{ $staff->email ?: 'Not recorded' }}</p>
                    </div>

                    <div class="detail-box">
                        <small>PHONE</small>
                        <p>{{ $staff->phone_number ?: 'Not recorded' }}</p>
                    </div>

                    <div class="detail-box">
                        <small>TOTAL BOOKINGS</small>
                        <p>{{ $staff->appointments_count }} bookings</p>
                    </div>

                    <div class="detail-box">
                        <small>ASSIGNED SERVICES</small>
                        <p>{{ $staff->services->count() }} services</p>
                    </div>

                </div>

                <div class="skill-box">

                    <small>ASSIGNED SERVICES FROM SERVICE MANAGEMENT</small>

                    @forelse ($staff->services as $service)

                        <span class="skill-chip">
                            {{ $service->service_name }}
                        </span>

                    @empty

                        <span class="skill-chip">
                            No services assigned
                        </span>

                    @endforelse

                </div>

                @if ($hasDayOff)

                    <div class="dayoff-box">

                        <small>ACTIVE / UPCOMING DAY OFF</small>

                        @foreach ($staff->activeDayOffs as $dayOff)

                            <span class="dayoff-chip">
                                {{ $dayOff->start_date->format('M d, Y') }}
                                –
                                {{ $dayOff->end_date->format('M d, Y') }}
                            </span>

                        @endforeach

                    </div>

                @endif

                <div class="actions">

                    <button
                        type="button"
                        class="action-btn light-btn"
                        onclick="openStaffModal('editStaffModal{{ $staff->id }}')"
                    >
                        Edit Staff
                    </button>

                    <form
                        method="POST"
                        action="{{ route('admin.staff.status',$staff) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="action-btn status-btn"
                        >
                            {{ $staff->status === 'Active' ? 'Disable Staff' : 'Enable Staff' }}
                        </button>
                    </form>

                    <button
                        type="button"
                        class="action-btn danger-btn"
                        onclick="openStaffModal('deleteStaffModal{{ $staff->id }}')"
                    >
                        Delete Staff
                    </button>

                </div>

            </section>

        @empty

            <div class="empty-state">
                <h3>No Staff Found</h3>
                <p>No staff records match the selected filters</p>
            </div>

        @endforelse

    </div>

    <div class="pagination">
        {{ $staffMembers->links() }}
    </div>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Edit Staff and Emergency Day Off Popup
    |--------------------------------------------------------------------------
    --}}

    @foreach ($staffMembers as $staff)

        <div
            class="modal-overlay"
            id="editStaffModal{{ $staff->id }}"
            role="dialog"
            aria-modal="true"
            aria-label="Edit Staff"
        >

            <div class="modal large">

                <h3>Edit Staff</h3>

                <p>
                    Update staff details, service assignments and day-off schedules
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.staff.update',$staff) }}"
                    id="editStaffForm{{ $staff->id }}"
                    onsubmit="return confirmStaffUpdate(event,{{ $staff->id }})"
                >
                    @csrf
                    @method('PATCH')

                    <div class="modal-grid">

                        <div>
                            <label for="staffName{{ $staff->id }}">Full Name</label>

                            <input
                                type="text"
                                id="staffName{{ $staff->id }}"
                                name="full_name"
                                value="{{ $staff->full_name }}"
                                maxlength="150"
                                required
                            >
                        </div>

                        <div>
                            <label for="staffEmail{{ $staff->id }}">Email</label>

                            <input
                                type="email"
                                id="staffEmail{{ $staff->id }}"
                                name="email"
                                value="{{ $staff->email }}"
                                maxlength="150"
                                required
                            >
                        </div>

                        <div>
                            <label for="staffPhone{{ $staff->id }}">Phone Number</label>

                            <input
                                type="text"
                                id="staffPhone{{ $staff->id }}"
                                name="phone_number"
                                value="{{ $staff->phone_number }}"
                                maxlength="30"
                            >
                        </div>

                        <div>
                            <label for="staffStatus{{ $staff->id }}">Status</label>

                            <select
                                id="staffStatus{{ $staff->id }}"
                                name="status"
                                required
                            >
                                <option
                                    value="Active"
                                    {{ $staff->status === 'Active' ? 'selected' : '' }}
                                >
                                    Active
                                </option>

                                <option
                                    value="Inactive"
                                    {{ $staff->status === 'Inactive' ? 'selected' : '' }}
                                >
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <div class="full">

                            <label>Assigned Services</label>

                            <div class="checkbox-grid">

                                @forelse ($services as $service)

                                    <label class="checkbox-pill">

                                        <input
                                            type="checkbox"
                                            name="service_ids[]"
                                            value="{{ $service->id }}"
                                            {{ $staff->services->contains('id',$service->id) ? 'checked' : '' }}
                                        >

                                        <span>
                                            {{ $service->service_name }}
                                        </span>

                                    </label>

                                @empty

                                    <small class="help">
                                        No services found in Service Management
                                    </small>

                                @endforelse

                            </div>

                        </div>

                        {{--
                        |--------------------------------------------------------------------------
                        | NS BEAUTY COMMENT: Emergency or Regular Day Off
                        |--------------------------------------------------------------------------
                        | Uses existing controller fields
                        |--------------------------------------------------------------------------
                        --}}

                        <div class="full">

                            <div class="emergency-info">

                                <h4>Staff Day Off and Emergency Scheduling</h4>

                                <p>
                                    When a new day off is recorded, the system checks
                                    affected upcoming appointments
                                </p>

                                <ol>
                                    <li>
                                        <strong>Replacement available</strong> —
                                        The system attempts to assign another active
                                        beautician qualified for all selected services
                                        without changing the appointment time
                                    </li>

                                    <li>
                                        <strong>No replacement available</strong> —
                                        The customer is notified that rescheduling is required
                                    </li>

                                    <li>
                                        <strong>Notifications</strong> —
                                        Customers receive an in-app notice through
                                        the notification system, with email delivery
                                        dependent on the configured notification service
                                    </li>
                                </ol>

                                <p style="margin-top:12px">
                                    Shop closing day: <strong>Every Sunday</strong>
                                </p>

                            </div>

                        </div>

                        <div>
                            <label for="offStart{{ $staff->id }}">
                                Day Off Start Date
                            </label>

                            <input
                                type="date"
                                id="offStart{{ $staff->id }}"
                                name="off_start_date"
                                min="{{ \Carbon\Carbon::today('Asia/Kuala_Lumpur')->toDateString() }}"
                            >
                        </div>

                        <div>
                            <label for="offEnd{{ $staff->id }}">
                                Day Off End Date
                            </label>

                            <input
                                type="date"
                                id="offEnd{{ $staff->id }}"
                                name="off_end_date"
                                min="{{ \Carbon\Carbon::today('Asia/Kuala_Lumpur')->toDateString() }}"
                            >
                        </div>

                        <div class="full">

                            <label for="offReason{{ $staff->id }}">
                                Day Off Reason
                            </label>

                            <textarea
                                id="offReason{{ $staff->id }}"
                                name="off_reason"
                                maxlength="1000"
                                placeholder="Enter reason for leave or emergency"
                            ></textarea>

                            <small class="help">
                                For a one-day absence, choose the same start and end date
                            </small>

                        </div>

                        <div class="full">

                            <label class="emergency-confirm">

                                <input
                                    type="checkbox"
                                    id="emergencyAcknowledgement{{ $staff->id }}"
                                >

                                <span>
                                    I understand that recording a new day off may
                                    automatically reassign affected appointments
                                    or notify customers that rescheduling is required
                                </span>

                            </label>

                            <small class="help">
                                Required only when a new day-off date range is entered
                            </small>

                        </div>

                    </div>

                    {{--
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Existing Day Off Records
                    |--------------------------------------------------------------------------
                    --}}

                    @if ($staff->activeDayOffs->count() > 0)

                        <div class="dayoff-list">

                            @foreach ($staff->activeDayOffs as $dayOff)

                                <div class="dayoff-item">

                                    <p>
                                        <strong>
                                            {{ $dayOff->start_date->format('M d, Y') }}
                                            –
                                            {{ $dayOff->end_date->format('M d, Y') }}
                                        </strong>

                                        <span style="display:block;margin-top:5px">
                                            {{ $dayOff->reason ?: 'No reason provided' }}
                                        </span>
                                    </p>

                                    <button
                                        type="submit"
                                        form="deleteDayOffForm{{ $dayOff->id }}"
                                        class="danger-btn"
                                    >
                                        Remove
                                    </button>

                                </div>

                            @endforeach

                        </div>

                    @endif

                    <div class="modal-actions">

                        <button
                            type="submit"
                            class="modal-primary"
                            id="saveStaffBtn{{ $staff->id }}"
                        >
                            Save Changes
                        </button>

                        <button
                            type="button"
                            class="modal-back"
                            onclick="closeStaffModal('editStaffModal{{ $staff->id }}')"
                        >
                            Back
                        </button>

                    </div>

                </form>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Separate Day Off Removal Forms
                |--------------------------------------------------------------------------
                --}}

                @foreach ($staff->activeDayOffs as $dayOff)

                    <form
                        id="deleteDayOffForm{{ $dayOff->id }}"
                        method="POST"
                        action="{{ route('admin.staff.day-offs.destroy',$dayOff) }}"
                        style="display:none"
                    >
                        @csrf
                        @method('DELETE')
                    </form>

                @endforeach

            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Delete Staff Popup
        |--------------------------------------------------------------------------
        --}}

        <div
            class="modal-overlay"
            id="deleteStaffModal{{ $staff->id }}"
            role="dialog"
            aria-modal="true"
            aria-label="Delete Staff"
        >

            <div class="modal">

                <h3>Delete Staff</h3>

                <div class="warning-box">

                    <strong>Delete this staff member?</strong>

                    <span>
                        The staff account will be moved to Deleted Staff
                        and may be restored within 30 days
                    </span>

                </div>

                <p>
                    Staff: <strong>{{ $staff->full_name }}</strong>
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.staff.destroy',$staff) }}"
                >
                    @csrf
                    @method('DELETE')

                    <div class="modal-actions">

                        <button
                            type="submit"
                            class="modal-danger"
                        >
                            Delete Staff
                        </button>

                        <button
                            type="button"
                            class="modal-back"
                            onclick="closeStaffModal('deleteStaffModal{{ $staff->id }}')"
                        >
                            Back
                        </button>

                    </div>

                </form>

            </div>
        </div>

    @endforeach

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Deleted Staff Popup
    |--------------------------------------------------------------------------
    --}}

    <div
        class="modal-overlay"
        id="deletedStaffModal"
        role="dialog"
        aria-modal="true"
        aria-label="Deleted Staff"
    >

        <div class="modal large">

            <h3>Deleted Staff</h3>

            <p>
                Deleted staff can be restored within 30 days
                Permanent deletion is only allowed when no linked booking records exist
            </p>

            <div class="staff-list">

                @forelse ($deletedStaff as $staff)

                    @php
                        $permanentDeleteDate = $staff->deleted_at
                            ? $staff->deleted_at->copy()->addDays(30)
                            : null;
                    @endphp

                    <section class="deleted-card" style="padding:20px">

                        <div class="staff-top" style="margin-bottom:0">

                            <div class="staff-main">

                                <div class="staff-avatar">
                                    {{ strtoupper(substr($staff->full_name,0,1)) }}
                                </div>

                                <div>
                                    <h3>{{ $staff->full_name }}</h3>

                                    <p>
                                        Deleted:
                                        {{ $staff->deleted_at?->format('F d, Y h:i A') ?? 'Not recorded' }}
                                    </p>

                                    <p>
                                        Permanent Deletion:
                                        {{ $permanentDeleteDate?->format('F d, Y') ?? 'Not recorded' }}
                                    </p>

                                    <p>
                                        Connected Bookings:
                                        {{ $staff->appointments_count }}
                                    </p>
                                </div>

                            </div>

                            <div class="actions">

                                <form
                                    method="POST"
                                    action="{{ route('admin.staff.restore',$staff->id) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="success-btn"
                                    >
                                        Restore
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('admin.staff.force-delete',$staff->id) }}"
                                    onsubmit="return confirm('Permanently delete this staff member? This action cannot be undone')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="danger-btn"
                                    >
                                        Delete Now
                                    </button>
                                </form>

                            </div>

                        </div>

                    </section>

                @empty

                    <div class="empty-state">
                        <h3>No Deleted Staff</h3>

                        <p>
                            Deleted staff records will appear here
                        </p>
                    </div>

                @endforelse

            </div>

            <div class="modal-actions">

                <button
                    type="button"
                    class="modal-back"
                    onclick="closeStaffModal('deletedStaffModal')"
                >
                    Back
                </button>

            </div>

        </div>

    </div>

</div>

@endsection

@section('scripts')
<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Staff Management Popup Controls
|--------------------------------------------------------------------------
| - Open and close modals
| - Keep scroll position manageable
| - No unnecessary close icons
|--------------------------------------------------------------------------
*/

function openStaffModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.classList.add("show");

    const modalContent = modal.querySelector(".modal");

    if (modalContent) {
        modalContent.scrollTop = 0;
    }

    modal.scrollTop = 0;
}

function closeStaffModal(id) {
    const modal = document.getElementById(id);

    if (modal) {
        modal.classList.remove("show");
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Day Off Confirmation
|--------------------------------------------------------------------------
| Require explicit acknowledgement only when day-off dates are entered
|--------------------------------------------------------------------------
*/

function confirmStaffUpdate(event, staffId) {
    const startInput = document.getElementById(
        "offStart" + staffId
    );

    const endInput = document.getElementById(
        "offEnd" + staffId
    );

    const acknowledgement = document.getElementById(
        "emergencyAcknowledgement" + staffId
    );

    const start = startInput ? startInput.value : "";
    const end = endInput ? endInput.value : "";

    if ((start && !end) || (!start && end)) {
        event.preventDefault();

        alert("Please provide both the start and end dates for the day off");

        return false;
    }

    if (start && end && end < start) {
        event.preventDefault();

        alert("The end date cannot be earlier than the start date");

        return false;
    }

    if (start && end && acknowledgement && !acknowledgement.checked) {
        event.preventDefault();

        alert(
            "Please acknowledge the emergency scheduling information before saving the day off"
        );

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Allow Normal Laravel Form Submission
    |--------------------------------------------------------------------------
    | CSRF and database validation remain on the controller
    |--------------------------------------------------------------------------
    */

    const submitButton = document.getElementById(
        "saveStaffBtn" + staffId
    );

    if (submitButton) {
        submitButton.disabled = true;

        submitButton.textContent = start && end
            ? "Checking Affected Bookings"
            : "Saving Changes";
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Modal Background Controls
|--------------------------------------------------------------------------
*/

document.addEventListener("click", function(event) {
    if (event.target.classList.contains("modal-overlay")) {
        event.target.classList.remove("show");
    }
});

document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".modal-overlay.show").forEach(function(modal) {
            modal.classList.remove("show");
        });
    }
});
</script>
@endsection
