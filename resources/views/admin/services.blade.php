@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Admin Service Management View
|
| Purpose:
| - Allows admin to add, edit, enable, disable, and delete services.
| - Allows admin to upload service images.
| - Shows deleted services before permanent deletion.
|
| Defense explanation:
| This module maintains the official service catalog of Nail Studio & Beauty.
| The design follows the Booking Management module for UI consistency.
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Add Service Form Alignment Fix
|--------------------------------------------------------------------------
| Purpose:
| - Aligns Service Image with Service Name, Price, Duration, and Status.
| - Keeps the existing functionality and visual design unchanged.
|--------------------------------------------------------------------------
*/
.form-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr .7fr .7fr .8fr;
    gap: 14px;
    align-items: start;
}

.form-group.full {
    grid-column: 1 / -1;
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Form Field Alignment Helper
|--------------------------------------------------------------------------
| Purpose:
| - Gives input/select/file fields the same visual height.
| - Prevents the Service Image upload field from appearing higher than the
|   other form fields.
|--------------------------------------------------------------------------
*/
.form-grid input,
.form-grid select {
    min-height: 45px;
}

.form-grid input[type="file"] {
    min-height: 45px;
    padding: 10px 12px;
    display: block;
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

input[type="file"] {
    padding: 11px;
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

.primary-btn {
    background: linear-gradient(135deg,#8a7158,#5d3f2c);
    color: white;
}

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

.danger-solid-btn {
    background: #b84d4d;
    color: white;
    border: none;
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
    gap: 18px;
    margin-bottom: 18px;
}

.service-main {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.service-img {
    width: 92px;
    height: 92px;
    border-radius: 22px;
    overflow: hidden;
    background:
        linear-gradient(135deg, rgba(111,78,55,.14), rgba(255,253,249,.55)),
        linear-gradient(135deg, #d9c2ac, #fff7ee);
    border: 1px solid var(--border);
    display: grid;
    place-items: center;
    color: var(--muted);
    font-size: 11px;
    font-weight: 900;
    text-align: center;
    flex: 0 0 auto;
}

.service-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.service-top h3 {
    font-size: 18px;
    margin-bottom: 5px;
}

.service-top p {
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
    background: rgba(81,148,91,.18);
    color: #2f7d3c;
}

.status.unavailable {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
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
}

.service-description-box {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.38);
    border: 1px solid rgba(255,255,255,.6);
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
    line-height: 1.6;
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

.deleted-count {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,.55);
    border: 1px solid var(--border);
    color: var(--dark);
    font-size: 12px;
    font-weight: 900;
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
| - Keeps the admin sidebar visible when service popups are opened.
| - The popup overlay starts after the sidebar instead of covering the
|   whole screen.
|
| Defense explanation:
| This improves navigation consistency because the admin can still see the
| main system menu while reviewing edit, delete, or deleted-services popups.
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
| NS BEAUTY COMMENT:
| Modal Design
|--------------------------------------------------------------------------
| This follows the Booking Management popup design but starts after the
| sidebar on desktop.
|
| Important:
| - Change left: 280px only if your sidebar width is different.
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Image Preview Fix
|--------------------------------------------------------------------------
| Purpose:
| - Fixes the broken image preview CSS from the previous file.
| - Keeps service image preview neat inside the Edit Service popup.
|--------------------------------------------------------------------------
*/
.image-preview {
    width: 100%;
    height: 190px;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255,255,255,.45);
    border: 1px solid var(--border);
    margin-top: 12px;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-check {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    color: #b84d4d;
    font-size: 12px;
    font-weight: 900;
}

.remove-check input {
    width: auto;
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

.modal-cancel {
    padding: 13px 20px;
    border-radius: 999px;
    border: none;
    background: #b84d4d;
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

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Responsive popup behavior.
|--------------------------------------------------------------------------
| On small screens, the sidebar is usually collapsed, so the popup can cover
| the full screen.
|--------------------------------------------------------------------------
*/
@media(max-width:1100px) {
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

@media(max-width:900px) {
    .modal-overlay {
        left: 0;
    }
}

@media(max-width:700px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .service-top,
    .service-main,
    .deleted-header {
        flex-direction: column;
    }

    .service-img {
        width: 100%;
        height: 210px;
    }

    .primary-btn,
    .light-btn,
    .danger-btn,
    .success-btn,
    .status-btn,
    .action-btn,
    .modal-cancel,
    .modal-back {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Service statistics.
    |--------------------------------------------------------------------------
    | Deleted services are excluded from the active service cards.
    */
    $totalServices = $services->count();
    $availableServices = $services->where('status', 'Available')->count();
    $unavailableServices = $services->where('status', 'Unavailable')->count();
    $deletedCount = $deletedServices->count();
@endphp

<div class="page-header">
    <h1>Service Management</h1>
    <p>Add, edit, upload images, and manage availability of Nail Studio & Beauty services.</p>
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

<section class="form-card">
    <div class="card-title">
        <div>
            <h2>Add New Service</h2>
            <p>Create a beauty service with name, image, price, duration, and availability.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">
            <div>
                <label>Service Name</label>
                <input type="text" name="service_name" value="{{ old('service_name') }}" maxlength="120" required>
                @error('service_name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label>Service Image</label>
                <input type="file" name="image_path" accept="image/jpeg,image/png,image/jpg,image/webp">
                <small class="help">Accepted: JPG, PNG, WEBP. Max size: 4MB.</small>
                @error('image_path')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label>Price RM</label>
                <input type="number" name="price" value="{{ old('price') }}" min="0" step="0.01" required>
                @error('price')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label>Duration</label>
                <input type="number" name="duration" value="{{ old('duration') }}" min="5" max="600" required>
                @error('duration')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label>Status</label>
                <select name="status" required>
                    <option value="Available" {{ old('status', 'Available') === 'Available' ? 'selected' : '' }}>
                        Available
                    </option>
                    <option value="Unavailable" {{ old('status') === 'Unavailable' ? 'selected' : '' }}>
                        Unavailable
                    </option>
                </select>
                @error('status')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full">
                <label>Description</label>
                <textarea name="description" maxlength="1000" placeholder="Describe what is included in this service.">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="reset" class="light-btn">Clear</button>
            <button type="submit" class="primary-btn">Add Service</button>
        </div>
    </form>
</section>

<div class="deleted-header">
    <div>
        <h2>Service List</h2>
        <p>Manage active services. Deleted services can still be restored within 30 days.</p>
    </div>

    <button type="button" class="light-btn" onclick="openServiceModal('deletedServicesModal')">
        Deleted Services
        @if ($deletedCount > 0)
            ({{ $deletedCount }})
        @endif
    </button>
</div>

<div class="service-list-wrap">
    @forelse ($services as $service)
        @php
            $statusKey = $service->status === 'Available' ? 'available' : 'unavailable';
        @endphp

        <section class="service-card">
            <div class="service-top">
                <div class="service-main">
                    <div class="service-img">
                        @if ($service->image_path)
                            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->service_name }}">
                        @else
                            No Image
                        @endif
                    </div>

                    <div>
                        <h3>{{ $service->service_name }}</h3>
                        <p>
                            Service ID:
                            #SV-{{ str_pad($service->id, 4, '0', STR_PAD_LEFT) }}
                        </p>
                        <p>
                            Last Updated:
                            {{ $service->updated_at ? $service->updated_at->format('F d, Y h:i A') : 'Not recorded' }}
                        </p>
                    </div>
                </div>

                <div class="badge-row">
                    <span class="status {{ $statusKey }}">
                        {{ $service->status }}
                    </span>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-box">
                    <small>PRICE</small>
                    <p>RM {{ number_format($service->price, 2) }}</p>
                </div>

                <div class="detail-box">
                    <small>DURATION</small>
                    <p>{{ $service->duration }} minutes</p>
                </div>

                <div class="detail-box">
                    <small>STATUS</small>
                    <p>{{ $service->status }}</p>
                </div>

                <div class="detail-box">
                    <small>IMAGE</small>
                    <p>{{ $service->image_path ? 'Uploaded' : 'No image' }}</p>
                </div>
            </div>

            <div class="service-description-box">
                <small>SERVICE DESCRIPTION</small>
                <p>{{ $service->description ?: 'No description added.' }}</p>
            </div>

            <div class="actions">
                <button type="button" class="action-btn light-btn" onclick="openServiceModal('editServiceModal{{ $service->id }}')">
                    Edit Service
                </button>

                <form method="POST" action="{{ route('admin.services.status', $service) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="action-btn status-btn">
                        {{ $service->status === 'Available' ? 'Disable Service' : 'Enable Service' }}
                    </button>
                </form>

                <button type="button" class="action-btn danger-btn" onclick="openServiceModal('deleteServiceModal{{ $service->id }}')">
                    Delete Service
                </button>
            </div>
        </section>
    @empty
        <div class="empty-state">
            <h3>No services found</h3>
            <p>Add your first service using the form above.</p>
        </div>
    @endforelse
</div>

@foreach ($services as $service)
    <div class="modal-overlay" id="editServiceModal{{ $service->id }}">
        <div class="modal large">
            <h3>Edit Service</h3>
            <p>Update service information, image, price, duration, and availability.</p>

            <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="modal-grid">
                    <div>
                        <label>Service Name</label>
                        <input type="text" name="service_name" value="{{ old('service_name', $service->service_name) }}" maxlength="120" required>
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Available" {{ old('status', $service->status) === 'Available' ? 'selected' : '' }}>
                                Available
                            </option>
                            <option value="Unavailable" {{ old('status', $service->status) === 'Unavailable' ? 'selected' : '' }}>
                                Unavailable
                            </option>
                        </select>
                    </div>

                    <div>
                        <label>Price RM</label>
                        <input type="number" name="price" value="{{ old('price', $service->price) }}" min="0" step="0.01" required>
                    </div>

                    <div>
                        <label>Duration Minutes</label>
                        <input type="number" name="duration" value="{{ old('duration', $service->duration) }}" min="5" max="600" required>
                    </div>

                    <div class="full">
                        <label>Description</label>
                        <textarea name="description" maxlength="1000">{{ old('description', $service->description) }}</textarea>
                    </div>

                    <div class="full">
                        <label>Service Image</label>
                        <input type="file" name="image_path" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <small class="help">Upload a new image only if you want to replace the current image.</small>

                        @if ($service->image_path)
                            <div class="image-preview">
                                <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->service_name }}">
                            </div>

                            <label class="remove-check">
                                <input type="checkbox" name="remove_image_path" value="1">
                                Remove current image
                            </label>
                        @else
                            <small class="help">No image uploaded yet for this service.</small>
                        @endif
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="modal-cancel" style="background: linear-gradient(135deg,#8a7158,#5d3f2c);">
                        Save Changes
                    </button>

                    <button type="button" class="modal-back" onclick="closeServiceModal('editServiceModal{{ $service->id }}')">
                        Back
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="deleteServiceModal{{ $service->id }}">
        <div class="modal">
            <h3>Delete Service</h3>

            <div class="warning-box">
                <strong>Are you sure you want to delete this service?</strong>
                <span>
                    This service will be moved to Deleted Services first.
                    You can still restore it within 30 days before it becomes eligible for permanent deletion.
                </span>
            </div>

            <p>
                Service:
                <strong>{{ $service->service_name }}</strong>
            </p>

            <form method="POST" action="{{ route('admin.services.destroy', $service) }}">
                @csrf
                @method('DELETE')

                <div class="modal-actions">
                    <button type="submit" class="modal-cancel">
                        Delete Service
                    </button>

                    <button type="button" class="modal-back" onclick="closeServiceModal('deleteServiceModal{{ $service->id }}')">
                        Back
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<div class="modal-overlay" id="deletedServicesModal">
    <div class="modal large">
        <h3>Deleted Services</h3>

        <p>
            Deleted services can be restored within 30 days.
            After 30 days, they will be permanently deleted automatically when this page is opened.
        </p>

        <div class="service-list-wrap">
            @forelse ($deletedServices as $deletedService)
                @php
                    $permanentDeleteDate = $deletedService->deleted_at
                        ? $deletedService->deleted_at->copy()->addDays(30)
                        : null;
                @endphp

                <section class="deleted-card" style="padding:20px;">
                    <div class="service-top" style="margin-bottom:0;">
                        <div class="service-main">
                            <div class="service-img">
                                @if ($deletedService->image_path)
                                    <img src="{{ asset('storage/' . $deletedService->image_path) }}" alt="{{ $deletedService->service_name }}">
                                @else
                                    No Image
                                @endif
                            </div>

                            <div>
                                <h3>{{ $deletedService->service_name }}</h3>

                                <p>
                                    Deleted:
                                    {{ $deletedService->deleted_at ? $deletedService->deleted_at->format('F d, Y h:i A') : 'Not recorded' }}
                                </p>

                                <p>
                                    Permanent Deletion:
                                    {{ $permanentDeleteDate ? $permanentDeleteDate->format('F d, Y') : 'Not recorded' }}
                                </p>
                            </div>
                        </div>

                        <div class="actions">
                            <form method="POST" action="{{ route('admin.services.restore', $deletedService->id) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="success-btn">
                                    Restore
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.services.force-delete', $deletedService->id) }}" onsubmit="return confirm('Permanently delete this service now? This cannot be undone.')">
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
                    <h3>No deleted services</h3>
                    <p>Deleted services will appear here.</p>
                </div>
            @endforelse
        </div>

        <div class="modal-actions">
            <button type="button" class="modal-back" onclick="closeServiceModal('deletedServicesModal')">
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
| Service Modal Script
|--------------------------------------------------------------------------
| Purpose:
| - Opens edit service popup.
| - Opens delete confirmation popup.
| - Opens deleted services popup.
| - Keeps popup scroll position clean whenever a popup opens.
|--------------------------------------------------------------------------
*/
function openServiceModal(id) {
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

function closeServiceModal(id) {
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