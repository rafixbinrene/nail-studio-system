<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Management Feature Migration
|
| Purpose:
| - Adds soft delete support for staff trash.
| - Adds optional user_id connection to the users table.
| - Creates staff_services table for assigning staff skills/services.
| - Creates staff_day_offs table for date-based staff day off records.
|
| Defense explanation:
| This supports Staff Management because the admin can manage staff profiles,
| service skills, availability, day-off records, and deleted staff recovery.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('staff', 'user_id')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('staff', 'deleted_at')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('staff_services')) {
            Schema::create('staff_services', function (Blueprint $table) {
                $table->id();

                $table->foreignId('staff_id')
                    ->constrained('staff')
                    ->cascadeOnDelete();

                $table->foreignId('service_id')
                    ->constrained('services')
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique(['staff_id', 'service_id']);
            });
        }

        if (!Schema::hasTable('staff_day_offs')) {
            Schema::create('staff_day_offs', function (Blueprint $table) {
                $table->id();

                $table->foreignId('staff_id')
                    ->constrained('staff')
                    ->cascadeOnDelete();

                $table->date('start_date');
                $table->date('end_date');
                $table->text('reason')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_day_offs');
        Schema::dropIfExists('staff_services');

        if (Schema::hasColumn('staff', 'deleted_at')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('staff', 'user_id')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};