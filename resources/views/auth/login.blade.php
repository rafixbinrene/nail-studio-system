
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | NS &amp; Beauty</title>

    {{--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Universal Browser Favicon
    |--------------------------------------------------------------------------
    | Same logo used on Welcome, Login, Register and Dashboard.
    | File location: public/images/nsbeauty-logo.png
    |--------------------------------------------------------------------------
    --}}

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('images/nsbeauty-logo.png') }}"
    >

    <link
        rel="apple-touch-icon"
        href="{{ asset('images/nsbeauty-logo.png') }}"
    >

    <style>
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: LOGIN PAGE THEME
        |--------------------------------------------------------------------------
        |
        | Updates:
        | - Use the official circular NS & Beauty logo.
        | - Display NS & Beauty as the business name.
        | - Keep the original Muji brown and cream theme.
        | - Preserve the single password visibility toggle.
        | - Preserve the responsive login card.
        |
        | No backend authentication changes are made.
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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Page Background
        |--------------------------------------------------------------------------
        */

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
        | NS BEAUTY COMMENT: Main Login Card
        |--------------------------------------------------------------------------
        */

        .card {
            width: 100%;
            max-width: 500px;

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
        | NS BEAUTY COMMENT: Universal NS & Beauty Branding
        |--------------------------------------------------------------------------
        |
        | Replaces the previous square NS text badge.
        | The logo is displayed as a perfect circle.
        |--------------------------------------------------------------------------
        */

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;

            gap: 11px;
            margin-bottom: 20px;

            text-align: center;
            font-size: 15px;
            font-weight: 900;

            color: var(--brown-dark);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Circular Logo Container
        |--------------------------------------------------------------------------
        */

        .logo-mark {
            width: 42px;
            height: 42px;

            min-width: 42px;
            min-height: 42px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            border-radius: 50%;
            overflow: hidden;

            background: #f8f6f2;

            border: 1px solid rgba(111,78,55,.14);

            box-shadow:
                0 5px 12px rgba(47,33,24,.08);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Circular Logo Image
        |--------------------------------------------------------------------------
        | object-fit preserves the logo appearance inside the circle.
        |--------------------------------------------------------------------------
        */

        .logo-mark img {
            display: block;

            width: 100%;
            height: 100%;

            object-fit: cover;

            border-radius: 50%;
        }

        .logo-name {
            color: var(--brown-dark);
            font-size: 15px;
            font-weight: 900;
            line-height: 1.3;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Page Heading
        |--------------------------------------------------------------------------
        */

        h1 {
            text-align: center;
            margin-bottom: 12px;

            font-size: clamp(28px,4vw,34px);
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
        | NS BEAUTY COMMENT: Form Layout
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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Input Appearance
        |--------------------------------------------------------------------------
        */

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

            box-shadow:
                0 0 0 3px rgba(111,78,55,.10);
        }

        .form-input.is-invalid {
            border-color: rgba(150,45,35,.55);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Fix Duplicate Password Eye Icon
        |--------------------------------------------------------------------------
        |
        | Microsoft Edge may display its native eye icon.
        | Hide that icon and retain the existing custom toggle.
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
        | NS BEAUTY COMMENT: Password Visibility Wrapper
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

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Single Password Toggle Button
        |--------------------------------------------------------------------------
        */

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
        | NS BEAUTY COMMENT: Show Only One Eye Icon
        |--------------------------------------------------------------------------
        */

        .password-toggle svg[hidden] {
            display: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Validation Messages
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
        | NS BEAUTY COMMENT: Email OTP Information
        |--------------------------------------------------------------------------
        */

        .security-box {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.7;

            margin-bottom: 22px;

            background: rgba(255,255,255,.44);

            border: 1px solid rgba(255,255,255,.70);

            border-radius: var(--radius-input);

            padding: 14px 16px;
        }

        .security-box strong {
            color: var(--brown);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Remember Me and Forgot Password
        |--------------------------------------------------------------------------
        */

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 14px;
            margin-bottom: 24px;
        }

        .remember {
            display: inline-flex;
            align-items: center;

            gap: 9px;

            color: var(--muted);

            font-size: 13px;
            font-weight: 600;

            cursor: pointer;
        }

        .remember input {
            width: 16px;
            height: 16px;

            margin: 0;

            accent-color: var(--brown);
            cursor: pointer;
        }

        .forgot {
            color: var(--brown);

            text-decoration: none;

            font-size: 13px;
            font-weight: 800;

            white-space: nowrap;
        }

        .forgot:hover,
        .footer a:hover,
        .back:hover {
            text-decoration: underline;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Login Submit Button
        |--------------------------------------------------------------------------
        */

        .submit-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;

            gap: 9px;

            width: 100%;
            min-height: 50px;

            padding: 14px 20px;

            border: none;
            border-radius: var(--radius-button);

            color: white;

            font-size: 14px;
            font-weight: 850;

            background:
                linear-gradient(
                    135deg,
                    #8a7158,
                    #5d3f2c
                );

            cursor: pointer;

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);

            box-shadow:
                0 10px 24px rgba(47,33,24,.15);
        }

        .submit-btn:focus-visible {
            outline: 3px solid rgba(111,78,55,.4);
            outline-offset: 3px;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Footer Links
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

        .back {
            display: block;

            text-align: center;

            margin-top: 20px;

            color: var(--muted);
            text-decoration: none;

            font-size: 13px;
            font-weight: 700;
        }

        .security-note {
            margin-top: 22px;
            padding-top: 18px;

            border-top: 1px solid rgba(111,78,55,.10);

            text-align: center;

            color: var(--muted);

            font-size: 12px;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Responsive Design
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

            .login-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .subtitle {
                margin-bottom: 24px;
            }

            .logo-mark {
                width: 38px;
                height: 38px;

                min-width: 38px;
                min-height: 38px;
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
        | NS BEAUTY COMMENT: Universal Round Business Logo
        |--------------------------------------------------------------------------
        |
        | Uses the same image as the Welcome page, shared layout,
        | and Admin / Staff / Customer sidebar.
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

            <span class="logo-name">
                NS &amp; Beauty
            </span>

        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Login Header
        |--------------------------------------------------------------------------
        --}}

        <h1>Welcome Back</h1>

        <p class="subtitle">
            Sign in to continue to your NS &amp; Beauty account
        </p>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Existing Session Messages
        |--------------------------------------------------------------------------
        --}}

        @if (session('status'))

            <div
                class="alert alert-success"
                role="status"
            >
                {{ session('status') }}
            </div>

        @endif

        @if ($errors->any())

            <div
                class="alert alert-error"
                role="alert"
            >
                Please check your details and try again
            </div>

        @endif

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Existing Login Form
        |--------------------------------------------------------------------------
        |
        | Preserved:
        | - POST login.submit
        | - CSRF token
        | - Email and password field names
        | - Email OTP authentication
        | - Remember Me
        | - Forgot Password
        |--------------------------------------------------------------------------
        --}}

        <form
            method="POST"
            action="{{ route('login.submit') }}"
        >
            @csrf

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Email Address
            |--------------------------------------------------------------------------
            --}}

            <div class="form-group">

                <label
                    class="form-label"
                    for="email"
                >
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
                    autofocus
                    @error('email') aria-invalid="true" @enderror
                >

                @error('email')

                    <div
                        class="error-text"
                        role="alert"
                    >
                        {{ $message }}
                    </div>

                @enderror

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Password Input
            |--------------------------------------------------------------------------
            --}}

            <div class="form-group">

                <label
                    class="form-label"
                    for="password"
                >
                    Password
                </label>

                <div class="password-wrapper">

                    <input
                        class="form-input @error('password') is-invalid @enderror"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                        @error('password') aria-invalid="true" @enderror
                    >

                    {{--
                    |--------------------------------------------------------------------------
                    | NS BEAUTY COMMENT: Single Password Eye Toggle
                    |--------------------------------------------------------------------------
                    |
                    | Native Edge reveal control is hidden with CSS.
                    | Only the NS Beauty custom icon appears.
                    |--------------------------------------------------------------------------
                    --}}

                    <button
                        type="button"
                        class="password-toggle"
                        id="passwordToggle"
                        onclick="togglePasswordVisibility()"
                        aria-label="Show password"
                        aria-pressed="false"
                        aria-controls="password"
                        title="Show password"
                    >

                        {{--
                        | NS BEAUTY COMMENT: Show Password Eye Icon
                        --}}

                        <svg
                            id="eyeIcon"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                            />

                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                            />
                        </svg>

                        {{--
                        | NS BEAUTY COMMENT: Hide Password Eye Icon
                        --}}

                        <svg
                            id="eyeOffIcon"
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

                            <path
                                d="M9.9 9.9a3 3 0 0 0 4.2 4.2"
                            />

                            <path
                                d="M3 3l18 18"
                            />
                        </svg>

                    </button>

                </div>

                @error('password')

                    <div
                        class="error-text"
                        role="alert"
                    >
                        {{ $message }}
                    </div>

                @enderror

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Email OTP Information
            |--------------------------------------------------------------------------
            | Existing authentication flow remains unchanged.
            |--------------------------------------------------------------------------
            --}}

            <div class="security-box">

                <strong>
                    Email verification
                </strong>

                <span>
                    — After entering the correct email and password,
                    we'll send a one-time verification code
                    to your registered email address
                </span>

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Remember Me and Forgot Password
            |--------------------------------------------------------------------------
            --}}

            <div class="login-options">

                <label class="remember">

                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        {{ old('remember') ? 'checked' : '' }}
                    >

                    Remember me

                </label>

                <a
                    href="{{ route('password.request') }}"
                    class="forgot"
                >
                    Forgot Password?
                </a>

            </div>

            {{--
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Login Submission
            |--------------------------------------------------------------------------
            | Sends the existing form to login.submit.
            |--------------------------------------------------------------------------
            --}}

            <button
                type="submit"
                class="submit-btn"
            >
                Send Email OTP
            </button>

        </form>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Registration Link
        |--------------------------------------------------------------------------
        --}}

        <div class="footer">

            Don't have an account?

            <a href="{{ route('register') }}">
                Create Account
            </a>

        </div>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Back to Welcome Page
        |--------------------------------------------------------------------------
        --}}

        <a
            href="{{ route('home') }}"
            class="back"
        >
            ← Back to Home
        </a>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Security Note
        |--------------------------------------------------------------------------
        --}}

        <div class="security-note">
            Email OTP verification is required before accessing your account
        </div>

    </main>

    <script>
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Password Visibility Toggle
        |--------------------------------------------------------------------------
        |
        | Frontend only.
        |
        | Preserved:
        | - Hidden password by default
        | - Click to show password
        | - Click again to hide password
        | - Only one SVG displayed at a time
        | - Email OTP authentication remains unchanged
        |--------------------------------------------------------------------------
        */

        function togglePasswordVisibility() {

            const passwordInput = document.getElementById(
                "password"
            );

            const toggleButton = document.getElementById(
                "passwordToggle"
            );

            const eyeIcon = document.getElementById(
                "eyeIcon"
            );

            const eyeOffIcon = document.getElementById(
                "eyeOffIcon"
            );

            if (
                !passwordInput ||
                !toggleButton ||
                !eyeIcon ||
                !eyeOffIcon
            ) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Toggle Password Input Type
            |--------------------------------------------------------------------------
            */

            const isHidden = passwordInput.type === "password";

            passwordInput.type = isHidden
                ? "text"
                : "password";

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Display Exactly One Eye Icon
            |--------------------------------------------------------------------------
            */

            eyeIcon.hidden = isHidden;

            eyeOffIcon.hidden = !isHidden;

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Accessibility States
            |--------------------------------------------------------------------------
            */

            toggleButton.setAttribute(
                "aria-label",
                isHidden ? "Hide password" : "Show password"
            );

            toggleButton.setAttribute(
                "aria-pressed",
                isHidden ? "true" : "false"
            );

            toggleButton.title = isHidden
                ? "Hide password"
                : "Show password";

        }
    </script>

</body>
</html>
