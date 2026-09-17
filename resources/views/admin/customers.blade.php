@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Admin Customer Management View
|
| Purpose:
| - Shows customer accounts.
| - Allows admin to view customer profiles.
| - Allows admin to view customer booking history.
| - Allows admin to block and unblock customer accounts.
|
| Defense explanation:
| This module supports customer account monitoring and security because
| blocking requires admin password confirmation and is recorded in Audit Logs.
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
.customer-card {
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

.filter-card {
    padding: 22px;
    margin-bottom: 24px;
}

.filter-row {
    display: grid;
    grid-template-columns: 1.5fr 1fr auto auto;
    gap: 14px;
    align-items: end;
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

.filter-btn,
.reset-btn,
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

.filter-btn {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.reset-btn,
.view-btn {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.block-btn {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.22);
}

.unblock-btn {
    background: rgba(81,148,91,.16);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.customer-list {
    display: grid;
    gap: 20px;
}

.customer-card {
    padding: 24px;
}

.customer-top {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.customer-main {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.customer-avatar {
    width: 70px;
    height: 70px;
    border-radius: 22px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
    font-size: 20px;
    font-weight: 900;
    flex: 0 0 auto;
}

.customer-top h3 {
    font-size: 18px;
    margin-bottom: 5px;
}

.customer-top p {
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
.account-badge {
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
    background: rgba(255,196,87,.22);
    color: #9a6a00;
}

.status.blocked {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
}

.account-badge.linked {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
}

.account-badge.not-linked {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
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

.info-box,
.block-info {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
    margin-bottom: 18px;
}

.block-info {
    background: rgba(184,77,77,.10);
    border: 1px solid rgba(184,77,77,.22);
}

.info-box small,
.block-info small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
    margin-bottom: 8px;
}

.info-box p,
.block-info p {
    color: #7d6d60;
    font-size: 13px;
    line-height: 1.6;
}

.actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Sidebar Visible During Popups
|--------------------------------------------------------------------------
| Purpose:
| - Keeps the admin sidebar visible when customer popups are opened.
|--------------------------------------------------------------------------
*/
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
    max-width: 620px;
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

.modal-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.modal-detail-box {
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    border-radius: 20px;
    padding: 16px;
}

.modal-detail-box small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
    margin-bottom: 8px;
}

.modal-detail-box p {
    color: var(--dark);
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 0;
    word-break: break-word;
}

.booking-list {
    display: grid;
    gap: 16px;
}

.booking-mini-card {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
}

.booking-mini-card h4 {
    font-size: 16px;
    margin-bottom: 8px;
}

.booking-mini-card p {
    margin-bottom: 6px;
    font-size: 13px;
}

.service-chip {
    display: inline-block;
    margin: 4px 6px 4px 0;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    font-size: 13px;
    font-weight: 700;
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
    .details-grid,
    .modal-details-grid {
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

    .customer-top,
    .customer-main {
        flex-direction: column;
    }

    .customer-avatar {
        width: 100%;
        height: 110px;
    }

    .filter-btn,
    .reset-btn,
    .action-btn,
    .modal-danger,
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
    <h1>Customer Management</h1>
    <p>View customer profiles, booking records, and manage account restrictions.</p>
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
        <p>Total Customers</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['active'] }}</h2>
        <p>Active Customers</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['inactive'] }}</h2>
        <p>Inactive Customers</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['blocked'] }}</h2>
        <p>Blocked Customers</p>
    </div>
</div>

<section class="filter-card">
    <form method="GET" action="{{ route('admin.customers') }}">
        <div class="filter-row">
            <div>
                <label>Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search name, email, or phone number..."
                >
            </div>

            <div>
                <label>Status</label>
                <select name="status">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="blocked" {{ $filters['status'] === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>

            <button type="submit" class="filter-btn">
                Filter
            </button>

            <a href="{{ route('admin.customers') }}" class="reset-btn">
                Reset
            </a>
        </div>
    </form>
</section>

<div class="customer-list">
    @forelse ($customers as $customer)
        @php
            $statusKey = strtolower($customer->status ?? 'active');
            $statusLabel = ucfirst($statusKey);

            $initials = collect(explode(' ', $customer->full_name))
                ->filter()
                ->take(2)
                ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                ->implode('');

            $latestBooking = $customer->appointments->first();

            $latestBookingDate = $latestBooking && $latestBooking->appointment_date
                ? \Carbon\Carbon::parse($latestBooking->appointment_date)->format('F d, Y')
                : 'No booking yet';

            $latestBookingTime = $latestBooking && $latestBooking->start_time
                ? \Carbon\Carbon::parse($latestBooking->start_time)->format('h:i A')
                : 'No time recorded';

            $accountBadgeClass = $customer->user ? 'linked' : 'not-linked';
            $accountBadgeLabel = $customer->user ? 'Linked Account' : 'No Linked Account';
        @endphp

        <section class="customer-card">
            <div class="customer-top">
                <div class="customer-main">
                    <div class="customer-avatar">
                        {{ $initials ?: 'C' }}
                    </div>

                    <div>
                        <h3>{{ $customer->full_name }}</h3>

                        <p>
                            Customer ID:
                            #CU-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}
                        </p>

                        <p>
                            Registered:
                            {{ $customer->created_at ? $customer->created_at->format('F d, Y h:i A') : 'Not recorded' }}
                        </p>
                    </div>
                </div>

                <div class="badge-row">
                    <span class="account-badge {{ $accountBadgeClass }}">
                        {{ $accountBadgeLabel }}
                    </span>

                    <span class="status {{ $statusKey }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-box">
                    <small>EMAIL</small>
                    <p>{{ $customer->email ?: 'Not recorded' }}</p>
                </div>

                <div class="detail-box">
                    <small>PHONE</small>
                    <p>{{ $customer->phone_number ?: 'Not recorded' }}</p>
                </div>

                <div class="detail-box">
                    <small>TOTAL BOOKINGS</small>
                    <p>{{ $customer->appointments_count }} booking(s)</p>
                </div>

                <div class="detail-box">
                    <small>LATEST BOOKING</small>
                    <p>{{ $latestBookingDate }}</p>
                </div>
            </div>

            <div class="info-box">
                <small>CUSTOMER ADDRESS</small>
                <p>{{ $customer->address ?: 'No address recorded.' }}</p>
            </div>

            @if ($statusKey === 'blocked')
                <div class="block-info">
                    <small>BLOCK RECORD</small>

                    <p>
                        <strong>Reason:</strong>
                        {{ $customer->block_reason ?: 'No reason recorded.' }}
                    </p>

                    <p>
                        <strong>Blocked By:</strong>
                        {{ $customer->blockedBy?->name ?? 'Unknown admin' }}
                    </p>

                    <p>
                        <strong>Blocked At:</strong>
                        {{ $customer->blocked_at ? $customer->blocked_at->format('F d, Y h:i A') : 'Not recorded' }}
                    </p>
                </div>
            @endif

            <div class="actions">
                <button type="button" class="action-btn view-btn" onclick="openCustomerModal('profileModal{{ $customer->id }}')">
                    View Profile
                </button>

                <button type="button" class="action-btn view-btn" onclick="openCustomerModal('bookingsModal{{ $customer->id }}')">
                    View Bookings
                </button>

                @if ($statusKey === 'blocked')
                    <button type="button" class="action-btn unblock-btn" onclick="openCustomerModal('unblockModal{{ $customer->id }}')">
                        Unblock Account
                    </button>
                @else
                    <button type="button" class="action-btn block-btn" onclick="openCustomerModal('blockModal{{ $customer->id }}')">
                        Block Account
                    </button>
                @endif
            </div>
        </section>
    @empty
        <div class="empty-state">
            <h3>No customers found</h3>
            <p>There are no customers matching the current filters.</p>
        </div>
    @endforelse
</div>

<div class="pagination">
    {{ $customers->links() }}
</div>

@foreach ($customers as $customer)
    @php
        $statusKey = strtolower($customer->status ?? 'active');
    @endphp

    <div class="modal-overlay" id="profileModal{{ $customer->id }}">
        <div class="modal large">
            <h3>Customer Profile</h3>

            <p>
                Basic account and contact information for
                <strong>{{ $customer->full_name }}</strong>.
            </p>

            <div class="modal-details-grid">
                <div class="modal-detail-box">
                    <small>FULL NAME</small>
                    <p>{{ $customer->full_name }}</p>
                </div>

                <div class="modal-detail-box">
                    <small>EMAIL</small>
                    <p>{{ $customer->email ?: 'Not recorded' }}</p>
                </div>

                <div class="modal-detail-box">
                    <small>PHONE NUMBER</small>
                    <p>{{ $customer->phone_number ?: 'Not recorded' }}</p>
                </div>

                <div class="modal-detail-box">
                    <small>ADDRESS</small>
                    <p>{{ $customer->address ?: 'Not recorded' }}</p>
                </div>

                <div class="modal-detail-box">
                    <small>STATUS</small>
                    <p>{{ ucfirst($statusKey) }}</p>
                </div>

                <div class="modal-detail-box">
                    <small>LINKED USER ACCOUNT</small>
                    <p>{{ $customer->user ? 'Linked to users table' : 'No linked user account' }}</p>
                </div>

                <div class="modal-detail-box">
                    <small>REGISTERED DATE</small>
                    <p>{{ $customer->created_at ? $customer->created_at->format('F d, Y h:i A') : 'Not recorded' }}</p>
                </div>

                <div class="modal-detail-box">
                    <small>TOTAL BOOKINGS</small>
                    <p>{{ $customer->appointments_count }} booking(s)</p>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="modal-back" onclick="closeCustomerModal('profileModal{{ $customer->id }}')">
                    Back
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="bookingsModal{{ $customer->id }}">
        <div class="modal large">
            <h3>Customer Bookings</h3>

            <p>
                Appointment records connected to
                <strong>{{ $customer->full_name }}</strong>.
            </p>

            <div class="booking-list">
                @forelse ($customer->appointments as $appointment)
                    @php
                        $bookingStatus = strtolower(str_replace(' ', '-', $appointment->status ?? 'pending'));
                        $bookingDate = $appointment->appointment_date
                            ? \Carbon\Carbon::parse($appointment->appointment_date)->format('F d, Y')
                            : 'Not set';

                        $bookingTime = $appointment->start_time
                            ? \Carbon\Carbon::parse($appointment->start_time)->format('h:i A')
                            : 'No time';

                        $bookingType = $appointment->booking_type === 'followup'
                            ? 'Backjob / Follow-up'
                            : 'Regular Booking';
                    @endphp

                    <div class="booking-mini-card">
                        <h4>
                            Booking #BK-{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}
                        </h4>

                        <p>
                            <strong>Type:</strong>
                            {{ $bookingType }}
                        </p>

                        <p>
                            <strong>Status:</strong>
                            {{ ucwords(str_replace('-', ' ', $bookingStatus)) }}
                        </p>

                        <p>
                            <strong>Date and Time:</strong>
                            {{ $bookingDate }} at {{ $bookingTime }}
                        </p>

                        <p>
                            <strong>Staff:</strong>
                            {{ $appointment->staff?->full_name ?? 'Not assigned' }}
                        </p>

                        <p>
                            <strong>Total:</strong>
                            RM {{ number_format($appointment->total_price ?? 0, 2) }}
                        </p>

                        <p>
                            <strong>Services:</strong>
                        </p>

                        @forelse ($appointment->services as $service)
                            <span class="service-chip">
                                {{ $service->service_name }}
                            </span>
                        @empty
                            <span class="service-chip">
                                No service recorded
                            </span>
                        @endforelse
                    </div>
                @empty
                    <div class="empty-state">
                        <h3>No bookings found</h3>
                        <p>This customer has no booking records yet.</p>
                    </div>
                @endforelse
            </div>

            <div class="modal-actions">
                <button type="button" class="modal-back" onclick="closeCustomerModal('bookingsModal{{ $customer->id }}')">
                    Back
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="blockModal{{ $customer->id }}">
        <div class="modal">
            <h3>Block Customer Account</h3>

            <div class="warning-box">
                <strong>Are you sure you want to block this customer?</strong>
                <span>
                    This customer will be restricted from using the system.
                    Admin password is required and the action will be recorded in Audit Logs.
                </span>
            </div>

            <form method="POST" action="{{ route('admin.customers.block', $customer) }}">
                @csrf
                @method('PATCH')

                <label>Customer</label>
                <input type="text" value="{{ $customer->full_name }}" readonly>

                <label>Block Reason</label>
                <textarea name="block_reason" placeholder="Enter reason for blocking this customer account." required></textarea>

                <label>Admin Password</label>
                <input type="password" name="admin_password" placeholder="Enter your admin password" required>

                <div class="modal-actions">
                    <button type="submit" class="modal-danger">
                        Block Account
                    </button>

                    <button type="button" class="modal-back" onclick="closeCustomerModal('blockModal{{ $customer->id }}')">
                        Back
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="unblockModal{{ $customer->id }}">
        <div class="modal">
            <h3>Unblock Customer Account</h3>

            <p>
                Admin password is required before unblocking
                <strong>{{ $customer->full_name }}</strong>.
            </p>

            <form method="POST" action="{{ route('admin.customers.unblock', $customer) }}">
                @csrf
                @method('PATCH')

                <label>Customer</label>
                <input type="text" value="{{ $customer->full_name }}" readonly>

                <label>Admin Password</label>
                <input type="password" name="admin_password" placeholder="Enter your admin password" required>

                <div class="modal-actions">
                    <button type="submit" class="modal-success">
                        Unblock Account
                    </button>

                    <button type="button" class="modal-back" onclick="closeCustomerModal('unblockModal{{ $customer->id }}')">
                        Back
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@endsection

@section('scripts')
<script>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Customer Modal Script
|--------------------------------------------------------------------------
| Purpose:
| - Opens customer profile popup.
| - Opens customer booking history popup.
| - Opens block/unblock account popup.
|--------------------------------------------------------------------------
*/
function openCustomerModal(id) {
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

function closeCustomerModal(id) {
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