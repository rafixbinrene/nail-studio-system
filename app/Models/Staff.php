<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Staff Model
|
| Purpose:
| - Stores staff profile information.
| - Connects staff to a user account when available.
| - Connects staff to services/skills.
| - Connects staff to appointment records.
| - Supports day-off records and soft delete trash.
|
| Defense explanation:
| Staff Management allows the admin to control staff availability,
| assigned services, and account status in an organized way.
|--------------------------------------------------------------------------
*/

class Staff extends Model
{
    use SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone_number',
        'status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'staff_services')
            ->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function dayOffs(): HasMany
    {
        return $this->hasMany(StaffDayOff::class);
    }

    public function activeDayOffs(): HasMany
    {
        return $this->hasMany(StaffDayOff::class)
            ->whereDate('end_date', '>=', today())
            ->orderBy('start_date');
    }
}