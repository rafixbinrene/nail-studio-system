
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Emergency Appointment Rescheduling Migration
|--------------------------------------------------------------------------
| Purpose:
| - Record why an appointment requires rescheduling
| - Preserve the original appointment schedule
| - Track emergency-related appointment changes
| - Support customer-approved rescheduling
|
| Existing appointment columns and booking records are preserved
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Add Rescheduling Fields
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {

            // NS BEAUTY COMMENT: Reason for requesting a new schedule
            $table->string('rescheduling_reason', 1000)
                ->nullable();

            // NS BEAUTY COMMENT: Date when rescheduling became necessary
            $table->timestamp('rescheduling_requested_at')
                ->nullable();

            // NS BEAUTY COMMENT: Original appointment date
            $table->date('original_appointment_date')
                ->nullable();

            // NS BEAUTY COMMENT: Original starting time
            $table->time('original_start_time')
                ->nullable();

            // NS BEAUTY COMMENT: Original assigned beautician
            $table->foreignId('original_staff_id')
                ->nullable()
                ->constrained('staff')
                ->nullOnDelete();

            // NS BEAUTY COMMENT: Date when rescheduling was completed
            $table->timestamp('rescheduled_at')
                ->nullable();

            // NS BEAUTY COMMENT: User who completed the rescheduling
            $table->foreignId('rescheduled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Rollback
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['original_staff_id']);
            $table->dropForeign(['rescheduled_by']);

            $table->dropColumn([
                'rescheduling_reason',
                'rescheduling_requested_at',
                'original_appointment_date',
                'original_start_time',
                'original_staff_id',
                'rescheduled_at',
                'rescheduled_by',
            ]);
        });
    }
};
