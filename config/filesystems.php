
<?php

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Filesystem Configuration
|--------------------------------------------------------------------------
| Purpose:
| - Configure local and cloud file storage.
| - Preserve existing public storage for other NS Beauty modules.
| - Support persistent homepage images through Laravel Cloud.
|
| Defense Explanation:
| The system uses Laravel's filesystem abstraction to separate
| uploaded image management from database storage.
|
| MySQL stores image paths, while Laravel Cloud Object Storage
| stores the actual image files.
|--------------------------------------------------------------------------
*/

return [

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Default Filesystem Disk
    |--------------------------------------------------------------------------
    | Keep local as the fallback default.
    |
    | Laravel Cloud may inject its own environment configuration.
    | Content Management explicitly uses the content_images disk.
    |--------------------------------------------------------------------------
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Available Filesystem Disks
    |--------------------------------------------------------------------------
    */

    'disks' => [

        /*
        |--------------------------------------------------------------------------
        | Local Private Storage
        |--------------------------------------------------------------------------
        */

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Existing Public Storage
        |--------------------------------------------------------------------------
        | Used by existing NS Beauty features that store files locally.
        |
        | This disk is intentionally preserved to avoid affecting
        | other modules such as Service Management.
        |--------------------------------------------------------------------------
        */

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(
                env('APP_URL', 'http://localhost'),
                '/'
            ) . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Content Images Disk
        |--------------------------------------------------------------------------
        | Purpose:
        | - Store homepage slideshow images.
        | - Store the studio image.
        |
        | Local development:
        | Uses storage/app/public through the local filesystem.
        |
        | Laravel Cloud production:
        | When the bucket is attached using the disk name
        | content_images, Laravel Cloud can inject disk-specific
        | variables into the deployed environment.
        |
        | Important:
        | Disk name must remain content_images.
        |--------------------------------------------------------------------------
        */

        'content_images' => [
            'driver' => env(
                'CONTENT_IMAGES_DRIVER',
                'local'
            ),

            'root' => storage_path('app/public'),

            'url' => env(
                'CONTENT_IMAGES_URL',
                rtrim(
                    env('APP_URL', 'http://localhost'),
                    '/'
                ) . '/storage'
            ),

            'key' => env('CONTENT_IMAGES_KEY'),

            'secret' => env('CONTENT_IMAGES_SECRET'),

            'region' => env(
                'CONTENT_IMAGES_REGION',
                'auto'
            ),

            'bucket' => env('CONTENT_IMAGES_BUCKET'),

            'endpoint' => env('CONTENT_IMAGES_ENDPOINT'),

            'use_path_style_endpoint' => env(
                'CONTENT_IMAGES_PATH_STYLE',
                false
            ),

            'visibility' => 'public',

            'throw' => true,

            'report' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Standard S3 Disk
        |--------------------------------------------------------------------------
        | Preserved for compatibility with other Laravel features.
        |--------------------------------------------------------------------------
        */

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env(
                'AWS_USE_PATH_STYLE_ENDPOINT',
                false
            ),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Symbolic Links
    |--------------------------------------------------------------------------
    | Local development needs php artisan storage:link.
    |
    | Images stored in Laravel Cloud Object Storage do not rely
    | on a local public/storage symbolic link.
    |--------------------------------------------------------------------------
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
