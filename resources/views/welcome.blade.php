
@php
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Public Welcome Page
|--------------------------------------------------------------------------
| Purpose:
| - Display business information managed by the administrator.
| - Display hero slideshow and studio images from Cloud Object Storage.
| - Display services as text-only cards.
| - Allow customers to view service details without service images.
| - Preserve Login, Register, and Dashboard navigation.
|
| Defense Explanation:
| The website retrieves business information and service data from
| the centralized database. Service images are no longer required
| because services are represented by their name, description,
| price, and duration.
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Content Settings
|--------------------------------------------------------------------------
| Keep the existing settings mapping to preserve the working
| Admin Content Management integration.
|--------------------------------------------------------------------------
*/

$rawSettings = $settings ?? $businessSettings ?? [];

$settingsArray = collect($rawSettings)
    ->mapWithKeys(function ($item, $key) {
        if (is_array($item) || is_object($item)) {
            $settingKey = data_get(
                $item,
                'setting_key',
                data_get($item, 'key')
            );

            if ($settingKey !== null) {
                return [
                    $settingKey => data_get(
                        $item,
                        'setting_value',
                        data_get($item, 'value')
                    )
                ];
            }
        }

        return [$key => $item];
    })
    ->toArray();

$setting = function ($key, $default = '') use ($settingsArray) {
    $value = data_get($settingsArray, $key);

    return blank($value) ? $default : $value;
};

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Cloud Image URL Resolver
|--------------------------------------------------------------------------
| This resolver is used ONLY for homepage hero and studio images.
|
| Service images have been removed from the website.
|--------------------------------------------------------------------------
*/

$contentImageUrl = function ($path) {
    if (blank($path)) {
        return null;
    }

    $path = trim((string) $path);

    if (Str::startsWith($path, [
        'http://',
        'https://',
        '//',
    ])) {
        return $path;
    }

    $path = ltrim($path, '/');

    if (Str::startsWith($path, [
        'images/',
        'assets/',
        'storage/',
    ])) {
        return asset($path);
    }

    try {
        return Storage::disk('content_images')->url($path);
    } catch (\Throwable $exception) {
        report($exception);

        return null;
    }
};

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Business Information
|--------------------------------------------------------------------------
*/

$businessName = $setting(
    'business_name',
    'Nail Studio & Beauty'
);

$businessTagline = $setting(
    'business_tagline',
    'Your Beauty, Our Priority'
);

$businessLocation = $setting(
    'business_address',
    'Donggongon, Penampang, Sabah, Malaysia'
);

$businessArea = 'Donggongon, Penampang, Sabah';

$businessPhone = $setting('business_phone');
$businessEmail = $setting('business_email');

$facebookUrl = $setting('facebook_url');
$instagramUrl = $setting('instagram_url');

$heroTitle = $setting(
    'landing_hero_title',
    'Welcome to Nail Studio & Beauty'
);

$heroDescription = $setting(
    'landing_hero_description',
    'Book your beauty appointments conveniently online.'
);

$heroEyebrow = $setting(
    'landing_hero_eyebrow',
    'Beauty studio in Penampang, Sabah'
);

$aboutTitle = $setting(
    'landing_about_title',
    'About Nail Studio & Beauty'
);

$aboutDescription = $setting(
    'landing_about_description',
    'Discover professional beauty services and convenient online appointment scheduling.'
);

$serviceTitle = $setting(
    'landing_services_title',
    'Our Beauty Services'
);

$serviceDescription = $setting(
    'landing_services_description',
    'Explore our available services and select the perfect treatment for you.'
);

$businessHours = $setting(
    'business_hours_note',
    'Operating hours will be available soon.'
);

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Hero Slideshow
|--------------------------------------------------------------------------
| Important:
| Preserve the five images uploaded through Admin Content Management.
| They are stored in the content_images Laravel Cloud disk.
|--------------------------------------------------------------------------
*/

$heroSlides = collect();

