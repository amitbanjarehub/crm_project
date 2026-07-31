<?php

namespace App\Modules\Report\Support;

use App\Modules\Project\Models\Project;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait AuthorizesReportAccess
{
    protected function canViewAllExecutiveData(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission(
                'reports.executive.view_all'
            );
    }

    protected function canViewAllProjectReports(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission(
                'reports.projects.view_all'
            );
    }

    /**
     * User ko report me kaun-se projects milenge.
     */
    protected function accessibleProjectsQuery(
        User $user,
        bool $forExecutiveDashboard = false
    ): Builder {
        $query = Project::query();

        $canViewAll = $forExecutiveDashboard
            ? $this->canViewAllExecutiveData($user)
            : $this->canViewAllProjectReports($user);

        if ($canViewAll) {
            return $query;
        }

        return $query->where(
            function (Builder $projectQuery) use ($user) {
                $projectQuery
                    ->where(
                        'project_manager_id',
                        $user->id
                    )
                    ->orWhereHas(
                        'members',
                        fn(Builder $memberQuery) =>
                            $memberQuery->where(
                                'users.id',
                                $user->id
                            )
                    );
            }
        );
    }

    protected function ensureCanAccessProjectReport(
        User $user,
        Project $project
    ): void {
        if ($this->canViewAllProjectReports($user)) {
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
            'You are not authorized to view this project report.'
        );
    }
}