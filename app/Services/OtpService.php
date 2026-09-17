<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Email OTP Service
|--------------------------------------------------------------------------
| Purpose:
| - Generates secure 6-digit OTP codes.
| - Hashes OTP before saving to database.
| - Sends OTP to the user's registered email.
| - Verifies OTP for registration, login, and password recovery.
| - Sends OTP email after response to make login faster.
|
| Defense explanation:
| The OTP is stored as a hash instead of plain text. Email sending is handled
| after the page response so the user is redirected to the OTP page faster.
|--------------------------------------------------------------------------
*/

class OtpService
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Generate and Send Email OTP
    |--------------------------------------------------------------------------
    | Used by:
    | - Registration OTP
    | - Login OTP after email + password
    | - Forgot Password OTP
    |
    | Performance improvement:
    | - OTP is saved immediately.
    | - Email is sent after response so login/register feels faster.
    |--------------------------------------------------------------------------
    */
    public function generateAndSend(User $user, string $purpose): bool
    {
        $otpCode = (string) random_int(100000, 999999);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Save Hashed OTP
        |--------------------------------------------------------------------------
        | Purpose:
        | - Store hashed OTP in database.
        | - Set OTP expiration to 5 minutes.
        |--------------------------------------------------------------------------
        */
        $user->update([
            'otp_code' => Hash::make($otpCode),
            'otp_purpose' => $purpose,
            'otp_expires_at' => now()->addMinutes(5),
            'otp_verified_at' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Fast Email Sending
        |--------------------------------------------------------------------------
        | Purpose:
        | - Do not make the user wait while Gmail sends the email.
        | - Laravel will redirect first, then send the email after response.
        |--------------------------------------------------------------------------
        */
        $this->sendEmailOtpAfterResponse($user, $otpCode, $purpose);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Verify Email OTP
    |--------------------------------------------------------------------------
    | Purpose:
    | - Checks if OTP exists.
    | - Checks if OTP purpose matches.
    | - Checks if OTP is not expired.
    | - Checks if entered OTP matches the hashed OTP.
    |--------------------------------------------------------------------------
    */
    public function verify(User $user, string $otpCode, string $purpose): bool
    {
        if (!$user->otp_code || !$user->otp_expires_at) {
            return false;
        }

        if ($user->otp_purpose !== $purpose) {
            return false;
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return false;
        }

        return Hash::check($otpCode, $user->otp_code);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Send OTP Email After Response
    |--------------------------------------------------------------------------
    | Purpose:
    | - Sends OTP email after the browser receives the next page.
    | - Makes login/register/forgot password faster.
    |--------------------------------------------------------------------------
    */
    private function sendEmailOtpAfterResponse(User $user, string $otpCode, string $purpose): void
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Dispatch After Response
        |--------------------------------------------------------------------------
        | Purpose:
        | - The email sending process runs after Laravel returns the response.
        | - This prevents the login page from loading slowly.
        |--------------------------------------------------------------------------
        */
        dispatch(function () use ($user, $otpCode, $purpose) {
            $this->sendEmailOtp($user, $otpCode, $purpose);
        })->afterResponse();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Send OTP Email
    |--------------------------------------------------------------------------
    | Purpose:
    | - Sends the OTP through Laravel Mail.
    | - Uses Gmail SMTP settings from .env.
    | - Logs the error if email sending fails.
    |--------------------------------------------------------------------------
    */
    private function sendEmailOtp(User $user, string $otpCode, string $purpose): bool
    {
        try {
            $purposeLabel = $this->getPurposeLabel($purpose);

            Mail::raw(
                "Hello {$user->name},\n\n" .
                "Your Nail Studio & Beauty OTP code is: {$otpCode}\n\n" .
                "Purpose: {$purposeLabel}\n" .
                "This code will expire in 5 minutes.\n\n" .
                "If you did not request this code, please ignore this email.",
                function ($message) use ($user, $purposeLabel) {
                    $message->to($user->email)
                        ->subject('Nail Studio & Beauty - ' . $purposeLabel);
                }
            );

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            Log::error('NS BEAUTY EMAIL OTP FAILED', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Clear OTP
    |--------------------------------------------------------------------------
    | Purpose:
    | - Removes OTP after successful verification.
    | - Prevents OTP reuse.
    |--------------------------------------------------------------------------
    */
    public function clearOtp(User $user): void
    {
        $user->update([
            'otp_code' => null,
            'otp_purpose' => null,
            'otp_expires_at' => null,
            'otp_verified_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: OTP Purpose Label
    |--------------------------------------------------------------------------
    | Purpose:
    | - Converts internal OTP purpose names into readable email labels.
    |--------------------------------------------------------------------------
    */
    private function getPurposeLabel(string $purpose): string
    {
        return match ($purpose) {
            'registration' => 'Account Registration Verification',
            'login_password' => 'Login Verification',
            'forgot_password' => 'Password Recovery',
            default => 'OTP Verification',
        };
    }
}