<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This model represents the website content settings.
|
| Purpose:
| - Used by Admin Content Management.
| - Stores editable public website information and image paths.
|
| Defense explanation:
| This connects the content_settings database table to Laravel so the admin
| can update website text and uploaded images through the system UI.
*/

class ContentSetting extends Model
{
    protected $fillable = [
        'salon_name',
        'tagline',
        'hero_title',
        'hero_subtitle',

        'hero_slide_1_image',
        'hero_slide_2_image',
        'hero_slide_3_image',
        'hero_slide_4_image',
        'hero_slide_5_image',

        'about_title',
        'about_description',
        'address',
        'phone_number',
        'email',
        'facebook_url',
        'instagram_url',
        'opening_hours',
        'studio_image_path',

        'announcement_title',
        'announcement_message',
        'announcement_status',
        'updated_by',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}