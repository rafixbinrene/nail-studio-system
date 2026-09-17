<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This migration adds customer management fields.
|
| Purpose:
| - Links customer profiles to user accounts.
| - Adds customer account status.
| - Stores block reason and blocking details.
|
| Defense explanation:
| This supports Customer Management because the admin can monitor customer
| accounts and block accounts when necessary for system safety.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('customers', 'address')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('address')->nullable()->after('phone_number');
            });
        }

        if (!Schema::hasColumn('customers', 'status')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'blocked'])
                    ->default('active')
                    ->after('address');
            });
        }

        if (!Schema::hasColumn('customers', 'block_reason')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->text('block_reason')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('customers', 'blocked_at')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->timestamp('blocked_at')->nullable()->after('block_reason');
            });
        }

        if (!Schema::hasColumn('customers', 'blocked_by')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('blocked_by')
                    ->nullable()
                    ->after('blocked_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'blocked_by')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('blocked_by');
            });
        }

        if (Schema::hasColumn('customers', 'blocked_at')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('blocked_at');
            });
        }

        if (Schema::hasColumn('customers', 'block_reason')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('block_reason');
            });
        }

        if (Schema::hasColumn('customers', 'status')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('customers', 'address')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }

        if (Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};