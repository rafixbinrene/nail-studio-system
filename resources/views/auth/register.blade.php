
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
        | NS BEAUTY COMMENT: Registration Page Theme
        |--------------------------------------------------------------------------
        | Frontend-only changes
        |
        | - Matches the Login Page design
        | - Uniform corner radius
        | - Muted placeholder text
        | - Password visibility controls
        | - Existing Laravel registration functionality remains unchanged
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
            --glass: rgba(255, 255, 255, .54);
            --border: rgba(255, 255, 255, .70);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Uniform Radius
            |--------------------------------------------------------------------------
            | Uses the same radius values as the updated Login Page
            |--------------------------------------------------------------------------
            */

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
                    rgba(255, 255, 255, .78),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 88% 12%,
                    rgba(234, 216, 196, .88),
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
        | NS BEAUTY COMMENT: Registration Card
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
                0 20px 50px rgba(47, 33, 24, .12),
                inset 0 1px 0 rgba(255, 255, 255, .7);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Business Branding
        |--------------------------------------------------------------------------
        */

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;

            text-align: center;
            font-size: 15px;
            font-weight: 900;
            color: var(--brown-dark);
            margin-bottom: 20px;
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
                linear-gradient(135deg, #8a7158, #5d3f2c);
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
        | NS BEAUTY COMMENT: Input Fields
        |--------------------------------------------------------------------------
        | User-entered text remains dark
        | Placeholder examples remain faded
        |--------------------------------------------------------------------------
        */

        .form-input {
            width: 100%;
            min-height: 50px;
            padding: 14px 16px;

            border-radius: var(--radius-input);
            border: 1px solid rgba(111, 78, 55, .16);

            background: rgba(255, 255, 255, .68);
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
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 0 0 3px rgba(111, 78, 55, .10);
        }

        .form-input.is-invalid {
            border-color: rgba(150, 45, 35, .55);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Password Visibility
        |--------------------------------------------------------------------------
        | Both password fields have independent visibility controls
        | No changes to password values or backend registration
        |--------------------------------------------------------------------------
        */

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper .form-input {
            padding-right: 54px;
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

            transition:
                background .2s ease,
                color .2s ease;
        }

        .password-toggle:hover {
            background: rgba(111, 78, 55, .08);
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
        | NS BEAUTY COMMENT: Validation and Session Alerts
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
            background: rgba(150, 45, 35, .10);
            color: #7a241d;
            border: 1px solid rgba(150, 45, 35, .18);
        }

        .alert-success {
            background: rgba(50, 130, 85, .10);
            color: #285c3f;
            border: 1px solid rgba(50, 130, 85, .18);
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

        .note {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 22px;

            background: rgba(255, 255, 255, .44);
            border: 1px solid rgba(255, 255, 255, .70);
            border-radius: var(--radius-input);

            padding: 14px 16px;
        }

        .note strong {
            color: var(--brown);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Registration Button
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
                linear-gradient(135deg, #8a7158, #5d3f2c);

            cursor: pointer;

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(47, 33, 24, .15);
        }

        .submit-btn:focus-visible {
            outline: 3px solid rgba(111, 78, 55, .4);
            outline-offset: 3px;
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Footer Navigation
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

            .subtitle {
                margin-bottom: 24px;
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
        | NS BEAUTY COMMENT: Business Branding
        |--------------------------------------------------------------------------
        --}}

        <div class="logo">
            <span class="logo-mark" aria-hidden="true">NS</span>
            <span>Nail Studio &amp; Beauty</span>
        </div>

        <h1>Create Account</h1>

        <p class="subtitle">
            Join our beauty community today
        </p>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Existing Session Messages
        |--------------------------------------------------------------------------
        --}}

        @if (session('status'))
            <div class="alert alert-success" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                Please check your details and try again
            </div>
        @endif

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Existing Registration Form
        |--------------------------------------------------------------------------
        | IMPORTANT: Backend integration remains unchanged
        |
        | POST route('register.store')
        | CSRF protection
        | Original form field names
        | Existing server-side validation
        | Existing Email OTP registration workflow
        |--------------------------------------------------------------------------
        --}}

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <!-- NS BEAUTY COMMENT: Customer Full Name -->

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

            <!-- NS BEAUTY COMMENT: Customer Phone Number -->

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

            <!-- NS BEAUTY COMMENT: Customer Address -->

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

            <!-- NS BEAUTY COMMENT: Customer Email Address -->

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
            | NS BEAUTY COMMENT: Email OTP Notice
            |--------------------------------------------------------------------------
            | Text only — existing OTP handling remains unchanged
            |--------------------------------------------------------------------------
            --}}

            <div class="note">
                <strong>Email verification</strong>
                <span>
                    — A one-time verification code will be sent
                    to your email address after account creation
                </span>
            </div>

            <!-- NS BEAUTY COMMENT: Customer Password -->

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
                            style="display:none;"
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

            <!-- NS BEAUTY COMMENT: Confirm Customer Password -->

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
                            style="display:none;"
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
            | NS BEAUTY COMMENT: Registration Submission
            |--------------------------------------------------------------------------
            | The button submits the same fields to the same Laravel route
            | Account creation and Email OTP remain backend-controlled
            |--------------------------------------------------------------------------
            --}}

            <button type="submit" class="submit-btn">
                Create Account &amp; Send Email OTP
            </button>

        </form>

        {{--
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Account and Home Navigation
        |--------------------------------------------------------------------------
        --}}

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
        | NS BEAUTY COMMENT: Independent Password Visibility Toggles
        |--------------------------------------------------------------------------
        | Frontend-only JavaScript
        |
        | Each eye button controls only its associated password field
        | Existing submitted field names and values remain unchanged
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

                const isHidden = input.type === "password";

                input.type = isHidden ? "text" : "password";

                if (eyeIcon) {
                    eyeIcon.style.display = isHidden ? "none" : "block";
                }

                if (eyeOffIcon) {
                    eyeOffIcon.style.display = isHidden ? "block" : "none";
                }

                const isConfirmation = inputId === "password_confirmation";

                const label = isHidden
                    ? (isConfirmation ? "Hide confirm password" : "Hide password")
                    : (isConfirmation ? "Show confirm password" : "Show password");

                button.setAttribute("aria-label", label);
                button.setAttribute("aria-pressed", isHidden ? "true" : "false");
                button.title = label;
            });

        });
    </script>

</body>
</html>
