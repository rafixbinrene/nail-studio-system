
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Admin Service Management View
|--------------------------------------------------------------------------
| Purpose:
| - Add and edit beauty services.
| - Manage service availability.
| - Display service information without images.
| - Move services to Trash and restore them.
| - Display automatic deletion dates.
|
| Defense Explanation:
| All service records are managed through the central database.
| Service information is presented consistently through text-based
| details without requiring service images.
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: General Page Design
    |--------------------------------------------------------------------------
    */

    .page-header {
        margin-bottom: 28px;
    }

    .page-header h1 {
        font-size: 34px;
        margin-bottom: 8px;
        color: var(--dark);
    }

    .page-header p {
        color: var(--muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .alert {
        padding: 14px 16px;
        border-radius: 18px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 700;
    }

    .alert-success {
        background: rgba(81, 148, 91, .16);
        color: #2f7d3c;
        border: 1px solid rgba(81, 148, 91, .25);
    }

    .alert-error {
        background: rgba(184, 77, 77, .12);
        color: #b84d4d;
        border: 1px solid rgba(184, 77, 77, .22);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Statistics Cards
    |--------------------------------------------------------------------------
    */

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card,
    .form-card,
    .service-card,
    .deleted-card {
        background: var(--glass);
        backdrop-filter: blur(28px);
        border: 1px solid var(--border);
        border-radius: 26px;
        box-shadow:
            0 20px 60px rgba(47, 33, 24, .08),
            inset 0 1px 0 rgba(255, 255, 255, .7);
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

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Add Service Form
    |--------------------------------------------------------------------------
    | Service images are permanently removed from this interface.
    |--------------------------------------------------------------------------
    */

    .form-card {
        padding: 22px;
        margin-bottom: 24px;
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
        grid-template-columns: 1.5fr 1fr 1fr 1fr;
        gap: 14px;
        align-items: start;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 800;
        color: var(--dark);
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 13px 14px;
        border-radius: 16px;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, .65);
        outline: none;
        color: var(--dark);
        font-family: inherit;
        font-size: 14px;
    }

    input,
    select {
        min-height: 45px;
    }

    input:focus,
    select:focus,
    textarea:focus {
        border-color: var(--brown);
        background: rgba(255, 255, 255, .9);
    }

    textarea {
        resize: vertical;
        min-height: 95px;
        line-height: 1.6;
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

    .form-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin-top: 18px;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Buttons
    |--------------------------------------------------------------------------
    */

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
        transition: .2s ease;
    }

    .primary-btn {
        background: linear-gradient(135deg, #8a7158, #5d3f2c);
        color: white;
    }

    .light-btn {
        background: rgba(255, 255, 255, .55);
        color: var(--dark);
        border: 1px solid var(--border);
    }

    .danger-btn {
        background: rgba(184, 77, 77, .12);
        color: #b84d4d;
        border: 1px solid rgba(184, 77, 77, .22);
    }

    .success-btn {
        background: rgba(81, 148, 91, .16);
        color: #2f7d3c;
        border: 1px solid rgba(81, 148, 91, .25);
    }

    .status-btn {
        background: rgba(138, 113, 88, .18);
        color: #6f4e37;
        border: 1px solid rgba(138, 113, 88, .22);
    }

    .primary-btn:hover,
    .light-btn:hover,
    .danger-btn:hover,
    .success-btn:hover,
    .status-btn:hover {
        transform: translateY(-1px);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Service List
    |--------------------------------------------------------------------------
    | Text-only service cards.
    |--------------------------------------------------------------------------
    */

    .service-list-wrap {
        display: grid;
        gap: 20px;
    }

    .service-card {
        padding: 24px;
    }

    .service-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 18px;
    }

    .service-main {
        flex: 1;
        min-width: 0;
    }

    .service-top h3 {
        font-size: 19px;
        margin-bottom: 7px;
        color: var(--dark);
    }

    .service-top p {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .badge-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .status {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        height: fit-content;
        white-space: nowrap;
    }

    .status.available {
        background: rgba(81, 148, 91, .18);
        color: #2f7d3c;
    }

    .status.unavailable {
        background: rgba(184, 77, 77, .15);
        color: #b84d4d;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Service Detail Boxes
    |--------------------------------------------------------------------------
    | Displays price, duration, and availability only.
    |--------------------------------------------------------------------------
    */

    .details-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .detail-box {
        background: rgba(255, 255, 255, .45);
        border: 1px solid rgba(255, 255, 255, .65);
        border-radius: 20px;
        padding: 16px;
    }

    .detail-box small {
        color: var(--muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .detail-box p {
        margin-top: 6px;
        font-size: 14px;
        font-weight: 700;
        color: var(--dark);
    }

    .service-description-box {
        padding: 16px;
        border-radius: 20px;
        background: rgba(255, 255, 255, .38);
        border: 1px solid rgba(255, 255, 255, .6);
        margin-bottom: 18px;
    }

    .service-description-box small {
        display: block;
        color: var(--muted);
        font-size: 11px;
        font-weight: 900;
        margin-bottom: 8px;
    }

    .service-description-box p {
        color: #7d6d60;
        font-size: 13px;
        line-height: 1.7;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Deleted Services
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Sidebar Visibility
    |--------------------------------------------------------------------------
    | Keeps the navigation sidebar visible while popups are open.
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
    | NS BEAUTY COMMENT: Modal Popups
    |--------------------------------------------------------------------------
    | Popups retain Back buttons instead of X icons.
    |--------------------------------------------------------------------------
    */

    .modal-overlay {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 280px;
        background: rgba(47, 33, 24, .35);
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
        background: rgba(255, 255, 255, .92);
        border: 1px solid rgba(255, 255, 255, .9);
        border-radius: 30px;
        padding: 28px;
        box-shadow:
            0 25px 80px rgba(47, 33, 24, .20),
            inset 0 1px 0 rgba(255, 255, 255, .8);
    }

    .modal.large {
        max-width: 840px;
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

    .warning-box {
        padding: 16px;
        border-radius: 20px;
        background: rgba(184, 77, 77, .12);
        border: 1px solid rgba(184, 77, 77, .22);
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

    .modal-cancel,
    .modal-back,
    .modal-save {
        padding: 13px 20px;
        border-radius: 999px;
        font-weight: 900;
        cursor: pointer;
        font-size: 13px;
    }

    .modal-cancel {
        border: none;
        background: #b84d4d;
        color: white;
    }

    .modal-back {
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, .65);
        color: var(--dark);
    }

    .modal-save {
        border: none;
        background: linear-gradient(135deg, #8a7158, #5d3f2c);
        color: white;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Responsive Layout
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1100px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .form-grid,
        .details-grid,
        .modal-grid {
            grid-template-columns: 1fr;
        }

        .badge-row {
            justify-content: flex-start;
        }
    }

    @media (max-width: 900px) {
        .modal-overlay {
            left: 0;
        }
    }

    @media (max-width: 700px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .service-top,
        .deleted-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .primary-btn,
        .light-btn,
        .danger-btn,
        .success-btn,
        .status-btn,
        .action-btn,
        .modal-cancel,
        .modal-back,
        .modal-save {
            width: 100%;
            text-align: center;
        }

        .actions,
        .modal-actions {
            width: 100%;
        }

        .form-card,
        .service-card,
        .modal {
            padding: 20px;
        }
    }
</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Live Service Statistics
    |--------------------------------------------------------------------------
    | Values are calculated from database records.
    |--------------------------------------------------------------------------
    */

    $totalServices = $services->count();

    $availableServices = $services
        ->where('status', 'Available')
        ->count();

    $unavailableServices = $services
        ->where('status', 'Unavailable')
        ->count();

    $deletedCount = $deletedServices->count();
@endphp

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Page Header
|--------------------------------------------------------------------------
--}}

<div class="page-header">
    <h1>Service Management</h1>
    <p>
        Add, edit, and manage the availability of
        Nail Studio & Beauty services.
    </p>
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

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Service Statistics
|--------------------------------------------------------------------------
--}}

<div class="stats-grid">

    <div class="stat-card">
        <h2>{{ $totalServices }}</h2>
        <p>Total Services</p>
    </div>

    <div class="stat-card">
        <h2>{{ $availableServices }}</h2>
        <p>Available Services</p>
    </div>

    <div class="stat-card">
        <h2>{{ $unavailableServices }}</h2>
        <p>Unavailable Services</p>
    </div>

</div>

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Add New Service
|--------------------------------------------------------------------------
| No image upload fields or file inputs.
|--------------------------------------------------------------------------
--}}

<section class="form-card">

    <div class="card-title">
        <div>
            <h2>Add New Service</h2>
            <p>
                Create a beauty service using its name,
                price, duration, status, and description.
            </p>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('admin.services.store') }}"
    >
        @csrf

        <div class="form-grid">

            <div>
                <label for="service_name">
                    Service Name
                </label>

                <input
                    type="text"
                    id="service_name"
                    name="service_name"
                    value="{{ old('service_name') }}"
                    maxlength="120"
                    required
                >

                @error('service_name')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div>
                <label for="price">
                    Price (RM)
                </label>

                <input
                    type="number"
                    id="price"
                    name="price"
                    value="{{ old('price') }}"
                    min="0"
                    max="999999.99"
                    step="0.01"
                    required
                >

                @error('price')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div>
                <label for="duration">
                    Duration (Minutes)
                </label>

                <input
                    type="number"
                    id="duration"
                    name="duration"
                    value="{{ old('duration') }}"
                    min="5"
                    max="600"
                    required
                >

                @error('duration')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div>
                <label for="status">
                    Availability Status
                </label>

                <select
                    id="status"
                    name="status"
                    required
                >
                    <option
                        value="Available"
                        {{ old('status', 'Available') === 'Available' ? 'selected' : '' }}
                    >
                        Available
                    </option>

                    <option
                        value="Unavailable"
                        {{ old('status') === 'Unavailable' ? 'selected' : '' }}
                    >
                        Unavailable
                    </option>
                </select>

                @error('status')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group full">
                <label for="description">
                    Service Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    maxlength="1000"
                    placeholder="Describe what is included in this service."
                >{{ old('description') }}</textarea>

                @error('description')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror

                <small class="help">
                    Describe the service clearly so customers
                    and staff understand what is included.
                </small>
            </div>

        </div>

        <div class="form-actions">
            <button
                type="reset"
                class="light-btn"
            >
                Clear
            </button>

            <button
                type="submit"
                class="primary-btn"
            >
                Add Service
            </button>
        </div>
    </form>

</section>

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Service List Header
|--------------------------------------------------------------------------
--}}

<div class="deleted-header">

    <div>
        <h2>Service List</h2>
        <p>
            Manage your service catalog.
            Deleted services can still be restored within 30 days.
        </p>
    </div>

    <button
        type="button"
        class="light-btn"
        onclick="openServiceModal('deletedServicesModal')"
    >
        Deleted Services

        @if ($deletedCount > 0)
            ({{ $deletedCount }})
        @endif
    </button>

</div>

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Active Service Cards
|--------------------------------------------------------------------------
| Image display and image status have been removed completely.
|--------------------------------------------------------------------------
--}}

