<?php

namespace App\Modules\Project\Models;

use App\Modules\Client\Models\Client;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\TimeTracking\Models\TimeEntry;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'project_code',
        'client_id',
        'name',
        'description',
        'project_manager_id',
        'priority',
        'status',
        'start_date',
        'due_date',
        'budget',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'budget' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'planned' => 'Planned',
        'active' => 'Active',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public function timeEntries(): HasMany
    {
        return $this->hasMany(
            TimeEntry::class,
            'project_id'
        );
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'project_manager_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projectMembers(): HasMany
    {
        return $this->hasMany(
            ProjectMember::class,
            'project_id'
        );
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'project_members',
            'project_id',
            'user_id'
        )
            ->withPivot([
                'member_role',
                'added_by',
            ])
            ->withTimestamps();
    }

    public function services(): HasMany
    {
        return $this->hasMany(
            ProjectService::class,
            'project_id'
        );
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            ProjectActivity::class,
            'project_id'
        );
    }

    public static function statuses(): array
    {
        return self::STATUSES;
    }

    public static function priorities(): array
    {
        return self::PRIORITIES;
    }

    public function progressPercentage(): int
    {
        $cancelledStatus =
            Task::cancelledStatus();

        $completedStatus =
            Task::completedStatus();

        $totalTasks = $this
            ->tasks()
            ->where(
                'status',
                '!=',
                $cancelledStatus
            )
            ->count();

        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this
            ->tasks()
            ->where(
                'status',
                $completedStatus
            )
            ->count();

        return (int) round(
            (
                $completedTasks
                / $totalTasks
            ) * 100
        );
    }

    public function isManager(User $user): bool
    {
        return (int) $this->project_manager_id
            === (int) $user->id;
    }

    public function hasMember(User $user): bool
    {
        return $this->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function isClosed(): bool
    {
        return in_array(
            $this->status,
            [
                'completed',
                'cancelled',
            ],
            true
        );
    }
}