<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Email OTP Fields for Users Table
|
| Purpose:
| - Adds phone number as customer contact information.
| - Adds OTP fields for registration, login, and password recovery.
|
| Defense explanation:
| These fields support Email OTP verification. The OTP is used for account
| registration, login verification, and forgot-password recovery.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->unique()->after('email');
            }

            if (!Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code')->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'otp_purpose')) {
                $table->string('otp_purpose')->nullable()->after('otp_code');
            }

            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp_purpose');
            }

            if (!Schema::hasColumn('users', 'otp_verified_at')) {
                $table->timestamp('otp_verified_at')->nullable()->after('otp_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'otp_verified_at')) {
                $table->dropColumn('otp_verified_at');
            }

            if (Schema::hasColumn('users', 'otp_expires_at')) {
                $table->dropColumn('otp_expires_at');
            }

            if (Schema::hasColumn('users', 'otp_purpose')) {
                $table->dropColumn('otp_purpose');
            }

            if (Schema::hasColumn('users', 'otp_code')) {
                $table->dropColumn('otp_code');
            }

            if (Schema::hasColumn('users', 'phone_number')) {
                $table->dropUnique(['phone_number']);
                $table->dropColumn('phone_number');
            }
        });
    }
};