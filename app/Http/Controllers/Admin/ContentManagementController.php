<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This controller handles the Admin Content Management module.
|
| Purpose:
| - Display editable website content.
| - Save updated text content.
| - Upload homepage slideshow and studio images.
| - Record updates in audit logs.
|
| Defense explanation:
| This supports maintainability because the admin can update homepage
| content and images without modifying source code.
*/

class ContentManagementController extends Controller
{
    public function index()
    {
        $content = ContentSetting::firstOrCreate(
            ['id' => 1],
            $this->defaultContent()
        );

        return view('admin.content', compact('content'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'salon_name' => ['required', 'string', 'max:120'],
            'tagline' => ['required', 'string', 'max:150'],

            'hero_title' => ['required', 'string', 'max:150'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],

            'hero_slide_1_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_slide_2_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_slide_3_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_slide_4_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_slide_5_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'studio_image_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'remove_hero_slide_1_image' => ['nullable', 'boolean'],
            'remove_hero_slide_2_image' => ['nullable', 'boolean'],
            'remove_hero_slide_3_image' => ['nullable', 'boolean'],
            'remove_hero_slide_4_image' => ['nullable', 'boolean'],
            'remove_hero_slide_5_image' => ['nullable', 'boolean'],
            'remove_studio_image_path' => ['nullable', 'boolean'],

            'about_title' => ['required', 'string', 'max:150'],
            'about_description' => ['nullable', 'string', 'max:1500'],

            'address' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:1000'],

            'announcement_title' => ['nullable', 'string', 'max:150'],
            'announcement_message' => ['nullable', 'string', 'max:1000'],
            'announcement_status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $content = ContentSetting::firstOrCreate(
            ['id' => 1],
            $this->defaultContent()
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Separate normal text fields from uploaded image fields.
        |--------------------------------------------------------------------------
        */
        $data = collect($validated)
            ->except([
                'hero_slide_1_image',
                'hero_slide_2_image',
                'hero_slide_3_image',
                'hero_slide_4_image',
                'hero_slide_5_image',
                'studio_image_path',
                'remove_hero_slide_1_image',
                'remove_hero_slide_2_image',
                'remove_hero_slide_3_image',
                'remove_hero_slide_4_image',
                'remove_hero_slide_5_image',
                'remove_studio_image_path',
            ])
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Image fields are stored in storage/app/public/content.
        | Database stores only the relative path, example: content/file.jpg
        |--------------------------------------------------------------------------
        */
        $imageFields = [
            'hero_slide_1_image',
            'hero_slide_2_image',
            'hero_slide_3_image',
            'hero_slide_4_image',
            'hero_slide_5_image',
            'studio_image_path',
        ];

        foreach ($imageFields as $field) {
            $removeField = 'remove_' . $field;

            if ($request->boolean($removeField)) {
                $this->deleteImage($content->{$field});
                $data[$field] = null;
            }

            if ($request->hasFile($field)) {
                $this->deleteImage($content->{$field});
                $data[$field] = $request->file($field)->store('content', 'public');
            }
        }

        $data['updated_by'] = auth()->id();

        $content->update($data);

        $this->createAuditLog(
            action: 'update_content_management',
            description: 'Admin updated website content and uploaded image settings.'
        );

        return redirect()
            ->route('admin.content')
            ->with('success', 'Content settings updated successfully.');
    }

    private function defaultContent(): array
    {
        return [
            'salon_name' => 'Nail Studio & Beauty',
            'tagline' => 'Your Beauty, Our Priority',
            'hero_title' => 'Welcome to Nail Studio & Beauty',
            'hero_subtitle' => 'Book your nail, lash, brow, facial, and beauty services with ease.',
            'about_title' => 'About Nail Studio & Beauty',
            'about_description' => 'Nail Studio & Beauty provides professional beauty services with organized booking, staff scheduling, and customer care.',
            'address' => 'Donggongon, Penampang, Sabah',
            'phone_number' => '',
            'email' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'opening_hours' => "Monday - Saturday: 9:00 AM - 6:00 PM\nSunday: Closed",
            'announcement_status' => 'inactive',
        ];
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function createAuditLog(string $action, string $description): void
    {
        $data = [
            'user_type' => auth()->user()->role ?? 'admin',
            'action' => $action,
            'description' => $description,
        ];

        if (Schema::hasColumn('audit_logs', 'user_id')) {
            $data['user_id'] = auth()->id();
        }

        if (Schema::hasColumn('audit_logs', 'module')) {
            $data['module'] = 'Content Management';
        }

        if (Schema::hasColumn('audit_logs', 'ip_address')) {
            $data['ip_address'] = request()->ip();
        }

        if (Schema::hasColumn('audit_logs', 'user_agent')) {
            $data['user_agent'] = request()->userAgent();
        }

        AuditLog::create($data);
    }
}