<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Email OTP Controller
|--------------------------------------------------------------------------
| Purpose:
| - Shows the Email OTP verification page.
| - Verifies mandatory registration OTP.
| - Verifies login OTP after email + password.
| - Sends forgot-password OTP.
| - Allows password reset after OTP verification.
| - Resends OTP when requested.
|
| Defense explanation:
| This controller centralizes all Email OTP workflows. It improves security
| because users must verify an OTP sent to their registered email before
| completing registration, login, or password recovery.
|--------------------------------------------------------------------------
*/

class OtpController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Show OTP Verification Page
    |--------------------------------------------------------------------------
    */
    public function show(Request $request)
    {
        if (!$request->session()->has('otp_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.otp', [
            'purpose' => $request->session()->get('otp_purpose'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Verify OTP
    |--------------------------------------------------------------------------
    | Handles:
    | - Registration OTP
    | - Login OTP after email + password
    | - Forgot Password OTP
    |--------------------------------------------------------------------------
    */
    public function verify(Request $request, OtpService $otpService)
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::find($request->session()->get('otp_user_id'));

        if (!$user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'otp' => 'OTP session expired. Please try again.',
                ]);
        }

        $purpose = $request->session()->get('otp_purpose');

        if (!$purpose) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'otp' => 'OTP purpose was not found. Please try again.',
                ]);
        }

        if (!$otpService->verify($user, $validated['otp'], $purpose)) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP code.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Registration OTP Verification
        |--------------------------------------------------------------------------
        */
        if ($purpose === 'registration') {
            return $this->completeRegistrationOtp($request, $user, $otpService);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Login OTP Verification
        |--------------------------------------------------------------------------
        */
        if ($purpose === 'login_password') {
            return $this->completeLoginOtp($request, $user, $otpService);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Forgot Password OTP Verification
        |--------------------------------------------------------------------------
        */
        if ($purpose === 'forgot_password') {
            return $this->completeForgotPasswordOtp($request, $user);
        }

        return redirect()
            ->route('login')
            ->withErrors([
                'otp' => 'Invalid OTP purpose. Please try again.',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Resend Email OTP
    |--------------------------------------------------------------------------
    */
    public function resend(Request $request, OtpService $otpService)
    {
        $user = User::find($request->session()->get('otp_user_id'));

        if (!$user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'otp' => 'OTP session expired. Please try again.',
                ]);
        }

        $purpose = $request->session()->get('otp_purpose');

        if (!$purpose) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'otp' => 'OTP purpose was not found. Please try again.',
                ]);
        }

        $otpSent = $otpService->generateAndSend($user, $purpose);

        if (!$otpSent) {
            return back()->withErrors([
                'otp' => 'OTP could not be resent. Please check mail configuration.',
            ]);
        }

        return back()->with('status', 'A new OTP has been sent to your email.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Show Forgot Password Page
    |--------------------------------------------------------------------------
    */
    public function forgotForm()
    {
        return view('auth.forgot-password');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Send Forgot Password OTP
    |--------------------------------------------------------------------------
    */
    public function sendForgotOtp(Request $request, OtpService $otpService)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            $customer = Customer::where('email', $validated['email'])->first();

            if ($customer && $customer->user_id) {
                $user = User::find($customer->user_id);
            }
        }

        if (!$user) {
            return back()
                ->withErrors([
                    'email' => 'No account found with that email address.',
                ])
                ->withInput();
        }

        $otpSent = $otpService->generateAndSend($user, 'forgot_password');

        if (!$otpSent) {
            return back()
                ->withErrors([
                    'email' => 'Password recovery OTP could not be sent. Please check mail configuration.',
                ])
                ->withInput();
        }

        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_purpose', 'forgot_password');

        return redirect()
            ->route('otp.verification')
            ->with('status', 'Password recovery OTP has been sent to your email.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Show Reset Password Page
    |--------------------------------------------------------------------------
    */
    public function resetForm(Request $request)
    {
        if (!$request->session()->has('password_reset_user_id')) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'password' => 'Please verify OTP first.',
                ]);
        }

        return view('auth.reset-password');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Reset Password
    |--------------------------------------------------------------------------
    */
    public function resetPassword(Request $request, OtpService $otpService)
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ]);

        $user = User::find($request->session()->get('password_reset_user_id'));

        if (!$user || !$user->otp_verified_at) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'password' => 'Password reset session expired. Please try again.',
                ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $otpService->clearOtp($user);

        $request->session()->forget('password_reset_user_id');

        $this->clearOtpSession($request);

        $this->createAuditLog(
            $user,
            'reset_password_otp',
            'User reset password using Email OTP.',
            $request
        );

        return redirect()
            ->route('login')
            ->with('status', 'Password reset successfully. You may now login.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Complete Registration OTP
    |--------------------------------------------------------------------------
    */
    private function completeRegistrationOtp(Request $request, User $user, OtpService $otpService)
    {
        $user->update([
            'email_verified_at' => now(),
        ]);

        $otpService->clearOtp($user);

        Auth::login($user);

        $request->session()->regenerate();

        $this->clearOtpSession($request);

        $this->createAuditLog(
            $user,
            'verify_registration_otp',
            'Customer verified mandatory registration Email OTP.',
            $request
        );

        return $this->redirectByRole($user)
            ->with('success', 'Account verified successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Complete Login OTP
    |--------------------------------------------------------------------------
    | Important fix:
    | - Laravel session does not have session()->boolean().
    | - We use session()->get('login_remember', false) instead.
    |--------------------------------------------------------------------------
    */
    private function completeLoginOtp(Request $request, User $user, OtpService $otpService)
    {
        $otpService->clearOtp($user);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Remember Me Value
        |--------------------------------------------------------------------------
        | Purpose:
        | - Safely retrieves remember me value from the session.
        | - Prevents BadMethodCallException from session()->boolean().
        |--------------------------------------------------------------------------
        */
        $remember = (bool) $request->session()->get('login_remember', false);

        Auth::login($user, $remember);

        $request->session()->regenerate();

        $this->clearOtpSession($request);

        $this->createAuditLog(
            $user,
            'verify_password_login_otp',
            'User verified email and password login using Email OTP.',
            $request
        );

        return $this->redirectByRole($user);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Complete Forgot Password OTP
    |--------------------------------------------------------------------------
    */
    private function completeForgotPasswordOtp(Request $request, User $user)
    {
        $user->update([
            'otp_verified_at' => now(),
        ]);

        $request->session()->put('password_reset_user_id', $user->id);

        return redirect()
            ->route('password.reset.form')
            ->with('status', 'OTP verified. You may now create a new password.');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Clear OTP Session
    |--------------------------------------------------------------------------
    */
    private function clearOtpSession(Request $request): void
    {
        $request->session()->forget([
            'otp_user_id',
            'otp_purpose',
            'login_remember',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Redirect User by Role
    |--------------------------------------------------------------------------
    */
    private function redirectByRole(User $user)
    {
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        if ($user->role === 'staff') {
            return redirect('/staff/dashboard');
        }

        return redirect('/customer/dashboard');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | PRIVATE FUNCTION: Create Authentication Audit Log
    |--------------------------------------------------------------------------
    */
    private function createAuditLog(User $user, string $action, string $description, Request $request): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'user_type' => $user->role,
            'action' => $action,
            'module' => 'Authentication',
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}