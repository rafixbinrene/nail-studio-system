<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| User Model
|
| Purpose:
| - Stores login accounts for admin, staff, and customers.
| - Supports role-based access control.
| - Supports account status control.
| - Supports Email OTP verification.
|
| Defense explanation:
| The User model is the central authentication record. OTP fields are used
| to verify identity during registration, login, and password recovery.
|--------------------------------------------------------------------------
*/

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'email_verified_at',
        'password',
        'role',
        'status',
        'otp_code',
        'otp_purpose',
        'otp_expires_at',
        'otp_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}