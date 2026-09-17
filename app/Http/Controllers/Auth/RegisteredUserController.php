<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Registration Controller
|--------------------------------------------------------------------------
| Purpose:
| - Shows the registration page.
| - Creates customer account.
| - Creates linked customer profile.
| - Sends mandatory Email OTP after registration.
| - Prevents first-time users from entering the dashboard without OTP.
|
| Defense explanation:
| First-time customers must verify their registered email address before they
| can access the customer dashboard. This confirms account ownership.
|--------------------------------------------------------------------------
*/

class RegisteredUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Show Registration Page
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Store New Customer Account
    |--------------------------------------------------------------------------
    | Flow:
    | 1. Validate registration form.
    | 2. Create user account.
    | 3. Create customer profile.
    | 4. Save audit log.
    | 5. Send mandatory Email OTP.
    | 6. Redirect to OTP page.
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, OtpService $otpService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'phone_number' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
                'unique:users,phone_number',
            ],

            'address' => ['nullable', 'string', 'max:500'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ]);

        $user = DB::transaction(function () use ($validated, $request) {
            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Create Login Account
            |--------------------------------------------------------------------------
            */
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'password' => $validated['password'],
                'role' => 'customer',
                'status' => 'active',
            ]);

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Create Customer Profile
            |--------------------------------------------------------------------------
            */
            Customer::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'user_id' => $user->id,
                    'full_name' => $validated['name'],
                    'phone_number' => $validated['phone_number'],
                    'address' => $validated['address'] ?? null,
                    'status' => 'active',
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Save Registration Audit Log
            |--------------------------------------------------------------------------
            */
            AuditLog::create([
                'user_id' => $user->id,
                'user_type' => 'customer',
                'action' => 'register_account',
                'module' => 'Authentication',
                'description' => 'Customer account registered and mandatory Email OTP verification requested.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $user;
        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Send Mandatory Registration Email OTP
        |--------------------------------------------------------------------------
        */
        $otpSent = $otpService->generateAndSend($user, 'registration');

        if (!$otpSent) {
            return back()
                ->withErrors([
                    'email' => 'Account was created, but Email OTP could not be sent. Please check mail configuration.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT:
        | Store OTP Session
        |--------------------------------------------------------------------------
        | Purpose:
        | - Temporarily stores the user ID and OTP purpose.
        | - Used by OtpController during OTP verification.
        |--------------------------------------------------------------------------
        */
        Auth::logout();

        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_purpose', 'registration');

        return redirect()
            ->route('otp.verification')
            ->with('status', 'Account created. Please check your email for the OTP code.');
    }
}