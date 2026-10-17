<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | NS &amp; Beauty</title>

    {{-- NS BEAUTY COMMENT: Shared logo for local and Laravel Cloud --}}
    <link rel="icon" type="image/png"
          href="{{ asset('images/nsbeauty-logo.png') }}">
    <link rel="apple-touch-icon"
          href="{{ asset('images/nsbeauty-logo.png') }}">

    <style>
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: REGISTRATION DESIGN
        |--------------------------------------------------------------------------
        | Muji-inspired cream, brown and glass appearance.
        | The existing registration route and field names are preserved.
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
            --muted: #7d6d60;
            --placeholder: #aa9e93;
            --glass: rgba(255,255,255,.54);
            --border: rgba(255,255,255,.70);
            --radius-card: 24px;
            --radius-input: 14px;
            --radius-button: 14px;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
            color: var(--brown-dark);
            background:
                radial-gradient(circle at 15% 18%,
                    rgba(255,255,255,.78), transparent 28%),
                radial-gradient(circle at 88% 12%,
                    rgba(234,216,196,.88), transparent 30%),
                linear-gradient(135deg,
                    #f1e6d8 0%, #fffdf9 45%, #e7d2bd 100%);
        }

        .card {
            width: 100%;
            max-width: 520px;
            padding: 40px;
            background: var(--glass);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            box-shadow:
                0 20px 50px rgba(47,33,24,.12),
                inset 0 1px 0 rgba(255,255,255,.7);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: OFFICIAL ROUND LOGO
        |--------------------------------------------------------------------------
        */

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 11px;
            margin-bottom: 20px;
            color: var(--brown-dark);
            font-size: 15px;
            font-weight: 900;
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
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        h1 {
            text-align: center;
            margin-bottom: 12px;
            font-size: clamp(28px,4vw,34px);
            letter-spacing: -.7px;
            line-height: 1.2;
            background: linear-gradient(
                135deg,#2f2118,#6f4e37,#a38468
            );
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            text-align: center;
            color: var(--muted);
            margin-bottom: 28px;
            font-size: 14px;
            line-height: 1.7;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: FORM INPUTS
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
            border-radius: var(--radius-input);
            border: 1px solid rgba(111,78,55,.16);
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
        | Hide Edge's native reveal button to avoid two eye icons.
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
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 12px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            z-index: 2;
        }

        .password-toggle:hover {
            background: rgba(111,78,55,.08);
            color: var(--brown);
        }

        .password-toggle:focus-visible {
            outline: 2px solid var(--brown);
            outline-offset: 2px;
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
            padding: 14px 16px;
            border-radius: var(--radius-input);
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
        }

        .alert-error {
            background: rgba(150,45,35,.10);
            color: #7a241d;
            border: 1px solid rgba(150,45,35,.18);
        }

        .alert-success {
            background: rgba(50,130,85,.10);
            color: #285c3f;
            border: 1px solid rgba(50,130,85,.18);
        }

        .error-text {
            margin-top: 7px;
            font-size: 12px;
            line-height: 1.5;
            color: #8a2f24;
            font-weight: 700;
        }

        .note {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 22px;
            background: rgba(255,255,255,.44);
            border: 1px solid rgba(255,255,255,.70);
            border-radius: var(--radius-input);
            padding: 14px 16px;
        }

        .note strong {
            color: var(--brown);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: BILINGUAL TERMS AND PRIVACY
        |--------------------------------------------------------------------------
        | Each document has its own EN / BM selector.
        | English is the default displayed language.
        |--------------------------------------------------------------------------
        */

        .agreement-section {
            margin: 6px 0 20px;
            padding: 18px;
            background: rgba(255,255,255,.46);
            border: 1px solid rgba(111,78,55,.14);
            border-radius: var(--radius-input);
        }

        .agreement-heading {
            font-size: 15px;
            font-weight: 850;
            color: var(--brown-dark);
            margin-bottom: 8px;
        }

        .agreement-description {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.7;
            margin-bottom: 14px;
        }

        .policy-details {
            margin-bottom: 11px;
            border: 1px solid rgba(111,78,55,.14);
            border-radius: 14px;
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

        .policy-language-btn:focus-visible {
            outline: 2px solid var(--brown);
            outline-offset: 2px;
        }

        .policy-content {
            max-height: 350px;
            overflow-y: auto;
            padding: 3px 5px 3px 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.85;
            overscroll-behavior: contain;
        }

        .policy-content[hidden] {
            display: none !important;
        }

        .policy-content h3 {
            margin: 12px 0 6px;
            font-size: 13px;
            line-height: 1.5;
            color: var(--brown-dark);
        }

        .policy-content p {
            margin-bottom: 13px;
        }

        .policy-content strong {
            color: var(--brown-dark);
        }

        .policy-disclaimer {
            padding: 12px;
            border-radius: 12px;
            background: rgba(111,78,55,.07);
            font-size: 11px;
            line-height: 1.7;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: MANDATORY AGREEMENT
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

        .submit-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            min-height: 50px;
            padding: 14px 20px;
            margin-top: 4px;
            border: none;
            border-radius: var(--radius-button);
            color: white;
            font-size: 14px;
            font-weight: 850;
            background: linear-gradient(135deg,#8a7158,#5d3f2c);
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

        .submit-btn:focus-visible {
            outline: 3px solid rgba(111,78,55,.4);
            outline-offset: 3px;
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
            text-align: center;
            margin-top: 20px;
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

            .policy-toolbar {
                align-items: flex-start;
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

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: ROUND BUSINESS LOGO
    |--------------------------------------------------------------------------
    --}}

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

    {{-- NS BEAUTY COMMENT: Session and validation messages --}}

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
    | NS BEAUTY COMMENT: EXISTING REGISTRATION FORM
    |--------------------------------------------------------------------------
    | These routes and field names must remain unchanged so the
    | backend registration and OTP process continue to work.
    |--------------------------------------------------------------------------
    --}}

    <form
        id="registrationForm"
        method="POST"
        action="{{ route('register.store') }}"
    >
        @csrf

        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
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

        <div class="note">
            <strong>Email verification</strong>
            — A one-time verification code will be sent to
            your email address during registration.
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: PASSWORD
        |--------------------------------------------------------------------------
        --}}

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
                    <svg class="eye-icon" viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg class="eye-off-icon" viewBox="0 0 24 24"
                         aria-hidden="true" hidden>
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

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: CONFIRM PASSWORD
        |--------------------------------------------------------------------------
        --}}

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
                    <svg class="eye-icon" viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg class="eye-off-icon" viewBox="0 0 24 24"
                         aria-hidden="true" hidden>
                        <path d="M10.6 5.2A10.5 10.5 0 0 1 12 5c6.5 0 10 7 10 7a15.5 15.5 0 0 1-3.2 4.1"/>
                        <path d="M6.1 6.1A15 15 0 0 0 2 12s3.5 7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" hidden/>
                        <path d="M6.1 6.1A15 15 0 0 0 2 12s3.5 7 10 7a10 10 0 0 0 5.3-1.5"/>
                        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                        <path d="M3 3l18 18"/>
                    </svg>
                </button>
            </div>
        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: BILINGUAL LEGAL DOCUMENTS
        |--------------------------------------------------------------------------
        | These are working policy drafts, not independently verified
        | legal-compliance certifications.
        |
        | OWNER MUST CONFIRM:
        | - Official privacy contact
        | - Specific retention/deletion practices
        | - All data recipients and infrastructure locations
        | - Applicable customer rights and actual handling procedures
        | - Final language and legal accuracy
        |--------------------------------------------------------------------------
        --}}

        <section class="agreement-section"
                 aria-labelledby="agreementHeading">

            <h2 class="agreement-heading" id="agreementHeading">
                Terms, Conditions &amp; Privacy
            </h2>

            <p class="agreement-description">
                Please review the Terms and Conditions and Privacy
                Notice in English or Bahasa Malaysia before creating
                your NS &amp; Beauty account.
            </p>

            {{-- =====================================================
                 NS BEAUTY COMMENT: TERMS AND CONDITIONS
                 ===================================================== --}}

            <details class="policy-details" id="termsDetails">
                <summary>Terms and Conditions / Terma dan Syarat</summary>

                <div class="policy-container">

                    <div class="policy-toolbar">
                        <span class="policy-toolbar-label">
                            Document language / Bahasa dokumen
                        </span>

                        <div class="policy-language-buttons"
                             role="group"
                             aria-label="Terms language">

                            <button type="button"
                                    class="policy-language-btn active"
                                    data-policy="terms"
                                    data-language="en"
                                    aria-pressed="true">
                                English
                            </button>

                            <button type="button"
                                    class="policy-language-btn"
                                    data-policy="terms"
                                    data-language="ms"
                                    aria-pressed="false">
                                Bahasa Malaysia
                            </button>
                        </div>
                    </div>

                    {{--
                    | NS BEAUTY COMMENT: ENGLISH TERMS
                    --}}

                    <div class="policy-content"
                         id="terms-en"
                         lang="en">

                        <h3>1. Introduction and Acceptance</h3>
                        <p>
                            These Terms and Conditions govern the use of
                            the NS &amp; Beauty online appointment
                            booking system. By registering and using
                            the service, customers agree to comply
                            with these terms and applicable booking
                            policies displayed on the website.
                        </p>

                        <h3>2. Account Registration</h3>
                        <p>
                            Customers must provide accurate and current
                            personal information. Accounts are intended
                            for their registered users. Customers are
                            responsible for keeping their account details
                            reasonably up to date.
                        </p>

                        <h3>3. Account Security</h3>
                        <p>
                            Customers must keep their passwords and
                            one-time verification codes confidential.
                            NS &amp; Beauty may apply email verification,
                            login limits, and other security measures
                            to protect customer accounts. Suspected
                            unauthorized access should be reported
                            through the business's official channels.
                        </p>

                        <h3>4. Appointment Booking</h3>
                        <p>
                            Appointment bookings depend on service
                            availability, staff schedules, service
                            duration, and the booking restrictions
                            displayed by the system. Submission of
                            a booking does not guarantee that a
                            particular staff member or service will
                            remain available if an operational issue
                            arises.
                        </p>

                        <h3>5. Cancellations and Rescheduling</h3>
                        <p>
                            Customers may request appointment changes
                            where the system permits them. Requests
                            remain subject to the applicable booking
                            rules, available time slots, and
                            operational requirements. Appointment
                            status changes may be recorded in
                            booking history.
                        </p>

                        <h3>6. Services, Prices and Payments</h3>
                        <p>
                            Service descriptions, estimated durations,
                            and prices are displayed in the booking
                            system. NS &amp; Beauty currently handles
                            payments outside this online registration
                            and booking platform. Any changes to
                            service prices or payment arrangements
                            should be confirmed with the business.
                        </p>

                        <h3>7. Business Schedule Changes</h3>
                        <p>
                            Operating schedules and appointments
                            may be affected by staff absences,
                            emergency closures, or other reasonable
                            business circumstances. NS &amp; Beauty
                            may contact affected customers using
                            their registered contact information.
                        </p>

                        <h3>8. Responsible Use</h3>
                        <p>
                            Customers must not create fraudulent
                            appointments, impersonate other persons,
                            attempt to bypass security controls,
                            interfere with the platform, or access
                            information without authorization.
                            Accounts may be restricted when misuse
                            or security risks are reasonably identified.
                        </p>

                        <h3>9. Notifications and Communications</h3>
                        <p>
                            The system may send essential communications
                            such as email verification codes, appointment
                            confirmations, reminders, cancellation
                            updates, and security-related messages.
                            Optional promotional messages should be
                            managed separately from necessary service
                            communications.
                        </p>

                        <h3>10. Accuracy and Availability</h3>
                        <p>
                            NS &amp; Beauty aims to maintain accurate
                            appointment information and a reliable
                            website. Temporary interruptions, technical
                            errors, or schedule changes may occur.
                            Customers should contact the business
                            if they believe an appointment record
                            is incorrect.
                        </p>

                        <h3>11. Changes to These Terms</h3>
                        <p>
                            These Terms may be updated to reflect
                            changes in business operations, security
                            requirements, or applicable law. Material
                            changes should be communicated appropriately.
                            Customers should review the current terms
                            when using the platform.
                        </p>

                        <h3>12. Applicable Law and Contact</h3>
                        <p>
                            These Terms are governed by applicable
                            Malaysian law. Questions relating to
                            account access, appointments, or these
                            Terms may be directed to NS &amp; Beauty
                            through its published official contact
                            channels.
                        </p>

                    </div>

                    {{--
                    | NS BEAUTY COMMENT: BAHASA MALAYSIA TERMS
                    --}}

                    <div class="policy-content"
                         id="terms-ms"
                         lang="ms"
                         hidden>

                        <h3>1. Pengenalan dan Persetujuan</h3>
                        <p>
                            Terma dan Syarat ini mengawal penggunaan
                            sistem tempahan janji temu dalam talian
                            NS &amp; Beauty. Dengan mendaftar dan
                            menggunakan perkhidmatan ini, pelanggan
                            bersetuju untuk mematuhi terma ini serta
                            dasar tempahan yang dipaparkan dalam
                            laman web.
                        </p>

                        <h3>2. Pendaftaran Akaun</h3>
                        <p>
                            Pelanggan hendaklah memberikan maklumat
                            peribadi yang tepat dan terkini. Akaun
                            adalah untuk kegunaan pemilik akaun
                            yang berdaftar. Pelanggan bertanggungjawab
                            memastikan maklumat akaun mereka sentiasa
                            dikemas kini apabila perlu.
                        </p>

                        <h3>3. Keselamatan Akaun</h3>
                        <p>
                            Pelanggan hendaklah merahsiakan kata laluan
                            dan kod pengesahan sekali guna. NS &amp;
                            Beauty boleh menggunakan pengesahan e-mel,
                            had percubaan log masuk dan langkah
                            keselamatan lain untuk melindungi akaun.
                            Sebarang akses tanpa kebenaran yang disyaki
                            hendaklah dilaporkan melalui saluran rasmi
                            perniagaan.
                        </p>

                        <h3>4. Tempahan Janji Temu</h3>
                        <p>
                            Tempahan adalah tertakluk pada ketersediaan
                            perkhidmatan, jadual kakitangan, tempoh
                            perkhidmatan dan had tempahan yang
                            dipaparkan oleh sistem. Tempahan yang
                            dibuat tidak menjamin kakitangan atau
                            perkhidmatan tertentu akan kekal tersedia
                            sekiranya timbul masalah operasi.
                        </p>

                        <h3>5. Pembatalan dan Penjadualan Semula</h3>
                        <p>
                            Pelanggan boleh memohon perubahan janji
                            temu apabila fungsi tersebut dibenarkan
                            oleh sistem. Permohonan tertakluk pada
                            peraturan tempahan, slot masa yang tersedia
                            serta keperluan operasi perniagaan.
                            Perubahan status janji temu boleh direkodkan
                            dalam sejarah tempahan.
                        </p>

                        <h3>6. Perkhidmatan, Harga dan Pembayaran</h3>
                        <p>
                            Penerangan perkhidmatan, anggaran tempoh
                            dan harga dipaparkan dalam sistem tempahan.
                            NS &amp; Beauty pada masa ini menguruskan
                            pembayaran di luar platform pendaftaran
                            dan tempahan dalam talian ini. Sebarang
                            perubahan harga atau kaedah pembayaran
                            hendaklah disahkan dengan pihak perniagaan.
                        </p>

                        <h3>7. Perubahan Jadual Perniagaan</h3>
                        <p>
                            Waktu operasi dan janji temu boleh terjejas
                            akibat ketidakhadiran kakitangan, penutupan
                            kecemasan atau keadaan operasi yang
                            munasabah. NS &amp; Beauty boleh menghubungi
                            pelanggan yang terlibat menggunakan
                            maklumat hubungan yang didaftarkan.
                        </p>

                        <h3>8. Penggunaan yang Bertanggungjawab</h3>
                        <p>
                            Pelanggan tidak dibenarkan membuat
                            tempahan palsu, menyamar sebagai orang
                            lain, memintas kawalan keselamatan,
                            mengganggu sistem atau mengakses maklumat
                            tanpa kebenaran. Akaun boleh dihadkan
                            jika penyalahgunaan atau risiko keselamatan
                            dikenal pasti secara munasabah.
                        </p>

                        <h3>9. Pemberitahuan dan Komunikasi</h3>
                        <p>
                            Sistem boleh menghantar komunikasi penting
                            seperti kod pengesahan e-mel, pengesahan
                            janji temu, peringatan, makluman pembatalan
                            dan mesej keselamatan. Mesej promosi
                            pilihan hendaklah diuruskan secara berasingan
                            daripada komunikasi perkhidmatan yang perlu.
                        </p>

                        <h3>10. Ketepatan dan Ketersediaan</h3>
                        <p>
                            NS &amp; Beauty berusaha mengekalkan
                            maklumat janji temu yang tepat serta
                            laman web yang boleh dipercayai.
                            Gangguan sementara, ralat teknikal atau
                            perubahan jadual mungkin berlaku.
                            Pelanggan hendaklah menghubungi perniagaan
                            jika terdapat rekod janji temu yang
                            dipercayai tidak tepat.
                        </p>

                        <h3>11. Pindaan Terma</h3>
                        <p>
                            Terma ini boleh dikemas kini berdasarkan
                            perubahan operasi perniagaan, keperluan
                            keselamatan atau undang-undang yang
                            terpakai. Perubahan penting hendaklah
                            dimaklumkan melalui kaedah yang bersesuaian.
                            Pelanggan digalakkan menyemak terma
                            semasa apabila menggunakan platform.
                        </p>

                        <h3>12. Undang-undang dan Hubungan</h3>
                        <p>
                            Terma ini tertakluk pada undang-undang
                            Malaysia yang berkenaan. Pertanyaan
                            mengenai akses akaun, janji temu atau
                            terma ini boleh diajukan kepada
                            NS &amp; Beauty melalui saluran hubungan
                            rasmi yang diterbitkan oleh perniagaan.
                        </p>

                    </div>
                </div>
            </details>

            {{-- =====================================================
                 NS BEAUTY COMMENT: PRIVACY NOTICE
                 ===================================================== --}}

            <details class="policy-details" id="privacyDetails">
                <summary>Privacy Notice / Notis Privasi</summary>

                <div class="policy-container">

                    <div class="policy-toolbar">
                        <span class="policy-toolbar-label">
                            Document language / Bahasa dokumen
                        </span>

                        <div class="policy-language-buttons"
                             role="group"
                             aria-label="Privacy notice language">

                            <button type="button"
                                    class="policy-language-btn active"
                                    data-policy="privacy"
                                    data-language="en"
                                    aria-pressed="true">
                                English
                            </button>

                            <button type="button"
                                    class="policy-language-btn"
                                    data-policy="privacy"
                                    data-language="ms"
                                    aria-pressed="false">
                                Bahasa Malaysia
                            </button>
                        </div>
                    </div>

                    {{--
                    | NS BEAUTY COMMENT: ENGLISH PRIVACY NOTICE
                    --}}

                    <div class="policy-content"
                         id="privacy-en"
                         lang="en">

                        <h3>1. Introduction and Data Controller</h3>
                        <p>
                            NS &amp; Beauty, operating in Donggongon,
                            Penampang, Sabah, Malaysia, is responsible
                            for personal information processed for
                            its booking and customer account services.
                            This Privacy Notice explains how information
                            is collected, used, disclosed, secured,
                            retained and managed.
                        </p>
                        <p>
                            Customers should use the privacy contact
                            published by NS &amp; Beauty to make
                            enquiries about personal data. The business
                            must confirm and publish its official
                            privacy contact details before launch.
                        </p>

                        <h3>2. Personal Information We Collect</h3>
                        <p>
                            Information may include your name, email
                            address, phone number, address if supplied,
                            account identifiers, booking selections,
                            appointment schedules, assigned staff,
                            appointment statuses, feedback, and
                            records needed for account security.
                            Passwords should be stored using secure
                            one-way hashing rather than as readable text.
                        </p>

                        <h3>3. Sources of Personal Information</h3>
                        <p>
                            Personal information is primarily obtained
                            directly from customers during registration,
                            booking, account updates, feedback, and
                            communications with the business. Relevant
                            booking updates may also be entered by
                            authorized staff. Technical and security
                            records may be generated through use of
                            the website.
                        </p>

                        <h3>4. Purposes of Processing</h3>
                        <p>
                            Information is processed to create and
                            manage accounts, authenticate users,
                            verify email addresses, schedule and
                            manage appointments, communicate booking
                            updates, provide customer support,
                            secure the platform, investigate misuse,
                            and generate operational reporting.
                            Information should not be used for
                            unrelated purposes without an appropriate
                            legal basis and notice.
                        </p>

                        <h3>5. Mandatory and Optional Information</h3>
                        <p>
                            A name, phone number, email address, and
                            password are required by the current
                            registration form. An address is optional
                            in the form. Without required information,
                            the business may be unable to create
                            an account, complete email verification,
                            or support online appointments.
                            Optional information should not be
                            required unnecessarily.
                        </p>

                        <h3>6. Appointment and Booking Records</h3>
                        <p>
                            The system may retain appointment dates
                            and times, selected beauty services,
                            assigned staff, booking status,
                            cancellations, rescheduling activity,
                            feedback, and related operational
                            records. Such information is used to
                            manage customer appointments, coordinate
                            staff, maintain records, and support
                            authorized business reporting.
                        </p>

                        <h3>7. Disclosure to Staff and Service Providers</h3>
                        <p>
                            Personal information may be accessed by
                            authorized NS &amp; Beauty personnel according
                            to their duties and permissions. Providers
                            supporting website hosting, databases,
                            email delivery, or other necessary
                            infrastructure may process information
                            only to the extent needed to deliver
                            those services.
                        </p>
                        <p>
                            The business must verify its actual
                            providers, categories of recipients
                            and contractual arrangements before
                            publishing the final notice. Personal
                            information should not be disclosed
                            to unrelated parties without a lawful
                            basis.
                        </p>

                        <h3>8. Data Security</h3>
                        <p>
                            NS &amp; Beauty applies or intends to apply
                            appropriate security safeguards such as
                            authenticated access, role-based permissions,
                            password protection, email verification,
                            input validation, and account activity
                            controls. Access to personal data should
                            be limited to authorized users.
                        </p>
                        <p>
                            No internet-based service can guarantee
                            absolute security. The business should
                            maintain suitable backup, incident
                            handling, and access management procedures.
                        </p>

                        <h3>9. Data Retention and Deletion</h3>
                        <p>
                            Personal information should be retained
                            only for as long as necessary for legitimate
                            business purposes, applicable legal
                            requirements, and the handling of relevant
                            enquiries or disputes. Information that
                            is no longer required should be securely
                            deleted, anonymized, or otherwise handled
                            under an approved retention procedure.
                        </p>
                        <p>
                            The business must establish and disclose
                            its actual retention periods or the
                            criteria used to determine them before
                            finalizing this notice.
                        </p>

                        <h3>10. Customer Rights and Requests</h3>
                        <p>
                            Subject to applicable Malaysian law,
                            customers may have rights to request
                            access to and correction of personal data,
                            withdraw consent where permitted, and
                            exercise other applicable statutory rights.
                            Requests can be submitted through the
                            business's official privacy contact.
                        </p>
                        <p>
                            NS &amp; Beauty may need to verify the
                            requester's identity before responding.
                            Certain information may need to be retained
                            where required or permitted by law.
                            Customers may also raise concerns with
                            the relevant Malaysian data protection
                            authority where applicable.
                        </p>

                        <h3>11. International Data Transfers</h3>
                        <p>
                            Website hosting, email delivery, technical
                            support, or connected infrastructure may
                            involve processing or access outside
                            Malaysia, depending on the services and
                            regions configured by the business.
                            Such transfers must comply with applicable
                            Malaysian personal data protection
                            requirements.
                        </p>
                        <p>
                            NS &amp; Beauty must verify where personal
                            information is stored or accessed and
                            what safeguards apply before making
                            definitive statements about international
                            data transfers.
                        </p>

                        <h3>12. Contact Information and Notice Updates</h3>
                        <p>
                            Customers may contact NS &amp; Beauty
                            through its official published channels
                            for privacy enquiries, access or correction
                            requests, and concerns regarding the use
                            of personal data.
                        </p>
                        <p>
                            This Notice may be revised when processing
                            practices, providers, legal requirements,
                            or operational needs change. The latest
                            approved notice should identify its
                            effective date and be made accessible
                            to customers.
                        </p>

                        <p class="policy-disclaimer">
                            <strong>Implementation note:</strong>
                            This is a draft notice. The business must
                            verify its actual processing practices,
                            official privacy contact, data recipients,
                            retention arrangements, and international
                            transfers before relying on it as its
                            final public privacy notice.
                        </p>

                    </div>

                    {{--
                    | NS BEAUTY COMMENT: BAHASA MALAYSIA PRIVACY NOTICE
                    --}}

                    <div class="policy-content"
                         id="privacy-ms"
                         lang="ms"
                         hidden>

                        <h3>1. Pengenalan dan Pengawal Data</h3>
                        <p>
                            NS &amp; Beauty, yang beroperasi di
                            Donggongon, Penampang, Sabah, Malaysia,
                            bertanggungjawab terhadap maklumat
                            peribadi yang diproses bagi perkhidmatan
                            akaun pelanggan dan tempahan janji temu.
                            Notis Privasi ini menerangkan cara
                            maklumat dikumpulkan, digunakan,
                            didedahkan, dilindungi, disimpan
                            dan diuruskan.
                        </p>
                        <p>
                            Pelanggan boleh menggunakan maklumat
                            hubungan privasi rasmi yang diterbitkan
                            oleh NS &amp; Beauty untuk mengemukakan
                            pertanyaan. Pihak perniagaan hendaklah
                            mengesahkan dan menerbitkan maklumat
                            hubungan privasi rasmi sebelum pelancaran.
                        </p>

                        <h3>2. Maklumat Peribadi yang Dikumpulkan</h3>
                        <p>
                            Maklumat yang boleh dikumpulkan termasuk
                            nama, alamat e-mel, nombor telefon,
                            alamat jika diberikan, pengecam akaun,
                            pilihan tempahan, jadual janji temu,
                            kakitangan yang ditugaskan, status
                            tempahan, maklum balas dan rekod
                            yang diperlukan untuk keselamatan akaun.
                            Kata laluan sepatutnya disimpan menggunakan
                            kaedah pencincangan sehala yang selamat,
                            bukannya dalam bentuk teks yang boleh dibaca.
                        </p>

                        <h3>3. Sumber Maklumat Peribadi</h3>
                        <p>
                            Maklumat peribadi diperoleh terutamanya
                            secara langsung daripada pelanggan melalui
                            pendaftaran, tempahan, kemas kini akaun,
                            maklum balas dan komunikasi dengan
                            perniagaan. Kakitangan yang diberi kuasa
                            juga boleh memasukkan kemas kini tempahan.
                            Rekod teknikal dan keselamatan boleh
                            dijana melalui penggunaan laman web.
                        </p>

                        <h3>4. Tujuan Pemprosesan</h3>
                        <p>
                            Maklumat diproses untuk membuka dan
                            mengurus akaun, mengesahkan pengguna,
                            mengesahkan alamat e-mel, menjadualkan
                            serta mengurus janji temu, menghantar
                            makluman tempahan, memberikan sokongan
                            pelanggan, melindungi sistem, menyiasat
                            penyalahgunaan dan menghasilkan laporan
                            operasi. Maklumat tidak sepatutnya
                            digunakan untuk tujuan yang tidak berkaitan
                            tanpa asas undang-undang dan notis
                            yang sewajarnya.
                        </p>

                        <h3>5. Maklumat Wajib dan Pilihan</h3>
                        <p>
                            Nama, nombor telefon, alamat e-mel dan
                            kata laluan diperlukan dalam borang
                            pendaftaran semasa. Alamat adalah
                            maklumat pilihan. Tanpa maklumat wajib,
                            perniagaan mungkin tidak dapat membuka
                            akaun, melengkapkan pengesahan e-mel
                            atau menyediakan tempahan janji temu
                            dalam talian. Maklumat pilihan tidak
                            sepatutnya diwajibkan tanpa keperluan.
                        </p>

                        <h3>6. Rekod Janji Temu dan Tempahan</h3>
                        <p>
                            Sistem boleh menyimpan tarikh dan masa
                            janji temu, perkhidmatan kecantikan yang
                            dipilih, kakitangan yang ditugaskan,
                            status tempahan, pembatalan, aktiviti
                            penjadualan semula, maklum balas dan
                            rekod operasi yang berkaitan. Maklumat
                            tersebut digunakan untuk mengurus
                            janji temu, menyelaraskan kakitangan,
                            menyimpan rekod dan menyediakan laporan
                            perniagaan yang dibenarkan.
                        </p>

                        <h3>7. Pendedahan kepada Kakitangan dan Penyedia Perkhidmatan</h3>
                        <p>
                            Maklumat peribadi boleh diakses oleh
                            kakitangan NS &amp; Beauty yang diberi
                            kuasa mengikut tugas serta kebenaran
                            masing-masing. Penyedia pengehosan
                            laman web, pangkalan data, penghantaran
                            e-mel atau infrastruktur lain boleh
                            memproses maklumat setakat yang diperlukan
                            bagi menyediakan perkhidmatan tersebut.
                        </p>
                        <p>
                            Pihak perniagaan hendaklah mengesahkan
                            penyedia sebenar, kategori penerima
                            maklumat dan perjanjian berkaitan sebelum
                            menerbitkan notis akhir. Maklumat
                            peribadi tidak sepatutnya didedahkan
                            kepada pihak yang tidak berkaitan tanpa
                            asas yang sah.
                        </p>

                        <h3>8. Keselamatan Data</h3>
                        <p>
                            NS &amp; Beauty menggunakan atau merancang
                            untuk menggunakan langkah keselamatan
                            yang bersesuaian seperti pengesahan
                            pengguna, kawalan akses berasaskan peranan,
                            perlindungan kata laluan, pengesahan
                            e-mel, pengesahan input dan kawalan
                            aktiviti akaun. Akses kepada data
                            peribadi hendaklah dihadkan kepada
                            pengguna yang diberi kuasa.
                        </p>
                        <p>
                            Tiada perkhidmatan internet yang dapat
                            menjamin keselamatan mutlak. Perniagaan
                            hendaklah menyediakan prosedur sandaran,
                            pengendalian insiden dan pengurusan
                            akses yang sesuai.
                        </p>

                        <h3>9. Tempoh Penyimpanan dan Pemadaman Data</h3>
                        <p>
                            Maklumat peribadi hendaklah disimpan
                            hanya selama diperlukan untuk tujuan
                            perniagaan yang sah, keperluan
                            undang-undang yang terpakai serta
                            pengendalian pertanyaan atau pertikaian
                            yang berkaitan. Maklumat yang tidak
                            lagi diperlukan hendaklah dipadam
                            dengan selamat, dinyahkenal pasti
                            atau diuruskan mengikut prosedur
                            penyimpanan data yang diluluskan.
                        </p>
                        <p>
                            Perniagaan hendaklah menetapkan dan
                            memaklumkan tempoh penyimpanan sebenar
                            atau kriteria yang digunakan untuk
                            menentukannya sebelum notis ini
                            dimuktamadkan.
                        </p>

                        <h3>10. Hak dan Permohonan Pelanggan</h3>
                        <p>
                            Tertakluk pada undang-undang Malaysia
                            yang berkenaan, pelanggan mungkin mempunyai
                            hak untuk meminta akses kepada data
                            peribadi, membetulkan data yang tidak
                            tepat, menarik balik persetujuan apabila
                            dibenarkan dan menggunakan hak berkanun
                            lain yang terpakai. Permohonan boleh
                            dikemukakan melalui saluran hubungan
                            privasi rasmi perniagaan.
                        </p>
                        <p>
                            NS &amp; Beauty mungkin perlu mengesahkan
                            identiti pemohon sebelum memberikan
                            maklum balas. Sesetengah maklumat
                            mungkin perlu disimpan apabila
                            dikehendaki atau dibenarkan oleh
                            undang-undang. Pelanggan juga boleh
                            mengemukakan aduan kepada pihak berkuasa
                            perlindungan data Malaysia yang berkaitan
                            apabila berkenaan.
                        </p>

                        <h3>11. Pemindahan Data Antarabangsa</h3>
                        <p>
                            Pengehosan laman web, penghantaran e-mel,
                            sokongan teknikal atau infrastruktur
                            berkaitan mungkin melibatkan pemprosesan
                            atau akses dari luar Malaysia, bergantung
                            pada penyedia dan wilayah yang digunakan.
                            Pemindahan sedemikian hendaklah mematuhi
                            keperluan undang-undang perlindungan
                            data peribadi Malaysia yang terpakai.
                        </p>
                        <p>
                            NS &amp; Beauty hendaklah mengesahkan
                            lokasi penyimpanan atau akses maklumat
                            peribadi serta langkah perlindungan
                            yang digunakan sebelum membuat
                            kenyataan muktamad tentang pemindahan
                            data antarabangsa.
                        </p>

                        <h3>12. Maklumat Hubungan dan Kemas Kini Notis</h3>
                        <p>
                            Pelanggan boleh menghubungi NS &amp;
                            Beauty melalui saluran rasmi yang
                            diterbitkan untuk pertanyaan privasi,
                            permohonan akses atau pembetulan,
                            dan kebimbangan mengenai penggunaan
                            maklumat peribadi.
                        </p>
                        <p>
                            Notis ini boleh disemak semula apabila
                            terdapat perubahan amalan pemprosesan,
                            penyedia perkhidmatan, keperluan
                            undang-undang atau operasi. Notis
                            terkini yang diluluskan hendaklah
                            menyatakan tarikh kuat kuasa dan
                            tersedia kepada pelanggan.
                        </p>

                        <p class="policy-disclaimer">
                            <strong>Nota pelaksanaan:</strong>
                            Ini merupakan draf notis. Pihak
                            perniagaan perlu mengesahkan amalan
                            pemprosesan sebenar, maklumat hubungan
                            privasi rasmi, penerima data, aturan
                            penyimpanan data dan pemindahan
                            antarabangsa sebelum menggunakan
                            notis ini sebagai notis privasi akhir.
                        </p>

                    </div>
                </div>
            </details>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: MANDATORY AGREEMENT CHECKBOX
            |--------------------------------------------------------------------------
            | This is not prechecked and does not authorize optional
            | promotional marketing.
            |--------------------------------------------------------------------------
            --}}

            <label for="terms_accepted" class="agreement-label">

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
                        Saya telah membaca dan bersetuju dengan
                        <strong>Terma dan Syarat</strong>
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
                This agreement applies to registration and necessary
                service communications, not optional promotional marketing.

                Persetujuan ini berkaitan dengan pendaftaran dan
                komunikasi perkhidmatan yang perlu, bukan pemasaran
                promosi pilihan.
            </p>

        </section>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: SUBMIT REGISTRATION
        |--------------------------------------------------------------------------
        | Retains existing register.store route and email OTP workflow.
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

    <div class="footer">
        Already have an account?
        <a href="{{ route('login') }}">Sign In</a>
    </div>

    <a href="{{ route('home') }}" class="back">
        ← Back to Home
    </a>

