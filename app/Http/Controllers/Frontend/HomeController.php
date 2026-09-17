<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContentSetting;
use App\Models\Service;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This controller handles the public welcome page.
|
| Purpose:
| - Load content saved by Admin Content Management.
| - Load uploaded hero slideshow images.
| - Load uploaded studio image.
| - Load available services from Service Management.
|
| Defense explanation:
| This proves that the admin's Content Management updates are reflected
| dynamically on the public website.
*/

class HomeController extends Controller
{
    public function index()
    {
        $content = ContentSetting::first();

        if (!$content) {
            $content = ContentSetting::create([
                'salon_name' => 'Nail Studio & Beauty',
                'tagline' => 'Your Beauty, Our Priority',
                'hero_title' => 'Welcome to Nail Studio & Beauty',
                'hero_subtitle' => 'Book nail, lash, brow, facial, hair, and beauty services through an organized online appointment system.',
                'about_title' => 'About Nail Studio & Beauty',
                'about_description' => 'Enjoy a cleaner appointment experience for beauty services, staff scheduling, reminders, and customer convenience.',
                'address' => 'Donggongon, Penampang, Sabah',
                'opening_hours' => "Monday - Saturday: 9:00 AM - 6:00 PM\nSunday: Closed",
                'announcement_status' => 'inactive',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | These settings match the uploaded landing page design.
        |--------------------------------------------------------------------------
        */
        $settings = [
            'business_name' => $content->salon_name,
            'business_tagline' => $content->tagline,
            'business_address' => $content->address,
            'business_phone' => $content->phone_number,
            'business_email' => $content->email,
            'facebook_url' => $content->facebook_url,
            'instagram_url' => $content->instagram_url,

            'landing_hero_eyebrow' => 'Beauty studio in Penampang, Sabah',
            'landing_hero_title' => $content->hero_title,
            'landing_hero_description' => $content->hero_subtitle,

            'landing_services_title' => 'Services',
            'landing_services_description' => 'Click any service to view its description, price, duration, and service category.',

            'landing_about_title' => $content->about_title,
            'landing_about_description' => $content->about_description,

            'business_hours_note' => $content->opening_hours,

            'studio_image_path' => $content->studio_image_path,

            'hero_slide_1_image' => $content->hero_slide_1_image,
            'hero_slide_2_image' => $content->hero_slide_2_image,
            'hero_slide_3_image' => $content->hero_slide_3_image,
            'hero_slide_4_image' => $content->hero_slide_4_image,
            'hero_slide_5_image' => $content->hero_slide_5_image,

            'landing_cta_title' => 'Ready to book your beauty appointment?',
            'landing_cta_description' => 'Create an account or login to request an appointment and manage your booking.',
            'landing_primary_button_text' => 'Create account to book',
            'landing_secondary_button_text' => 'Login',
            'landing_cta_button_text' => 'Create account',
        ];

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Hero slides are built from images uploaded in Content Management.
        |--------------------------------------------------------------------------
        */
        $heroSlides = collect([
            [
                'image_path' => $content->hero_slide_1_image,
                'alt_text' => 'Nail Studio & Beauty hero slide 1',
                'sort_order' => 1,
                'is_active' => !empty($content->hero_slide_1_image),
            ],
            [
                'image_path' => $content->hero_slide_2_image,
                'alt_text' => 'Nail Studio & Beauty hero slide 2',
                'sort_order' => 2,
                'is_active' => !empty($content->hero_slide_2_image),
            ],
            [
                'image_path' => $content->hero_slide_3_image,
                'alt_text' => 'Nail Studio & Beauty hero slide 3',
                'sort_order' => 3,
                'is_active' => !empty($content->hero_slide_3_image),
            ],
            [
                'image_path' => $content->hero_slide_4_image,
                'alt_text' => 'Nail Studio & Beauty hero slide 4',
                'sort_order' => 4,
                'is_active' => !empty($content->hero_slide_4_image),
            ],
            [
                'image_path' => $content->hero_slide_5_image,
                'alt_text' => 'Nail Studio & Beauty hero slide 5',
                'sort_order' => 5,
                'is_active' => !empty($content->hero_slide_5_image),
            ],
        ])
            ->filter(fn ($slide) => $slide['is_active'])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Public welcome page only displays services marked as Available.
        |--------------------------------------------------------------------------
        */
        $services = Service::whereIn('status', ['Available', 'available'])
            ->orderBy('service_name')
            ->get();

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