<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('appointments', 'follow_up_appointment_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreignId('follow_up_appointment_id')
                    ->nullable()
                    ->after('booking_type')
                    ->constrained('appointments')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'follow_up_appointment_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('follow_up_appointment_id');
            });
        }
    }
};