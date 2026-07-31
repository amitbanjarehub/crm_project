<?php

namespace App\Modules\User\Models;

use App\Modules\Lead\Models\Lead;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Role\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Modules\Client\Models\Client;
use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\ProjectMember;
use App\Modules\Project\Models\ProjectService;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use App\Modules\Task\Models\TaskAttachment;
use App\Modules\TimeTracking\Models\TimeEntry;
use Illuminate\Database\Eloquent\Relations\HasOne;



class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function managedProjects(): HasMany
    {
        return $this->hasMany(
            Project::class,
            'project_manager_id'
        );
    }

    public function createdProjects(): HasMany
    {
        return $this->hasMany(
            Project::class,
            'created_by'
        );
    }

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(
            ProjectMember::class,
            'user_id'
        );
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_members',
            'user_id',
            'project_id'
        )
            ->withPivot([
                'member_role',
                'added_by',
            ])
            ->withTimestamps();
    }

    public function assignedProjectServices(): HasMany
    {
        return $this->hasMany(
            ProjectService::class,
            'assigned_to'
        );
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'assigned_to'
        );
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'created_by'
        );
    }

    public function reviewedTasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'reviewer_id'
        );
    }

    public function taskComments(): HasMany
    {
        return $this->hasMany(
            TaskComment::class,
            'user_id'
        );
    }

    public function taskAttachments(): HasMany
    {
        return $this->hasMany(
            TaskAttachment::class,
            'uploaded_by'
        );
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(
            TimeEntry::class,
            'user_id'
        );
    }

    public function activeTimeEntry(): HasOne
    {
        return $this->hasOne(
            TimeEntry::class,
            'user_id'
        )
            ->whereIn(
                'status',
                [
                    'running',
                    'paused',
                ]
            )
            ->latestOfMany();
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(
            Lead::class,
            'assigned_to'
        );
    }

    public function createdLeads(): HasMany
    {
        return $this->hasMany(
            Lead::class,
            'created_by'
        );
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(
            FollowUp::class,
            'user_id'
        );
    }

    public function assignedClients(): HasMany
    {
        return $this->hasMany(
            Client::class,
            'assigned_to'
        );
    }

    public function createdClients(): HasMany
    {
        return $this->hasMany(
            Client::class,
            'created_by'
        );
    }

    public function convertedLeads(): HasMany
    {
        return $this->hasMany(
            Lead::class,
            'converted_by'
        );
    }

    /**
     * Admin role ko hamesha full access milega.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role?->isAdminRole() ?? false;
    }

    /**
     * User ke paas ek particular permission hai ya nahi.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        return $this->role
            ->permissions
            ->contains('slug', $permissionSlug);
    }

    /**
     * Multiple permissions me se kam se kam ek permission check karega.
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $permissionSlug) {
            if ($this->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }
}