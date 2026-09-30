@php

    /*

    |--------------------------------------------------------------------------

    | NS Beauty Database-Ready Landing Page

    |--------------------------------------------------------------------------

    | Save as: resources/views/welcome.blade.php

    |

    | This page is designed for the real Nail Studio & Beauty system.

    | Public content should come from the admin panel/database.

    |

    | Recommended variables from your controller:

    | - $settings / $businessSettings        BusinessSetting::pluck('setting_value', 'setting_key')

    | - $heroSlides                          HeroSlide records encoded by admin

    | - $services / $featuredServices        Active Service records encoded by admin

    | - $businessHours                       BusinessHour records encoded by admin

    | - $landingStats / $stats               Optional landing stat cards encoded by admin

    | - $whyChooseCards / $aboutCards        Optional why-choose cards encoded by admin

    |

    | Image path rule:

    | - Admin uploads should normally be saved in storage/app/public/...

    | - Then display using paths such as: services/nail-art.jpg

    | - The helper below will convert it to asset('storage/services/nail-art.jpg')

    */



    /*

    |--------------------------------------------------------------------------

    | NS BEAUTY COMMENT:

    | Load Admin Content Management Data

    |--------------------------------------------------------------------------

    | Purpose:

    | - Reads the saved ContentSetting record created from Admin Content Management.

    | - Connects admin-uploaded hero/studio images to the public welcome page.

    | - Uses safety checks so the landing page will not go blank if the table

    |   or columns are not available yet during local/deployment testing.

    |--------------------------------------------------------------------------

    */

    $adminContentSettings = [];



    try {

        if (

            class_exists(\App\Models\ContentSetting::class)

            && \Illuminate\Support\Facades\Schema::hasTable('content_settings')

        ) {

            $adminContent = \App\Models\ContentSetting::query()->first();



            if ($adminContent) {

                $adminContentSettings = [

                    /*

                    |--------------------------------------------------------------------------

                    | NS BEAUTY COMMENT:

                    | Business identity mapping

                    |--------------------------------------------------------------------------

                    | Admin Content Management fields:

                    | - salon_name

                    | - tagline

                    |--------------------------------------------------------------------------

                    */

                    'business_name' => data_get($adminContent, 'salon_name'),

                    'business_tagline' => data_get($adminContent, 'tagline'),



                    /*

                    |--------------------------------------------------------------------------

                    | NS BEAUTY COMMENT:

                    | Hero section mapping

                    |--------------------------------------------------------------------------

                    | Admin Content Management fields:

                    | - hero_title

                    | - hero_subtitle

                    |

                    | Welcome page setting keys:

                    | - landing_hero_title

                    | - landing_hero_description

                    |--------------------------------------------------------------------------

                    */

                    'landing_hero_title' => data_get($adminContent, 'hero_title'),

                    'landing_hero_description' => data_get($adminContent, 'hero_subtitle'),



                    /*

                    |--------------------------------------------------------------------------

                    | NS BEAUTY COMMENT:

                    | About section mapping

                    |--------------------------------------------------------------------------

                    */

                    'landing_about_title' => data_get($adminContent, 'about_title'),

                    'landing_about_description' => data_get($adminContent, 'about_description'),



                    /*

                    |--------------------------------------------------------------------------

                    | NS BEAUTY COMMENT:

                    | Contact section mapping

                    |--------------------------------------------------------------------------

                    */

                    'business_address' => data_get($adminContent, 'address'),

                    'business_phone' => data_get($adminContent, 'phone_number'),

                    'business_email' => data_get($adminContent, 'email'),

                    'facebook_url' => data_get($adminContent, 'facebook_url'),

                    'instagram_url' => data_get($adminContent, 'instagram_url'),

                    'business_hours_note' => data_get($adminContent, 'opening_hours'),



                    /*

                    |--------------------------------------------------------------------------

                    | NS BEAUTY COMMENT:

                    | Uploaded homepage images

                    |--------------------------------------------------------------------------

                    | These paths are saved by Admin Content Management as:

                    | content/example-image.jpg

                    |

                    | The $fileUrl helper below converts them to:

                    | /storage/content/example-image.jpg

                    |--------------------------------------------------------------------------

                    */

                    'hero_slide_1_image' => data_get($adminContent, 'hero_slide_1_image'),

                    'hero_slide_2_image' => data_get($adminContent, 'hero_slide_2_image'),

                    'hero_slide_3_image' => data_get($adminContent, 'hero_slide_3_image'),

                    'hero_slide_4_image' => data_get($adminContent, 'hero_slide_4_image'),

                    'hero_slide_5_image' => data_get($adminContent, 'hero_slide_5_image'),

                    'studio_image_path' => data_get($adminContent, 'studio_image_path'),

                ];

            }

        }

    } catch (\Throwable $exception) {

        /*

        |--------------------------------------------------------------------------

        | NS BEAUTY COMMENT:

        | Safe Fallback

        |--------------------------------------------------------------------------

        | Purpose:

        | - Prevents the public welcome page from going blank if Content Management

        |   data is temporarily unavailable.

        |--------------------------------------------------------------------------

        */

        $adminContentSettings = [];

    }



    /*

    |--------------------------------------------------------------------------

    | NS BEAUTY COMMENT:

    | Final Settings Source

    |--------------------------------------------------------------------------

    | Purpose:

    | - Keeps any controller-provided settings.

    | - Lets Admin Content Management override matching welcome page keys.

    |--------------------------------------------------------------------------

    */

    $rawSettings = collect($settings ?? $businessSettings ?? [])

        ->merge($adminContentSettings);



    $settingsArray = collect($rawSettings)->mapWithKeys(function ($item, $key) {

        if (is_array($item) || is_object($item)) {

            $settingKey = data_get($item, 'setting_key', data_get($item, 'key'));

            $settingValue = data_get($item, 'setting_value', data_get($item, 'value'));



            if ($settingKey !== null) {

                return [$settingKey => $settingValue];

            }

        }



        return [$key => $item];

    })->toArray();



    $setting = function (string $key, $default = '') use ($settingsArray) {

        $value = data_get($settingsArray, $key);



        if ($value === null) {

            return $default;

        }



        if (is_string($value) && trim($value) === '') {

            return $default;

        }



        return $value;

    };



    $fileUrl = function ($path) {

        if ($path === null) {

            return null;

        }



        $path = trim((string) $path);



        if ($path === '') {

            return null;

        }



        if (\Illuminate\Support\Str::startsWith($path, ['http\://', 'https\://', '//'])) {

            return $path;

        }



        $path = ltrim($path, '/');



        if (\Illuminate\Support\Str::startsWith($path, ['images/', 'assets/', 'storage/'])) {

            return asset($path);

        }



        return asset('storage/' . $path);

    };



    $formatPrice = function ($service) {

        $priceLabel = data_get($service, 'price_label');



        if (!blank($priceLabel)) {

            return $priceLabel;

        }



        $price = data_get($service, 'price', data_get($service, 'service_price'));



        if (is_numeric($price)) {

            return 'RM ' . number_format((float) $price, 2);

        }



        return 'Price to be updated';

    };



    $formatDuration = function ($service) {

        $durationLabel = data_get($service, 'duration_label');



        if (!blank($durationLabel)) {

            return $durationLabel;

        }



        $minutes = data_get($service, 'duration_minutes', data_get($service, 'duration'));



        if (is_numeric($minutes)) {

            $minutes = (int) $minutes;



            if ($minutes >= 60) {

                $hours = intdiv($minutes, 60);

                $remainingMinutes = $minutes % 60;



                return $remainingMinutes > 0

                    ? $hours . ' hr ' . $remainingMinutes . ' min'

                    : $hours . ' hr';

            }



            return $minutes . ' min';

        }



        return 'Duration to be updated';

    };



    $businessName = $setting('business_name', 'Nail Studio & Beauty');

    $businessTagline = $setting('business_tagline', 'Your Beauty, Our Priority.');

    $businessLocation = $setting('business_address', 'Kedai Sedco Block E, Lot 3, Ground Floor, Donggongon, Penampang, Sabah, Malaysia');

    $businessEstablished = $setting('business_established', 'January 2026');

    $businessArea = $setting('business_area', 'Donggongon, Penampang, Sabah');

    $businessPhone = $setting('business_phone');

    $businessEmail = $setting('business_email');

    $facebookUrl = $setting('facebook_url');

    $instagramUrl = $setting('instagram_url');

    $tiktokUrl = $setting('tiktok_url');



    $heroEyebrow = $setting('landing_hero_eyebrow', 'Beauty studio in Penampang, Sabah');

    $heroTitle = $setting('landing_hero_title', $businessTagline);

    $heroDescription = $setting(

        'landing_hero_description',

        'Book nail, lash, brow, facial, hair, and beauty services through an organized online appointment system.'

    );



    $serviceSectionTitle = $setting('landing_services_title', 'Services');

    $serviceSectionDescription = $setting(

        'landing_services_description',

        'Click any service to view its description, price, duration, and service category.'

    );



    $aboutSectionTitle = $setting('landing_about_title', 'Why Choose ' . $businessName . '?');

    $aboutSectionDescription = $setting(

        'landing_about_description',

        'Enjoy a cleaner appointment experience for beauty services, staff scheduling, reminders, and customer convenience.'

    );



    $visitTitle = $setting('landing_visit_title', 'Visit Our Studio');

    $ctaTitle = $setting('landing_cta_title', 'Ready to book your beauty appointment?');

    $ctaDescription = $setting(

        'landing_cta_description',

        'Create an account or login to request an appointment and manage your booking.'

    );



    $studioImage = $fileUrl($setting('studio_image_path', 'images/nsbeauty/studio.jpg'));



    $loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');

    $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/register');



    $dashboardUrl = null;



    if (auth()->check()) {

        $userRole = auth()->user()->role ?? 'customer';



        if ($userRole === 'admin') {

            $dashboardUrl = url('/admin/dashboard');

        } elseif ($userRole === 'staff') {

            $dashboardUrl = url('/staff/dashboard');

        } else {

            $dashboardUrl = url('/customer/dashboard');

        }

    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Hero Slides From Admin Content Management
    |--------------------------------------------------------------------------
    | Purpose:
    | - Prioritizes the images uploaded in Admin > Content Management.
    | - Fixes the issue where old/default hero images still appear.
    | - Reads hero_slide_1_image to hero_slide_5_image from content_settings.
    |--------------------------------------------------------------------------
    */
    $settingSlides = collect();

    for ($i = 1; $i <= 5; $i++) {
        $imagePath = $setting("hero_slide_{$i}_image");

        if (!blank($imagePath)) {
            $settingSlides->push([
                'image_path' => $imagePath,
                'alt_text' => $setting("hero_slide_{$i}_alt", $businessName . ' studio photo'),
                'title' => $setting("hero_slide_{$i}_title"),
                'description' => $setting("hero_slide_{$i}_description"),
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Fallback Hero Slides
    |--------------------------------------------------------------------------
    | Purpose:
    | - If Admin has uploaded Content Management images, use them first.
    | - If no Admin images exist, continue using controller-provided hero slides.
    |--------------------------------------------------------------------------
    */
    if ($settingSlides->isNotEmpty()) {
        $heroSlides = $settingSlides;
    } else {
        $heroSlideSource = $heroSlides ?? [];

        $heroSlides = collect($heroSlideSource)
            ->filter(fn ($slide) => (bool) data_get($slide, 'is_active', true))
            ->sortBy(fn ($slide) => data_get($slide, 'sort_order', data_get($slide, 'order', 999)))
            ->values();
    }

    $serviceSource = $featuredServices ?? $services ?? [];

    $landingServices = collect($serviceSource)

        ->filter(fn ($service) => (bool) data_get($service, 'is_active', true))

        ->sortBy(fn ($service) => data_get($service, 'sort_order', data_get($service, 'name', data_get($service, 'service_name'))))

        ->values();



    $publishedServiceCount = $landingServices->count();



    $statsSource = $landingStats ?? $stats ?? [];

    $statCards = collect($statsSource)

        ->filter(fn ($stat) => (bool) data_get($stat, 'is_active', true))

        ->sortBy(fn ($stat) => data_get($stat, 'sort_order', data_get($stat, 'order', 999)))

        ->values();



    if ($statCards->isEmpty()) {

        $statCards = collect([

            [

                'value' => $setting('landing_stat_1_value', '2026'),

                'label' => $setting('landing_stat_1_label', 'Established in ' . $businessEstablished),

            ],

            [

                'value' => $setting('landing_stat_2_value', $setting('team_count', '4')),

                'label' => $setting('landing_stat_2_label', 'Owner and staff team'),

            ],

            [

                'value' => $setting('landing_stat_3_value', $publishedServiceCount > 0 ? $publishedServiceCount . '+' : 'Services'),

                'label' => $setting('landing_stat_3_label', 'Published beauty services'),

            ],

            [

                'value' => $setting('landing_stat_4_value', 'Sabah'),

                'label' => $setting('landing_stat_4_label', 'Located in ' . $businessArea),

            ],

        ]);

    }



    $whyCardSource = $whyChooseCards ?? $aboutCards ?? [];

    $whyCards = collect($whyCardSource)

        ->filter(fn ($card) => (bool) data_get($card, 'is_active', true))

        ->sortBy(fn ($card) => data_get($card, 'sort_order', data_get($card, 'order', 999)))

        ->values();



    if ($whyCards->isEmpty()) {

        $whyCards = collect([

            [

                'icon' => $setting('why_card_1_icon', '✨'),

                'title' => $setting('why_card_1_title', 'Beauty Service Focus'),

                'description' => $setting('why_card_1_description', 'Services are organized into categories so customers can easily browse and choose what they need.'),

            ],

            [

                'icon' => $setting('why_card_2_icon', '📅'),

                'title' => $setting('why_card_2_title', 'Online Appointment Request'),

                'description' => $setting('why_card_2_description', 'Customers can request appointments through the system instead of relying only on manual chat messages.'),

            ],

            [

                'icon' => $setting('why_card_3_icon', '🕊️'),

                'title' => $setting('why_card_3_title', 'Organized Scheduling'),

                'description' => $setting('why_card_3_description', 'The system helps display available schedules and prevents overlapping bookings.'),

            ],

            [

                'icon' => $setting('why_card_4_icon', '🔔'),

                'title' => $setting('why_card_4_title', 'Customer Updates'),

                'description' => $setting('why_card_4_description', 'Customers can receive booking confirmations, reminders, and appointment status updates.'),

            ],

        ]);

    }



    $businessHourSource = $businessHours ?? [];

    $businessHourRows = collect($businessHourSource)

        ->sortBy(fn ($hour) => data_get($hour, 'sort_order', data_get($hour, 'day_order', 999)))

        ->values();



    $businessHoursNote = $setting('business_hours_note', 'Operating hours will be available soon.');

@endphp



<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $setting('landing_meta_title', $businessName . ' | Beauty Appointment System') }}</title>

    <meta name="description" content="{{ $setting('landing_meta_description', $businessName . ' online appointment and service information page.') }}">

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">

    <meta http-equiv="Pragma" content="no-cache">

    <meta http-equiv="Expires" content="0">



    <style>

        * {

            box-sizing: border-box;

            margin: 0;

            padding: 0;

            font-family: "Segoe UI", Arial, sans-serif;

        }



        :root {

            --brown: #6f4e37;

            --brown-dark: #2f2118;

            --brown-soft: #8a7158;

            --cream: #f8f6f2;

            --pearl: #fffdf9;

            --muted: #7d6d60;

            --line: rgba(255, 255, 255, 0.68);

            --glass: rgba(255, 255, 255, 0.54);

            --shadow: 0 28px 70px rgba(47, 33, 24, 0.16);

        }



        html {

            scroll-behavior: smooth;

        }



        body {

            min-height: 100vh;

            color: var(--brown-dark);

            overflow-x: hidden;

            background:

                radial-gradient(circle at 15% 18%, rgba(255, 255, 255, 0.78), transparent 28%),

                radial-gradient(circle at 88% 12%, rgba(234, 216, 196, 0.88), transparent 30%),

                linear-gradient(135deg, #f1e6d8 0%, #fffdf9 45%, #e7d2bd 100%);

        }



        body.modal-open {

            overflow: hidden;

        }



        a {

            color: inherit;

            text-decoration: none;

        }



        button,

        input,

        textarea,

        select {

            font: inherit;

        }



        .glass {

            background: var(--glass);

            backdrop-filter: blur(24px);

            -webkit-backdrop-filter: blur(24px);

            border: 1px solid var(--line);

            box-shadow: var(--shadow);

        }



        .top-nav {

            width: min(1180px, 92%);

            position: sticky;

            top: 18px;

            z-index: 50;

            margin: 18px auto 0;

            padding: 14px 18px;

            border-radius: 999px;

            display: flex;

            align-items: center;

            justify-content: space-between;

        }



        .brand {

            display: flex;

            align-items: center;

            gap: 12px;

            min-width: 0;

        }



        .brand-mark {

            width: 38px;

            height: 38px;

            border-radius: 50%;

            display: grid;

            place-items: center;

            color: var(--pearl);

            font-size: 16px;

            font-weight: 900;

            background: linear-gradient(135deg, #8a7158, #5d3f2c);

            flex: 0 0 auto;

        }



        .brand-name {

            font-size: 17px;

            font-weight: 900;

            letter-spacing: -0.3px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;

        }



        .nav-links {

            display: flex;

            align-items: center;

            gap: 24px;

        }



        .nav-links a {

            font-size: 13px;

            font-weight: 750;

            color: #4f3b2f;

            transition: 0.2s ease;

        }



        .nav-links a:hover {

            color: var(--brown);

        }



        .btn-main,

        .btn {

            border: none;

            display: inline-flex;

            justify-content: center;

            align-items: center;

            gap: 8px;

            border-radius: 999px;

            cursor: pointer;

            font-weight: 850;

            transition: 0.22s ease;

        }



        .btn-main {

            padding: 11px 18px;

            color: var(--pearl) !important;

            background: linear-gradient(135deg, #8a7158, #5d3f2c);

            box-shadow: 0 15px 30px rgba(93, 63, 44, 0.22);

        }



        .btn-main:hover,

        .btn:hover {

            transform: translateY(-2px);

        }



        .menu-btn {

            width: 44px;

            height: 44px;

            display: none;

            border: none;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.7);

            color: var(--brown-dark);

            cursor: pointer;

            font-size: 22px;

            font-weight: 900;

        }



        .hero {

            width: min(1180px, 92%);

            margin: 34px auto 0;

            display: grid;

            grid-template-columns: 1.02fr 0.98fr;

            gap: 34px;

            align-items: center;

            min-height: calc(100vh - 120px);

            padding: 30px 0 58px;

        }



        .hero-panel {

            border-radius: 38px;

            padding: clamp(30px, 5vw, 62px);

        }



        .eyebrow {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            margin-bottom: 22px;

            padding: 9px 14px;

            border-radius: 999px;

            background: rgba(255, 255, 255, 0.6);

            border: 1px solid rgba(255, 255, 255, 0.76);

            color: var(--brown);

            font-size: 12px;

            font-weight: 900;

            letter-spacing: 0.2px;

        }



        .hero h1 {

            max-width: 680px;

            font-size: clamp(42px, 7vw, 78px);

            line-height: 0.95;

            letter-spacing: -2.7px;

            margin-bottom: 24px;

        }



        .hero p {

            max-width: 600px;

            color: var(--muted);

            font-size: 16px;

            line-height: 1.8;

            margin-bottom: 30px;

        }



        .hero-actions {

            display: flex;

            flex-wrap: wrap;

            gap: 14px;

        }



        .btn {

            padding: 14px 22px;

            font-size: 14px;

        }



        .btn-brown {

            color: var(--pearl);

            background: linear-gradient(135deg, #8a7158, #5d3f2c);

            box-shadow: 0 18px 36px rgba(93, 63, 44, 0.22);

        }



        .btn-light {

            color: var(--brown-dark);

            background: rgba(255, 255, 255, 0.68);

            border: 1px solid rgba(255, 255, 255, 0.82);

        }



        .slideshow {

            min-height: 560px;

            border-radius: 42px;

            overflow: hidden;

            position: relative;

            border: 1px solid rgba(255, 255, 255, 0.7);

            box-shadow: 0 38px 86px rgba(47, 33, 24, 0.18);

            background:

                linear-gradient(135deg, rgba(111, 78, 55, 0.18), rgba(255, 253, 249, 0.5)),

                linear-gradient(135deg, #d9c2ac, #fff7ee);

        }



        .slide {

            width: 100%;

            height: 100%;

            min-height: 560px;

            object-fit: cover;

            position: absolute;

            inset: 0;

            opacity: 0;

            transform: scale(1.04);

            transition: opacity 0.9s ease, transform 1.8s ease;

        }



        .slide.active {

            opacity: 1;

            transform: scale(1);

        }



        .image-placeholder::after {

            content: "NS Beauty";

            position: absolute;

            inset: 0;

            display: grid;

            place-items: center;

            color: rgba(47, 33, 24, 0.42);

            font-size: clamp(34px, 5vw, 64px);

            font-weight: 950;

            letter-spacing: -1px;

            text-align: center;

            pointer-events: none;

        }



        .floating-card {

            position: absolute;

            left: 22px;

            right: 22px;

            bottom: 22px;

            padding: 22px;

            border-radius: 28px;

            background: rgba(255, 253, 249, 0.62);

            backdrop-filter: blur(20px);

            -webkit-backdrop-filter: blur(20px);

            border: 1px solid rgba(255, 255, 255, 0.75);

        }



        .floating-card h2 {

            font-size: 18px;

            margin-bottom: 8px;

        }



        .floating-card p {

            color: var(--muted);

            font-size: 13px;

            line-height: 1.6;

            margin: 0;

        }



        .stats {

            width: min(1180px, 92%);

            margin: 0 auto 22px;

            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 18px;

        }



        .stat-card {

            padding: 25px 20px;

            border-radius: 28px;

            text-align: center;

        }



        .stat-card h2 {

            font-size: 30px;

            margin-bottom: 8px;

            background: linear-gradient(135deg, #6f4e37, #b08a67);

            -webkit-background-clip: text;

            -webkit-text-fill-color: transparent;

        }



        .stat-card p {

            font-size: 13px;

            color: var(--muted);

            font-weight: 750;

            line-height: 1.5;

        }



        .section {

            width: min(1180px, 92%);

            margin: 0 auto;

            padding: 72px 0;

        }



        .section-title {

            text-align: center;

            margin-bottom: 42px;

        }



        .section-title h2 {

            font-size: clamp(30px, 4vw, 42px);

            margin-bottom: 12px;

            letter-spacing: -0.8px;

        }



        .section-title p {

            color: var(--muted);

            font-size: 14px;

            line-height: 1.7;

        }



        .services-grid {

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 26px;

        }



        .service-card {

            width: 100%;

            text-align: left;

            border: 1px solid rgba(255, 255, 255, 0.72);

            background: rgba(255, 255, 255, 0.48);

            border-radius: 30px;

            padding: 14px;

            color: var(--brown-dark);

            box-shadow: 0 22px 48px rgba(47, 33, 24, 0.1);

            transition: 0.24s ease;

            cursor: pointer;

        }



        .service-card:hover,

        .service-card:focus-visible {

            transform: translateY(-8px);

            box-shadow: 0 34px 72px rgba(47, 33, 24, 0.18);

            outline: none;

        }



        .service-img {

            height: 238px;

            border-radius: 24px;

            overflow: hidden;

            position: relative;

            margin-bottom: 18px;

            background:

                linear-gradient(135deg, rgba(111, 78, 55, 0.14), rgba(255, 253, 249, 0.55)),

                linear-gradient(135deg, #d9c2ac, #fff7ee);

        }



        .service-img img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

        }



        .service-badge {

            position: absolute;

            top: 14px;

            right: 14px;

            max-width: calc(100% - 28px);

            background: rgba(255, 253, 249, 0.78);

            backdrop-filter: blur(16px);

            -webkit-backdrop-filter: blur(16px);

            padding: 8px 13px;

            border-radius: 999px;

            font-size: 12px;

            font-weight: 900;

            color: var(--brown);

            border: 1px solid rgba(255, 255, 255, 0.74);

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;

        }



        .service-card h3 {

            font-size: 18px;

            margin: 0 8px 10px;

            letter-spacing: -0.2px;

        }



        .service-meta {

            display: flex;

            flex-wrap: wrap;

            gap: 9px;

            margin: 0 8px 12px;

        }



        .service-meta span {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            border-radius: 999px;

            padding: 7px 10px;

            background: rgba(255, 255, 255, 0.55);

            color: var(--muted);

            font-size: 12px;

            font-weight: 750;

        }



        .click-note {

            margin: 0 8px 8px;

            color: #9a8778;

            font-size: 12px;

            font-weight: 700;

        }



        .why-grid {

            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 22px;

        }



        .why-card {

            padding: 28px;

            border-radius: 28px;

            text-align: center;

        }



        .why-card span {

            display: grid;

            place-items: center;

            width: 52px;

            height: 52px;

            margin: 0 auto 16px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.58);

            font-size: 24px;

        }



        .why-card h3 {

            margin-bottom: 10px;

            font-size: 16px;

        }



        .why-card p {

            color: var(--muted);

            font-size: 13px;

            line-height: 1.7;

        }



        .visit {

            display: grid;

            grid-template-columns: 0.95fr 1.05fr;

            gap: 34px;

            align-items: stretch;

        }



        .info-panel {

            padding: clamp(26px, 4vw, 42px);

            border-radius: 34px;

        }



        .info-panel h2 {

            font-size: clamp(30px, 4vw, 40px);

            margin-bottom: 26px;

            letter-spacing: -0.8px;

        }



        .info-box {

            padding: 18px 0;

            border-bottom: 1px solid rgba(111, 78, 55, 0.12);

        }



        .info-box:last-child {

            border-bottom: none;

        }



        .info-box h3 {

            font-size: 15px;

            margin-bottom: 8px;

        }



        .info-box p {

            color: var(--muted);

            font-size: 14px;

            line-height: 1.75;

        }



        .studio-image {

            min-height: 430px;

            border-radius: 34px;

            overflow: hidden;

            position: relative;

            border: 1px solid rgba(255, 255, 255, 0.72);

            box-shadow: var(--shadow);

            background:

                linear-gradient(135deg, rgba(111, 78, 55, 0.14), rgba(255, 253, 249, 0.55)),

                linear-gradient(135deg, #d9c2ac, #fff7ee);

        }



        .studio-image img {

            width: 100%;

            height: 100%;

            min-height: 430px;

            object-fit: cover;

            display: block;

        }



        .cta {

            width: min(1180px, 92%);

            margin: 0 auto 40px;

            padding: clamp(28px, 5vw, 48px);

            border-radius: 34px;

            display: grid;

            grid-template-columns: 1fr auto;

            gap: 22px;

            align-items: center;

        }



        .cta h2 {

            font-size: clamp(28px, 4vw, 40px);

            margin-bottom: 10px;

            letter-spacing: -0.7px;

        }



        .cta p {

            color: var(--muted);

            line-height: 1.7;

        }



        footer {

            width: min(1180px, 92%);

            margin: 0 auto 24px;

            background: rgba(47, 33, 24, 0.9);

            backdrop-filter: blur(20px);

            -webkit-backdrop-filter: blur(20px);

            color: var(--pearl);

            text-align: center;

            padding: 40px 7%;

            border-radius: 34px;

            box-shadow: 0 25px 70px rgba(47, 33, 24, 0.2);

        }



        footer h3 {

            margin-bottom: 13px;

            font-size: 20px;

        }



        footer p {

            color: #d8cfc4;

            font-size: 13px;

            line-height: 1.7;

        }



        .copyright {

            margin-top: 22px;

            color: #b8aa9a;

            font-size: 12px;

        }



        .modal-overlay {

            position: fixed;

            inset: 0;

            z-index: 999;

            display: none;

            align-items: center;

            justify-content: center;

            padding: 20px;

            background: rgba(47, 33, 24, 0.34);

            backdrop-filter: blur(18px);

            -webkit-backdrop-filter: blur(18px);

        }



        .modal-overlay.show {

            display: flex;

        }



        .modal-box {

            width: min(540px, 100%);

            max-height: min(760px, 92vh);

            overflow: auto;

            border-radius: 32px;

            background: rgba(255, 253, 249, 0.76);

            backdrop-filter: blur(30px);

            -webkit-backdrop-filter: blur(30px);

            border: 1px solid rgba(255, 255, 255, 0.78);

            box-shadow: 0 35px 90px rgba(47, 33, 24, 0.26);

            animation: popUp 0.22s ease;

        }



        @keyframes popUp {

            from {

                opacity: 0;

                transform: translateY(18px) scale(0.98);

            }



            to {

                opacity: 1;

                transform: translateY(0) scale(1);

            }

        }



        .modal-image-wrap {

            height: 260px;

            position: relative;

            overflow: hidden;

            background:

                linear-gradient(135deg, rgba(111, 78, 55, 0.16), rgba(255, 253, 249, 0.55)),

                linear-gradient(135deg, #d9c2ac, #fff7ee);

        }



        .modal-image-wrap img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

        }



        .modal-content {

            padding: 28px;

        }



        .modal-content h3 {

            font-size: 25px;

            margin-bottom: 12px;

            letter-spacing: -0.5px;

        }



        .modal-meta {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

            margin-bottom: 18px;

        }



        .modal-meta span {

            background: rgba(255, 255, 255, 0.62);

            border: 1px solid rgba(255, 255, 255, 0.75);

            padding: 8px 12px;

            border-radius: 999px;

            font-size: 12px;

            font-weight: 850;

            color: var(--brown);

        }



        .modal-content p {

            color: var(--muted);

            line-height: 1.75;

            font-size: 14px;

            margin-bottom: 22px;

        }



        .close-btn {

            width: 100%;

            border: none;

            padding: 14px;

            border-radius: 999px;

            background: linear-gradient(135deg, #8a7158, #5d3f2c);

            color: white;

            font-weight: 900;

            cursor: pointer;

            transition: 0.2s ease;

        }



        .close-btn:hover {

            transform: translateY(-1px);

        }



        @media (max-width: 980px) {

            .hero,

            .visit,

            .cta {

                grid-template-columns: 1fr;

            }



            .slideshow,

            .slide {

                min-height: 440px;

            }



            .stats,

            .why-grid {

                grid-template-columns: repeat(2, 1fr);

            }



            .services-grid {

                grid-template-columns: repeat(2, 1fr);

            }



            .cta .btn {

                width: fit-content;

            }

        }



        @media (max-width: 720px) {

            .top-nav {

                align-items: flex-start;

                border-radius: 28px;

                padding: 12px;

            }



            .brand {

                padding: 4px 0 4px 4px;

            }



            .menu-btn {

                display: grid;

                place-items: center;

            }



            .nav-links {

                display: none;

                width: 100%;

                position: absolute;

                left: 0;

                right: 0;

                top: calc(100% + 10px);

                padding: 14px;

                border-radius: 28px;

                background: rgba(255, 253, 249, 0.9);

                backdrop-filter: blur(24px);

                -webkit-backdrop-filter: blur(24px);

                border: 1px solid rgba(255, 255, 255, 0.75);

                box-shadow: var(--shadow);

                flex-direction: column;

                align-items: stretch;

                gap: 8px;

            }



            .nav-links.show {

                display: flex;

            }



            .nav-links a {

                padding: 12px 14px;

                border-radius: 18px;

                background: rgba(255, 255, 255, 0.42);

            }



            .nav-links .btn-main {

                text-align: center;

            }



            .hero {

                margin-top: 22px;

                min-height: auto;

                padding-bottom: 42px;

            }



            .hero-panel {

                border-radius: 30px;

            }



            .hero h1 {

                letter-spacing: -1.5px;

            }



            .hero-actions,

            .cta .btn {

                width: 100%;

            }



            .hero-actions .btn,

            .cta .btn {

                width: 100%;

            }



            .slideshow,

            .slide {

                min-height: 340px;

                border-radius: 32px;

            }



            .stats,

            .services-grid,

            .why-grid {

                grid-template-columns: 1fr;

            }



            .section {

                padding: 54px 0;

            }



            .service-img {

                height: 218px;

            }



            .studio-image,

            .studio-image img {

                min-height: 320px;

            }



            .modal-image-wrap {

                height: 210px;

            }

        }





        .empty-state {

            width: min(720px, 92%);

            margin: 0 auto;

            padding: 26px;

            border-radius: 28px;

            text-align: center;

            color: var(--muted);

            background: rgba(255, 253, 249, 0.58);

            border: 1px solid var(--line);

            box-shadow: var(--shadow);

            line-height: 1.8;

        }



        .empty-state strong {

            display: inline-block;

            margin-bottom: 6px;

            color: var(--brown-dark);

            font-size: 18px;

        }



        .hours-list {

            list-style: none;

            display: grid;

            gap: 10px;

        }



        .hours-list li {

            display: flex;

            justify-content: space-between;

            gap: 18px;

            padding-bottom: 10px;

            border-bottom: 1px solid rgba(111, 78, 55, 0.12);

            color: var(--muted);

            line-height: 1.5;

        }



        .hours-list li:last-child {

            border-bottom: none;

            padding-bottom: 0;

        }



        .hours-list strong {

            color: var(--brown-dark);

            white-space: nowrap;

        }



        .hours-list span {

            text-align: right;

        }



        .contact-links,

        .footer-socials {

            display: flex;

            flex-wrap: wrap;

            gap: 10px;

            margin-top: 12px;

        }



        .contact-links a,

        .footer-socials a {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 9px 13px;

            border-radius: 999px;

            color: var(--brown-dark);

            background: rgba(255, 255, 255, 0.62);

            border: 1px solid rgba(111, 78, 55, 0.12);

            font-size: 13px;

            font-weight: 700;

        }



        .modal-meta span:empty {

            display: none;

        }



        @media (max-width: 680px) {

            .hours-list li {

                align-items: flex-start;

                flex-direction: column;

                gap: 4px;

            }



            .hours-list span {

                text-align: left;

            }

        }



    </style>

</head>



<body>

    <nav class="top-nav glass" aria-label="Main navigation">

        <a href="{{ url('/') }}" class="brand" aria-label="{{ $businessName }} home">

            <span class="brand-mark">NS</span>

            <span class="brand-name">{{ $businessName }}</span>

        </a>



        <button class="menu-btn" type="button" onclick="toggleMenu()" aria-label="Open navigation menu" aria-expanded="false" id="menuButton">

            ☰

        </button>



        <div class="nav-links" id="navLinks">

            <a href="{{ url('/') }}">Home</a>

            <a href="#services">Services</a>

            <a href="#about">About</a>

            <a href="#visit">Visit</a>



            @auth

                <a href="{{ $dashboardUrl }}" class="btn-main">Dashboard</a>

            @else

                <a href="{{ $loginUrl }}">Login</a>

                <a href="{{ $registerUrl }}" class="btn-main">Register</a>

            @endauth

        </div>

    </nav>



    <main>

        <section class="hero" id="home">

            <div class="hero-panel glass">

                <span class="eyebrow">{{ $heroEyebrow }}</span>



                <h1>{{ $heroTitle }}</h1>



                <p>{{ $heroDescription }}</p>



                <div class="hero-actions">

                    @auth

                        <a href="{{ $dashboardUrl }}" class="btn btn-brown">Go to dashboard</a>

                    @else

                        <a href="{{ $registerUrl }}" class="btn btn-brown">{{ $setting('landing_primary_button_text', 'Create account to book') }}</a>

                        <a href="{{ $loginUrl }}" class="btn btn-light">{{ $setting('landing_secondary_button_text', 'Login') }}</a>

                    @endauth

                </div>

            </div>



            <div class="slideshow {{ $heroSlides->isEmpty() ? 'image-placeholder' : '' }}" id="heroSlideshow" aria-label="Studio service photos">

                @forelse ($heroSlides as $index => $slide)

                    @php

                        $slideImage = $fileUrl(

                            data_get($slide, 'image_path')

                            ?? data_get($slide, 'image')

                            ?? data_get($slide, 'src')

                            ?? data_get($slide, 'photo')

                        );



                        $slideAlt = data_get($slide, 'alt_text')

                            ?? data_get($slide, 'alt')

                            ?? data_get($slide, 'title')

                            ?? $businessName . ' studio photo';

                    @endphp



                    @if ($slideImage)

                        <img

                            src="{{ $slideImage }}"

                            class="slide {{ $index === 0 ? 'active' : '' }}"

                            alt="{{ $slideAlt }}"

                            onerror="handleImageError(this)"

                        >

                    @endif

                @empty

                @endforelse



                <div class="floating-card">

                    <h2>{{ $setting('hero_floating_title', 'Clean, calm, and organized beauty care') }}</h2>

                    <p>{{ $setting('hero_floating_description', 'Designed for real appointment handling, staff scheduling, and customer convenience.') }}</p>

                </div>

            </div>

        </section>



        <section class="stats" aria-label="Business information">

            @foreach ($statCards as $stat)

                <div class="stat-card glass">

                    <h2>{{ data_get($stat, 'value', data_get($stat, 'stat_value')) }}</h2>

                    <p>{{ data_get($stat, 'label', data_get($stat, 'stat_label')) }}</p>

                </div>

            @endforeach

        </section>



        <section class="section" id="services">

            <div class="section-title">

                <h2>{{ $serviceSectionTitle }}</h2>

                <p>{{ $serviceSectionDescription }}</p>

            </div>



            @if ($landingServices->isNotEmpty())

                <div class="services-grid">

                    @foreach ($landingServices as $service)

                        @php

                            $serviceName = data_get($service, 'name')

                                ?? data_get($service, 'service_name')

                                ?? data_get($service, 'title')

                                ?? 'Service';



                            $serviceDescription = data_get($service, 'description')

                                ?? data_get($service, 'service_description')

                                ?? 'Service details are available during appointment selection.';



                            $categoryName = data_get($service, 'category.name')

                                ?? data_get($service, 'category_name')

                                ?? data_get($service, 'category')

                                ?? 'Beauty Service';



                            $priceText = $formatPrice($service);

                            $durationText = $formatDuration($service);



                            $serviceImage = $fileUrl(

                                data_get($service, 'image_path')

                                ?? data_get($service, 'image')

                                ?? data_get($service, 'photo')

                                ?? data_get($service, 'thumbnail')

                            );

                        @endphp



                        <button

                            type="button"

                            class="service-card"

                            onclick="openServiceModal(this)"

                            data-title="{{ $serviceName }}"

                            data-description="{{ $serviceDescription }}"

                            data-price="{{ $priceText }}"

                            data-duration="{{ $durationText }}"

                            data-category="{{ $categoryName }}"

                            data-image="{{ $serviceImage }}"

                        >

                            <div class="service-img {{ $serviceImage ? '' : 'image-placeholder' }}">

                                <span class="service-badge">{{ $priceText }}</span>



                                @if ($serviceImage)

                                    <img src="{{ $serviceImage }}" alt="{{ $serviceName }}" onerror="handleImageError(this)">

                                @endif

                            </div>



                            <h3>{{ $serviceName }}</h3>



                            <div class="service-meta">

                                <span>⏱ {{ $durationText }}</span>

                            </div>



                            <div class="click-note">Click to view description</div>

                        </button>

                    @endforeach

                </div>

            @else

                <div class="empty-state">

                    <strong>No services are available yet.</strong><br>

                    Services will appear here once they are published in the system.

                </div>

            @endif

        </section>



        <section class="section" id="about">

            <div class="section-title">

                <h2>{{ $aboutSectionTitle }}</h2>

                <p>{{ $aboutSectionDescription }}</p>

            </div>



            <div class="why-grid">

                @foreach ($whyCards as $card)

                    <div class="why-card glass">

                        <span>{{ data_get($card, 'icon', '✨') }}</span>

                        <h3>{{ data_get($card, 'title', data_get($card, 'heading', 'Content title')) }}</h3>

                        <p>{{ data_get($card, 'description', data_get($card, 'body', 'Details are available through the booking system.')) }}</p>

                    </div>

                @endforeach

            </div>

        </section>



        <section class="section visit" id="visit">

            <div class="info-panel glass">

                <h2>{{ $visitTitle }}</h2>



                <div class="info-box">

                    <h3>Location</h3>

                    <p>{{ $businessLocation }}</p>

                </div>



                <div class="info-box">

                    <h3>Operating Hours</h3>



                    @if ($businessHourRows->isNotEmpty())

                        <ul class="hours-list">

                            @foreach ($businessHourRows as $hour)

                                @php

                                    $dayLabel = data_get($hour, 'day_label', data_get($hour, 'day'));

                                    $isClosed = (bool) data_get($hour, 'is_closed', false);

                                    $openTime = data_get($hour, 'open_time', data_get($hour, 'opening_time'));

                                    $closeTime = data_get($hour, 'close_time', data_get($hour, 'closing_time'));

                                    $notes = data_get($hour, 'notes');

                                @endphp



                                <li>

                                    <strong>{{ $dayLabel }}</strong>

                                    <span>

                                        @if ($isClosed)

                                            Closed

                                        @elseif ($notes)

                                            {{ $notes }}

                                        @elseif ($openTime && $closeTime)

                                            {{ \Carbon\Carbon::parse($openTime)->format('g:i A') }} - {{ \Carbon\Carbon::parse($closeTime)->format('g:i A') }}

                                        @else

                                            To be updated

                                        @endif

                                    </span>

                                </li>

                            @endforeach

                        </ul>

                    @else

                        <p>{{ $businessHoursNote }}</p>

                    @endif

                </div>



                <div class="info-box">

                    <h3>Contact</h3>



                    @if ($businessPhone || $businessEmail || $facebookUrl || $instagramUrl || $tiktokUrl)

                        <p>{{ $setting('contact_note', 'You may contact the studio through the available channels below.') }}</p>



                        <div class="contact-links">

                            @if ($businessPhone)

                                <a href="tel:{{ preg_replace('/\s+/', '', $businessPhone) }}">{{ $businessPhone }}</a>

                            @endif



                            @if ($businessEmail)

                                <a href="mailto:{{ $businessEmail }}">{{ $businessEmail }}</a>

                            @endif



                            @if ($facebookUrl)

                                <a href="{{ $facebookUrl }}" target="_blank" rel="noopener">Facebook</a>

                            @endif



                            @if ($instagramUrl)

                                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener">Instagram</a>

                            @endif



                            @if ($tiktokUrl)

                                <a href="{{ $tiktokUrl }}" target="_blank" rel="noopener">TikTok</a>

                            @endif

                        </div>

                    @else

                        <p>Contact details will be available soon.</p>

                    @endif

                </div>

            </div>



            <div class="studio-image {{ $studioImage ? '' : 'image-placeholder' }}">

                @if ($studioImage)

                    <img src="{{ $studioImage }}" alt="{{ $businessName }} studio" onerror="handleImageError(this)">

                @endif

            </div>

        </section>



        <section class="cta glass">

            <div>

                <h2>{{ $ctaTitle }}</h2>

                <p>{{ $ctaDescription }}</p>

            </div>



            @auth

                <a href="{{ $dashboardUrl }}" class="btn btn-brown">Go to dashboard</a>

            @else

                <a href="{{ $registerUrl }}" class="btn btn-brown">{{ $setting('landing_cta_button_text', 'Create account') }}</a>

            @endauth

        </section>

    </main>



    <footer>

        <h3>{{ $businessName }}</h3>

        <p>

            {{ $businessTagline }}<br>

            {{ $businessArea }}

        </p>



        @if ($facebookUrl || $instagramUrl || $tiktokUrl)

            <div class="footer-socials">

                @if ($facebookUrl)

                    <a href="{{ $facebookUrl }}" target="_blank" rel="noopener">Facebook</a>

                @endif



                @if ($instagramUrl)

                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener">Instagram</a>

                @endif



                @if ($tiktokUrl)

                    <a href="{{ $tiktokUrl }}" target="_blank" rel="noopener">TikTok</a>

                @endif

            </div>

        @endif



        <p class="copyright">© {{ date('Y') }} {{ $businessName }}. All rights reserved.</p>

    </footer>



    <div class="modal-overlay" id="serviceModal" onclick="closeServiceModal(event)" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-box">

            <div class="modal-image-wrap" id="modalImageWrap">

                <img id="modalImage" src="" alt="Service image" onerror="handleImageError(this)">

            </div>



            <div class="modal-content">

                <h3 id="modalTitle"></h3>



                <div class="modal-meta">

                    <span id="modalPrice"></span>

                    <span id="modalDuration"></span>

                    <span id="modalCategory"></span>

                </div>



                <p id="modalDescription"></p>



                <button class="close-btn" type="button" onclick="closeModalButton()">Back to services</button>

            </div>

        </div>

    </div>



    <script>

        let currentSlide = 0;



        const slides = document.querySelectorAll("#heroSlideshow .slide");



        if (slides.length > 1) {

            setInterval(() => {

                slides[currentSlide].classList.remove("active");

                currentSlide = (currentSlide + 1) % slides.length;

                slides[currentSlide].classList.add("active");

            }, 5000);

        }



        function toggleMenu() {

            const navLinks = document.getElementById("navLinks");

            const menuButton = document.getElementById("menuButton");

            const isOpen = navLinks.classList.toggle("show");



            menuButton.setAttribute("aria-expanded", isOpen ? "true" : "false");

            menuButton.textContent = isOpen ? "×" : "☰";

        }



        function openServiceModal(button) {

            const modal = document.getElementById("serviceModal");

            const modalImageWrap = document.getElementById("modalImageWrap");

            const modalImage = document.getElementById("modalImage");



            document.getElementById("modalTitle").textContent = button.dataset.title || "Service";

            document.getElementById("modalDescription").textContent = button.dataset.description || "Service description will be updated by the admin.";

            document.getElementById("modalPrice").textContent = button.dataset.price || "Price to be updated";

            document.getElementById("modalDuration").textContent = button.dataset.duration || "Duration to be updated";

            document.getElementById("modalCategory").textContent = button.dataset.category || "Beauty Service";



            modalImageWrap.classList.remove("image-placeholder");

            modalImage.style.display = "block";



            if (button.dataset.image) {

                modalImage.src = button.dataset.image;

                modalImage.alt = button.dataset.title || "Service image";

            } else {

                modalImage.removeAttribute("src");

                modalImage.style.display = "none";

                modalImageWrap.classList.add("image-placeholder");

            }



            modal.classList.add("show");

            document.body.classList.add("modal-open");

        }



        function closeServiceModal(event) {

            if (event.target.id === "serviceModal") {

                closeModalButton();

            }

        }



        function closeModalButton() {

            document.getElementById("serviceModal").classList.remove("show");

            document.body.classList.remove("modal-open");

        }



        function handleImageError(image) {

            const wrapper = image.closest(".slideshow, .service-img, .studio-image, .modal-image-wrap");



            image.style.display = "none";



            if (wrapper) {

                wrapper.classList.add("image-placeholder");

            }

        }



        document.addEventListener("keydown", function(event) {

            if (event.key === "Escape") {

                closeModalButton();

            }

        });



        document.querySelectorAll("#navLinks a").forEach((link) => {

            link.addEventListener("click", () => {

                const navLinks = document.getElementById("navLinks");

                const menuButton = document.getElementById("menuButton");



                navLinks.classList.remove("show");

                menuButton.setAttribute("aria-expanded", "false");

                menuButton.textContent = "☰";

            });

        });

    </script>

</body>

</html>
