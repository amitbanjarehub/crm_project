<?php

namespace App\Modules\TimeTracking\Models;

use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\ProjectService;
use App\Modules\Role\Models\Role;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeEntry extends Model
{
    use SoftDeletes;

    protected $table = 'time_entries';

    protected $fillable = [
        'user_id',
        'role_id',
        'task_id',
        'project_id',
        'project_service_id',
        'active_key',
        'status',
        'started_at',
        'last_started_at',
        'paused_at',
        'stopped_at',
        'total_seconds',
        'notes',
        'user_name_snapshot',
        'role_name_snapshot',
        'member_role_snapshot',
        'created_by',
        'stopped_by',
        'stop_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_started_at' => 'datetime',
        'paused_at' => 'datetime',
        'stopped_at' => 'datetime',
        'total_seconds' => 'integer',
    ];

    public const STATUSES = [
        'running' => 'Running',
        'paused' => 'Paused',
        'stopped' => 'Stopped',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'role_id'
        );
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(
            Task::class,
            'task_id'
        )->withTrashed();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'project_id'
        )->withTrashed();
    }

    public function projectService(): BelongsTo
    {
        return $this->belongsTo(
            ProjectService::class,
            'project_service_id'
        )->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function stoppedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'stopped_by'
        );
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(
            TimeEntryBreak::class,
            'time_entry_id'
        );
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                'running',
                'paused',
            ]
        );
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isStopped(): bool
    {
        return $this->status === 'stopped';
    }

    /**
     * Stored seconds + current running segment.
     */
    public function liveSeconds(
        ?CarbonInterface $at = null
    ): int {
        $seconds = (int) $this->total_seconds;

        if (
            !$this->isRunning()
            || !$this->last_started_at
        ) {
            return max(0, $seconds);
        }

        $now = $at ?? now();

        $runningSeconds = (int)
            $this->last_started_at
                ->diffInSeconds(
                    $now,
                    false
                );

        return max(
            0,
            $seconds + max(0, $runningSeconds)
        );
    }

    public static function formatSeconds(
        int $seconds
    ): string {
        $seconds = max(0, $seconds);

        $hours = intdiv($seconds, 3600);

        $minutes = intdiv(
            $seconds % 3600,
            60
        );

        $remainingSeconds = $seconds % 60;

        return sprintf(
            '%02d:%02d:%02d',
            $hours,
            $minutes,
            $remainingSeconds
        );
    }
}