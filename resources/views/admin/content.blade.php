@extends('layouts.app')

{{-- 
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Admin Content Management View
|
| Purpose:
| - Allows admin to update public website content.
| - Allows admin to upload homepage slideshow images.
| - Allows admin to upload the studio image.
|
| Defense explanation:
| This module improves maintainability because the admin can update text
| and images without editing source code.
|--------------------------------------------------------------------------
--}}

@section('styles')
<style>
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
}

.alert-success,
.alert-error {
    padding: 16px 18px;
    border-radius: 18px;
    margin-bottom: 18px;
    font-size: 14px;
    font-weight: 800;
}

.alert-success {
    background: rgba(81,148,91,.15);
    color: #2f7d3c;
    border: 1px solid rgba(81,148,91,.25);
}

.alert-error {
    background: rgba(184,77,77,.15);
    color: #b84d4d;
    border: 1px solid rgba(184,77,77,.25);
}

.content-grid {
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    gap: 24px;
}

.form-card,
.preview-card {
    background: var(--glass);
    backdrop-filter: blur(28px);
    border: 1px solid var(--border);
    border-radius: 28px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
    padding: 26px;
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
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group.full {
    grid-column: 1 / -1;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 900;
    color: var(--dark);
}

input,
textarea,
select {
    width: 100%;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,.65);
    outline: none;
    color: var(--dark);
    font-family: inherit;
}

textarea {
    min-height: 120px;
    resize: vertical;
}

input[type="file"] {
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

.image-upload-box {
    padding: 16px;
    border-radius: 20px;
    background: rgba(255,255,255,.45);
    border: 1px solid rgba(255,255,255,.65);
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
}

.remove-check {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 10px;
    font-size: 12px;
    color: #b84d4d;
    font-weight: 900;
}

.remove-check input {
    width: auto;
}

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
}

.preview-box p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.6;
    white-space: pre-line;
}

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

@media(max-width:1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .preview-card {
        position: static;
    }
}

@media(max-width:700px) {
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
        text-align: center;
    }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>Content Management</h1>
    <p>Update public website content, homepage images, business information, and announcements.</p>
</div>

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert-error">
        Please check the form. Some fields need correction.
    </div>
@endif

