
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
| - Updates public website content.
| - Uploads hero slideshow and studio images.
| - Stores images in the dedicated content_images filesystem.
| - Records uploaded image paths in the database.
| - Records administrative updates in Audit Logs.
|
| Defense Explanation:
| This module allows administrators to manage website images and
| information dynamically without manually editing website code.
|
| In production, content_images connects to Laravel Cloud Object Storage.
| In local development, it can use the configured local fallback.
|--------------------------------------------------------------------------
*/

class ContentManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Image Storage Configuration
    |--------------------------------------------------------------------------
    | The disk name must match the disk connected in Laravel Cloud.
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
    | Validates submitted information, uploads images, and saves changes.
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $rules = [
            'salon_name' => ['required', 'string', 'max:120'],
            'tagline' => ['required', 'string', 'max:150'],

            'hero_title' => ['required', 'string', 'max:150'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],

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

            'announcement_status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validate Image Files
        |--------------------------------------------------------------------------
        | Only JPG, JPEG, PNG, and WEBP images are accepted.
        | Maximum file size: 4 MB per image.
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

        $content = ContentSetting::firstOrCreate(
            ['id' => 1],
            $this->defaultContent()
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Separate Text and Image Data
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
        | NS BEAUTY COMMENT: Upload Images
        |--------------------------------------------------------------------------
        | New images are uploaded before database changes are saved.
        | If an upload fails, newly uploaded files are cleaned up.
        |--------------------------------------------------------------------------
        */

        $newPaths = [];
        $oldPaths = [];

        try {
            foreach (self::IMAGE_FIELDS as $field) {
                $remove = $request->boolean('remove_' . $field);
                $upload = $request->file($field);

                /*
                | If an admin uploads a replacement image and also checks
                | Remove, the newly uploaded image takes priority.
                */

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
            | NS BEAUTY COMMENT: Save Database Changes
            |--------------------------------------------------------------------------
            | MySQL stores the image path, not the actual image file.
            |--------------------------------------------------------------------------
            */

            $data['updated_by'] = auth()->id();

            $content->update($data);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Upload Failure Handling
            |--------------------------------------------------------------------------
            | Clean up newly uploaded images if saving fails.
            | Record technical errors in the Laravel log.
            |--------------------------------------------------------------------------
            */

            foreach ($newPaths as $path) {
                try {
                    Storage::disk(self::IMAGE_DISK)->delete($path);
                } catch (\Throwable $ignored) {
                    // Preserve the original upload error.
                }
            }

            report($e);

            throw ValidationException::withMessages([
                'images' =>
                    'Your image could not be saved. '
                    . 'Check the connected Content Images bucket '
                    . 'and try again.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Delete Replaced Images
        |--------------------------------------------------------------------------
        | Only delete old images that are no longer referenced.
        | Older files from the original public disk are not deleted here.
        |--------------------------------------------------------------------------
        */

        foreach (array_filter(array_unique($oldPaths)) as $oldPath) {
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
        | NS BEAUTY COMMENT: Record Audit Log
        |--------------------------------------------------------------------------
        */

        $this->createAuditLog(
            action: 'update_content_management',
            description:
                'Admin updated website content and uploaded image settings.'
        );

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
    */

    private function defaultContent(): array
    {
        return [
            'salon_name' => 'Nail Studio & Beauty',
            'tagline' => 'Your Beauty, Our Priority',

            'hero_title' => 'Welcome to Nail Studio & Beauty',

            'hero_subtitle' =>
                'Book your nail, lash, brow, facial, '
                . 'and beauty services with ease.',

            'about_title' => 'About Nail Studio & Beauty',

            'about_description' =>
                'Nail Studio & Beauty provides professional '
                . 'beauty services with organized booking, '
                . 'staff scheduling, and customer care.',

            'address' => 'Donggongon, Penampang, Sabah',

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
    | Tracks administrative changes to website content.
    |--------------------------------------------------------------------------
    */

    private function createAuditLog(
        string $action,
        string $description
    ): void {
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
