<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StaffActivationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Activation Controller
|--------------------------------------------------------------------------
| Purpose:
| - Shows staff password setup page from email activation link.
| - Verifies activation token.
| - Lets staff create their own password.
| - Activates the staff account after password setup.
|
| Defense explanation:
| Staff accounts are not manually assigned passwords by the admin. Instead,
| the system sends a secure activation email, allowing the staff member to
| set their own password before logging in.
|--------------------------------------------------------------------------
*/

class StaffActivationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Show Staff Activation Form
    |--------------------------------------------------------------------------
    */
    public function show(string $token)
    {
        $tokenRecord = StaffActivationToken::with('user')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord || !$tokenRecord->user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'This staff activation link is invalid or expired.',
                ]);
        }

        return view('auth.staff-activate', [
            'token' => $token,
            'user' => $tokenRecord->user,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | FUNCTION: Activate Staff Account
    |--------------------------------------------------------------------------
    | Flow:
    | 1. Validate token.
    | 2. Validate new password.
    | 3. Save password.
    | 4. Mark account active and email verified.
    | 5. Mark activation token as used.
    | 6. Redirect staff to login.
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, string $token)
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ]);

        $tokenRecord = StaffActivationToken::with('user')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord || !$tokenRecord->user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'This staff activation link is invalid or expired.',
                ]);
        }

        $user = $tokenRecord->user;

        $user->update([
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_purpose' => null,
            'otp_expires_at' => null,
            'otp_verified_at' => null,
        ]);

        $tokenRecord->update([
            'used_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'user_type' => 'staff',
            'action' => 'activate_staff_account',
            'module' => 'Authentication',
            'description' => 'Staff activated account and set password.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::logout();

        return redirect()
            ->route('login')
            ->with('status', 'Staff account activated successfully. You may now login.');
    }
}