<div class="content-grid">
    <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" class="form-card">
        @csrf
        @method('PATCH')

        <div class="form-section">
            <div class="section-title">
                <h2>Business Identity</h2>
                <p>Basic salon name and tagline displayed on the website.</p>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="salon_name">Salon Name</label>
                    <input type="text" id="salon_name" name="salon_name" value="{{ old('salon_name', $content->salon_name) }}" maxlength="120" required>
                    @error('salon_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tagline">Tagline</label>
                    <input type="text" id="tagline" name="tagline" value="{{ old('tagline', $content->tagline) }}" maxlength="150" required>
                    @error('tagline')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <h2>Homepage Hero Section</h2>
                <p>Main welcome message shown to customers.</p>
            </div>

            <div class="form-group">
                <label for="hero_title">Hero Title</label>
                <input type="text" id="hero_title" name="hero_title" value="{{ old('hero_title', $content->hero_title) }}" maxlength="150" required>
                @error('hero_title')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="hero_subtitle">Hero Subtitle</label>
                <textarea id="hero_subtitle" name="hero_subtitle" maxlength="500">{{ old('hero_subtitle', $content->hero_subtitle) }}</textarea>
                <small class="help">Short message shown below the hero title.</small>
                @error('hero_subtitle')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <h2>Website Images</h2>
                <p>Upload homepage slideshow images and studio image.</p>
            </div>

            <div class="image-upload-grid">
                @for ($i = 1; $i <= 5; $i++)
                    @php
                        $field = 'hero_slide_' . $i . '_image';
                        $removeField = 'remove_' . $field;
                        $currentImage = $content->{$field};
                    @endphp

                    <div class="image-upload-box">
                        <label for="{{ $field }}">Hero Slide Image {{ $i }}</label>

                        <input type="file" id="{{ $field }}" name="{{ $field }}" accept="image/jpeg,image/png,image/jpg,image/webp">

                        <small class="help">Accepted: JPG, PNG, WEBP. Max size: 4MB.</small>

                        @if ($currentImage)
                            <div class="image-preview">
                                <img src="{{ asset('storage/' . $currentImage) }}" alt="Hero slide {{ $i }}">
                            </div>

                            <label class="remove-check">
                                <input type="checkbox" name="{{ $removeField }}" value="1">
                                Remove this image
                            </label>
                        @endif

                        @error($field)
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                @endfor

                <div class="image-upload-box">
                    <label for="studio_image_path">Studio Image</label>

                    <input type="file" id="studio_image_path" name="studio_image_path" accept="image/jpeg,image/png,image/jpg,image/webp">

                    <small class="help">This image appears in the Visit Our Studio section.</small>

                    @if ($content->studio_image_path)
                        <div class="image-preview">
                            <img src="{{ asset('storage/' . $content->studio_image_path) }}" alt="Studio image">
                        </div>

                        <label class="remove-check">
                            <input type="checkbox" name="remove_studio_image_path" value="1">
                            Remove this image
                        </label>
                    @endif

                    @error('studio_image_path')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <h2>About Section</h2>
                <p>Description of Nail Studio & Beauty.</p>
            </div>

            <div class="form-group">
                <label for="about_title">About Title</label>
                <input type="text" id="about_title" name="about_title" value="{{ old('about_title', $content->about_title) }}" maxlength="150" required>
                @error('about_title')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="about_description">About Description</label>
                <textarea id="about_description" name="about_description" maxlength="1500">{{ old('about_description', $content->about_description) }}</textarea>
                @error('about_description')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <h2>Contact Details</h2>
                <p>Business contact information displayed to customers.</p>
            </div>

            <div class="form-grid">
                <div class="form-group full">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $content->address) }}" maxlength="255">
                    @error('address')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $content->phone_number) }}" maxlength="30">
                    @error('phone_number')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $content->email) }}" maxlength="120">
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="facebook_url">Facebook URL</label>
                    <input type="url" id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $content->facebook_url) }}" maxlength="255">
                    @error('facebook_url')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="instagram_url">Instagram URL</label>
                    <input type="url" id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $content->instagram_url) }}" maxlength="255">
                    @error('instagram_url')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full">
                    <label for="opening_hours">Opening Hours</label>
                    <textarea id="opening_hours" name="opening_hours" maxlength="1000">{{ old('opening_hours', $content->opening_hours) }}</textarea>
                    <small class="help">Example: Monday - Saturday: 9:00 AM - 6:00 PM</small>
                    @error('opening_hours')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <h2>Announcement</h2>
                <p>Optional public announcement for customers.</p>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="announcement_status">Announcement Status</label>
                    <select id="announcement_status" name="announcement_status" required>
                        <option value="inactive" {{ old('announcement_status', $content->announcement_status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="active" {{ old('announcement_status', $content->announcement_status) === 'active' ? 'selected' : '' }}>Active</option>
                    </select>
                    @error('announcement_status')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="announcement_title">Announcement Title</label>
                    <input type="text" id="announcement_title" name="announcement_title" value="{{ old('announcement_title', $content->announcement_title) }}" maxlength="150">
                    @error('announcement_title')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full">
                    <label for="announcement_message">Announcement Message</label>
                    <textarea id="announcement_message" name="announcement_message" maxlength="1000">{{ old('announcement_message', $content->announcement_message) }}</textarea>
                    @error('announcement_message')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="action-row">
            <a href="{{ route('admin.content') }}" class="reset-btn">Reset</a>
            <button type="submit" class="save-btn">Save Content Changes</button>
        </div>
    </form>

    <aside class="preview-card">
        <div class="section-title">
            <h2>Content Preview</h2>
            <p>Quick view of the current saved content.</p>
        </div>

        <div class="preview-box">
            <h3>{{ $content->salon_name }}</h3>
            <p>{{ $content->tagline }}</p>
        </div>

        <div class="preview-box">
            <h3>{{ $content->hero_title }}</h3>
            <p>{{ $content->hero_subtitle ?: 'No hero subtitle set.' }}</p>
        </div>

        <div class="preview-box">
            <h3>Uploaded Images</h3>
            <p>
                Hero Slide 1: {{ $content->hero_slide_1_image ? 'Uploaded' : 'Not uploaded' }}
                Hero Slide 2: {{ $content->hero_slide_2_image ? 'Uploaded' : 'Not uploaded' }}
                Hero Slide 3: {{ $content->hero_slide_3_image ? 'Uploaded' : 'Not uploaded' }}
                Hero Slide 4: {{ $content->hero_slide_4_image ? 'Uploaded' : 'Not uploaded' }}
                Hero Slide 5: {{ $content->hero_slide_5_image ? 'Uploaded' : 'Not uploaded' }}
                Studio Image: {{ $content->studio_image_path ? 'Uploaded' : 'Not uploaded' }}
            </p>
        </div>

        <div class="preview-box">
            <h3>{{ $content->about_title }}</h3>
            <p>{{ $content->about_description ?: 'No about description set.' }}</p>
        </div>

        <div class="preview-box">
            <h3>Contact Details</h3>
            <p>
                Address: {{ $content->address ?: 'Not set' }}
                Phone: {{ $content->phone_number ?: 'Not set' }}
                Email: {{ $content->email ?: 'Not set' }}
            </p>
        </div>

        <div class="preview-box">
            <h3>Opening Hours</h3>
            <p>{{ $content->opening_hours ?: 'No opening hours set.' }}</p>
        </div>

        <div class="preview-box">
            <h3>{{ $content->announcement_title ?: 'Announcement' }}</h3>
            <p>{{ $content->announcement_message ?: 'No announcement message set.' }}</p>

            <span class="status-pill {{ $content->announcement_status }}">
                {{ ucfirst($content->announcement_status) }}
            </span>
        </div>
    </aside>
</div>

@endsection