
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Account | Nail Studio &amp; Beauty</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: REGISTRATION PAGE
        |--------------------------------------------------------------------------
        | Nail Studio & Beauty
        |
        | Changes:
        | 1. Fix duplicated password eye icons.
        | 2. Add Terms and Conditions.
        | 3. Add Privacy Notice.
        | 4. Require agreement before requesting registration OTP.
        |
        | Preserved:
        | - Existing register.store route
        | - CSRF protection
        | - Name, phone, address and email fields
        | - Password and password confirmation
        | - Email OTP registration
        | - Existing validation error handling
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
            --radius-small: 12px;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;

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

            color: var(--brown-dark);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: MAIN REGISTRATION CARD
        |--------------------------------------------------------------------------
        */

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
        | NS BEAUTY COMMENT: BRANDING
        |--------------------------------------------------------------------------
        */

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;

            margin-bottom: 20px;

            text-align: center;
            font-size: 15px;
            font-weight: 900;
            color: var(--brown-dark);
        }

        .logo-mark {
            width: 38px;
            height: 38px;

            border-radius: var(--radius-small);

            display: grid;
            place-items: center;

            color: white;
            font-size: 13px;
            font-weight: 900;

            background:
                linear-gradient(135deg,#8a7158,#5d3f2c);
        }

        h1 {
            text-align: center;
            margin-bottom: 12px;

            font-size: clamp(28px, 4vw, 34px);
            letter-spacing: -.7px;
            line-height: 1.2;

            background:
                linear-gradient(
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
            text-align: center;
            color: var(--muted);
            margin-bottom: 28px;
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
            font-weight: 400;
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
        | NS BEAUTY COMMENT: PASSWORD VISIBILITY FIX
        |--------------------------------------------------------------------------
        |
        | Microsoft Edge can display its native password eye
        | alongside the custom NS Beauty visibility button.
        |
        | Hide the browser-native password reveal icon.
        |--------------------------------------------------------------------------
        */

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: CUSTOM PASSWORD TOGGLES
        |--------------------------------------------------------------------------
        |
        | Each password field has one independent toggle.
        |--------------------------------------------------------------------------
        */

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
            border-radius: var(--radius-small);

            background: transparent;
            color: var(--muted);

            cursor: pointer;
            z-index: 2;

            transition:
                background .2s ease,
                color .2s ease;
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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: HIDE INACTIVE CUSTOM SVG
        |--------------------------------------------------------------------------
        */

        .password-toggle svg[hidden] {
            display: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: VALIDATION MESSAGES
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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: EMAIL OTP INFORMATION
        |--------------------------------------------------------------------------
        */

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
        | NS BEAUTY COMMENT: TERMS AND PRIVACY AGREEMENT
        |--------------------------------------------------------------------------
        |
        | The agreement is shown before the registration button.
        | The checkbox starts unchecked.
        |
        | NOTE:
        | The text below is a preliminary policy summary.
        | Complete, approved English and Bahasa Malaysia legal
        | notices should be published before public deployment.
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
            color: var(--brown-dark);
            font-size: 14px;
            font-weight: 850;
            margin-bottom: 8px;
        }

        .agreement-description {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.7;
            margin-bottom: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: EXPANDABLE POLICY SECTIONS
        |--------------------------------------------------------------------------
        */

        .policy-details {
            margin-bottom: 10px;
            border: 1px solid rgba(111,78,55,.14);
            border-radius: 14px;
            background: rgba(255,255,255,.58);
            overflow: hidden;
        }

        .policy-details summary {
            padding: 13px 14px;
            color: var(--brown);
            font-size: 12px;
            font-weight: 850;
            cursor: pointer;
        }

        .policy-details summary:hover {
            background: rgba(111,78,55,.06);
        }

        .policy-content {
            padding: 4px 15px 15px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.8;
        }

        .policy-content p {
            margin-bottom: 11px;
        }

        .policy-content p:last-child {
            margin-bottom: 0;
        }

        .policy-content strong {
            color: var(--brown-dark);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: MANDATORY CHECKBOX
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
        | NS BEAUTY COMMENT: REGISTRATION BUTTON
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
            border-radius: var(--radius-button);

            color: white;
            font-size: 14px;
            font-weight: 850;

            background:
                linear-gradient(135deg,#8a7158,#5d3f2c);

            cursor: pointer;

            transition:
                transform .2s ease,
                box-shadow .2s ease,
                opacity .2s ease;
        }

        .submit-btn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(47,33,24,.15);
        }

        .submit-btn:focus-visible {
            outline: 3px solid rgba(111,78,55,.4);
            outline-offset: 3px;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: DISABLED REGISTRATION BUTTON
        |--------------------------------------------------------------------------
        */

        .submit-btn:disabled {
            opacity: .48;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: FOOTER LINKS
        |--------------------------------------------------------------------------
        */

        .footer {
            margin-top: 26px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .footer a {
            color: var(--brown);
            text-decoration: none;
            font-weight: 850;
        }

        .footer a:hover,
        .back:hover {
            text-decoration: underline;
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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: RESPONSIVE DESIGN
        |--------------------------------------------------------------------------
        */

        @media (max-width: 520px) {
            body {
                padding: 16px;
            }

            .card {
                padding: 30px 24px;
                border-radius: 20px;
            }

            .subtitle {
                margin-bottom: 24px;
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

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: BUSINESS BRANDING
        |--------------------------------------------------------------------------
        --}}

        <div class="logo">
            <span class="logo-mark" aria-hidden="true">
                NS
            </span>

            <span>
                Nail Studio &amp; Beauty
            </span>
        </div>

        <h1>Create Account</h1>

        <p class="subtitle">
            Join our beauty community today
        </p>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: SESSION MESSAGES
        |--------------------------------------------------------------------------
        --}}

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
        | NS BEAUTY COMMENT: REGISTRATION FORM
        |--------------------------------------------------------------------------
        |
        | Preserved:
        | - POST register.store
        | - CSRF
        | - Existing registration fields
        | - Existing email OTP workflow
        |--------------------------------------------------------------------------
        --}}

        <form
            id="registrationForm"
            method="POST"
            action="{{ route('register.store') }}"
        >
            @csrf

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: FULL NAME
            |--------------------------------------------------------------------------
            --}}

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

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: PHONE NUMBER
            |--------------------------------------------------------------------------
            --}}

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

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: ADDRESS
            |--------------------------------------------------------------------------
            --}}

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

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: EMAIL ADDRESS
            |--------------------------------------------------------------------------
            --}}

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

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: EMAIL OTP INFORMATION
            |--------------------------------------------------------------------------
            --}}

            <div class="note">
                <strong>Email verification</strong>

                <span>
                    — A one-time verification code will be sent
                    to your email address during registration.
                </span>
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

                    {{--
                    | NS BEAUTY COMMENT: Password Visibility Button
                    | Exactly one custom icon is displayed at a time.
                    --}}

                    <button
                        type="button"
                        class="password-toggle"
                        data-password-target="password"
                        aria-label="Show password"
                        aria-pressed="false"
                        aria-controls="password"
                        title="Show password"
                    >

                        <svg
                            class="eye-icon"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                            />
                            <circle cx="12" cy="12" r="3" />
                        </svg>

                        <svg
                            class="eye-off-icon"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                            hidden
                        >
                            <path
                                d="M10.6 5.2A10.5 10.5 0 0 1 12 5c6.5 0 10 7 10 7a15.5 15.5 0 0 1-3.2 4.1"
                            />
                            <path
                                d="M6.1 6.1A15 15 0 0 0 2 12s3.5 7 10 7a10 10 0 0 0 5.3-1.5"
                            />
                            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                            <path d="M3 3l18 18" />
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

                <label
                    class="form-label"
                    for="password_confirmation"
                >
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
                        title="Show confirm password"
                    >

                        <svg
                            class="eye-icon"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                            />
                            <circle cx="12" cy="12" r="3" />
                        </svg>

                        <svg
                            class="eye-off-icon"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                            hidden
                        >
                            <path
                                d="M10.6 5.2A10.5 10.5 0 0 1 12 5c6.5 0 10 7 10 7a15.5 15.5 0 0 1-3.2 4.1"
                            />
                            <path
                                d="M6.1 6.1A15 15 0 0 0 2 12s3.5 7 10 7a10 10 0 0 0 5.3-1.5"
                            />
                            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                            <path d="M3 3l18 18" />
                        </svg>

                    </button>

                </div>
            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: TERMS AND PRIVACY ACKNOWLEDGMENT
            |--------------------------------------------------------------------------
            |
            | The following sections are preliminary summaries.
            |
            | Before public deployment:
            | - Approve and publish complete Terms and Conditions.
            | - Approve Privacy Notices in English and Bahasa Malaysia.
            | - Verify the actual practices, retention periods,
            |   contact details and service providers.
            |
            | A frontend checkbox alone does not prove compliance.
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
                    Please review the following information
                    before creating your Nail Studio &amp; Beauty account.
                </p>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: TERMS AND CONDITIONS SUMMARY
                |--------------------------------------------------------------------------
                --}}

                <details class="policy-details">

                    <summary>
                        Terms and Conditions
                    </summary>

                    <div class="policy-content">

                        <p>
                            <strong>1. Account Registration</strong>
                            Customers must provide accurate registration
                            information and maintain the confidentiality
                            of their account credentials.
                        </p>

                        <p>
                            <strong>2. Account Security</strong>
                            Email verification may be required before
                            account access. Customers should not
                            share their passwords or verification codes.
                        </p>

                        <p>
                            <strong>3. Appointment Booking</strong>
                            Bookings are subject to available schedules,
                            service durations, staff availability,
                            and the booking policies enforced
                            by the system.
                        </p>

                        <p>
                            <strong>4. Cancellations and Rescheduling</strong>
                            Appointment changes are subject to the
                            policies displayed during booking and
                            the actions available in the customer's
                            account.
                        </p>

                        <p>
                            <strong>5. Payments</strong>
                            Payments are handled directly by the
                            business according to its current
                            payment arrangements. Online payment
                            processing is not provided through
                            this registration form.
                        </p>

                        <p>
                            <strong>6. Responsible Use</strong>
                            Customers must not misuse the platform,
                            attempt unauthorized access, or submit
                            fraudulent bookings.
                        </p>

                        <p>
                            <strong>7. Service Updates</strong>
                            Appointment availability may change
                            due to staff absence, emergency closures,
                            or other operational circumstances.
                        </p>

                        <p>
                            <strong>8. Contact and Questions</strong>
                            Customers may contact Nail Studio &amp; Beauty
                            through its published official business
                            contact channels for questions concerning
                            bookings or accounts.
                        </p>

                    </div>

                </details>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: PRIVACY NOTICE SUMMARY
                |--------------------------------------------------------------------------
                |
                | This describes the general processing purposes.
                | It is not a substitute for a complete PDPA notice.
                |--------------------------------------------------------------------------
                --}}

                <details class="policy-details">

                    <summary>
                        Privacy Notice / Notis Privasi
                    </summary>

                    <div class="policy-content">

                        <p>
                            <strong>1. Personal Information</strong>
                            Nail Studio &amp; Beauty collects the
                            personal information submitted during
                            registration, including your name,
                            phone number, email address, and
                            any address you provide.
                        </p>

                        <p>
                            <strong>2. Purpose of Processing</strong>
                            Information is used to manage accounts,
                            authenticate users, verify email addresses,
                            process appointments, contact customers,
                            and provide booking notifications.
                        </p>

                        <p>
                            <strong>3. Booking Records</strong>
                            Appointment information, selected services,
                            booking status, and related activity may be
                            stored to support business operations.
                        </p>

                        <p>
                            <strong>4. Security</strong>
                            Access controls and other technical
                            safeguards are used to help protect
                            customer information. No online system
                            can guarantee absolute security.
                        </p>

                        <p>
                            <strong>5. Authorized Access</strong>
                            Customer information may be accessed by
                            authorized personnel where necessary
                            to provide services and manage bookings.
                        </p>

                        <p>
                            <strong>6. Service Providers</strong>
                            Hosting and email service providers
                            may process information required to
                            operate the website and deliver
                            verification or notification messages.
                        </p>

                        <p>
                            <strong>7. Data Requests</strong>
                            Customers may contact the business
                            regarding access, correction, and
                            other applicable personal-data rights
                            under Malaysian law.
                        </p>

                        <p>
                            <strong>8. Malaysian Law</strong>
                            Personal data processing is subject
                            to applicable Malaysian legislation,
                            including the Personal Data Protection
                            Act 2010 and relevant amendments,
                            where applicable.
                        </p>

                        <p>
                            <strong>Important:</strong>
                            A complete published Privacy Notice
                            must additionally explain applicable
                            disclosures, retention practices,
                            contact details, and data-subject rights
                            in sufficient detail.
                        </p>

                    </div>

                </details>

                {{--
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: MANDATORY REGISTRATION AGREEMENT
                |--------------------------------------------------------------------------
                |
                | Must be checked before the submit button is enabled.
                | Name submitted to Laravel: terms_accepted
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
                        of Nail Studio &amp; Beauty.
                    </span>

                </label>

                @error('terms_accepted')
                    <div
                        class="error-text"
                        role="alert"
                    >
                        {{ $message }}
                    </div>
                @enderror

                <p class="agreement-footnote">
                    This agreement relates to account registration
                    and necessary service communications.
                    It does not grant consent to receive
                    optional promotional marketing messages.
                </p>

            </section>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: REGISTRATION SUBMISSION
            |--------------------------------------------------------------------------
            |
            | Disabled until the mandatory checkbox is selected.
            | The existing Laravel route and email OTP flow remain.
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

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: FOOTER NAVIGATION
        |--------------------------------------------------------------------------
        --}}

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
        | NS BEAUTY COMMENT: PASSWORD VISIBILITY
        |--------------------------------------------------------------------------
        |
        | Each custom eye button controls one password input.
        | Edge's native eye icon is hidden by CSS.
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll(
            ".password-toggle"
        ).forEach(function (button) {

            button.addEventListener("click", function () {

                const inputId = button.getAttribute(
                    "data-password-target"
                );

                const input = document.getElementById(
                    inputId
                );

                if (!input) {
                    return;
                }

                const eyeIcon = button.querySelector(
                    ".eye-icon"
                );

                const eyeOffIcon = button.querySelector(
                    ".eye-off-icon"
                );

                const wasHidden = input.type === "password";

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Change Password Visibility
                |--------------------------------------------------------------------------
                */

                input.type = wasHidden ? "text" : "password";

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Display One Eye Icon
                |--------------------------------------------------------------------------
                */

                if (eyeIcon) {
                    eyeIcon.hidden = wasHidden;
                }

                if (eyeOffIcon) {
                    eyeOffIcon.hidden = !wasHidden;
                }

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Accessibility Labels
                |--------------------------------------------------------------------------
                */

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

                button.setAttribute(
                    "aria-label",
                    label
                );

                button.setAttribute(
                    "aria-pressed",
                    wasHidden ? "true" : "false"
                );

                button.title = label;

            });

        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: MANDATORY AGREEMENT VALIDATION
        |--------------------------------------------------------------------------
        |
        | Frontend behavior:
        | - Checkbox starts unchecked.
        | - Registration button starts disabled.
        | - Checking enables the button.
        | - Unchecking disables it.
        |
        | Server-side validation is also required.
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

            if (
                !agreementCheckbox ||
                !submitButton
            ) {
                return;
            }

            submitButton.disabled =
                !agreementCheckbox.checked;
        }

        if (
            agreementCheckbox &&
            submitButton
        ) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Unchecked by Default
            |--------------------------------------------------------------------------
            | Never preselect legal acceptance.
            |--------------------------------------------------------------------------
            */

            agreementCheckbox.checked = false;

            updateAgreementState();

            agreementCheckbox.addEventListener(
                "change",
                updateAgreementState
            );

        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Submission Check
        |--------------------------------------------------------------------------
        | Prevent submission if agreement is missing.
        |--------------------------------------------------------------------------
        */

        if (
            registrationForm &&
            agreementCheckbox
        ) {

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
