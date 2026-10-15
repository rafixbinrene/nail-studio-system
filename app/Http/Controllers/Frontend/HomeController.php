<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContentSetting;
use App\Models\Service;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Public Home Controller
|--------------------------------------------------------------------------
| Purpose:
| - Retrieve website information from Admin Content Management.
| - Display hero slideshow images.
| - Display the studio image.
| - Display available services.
| - Display active announcements.
| - Display social media and WhatsApp contact details.
|
| Defense Explanation:
| This controller connects the central database to the public website.
| Changes saved by the administrator are reflected on the Welcome Page
| when visitors load or refresh the website.
|--------------------------------------------------------------------------
*/

class HomeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Display Welcome Page
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Retrieve Content Settings
        |--------------------------------------------------------------------------
        */

        $content = ContentSetting::first();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Initialize Default Content
        |--------------------------------------------------------------------------
        | Default content is created only when no record exists.
        | Existing administrator settings remain unchanged.
        |--------------------------------------------------------------------------
        */

        if (!$content) {
            $content = ContentSetting::create([
                'salon_name' => 'Nail Studio & Beauty',

                'tagline' => 'Your Beauty, Our Priority',

                'hero_title' =>
                    'Welcome to Nail Studio & Beauty',

                'hero_subtitle' =>
                    'Book nail, lash, brow, facial, hair, and beauty services through our online appointment system',

                'about_title' =>
                    'About Nail Studio & Beauty',

                'about_description' =>
                    'Discover professional beauty treatments and convenient appointment scheduling at Nail Studio & Beauty',

                'address' =>
                    'Donggongon, Penampang, Sabah',

                'opening_hours' =>
                    "Monday - Saturday: 9:00 AM - 6:00 PM\n"
                    . "Sunday: Closed",

                'announcement_status' => 'inactive',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Map Content Management to Welcome Page
        |--------------------------------------------------------------------------
        | All settings are sourced from the same ContentSetting record.
        |--------------------------------------------------------------------------
        */

        $settings = [
            /*
            |--------------------------------------------------------------------------
            | Business Identity
            |--------------------------------------------------------------------------
            */

            'business_name' => $content->salon_name,
            'business_tagline' => $content->tagline,
            'business_address' => $content->address,
            'business_phone' => $content->phone_number,
            'business_email' => $content->email,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Social Media and Contact Channels
            |--------------------------------------------------------------------------
            | WhatsApp can be stored as a URL or a phone number.
            |--------------------------------------------------------------------------
            */

            'facebook_url' => $content->facebook_url,
            'instagram_url' => $content->instagram_url,
            'tiktok_url' => $content->tiktok_url,

            'whatsapp_url' => $content->whatsapp_url,
            'whatsapp_number' => $content->whatsapp_number,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Hero Text
            |--------------------------------------------------------------------------
            */

            'landing_hero_eyebrow' =>
                'Beauty studio in Penampang, Sabah',

            'landing_hero_title' =>
                $content->hero_title,

            'landing_hero_description' =>
                $content->hero_subtitle,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Service Catalog Text
            |--------------------------------------------------------------------------
            */

            'landing_services_title' =>
                'Our Beauty Services',

            'landing_services_description' =>
                'Explore our nail, lash, brow, facial, and beauty treatments',

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: About Section
            |--------------------------------------------------------------------------
            */

            'landing_about_title' =>
                $content->about_title,

            'landing_about_description' =>
                $content->about_description,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Studio Operating Hours
            |--------------------------------------------------------------------------
            */

            'business_hours_note' =>
                $content->opening_hours,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Hero Slideshow Images
            |--------------------------------------------------------------------------
            | Preserve the existing Cloud Object Storage paths.
            |--------------------------------------------------------------------------
            */

            'hero_slide_1_image' =>
                $content->hero_slide_1_image,

            'hero_slide_2_image' =>
                $content->hero_slide_2_image,

            'hero_slide_3_image' =>
                $content->hero_slide_3_image,

            'hero_slide_4_image' =>
                $content->hero_slide_4_image,

            'hero_slide_5_image' =>
                $content->hero_slide_5_image,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Studio Image
            |--------------------------------------------------------------------------
            */

            'studio_image_path' =>
                $content->studio_image_path,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Public Announcements
            |--------------------------------------------------------------------------
            | Active announcements appear on the Welcome Page.
            | Inactive announcements remain hidden.
            |--------------------------------------------------------------------------
            */

            'announcement_status' =>
                $content->announcement_status,

            'announcement_title' =>
                $content->announcement_title,

            'announcement_message' =>
                $content->announcement_message,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Call-to-Action Text
            |--------------------------------------------------------------------------
            */

            'landing_cta_title' =>
                'Ready to book your beauty appointment?',

            'landing_cta_description' =>
                'Create an account or log in to manage your booking',

            'landing_primary_button_text' =>
                'Create account to book',

            'landing_secondary_button_text' =>
                'Login',

            'landing_cta_button_text' =>
                'Create account',
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Build Hero Slideshow Collection
        |--------------------------------------------------------------------------
        | Preserve the five images uploaded through Content Management.
        |--------------------------------------------------------------------------
        */

        $heroSlides = collect([
            [
                'image_path' =>
                    $content->hero_slide_1_image,

                'alt_text' =>
                    'Nail Studio & Beauty hero slide 1',

                'sort_order' => 1,

                'is_active' =>
                    !empty($content->hero_slide_1_image),
            ],
            [
                'image_path' =>
                    $content->hero_slide_2_image,

                'alt_text' =>
                    'Nail Studio & Beauty hero slide 2',

                'sort_order' => 2,

                'is_active' =>
                    !empty($content->hero_slide_2_image),
            ],
            [
                'image_path' =>
                    $content->hero_slide_3_image,

                'alt_text' =>
                    'Nail Studio & Beauty hero slide 3',

                'sort_order' => 3,

                'is_active' =>
                    !empty($content->hero_slide_3_image),
            ],
            [
                'image_path' =>
                    $content->hero_slide_4_image,

                'alt_text' =>
                    'Nail Studio & Beauty hero slide 4',

                'sort_order' => 4,

                'is_active' =>
                    !empty($content->hero_slide_4_image),
            ],
            [
                'image_path' =>
                    $content->hero_slide_5_image,

                'alt_text' =>
                    'Nail Studio & Beauty hero slide 5',

                'sort_order' => 5,

                'is_active' =>
                    !empty($content->hero_slide_5_image),
            ],
        ])
            ->filter(fn ($slide) => $slide['is_active'])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Retrieve Available Services
        |--------------------------------------------------------------------------
        | Services are synchronized with Admin Service Management.
        | No service images are used.
        |--------------------------------------------------------------------------
        */

        $services = Service::whereIn(
            'status',
            ['Available', 'available']
        )
            ->orderBy('service_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Render Welcome Page
        |--------------------------------------------------------------------------
        */

        return view('welcome', [
            'settings' => $settings,
            'heroSlides' => $heroSlides,
            'services' => $services,
            'businessHours' => collect(),
            'landingStats' => collect(),
            'whyChooseCards' => collect(),
        ]);
    }
}
