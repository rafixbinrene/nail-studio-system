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
    | NS BEAUTY COMMENT:
    | This updated top section connects Admin Content Management
    | to the public welcome page.
    |
    | Admin Content Management saves images/text in ContentSetting.
    | This section maps those saved values into the setting keys used
    | by the welcome page.
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Load Admin Content Management Data
    |--------------------------------------------------------------------------
    | Purpose:
    | - Reads the saved Content Management record from the database.
    | - Connects Admin uploaded images/text to the public welcome page.
    | - Fixes the issue where uploaded Laravel Cloud images do not reflect
    |   on the landing page.
    |--------------------------------------------------------------------------
    */
    $adminContent = null;

    if (class_exists(\App\Models\ContentSetting::class)) {
        $adminContent = \App\Models\ContentSetting::query()->first();
    }

    $adminContentSettings = [];

    if ($adminContent) {
        $adminContentSettings = [
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Business identity mapping
            |--------------------------------------------------------------------------
            */
            'business_name' => $adminContent->salon_name,
            'business_tagline' => $adminContent->tagline,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Hero section mapping
            |--------------------------------------------------------------------------
            | Admin Content Management uses:
            | - hero_title
            | - hero_subtitle
            |
            | Welcome page uses:
            | - landing_hero_title
            | - landing_hero_description
            |--------------------------------------------------------------------------
            */
            'landing_hero_title' => $adminContent->hero_title,
            'landing_hero_description' => $adminContent->hero_subtitle,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | About section mapping
            |--------------------------------------------------------------------------
            */
            'landing_about_title' => $adminContent->about_title,
            'landing_about_description' => $adminContent->about_description,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Contact section mapping
            |--------------------------------------------------------------------------
            */
            'business_address' => $adminContent->address,
            'business_phone' => $adminContent->phone_number,
            'business_email' => $adminContent->email,
            'facebook_url' => $adminContent->facebook_url,
            'instagram_url' => $adminContent->instagram_url,

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Uploaded homepage images
            |--------------------------------------------------------------------------
            | These paths are stored like:
            | content/example-image.jpg
            |
            | The existing $fileUrl helper below will convert them to:
            | /storage/content/example-image.jpg
            |--------------------------------------------------------------------------
            */
            'hero_slide_1_image' => $adminContent->hero_slide_1_image,
            'hero_slide_2_image' => $adminContent->hero_slide_2_image,
            'hero_slide_3_image' => $adminContent->hero_slide_3_image,
            'hero_slide_4_image' => $adminContent->hero_slide_4_image,
            'hero_slide_5_image' => $adminContent->hero_slide_5_image,
            'studio_image_path' => $adminContent->studio_image_path,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Final Settings Source
    |--------------------------------------------------------------------------
    | Purpose:
    | - Keeps any existing controller-provided settings.
    | - Lets Admin Content Management override the welcome page content.
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

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | File URL Helper
    |--------------------------------------------------------------------------
    | Purpose:
    | - Converts database image paths into public URLs.
    | - Example:
    |   content/sample.jpg becomes /storage/content/sample.jpg
    |--------------------------------------------------------------------------
    */
    $fileUrl = function ($path) {
        if ($path === null) {
            return null;
        }

        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) {
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

    $heroSlideSource = $heroSlides ?? [];
    $heroSlides = collect($heroSlideSource)
        ->filter(fn ($slide) => (bool) data_get($slide, 'is_active', true))
        ->sortBy(fn ($slide) => data_get($slide, 'sort_order', data_get($slide, 'order', 999)))
        ->values();

    if ($heroSlides->isEmpty()) {
        $settingSlides = collect();

        for ($i = 1; $i <= 10; $i++) {
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

        $heroSlides = $settingSlides;
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