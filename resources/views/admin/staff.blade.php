@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Admin Staff Management View
|
| Purpose:
| - Shows staff profiles.
| - Allows admin to add, edit, enable, disable, and delete staff.
| - Allows admin to assign existing services from Service Management to staff.
| - Allows admin to set staff day-off date ranges.
|
| Defense explanation:
| This module supports staff scheduling because the admin can assign services
| to staff based on the official services created in Service Management.
| This prevents duplicate service records and keeps service assignment clean.
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
.page-header {
    margin-bottom: 28px;
}

.page-header h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.page-header p {
    color: var(--muted);
    font-size: 14px;
}

.alert {
    padding: 14px 16px;
    border-radius: 18px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 700;
}

.alert-success {
    background: rgba(81,148,91,.16);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.alert-error {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card,
.filter-card,
.form-card,
.staff-card,
.deleted-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 26px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.stat-card {
    padding: 20px;
}

.stat-card h2 {
    font-size: 28px;
    color: var(--brown);
}

.stat-card p {
    color: var(--muted);
    font-size: 12px;
    margin-top: 6px;
    font-weight: 700;
}

.filter-card,
.form-card {
    padding: 22px;
    margin-bottom: 24px;
}

.filter-row {
    display: grid;
    grid-template-columns: 1.5fr 1fr auto auto;
    gap: 14px;
    align-items: end;
}

.card-title {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 20px;
}

.card-title h2 {
    font-size: 22px;
    margin-bottom: 6px;
}

.card-title p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
}

.form-grid {
    display: grid;
    grid-template-columns: 1.2fr 1.2fr .9fr .8fr;
    gap: 14px;
    align-items: end;
}

.form-group.full {
    grid-column: 1 / -1;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 800;
}

input,
select,
textarea {
    width: 100%;
    padding: 13px 14px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    outline: none;
}

textarea {
    resize: none;
    height: 95px;
}

.help {
    display: block;
    margin-top: 7px;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.4;
}

.error-text {
    display: block;
    color: #b84d4d;
    font-size: 12px;
    margin-top: 6px;
    font-weight: 800;
}

.filter-btn,
.reset-btn,
.primary-btn,
.light-btn,
.danger-btn,
.success-btn,
.status-btn,
.action-btn {
    border: none;
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.filter-btn,
.primary-btn {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.reset-btn,
.light-btn {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.danger-btn {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.success-btn {
    background: rgba(81,148,91,.16);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.status-btn {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
    border: 1px solid rgba(138,113,88,.22);
}

.form-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
    margin-top: 18px;
}

.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}

.checkbox-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.45);
    border: 1px solid var(--border);
    color: var(--dark);
    font-size: 13px;
    font-weight: 800;
}

.checkbox-pill input {
    width: auto;
}

.staff-list {
    display: grid;
    gap: 20px;
}

.staff-card {
    padding: 24px;
}

.staff-top {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.staff-main {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.staff-avatar {
    width: 76px;
    height: 76px;
    border-radius: 22px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-size: 20px;
    font-weight: 900;
    flex: 0 0 auto;
}

.staff-top h3 {
    font-size: 18px;
    margin-bottom: 5px;
}

.staff-top p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
}

.badge-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.status,
.account-badge,
.dayoff-badge {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    height: fit-content;
    white-space: nowrap;
}

.status.active {
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
}

.status.inactive {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

.account-badge {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.dayoff-badge {
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 18px;
}

.detail-box {
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    border-radius: 20px;
    padding: 16px;
}

.detail-box small {
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
}

.detail-box p {
    margin-top: 6px;
    font-size: 14px;
    font-weight: 700;
    word-break: break-word;
}

.skill-box,
.dayoff-box {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
    margin-bottom: 18px;
}

.dayoff-box {
    background: rgba(255,196,87,.10);
    border: 1px solid rgba(255,196,87,.22);
}

.skill-box small,
.dayoff-box small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
    margin-bottom: 8px;
}

.skill-chip,
.dayoff-chip {
    display: inline-block;
    margin: 4px 6px 4px 0;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    font-size: 13px;
    font-weight: 700;
}

.dayoff-chip {
    background: rgba(255,196,87,.16);
    color: #9a6a00;
    border: 1px solid rgba(255,196,87,.25);
}

.actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.deleted-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin: 28px 0 18px;
}

.deleted-header h2 {
    font-size: 22px;
}

.deleted-header p {
    color: var(--muted);
    font-size: 13px;
    margin-top: 5px;
}

.pagination {
    margin-top: 24px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--glass);
    border: 1px solid var(--border);
    border-radius: 30px;
    color: var(--muted);
}

.empty-state h3 {
    color: var(--dark);
    margin-bottom: 8px;
}

.sidebar,
.admin-sidebar,
.app-sidebar,
aside.sidebar,
#sidebar {
    z-index: 10000 !important;
}

.modal-overlay {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 280px;
    background: rgba(47,33,24,.35);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    z-index: 999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
}

.modal-overlay.show {
    display: flex;
}

.modal {
    width: 100%;
    max-width: 640px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(255,255,255,.9);
    border-radius: 30px;
    padding: 28px;
    box-shadow:
        0 25px 80px rgba(47,33,24,.20),
        inset 0 1px 0 rgba(255,255,255,.8);
}

.modal.large {
    max-width: 900px;
}

.modal h3 {
    margin-bottom: 10px;
    font-size: 22px;
}

.modal p {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 18px;
}

.modal-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.modal-grid .full {
    grid-column: 1 / -1;
}

.dayoff-list {
    display: grid;
    gap: 12px;
    margin-top: 10px;
}

.dayoff-item {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    padding: 14px;
    border-radius: 20px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
}

.dayoff-item p {
    margin-bottom: 0;
}

.warning-box {
    padding: 16px;
    border-radius: 20px;
    background: rgba(184,77,77,.12);
    border: 1px solid rgba(184,77,77,.22);
    margin-bottom: 18px;
}

.warning-box strong {
    color: #b84d4d;
    display: block;
    margin-bottom: 6px;
}

.warning-box span {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.6;
}

.modal-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 18px;
}

