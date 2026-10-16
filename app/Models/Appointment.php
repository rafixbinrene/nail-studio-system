<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Appointment Model
|--------------------------------------------------------------------------
| Purpose:
| - Represent customer appointment records
| - Connect customers, staff and selected services
| - Support regular and follow-up bookings
| - Support booking cancellations and status updates
| - Support emergency staff reassignment
| - Support appointments requiring rescheduling
| - Preserve original appointment information
| - Track completed appointment rescheduling
| - Preserve existing feedback relationships
|
| Defense Explanation:
| This model centralizes appointment information and relationships
| across the Admin, Staff and Customer modules
|
| The emergency rescheduling fields allow the system to preserve
| original booking information when an unexpected staff absence
| or business closure affects an appointment
|--------------------------------------------------------------------------
*/

class Appointment extends Model
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Mass Assignable Fields
    |--------------------------------------------------------------------------
    | These fields can be saved through Laravel Eloquent
    | using create(), update() or fill()
    |
    | Existing appointment fields are preserved
    | Emergency rescheduling fields have been added
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        // NS BEAUTY COMMENT: Basic appointment information
        'customer_id',
        'staff_id',
        'appointment_date',
        'start_time',
        'end_time',
        'total_price',
        'status',

        // NS BEAUTY COMMENT: Booking type and follow-up information
        'booking_type',
        'follow_up_appointment_id',
        'follow_up_from',
        'follow_up_reason',

        // NS BEAUTY COMMENT: Cancellation information
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',

        // NS BEAUTY COMMENT: Appointment status tracking
        'status_updated_by',
        'status_updated_at',

        // NS BEAUTY COMMENT: Emergency rescheduling information
        'rescheduling_reason',
        'rescheduling_requested_at',
        'original_appointment_date',
        'original_start_time',
        'original_staff_id',
        'rescheduled_at',
        'rescheduled_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Attribute Casting
    |--------------------------------------------------------------------------
    | Converts database dates and timestamps into Carbon objects
    | and preserves total_price as a two-decimal monetary value
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        // NS BEAUTY COMMENT: Original appointment fields
        'appointment_date' => 'date',
        'cancelled_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'total_price' => 'decimal:2',

        // NS BEAUTY COMMENT: Emergency rescheduling fields
        'rescheduling_requested_at' => 'datetime',
        'original_appointment_date' => 'date',
        'rescheduled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Active Appointment Statuses
    |--------------------------------------------------------------------------
    | Active appointments occupy staff time slots
    |
    | Both lowercase and existing capitalized status values
    | are supported for compatibility with stored records
    |--------------------------------------------------------------------------
    */

    public const ACTIVE_STATUSES = [
        'pending',
        'approved',
        'ongoing',
        'Pending',
        'Approved',
        'Ongoing',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Rescheduling Statuses
    |--------------------------------------------------------------------------
    | Used when:
    | - An assigned beautician becomes unavailable
    | - No qualified replacement beautician is available
    | - The studio closes unexpectedly
    |
    | Appointments requiring rescheduling are not considered
    | active staff-slot assignments
    |--------------------------------------------------------------------------
    */

    public const RESCHEDULING_STATUSES = [
        'rescheduling_required',
        'Rescheduling Required',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Closed Appointment Statuses
    |--------------------------------------------------------------------------
    | These appointments have ended or been cancelled
    |--------------------------------------------------------------------------
    */

    public const CLOSED_STATUSES = [
        'finished',
        'cancelled',
        'no-show',
        'Finished',
        'Cancelled',
        'No Show',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Customer Relationship
    |--------------------------------------------------------------------------
    | Each appointment belongs to one customer
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Assigned Beautician Relationship
    |--------------------------------------------------------------------------
    | Each appointment belongs to one assigned staff member
    |--------------------------------------------------------------------------
    */

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Appointment Services
    |--------------------------------------------------------------------------
    | An appointment can contain multiple services
    |
    | The pivot table preserves the recorded service prices
    | and durations at the time of booking
    |--------------------------------------------------------------------------
    */

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'appointment_services'
        )
            ->withPivot([
                'price',
                'duration',
            ])
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Original Follow-up Appointment
    |--------------------------------------------------------------------------
    | Connects a follow-up booking to the original appointment
    |--------------------------------------------------------------------------
    */

    public function followUpAppointment(): BelongsTo
    {
        return $this->belongsTo(
            Appointment::class,
            'follow_up_appointment_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Follow-up Booking Records
    |--------------------------------------------------------------------------
    | Loads follow-up appointments connected to this booking
    |--------------------------------------------------------------------------
    */

    public function followUpBookings(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'follow_up_appointment_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Cancellation User Relationship
    |--------------------------------------------------------------------------
    */

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Status Updated By Relationship
    |--------------------------------------------------------------------------
    */

    public function statusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'status_updated_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Original Beautician Relationship
    |--------------------------------------------------------------------------
    | Used to identify the beautician assigned before rescheduling
    |--------------------------------------------------------------------------
    */

    public function originalStaff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'original_staff_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Rescheduled By Relationship
    |--------------------------------------------------------------------------
    | Identifies the user who completed the rescheduling process
    |--------------------------------------------------------------------------
    */

    public function rescheduledBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'rescheduled_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Appointment Feedback Relationship
    |--------------------------------------------------------------------------
    | Used by:
    | - Customer Booking History
    | - Admin Booking Management
    | - Staff Appointments
    |--------------------------------------------------------------------------
    */

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Check Active Appointment
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return in_array(
            $this->status,
            self::ACTIVE_STATUSES,
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Check Rescheduling Requirement
    |--------------------------------------------------------------------------
    */

    public function requiresRescheduling(): bool
    {
        return in_array(
            $this->status,
            self::RESCHEDULING_STATUSES,
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Check Closed Appointment
    |--------------------------------------------------------------------------
    */

    public function isClosed(): bool
    {
        return in_array(
            $this->status,
            self::CLOSED_STATUSES,
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Check Whether Staff Slot Is Occupied
    |--------------------------------------------------------------------------
    | Active bookings occupy staff slots
    |
    | Appointments awaiting emergency rescheduling do not
    | occupy their previous staff slots
    |
    | Booking availability queries must use the same rule
    |--------------------------------------------------------------------------
    */

    public function occupiesStaffSlot(): bool
    {
        return $this->isActive();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Check Whether Appointment Can Be Rescheduled
    |--------------------------------------------------------------------------
    | Currently only appointments explicitly marked as
    | rescheduling_required are eligible
    |
    | Additional checks for customer ownership, business hours,
    | Sunday closure, staff skills, conflicts and date limits
    | must be performed in the rescheduling controller
    |--------------------------------------------------------------------------
    */

    public function canBeRescheduled(): bool
    {
        return $this->requiresRescheduling();
    }
}
