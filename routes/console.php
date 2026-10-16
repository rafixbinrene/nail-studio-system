
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Laravel Console Commands
|--------------------------------------------------------------------------
| Nail Studio & Beauty
|
| Purpose:
| - Preserve the default Laravel inspire command
| - Register automatic appointment reminder schedules
| - Execute reminders using Malaysia Time
|
| Important:
| The reminder command must exist before this schedule
| can successfully execute.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Default Laravel Inspire Command
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Automatic Appointment Reminders
|--------------------------------------------------------------------------
| Check once every minute for appointments requiring reminders.
|
| Planned reminders:
| - 24 hours before an appointment
| - 2 hours before an appointment
|
| The command will perform the reminder-time calculations,
| check appointment eligibility, and prevent duplicates.
|
| This command will be created in the next step:
|
| app/Console/Commands/SendAppointmentReminders.php
|--------------------------------------------------------------------------
*/

Schedule::command('nsbeauty:send-appointment-reminders')
    ->everyMinute()
    ->timezone('Asia/Kuala_Lumpur')
    ->withoutOverlapping();
