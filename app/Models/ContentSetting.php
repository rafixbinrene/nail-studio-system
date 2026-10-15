<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Content Setting Model
|--------------------------------------------------------------------------
| Purpose:
| - Store editable website content managed by the administrator
| - Store hero slideshow and studio image paths
| - Store social media and contact details
| - Store announcement content and visibility
|
| Defense Explanation:
| This Eloquent model connects Admin Content Management
| and the public Welcome Page through the content_settings table
|--------------------------------------------------------------------------
*/

class ContentSetting extends Model
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Mass Assignable Fields
    |--------------------------------------------------------------------------
    | These fields can be saved through validated controller requests
    |
    | Important:
    | The database must contain the corresponding columns
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        /*
        |--------------------------------------------------------------------------
        | Business Identity
        |--------------------------------------------------------------------------
        */
        'salon_name',
        'tagline',

        /*
        |--------------------------------------------------------------------------
        | Homepage Hero Content
        |--------------------------------------------------------------------------
        */
        'hero_title',
        'hero_subtitle',

        /*
        |--------------------------------------------------------------------------
        | Hero Slideshow Images
        |--------------------------------------------------------------------------
        */
        'hero_slide_1_image',
        'hero_slide_2_image',
        'hero_slide_3_image',
        'hero_slide_4_image',
        'hero_slide_5_image',

        /*
        |--------------------------------------------------------------------------
        | About and Business Information
        |--------------------------------------------------------------------------
        */
        'about_title',
        'about_description',
        'address',
        'phone_number',
        'email',
        'opening_hours',

        /*
        |--------------------------------------------------------------------------
        | Social Media and Contact
        |--------------------------------------------------------------------------
        */
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'whatsapp_url',
        'whatsapp_number',

        /*
        |--------------------------------------------------------------------------
        | Studio Image
        |--------------------------------------------------------------------------
        */
        'studio_image_path',

        /*
        |--------------------------------------------------------------------------
        | Public Announcements
        |--------------------------------------------------------------------------
        */
        'announcement_title',
        'announcement_message',
        'announcement_status',

        /*
        |--------------------------------------------------------------------------
        | Audit Reference
        |--------------------------------------------------------------------------
        */
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Last Administrator Who Updated Content
    |--------------------------------------------------------------------------
    */

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