</main>

<script>
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: PASSWORD VISIBILITY TOGGLES
    |--------------------------------------------------------------------------
    | Independent control for Password and Confirm Password.
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(".password-toggle").forEach(function (button) {

        button.addEventListener("click", function () {

            const inputId = button.getAttribute("data-password-target");
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
                ? (isConfirmation
                    ? "Hide confirm password"
                    : "Hide password")
                : (isConfirmation
                    ? "Show confirm password"
                    : "Show password");

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
    | NS BEAUTY COMMENT: INDEPENDENT POLICY LANGUAGE SWITCHES
    |--------------------------------------------------------------------------
    |
    | Terms and Privacy use independent selectors.
    | Switching language does not submit the registration form.
    | Both language versions remain present in the page.
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
    | NS BEAUTY COMMENT: MANDATORY TERMS AGREEMENT
    |--------------------------------------------------------------------------
    | The button is disabled until the checkbox is checked.
    | Server-side validation must also enforce required|accepted.
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

        // Handle browser back/forward form restoration.
        window.addEventListener(
            "pageshow",
            updateAgreementState
        );
    }

    if (registrationForm && agreementCheckbox) {

        registrationForm.addEventListener("submit", function (event) {

            if (!agreementCheckbox.checked) {

                event.preventDefault();

                agreementCheckbox.reportValidity();
                agreementCheckbox.focus();
            }
        });
    }
</script>

</body>
</html>