for ($i = 1; $i <= 5; $i++) {
    $path = $setting("hero_slide_{$i}_image");

    if (!blank($path)) {
        $url = $contentImageUrl($path);

        if ($url) {
            $heroSlides->push([
                'image_path' => $path,
                'image_url' => $url,
                'alt_text' => $businessName . ' hero slide ' . $i,
                'sort_order' => $i,
            ]);
        }
    }
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Controller Slide Fallback
|--------------------------------------------------------------------------
*/

if ($heroSlides->isEmpty()) {
    $heroSlides = collect($heroSlideSource ?? []);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Studio Image
|--------------------------------------------------------------------------
| The studio image is NOT a service image.
| This remains connected to Admin Content Management.
|--------------------------------------------------------------------------
*/

$studioImagePath = $setting('studio_image_path');

$studioImage = $studioImagePath
    ? $contentImageUrl($studioImagePath)
    : asset('images/nsbeauty/studio.jpg');

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Service Synchronization
|--------------------------------------------------------------------------
| The same services table is used by Admin Service Management.
|
| Only Available services are displayed on the public website.
| No service image fields are read or displayed.
|--------------------------------------------------------------------------
*/

$landingServices = collect(
    $featuredServices ?? $services ?? []
)->filter(function ($service) {
    $status = data_get($service, 'status');

    if ($status !== null) {
        return strtolower((string) $status) === 'available';
    }

    return (bool) data_get($service, 'is_active', true);
})->values();

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Service Price
|--------------------------------------------------------------------------
*/

$formatPrice = function ($service) {
    $priceLabel = data_get($service, 'price_label');

    if (!blank($priceLabel)) {
        return $priceLabel;
    }

    $price = data_get(
        $service,
        'price',
        data_get($service, 'service_price')
    );

    return is_numeric($price)
        ? 'RM ' . number_format((float) $price, 2)
        : 'Price to be updated';
};

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Service Duration
|--------------------------------------------------------------------------
*/

$formatDuration = function ($service) {
    $minutes = data_get(
        $service,
        'duration_minutes',
        data_get($service, 'duration')
    );

    if (!is_numeric($minutes)) {
        return data_get(
            $service,
            'duration_label',
            'Duration to be updated'
        );
    }

    $minutes = (int) $minutes;

    if ($minutes >= 60) {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $remaining
            ? $hours . ' hr ' . $remaining . ' min'
            : $hours . ' hr';
    }

    return $minutes . ' min';
};

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Authentication
|--------------------------------------------------------------------------
*/

$loginUrl = Route::has('login')
    ? route('login')
    : url('/login');

$registerUrl = Route::has('register')
    ? route('register')
    : url('/register');

$dashboardUrl = null;

if (auth()->check()) {
    $role = auth()->user()->role ?? 'customer';

    $dashboardUrl = match ($role) {
        'admin' => url('/admin/dashboard'),
        'staff' => url('/staff/dashboard'),
        default => url('/customer/dashboard'),
    };
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Statistics
|--------------------------------------------------------------------------
*/

$statCards = collect($landingStats ?? []);

if ($statCards->isEmpty()) {
    $statCards = collect([
        [
            'value' => '2026',
            'label' => 'Established',
        ],
        [
            'value' => '4',
            'label' => 'Owner and Staff',
        ],
        [
            'value' => $landingServices->count(),
            'label' => 'Available Services',
        ],
        [
            'value' => 'Sabah',
            'label' => 'Our Location',
        ],
    ]);
}

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Why Choose Us
|--------------------------------------------------------------------------
*/

$whyCards = collect($whyChooseCards ?? []);

if ($whyCards->isEmpty()) {
    $whyCards = collect([
        [
            'icon' => '✨',
            'title' => 'Beauty Service Focus',
            'description' => 'Explore our professional beauty services in one convenient website.',
        ],
        [
            'icon' => '📅',
            'title' => 'Online Booking',
            'description' => 'Schedule appointments using our organized online booking system.',
        ],
        [
            'icon' => '🕊️',
            'title' => 'Organized Scheduling',
            'description' => 'View available schedules and avoid overlapping appointments.',
        ],
        [
            'icon' => '🔔',
            'title' => 'Booking Updates',
            'description' => 'Receive confirmations, reminders, and appointment updates.',
        ],
    ]);
}
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Nail Studio & Beauty online booking and beauty services."
    >

    <title>{{ $businessName }} | Beauty Appointment System</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Website Theme
        |--------------------------------------------------------------------------
        */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        :root {
            --brown: #6f4e37;
            --brown-dark: #2f2118;
            --brown-soft: #8a7158;
            --cream: #f8f6f2;
            --pearl: #fffdf9;
            --muted: #7d6d60;
            --line: rgba(255,255,255,.68);
            --glass: rgba(255,255,255,.54);
            --shadow: 0 28px 70px rgba(47,33,24,.16);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            color: var(--brown-dark);
            overflow-x: hidden;
            background:
                radial-gradient(circle at 15% 18%, rgba(255,255,255,.78), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(234,216,196,.88), transparent 30%),
                linear-gradient(135deg,#f1e6d8 0%,#fffdf9 45%,#e7d2bd 100%);
        }

        body.modal-open {
            overflow: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font: inherit;
        }

        .glass {
            background: var(--glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Navigation
        |--------------------------------------------------------------------------
        */

        .top-nav {
            width: min(1180px,92%);
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
            color: white;
            font-weight: 900;
            background: linear-gradient(135deg,#8a7158,#5d3f2c);
        }

        .brand-name {
            font-size: 17px;
            font-weight: 900;
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
        }

        .btn,
        .btn-main {
            border: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 850;
            transition: .22s ease;
        }

        .btn-main {
            padding: 11px 18px;
            color: white !important;
            background: linear-gradient(135deg,#8a7158,#5d3f2c);
        }

        .btn:hover,
        .btn-main:hover {
            transform: translateY(-2px);
        }

        .menu-btn {
            display: none;
            width: 44px;
            height: 44px;
            border: none;
            border-radius: 50%;
            background: rgba(255,255,255,.7);
            cursor: pointer;
            font-size: 22px;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Hero Section
        |--------------------------------------------------------------------------
        */

        .hero {
            width: min(1180px,92%);
            margin: 34px auto 0;
            display: grid;
            grid-template-columns: 1.02fr .98fr;
            gap: 34px;
            align-items: center;
            min-height: calc(100vh - 120px);
            padding: 30px 0 58px;
        }

        .hero-panel {
            border-radius: 38px;
            padding: clamp(30px,5vw,62px);
        }

        .eyebrow {
            display: inline-flex;
            padding: 9px 14px;
            margin-bottom: 22px;
            border-radius: 999px;
            background: rgba(255,255,255,.6);
            color: var(--brown);
            font-size: 12px;
            font-weight: 900;
        }

        .hero h1 {
            font-size: clamp(42px,7vw,78px);
            line-height: .95;
            letter-spacing: -2.7px;
            margin-bottom: 24px;
        }

        .hero p {
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
            color: white;
            background: linear-gradient(135deg,#8a7158,#5d3f2c);
        }

        .btn-light {
            color: var(--brown-dark);
            background: rgba(255,255,255,.68);
            border: 1px solid rgba(255,255,255,.82);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Hero Slideshow
        |--------------------------------------------------------------------------
        | Cloud-hosted hero images remain unchanged.
        |--------------------------------------------------------------------------
        */

        .slideshow {
            min-height: 560px;
            border-radius: 42px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255,255,255,.7);
            box-shadow: 0 38px 86px rgba(47,33,24,.18);
            background:
                linear-gradient(135deg,rgba(111,78,55,.18),rgba(255,253,249,.5)),
                linear-gradient(135deg,#d9c2ac,#fff7ee);
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
            transition: opacity .9s ease, transform 1.8s ease;
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
            color: rgba(47,33,24,.42);
            font-size: clamp(34px,5vw,64px);
            font-weight: 950;
            pointer-events: none;
        }

        .floating-card {
            position: absolute;
            left: 22px;
            right: 22px;
            bottom: 22px;
            padding: 22px;
            border-radius: 28px;
            background: rgba(255,253,249,.62);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.75);
        }

        .floating-card h2 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .floating-card p {
            font-size: 13px;
            margin: 0;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Statistics
        |--------------------------------------------------------------------------
        */

        .stats {
            width: min(1180px,92%);
            margin: 0 auto 22px;
            display: grid;
            grid-template-columns: repeat(4,1fr);
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
            color: var(--brown);
        }

        .stat-card p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: General Sections
        |--------------------------------------------------------------------------
        */

        .section {
            width: min(1180px,92%);
            margin: 0 auto;
            padding: 72px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 42px;
        }

        .section-title h2 {
            font-size: clamp(30px,4vw,42px);
            margin-bottom: 12px;
        }

        .section-title p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Text-Only Service Cards
        |--------------------------------------------------------------------------
        | Service image elements have been removed completely.
        |--------------------------------------------------------------------------
        */

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3,1fr);
            gap: 26px;
        }

        .service-card {
            width: 100%;
            min-height: 230px;
            display: flex;
            flex-direction: column;
            text-align: left;
            border: 1px solid rgba(255,255,255,.72);
            background: rgba(255,255,255,.48);
            border-radius: 30px;
            padding: 26px;
            color: var(--brown-dark);
            box-shadow: 0 22px 48px rgba(47,33,24,.1);
            transition: .24s ease;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 34px 72px rgba(47,33,24,.18);
        }

        .service-card:focus-visible {
            outline: 3px solid var(--brown);
            outline-offset: 3px;
        }

        .service-card h3 {
            font-size: 20px;
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .service-description {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 20px;
            white-space: pre-line;
            overflow-wrap: anywhere;
            flex: 1;
        }

        .service-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;
            margin-bottom: 18px;
        }

        .service-meta span {
            border-radius: 999px;
            padding: 9px 12px;
            background: rgba(255,255,255,.7);
            border: 1px solid rgba(255,255,255,.75);
            color: var(--brown);
            font-size: 12px;
            font-weight: 800;
        }

        .click-note {
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid rgba(111,78,55,.12);
            color: #9a8778;
            font-size: 12px;
            font-weight: 700;
        }

        .empty-state {
            max-width: 720px;
            margin: auto;
            padding: 26px;
            border-radius: 28px;
            text-align: center;
            background: rgba(255,253,249,.58);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: About Section
        |--------------------------------------------------------------------------
        */

        .why-grid {
            display: grid;
            grid-template-columns: repeat(4,1fr);
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
            background: rgba(255,255,255,.58);
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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Studio Information
        |--------------------------------------------------------------------------
        */

        .visit {
            display: grid;
            grid-template-columns: .95fr 1.05fr;
            gap: 34px;
            align-items: stretch;
        }

        .info-panel {
            padding: clamp(26px,4vw,42px);
            border-radius: 34px;
        }

        .info-panel h2 {
            font-size: clamp(30px,4vw,40px);
            margin-bottom: 26px;
        }

        .info-box {
            padding: 18px 0;
            border-bottom: 1px solid rgba(111,78,55,.12);
        }

        .info-box h3 {
            font-size: 15px;
            margin-bottom: 8px;
        }

        .info-box p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.75;
            white-space: pre-line;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Cloud-Hosted Studio Image
        |--------------------------------------------------------------------------
        | This image remains because it represents the salon itself,
        | not a service.
        |--------------------------------------------------------------------------
        */

        .studio-image {
            min-height: 430px;
            border-radius: 34px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255,255,255,.72);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg,#d9c2ac,#fff7ee);
        }

        .studio-image img {
            width: 100%;
            height: 100%;
            min-height: 430px;
            object-fit: cover;
        }

        .contact-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .contact-links a {
            padding: 9px 13px;
            border-radius: 999px;
            background: rgba(255,255,255,.62);
            font-size: 13px;
            font-weight: 700;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Call to Action
        |--------------------------------------------------------------------------
        */

        .cta {
            width: min(1180px,92%);
            margin: 0 auto 40px;
            padding: clamp(28px,5vw,48px);
            border-radius: 34px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 22px;
            align-items: center;
        }

        .cta h2 {
            font-size: clamp(28px,4vw,40px);
            margin-bottom: 10px;
        }

        .cta p {
            color: var(--muted);
            line-height: 1.7;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Footer
        |--------------------------------------------------------------------------
        */

        footer {
            width: min(1180px,92%);
            margin: 0 auto 24px;
            background: rgba(47,33,24,.9);
            color: white;
            text-align: center;
            padding: 40px 7%;
            border-radius: 34px;
        }

        footer h3 {
            margin-bottom: 13px;
        }

        footer p {
            color: #d8cfc4;
            font-size: 13px;
            line-height: 1.7;
        }

        .copyright {
            margin-top: 22px;
            color: #b8aa9a;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Text-Only Service Details Popup
        |--------------------------------------------------------------------------
        | No image container or image JavaScript is used.
        |--------------------------------------------------------------------------
        */

        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(47,33,24,.34);
            backdrop-filter: blur(18px);
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-box {
            width: min(540px,100%);
            max-height: 92vh;
            overflow: auto;
            border-radius: 32px;
            background: rgba(255,253,249,.94);
            box-shadow: 0 35px 90px rgba(47,33,24,.26);
        }

        .modal-content {
            padding: 32px;
        }

        .modal-content h3 {
            font-size: 25px;
            margin-bottom: 14px;
        }

        .modal-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .modal-meta span {
            background: rgba(255,255,255,.8);
            border-radius: 999px;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: 800;
            color: var(--brown);
        }

        .modal-content p {
            color: var(--muted);
            line-height: 1.75;
            margin-bottom: 22px;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .close-btn {
            width: 100%;
            border: none;
            padding: 14px;
            border-radius: 999px;
            background: linear-gradient(135deg,#8a7158,#5d3f2c);
            color: white;
            font-weight: 900;
            cursor: pointer;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Responsive Design
        |--------------------------------------------------------------------------
        */

        @media (max-width: 980px) {
            .hero,
            .visit,
            .cta {
                grid-template-columns: 1fr;
            }

            .stats,
            .why-grid {
                grid-template-columns: repeat(2,1fr);
            }

            .services-grid {
                grid-template-columns: repeat(2,1fr);
            }

            .slideshow,
            .slide {
                min-height: 440px;
            }
        }

        @media (max-width: 720px) {
            .top-nav {
                border-radius: 28px;
                padding: 12px;
            }

            .menu-btn {
                display: grid;
                place-items: center;
            }

            .nav-links {
                display: none;
                position: absolute;
                left: 0;
                right: 0;
                top: calc(100% + 10px);
                padding: 14px;
                border-radius: 28px;
                background: rgba(255,253,249,.96);
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
                box-shadow: var(--shadow);
            }

            .nav-links.show {
                display: flex;
            }

            .nav-links a {
                padding: 12px 14px;
            }

            .hero {
                min-height: auto;
                margin-top: 22px;
            }

            .hero h1 {
                letter-spacing: -1.5px;
            }

            .hero-actions .btn {
                width: 100%;
            }

            .slideshow,
            .slide {
                min-height: 340px;
            }

            .stats,
            .services-grid,
            .why-grid {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 54px 0;
            }

            .studio-image,
            .studio-image img {
                min-height: 320px;
            }

            .cta .btn {
                width: 100%;
            }

            .service-card {
                min-height: 0;
            }

            .modal-content {
                padding: 24px;
            }
        }
    </style>
</head>

<body>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Main Navigation
    |--------------------------------------------------------------------------
    --}}

    <nav class="top-nav glass" aria-label="Main navigation">

        <a href="{{ url('/') }}" class="brand">
            <span class="brand-mark">NS</span>
            <span class="brand-name">{{ $businessName }}</span>
        </a>

        <button
            class="menu-btn"
            type="button"
            onclick="toggleMenu()"
            id="menuButton"
            aria-label="Toggle navigation"
            aria-expanded="false"
        >
            ☰
        </button>

        <div class="nav-links" id="navLinks">

            <a href="{{ url('/') }}">Home</a>
            <a href="#services">Services</a>
            <a href="#about">About</a>
            <a href="#visit">Visit</a>

            @auth

                <a href="{{ $dashboardUrl }}" class="btn-main">
                    Dashboard
                </a>

            @else

                <a href="{{ $loginUrl }}">Login</a>

                <a href="{{ $registerUrl }}" class="btn-main">
                    Register
                </a>

            @endauth

        </div>
    </nav>

    <main>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Hero Section
        |--------------------------------------------------------------------------
        --}}

        <section class="hero" id="home">

            <div class="hero-panel glass">

                <span class="eyebrow">
                    {{ $heroEyebrow }}
                </span>

                <h1>{{ $heroTitle }}</h1>

                <p>{{ $heroDescription }}</p>

                <div class="hero-actions">

                    @auth

                        <a
                            href="{{ $dashboardUrl }}"
                            class="btn btn-brown"
                        >
                            Go to dashboard
                        </a>

                    @else

                        <a
                            href="{{ $registerUrl }}"
                            class="btn btn-brown"
                        >
                            Create account to book
                        </a>

                        <a
                            href="{{ $loginUrl }}"
                            class="btn btn-light"
                        >
                            Login
                        </a>

                    @endauth

                </div>

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Homepage Hero Slideshow
            |--------------------------------------------------------------------------
            | Remains connected to the Laravel Cloud bucket.
            |--------------------------------------------------------------------------
            --}}

            <div
                class="slideshow {{ $heroSlides->isEmpty() ? 'image-placeholder' : '' }}"
                id="heroSlideshow"
                aria-label="Nail Studio & Beauty photos"
            >

                @foreach ($heroSlides as $index => $slide)

                    @php
                        $slideUrl = data_get($slide, 'image_url');

                        if (!$slideUrl) {
                            $slideUrl = $contentImageUrl(
                                data_get($slide, 'image_path')
                                    ?? data_get($slide, 'image')
                            );
                        }

                        $slideAlt = data_get(
                            $slide,
                            'alt_text',
                            $businessName . ' studio photo'
                        );
                    @endphp

                    @if ($slideUrl)

                        <img
                            src="{{ $slideUrl }}"
                            alt="{{ $slideAlt }}"
                            class="slide {{ $index === 0 ? 'active' : '' }}"
                            onerror="handleImageError(this)"
                        >

                    @endif

                @endforeach

                <div class="floating-card">

                    <h2>
                        Clean, calm, and organized beauty care
                    </h2>

                    <p>
                        Beauty appointments, convenient scheduling,
                        and professional customer service.
                    </p>

                </div>

            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Business Statistics
        |--------------------------------------------------------------------------
        --}}

        <section class="stats">

            @foreach ($statCards as $stat)

                <div class="stat-card glass">

                    <h2>{{ data_get($stat, 'value') }}</h2>

                    <p>{{ data_get($stat, 'label') }}</p>

                </div>

            @endforeach

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Text-Only Service Catalog
        |--------------------------------------------------------------------------
        | Displays Available services from the database.
        |
        | Admin updates are reflected when the page reloads.
        | Service images are completely removed.
        |--------------------------------------------------------------------------
        --}}

        <section class="section" id="services">

            <div class="section-title">

                <h2>{{ $serviceTitle }}</h2>

                <p>{{ $serviceDescription }}</p>

            </div>

            @if ($landingServices->isNotEmpty())

                <div class="services-grid">

                    @foreach ($landingServices as $service)

                        @php
                            $serviceName = data_get(
                                $service,
                                'service_name',
                                data_get(
                                    $service,
                                    'name',
                                    'Beauty Service'
                                )
                            );

                            $description = data_get(
                                $service,
                                'description',
                                data_get(
                                    $service,
                                    'service_description',
                                    'Service details will be available during booking.'
                                )
                            );

                            $description = blank($description)
                                ? 'No service description available.'
                                : $description;

                            $price = $formatPrice($service);

                            $duration = $formatDuration($service);

                            $category = data_get(
                                $service,
                                'category.name',
                                data_get(
                                    $service,
                                    'category_name',
                                    'Beauty Service'
                                )
                            );

                            if (!is_string($category)) {
                                $category = 'Beauty Service';
                            }
                        @endphp

                        <button
                            type="button"
                            class="service-card"
                            onclick="openServiceModal(this)"
                            data-title="{{ $serviceName }}"
                            data-description="{{ $description }}"
                            data-price="{{ $price }}"
                            data-duration="{{ $duration }}"
                            data-category="{{ $category }}"
                        >

                            <h3>{{ $serviceName }}</h3>

                            <p class="service-description">
                                {{ $description }}
                            </p>

                            <div class="service-meta">

                                <span>
                                    {{ $price }}
                                </span>

                                <span>
                                    ⏱ {{ $duration }}
                                </span>

                            </div>

                            <div class="click-note">
                                Click to view full details
                            </div>

                        </button>

                    @endforeach

                </div>

            @else

                <div class="empty-state">

                    <strong>
                        No services are available yet.
                    </strong>

                    <p>
                        Services will appear here once they are
                        published by the administrator.
                    </p>

                </div>

            @endif

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: About Section
        |--------------------------------------------------------------------------
        --}}

        <section class="section" id="about">

            <div class="section-title">

                <h2>{{ $aboutTitle }}</h2>

                <p>{{ $aboutDescription }}</p>

            </div>

            <div class="why-grid">

                @foreach ($whyCards as $card)

                    <div class="why-card glass">

                        <span>
                            {{ data_get($card, 'icon', '✨') }}
                        </span>

                        <h3>{{ data_get($card, 'title') }}</h3>

                        <p>{{ data_get($card, 'description') }}</p>

                    </div>

                @endforeach

            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Studio Information
        |--------------------------------------------------------------------------
        --}}

        <section class="section visit" id="visit">

            <div class="info-panel glass">

                <h2>Visit Our Studio</h2>

                <div class="info-box">

                    <h3>Location</h3>

                    <p>{{ $businessLocation }}</p>

                </div>

                <div class="info-box">

                    <h3>Operating Hours</h3>

                    <p>{{ $businessHours }}</p>

                </div>

                <div class="info-box">

                    <h3>Contact</h3>

                    @if (
                        $businessPhone ||
                        $businessEmail ||
                        $facebookUrl ||
                        $instagramUrl
                    )

                        <p>
                            Contact the studio through the
                            available channels below.
                        </p>

                        <div class="contact-links">

                            @if ($businessPhone)

                                <a
                                    href="tel:{{ preg_replace('/\s+/', '', $businessPhone) }}"
                                >
                                    {{ $businessPhone }}
                                </a>

                            @endif

                            @if ($businessEmail)

                                <a href="mailto:{{ $businessEmail }}">
                                    {{ $businessEmail }}
                                </a>

                            @endif

                            @if ($facebookUrl)

                                <a
                                    href="{{ $facebookUrl }}"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Facebook
                                </a>

                            @endif

                            @if ($instagramUrl)

                                <a
                                    href="{{ $instagramUrl }}"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Instagram
                                </a>

                            @endif

                        </div>

                    @else

                        <p>
                            Contact details will be available soon.
                        </p>

                    @endif

                </div>

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Studio Image
            |--------------------------------------------------------------------------
            | Preserves the image uploaded through Admin Content Management.
            |--------------------------------------------------------------------------
            --}}

            <div
                class="studio-image"
                id="studioImageWrapper"
            >

                @if ($studioImage)

                    <img
                        src="{{ $studioImage }}"
                        alt="{{ $businessName }} studio"
                        loading="lazy"
                        onerror="handleImageError(this)"
                    >

                @endif

            </div>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Booking Call to Action
        |--------------------------------------------------------------------------
        --}}

        <section class="cta glass">

            <div>

                <h2>
                    Ready to book your beauty appointment?
                </h2>

                <p>
                    Create an account or log in to
                    manage your appointment.
                </p>

            </div>

            @auth

                <a
                    href="{{ $dashboardUrl }}"
                    class="btn btn-brown"
                >
                    Go to dashboard
                </a>

            @else

                <a
                    href="{{ $registerUrl }}"
                    class="btn btn-brown"
                >
                    Create account
                </a>

            @endauth

        </section>

    </main>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Footer
    |--------------------------------------------------------------------------
    --}}

    <footer>

        <h3>{{ $businessName }}</h3>

        <p>
            {{ $businessTagline }}
            <br>
            {{ $businessArea }}
        </p>

        <p class="copyright">
            © {{ date('Y') }} {{ $businessName }}.
            All rights reserved.
        </p>

    </footer>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Text-Only Service Details Popup
    |--------------------------------------------------------------------------
    | No image container, image source, or service image handling.
    |--------------------------------------------------------------------------
    --}}

    <div
        class="modal-overlay"
        id="serviceModal"
        onclick="closeServiceModal(event)"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalTitle"
    >

        <div class="modal-box">

            <div class="modal-content">

                <h3 id="modalTitle"></h3>

                <div class="modal-meta">

                    <span id="modalPrice"></span>

                    <span id="modalDuration"></span>

                    <span id="modalCategory"></span>

                </div>

                <p id="modalDescription"></p>

                <button
                    class="close-btn"
                    type="button"
                    onclick="closeModalButton()"
                >
                    Back to services
                </button>

            </div>

        </div>

    </div>

    <script>
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Hero Slideshow
        |--------------------------------------------------------------------------
        | Preserves automatic image rotation.
        |--------------------------------------------------------------------------
        */

        let currentSlide = 0;

        const slides = document.querySelectorAll(
            "#heroSlideshow .slide"
        );

        if (slides.length > 1) {
            setInterval(() => {
                slides[currentSlide].classList.remove("active");

                currentSlide = (currentSlide + 1) % slides.length;

                slides[currentSlide].classList.add("active");
            }, 5000);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Mobile Navigation
        |--------------------------------------------------------------------------
        */

        function toggleMenu() {
            const links = document.getElementById("navLinks");
            const button = document.getElementById("menuButton");

            const open = links.classList.toggle("show");

            button.setAttribute(
                "aria-expanded",
                open ? "true" : "false"
            );

            button.textContent = open ? "×" : "☰";
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Text-Only Service Popup
        |--------------------------------------------------------------------------
        | Displays service information stored in data attributes.
        | No service image is loaded or displayed.
        |--------------------------------------------------------------------------
        */

        function openServiceModal(button) {
            const modal = document.getElementById("serviceModal");

            document.getElementById("modalTitle").textContent =
                button.dataset.title || "Beauty Service";

            document.getElementById("modalDescription").textContent =
                button.dataset.description || "Details unavailable.";

            document.getElementById("modalPrice").textContent =
                button.dataset.price || "";

            document.getElementById("modalDuration").textContent =
                button.dataset.duration || "";

            document.getElementById("modalCategory").textContent =
                button.dataset.category || "";

            modal.classList.add("show");

            document.body.classList.add("modal-open");
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Close Service Popup
        |--------------------------------------------------------------------------
        */

        function closeServiceModal(event) {
            if (event.target.id === "serviceModal") {
                closeModalButton();
            }
        }

        function closeModalButton() {
            document.getElementById("serviceModal")
                .classList.remove("show");

            document.body.classList.remove("modal-open");
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Hero and Studio Image Error Handling
        |--------------------------------------------------------------------------
        | Service images have been removed.
        | This function is only for the hero slideshow and studio.
        |--------------------------------------------------------------------------
        */

        function handleImageError(image) {
            const wrapper = image.closest(
                ".slideshow, .studio-image"
            );

            image.style.display = "none";

            if (wrapper) {
                wrapper.classList.add("image-placeholder");
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Escape Key
        |--------------------------------------------------------------------------
        */

        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape") {
                closeModalButton();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Navigation Links
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll("#navLinks a").forEach((link) => {
            link.addEventListener("click", () => {
                const links = document.getElementById("navLinks");
                const button = document.getElementById("menuButton");

                links.classList.remove("show");

                button.setAttribute("aria-expanded", "false");
                button.textContent = "☰";
            });
        });
    </script>

</body>
</html>
