<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Day Off Model
|
| Purpose:
| - Stores staff unavailable date ranges.
| - Supports one-day or multi-day day-off records.
|
| Defense explanation:
| This helps prevent staff from being assigned to bookings when they are
| unavailable.
|--------------------------------------------------------------------------
*/

class StaffDayOff extends Model
{
    protected $fillable = [
        'staff_id',
        'start_date',
        'end_date',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}