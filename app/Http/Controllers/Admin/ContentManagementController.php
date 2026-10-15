<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Admin Content Management Controller
|--------------------------------------------------------------------------
| Purpose:
| - Manage public website content
| - Upload and remove hero slideshow and studio images
| - Save announcements and their visibility status
| - Save business contact details and social media links
| - Record administrative changes through Audit Logs
|
| Defense Explanation:
| This module enables administrators to update the public website
| using the centralized database without editing website source code
|--------------------------------------------------------------------------
*/

class ContentManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Cloud Image Storage
    |--------------------------------------------------------------------------
    | Images are stored in the content_images filesystem disk
    | connected to Laravel Cloud Object Storage
    |--------------------------------------------------------------------------
    */

    private const IMAGE_DISK = 'content_images';

    private const IMAGE_FIELDS = [
        'hero_slide_1_image',
        'hero_slide_2_image',
        'hero_slide_3_image',
        'hero_slide_4_image',
        'hero_slide_5_image',
        'studio_image_path',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Display Content Management
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $content = ContentSetting::firstOrCreate(
            ['id' => 1],
            $this->defaultContent()
        );

        return view('admin.content', compact('content'));
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Update Website Content
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Content Validation
        |--------------------------------------------------------------------------
        | Preserve all existing validation requirements
        |--------------------------------------------------------------------------
        */

        $rules = [
            'salon_name' => [
                'required',
                'string',
                'max:120',
            ],

            'tagline' => [
                'required',
                'string',
                'max:150',
            ],

            'hero_title' => [
                'required',
                'string',
                'max:150',
            ],

            'hero_subtitle' => [
                'nullable',
                'string',
                'max:500',
            ],

            'about_title' => [
                'required',
                'string',
                'max:150',
            ],

            'about_description' => [
                'nullable',
                'string',
                'max:1500',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:120',
            ],

            'facebook_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'instagram_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'opening_hours' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Announcement Validation
            |--------------------------------------------------------------------------
            */

            'announcement_title' => [
                'nullable',
                'string',
                'max:150',
            ],

            'announcement_message' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'announcement_status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: TikTok and WhatsApp Validation
        |--------------------------------------------------------------------------
        | Only accept these fields when their database columns exist
        |
        | This avoids attempting to save into nonexistent columns
        | while we verify the ContentSetting migration
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('content_settings', 'tiktok_url')) {
            $rules['tiktok_url'] = [
                'nullable',
                'url',
                'max:255',
            ];
        }

        if (Schema::hasColumn('content_settings', 'whatsapp_url')) {
            $rules['whatsapp_url'] = [
                'nullable',
                'string',
                'max:255',
            ];
        }

        if (Schema::hasColumn('content_settings', 'whatsapp_number')) {
            $rules['whatsapp_number'] = [
                'nullable',
                'string',
                'max:30',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Image Validation
        |--------------------------------------------------------------------------
        | Allow JPG, JPEG, PNG and WEBP
        | Maximum size: 4 MB
        |--------------------------------------------------------------------------
        */

        foreach (self::IMAGE_FIELDS as $field) {
            $rules[$field] = [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ];

            $rules['remove_' . $field] = [
                'nullable',
                'boolean',
            ];
        }

        $validated = $request->validate($rules);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Retrieve Current Settings
        |--------------------------------------------------------------------------
        */

        $content = ContentSetting::firstOrCreate(
            ['id' => 1],
            $this->defaultContent()
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Separate Text Fields from Images
        |--------------------------------------------------------------------------
        */

        $imageAndRemovalFields = [];

        foreach (self::IMAGE_FIELDS as $field) {
            $imageAndRemovalFields[] = $field;
            $imageAndRemovalFields[] = 'remove_' . $field;
        }

        $data = collect($validated)
            ->except($imageAndRemovalFields)
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Track Image Uploads
        |--------------------------------------------------------------------------
        */

        $newPaths = [];
        $oldPaths = [];

        try {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Upload and Remove Images
            |--------------------------------------------------------------------------
            | Image uploads take priority over removal checkboxes
            |--------------------------------------------------------------------------
            */

            foreach (self::IMAGE_FIELDS as $field) {
                $remove = $request->boolean(
                    'remove_' . $field
                );

                $upload = $request->file($field);

                if ($upload) {
                    $path = $upload->store(
                        'content',
                        self::IMAGE_DISK
                    );

                    if (!$path) {
                        throw new \RuntimeException(
                            "Unable to save image: {$field}"
                        );
                    }

                    $newPaths[] = $path;
                    $oldPaths[] = $content->{$field};
                    $data[$field] = $path;

                } elseif ($remove) {
                    $oldPaths[] = $content->{$field};
                    $data[$field] = null;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Save Content Changes
            |--------------------------------------------------------------------------
            | Force-fill only fields that passed validation
            |
            | This also supports the new social media fields without
            | depending on their inclusion in the model's $fillable
            |--------------------------------------------------------------------------
            */

            $data['updated_by'] = auth()->id();

            $content->forceFill($data);
            $content->save();

        } catch (\Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Remove Newly Uploaded Images on Failure
            |--------------------------------------------------------------------------
            */

            foreach ($newPaths as $path) {
                try {
                    Storage::disk(self::IMAGE_DISK)
                        ->delete($path);

                } catch (\Throwable $ignored) {
                    // NS BEAUTY COMMENT: Preserve the original error
                }
            }

            report($e);

            throw ValidationException::withMessages([
                'content' =>
                    'The website content could not be saved. '
                    . 'Please verify the settings and image storage '
                    . 'configuration, then try again.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Delete Replaced Images
        |--------------------------------------------------------------------------
        | Delete only images no longer referenced by Content Management
        |--------------------------------------------------------------------------
        */

        foreach (
            array_filter(array_unique($oldPaths))
            as $oldPath
        ) {
            try {
                $stillUsed = false;

                foreach (self::IMAGE_FIELDS as $field) {
                    if ($content->{$field} === $oldPath) {
                        $stillUsed = true;
                        break;
                    }
                }

                if (!$stillUsed) {
                    Storage::disk(self::IMAGE_DISK)
                        ->delete($oldPath);
                }

            } catch (\Throwable $e) {
                report($e);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Record Administrative Action
        |--------------------------------------------------------------------------
        */

        $this->createAuditLog(
            action: 'update_content_management',
            description:
                'Admin updated website content, social media, '
                . 'announcement, or uploaded image settings.'
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Return Success Message
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('admin.content')
            ->with(
                'success',
                'Content settings updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Default Website Content
    |--------------------------------------------------------------------------
    | Only original database-supported fields are initialized here
    |--------------------------------------------------------------------------
    */

    private function defaultContent(): array
    {
        return [
            'salon_name' =>
                'Nail Studio & Beauty',

            'tagline' =>
                'Your Beauty, Our Priority',

            'hero_title' =>
                'Welcome to Nail Studio & Beauty',

            'hero_subtitle' =>
                'Book your nail, lash, brow, facial, '
                . 'and beauty services with ease.',

            'about_title' =>
                'About Nail Studio & Beauty',

            'about_description' =>
                'Nail Studio & Beauty provides professional '
                . 'beauty services with organized booking, '
                . 'staff scheduling, and customer care.',

            'address' =>
                'Donggongon, Penampang, Sabah',

            'phone_number' => '',

            'email' => '',

            'facebook_url' => '',

            'instagram_url' => '',

            'opening_hours' =>
                "Monday - Saturday: 9:00 AM - 6:00 PM\n"
                . "Sunday: Closed",

            'announcement_status' => 'inactive',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Audit Logging
    |--------------------------------------------------------------------------
    */

    private function createAuditLog(
        string $action,
        string $description
    ): void {
        $data = [
            'user_type' =>
                auth()->user()->role ?? 'admin',

            'action' => $action,

            'description' => $description,
        ];

        if (
            Schema::hasColumn(
                'audit_logs',
                'user_id'
            )
        ) {
            $data['user_id'] = auth()->id();
        }

        if (
            Schema::hasColumn(
                'audit_logs',
                'module'
            )
        ) {
            $data['module'] = 'Content Management';
        }

        if (
            Schema::hasColumn(
                'audit_logs',
                'ip_address'
            )
        ) {
            $data['ip_address'] = request()->ip();
        }

        if (
            Schema::hasColumn(
                'audit_logs',
                'user_agent'
            )
        ) {
            $data['user_agent'] = request()->userAgent();
        }

        AuditLog::create($data);
    }
}