.modal-danger {
    padding: 13px 20px;
    border-radius: 999px;
    border: none;
    background: #b84d4d;
    color: white;
    font-weight: 900;
    cursor: pointer;
}

.modal-primary {
    padding: 13px 20px;
    border-radius: 999px;
    border: none;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-weight: 900;
    cursor: pointer;
}

.modal-success {
    padding: 13px 20px;
    border-radius: 999px;
    border: none;
    background: #2f7d3c;
    color: white;
    font-weight: 900;
    cursor: pointer;
}

.modal-back {
    padding: 13px 20px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    color: var(--dark);
    font-weight: 900;
    cursor: pointer;
}

@media(max-width:1100px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .filter-row,
    .form-grid,
    .details-grid,
    .modal-grid,
    .checkbox-grid {
        grid-template-columns: 1fr;
    }

    .badge-row {
        justify-content: flex-start;
    }
}

@media(max-width:900px) {
    .modal-overlay {
        left: 0;
    }
}

@media(max-width:700px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .staff-top,
    .staff-main,
    .deleted-header,
    .dayoff-item {
        flex-direction: column;
        align-items: stretch;
    }

    .staff-avatar {
        width: 100%;
        height: 110px;
    }

    .filter-btn,
    .reset-btn,
    .primary-btn,
    .light-btn,
    .danger-btn,
    .success-btn,
    .status-btn,
    .action-btn,
    .modal-danger,
    .modal-primary,
    .modal-success,
    .modal-back {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>Staff Management</h1>
    <p>Manage staff profiles, assigned services from Service Management, account status, and day-off schedules.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
    </div>
@endif

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

<section class="filter-card">
    <form method="GET" action="{{ route('admin.staff') }}">
        <div class="filter-row">
            <div>
                <label>Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search staff name, email, or phone number..."
                >
            </div>

            <div>
                <label>Status</label>
                <select name="status">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="filter-btn">Filter</button>

            <a href="{{ route('admin.staff') }}" class="reset-btn">Reset</a>
        </div>
    </form>
</section>

<section class="form-card">
    <div class="card-title">
        <div>
            <h2>Add New Staff</h2>
            <p>Create a staff profile and assign services that already exist in Service Management.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.staff.store') }}">
        @csrf

        <div class="form-grid">
            <div>
                <label>Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" maxlength="150" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" maxlength="150" required>
            </div>

            <div>
                <label>Phone Number</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" maxlength="30">
            </div>

            <div>
                <label>Status</label>
                <select name="status" required>
                    <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Services from Service Management</label>

                <div class="checkbox-grid">
                    @forelse ($services as $service)
                        <label class="checkbox-pill">
                            <input
                                type="checkbox"
                                name="service_ids[]"
                                value="{{ $service->id }}"
                                {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}
                            >
                            {{ $service->service_name }}
                        </label>
                    @empty
                        <small class="help">No services found. Please add services in Service Management first.</small>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="reset" class="light-btn">Clear</button>
            <button type="submit" class="primary-btn">Add Staff</button>
        </div>
    </form>
</section>

<div class="deleted-header">
    <div>
        <h2>Staff List</h2>
        <p>Manage active staff. Deleted staff can be restored within 30 days.</p>
    </div>

    <button type="button" class="light-btn" onclick="openStaffModal('deletedStaffModal')">
        Deleted Staff
        @if ($deletedStaff->count() > 0)
            ({{ $deletedStaff->count() }})
        @endif
    </button>
</div>

<div class="staff-list">
    @forelse ($staffMembers as $staff)
        @php
            $statusKey = strtolower($staff->status ?? 'Active');

            $initials = collect(explode(' ', $staff->full_name))
                ->filter()
                ->take(2)
                ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                ->implode('');

            $hasDayOff = $staff->activeDayOffs->count() > 0;
        @endphp

        <section class="staff-card">
            <div class="staff-top">
                <div class="staff-main">
                    <div class="staff-avatar">
                        {{ $initials ?: 'S' }}
                    </div>

                    <div>
                        <h3>{{ $staff->full_name }}</h3>

                        <p>
                            Staff ID:
                            #ST-{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}
                        </p>

                        <p>
                            Created:
                            {{ $staff->created_at ? $staff->created_at->format('F d, Y h:i A') : 'Not recorded' }}
                        </p>
                    </div>
                </div>

                <div class="badge-row">
                    @if ($hasDayOff)
                        <span class="dayoff-badge">Has Day Off</span>
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
                    <p>{{ $staff->appointments_count }} booking(s)</p>
                </div>

                <div class="detail-box">
                    <small>ASSIGNED SERVICES</small>
                    <p>{{ $staff->services->count() }} service(s)</p>
                </div>
            </div>

            <div class="skill-box">
                <small>ASSIGNED SERVICES FROM SERVICE MANAGEMENT</small>

                @forelse ($staff->services as $service)
                    <span class="skill-chip">{{ $service->service_name }}</span>
                @empty
                    <span class="skill-chip">No services assigned</span>
                @endforelse
            </div>

            @if ($hasDayOff)
                <div class="dayoff-box">
                    <small>ACTIVE / UPCOMING DAY OFF</small>

                    @foreach ($staff->activeDayOffs as $dayOff)
                        <span class="dayoff-chip">
                            {{ $dayOff->start_date->format('M d, Y') }}
                            -
                            {{ $dayOff->end_date->format('M d, Y') }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="actions">
                <button type="button" class="action-btn light-btn" onclick="openStaffModal('editStaffModal{{ $staff->id }}')">
                    Edit Staff
                </button>

                <form method="POST" action="{{ route('admin.staff.status', $staff) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="action-btn status-btn">
                        {{ $staff->status === 'Active' ? 'Disable Staff' : 'Enable Staff' }}
                    </button>
                </form>

                <button type="button" class="action-btn danger-btn" onclick="openStaffModal('deleteStaffModal{{ $staff->id }}')">
                    Delete Staff
                </button>
            </div>
        </section>
    @empty
        <div class="empty-state">
            <h3>No staff found</h3>
            <p>There are no staff records matching the current filters.</p>
        </div>
    @endforelse
</div>

<div class="pagination">
    {{ $staffMembers->links() }}
</div>

@foreach ($staffMembers as $staff)
    <div class="modal-overlay" id="editStaffModal{{ $staff->id }}">
        <div class="modal large">
            <h3>Edit Staff</h3>

            <p>
                Update staff profile, assigned services, and optional day-off schedule.
            </p>

            <form method="POST" action="{{ route('admin.staff.update', $staff) }}">
                @csrf
                @method('PATCH')

                <div class="modal-grid">
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $staff->full_name) }}" maxlength="150" required>
                    </div>

                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $staff->email) }}" maxlength="150" required>
                    </div>

                    <div>
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $staff->phone_number) }}" maxlength="30">
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Active" {{ old('status', $staff->status) === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status', $staff->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="full">
                        <label>Services from Service Management</label>

                        <div class="checkbox-grid">
                            @forelse ($services as $service)
                                <label class="checkbox-pill">
                                    <input
                                        type="checkbox"
                                        name="service_ids[]"
                                        value="{{ $service->id }}"
                                        {{ $staff->services->contains('id', $service->id) ? 'checked' : '' }}
                                    >
                                    {{ $service->service_name }}
                                </label>
                            @empty
                                <small class="help">No services found. Please add services in Service Management first.</small>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label>Day Off Start Date</label>
                        <input type="date" name="off_start_date" min="{{ now()->toDateString() }}">
                    </div>

                    <div>
                        <label>Day Off End Date</label>
                        <input type="date" name="off_end_date" min="{{ now()->toDateString() }}">
                    </div>

                    <div class="full">
                        <label>Day Off Reason</label>
                        <textarea name="off_reason" placeholder="Optional reason for staff day off."></textarea>
                        <small class="help">For one-day day off, use the same start and end date.</small>
                    </div>
                </div>

                @if ($staff->activeDayOffs->count() > 0)
                    <div class="dayoff-list">
                        @foreach ($staff->activeDayOffs as $dayOff)
                            <div class="dayoff-item">
                                <p>
                                    <strong>
                                        {{ $dayOff->start_date->format('M d, Y') }}
                                        -
                                        {{ $dayOff->end_date->format('M d, Y') }}
                                    </strong>
                                    <br>
                                    {{ $dayOff->reason ?: 'No reason provided.' }}
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
                    <button type="submit" class="modal-primary">
                        Save Changes
                    </button>

                    <button type="button" class="modal-back" onclick="closeStaffModal('editStaffModal{{ $staff->id }}')">
                        Back
                    </button>
                </div>
            </form>

            @foreach ($staff->activeDayOffs as $dayOff)
                <form
                    id="deleteDayOffForm{{ $dayOff->id }}"
                    method="POST"
                    action="{{ route('admin.staff.day-offs.destroy', $dayOff) }}"
                    style="display:none;"
                >
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </div>
    </div>

    <div class="modal-overlay" id="deleteStaffModal{{ $staff->id }}">
        <div class="modal">
            <h3>Delete Staff</h3>

            <div class="warning-box">
                <strong>Are you sure you want to delete this staff?</strong>
                <span>
                    This staff will be moved to Deleted Staff first.
                    It can be restored within 30 days.
                </span>
            </div>

            <p>
                Staff:
                <strong>{{ $staff->full_name }}</strong>
            </p>

            <form method="POST" action="{{ route('admin.staff.destroy', $staff) }}">
                @csrf
                @method('DELETE')

                <div class="modal-actions">
                    <button type="submit" class="modal-danger">
                        Delete Staff
                    </button>

                    <button type="button" class="modal-back" onclick="closeStaffModal('deleteStaffModal{{ $staff->id }}')">
                        Back
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<div class="modal-overlay" id="deletedStaffModal">
    <div class="modal large">
        <h3>Deleted Staff</h3>

        <p>
            Deleted staff can be restored within 30 days.
            Permanent deletion is only allowed when no booking records are connected.
        </p>

        <div class="staff-list">
            @forelse ($deletedStaff as $staff)
                @php
                    $permanentDeleteDate = $staff->deleted_at
                        ? $staff->deleted_at->copy()->addDays(30)
                        : null;
                @endphp

                <section class="deleted-card" style="padding:20px;">
                    <div class="staff-top" style="margin-bottom:0;">
                        <div class="staff-main">
                            <div class="staff-avatar">
                                {{ strtoupper(substr($staff->full_name, 0, 1)) }}
                            </div>

                            <div>
                                <h3>{{ $staff->full_name }}</h3>

                                <p>
                                    Deleted:
                                    {{ $staff->deleted_at ? $staff->deleted_at->format('F d, Y h:i A') : 'Not recorded' }}
                                </p>

                                <p>
                                    Permanent Deletion:
                                    {{ $permanentDeleteDate ? $permanentDeleteDate->format('F d, Y') : 'Not recorded' }}
                                </p>

                                <p>
                                    Connected Bookings:
                                    {{ $staff->appointments_count }}
                                </p>
                            </div>
                        </div>

                        <div class="actions">
                            <form method="POST" action="{{ route('admin.staff.restore', $staff->id) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="success-btn">
                                    Restore
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.staff.force-delete', $staff->id) }}" onsubmit="return confirm('Permanently delete this staff now? This cannot be undone.')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="danger-btn">
                                    Delete Now
                                </button>
                            </form>
                        </div>
                    </div>
                </section>
            @empty
                <div class="empty-state">
                    <h3>No deleted staff</h3>
                    <p>Deleted staff records will appear here.</p>
                </div>
            @endforelse
        </div>

        <div class="modal-actions">
            <button type="button" class="modal-back" onclick="closeStaffModal('deletedStaffModal')">
                Back
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Staff Modal Script
|--------------------------------------------------------------------------
| Purpose:
| - Opens edit staff popup.
| - Opens delete staff popup.
| - Opens deleted staff popup.
|--------------------------------------------------------------------------
*/
function openStaffModal(id) {
    const modal = document.getElementById(id);

    if (modal) {
        modal.classList.add('show');

        const modalBox = modal.querySelector('.modal');

        if (modalBox) {
            modalBox.scrollTop = 0;
        }

        modal.scrollTop = 0;
    }
}

function closeStaffModal(id) {
    const modal = document.getElementById(id);

    if (modal) {
        modal.classList.remove('show');
    }
}

document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('show');
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(function(modal) {
            modal.classList.remove('show');
        });
    }
});
</script>
@endsection