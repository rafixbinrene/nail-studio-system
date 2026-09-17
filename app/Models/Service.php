<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This model represents the salon services.
|
| Purpose:
| - Stores service name, description, image, price, duration, and status.
| - Supports soft delete so deleted services go to Trash first.
| - Connects services to appointments through appointment_services.
|
| Defense explanation:
| Soft delete protects the business from accidental deletion because deleted
| services can still be restored before permanent removal.
*/

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_name',
        'description',
        'image_path',
        'price',
        'duration',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'appointment_services')
            ->withPivot(['price', 'duration'])
            ->withTimestamps();
    }
}