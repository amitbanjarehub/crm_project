<?php

namespace App\Modules\Project\Support;

use App\Modules\Project\Models\Project;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;

trait AuthorizesProjectAccess
{
    protected function canViewAllProjects(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission('projects.view_all');
    }

    protected function canViewAllTasks(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission('tasks.view_all');
    }

    protected function ensureCanAccessProject(
        User $user,
        Project $project
    ): void {
        if ($this->canViewAllProjects($user)) {
            return;
        }

        if ($project->isManager($user)) {
            return;
        }

        if ($project->hasMember($user)) {
            return;
        }

        abort(
            403,
            'You are not authorized to access this project.'
        );
    }

    protected function ensureCanAccessTask(
        User $user,
        Task $task
    ): void {
        if ($this->canViewAllTasks($user)) {
            return;
        }

        if ((int) $task->assigned_to === (int) $user->id) {
            return;
        }

        $this->ensureCanAccessProject(
            $user,
            $task->project
        );
    }

    protected function ensureCanModifyTask(
        User $user,
        Task $task
    ): void {
        if ($this->canViewAllTasks($user)) {
            return;
        }

        if ($task->project->isManager($user)) {
            return;
        }

        if ((int) $task->assigned_to === (int) $user->id) {
            return;
        }

        abort(
            403,
            'You are not authorized to modify this task.'
        );
    }

    protected function ensureCanManageTaskDependencies(
        User $user,
        Task $task
    ): void {
        /*
         * Super Admin always allowed.
         */
        if ($user->isSuperAdmin()) {
            return;
        }

        /*
         * Required permission hona chahiye.
         */
        abort_unless(
            $user->hasPermission(
                'tasks.manage_dependencies'
            ),
            403,
            'You are not authorized to manage task dependencies.'
        );

        /*
         * User ko Project access bhi hona chahiye.
         */
        $this->ensureCanAccessProject(
            $user,
            $task->project
        );
    }
}