<div class="service-list-wrap">

    @forelse ($services as $service)

        @php
            $statusKey = $service->status === 'Available'
                ? 'available'
                : 'unavailable';
        @endphp

        <section class="service-card">

            <div class="service-top">

                <div class="service-main">

                    <h3>
                        {{ $service->service_name }}
                    </h3>

                    <p>
                        Service ID:
                        #SV-{{ str_pad($service->id, 4, '0', STR_PAD_LEFT) }}
                    </p>

                    <p>
                        Last Updated:
                        {{ $service->updated_at
                            ? $service->updated_at->format('F d, Y h:i A')
                            : 'Not recorded' }}
                    </p>

                </div>

                <div class="badge-row">
                    <span class="status {{ $statusKey }}">
                        {{ $service->status }}
                    </span>
                </div>

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Service Details
            |--------------------------------------------------------------------------
            | Only price, duration, and availability are displayed.
            |--------------------------------------------------------------------------
            --}}

            <div class="details-grid">

                <div class="detail-box">
                    <small>Price</small>
                    <p>
                        RM {{ number_format((float) $service->price, 2) }}
                    </p>
                </div>

                <div class="detail-box">
                    <small>Duration</small>
                    <p>
                        {{ $service->duration }} minutes
                    </p>
                </div>

                <div class="detail-box">
                    <small>Availability</small>
                    <p>{{ $service->status }}</p>
                </div>

            </div>

            <div class="service-description-box">
                <small>Service Description</small>

                <p>
                    {{ $service->description ?: 'No description added.' }}
                </p>
            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Service Actions
            |--------------------------------------------------------------------------
            --}}

            <div class="actions">

                <button
                    type="button"
                    class="action-btn light-btn"
                    onclick="openServiceModal('editServiceModal{{ $service->id }}')"
                >
                    Edit Service
                </button>

                <form
                    method="POST"
                    action="{{ route('admin.services.status', $service) }}"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="action-btn status-btn"
                    >
                        {{ $service->status === 'Available'
                            ? 'Disable Service'
                            : 'Enable Service' }}
                    </button>
                </form>

                <button
                    type="button"
                    class="action-btn danger-btn"
                    onclick="openServiceModal('deleteServiceModal{{ $service->id }}')"
                >
                    Delete Service
                </button>

            </div>

        </section>

    @empty

        <div class="empty-state">
            <h3>No services found</h3>
            <p>
                Add your first service using the form above.
            </p>
        </div>

    @endforelse

