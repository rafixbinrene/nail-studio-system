<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Feedback Model
|
| Purpose:
| - Stores customer rating and comment.
| - Connects feedback to customer, appointment, and staff.
|
| Defense explanation:
| Feedback records help the business evaluate service quality and customer
| satisfaction after completed appointments.
|--------------------------------------------------------------------------
*/

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'customer_id',
        'appointment_id',
        'staff_id',
        'rating',
        'comment',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}