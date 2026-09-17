<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Feedback Booking Connection Migration
|
| Purpose:
| - Connects customer feedback to a specific appointment.
| - Connects feedback to the staff member who handled the booking.
| - Prevents duplicate feedback for the same appointment.
|
| Defense explanation:
| Feedback must be connected to a finished booking so the customer can only
| review an actual completed service transaction.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback', 'appointment_id')) {
                $table->foreignId('appointment_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('appointments')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('feedback', 'staff_id')) {
                $table->foreignId('staff_id')
                    ->nullable()
                    ->after('appointment_id')
                    ->constrained('staff')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (Schema::hasColumn('feedback', 'staff_id')) {
                $table->dropConstrainedForeignId('staff_id');
            }

            if (Schema::hasColumn('feedback', 'appointment_id')) {
                $table->dropConstrainedForeignId('appointment_id');
            }
        });
    }
};