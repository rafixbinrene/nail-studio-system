
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | NS &amp; Beauty</title>

    {{-- NS BEAUTY COMMENT: Official business logo --}}
    <link rel="icon" type="image/png"
          href="{{ asset('images/nsbeauty-logo.png') }}">
    <link rel="apple-touch-icon"
          href="{{ asset('images/nsbeauty-logo.png') }}">

    <style>
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: REGISTRATION PAGE DESIGN
        |--------------------------------------------------------------------------
        | Preserves the original brown, cream and glass design.
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
            --muted: #7d6d60;
            --placeholder: #aa9e93;
            --glass: rgba(255,255,255,.54);
            --border: rgba(255,255,255,.70);
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
            color: var(--brown-dark);
            background:
                radial-gradient(
                    circle at 15% 18%,
                    rgba(255,255,255,.78),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 88% 12%,
                    rgba(234,216,196,.88),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #f1e6d8 0%,
                    #fffdf9 45%,
                    #e7d2bd 100%
                );
        }

        .card {
            width: 100%;
            max-width: 520px;
            padding: 40px;
            background: var(--glass);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow:
                0 20px 50px rgba(47,33,24,.12),
                inset 0 1px 0 rgba(255,255,255,.7);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: BRANDING
        |--------------------------------------------------------------------------
        */

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 11px;
            margin-bottom: 20px;
            font-size: 15px;
            font-weight: 900;
            color: var(--brown-dark);
        }

        .logo-mark {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 50%;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8f6f2;
            border: 1px solid rgba(111,78,55,.14);
        }

        .logo-mark img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            border-radius: 50%;
        }

        h1 {
            text-align: center;
            margin-bottom: 12px;
            font-size: clamp(28px, 4vw, 34px);
            letter-spacing: -.7px;
            line-height: 1.2;
            background: linear-gradient(
                135deg,
                #2f2118,
                #6f4e37,
                #a38468
            );
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            margin-bottom: 28px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: FORM FIELDS
        |--------------------------------------------------------------------------
        */

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 9px;
            font-size: 13px;
            font-weight: 800;
            color: var(--brown-dark);
        }

        .form-input {
            width: 100%;
            min-height: 50px;
            padding: 14px 16px;
            border: 1px solid rgba(111,78,55,.16);
            border-radius: 14px;
            background: rgba(255,255,255,.68);
            color: var(--brown-dark);
            font-size: 14px;
            font-weight: 500;
            outline: none;
            transition:
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }

        .form-input::placeholder {
            color: var(--placeholder);
            opacity: .75;
        }

        .form-input:focus {
            border-color: var(--brown);
            background: rgba(255,255,255,.88);
            box-shadow: 0 0 0 3px rgba(111,78,55,.10);
        }

        .form-input.is-invalid {
            border-color: rgba(150,45,35,.55);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: PASSWORD VISIBILITY
        |--------------------------------------------------------------------------
        */

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper .form-input {
            padding-right: 60px;
        }

        .password-toggle {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 12px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
        }

        .password-toggle:hover {
            color: var(--brown);
            background: rgba(111,78,55,.08);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            pointer-events: none;
        }

        .password-toggle svg[hidden] {
            display: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: MESSAGES
        |--------------------------------------------------------------------------
        */

        .alert {
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.6;
        }

        .alert-error {
            color: #7a241d;
            background: rgba(150,45,35,.10);
            border: 1px solid rgba(150,45,35,.18);
        }

        .alert-success {
            color: #285c3f;
            background: rgba(50,130,85,.10);
            border: 1px solid rgba(50,130,85,.18);
        }

        .error-text {
            margin-top: 7px;
            color: #8a2f24;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.5;
        }

        .note {
            margin-bottom: 22px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.70);
            background: rgba(255,255,255,.44);
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .note strong {
            color: var(--brown);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: LEGAL DOCUMENTS
        |--------------------------------------------------------------------------
        | English and Bahasa Malaysia policy switching.
        |--------------------------------------------------------------------------
        */

        .agreement-section {
            margin: 6px 0 20px;
            padding: 18px;
            border-radius: 14px;
            border: 1px solid rgba(111,78,55,.14);
            background: rgba(255,255,255,.46);
        }

        .agreement-heading {
            margin-bottom: 8px;
            color: var(--brown-dark);
            font-size: 15px;
            font-weight: 850;
        }

        .agreement-description {
            margin-bottom: 14px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.7;
        }

        .policy-details {
            margin-bottom: 11px;
            border-radius: 14px;
            border: 1px solid rgba(111,78,55,.14);
            background: rgba(255,255,255,.58);
            overflow: hidden;
        }

        .policy-details summary {
            padding: 14px;
            color: var(--brown);
            font-size: 13px;
            font-weight: 850;
            cursor: pointer;
        }

        .policy-details summary:hover {
            background: rgba(111,78,55,.06);
        }

        .policy-container {
            padding: 4px 14px 16px;
        }

        .policy-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(111,78,55,.12);
        }

        .policy-toolbar-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .policy-language-buttons {
            display: inline-flex;
            gap: 5px;
            padding: 4px;
            border-radius: 12px;
            background: rgba(111,78,55,.07);
        }

        .policy-language-btn {
            padding: 8px 11px;
            border: 1px solid transparent;
            border-radius: 9px;
            background: transparent;
            color: var(--brown);
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .policy-language-btn.active {
            background: #ffffff;
            border-color: rgba(111,78,55,.14);
            box-shadow: 0 3px 8px rgba(47,33,24,.06);
        }

        .policy-language-btn:focus-visible,
        .password-toggle:focus-visible {
            outline: 2px solid var(--brown);
            outline-offset: 2px;
        }

        .policy-content {
            max-height: 350px;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 3px 5px 3px 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.85;
        }

        .policy-content[hidden] {
            display: none !important;
        }

        .policy-content h3 {
            margin: 14px 0 6px;
            color: var(--brown-dark);
            font-size: 13px;
            line-height: 1.5;
        }

        .policy-content p {
            margin-bottom: 13px;
        }

        .policy-content strong {
            color: var(--brown-dark);
        }

        .policy-contact {
            color: var(--brown);
            overflow-wrap: anywhere;
        }

        .policy-pending {
            margin: 12px 0;
            padding: 12px;
            border-radius: 10px;
            background: rgba(111,78,55,.07);
            border: 1px solid rgba(111,78,55,.12);
            color: var(--brown-dark);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: AGREEMENT CHECKBOX
        |--------------------------------------------------------------------------
        */

        .agreement-label {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin-top: 16px;
            color: var(--brown-dark);
            font-size: 12px;
            line-height: 1.7;
            cursor: pointer;
        }

        .agreement-checkbox {
            flex-shrink: 0;
            width: 17px;
            height: 17px;
            margin-top: 2px;
            accent-color: var(--brown);
            cursor: pointer;
        }

        .agreement-label strong {
            color: var(--brown);
        }

        .agreement-footnote {
            margin-top: 10px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: SUBMISSION AND NAVIGATION
        |--------------------------------------------------------------------------
        */

        .submit-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            min-height: 50px;
            padding: 14px 20px;
            margin-top: 4px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg,#8a7158,#5d3f2c);
            color: white;
            font-size: 14px;
            font-weight: 850;
            cursor: pointer;
            transition: transform .2s ease, opacity .2s ease;
        }

        .submit-btn:not(:disabled):hover {
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            opacity: .48;
            cursor: not-allowed;
        }

        .footer {
            margin-top: 26px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .footer a {
            color: var(--brown);
            font-weight: 850;
            text-decoration: none;
        }

        .back {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: var(--muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .footer a:hover,
        .back:hover {
            text-decoration: underline;
        }

        @media (max-width: 520px) {
            body {
                padding: 16px;
            }

            .card {
                padding: 30px 24px;
                border-radius: 20px;
            }

            .agreement-section {
                padding: 15px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body>

<main class="card">

    {{-- NS BEAUTY COMMENT: OFFICIAL ROUND LOGO --}}

    <div class="logo">
        <span class="logo-mark">
            <img
                src="{{ asset('images/nsbeauty-logo.png') }}"
                alt="NS & Beauty Logo"
                width="42"
                height="42"
            >
        </span>
        <span>NS &amp; Beauty</span>
    </div>

    <h1>Create Account</h1>

    <p class="subtitle">
        Join our beauty community today
    </p>

    {{-- NS BEAUTY COMMENT: SESSION MESSAGES --}}

    @if (session('status'))
        <div class="alert alert-success" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            Please check your details and try again.
        </div>
    @endif

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: CUSTOMER REGISTRATION FORM
    |--------------------------------------------------------------------------
    | Existing controller route, field names and OTP workflow preserved.
    |--------------------------------------------------------------------------
    --}}

    <form
        id="registrationForm"
        method="POST"
        action="{{ route('register.store') }}"
    >
        @csrf

        {{-- Full Name --}}

        <div class="form-group">
            <label class="form-label" for="name">
                Full Name
            </label>

            <input
                class="form-input @error('name') is-invalid @enderror"
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Enter your full name"
                required
                autocomplete="name"
                @error('name') aria-invalid="true" @enderror
            >

            @error('name')
                <div class="error-text" role="alert">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Phone Number --}}

        <div class="form-group">
            <label class="form-label" for="phone_number">
                Phone Number
            </label>

            <input
                class="form-input @error('phone_number') is-invalid @enderror"
                type="tel"
                id="phone_number"
                name="phone_number"
                value="{{ old('phone_number') }}"
                placeholder="e.g. 09123456789 or +60123456789"
                required
                autocomplete="tel"
                @error('phone_number') aria-invalid="true" @enderror
            >

            @error('phone_number')
                <div class="error-text" role="alert">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Optional Address --}}

        <div class="form-group">
            <label class="form-label" for="address">
                Address
            </label>

            <input
                class="form-input @error('address') is-invalid @enderror"
                type="text"
                id="address"
                name="address"
                value="{{ old('address') }}"
                placeholder="Enter your address"
                autocomplete="street-address"
                @error('address') aria-invalid="true" @enderror
            >

            @error('address')
                <div class="error-text" role="alert">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Email Address --}}

        <div class="form-group">
            <label class="form-label" for="email">
                Email Address
            </label>

            <input
                class="form-input @error('email') is-invalid @enderror"
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your email address"
                required
                autocomplete="email"
                @error('email') aria-invalid="true" @enderror
            >

            @error('email')
                <div class="error-text" role="alert">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- NS BEAUTY COMMENT: Mandatory registration email OTP --}}

        <div class="note">
            <strong>Email verification</strong>
            — A one-time verification code will be sent to
            your email address during registration.
        </div>

        {{-- Password --}}

        <div class="form-group">
            <label class="form-label" for="password">
                Password
            </label>

            <div class="password-wrapper">

                <input
                    class="form-input @error('password') is-invalid @enderror"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create a password"
                    required
                    autocomplete="new-password"
                    @error('password') aria-invalid="true" @enderror
                >

                <button
                    type="button"
                    class="password-toggle"
                    data-password-target="password"
                    aria-label="Show password"
                    aria-pressed="false"
                    aria-controls="password"
                >
                    <svg class="eye-icon"
                         viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>

                    <svg class="eye-off-icon"
                         viewBox="0 0 24 24"
                         aria-hidden="true"
                         hidden>
                        <path d="M10.6 5.2A10.5 10.5 0 0 1 12 5c6.5 0 10 7 10 7a15.5 15.5 0 0 1-3.2 4.1"/>
                        <path d="M6.1 6.1A15 15 0 0 0 2 12s3.5 7 10 7a10 10 0 0 0 5.3-1.5"/>
                        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                        <path d="M3 3l18 18"/>
                    </svg>
                </button>
            </div>

            @error('password')
                <div class="error-text" role="alert">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Confirm Password --}}

        <div class="form-group">
            <label class="form-label" for="password_confirmation">
                Confirm Password
            </label>

            <div class="password-wrapper">

                <input
                    class="form-input"
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Re-enter your password"
                    required
                    autocomplete="new-password"
                >

                <button
                    type="button"
                    class="password-toggle"
                    data-password-target="password_confirmation"
                    aria-label="Show confirm password"
                    aria-pressed="false"
                    aria-controls="password_confirmation"
                >
                    <svg class="eye-icon"
                         viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>

                    <svg class="eye-off-icon"
                         viewBox="0 0 24 24"
                         aria-hidden="true"
                         hidden>
                        <path d="M10.6 5.2A10.5 10.5 0 0 1 12 5c6.5 0 10 7 10 7a15.5 15.5 0 0 1-3.2 4.1"/>
                        <path d="M6.1 6.1A15 15 0 0 0 2 12s3.5 7 10 7a10 10 0 0 0 5.3-1.5"/>
                        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                        <path d="M3 3l18 18"/>
                    </svg>
                </button>

            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: TERMS AND PRIVACY DOCUMENTS
        |--------------------------------------------------------------------------
        | Owner-confirmed details:
        | - Contact: nailstudio.beautyofficial@gmail.com
        | - Promotional emails: Not currently sent
        |
        | Still pending:
        | - Approved personal-data retention and deletion schedule
        | - Exact overseas cloud/database locations
        | - External provider and cross-border safeguards verification
        | - Final owner/legal approval and effective date
        |
        | The pending items are intentionally NOT invented.
        |--------------------------------------------------------------------------
        --}}

        <section
            class="agreement-section"
            aria-labelledby="agreementHeading"
        >

            <h2
                class="agreement-heading"
                id="agreementHeading"
            >
                Terms, Conditions &amp; Privacy
            </h2>

            <p class="agreement-description">
                Review both documents in English or Bahasa Malaysia
                before creating your account.
            </p>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: TERMS AND CONDITIONS
            |--------------------------------------------------------------------------
            --}}

            <details
                class="policy-details"
                id="termsDetails"
            >
                <summary>
                    Terms and Conditions / Terma dan Syarat
                </summary>

                <div class="policy-container">

                    <div class="policy-toolbar">
                        <span class="policy-toolbar-label">
                            Document language / Bahasa dokumen
                        </span>

                        <div
                            class="policy-language-buttons"
                            role="group"
                            aria-label="Terms language"
                        >
                            <button
                                type="button"
                                class="policy-language-btn active"
                                data-policy="terms"
                                data-language="en"
                                aria-pressed="true"
                            >
                                English
                            </button>

                            <button
                                type="button"
                                class="policy-language-btn"
                                data-policy="terms"
                                data-language="ms"
                                aria-pressed="false"
                            >
                                Bahasa Malaysia
                            </button>
                        </div>
                    </div>

                    {{--
                    | NS BEAUTY COMMENT: ENGLISH TERMS AND CONDITIONS
                    --}}

                    <div
                        class="policy-content"
                        id="terms-en"
                        lang="en"
                    >

                        <h3>1. Introduction and Acceptance</h3>
                        <p>
                            These Terms and Conditions govern access
                            to and use of the NS &amp; Beauty online
                            appointment booking system. By creating
                            an account, customers agree to comply
                            with these Terms and the booking policies
                            displayed when using the system.
                        </p>

                        <h3>2. Account Registration</h3>
                        <p>
                            Customers must provide accurate, complete
                            and current registration information.
                            Each account is intended for use by
                            its registered account holder.
                            Customers are responsible for updating
                            information when necessary.
                        </p>

                        <h3>3. Account Security</h3>
                        <p>
                            Customers are responsible for protecting
                            their passwords and email verification
                            codes. Passwords and OTP codes must
                            not be shared. NS &amp; Beauty may
                            apply login restrictions, email
                            verification and other measures to
                            protect customer accounts.
                        </p>

                        <h3>4. Appointment Bookings</h3>
                        <p>
                            Appointments are subject to available
                            dates, times, staff schedules, service
                            durations and the booking limitations
                            displayed by the system. Customers
                            should review their booking information
                            before confirming an appointment.
                        </p>

                        <h3>5. Cancellations and Rescheduling</h3>
                        <p>
                            Appointment cancellations and changes
                            are subject to the booking policies
                            and functions available in the system.
                            A requested time slot may be unavailable
                            when an appointment is rescheduled.
                            Relevant changes may be recorded in
                            booking history.
                        </p>

                        <h3>6. Services, Pricing and Payments</h3>
                        <p>
                            Service descriptions, estimated durations
                            and prices are displayed through the
                            booking system. Payments are currently
                            handled directly by NS &amp; Beauty
                            outside the online booking platform.
                            The system does not process customer
                            online payments.
                        </p>

                        <h3>7. Operating Hours and Closures</h3>
                        <p>
                            Service availability may change because
                            of staff absences, operational matters
                            or emergency studio closures.
                            Affected customers may be contacted
                            regarding appointment changes or
                            available rescheduling options.
                        </p>

                        <h3>8. Responsible Use</h3>
                        <p>
                            Customers must not create fraudulent
                            bookings, impersonate another person,
                            attempt unauthorized access, interfere
                            with the website or misuse customer
                            information. Accounts may be restricted
                            in response to suspected misuse or
                            legitimate security concerns.
                        </p>

                        <h3>9. Account and Booking Notifications</h3>
                        <p>
                            NS &amp; Beauty may send essential
                            communications, including registration
                            verification codes, appointment
                            confirmations, reminders, booking
                            changes and security-related messages.
                            These messages support the services
                            requested by customers.
                        </p>
                        <p>
                            NS &amp; Beauty does not currently
                            send promotional marketing emails.
                        </p>

                        <h3>10. Website Availability</h3>
                        <p>
                            NS &amp; Beauty aims to keep the
                            booking system available and accurate.
                            However, maintenance, technical
                            issues or unexpected disruptions
                            may occasionally affect access
                            or appointment availability.
                        </p>

                        <h3>11. Changes to the Terms</h3>
                        <p>
                            These Terms may be revised to reflect
                            operational, technical or legal changes.
                            Material changes will be communicated
                            where required by applicable law.
                            Customers should review the current
                            Terms when using the service.
                        </p>

                        <h3>12. Governing Law and Contact</h3>
                        <p>
                            These Terms are subject to applicable
                            Malaysian law. Questions concerning
                            accounts, appointments or these Terms
                            may be submitted to NS &amp; Beauty at
                            <strong class="policy-contact">
                                nailstudio.beautyofficial@gmail.com
                            </strong>.
                        </p>

                    </div>

                    {{--
                    | NS BEAUTY COMMENT: BAHASA MALAYSIA TERMS
                    --}}

                    <div
                        class="policy-content"
                        id="terms-ms"
                        lang="ms"
                        hidden
                    >

                        <h3>1. Pengenalan dan Persetujuan</h3>
                        <p>
                            Terma dan Syarat ini mengawal akses
                            dan penggunaan sistem tempahan janji
                            temu dalam talian NS &amp; Beauty.
                            Dengan mendaftar akaun, pelanggan
                            bersetuju mematuhi Terma ini serta
                            dasar tempahan yang dipaparkan
                            semasa menggunakan sistem.
                        </p>

                        <h3>2. Pendaftaran Akaun</h3>
                        <p>
                            Pelanggan hendaklah memberikan
                            maklumat pendaftaran yang tepat,
                            lengkap dan terkini. Setiap akaun
                            adalah untuk kegunaan pemegang
                            akaun berdaftar. Pelanggan
                            bertanggungjawab mengemas kini
                            maklumat apabila perlu.
                        </p>

                        <h3>3. Keselamatan Akaun</h3>
                        <p>
                            Pelanggan bertanggungjawab melindungi
                            kata laluan dan kod pengesahan e-mel
                            mereka. Kata laluan dan kod OTP tidak
                            boleh dikongsi. NS &amp; Beauty boleh
                            mengenakan had log masuk, pengesahan
                            e-mel dan langkah keselamatan lain
                            untuk melindungi akaun pelanggan.
                        </p>

                        <h3>4. Tempahan Janji Temu</h3>
                        <p>
                            Janji temu tertakluk pada tarikh,
                            masa, jadual kakitangan, tempoh
                            perkhidmatan serta had tempahan
                            yang tersedia dalam sistem.
                            Pelanggan hendaklah menyemak
                            maklumat tempahan sebelum
                            mengesahkan janji temu.
                        </p>

                        <h3>5. Pembatalan dan Penjadualan Semula</h3>
                        <p>
                            Pembatalan dan perubahan janji temu
                            tertakluk pada dasar tempahan serta
                            fungsi yang tersedia dalam sistem.
                            Slot masa yang diminta mungkin
                            tidak tersedia apabila janji temu
                            dijadualkan semula. Perubahan
                            berkaitan boleh direkodkan
                            dalam sejarah tempahan.
                        </p>

                        <h3>6. Perkhidmatan, Harga dan Pembayaran</h3>
                        <p>
                            Penerangan perkhidmatan, anggaran
                            tempoh dan harga dipaparkan melalui
                            sistem tempahan. Pembayaran pada
                            masa ini diuruskan secara langsung
                            oleh NS &amp; Beauty di luar
                            platform tempahan dalam talian.
                            Sistem ini tidak memproses
                            pembayaran pelanggan secara
                            dalam talian.
                        </p>

                        <h3>7. Waktu Operasi dan Penutupan</h3>
                        <p>
                            Ketersediaan perkhidmatan boleh
                            berubah akibat ketidakhadiran
                            kakitangan, urusan operasi atau
                            penutupan studio secara kecemasan.
                            Pelanggan yang terjejas boleh
                            dihubungi berhubung perubahan
                            janji temu atau pilihan
                            penjadualan semula yang tersedia.
                        </p>

                        <h3>8. Penggunaan yang Bertanggungjawab</h3>
                        <p>
                            Pelanggan tidak dibenarkan membuat
                            tempahan palsu, menyamar sebagai
                            orang lain, cuba mengakses sistem
                            tanpa kebenaran, mengganggu
                            laman web atau menyalahgunakan
                            maklumat pelanggan. Akaun boleh
                            dihadkan sekiranya terdapat
                            penyalahgunaan yang disyaki
                            atau kebimbangan keselamatan
                            yang munasabah.
                        </p>

                        <h3>9. Pemberitahuan Akaun dan Tempahan</h3>
                        <p>
                            NS &amp; Beauty boleh menghantar
                            komunikasi penting seperti
                            kod pengesahan pendaftaran,
                            pengesahan janji temu, peringatan,
                            perubahan tempahan dan mesej
                            berkaitan keselamatan.
                            Mesej ini menyokong perkhidmatan
                            yang diminta oleh pelanggan.
                        </p>
                        <p>
                            NS &amp; Beauty pada masa ini
                            tidak menghantar e-mel
                            pemasaran atau promosi.
                        </p>

                        <h3>10. Ketersediaan Laman Web</h3>
                        <p>
                            NS &amp; Beauty berusaha
                            memastikan sistem tempahan
                            sentiasa tersedia dan tepat.
                            Walau bagaimanapun, penyelenggaraan,
                            masalah teknikal atau gangguan
                            luar jangka mungkin menjejaskan
                            akses atau ketersediaan
                            janji temu.
                        </p>

                        <h3>11. Perubahan kepada Terma</h3>
                        <p>
                            Terma ini boleh disemak semula
                            bagi mencerminkan perubahan
                            operasi, teknikal atau
                            undang-undang. Perubahan penting
                            akan dimaklumkan apabila
                            dikehendaki oleh undang-undang
                            yang terpakai. Pelanggan
                            hendaklah menyemak Terma
                            semasa ketika menggunakan
                            perkhidmatan.
                        </p>

                        <h3>12. Undang-undang dan Hubungan</h3>
                        <p>
                            Terma ini tertakluk pada
                            undang-undang Malaysia yang
                            terpakai. Pertanyaan mengenai
                            akaun, janji temu atau Terma
                            ini boleh dihantar kepada
                            NS &amp; Beauty melalui
                            <strong class="policy-contact">
                                nailstudio.beautyofficial@gmail.com
                            </strong>.
                        </p>

                    </div>
                </div>
            </details>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: PRIVACY NOTICE
            |--------------------------------------------------------------------------
            | Both versions contain 12 corresponding sections.
            |--------------------------------------------------------------------------
            --}}

            <details
                class="policy-details"
                id="privacyDetails"
            >
                <summary>
                    Privacy Notice / Notis Privasi
                </summary>

                <div class="policy-container">

                    <div class="policy-toolbar">
                        <span class="policy-toolbar-label">
                            Document language / Bahasa dokumen
                        </span>

                        <div
                            class="policy-language-buttons"
                            role="group"
                            aria-label="Privacy notice language"
                        >
                            <button
                                type="button"
                                class="policy-language-btn active"
                                data-policy="privacy"
                                data-language="en"
                                aria-pressed="true"
                            >
                                English
                            </button>

                            <button
                                type="button"
                                class="policy-language-btn"
                                data-policy="privacy"
                                data-language="ms"
                                aria-pressed="false"
                            >
                                Bahasa Malaysia
                            </button>
                        </div>
                    </div>

                    {{--
                    | NS BEAUTY COMMENT: ENGLISH PRIVACY NOTICE
                    --}}

                    <div
                        class="policy-content"
                        id="privacy-en"
                        lang="en"
                    >

                        <h3>1. Introduction and Data Controller</h3>
                        <p>
                            NS &amp; Beauty operates in
                            Donggongon, Penampang, Sabah,
                            Malaysia. This Privacy Notice
                            explains how we collect,
                            use, process, store, disclose
                            and protect personal information
                            associated with customer accounts,
                            appointments and related services.
                        </p>
                        <p>
                            NS &amp; Beauty determines the
                            purposes and means of processing
                            customer information for its
                            booking and business operations.
                            Privacy enquiries may be directed to
                            <strong class="policy-contact">
                                nailstudio.beautyofficial@gmail.com
                            </strong>.
                        </p>

                        <h3>2. Personal Information We Collect</h3>
                        <p>
                            We collect or process personal
                            information relevant to registration,
                            accounts and appointments. This
                            may include full name, email address,
                            phone number, optional address,
                            account identifiers, authentication
                            records, booked services, appointment
                            schedules, assigned staff, booking
                            statuses, cancellation and rescheduling
                            history, customer feedback and ratings.
                        </p>
                        <p>
                            Technical and security information
                            may also be processed where necessary
                            to manage accounts, record activity
                            and protect the platform. Account
                            passwords are handled through the
                            application's authentication mechanisms.
                        </p>

                        <h3>3. Sources of Personal Information</h3>
                        <p>
                            Personal information is obtained
                            directly from customers when
                            registering, booking appointments,
                            updating account details, providing
                            feedback or contacting the business.
                            Authorized personnel may enter or
                            update booking-related information
                            during business operations.
                        </p>
                        <p>
                            Certain technical records may
                            also be generated when customers
                            access or use the website.
                        </p>

                        <h3>4. Purposes of Processing</h3>
                        <p>
                            We process information to register
                            and manage customer accounts,
                            authenticate users, verify email
                            addresses, organize appointments,
                            assign staff, communicate booking
                            updates, provide customer support,
                            maintain booking history and
                            prepare internal operational reports.
                        </p>
                        <p>
                            Information is also processed
                            for appropriate account protection,
                            security monitoring and investigation
                            of suspected misuse.
                        </p>
                        <p>
                            <strong>
                                NS &amp; Beauty does not
                                currently send promotional
                                marketing emails.
                            </strong>
                        </p>
                        <p>
                            Email messages from the system
                            are intended for account
                            verification, security and
                            necessary service notifications.
                        </p>

                        <h3>5. Mandatory and Optional Information</h3>
                        <p>
                            Registration requires a full name,
                            phone number, email address and
                            password. Providing an address
                            is optional in the current
                            registration form.
                        </p>
                        <p>
                            Without the required information,
                            we may be unable to register
                            an account, complete email
                            verification or provide access
                            to online appointment booking.
                        </p>

                        <h3>6. Appointment and Booking Records</h3>
                        <p>
                            We process booking information
                            including appointment dates,
                            times, selected services,
                            assigned staff, booking statuses
                            and related changes.
                        </p>
                        <p>
                            Authorized personnel use
                            this information to coordinate
                            services, manage staff availability,
                            respond to customer enquiries
                            and maintain business records.
                            Relevant information may also
                            be used for internal operational
                            analytics.
                        </p>

                        <h3>7. Disclosure to Authorized Personnel and Providers</h3>
                        <p>
                            Customer information may be accessed
                            by authorized administrators and
                            staff where necessary to carry out
                            their responsibilities.
                        </p>
                        <p>
                            Technical providers supporting
                            application hosting, database
                            services, email delivery and
                            related infrastructure may
                            process information required
                            to provide those services.
                        </p>
                        <p>
                            NS &amp; Beauty does not sell
                            customer personal information
                            to advertisers. Information
                            may also be disclosed when
                            required or permitted by law.
                        </p>


                        <h3>8. Personal Data Security</h3>
                        <p>
                            The NS &amp; Beauty system includes
                            security measures designed to
                            protect customer information,
                            including account authentication,
                            role-based access restrictions,
                            email verification and controls
                            intended to prevent unauthorized
                            activity.
                        </p>
                        <p>
                            Access to customer information
                            is intended to be restricted
                            according to authorized user
                            responsibilities. No internet-based
                            system can guarantee absolute security.
                            Appropriate access management,
                            backups and incident handling
                            remain important operational
                            responsibilities.
                        </p>

                        <h3>9. Data Retention and Deletion</h3>
                        <p>
                            NS &amp; Beauty processes personal
                            information for customer account
                            management, appointment services,
                            operational records and applicable
                            legal purposes.
                        </p>
                        <p>
                            Personal information should be
                            retained only for as long as
                            necessary for the relevant purpose
                            or as required or permitted by
                            applicable law.
                        </p>


                        <h3>10. Customer Rights and Privacy Requests</h3>
                        <p>
                            Subject to applicable Malaysian
                            personal data protection law,
                            customers may request access
                            to their personal information,
                            seek correction of inaccurate
                            information and exercise other
                            applicable rights.
                        </p>
                        <p>
                            Where permitted by law,
                            customers may request restrictions
                            on processing or withdraw
                            consent. Requests are subject
                            to applicable legal requirements.
                        </p>
                        <p>
                            Customers may submit enquiries
                            or privacy requests to
                            <strong class="policy-contact">
                                nailstudio.beautyofficial@gmail.com
                            </strong>.
                            Reasonable identity verification
                            may be required before processing
                            requests to prevent unauthorized
                            disclosures.
                        </p>
                        <p>
                            Customers may also raise applicable
                            complaints with the Malaysian
                            Personal Data Protection Commissioner.
                        </p>

                        <h3>11. International Data Transfers</h3>
                        <p>
                            NS &amp; Beauty uses cloud-based
                            services to operate its website
                            and online appointment system.
                            Depending on service configurations,
                            customer information may be
                            stored, processed or accessed
                            outside Malaysia.
                        </p>
                        <p>
                            International transfers, where
                            applicable, must comply with
                            Malaysian personal data
                            protection requirements.
                        </p>


                        <h3>12. Contact Information and Notice Updates</h3>
                        <p>
                            For questions about personal data,
                            requests for access or correction,
                            and concerns regarding information
                            processing, please contact:
                        </p>
                        <p>
                            <strong>NS &amp; Beauty</strong><br>
                            Donggongon, Penampang,<br>
                            Sabah, Malaysia<br>
                            <strong class="policy-contact">
                                nailstudio.beautyofficial@gmail.com
                            </strong>
                        </p>
                        <p>
                            We may update this Privacy Notice
                            to reflect relevant changes in
                            business practices, technology
                            or applicable law. The approved
                            notice will identify its effective
                            date and version.
                        </p>

                    </div>

                    {{--
                    | NS BEAUTY COMMENT: BAHASA MALAYSIA PRIVACY NOTICE
                    --}}

                    <div
                        class="policy-content"
                        id="privacy-ms"
                        lang="ms"
                        hidden
                    >

                        <h3>1. Pengenalan dan Pengawal Data</h3>
                        <p>
                            NS &amp; Beauty beroperasi di
                            Donggongon, Penampang, Sabah,
                            Malaysia. Notis Privasi ini
                            menerangkan cara kami mengumpul,
                            menggunakan, memproses, menyimpan,
                            mendedahkan dan melindungi
                            maklumat peribadi berkaitan
                            akaun pelanggan, janji temu
                            serta perkhidmatan berkaitan.
                        </p>
                        <p>
                            NS &amp; Beauty menentukan
                            tujuan dan kaedah pemprosesan
                            maklumat pelanggan bagi sistem
                            tempahan dan operasi perniagaan.
                            Pertanyaan berkaitan privasi
                            boleh dihantar kepada
                            <strong class="policy-contact">
                                nailstudio.beautyofficial@gmail.com
                            </strong>.
                        </p>

                        <h3>2. Maklumat Peribadi yang Dikumpulkan</h3>
                        <p>
                            Kami mengumpul atau memproses
                            maklumat peribadi yang berkaitan
                            dengan pendaftaran, akaun
                            dan janji temu. Maklumat ini
                            boleh merangkumi nama penuh,
                            alamat e-mel, nombor telefon,
                            alamat pilihan, pengecam akaun,
                            rekod pengesahan, perkhidmatan
                            yang ditempah, jadual janji temu,
                            kakitangan yang ditugaskan,
                            status tempahan, sejarah pembatalan
                            dan penjadualan semula, serta
                            maklum balas dan penilaian pelanggan.
                        </p>
                        <p>
                            Maklumat teknikal dan keselamatan
                            juga boleh diproses apabila
                            diperlukan untuk mengurus akaun,
                            merekodkan aktiviti dan melindungi
                            platform. Kata laluan akaun
                            dikendalikan melalui mekanisme
                            pengesahan aplikasi.
                        </p>

                        <h3>3. Sumber Maklumat Peribadi</h3>
                        <p>
                            Maklumat peribadi diperoleh
                            secara langsung daripada pelanggan
                            semasa mendaftar, membuat tempahan,
                            mengemas kini butiran akaun,
                            memberikan maklum balas atau
                            menghubungi perniagaan.
                        </p>
                        <p>
                            Kakitangan yang diberi kuasa
                            boleh memasukkan atau mengemas
                            kini maklumat tempahan semasa
                            operasi perniagaan. Rekod teknikal
                            tertentu juga boleh dijana
                            apabila pelanggan mengakses
                            atau menggunakan laman web.
                        </p>

                        <h3>4. Tujuan Pemprosesan</h3>
                        <p>
                            Kami memproses maklumat untuk
                            mendaftar dan mengurus akaun
                            pelanggan, mengesahkan pengguna
                            dan alamat e-mel, mengurus
                            janji temu, menugaskan kakitangan,
                            menyampaikan makluman tempahan,
                            memberikan sokongan pelanggan,
                            menyimpan sejarah tempahan
                            dan menyediakan laporan operasi
                            dalaman.
                        </p>
                        <p>
                            Maklumat juga diproses untuk
                            perlindungan akaun, pemantauan
                            keselamatan yang bersesuaian
                            dan penyiasatan penyalahgunaan
                            yang disyaki.
                        </p>
                        <p>
                            <strong>
                                NS &amp; Beauty pada masa
                                ini tidak menghantar e-mel
                                pemasaran atau promosi.
                            </strong>
                        </p>
                        <p>
                            Mesej e-mel daripada sistem
                            bertujuan untuk pengesahan
                            akaun, keselamatan dan
                            pemberitahuan perkhidmatan
                            yang diperlukan.
                        </p>

                        <h3>5. Maklumat Wajib dan Pilihan</h3>
                        <p>
                            Pendaftaran memerlukan nama
                            penuh, nombor telefon,
                            alamat e-mel dan kata laluan.
                            Pemberian alamat adalah
                            pilihan dalam borang
                            pendaftaran semasa.
                        </p>
                        <p>
                            Tanpa maklumat wajib, kami
                            mungkin tidak dapat membuka
                            akaun, melengkapkan pengesahan
                            e-mel atau memberikan akses
                            kepada tempahan janji temu
                            secara dalam talian.
                        </p>

                        <h3>6. Rekod Janji Temu dan Tempahan</h3>
                        <p>
                            Kami memproses maklumat tempahan
                            termasuk tarikh dan masa janji
                            temu, perkhidmatan yang dipilih,
                            kakitangan yang ditugaskan,
                            status tempahan dan perubahan
                            berkaitan.
                        </p>
                        <p>
                            Kakitangan yang diberi kuasa
                            menggunakan maklumat ini untuk
                            menyelaraskan perkhidmatan,
                            mengurus ketersediaan kakitangan,
                            menjawab pertanyaan pelanggan
                            dan menyimpan rekod perniagaan.
                            Maklumat berkaitan juga boleh
                            digunakan bagi analisis
                            operasi dalaman.
                        </p>

                        <h3>7. Pendedahan kepada Kakitangan dan Penyedia Perkhidmatan</h3>
                        <p>
                            Maklumat pelanggan boleh diakses
                            oleh pentadbir dan kakitangan
                            yang diberi kuasa apabila
                            diperlukan untuk melaksanakan
                            tanggungjawab mereka.
                        </p>
                        <p>
                            Penyedia teknikal yang menyokong
                            pengehosan aplikasi, perkhidmatan
                            pangkalan data, penghantaran
                            e-mel dan infrastruktur berkaitan
                            boleh memproses maklumat yang
                            diperlukan bagi menyediakan
                            perkhidmatan tersebut.
                        </p>
                        <p>
                            NS &amp; Beauty tidak menjual
                            maklumat peribadi pelanggan
                            kepada pengiklan. Maklumat
                            juga boleh didedahkan apabila
                            dikehendaki atau dibenarkan
                            oleh undang-undang.
                        </p>


                        <h3>8. Keselamatan Data Peribadi</h3>
                        <p>
                            Sistem NS &amp; Beauty mempunyai
                            langkah keselamatan yang bertujuan
                            melindungi maklumat pelanggan,
                            termasuk pengesahan akaun,
                            sekatan akses berasaskan peranan,
                            pengesahan e-mel dan kawalan
                            untuk mencegah aktiviti
                            tanpa kebenaran.
                        </p>
                        <p>
                            Akses kepada maklumat pelanggan
                            bertujuan dihadkan mengikut
                            tanggungjawab pengguna yang
                            diberi kuasa. Tiada sistem
                            berasaskan internet yang
                            dapat menjamin keselamatan
                            mutlak. Pengurusan akses,
                            sandaran dan pengendalian
                            insiden yang bersesuaian
                            kekal sebagai tanggungjawab
                            operasi yang penting.
                        </p>

                        <h3>9. Tempoh Penyimpanan dan Pemadaman Data</h3>
                        <p>
                            NS &amp; Beauty memproses
                            maklumat peribadi untuk
                            pengurusan akaun pelanggan,
                            perkhidmatan janji temu,
                            rekod operasi dan tujuan
                            undang-undang yang terpakai.
                        </p>
                        <p>
                            Maklumat peribadi hendaklah
                            disimpan hanya selama diperlukan
                            bagi tujuan berkaitan atau
                            sebagaimana dikehendaki atau
                            dibenarkan oleh undang-undang
                            yang terpakai.
                        </p>


                        <h3>10. Hak Pelanggan dan Permohonan Privasi</h3>
                        <p>
                            Tertakluk pada undang-undang
                            perlindungan data peribadi
                            Malaysia yang terpakai,
                            pelanggan boleh memohon akses
                            kepada maklumat peribadi,
                            meminta pembetulan maklumat
                            yang tidak tepat serta
                            menggunakan hak lain yang
                            terpakai.
                        </p>
                        <p>
                            Apabila dibenarkan oleh
                            undang-undang, pelanggan
                            boleh memohon sekatan
                            pemprosesan atau menarik
                            balik persetujuan. Permohonan
                            tertakluk pada keperluan
                            undang-undang yang berkenaan.
                        </p>
                        <p>
                            Pertanyaan atau permohonan
                            privasi boleh dihantar kepada
                            <strong class="policy-contact">
                                nailstudio.beautyofficial@gmail.com
                            </strong>.
                            Pengesahan identiti yang
                            munasabah mungkin diperlukan
                            sebelum memproses permohonan
                            bagi mengelakkan pendedahan
                            tanpa kebenaran.
                        </p>
                        <p>
                            Pelanggan juga boleh mengemukakan
                            aduan yang berkaitan kepada
                            Pesuruhjaya Perlindungan
                            Data Peribadi Malaysia.
                        </p>

                        <h3>11. Pemindahan Data Antarabangsa</h3>
                        <p>
                            NS &amp; Beauty menggunakan
                            perkhidmatan berasaskan awan
                            untuk mengendalikan laman web
                            dan sistem tempahan janji
                            temu dalam talian.
                            Bergantung pada konfigurasi
                            perkhidmatan, maklumat
                            pelanggan mungkin disimpan,
                            diproses atau diakses
                            dari luar Malaysia.
                        </p>
                        <p>
                            Pemindahan antarabangsa,
                            apabila berkenaan, hendaklah
                            mematuhi keperluan undang-undang
                            perlindungan data peribadi
                            Malaysia.
                        </p>


                        <h3>12. Maklumat Hubungan dan Kemas Kini Notis</h3>
                        <p>
                            Untuk pertanyaan mengenai
                            data peribadi, permohonan
                            akses atau pembetulan, serta
                            kebimbangan mengenai pemprosesan
                            maklumat, sila hubungi:
                        </p>
                        <p>
                            <strong>NS &amp; Beauty</strong><br>
                            Donggongon, Penampang,<br>
                            Sabah, Malaysia<br>
                            <strong class="policy-contact">
                                nailstudio.beautyofficial@gmail.com
                            </strong>
                        </p>
                        <p>
                            Kami boleh mengemas kini
                            Notis Privasi ini bagi
                            mencerminkan perubahan
                            amalan perniagaan, teknologi
                            atau undang-undang yang
                            terpakai. Notis yang
                            diluluskan akan menyatakan
                            tarikh kuat kuasa dan
                            nombor versinya.
                        </p>

                    </div>
                </div>
            </details>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: MANDATORY AGREEMENT CHECKBOX
            |--------------------------------------------------------------------------
            | The backend controller must validate:
            | 'terms_accepted' => ['required', 'accepted']
            |--------------------------------------------------------------------------
            --}}

            <label
                for="terms_accepted"
                class="agreement-label"
            >
                <input
                    type="checkbox"
                    class="agreement-checkbox"
                    id="terms_accepted"
                    name="terms_accepted"
                    value="1"
                    required
                    aria-required="true"
                >

                <span>
                    I have read and agree to the
                    <strong>Terms and Conditions</strong>
                    and acknowledge the
                    <strong>Privacy Notice</strong>
                    of NS &amp; Beauty.

                    <span style="display:block;margin-top:6px;">
                        Saya telah membaca dan bersetuju
                        dengan <strong>Terma dan Syarat</strong>
                        serta mengakui
                        <strong>Notis Privasi</strong>
                        NS &amp; Beauty.
                    </span>
                </span>
            </label>

            @error('terms_accepted')
                <div class="error-text" role="alert">
                    {{ $message }}
                </div>
            @enderror

            <p class="agreement-footnote">
                Agreement covers registration and essential
                service communications only. It does not
                authorize optional promotional marketing.

                Persetujuan meliputi pendaftaran dan
                komunikasi perkhidmatan yang perlu sahaja.
                Ia tidak memberikan kebenaran untuk
                pemasaran promosi pilihan.
            </p>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: REGISTER AND SEND EMAIL OTP
        |--------------------------------------------------------------------------
        --}}

        <button
            type="submit"
            class="submit-btn"
            id="registrationSubmitButton"
            disabled
        >
            Create Account &amp; Send Email OTP
        </button>

    </form>

    {{-- NS BEAUTY COMMENT: FOOTER NAVIGATION --}}

    <div class="footer">
        Already have an account?
        <a href="{{ route('login') }}">
            Sign In
        </a>
    </div>

    <a
        href="{{ route('home') }}"
        class="back"
    >
        ← Back to Home
    </a>

