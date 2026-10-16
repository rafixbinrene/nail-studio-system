<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Studio Closure Model
|--------------------------------------------------------------------------
| Purpose:
| - Manage studio-wide emergency closure records
| - Determine whether the studio is closed on a specific date
| - Retrieve active emergency closures
| - Track administrators who declared or lifted closures
| - Preserve closure history for audit purposes
|
| Important:
| A closure is enforced only when its status is active
| and the requested date is within its date range
|--------------------------------------------------------------------------
*/

class StudioClosure extends Model
{
    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Mass-Assignable Columns
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'reason',
        'admin_notes',
        'status',
        'created_by',
        'lifted_by',
        'declared_at',
        'lifted_at',
        'lift_reason',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'declared_at' => 'datetime',
        'lifted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Closure Status Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_ACTIVE = 'active';

    public const STATUS_LIFTED = 'lifted';

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Administrator Relationships
    |--------------------------------------------------------------------------
    */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function liftedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'lifted_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Active Closures Query Scope
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_ACTIVE
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Closures Affecting a Specific Date
    |--------------------------------------------------------------------------
    */

    public function scopeAffectingDate(
        Builder $query,
        string $date
    ): Builder {
        return $query
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Check Whether Studio Is Closed
    |--------------------------------------------------------------------------
    | Returns true when an active closure covers the date
    |--------------------------------------------------------------------------
    */

    public static function isClosedOn(
        string $date
    ): bool {
        return self::query()
            ->active()
            ->affectingDate($date)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Get Active Closure for Date
    |--------------------------------------------------------------------------
    */

    public static function activeClosureOn(
        string $date
    ): ?self {
        return self::query()
            ->active()
            ->affectingDate($date)
            ->orderBy('start_date')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Check Whether Closure Is Active
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Determine Whether Closure Covers Date
    |--------------------------------------------------------------------------
    */

    public function coversDate(
        string $date
    ): bool {
        $target = Carbon::parse($date)->startOfDay();

        return $this->isActive()
            && $target->betweenIncluded(
                $this->start_date,
                $this->end_date
            );
    }

    /*
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT: Get Inclusive Closure Duration
    |--------------------------------------------------------------------------
    */

    public function getTotalDaysAttribute(): int
    {
        if (
            !$this->start_date ||
            !$this->end_date
        ) {
            return 0;
        }

        return (int) $this->start_date
            ->diffInDays($this->end_date) + 1;
    }
}