</div>

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Edit and Delete Popups
|--------------------------------------------------------------------------
| No image upload, preview, or removal fields.
|--------------------------------------------------------------------------
--}}

@foreach ($services as $service)

    {{--
    |--------------------------------------------------------------------------
    | Edit Service Popup
    |--------------------------------------------------------------------------
    --}}

    <div
        class="modal-overlay"
        id="editServiceModal{{ $service->id }}"
    >
        <div class="modal large">

            <h3>Edit Service</h3>

            <p>
                Update the service name, price,
                duration, availability, and description.
            </p>

            <form
                method="POST"
                action="{{ route('admin.services.update', $service) }}"
            >
                @csrf
                @method('PATCH')

                <div class="modal-grid">

                    <div>
                        <label for="edit_name_{{ $service->id }}">
                            Service Name
                        </label>

                        <input
                            type="text"
                            id="edit_name_{{ $service->id }}"
                            name="service_name"
                            value="{{ $service->service_name }}"
                            maxlength="120"
                            required
                        >
                    </div>

                    <div>
                        <label for="edit_status_{{ $service->id }}">
                            Availability Status
                        </label>

                        <select
                            id="edit_status_{{ $service->id }}"
                            name="status"
                            required
                        >
                            <option
                                value="Available"
                                {{ $service->status === 'Available'
                                    ? 'selected'
                                    : '' }}
                            >
                                Available
                            </option>

                            <option
                                value="Unavailable"
                                {{ $service->status === 'Unavailable'
                                    ? 'selected'
                                    : '' }}
                            >
                                Unavailable
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_price_{{ $service->id }}">
                            Price (RM)
                        </label>

                        <input
                            type="number"
                            id="edit_price_{{ $service->id }}"
                            name="price"
                            value="{{ $service->price }}"
                            min="0"
                            max="999999.99"
                            step="0.01"
                            required
                        >
                    </div>

                    <div>
                        <label for="edit_duration_{{ $service->id }}">
                            Duration (Minutes)
                        </label>

                        <input
                            type="number"
                            id="edit_duration_{{ $service->id }}"
                            name="duration"
                            value="{{ $service->duration }}"
                            min="5"
                            max="600"
                            required
                        >
                    </div>

                    <div class="full">
                        <label for="edit_description_{{ $service->id }}">
                            Service Description
                        </label>

                        <textarea
                            id="edit_description_{{ $service->id }}"
                            name="description"
                            maxlength="1000"
                        >{{ $service->description }}</textarea>
                    </div>

                </div>

                <div class="modal-actions">

                    <button
                        type="submit"
                        class="modal-save"
                    >
                        Save Changes
                    </button>

                    <button
                        type="button"
                        class="modal-back"
                        onclick="closeServiceModal('editServiceModal{{ $service->id }}')"
                    >
                        Back
                    </button>

                </div>
            </form>

        </div>
    </div>

    {{--
    |--------------------------------------------------------------------------
    | Delete Service Confirmation Popup
    |--------------------------------------------------------------------------
    --}}

    <div
        class="modal-overlay"
        id="deleteServiceModal{{ $service->id }}"
    >
        <div class="modal">

            <h3>Delete Service</h3>

            <div class="warning-box">

                <strong>
                    Are you sure you want to delete this service?
                </strong>

                <span>
                    The service will be moved to Deleted Services.
                    It can be restored within 30 days before
                    permanent deletion.
                </span>

            </div>

            <p>
                Service:
                <strong>{{ $service->service_name }}</strong>
            </p>

            <form
                method="POST"
                action="{{ route('admin.services.destroy', $service) }}"
            >
                @csrf
                @method('DELETE')

                <div class="modal-actions">

                    <button
                        type="submit"
                        class="modal-cancel"
                    >
                        Delete Service
                    </button>

                    <button
                        type="button"
                        class="modal-back"
                        onclick="closeServiceModal('deleteServiceModal{{ $service->id }}')"
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
| NS BEAUTY COMMENT: Deleted Services Popup
|--------------------------------------------------------------------------
| Lists service information without images.
|--------------------------------------------------------------------------
--}}

