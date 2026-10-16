
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Studio-Wide Emergency Closures Migration
|--------------------------------------------------------------------------
| Purpose:
| Store temporary studio closures caused by emergencies,
| maintenance, power outages, flooding, or other disruptions
|
| Features:
| - Single-day and multi-day closures
| - Closure reasons and administrative notes
| - Active and lifted closure statuses
| - Admin accountability
| - Closure timestamps
| - Support for customer booking restrictions
| - Support for affected appointment rescheduling
| - Audit-friendly historical records
|
| Important:
| This migration creates the closure records only
| Appointment updates, notifications, and booking restrictions
| will be implemented in the controllers and services
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    /**
     * NS BEAUTY COMMENT: Create studio_closures table
     */
    public function up(): void
    {
        Schema::create('studio_closures', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Primary Identifier
            |--------------------------------------------------------------------------
            */

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Closure Details
            |--------------------------------------------------------------------------
            */

            $table->string('title', 150);

            $table->date('start_date');

            $table->date('end_date');

            $table->text('reason');

            $table->text('admin_notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Closure Status
            |--------------------------------------------------------------------------
            | active = studio closed during the specified dates
            | lifted = administrator ended the closure
            |--------------------------------------------------------------------------
            */

            $table->string('status', 20)
                ->default('active');

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Administrator Who Declared Closure
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Administrator Who Lifted Closure
            |--------------------------------------------------------------------------
            */

            $table->foreignId('lifted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Closure Lifecycle
            |--------------------------------------------------------------------------
            */

            $table->timestamp('declared_at')
                ->nullable();

            $table->timestamp('lifted_at')
                ->nullable();

            $table->text('lift_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Laravel Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT: Performance Indexes
            |--------------------------------------------------------------------------
            | Speeds up active closure checks by date range
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['status', 'start_date', 'end_date'],
                'studio_closures_status_dates_index'
            );

            $table->index(
                'created_by',
                'studio_closures_created_by_index'
            );

        });
    }

    /**
     * NS BEAUTY COMMENT: Reverse this migration
     */
    public function down(): void
    {
        Schema::dropIfExists('studio_closures');
    }
};
