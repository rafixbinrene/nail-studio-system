<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| App Notification Model
|
| Purpose:
| - Represents one notification inside the website.
| - Can belong to an admin, staff, or customer user.
|
| Defense explanation:
| This model supports role-based and user-specific notification delivery.
|--------------------------------------------------------------------------
*/

class AppNotification extends Model
{
    protected $fillable = [
        'recipient_user_id',
        'recipient_role',
        'title',
        'message',
        'link',
        'type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }
}