</main>

<script>
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: PASSWORD VISIBILITY BUTTONS
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        ".password-toggle"
    ).forEach(function (button) {

        button.addEventListener("click", function () {

            const inputId = button.getAttribute(
                "data-password-target"
            );

            const input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            const eyeIcon = button.querySelector(".eye-icon");
            const eyeOffIcon = button.querySelector(".eye-off-icon");

            const wasHidden = input.type === "password";

            input.type = wasHidden ? "text" : "password";

            if (eyeIcon) {
                eyeIcon.hidden = wasHidden;
            }

            if (eyeOffIcon) {
                eyeOffIcon.hidden = !wasHidden;
            }

            const isConfirmation =
                inputId === "password_confirmation";

            const label = wasHidden
                ? (
                    isConfirmation
                        ? "Hide confirm password"
                        : "Hide password"
                )
                : (
                    isConfirmation
                        ? "Show confirm password"
                        : "Show password"
                );

            button.setAttribute("aria-label", label);
            button.setAttribute(
                "aria-pressed",
                wasHidden ? "true" : "false"
            );
            button.title = label;
        });
    });

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: POLICY LANGUAGE SWITCHES
    |--------------------------------------------------------------------------
    | Terms and Privacy can be switched independently.
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        ".policy-language-btn"
    ).forEach(function (button) {

        button.addEventListener("click", function () {

            const policy = button.dataset.policy;
            const language = button.dataset.language;

            if (
                !["terms", "privacy"].includes(policy) ||
                !["en", "ms"].includes(language)
            ) {
                return;
            }

            const englishContent = document.getElementById(
                policy + "-en"
            );

            const malayContent = document.getElementById(
                policy + "-ms"
            );

            if (!englishContent || !malayContent) {
                return;
            }

            englishContent.hidden = language !== "en";
            malayContent.hidden = language !== "ms";

            document.querySelectorAll(
                '.policy-language-btn[data-policy="' + policy + '"]'
            ).forEach(function (languageButton) {

                const isActive =
                    languageButton.dataset.language === language;

                languageButton.classList.toggle(
                    "active",
                    isActive
                );

                languageButton.setAttribute(
                    "aria-pressed",
                    isActive ? "true" : "false"
                );
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: AGREEMENT VALIDATION
    |--------------------------------------------------------------------------
    | Frontend validation supplements backend validation.
    | Checkbox is never preselected.
    |--------------------------------------------------------------------------
    */

    const agreementCheckbox = document.getElementById(
        "terms_accepted"
    );

    const submitButton = document.getElementById(
        "registrationSubmitButton"
    );

    const registrationForm = document.getElementById(
        "registrationForm"
    );

    function updateAgreementState() {

        if (!agreementCheckbox || !submitButton) {
            return;
        }

        submitButton.disabled = !agreementCheckbox.checked;
    }

    if (agreementCheckbox && submitButton) {

        agreementCheckbox.checked = false;

        updateAgreementState();

        agreementCheckbox.addEventListener(
            "change",
            updateAgreementState
        );

        window.addEventListener(
            "pageshow",
            updateAgreementState
        );
    }

    if (registrationForm && agreementCheckbox) {

        registrationForm.addEventListener(
            "submit",
            function (event) {

                if (!agreementCheckbox.checked) {

                    event.preventDefault();

                    agreementCheckbox.reportValidity();
                    agreementCheckbox.focus();
                }
            }
        );
    }
</script>

</body>
</html>
