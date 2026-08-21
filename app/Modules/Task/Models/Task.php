<?php

namespace App\Modules\Task\Models;

use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\ProjectService;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Modules\TimeTracking\Models\TimeEntry;

class Task extends Model
{
    use SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'project_id',
        'project_service_id',
        'parent_task_id',
        'title',
        'description',
        'assigned_to',
        'created_by',
        'priority',
        'status',
        'progress_percent',
        'requires_review',
        'reviewer_id',
        'submitted_for_review_at',
        'reviewed_at',
        'review_note',
        'start_date',
        'due_at',
        'estimated_hours',
        'completed_at',
        'status_kanban_position',
        'priority_kanban_position',
    ];

    protected $casts = [
        'requires_review' => 'boolean',
        'submitted_for_review_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'start_date' => 'date',
        'due_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    // public const STATUSES = [
    //     'to_do' => 'To Do',
    //     'in_progress' => 'In Progress',
    //     'in_review' => 'In Review',
    //     'blocked' => 'Blocked',
    //     'completed' => 'Completed',
    //     'cancelled' => 'Cancelled',
    // ];

    // public const PRIORITIES = [
    //     'low' => 'Low',
    //     'medium' => 'Medium',
    //     'high' => 'High',
    //     'urgent' => 'Urgent',
    // ];

    public function statusDefinition(): BelongsTo
    {
        return $this->belongsTo(
            TaskStatus::class,
            'status',
            'slug'
        );
    }

    public function priorityDefinition(): BelongsTo
    {
        return $this->belongsTo(
            TaskPriority::class,
            'priority',
            'slug'
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectService(): BelongsTo
    {
        return $this->belongsTo(
            ProjectService::class,
            'project_service_id'
        );
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_task_id'
        );
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_task_id'
        );
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewer_id'
        );
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(
            TimeEntry::class,
            'task_id'
        );
    }

    public function activeTimeEntries(): HasMany
    {
        return $this->hasMany(
            TimeEntry::class,
            'task_id'
        )->whereIn(
                'status',
                [
                    'running',
                    'paused',
                ]
            );
    }

    public function totalTrackedSeconds(): int
    {
        return (int) $this
            ->timeEntries()
            ->sum('total_seconds');
    }

    /*
     * Dependency records jahan current Task wait kar rahi hai.
     */
    public function dependencyLinks(): HasMany
    {
        return $this->hasMany(
            TaskDependency::class,
            'task_id'
        );
    }

    /*
     * Dependency records jahan doosri Tasks
     * current Task ke complete hone ka wait kar rahi hain.
     */
    public function dependentLinks(): HasMany
    {
        return $this->hasMany(
            TaskDependency::class,
            'depends_on_task_id'
        );
    }

    /*
     * Current Task kin Tasks par depend karti hai.
     *
     * Example:
     * Development Task prerequisites:
     * - Design Task
     * - Content Approval Task
     */
    public function prerequisiteTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'task_dependencies',
            'task_id',
            'depends_on_task_id'
        )
            ->withPivot([
                'id',
                'created_by',
            ])
            ->withTimestamps();
    }

    /*
     * Kaunsi Tasks current Task par depend karti hain.
     */
    public function dependentTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'task_dependencies',
            'depends_on_task_id',
            'task_id'
        )
            ->withPivot([
                'id',
                'created_by',
            ])
            ->withTimestamps();
    }

    /*
     * Sirf completed prerequisite dependency satisfy karegi.
     *
     * Cancelled, In Review, In Progress aur To Do
     * dependency satisfy nahi karenge.
     */
    // public function hasIncompleteDependencies(): bool
    // {
    //     return $this->prerequisiteTasks()
    //         ->where(
    //             'tasks.status',
    //             '!=',
    //             'completed'
    //         )
    //         ->exists();
    // }

    public function hasIncompleteDependencies(): bool
    {
        return $this->prerequisiteTasks()
            ->where(
                'tasks.status',
                '!=',
                self::completedStatus()
            )
            ->exists();
    }

    public function hasSystemStatus(
        string $systemKey
    ): bool {
        if (
            $this->relationLoaded(
                'statusDefinition'
            )
        ) {
            return $this
                ->statusDefinition
                    ?->system_key === $systemKey;
        }

        return TaskStatus::query()
            ->where(
                'slug',
                $this->status
            )
            ->where(
                'system_key',
                $systemKey
            )
            ->exists();
    }

    public function isToDo(): bool
    {
        return $this->hasSystemStatus(
            'to_do'
        );
    }

    public function isInProgress(): bool
    {
        return $this->hasSystemStatus(
            'in_progress'
        );
    }

    public function isInReview(): bool
    {
        return $this->hasSystemStatus(
            'in_review'
        );
    }

    public function isBlocked(): bool
    {
        return $this->hasSystemStatus(
            'blocked'
        );
    }

    public function isCompleted(): bool
    {
        return $this->hasSystemStatus(
            'completed'
        );
    }

    public function isCancelled(): bool
    {
        return $this->hasSystemStatus(
            'cancelled'
        );
    }

    // public static function statuses(): array
    // {
    //     return self::STATUSES;
    // }

    // public static function priorities(): array
    // {
    //     return self::PRIORITIES;
    // }

    public static function statuses(): array
    {
        return TaskStatus::options();
    }

    public static function activeStatuses(): array
    {
        return TaskStatus::options(true);
    }

    public static function manualStatuses(): array
    {
        return TaskStatus::manualOptions();
    }

    public static function priorities(): array
    {
        return TaskPriority::options();
    }

    public static function activePriorities(): array
    {
        return TaskPriority::options(true);
    }

    public static function defaultStatus(): string
    {
        return TaskStatus::defaultSlug();
    }

    public static function defaultPriority(): string
    {
        return TaskPriority::defaultSlug();
    }

    public static function toDoStatus(): string
    {
        return TaskStatus::requiredSystemSlug(
            'to_do'
        );
    }

    public static function inProgressStatus(): string
    {
        return TaskStatus::requiredSystemSlug(
            'in_progress'
        );
    }

    public static function inReviewStatus(): string
    {
        return TaskStatus::requiredSystemSlug(
            'in_review'
        );
    }

    public static function blockedStatus(): string
    {
        return TaskStatus::requiredSystemSlug(
            'blocked'
        );
    }

    public static function completedStatus(): string
    {
        return TaskStatus::requiredSystemSlug(
            'completed'
        );
    }

    public static function cancelledStatus(): string
    {
        return TaskStatus::requiredSystemSlug(
            'cancelled'
        );
    }

    public static function closedStatusSlugs(): array
    {
        return TaskStatus::closedSlugs();
    }

    public function isOverdue(): bool
    {
        return $this->due_at
            && !$this->isClosed()
            && $this->due_at->isPast();
    }

    public function isClosed(): bool
    {
        if (
            $this->relationLoaded(
                'statusDefinition'
            )
        ) {
            return (bool) $this
                ->statusDefinition
                    ?->is_closed;
        }

        return TaskStatus::query()
            ->where(
                'slug',
                $this->status
            )
            ->where(
                'is_closed',
                true
            )
            ->exists();
    }
}