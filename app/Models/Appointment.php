<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| Appointment Model
|
| Purpose:
| - Represents customer booking records.
| - Connects appointments to customer, staff, services, follow-up bookings,
|   cancellation records, status updates, and feedback.
|
| Defense explanation:
| This model centralizes booking relationships so the system can display
| complete appointment information across customer, staff, and admin modules.
|--------------------------------------------------------------------------
*/

class Appointment extends Model
{
    protected $fillable = [
        'customer_id',
        'staff_id',
        'appointment_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'booking_type',
        'follow_up_appointment_id',
        'follow_up_from',
        'follow_up_reason',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'status_updated_by',
        'status_updated_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'cancelled_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    public const ACTIVE_STATUSES = [
        'pending',
        'approved',
        'ongoing',
        'Pending',
        'Approved',
        'Ongoing',
    ];

    public const CLOSED_STATUSES = [
        'finished',
        'cancelled',
        'no-show',
        'Finished',
        'Cancelled',
        'No Show',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointment_services')
            ->withPivot(['price', 'duration'])
            ->withTimestamps();
    }

    public function followUpAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'follow_up_appointment_id');
    }

    public function followUpBookings(): HasMany
    {
        return $this->hasMany(Appointment::class, 'follow_up_appointment_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function statusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Appointment Feedback Relationship
    |--------------------------------------------------------------------------
    | Purpose:
    | - Loads the customer feedback connected to this appointment.
    | - Used in Customer Booking History, Admin Booking Management, and
    |   Staff Appointments.
    |--------------------------------------------------------------------------
    */
    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
}