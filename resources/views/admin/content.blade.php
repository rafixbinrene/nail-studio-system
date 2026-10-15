
@extends('layouts.app')

{{--
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Admin Content Management
|--------------------------------------------------------------------------
| Purpose:
| - Manage public website information
| - Upload and preview five hero slideshow images
| - Upload and preview the studio image
| - Manage Facebook, Instagram, TikTok, and WhatsApp
| - Manage public announcements with Active/Inactive status
| - Preview content saved in the database
|
| Defense Explanation:
| Administrators can update website information through this interface
| without editing the public Welcome Page source code
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Page Header
    |--------------------------------------------------------------------------
    */

    .page-header {
        margin-bottom: 24px;
    }

    .page-header h1 {
        font-size: 34px;
        margin-bottom: 8px;
    }

    .page-header p {
        color: var(--muted);
        font-size: 14px;
        line-height: 1.6;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Alerts
    |--------------------------------------------------------------------------
    */

    .alert-success,
    .alert-error {
        padding: 16px 18px;
        border-radius: 18px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 800;
    }

    .alert-success {
        background: rgba(81, 148, 91, .15);
        color: #2f7d3c;
        border: 1px solid rgba(81, 148, 91, .25);
    }

    .alert-error {
        background: rgba(184, 77, 77, .15);
        color: #b84d4d;
        border: 1px solid rgba(184, 77, 77, .25);
    }

    .alert-error ul {
        margin: 10px 0 0 20px;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Main Layout
    |--------------------------------------------------------------------------
    */

    .content-grid {
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 24px;
        align-items: start;
    }

    .form-card,
    .preview-card {
        background: var(--glass);
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid var(--border);
        border-radius: 28px;
        box-shadow:
            0 20px 60px rgba(47,33,24,.08),
            inset 0 1px 0 rgba(255,255,255,.7);
        padding: 26px;
        min-width: 0;
    }

    .section-title {
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(47,33,24,.10);
    }

    .section-title h2 {
        font-size: 20px;
        margin-bottom: 6px;
    }

    .section-title p {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .form-section {
        margin-bottom: 30px;
    }

    .form-grid,
    .image-upload-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .form-group {
        margin-bottom: 18px;
        min-width: 0;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label,
    .image-upload-box > label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 900;
        color: var(--dark);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Form Inputs
    |--------------------------------------------------------------------------
    */

    .form-card input:not([type="checkbox"]),
    .form-card textarea,
    .form-card select {
        width: 100%;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid var(--border);
        background: rgba(255,255,255,.65);
        outline: none;
        color: var(--dark);
        font-family: inherit;
        font-size: 14px;
    }

    .form-card input:focus,
    .form-card textarea:focus,
    .form-card select:focus {
        border-color: #8a7158;
        box-shadow: 0 0 0 3px rgba(138,113,88,.12);
    }

    .form-card textarea {
        min-height: 120px;
        resize: vertical;
    }

    .form-card input[type="file"] {
        padding: 12px;
        background: rgba(255,255,255,.75);
    }

    small.help {
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

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Image Upload and Preview
    |--------------------------------------------------------------------------
    | Preserve the existing content_images integration
    |--------------------------------------------------------------------------
    */

    .image-upload-box {
        padding: 16px;
        border-radius: 20px;
        background: rgba(255,255,255,.45);
        border: 1px solid rgba(255,255,255,.65);
        min-width: 0;
    }

    .image-preview {
        margin-top: 12px;
        width: 100%;
        height: 170px;
        border-radius: 18px;
        overflow: hidden;
        background: rgba(255,255,255,.45);
        border: 1px solid var(--border);
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .image-missing {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 170px;
        padding: 16px;
        text-align: center;
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
    }

    .remove-check {
        display: flex !important;
        gap: 8px;
        align-items: center;
        margin-top: 10px;
        font-size: 12px;
        color: #b84d4d;
        font-weight: 900;
        cursor: pointer;
    }

    .remove-check input {
        width: auto;
        flex-shrink: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Save and Reset
    |--------------------------------------------------------------------------
    */

    .action-row {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 22px;
    }

    .save-btn,
    .reset-btn {
        border: none;
        border-radius: 999px;
        padding: 14px 22px;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        text-align: center;
    }

    .save-btn {
        background: linear-gradient(135deg,#8a7158,#5d3f2c);
        color: white;
    }

    .reset-btn {
        background: rgba(255,255,255,.55);
        border: 1px solid var(--border);
        color: var(--dark);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Saved Content Preview
    |--------------------------------------------------------------------------
    */

    .preview-card {
        position: sticky;
        top: 24px;
        align-self: start;
    }

    .preview-box {
        background: rgba(255,255,255,.42);
        border: 1px solid rgba(255,255,255,.65);
        border-radius: 24px;
        padding: 22px;
        margin-bottom: 18px;
    }

    .preview-box h3 {
        color: var(--brown);
        margin-bottom: 8px;
        font-size: 17px;
    }

    .preview-box p {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.7;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .preview-list {
        display: grid;
        gap: 12px;
    }

    .preview-item {
        border-bottom: 1px solid rgba(47,33,24,.08);
        padding-bottom: 10px;
    }

    .preview-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .preview-item strong {
        display: block;
        font-size: 12px;
        margin-bottom: 4px;
    }

    .preview-item span,
    .preview-item a {
        display: block;
        color: var(--muted);
        font-size: 12px;
        overflow-wrap: anywhere;
        line-height: 1.5;
    }

    .preview-item a {
        color: var(--brown);
        text-decoration: underline;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Announcement Status
    |--------------------------------------------------------------------------
    */

    .status-pill {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
    }

    .status-pill.active {
        background: rgba(81,148,91,.18);
        color: #2f7d3c;
    }

    .status-pill.inactive {
        background: rgba(184,77,77,.15);
        color: #b84d4d;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Responsive Layout
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1100px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .preview-card {
            position: static;
        }
    }

    @media (max-width: 700px) {
        .form-grid,
        .image-upload-grid {
            grid-template-columns: 1fr;
        }

        .action-row {
            justify-content: stretch;
        }

        .save-btn,
        .reset-btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Image URL Resolver
    |--------------------------------------------------------------------------
    | New content images use the content_images disk
    | Existing public image paths remain supported
    |--------------------------------------------------------------------------
    */

    $contentImageDisk = 'content_images';

    $contentImageUrl = function ($path) use ($contentImageDisk) {
        if (blank($path)) {
            return null;
        }

        $path = trim((string) $path);

        if (\Illuminate\Support\Str::startsWith($path, [
            'http://',
            'https://',
            '//',
        ])) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (\Illuminate\Support\Str::startsWith($path, [
            'images/',
            'assets/',
            'storage/',
        ])) {
            return asset($path);
        }

        try {
            return \Illuminate\Support\Facades\Storage::disk(
                $contentImageDisk
            )->url($path);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    };
@endphp

<div class="page-header">
    <h1>Content Management</h1>
    <p>
        Update public website content, homepage images,
        contact information, and announcements
    </p>
</div>

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert-error">
        Please check the form. Some fields need correction

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-grid">

    <form
        method="POST"
        action="{{ route('admin.content.update') }}"
        enctype="multipart/form-data"
        class="form-card"
    >
        @csrf
        @method('PATCH')

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Business Identity
        |--------------------------------------------------------------------------
        --}}

        <div class="form-section">
            <div class="section-title">
                <h2>Business Identity</h2>
                <p>Salon name and tagline displayed on the public website</p>
            </div>

            <div class="form-grid">

                <div class="form-group">
                    <label for="salon_name">Salon Name</label>

                    <input
                        type="text"
                        id="salon_name"
                        name="salon_name"
                        value="{{ old('salon_name', $content->salon_name) }}"
                        maxlength="120"
                        required
                    >

                    @error('salon_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tagline">Tagline</label>

                    <input
                        type="text"
                        id="tagline"
                        name="tagline"
                        value="{{ old('tagline', $content->tagline) }}"
                        maxlength="150"
                        required
                    >

                    @error('tagline')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Homepage Hero Text
        |--------------------------------------------------------------------------
        --}}

        <div class="form-section">
            <div class="section-title">
                <h2>Homepage Hero Section</h2>
                <p>Main welcome message shown to customers</p>
            </div>

            <div class="form-group">
                <label for="hero_title">Hero Title</label>

                <input
                    type="text"
                    id="hero_title"
                    name="hero_title"
                    value="{{ old('hero_title', $content->hero_title) }}"
                    maxlength="150"
                    required
                >

                @error('hero_title')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="hero_subtitle">Hero Subtitle</label>

                <textarea
                    id="hero_subtitle"
                    name="hero_subtitle"
                    maxlength="500"
                >{{ old('hero_subtitle', $content->hero_subtitle) }}</textarea>

                <small class="help">
                    This message appears below the main hero title
                </small>

                @error('hero_subtitle')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Website Images
        |--------------------------------------------------------------------------
        | Five hero slides and one studio image
        | Existing Cloud storage integration is preserved
        |--------------------------------------------------------------------------
        --}}

        <div class="form-section">
            <div class="section-title">
                <h2>Website Images</h2>
                <p>
                    Upload hero slideshow images and the studio image
                    to the connected Content Images storage
                </p>
            </div>

            <div class="image-upload-grid">

                @for ($i = 1; $i <= 5; $i++)

                    @php
                        $field = 'hero_slide_' . $i . '_image';
                        $removeField = 'remove_' . $field;
                        $currentImage = $content->{$field};
                        $previewUrl = $contentImageUrl($currentImage);
                    @endphp

                    <div class="image-upload-box">

                        <label for="{{ $field }}">
                            Hero Slide Image {{ $i }}
                        </label>

                        <input
                            type="file"
                            id="{{ $field }}"
                            name="{{ $field }}"
                            accept="image/jpeg,image/png,image/webp"
                        >

                        <small class="help">
                            JPG, PNG, or WEBP — Maximum 4 MB
                        </small>

                        @if ($currentImage)

                            <div class="image-preview">
                                @if ($previewUrl)

                                    <img
                                        src="{{ $previewUrl }}"
                                        alt="Hero slide {{ $i }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >

                                    <div
                                        class="image-missing"
                                        style="display:none;"
                                    >
                                        Image unavailable
                                        Upload a replacement if needed
                                    </div>

                                @else

                                    <div class="image-missing">
                                        Image URL unavailable
                                        Upload a replacement
                                    </div>

                                @endif
                            </div>

                            <label class="remove-check">
                                <input
                                    type="checkbox"
                                    name="{{ $removeField }}"
                                    value="1"
                                >
                                Remove this image
                            </label>

                        @endif

                        @error($field)
                            <span class="error-text">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                @endfor

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Studio Image
                |--------------------------------------------------------------------------
                --}}

                @php
                    $studioPreviewUrl = $contentImageUrl(
                        $content->studio_image_path
                    );
                @endphp

                <div class="image-upload-box">

                    <label for="studio_image_path">
                        Studio Image
                    </label>

                    <input
                        type="file"
                        id="studio_image_path"
                        name="studio_image_path"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small class="help">
                        Displayed in the Visit Our Studio section
                        Maximum 4 MB
                    </small>

                    @if ($content->studio_image_path)

                        <div class="image-preview">

                            @if ($studioPreviewUrl)

                                <img
                                    src="{{ $studioPreviewUrl }}"
                                    alt="Studio image"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >

                                <div
                                    class="image-missing"
                                    style="display:none;"
                                >
                                    Studio image unavailable
                                    Upload a replacement if needed
                                </div>

                            @else

                                <div class="image-missing">
                                    Image URL unavailable
                                    Upload a replacement
                                </div>

                            @endif

                        </div>

                        <label class="remove-check">
                            <input
                                type="checkbox"
                                name="remove_studio_image_path"
                                value="1"
                            >
                            Remove this image
                        </label>

                    @endif

                    @error('studio_image_path')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror

                </div>

            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: About Section
        |--------------------------------------------------------------------------
        --}}

        <div class="form-section">
            <div class="section-title">
                <h2>About Section</h2>
                <p>Introduce Nail Studio & Beauty to website visitors</p>
            </div>

            <div class="form-group">
                <label for="about_title">About Title</label>

                <input
                    type="text"
                    id="about_title"
                    name="about_title"
                    value="{{ old('about_title', $content->about_title) }}"
                    maxlength="150"
                    required
                >

                @error('about_title')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="about_description">
                    About Description
                </label>

                <textarea
                    id="about_description"
                    name="about_description"
                    maxlength="1500"
                >{{ old('about_description', $content->about_description) }}</textarea>

                @error('about_description')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Contact Details
        |--------------------------------------------------------------------------
        | Existing fields are preserved
        | TikTok and WhatsApp fields are added below
        |--------------------------------------------------------------------------
        --}}

        <div class="form-section">
            <div class="section-title">
                <h2>Contact Details</h2>
                <p>
                    Contact information and social media displayed
                    on the public Welcome Page
                </p>
            </div>

            <div class="form-grid">

                <div class="form-group full">
                    <label for="address">Address</label>

                    <input
                        type="text"
                        id="address"
                        name="address"
                        value="{{ old('address', $content->address) }}"
                        maxlength="255"
                    >

                    @error('address')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_number">
                        Phone Number
                    </label>

                    <input
                        type="tel"
                        id="phone_number"
                        name="phone_number"
                        value="{{ old('phone_number', $content->phone_number) }}"
                        maxlength="30"
                    >

                    @error('phone_number')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $content->email) }}"
                        maxlength="120"
                    >

                    @error('email')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="facebook_url">
                        Facebook URL
                    </label>

                    <input
                        type="url"
                        id="facebook_url"
                        name="facebook_url"
                        value="{{ old('facebook_url', $content->facebook_url) }}"
                        maxlength="255"
                        placeholder="https://www.facebook.com/..."
                    >

                    @error('facebook_url')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="instagram_url">
                        Instagram URL
                    </label>

                    <input
                        type="url"
                        id="instagram_url"
                        name="instagram_url"
                        value="{{ old('instagram_url', $content->instagram_url) }}"
                        maxlength="255"
                        placeholder="https://www.instagram.com/..."
                    >

                    @error('instagram_url')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: TikTok URL
                |--------------------------------------------------------------------------
                | Saved in content_settings.tiktok_url
                |--------------------------------------------------------------------------
                --}}

                <div class="form-group">
                    <label for="tiktok_url">
                        TikTok URL
                    </label>

                    <input
                        type="url"
                        id="tiktok_url"
                        name="tiktok_url"
                        value="{{ old('tiktok_url', $content->tiktok_url) }}"
                        maxlength="255"
                        placeholder="https://www.tiktok.com/@username"
                    >

                    <small class="help">
                        Paste the official NS Beauty TikTok profile link
                    </small>

                    @error('tiktok_url')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: WhatsApp URL
                |--------------------------------------------------------------------------
                | Example: https://wa.me/60123456789
                |--------------------------------------------------------------------------
                --}}

                <div class="form-group">
                    <label for="whatsapp_url">
                        WhatsApp URL
                    </label>

                    <input
                        type="url"
                        id="whatsapp_url"
                        name="whatsapp_url"
                        value="{{ old('whatsapp_url', $content->whatsapp_url) }}"
                        maxlength="255"
                        placeholder="https://wa.me/60123456789"
                    >

                    <small class="help">
                        Paste a direct WhatsApp chat link
                    </small>

                    @error('whatsapp_url')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: WhatsApp Number
                |--------------------------------------------------------------------------
                | Used as a fallback when WhatsApp URL is empty
                |--------------------------------------------------------------------------
                --}}

                <div class="form-group">
                    <label for="whatsapp_number">
                        WhatsApp Number
                    </label>

                    <input
                        type="tel"
                        id="whatsapp_number"
                        name="whatsapp_number"
                        value="{{ old('whatsapp_number', $content->whatsapp_number) }}"
                        maxlength="30"
                        placeholder="60123456789"
                    >

                    <small class="help">
                        Include Malaysia country code 60, without spaces or +
                    </small>

                    @error('whatsapp_number')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group full">
                    <label for="opening_hours">
                        Opening Hours
                    </label>

                    <textarea
                        id="opening_hours"
                        name="opening_hours"
                        maxlength="1000"
                    >{{ old('opening_hours', $content->opening_hours) }}</textarea>

                    <small class="help">
                        Example: Monday - Saturday: 9:00 AM - 6:00 PM
                    </small>

                    @error('opening_hours')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Public Announcements
        |--------------------------------------------------------------------------
        | Existing announcement functionality remains unchanged
        |
        | Active: Announcement appears on the Welcome Page
        | Inactive: Announcement is hidden
        |--------------------------------------------------------------------------
        --}}

        <div class="form-section">

            <div class="section-title">
                <h2>Announcement</h2>
                <p>
                    Publish important updates, promotions,
                    or temporary scheduling notices to customers
                </p>
            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label for="announcement_status">
                        Announcement Status
                    </label>

                    <select
                        id="announcement_status"
                        name="announcement_status"
                        required
                    >

                        <option
                            value="inactive"
                            {{ old('announcement_status', $content->announcement_status) === 'inactive' ? 'selected' : '' }}
                        >
                            Inactive
                        </option>

                        <option
                            value="active"
                            {{ old('announcement_status', $content->announcement_status) === 'active' ? 'selected' : '' }}
                        >
                            Active
                        </option>

                    </select>

                    @error('announcement_status')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror

                </div>

                <div class="form-group">

                    <label for="announcement_title">
                        Announcement Title
                    </label>

                    <input
                        type="text"
                        id="announcement_title"
                        name="announcement_title"
                        value="{{ old('announcement_title', $content->announcement_title) }}"
                        maxlength="150"
                        placeholder="e.g. Special Weekend Promotion"
                    >

                    @error('announcement_title')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror

                </div>

                <div class="form-group full">

                    <label for="announcement_message">
                        Announcement Message
                    </label>

                    <textarea
                        id="announcement_message"
                        name="announcement_message"
                        maxlength="1000"
                        placeholder="Enter the announcement details"
                    >{{ old('announcement_message', $content->announcement_message) }}</textarea>

                    @error('announcement_message')
                        <span class="error-text">
                            {{ $message }}
                        </span>
                    @enderror

                </div>

            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Save and Reset Actions
        |--------------------------------------------------------------------------
        --}}

        <div class="action-row">

            <a
                href="{{ route('admin.content') }}"
                class="reset-btn"
            >
                Reset
            </a>

            <button
                type="submit"
                class="save-btn"
            >
                Save Content Changes
            </button>

        </div>

    </form>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Saved Content Preview
    |--------------------------------------------------------------------------
    | Displays the values currently saved in the database
    |--------------------------------------------------------------------------
    --}}

    <aside class="preview-card">

        <div class="section-title">
            <h2>Content Preview</h2>
            <p>
                Quick view of the current saved website content
            </p>
        </div>

        <div class="preview-box">
            <h3>{{ $content->salon_name }}</h3>
            <p>{{ $content->tagline }}</p>
        </div>

        <div class="preview-box">
            <h3>{{ $content->hero_title }}</h3>

            <p>
                {{ $content->hero_subtitle ?: 'No hero subtitle set' }}
            </p>
        </div>

        <div class="preview-box">
            <h3>Uploaded Images</h3>

            <div class="preview-list">

                @for ($i = 1; $i <= 5; $i++)
                    @php
                        $imageField = 'hero_slide_' . $i . '_image';
                    @endphp

                    <div class="preview-item">
                        <strong>Hero Slide {{ $i }}</strong>
                        <span>
                            {{ $content->{$imageField} ? 'Uploaded' : 'Not uploaded' }}
                        </span>
                    </div>
                @endfor

                <div class="preview-item">
                    <strong>Studio Image</strong>
                    <span>
                        {{ $content->studio_image_path ? 'Uploaded' : 'Not uploaded' }}
                    </span>
                </div>

            </div>
        </div>

        <div class="preview-box">
            <h3>{{ $content->about_title }}</h3>

            <p>
                {{ $content->about_description ?: 'No about description set' }}
            </p>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Contact Preview
        |--------------------------------------------------------------------------
        | Includes saved TikTok and WhatsApp information
        |--------------------------------------------------------------------------
        --}}

        <div class="preview-box">
            <h3>Contact Details</h3>

            <div class="preview-list">

                <div class="preview-item">
                    <strong>Address</strong>
                    <span>{{ $content->address ?: 'Not set' }}</span>
                </div>

                <div class="preview-item">
                    <strong>Phone</strong>
                    <span>{{ $content->phone_number ?: 'Not set' }}</span>
                </div>

                <div class="preview-item">
                    <strong>Email</strong>
                    <span>{{ $content->email ?: 'Not set' }}</span>
                </div>

                <div class="preview-item">
                    <strong>Facebook</strong>
                    <span>{{ $content->facebook_url ?: 'Not set' }}</span>
                </div>

                <div class="preview-item">
                    <strong>Instagram</strong>
                    <span>{{ $content->instagram_url ?: 'Not set' }}</span>
                </div>

                <div class="preview-item">
                    <strong>TikTok</strong>
                    <span>{{ $content->tiktok_url ?: 'Not set' }}</span>
                </div>

                <div class="preview-item">
                    <strong>WhatsApp URL</strong>
                    <span>{{ $content->whatsapp_url ?: 'Not set' }}</span>
                </div>

                <div class="preview-item">
                    <strong>WhatsApp Number</strong>
                    <span>{{ $content->whatsapp_number ?: 'Not set' }}</span>
                </div>

            </div>
        </div>

        <div class="preview-box">
            <h3>Opening Hours</h3>

            <p>
                {{ $content->opening_hours ?: 'No opening hours set' }}
            </p>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Announcement Preview
        |--------------------------------------------------------------------------
        --}}

        <div class="preview-box">

            <h3>
                {{ $content->announcement_title ?: 'Announcement' }}
            </h3>

            <p>
                {{ $content->announcement_message ?: 'No announcement message set' }}
            </p>

            <span class="status-pill {{ $content->announcement_status }}">
                {{ ucfirst($content->announcement_status) }}
            </span>

        </div>

    </aside>

</div>

@endsection
