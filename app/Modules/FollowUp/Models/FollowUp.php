<?php

namespace App\Modules\FollowUp\Models;

use App\Modules\Lead\Models\Lead;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $table = 'lead_follow_ups';

    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'followed_up_at',
        'outcome',
        'notes',
        'next_follow_up_at',
    ];

    protected $casts = [
        'followed_up_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
    ];

    public const TYPES = [
        'call' => 'Phone Call',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'meeting' => 'Meeting',
        'demo' => 'Product Demo',
        'visit' => 'Office Visit',
        'other' => 'Other',
    ];

    public const OUTCOMES = [
        'no_response' => 'No Response',
        'interested' => 'Interested',
        'callback' => 'Callback Required',
        'meeting_scheduled' => 'Meeting Scheduled',
        'qualified' => 'Qualified',
        'not_interested' => 'Not Interested',
        'converted' => 'Converted',
        'other' => 'Other',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function types(): array
    {
        return self::TYPES;
    }

    public static function outcomes(): array
    {
        return self::OUTCOMES;
    }
}