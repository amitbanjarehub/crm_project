<?php

namespace App\Modules\Project\Models;

use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\TimeTracking\Models\TimeEntry;

class ProjectService extends Model
{
    use SoftDeletes;

    protected $table = 'project_services';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'assigned_to',
        'priority',
        'status',
        'start_date',
        'due_date',
        'sort_order',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'in_review' => 'In Review',
        'completed' => 'Completed',
        'on_hold' => 'On Hold',
        'cancelled' => 'Cancelled',
    ];

    public function timeEntries(): HasMany
    {
        return $this->hasMany(
            TimeEntry::class,
            'project_service_id'
        );
    }
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

    public function tasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'project_service_id'
        );
    }

    public static function statuses(): array
    {
        return self::STATUSES;
    }

    public function progressPercentage(): int
    {
        $total = $this->tasks()
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->tasks()
            ->where('status', 'completed')
            ->count();

        return (int) round(
            ($completed / $total) * 100
        );
    }
}