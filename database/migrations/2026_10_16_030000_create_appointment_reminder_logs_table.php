
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Appointment Reminder Logs Migration
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Purpose:
| - Track scheduled appointment reminders
| - Support 24-hour reminders
| - Support 2-hour reminders
| - Prevent duplicate reminder records
| - Support appointment rescheduling
| - Track notification and email delivery attempts
|
| Security and reliability:
| - Every reminder belongs to an existing appointment
| - Duplicate reminder records are prevented by a unique index
| - Original appointment schedule is recorded for auditing
| - Appointment deletion removes associated reminder logs
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Create Reminder Tracking Table
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create(
            'appointment_reminder_logs',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Primary Key
                |--------------------------------------------------------------------------
                */

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Related Appointment
                |--------------------------------------------------------------------------
                */

                $table->foreignId('appointment_id')
                    ->constrained('appointments')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Reminder Type
                |--------------------------------------------------------------------------
                | Supported values:
                | 24h = 24 hours before appointment
                | 2h  = 2 hours before appointment
                |--------------------------------------------------------------------------
                */

                $table->string('reminder_type', 10);

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Appointment Schedule Snapshot
                |--------------------------------------------------------------------------
                | Store the date and time used when the reminder
                | was processed.
                |
                | If an appointment is rescheduled, its new
                | schedule can receive its own reminder record.
                |--------------------------------------------------------------------------
                */

                $table->date('appointment_date');

                $table->time('appointment_time');

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Processing Status
                |--------------------------------------------------------------------------
                | pending = reserved for processing
                | sent    = processing completed
                | failed  = processing encountered an error
                |--------------------------------------------------------------------------
                */

                $table->string('status', 20)
                    ->default('pending');

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Website Notification Tracking
                |--------------------------------------------------------------------------
                */

                $table->boolean('website_notification_sent')
                    ->default(false);

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Email Tracking
                |--------------------------------------------------------------------------
                | This indicates whether the application's mail
                | transport accepted the email send operation,
                | not whether it reached the recipient's inbox.
                |--------------------------------------------------------------------------
                */

                $table->boolean('email_sent')
                    ->default(false);

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Processing Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamp('attempted_at')
                    ->nullable();

                $table->timestamp('completed_at')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Error Logging
                |--------------------------------------------------------------------------
                */

                $table->text('error_message')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Laravel Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Duplicate Prevention
                |--------------------------------------------------------------------------
                | Only one reminder record may exist for each:
                | - Appointment
                | - Reminder type
                | - Appointment date
                | - Appointment time
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'appointment_id',
                        'reminder_type',
                        'appointment_date',
                        'appointment_time',
                    ],
                    'ns_reminder_unique_schedule'
                );

                /*
                |--------------------------------------------------------------------------
                | NS BEAUTY COMMENT: Query Optimization
                |--------------------------------------------------------------------------
                */

                $table->index(
                    ['status', 'reminder_type'],
                    'ns_reminder_status_index'
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Roll Back Migration
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::dropIfExists(
            'appointment_reminder_logs'
        );
    }
};
