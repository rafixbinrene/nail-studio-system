<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This model represents customer profiles.
|
| Purpose:
| - Stores customer information.
| - Connects customers to user accounts.
| - Connects customers to appointment records.
| - Supports account blocking/unblocking.
|
| Defense explanation:
| Customer Management allows the admin to review customer details,
| booking history, and control account access when needed.
|--------------------------------------------------------------------------
*/

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone_number',
        'address',
        'status',
        'block_reason',
        'blocked_at',
        'blocked_by',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}