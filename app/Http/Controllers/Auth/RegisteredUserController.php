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
| NS BEAUTY COMMENT: CUSTOMER REGISTRATION CONTROLLER
|--------------------------------------------------------------------------
| Purpose:
| - Displays the customer registration page.
| - Validates customer registration information.
| - Requires Terms and Conditions acceptance on the backend.
| - Creates the customer login account and linked customer profile.
| - Records registration and policy acceptance in the audit log.
| - Sends mandatory email OTP verification.
| - Prevents access to the customer dashboard before OTP verification.
|
| Security:
| - CSRF protection is provided by Laravel web middleware.
| - Server-side validation cannot be bypassed by disabling JavaScript.
| - Only customer accounts may be created through public registration.
| - Registration operations are protected by a database transaction.
|
| NS BEAUTY COMMENT:
| The Terms and Privacy Notice must be reviewed and approved by the
| business owner before accepting real customer registrations.
|--------------------------------------------------------------------------
*/

class RegisteredUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: SHOW REGISTRATION PAGE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: REGISTER A NEW CUSTOMER
    |--------------------------------------------------------------------------
    |
    | Workflow:
    | 1. Validate registration details and policy acceptance.
    | 2. Create the customer login account.
    | 3. Create the linked customer profile.
    | 4. Record registration and accepted policy in audit logs.
    | 5. Send mandatory registration email OTP.
    | 6. Store the OTP verification session.
    | 7. Redirect the customer to the OTP verification page.
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, OtpService $otpService)
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: SERVER-SIDE REGISTRATION VALIDATION
        |--------------------------------------------------------------------------
        |
        | The "accepted" rule requires a valid acceptance value.
        | A user cannot bypass this requirement by modifying the
        | registration page or disabling JavaScript.
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone_number' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
                'unique:users,phone_number',
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
            ],

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: MANDATORY POLICY ACCEPTANCE
            |--------------------------------------------------------------------------
            |
            | Field name matches the registration Blade form:
            | name="terms_accepted"
            |
            | This covers agreement to the Terms and Conditions and
            | acknowledgment of the Privacy Notice.
            |
            | It does not grant optional marketing consent.
            |--------------------------------------------------------------------------
            */

            'terms_accepted' => [
                'required',
                'accepted',
            ],
        ], [
            'terms_accepted.required' =>
                'You must agree to the Terms and Conditions and acknowledge the Privacy Notice before registering.',

            'terms_accepted.accepted' =>
                'Please accept the Terms and Conditions and acknowledge the Privacy Notice to continue.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: CREATE ACCOUNT IN A DATABASE TRANSACTION
        |--------------------------------------------------------------------------
        |
        | All related database records must be created successfully.
        | If an exception occurs, the transaction will roll back.
        |--------------------------------------------------------------------------
        */

        $user = DB::transaction(function () use ($validated, $request) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: CREATE CUSTOMER LOGIN ACCOUNT
            |--------------------------------------------------------------------------
            |
            | The customer role is assigned by the server.
            | Users cannot submit their own admin or staff role
            | through the public registration form.
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
            | NS BEAUTY COMMENT: CREATE LINKED CUSTOMER PROFILE
            |--------------------------------------------------------------------------
            */

            Customer::updateOrCreate(
                [
                    'email' => $validated['email'],
                ],
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
            | NS BEAUTY COMMENT: REGISTRATION AND POLICY AUDIT LOG
            |--------------------------------------------------------------------------
            |
            | The validation above confirms acceptance before the
            | database transaction begins.
            |
            | The audit record includes:
            | - Registered user
            | - Registration event
            | - Policy acknowledgment
            | - Timestamp from AuditLog created_at
            | - IP address and browser user agent
            |
            | This uses the existing audit_logs table.
            |
            | IMPORTANT:
            | For stronger evidence of consent, a future database
            | enhancement can record the precise policy version,
            | notice language, and acceptance timestamp separately.
            |--------------------------------------------------------------------------
            */

            AuditLog::create([
                'user_id' => $user->id,
                'user_type' => 'customer',
                'action' => 'register_account',
                'module' => 'Authentication',
                'description' =>
                    'Customer registered, accepted the NS & Beauty Terms and Conditions, acknowledged the Privacy Notice, and requested mandatory email OTP verification.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $user;
        });

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: SEND MANDATORY REGISTRATION EMAIL OTP
        |--------------------------------------------------------------------------
        |
        | The account is already created at this point.
        | Email OTP verification is still required before login.
        |--------------------------------------------------------------------------
        */

        $otpSent = $otpService->generateAndSend(
            $user,
            'registration'
        );

        if (!$otpSent) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: OTP DELIVERY FAILURE
            |--------------------------------------------------------------------------
            |
            | Preserve the existing failure handling.
            |
            | The customer account has already been created.
            | The email delivery configuration should be reviewed
            | if this error appears.
            |--------------------------------------------------------------------------
            */

            return back()
                ->withErrors([
                    'email' =>
                        'Account was created, but Email OTP could not be sent. Please check mail configuration.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: STORE OTP VERIFICATION SESSION
        |--------------------------------------------------------------------------
        |
        | These session keys are used by the existing OtpController.
        | Keep their names unchanged.
        |--------------------------------------------------------------------------
        */

        Auth::logout();

        $request->session()->put(
            'otp_user_id',
            $user->id
        );

        $request->session()->put(
            'otp_purpose',
            'registration'
        );

        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: REDIRECT TO EMAIL OTP PAGE
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('otp.verification')
            ->with(
                'status',
                'Account created. Please check your email for the OTP code.'
            );
    }
}
