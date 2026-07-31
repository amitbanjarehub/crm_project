<?php

namespace App\Modules\TimeTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntryBreak extends Model
{
    protected $table = 'time_entry_breaks';

    protected $fillable = [
        'time_entry_id',
        'paused_at',
        'resumed_at',
        'break_seconds',
    ];

    protected $casts = [
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'break_seconds' => 'integer',
    ];

    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo(
            TimeEntry::class,
            'time_entry_id'
        );
    }
}