<div
    class="modal-overlay"
    id="deletedServicesModal"
>
    <div class="modal large">

        <h3>Deleted Services</h3>

        <p>
            Deleted services can be restored within 30 days.
            After 30 days, they are eligible for automatic
            permanent deletion when this page is opened.
        </p>

        <div class="service-list-wrap">

            @forelse ($deletedServices as $deletedService)

                @php
                    $permanentDeleteDate =
                        $deletedService->deleted_at
                            ? $deletedService->deleted_at
                                ->copy()
                                ->addDays(30)
                            : null;
                @endphp

                <section
                    class="deleted-card"
                    style="padding:20px;"
                >

                    <div class="service-top" style="margin-bottom:0;">

                        <div class="service-main">

                            <h3>
                                {{ $deletedService->service_name }}
                            </h3>

                            <p>
                                Price:
                                RM {{ number_format((float) $deletedService->price, 2) }}
                            </p>

                            <p>
                                Duration:
                                {{ $deletedService->duration }} minutes
                            </p>

                            <p>
                                Deleted:
                                {{ $deletedService->deleted_at
                                    ? $deletedService->deleted_at->format('F d, Y h:i A')
                                    : 'Not recorded' }}
                            </p>

                            <p>
                                Permanent Deletion:
                                {{ $permanentDeleteDate
                                    ? $permanentDeleteDate->format('F d, Y')
                                    : 'Not recorded' }}
                            </p>

                        </div>

                        <div class="actions">

                            <form
                                method="POST"
                                action="{{ route('admin.services.restore', $deletedService->id) }}"
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
                                action="{{ route('admin.services.force-delete', $deletedService->id) }}"
                                onsubmit="return confirm('Permanently delete this service now? This cannot be undone.')"
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

                    <h3>No deleted services</h3>

                    <p>
                        Deleted services will appear here.
                    </p>

                </div>

            @endforelse

        </div>

        <div class="modal-actions">

            <button
                type="button"
                class="modal-back"
                onclick="closeServiceModal('deletedServicesModal')"
            >
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
    | NS BEAUTY COMMENT: Service Modal Management
    |--------------------------------------------------------------------------
    | Opens and closes Edit, Delete, and Deleted Services popups.
    | Keeps the admin sidebar visible during popup interactions.
    |--------------------------------------------------------------------------
    */

    function openServiceModal(id) {
        const modal = document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.add('show');

        const modalBox = modal.querySelector('.modal');

        if (modalBox) {
            modalBox.scrollTop = 0;
        }

        modal.scrollTop = 0;
    }

    function closeServiceModal(id) {
        const modal = document.getElementById(id);

        if (modal) {
            modal.classList.remove('show');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Click Outside Popup
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('show');
        }
    });

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Escape Key
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll(
                '.modal-overlay.show'
            ).forEach(function (modal) {
                modal.classList.remove('show');
            });
        }
    });
</script>
@endsection
