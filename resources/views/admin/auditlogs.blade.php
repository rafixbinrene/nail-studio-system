@extends('layouts.app')

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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card,
.filter-card,
.log-card {
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
    font-weight: 800;
}

.filter-card {
    padding: 22px;
    margin-bottom: 24px;
}

.filter-row {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1fr 1fr auto auto;
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
select {
    width: 100%;
    padding: 13px 14px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    outline: none;
}

.filter-btn,
.reset-btn {
    padding: 13px 18px;
    border-radius: 999px;
    border: none;
    text-decoration: none;
    font-weight: 900;
    cursor: pointer;
    white-space: nowrap;
}

.filter-btn {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

.reset-btn {
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--dark);
}

.logs-list {
    display: grid;
    gap: 16px;
}

.log-card {
    padding: 22px;
}

.log-top {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 16px;
}

.log-top h3 {
    font-size: 17px;
    margin-bottom: 5px;
}

.log-top p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
}

.badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.badge {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    height: fit-content;
}

.badge.customer {
    background: rgba(82,139,255,.15);
    color: #2f5fb8;
}

.badge.staff {
    background: rgba(138,113,88,.18);
    color: #6f4e37;
}

.badge.admin {
    background: rgba(184,77,77,.12);
    color: #b84d4d;
}

.badge.module {
    background: rgba(255,255,255,.55);
    color: var(--dark);
    border: 1px solid var(--border);
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-top: 16px;
}

.detail-box {
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
    border-radius: 18px;
    padding: 14px;
}

.detail-box small {
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
}

.detail-box p {
    margin-top: 6px;
    font-size: 13px;
    font-weight: 700;
    word-break: break-word;
}

.description-box {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
    margin-top: 14px;
}

.description-box small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
    margin-bottom: 8px;
}

.description-box p {
    color: #7d6d60;
    font-size: 13px;
    line-height: 1.6;
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

.pagination {
    margin-top: 24px;
}

@media(max-width:1200px) {
    .filter-row {
        grid-template-columns: 1fr 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .details-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width:700px) {
    .filter-row,
    .stats-grid,
    .details-grid {
        grid-template-columns: 1fr;
    }

    .log-top {
        flex-direction: column;
    }

    .badges {
        justify-content: flex-start;
    }

    .filter-btn,
    .reset-btn {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>Audit Logs</h1>
    <p>Track system actions from customers, staff, and administrators.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h2>{{ $stats['total'] }}</h2>
        <p>Total Logs</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['today'] }}</h2>
        <p>Today</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['customer'] }}</h2>
        <p>Customer Actions</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['staff'] }}</h2>
        <p>Staff Actions</p>
    </div>

    <div class="stat-card">
        <h2>{{ $stats['admin'] }}</h2>
        <p>Admin Actions</p>
    </div>
</div>

<section class="filter-card">
    <form method="GET" action="{{ route('admin.auditlogs') }}">
        <div class="filter-row">
            <div>
                <label>Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search user, action, module, IP..."
                >
            </div>

            <div>
                <label>User Type</label>
                <select name="user_type">
                    <option value="all" {{ $filters['user_type'] === 'all' ? 'selected' : '' }}>All</option>
                    <option value="customer" {{ $filters['user_type'] === 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="staff" {{ $filters['user_type'] === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="admin" {{ $filters['user_type'] === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div>
                <label>Module</label>
                <select name="module">
                    <option value="all" {{ $filters['module'] === 'all' ? 'selected' : '' }}>All</option>

                    @foreach ($modules as $module)
                        <option value="{{ $module }}" {{ $filters['module'] === $module ? 'selected' : '' }}>
                            {{ $module }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Action</label>
                <select name="action">
                    <option value="all" {{ $filters['action'] === 'all' ? 'selected' : '' }}>All</option>

                    @foreach ($actions as $action)
                        <option value="{{ $action }}" {{ $filters['action'] === $action ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', ucwords($action, '_')) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Date</label>
                <input type="date" name="date" value="{{ $filters['date'] }}">
            </div>

            <button type="submit" class="filter-btn">
                Filter
            </button>

            <a href="{{ route('admin.auditlogs') }}" class="reset-btn">
                Reset
            </a>
        </div>
    </form>
</section>

<div class="logs-list">
    @forelse ($logs as $log)
        @php
            $userType = strtolower($log->user_type ?? 'system');
            $createdAt = $log->created_at
                ? \Carbon\Carbon::parse($log->created_at)->format('F d, Y h:i A')
                : 'Not recorded';

            $actionLabel = str_replace('_', ' ', ucwords($log->action ?? 'System Action', '_'));
        @endphp

        <section class="log-card">
            <div class="log-top">
                <div>
                    <h3>{{ $actionLabel }}</h3>

                    <p>
                        Log ID:
                        #LOG-{{ str_pad($log->id, 4, '0', STR_PAD_LEFT) }}
                    </p>

                    <p>
                        Recorded:
                        {{ $createdAt }}
                    </p>
                </div>

                <div class="badges">
                    <span class="badge {{ $userType }}">
                        {{ ucfirst($userType) }}
                    </span>

                    <span class="badge module">
                        {{ $log->module ?? 'System' }}
                    </span>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-box">
                    <small>USER NAME</small>
                    <p>{{ $log->user?->name ?? 'System / Unknown' }}</p>
                </div>

                <div class="detail-box">
                    <small>USER EMAIL</small>
                    <p>{{ $log->user?->email ?? 'Not recorded' }}</p>
                </div>

                <div class="detail-box">
                    <small>USER TYPE</small>
                    <p>{{ ucfirst($userType) }}</p>
                </div>

                <div class="detail-box">
                    <small>MODULE</small>
                    <p>{{ $log->module ?? 'System' }}</p>
                </div>

                <div class="detail-box">
                    <small>ACTION</small>
                    <p>{{ $log->action ?? 'Not recorded' }}</p>
                </div>

                <div class="detail-box">
                    <small>IP ADDRESS</small>
                    <p>{{ $log->ip_address ?? 'Not recorded' }}</p>
                </div>

                <div class="detail-box">
                    <small>DATE</small>
                    <p>{{ $createdAt }}</p>
                </div>

                <div class="detail-box">
                    <small>USER ID</small>
                    <p>{{ $log->user_id ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="description-box">
                <small>DESCRIPTION</small>
                <p>{{ $log->description ?? 'No description recorded.' }}</p>
            </div>
        </section>
    @empty
        <div class="empty-state">
            <h3>No audit logs found</h3>
            <p>No system activity matched your current filters.</p>
        </div>
    @endforelse
</div>

<div class="pagination">
    {{ $logs->links() }}
</div>

@endsection