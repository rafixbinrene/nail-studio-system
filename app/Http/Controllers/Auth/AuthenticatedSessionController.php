<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Login Controller
|--------------------------------------------------------------------------
| Purpose:
| - Shows the login page.
| - Validates email and password.
| - Sends Email OTP after correct password.
| - Prevents blocked or inactive users from logging in.
| - Applies login attempt rate limiting.
|
| Defense explanation:
| The system uses two-step login authentication. The user must first enter
| the correct email and password, then verify the Email OTP before accessing
| the dashboard. This improves account security because password access alone
| is not enough to enter the system.
|--------------------------------------------------------------------------
*/

class AuthenticatedSessionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Show Login Page
    |--------------------------------------------------------------------------
    | Purpose:
    | - Displays the login form.
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Process Login Request
    |--------------------------------------------------------------------------
    | Flow:
    | 1. Validate email and password.
    | 2. Check login rate limit.
    | 3. Find user account.
    | 4. Verify password.
    | 5. Check if account is blocked or inactive.
    | 6. If customer email is not verified, send registration OTP.
    | 7. Otherwise, send login OTP.
    | 8. Redirect to OTP verification page.
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, OtpService $otpService)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable'],
        ]);

        $this->ensureIsNotRateLimited($request);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Find User by Email
        |--------------------------------------------------------------------------
        */
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey($request), 300);

            throw ValidationException::withMessages([
                'email' => 'No account found with this email address.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Password Verification
        |--------------------------------------------------------------------------
        | Purpose:
        | - Login requires correct email and password before OTP is sent.
        |--------------------------------------------------------------------------
        */
        if (!Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($this->throttleKey($request), 300);

            throw ValidationException::withMessages([
                'email' => 'The email or password is incorrect.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Blocked / Inactive Account Protection
        |--------------------------------------------------------------------------
        */
        if (isset($user->status) && in_array(strtolower($user->status), ['blocked', 'inactive'], true)) {
            throw ValidationException::withMessages([
                'email' => 'Your account is currently not allowed to access the system.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | OTP Purpose Decision
        |--------------------------------------------------------------------------
        | Purpose:
        | - If customer has not verified email yet, force registration OTP.
        | - Otherwise, send normal login OTP.
        |--------------------------------------------------------------------------
        */
        $otpPurpose = 'login_password';

        if ($user->role === 'customer' && !$user->email_verified_at) {
            $otpPurpose = 'registration';
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Send Email OTP
        |--------------------------------------------------------------------------
        */
        $otpSent = $otpService->generateAndSend($user, $otpPurpose);

        if (!$otpSent) {
            throw ValidationException::withMessages([
                'email' => 'Email OTP could not be sent. Please check mail configuration.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Save OTP Session
        |--------------------------------------------------------------------------
        | Purpose:
        | - Stores temporary OTP data while waiting for user verification.
        |--------------------------------------------------------------------------
        */
        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_purpose', $otpPurpose);
        $request->session()->put('login_remember', $request->boolean('remember'));

        return redirect()
            ->route('otp.verification')
            ->with('status', 'OTP sent to your email. Please verify to continue.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Logout
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Login Rate Limit Check
    |--------------------------------------------------------------------------
    | Purpose:
    | - Allows only 5 failed login attempts.
    | - Locks login for 5 minutes after too many failed attempts.
    |--------------------------------------------------------------------------
    */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Rate Limit Key
    |--------------------------------------------------------------------------
    | Purpose:
    | - Uses email + IP address to track failed login attempts.
    |--------------------------------------------------------------------------
    */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}