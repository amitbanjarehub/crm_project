<?php

namespace App\Modules\TimeTracking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Models\Project;
use App\Modules\Role\Models\Role;
use App\Modules\TimeTracking\Models\TimeEntry;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeTrackingReportController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = $request->user();

        $canViewAll =
            $loggedInUser->isSuperAdmin()
            || $loggedInUser->hasPermission(
                'time_tracking.view_all'
            );

        $canViewTeam =
            $canViewAll
            || $loggedInUser->hasPermission(
                'time_tracking.view_team'
            );

        abort_unless(
            $canViewTeam,
            403,
            'You are not authorized to view team time reports.'
        );

        $validated = $request->validate([
            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],

            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'role_id' => [
                'nullable',
                'integer',
                'exists:roles,id',
            ],

            'project_id' => [
                'nullable',
                'integer',
                'exists:projects,id',
            ],
        ]);

        $dateFrom = Carbon::parse(
            $validated['date_from']
            ?? now()->startOfMonth()->toDateString()
        )->startOfDay();

        $dateTo = Carbon::parse(
            $validated['date_to']
            ?? today()->toDateString()
        )->endOfDay();

        abort_if(
            $dateFrom->diffInDays($dateTo) > 366,
            422,
            'Report date range cannot exceed 366 days.'
        );

        $query = TimeEntry::query()
            ->with([
                'user:id,name,email,role_id',
                'role:id,name',
                'task:id,title,status',
                'project:id,project_code,name,project_manager_id',
                'projectService:id,name',
            ])
            ->whereBetween(
                'started_at',
                [
                    $dateFrom,
                    $dateTo,
                ]
            );

        /*
         * Project Manager ko sirf apne managed
         * projects ka team report milega.
         */
        if (!$canViewAll) {
            $query->whereHas(
                'project',
                fn($projectQuery) =>
                    $projectQuery->where(
                        'project_manager_id',
                        $loggedInUser->id
                    )
            );
        }

        if (!empty($validated['user_id'])) {
            $query->where(
                'user_id',
                $validated['user_id']
            );
        }

        if (!empty($validated['role_id'])) {
            $query->where(
                'role_id',
                $validated['role_id']
            );
        }

        if (!empty($validated['project_id'])) {
            $query->where(
                'project_id',
                $validated['project_id']
            );
        }

        $entries = $query
            ->latest('started_at')
            ->get();

        $totalSeconds = (int)
            $entries->sum(
                fn(TimeEntry $entry) =>
                    $entry->liveSeconds()
            );

        $userSummary = $entries
            ->groupBy(
                fn(TimeEntry $entry) =>
                    $entry->user_id
                    ?? "deleted-{$entry->user_name_snapshot}"
            )
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'name' =>
                        $first->user?->name
                        ?? $first->user_name_snapshot
                        ?? 'Deleted User',

                    'role' =>
                        $first->role?->name
                        ?? $first->role_name_snapshot
                        ?? 'No Role',

                    'seconds' => (int)
                        $group->sum(
                            fn(TimeEntry $entry) =>
                                $entry->liveSeconds()
                        ),

                    'sessions' =>
                        $group->count(),
                ];
            })
            ->sortByDesc('seconds')
            ->values();

        $roleSummary = $entries
            ->groupBy(
                fn(TimeEntry $entry) =>
                    $entry->role_id
                    ?? $entry->role_name_snapshot
                    ?? 'no-role'
            )
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'role' =>
                        $first->role?->name
                        ?? $first->role_name_snapshot
                        ?? 'No Role',

                    'seconds' => (int)
                        $group->sum(
                            fn(TimeEntry $entry) =>
                                $entry->liveSeconds()
                        ),

                    'sessions' =>
                        $group->count(),
                ];
            })
            ->sortByDesc('seconds')
            ->values();

        $projectSummary = $entries
            ->groupBy('project_id')
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'project' =>
                        $first->project?->name
                        ?? 'Deleted Project',

                    'code' =>
                        $first->project?->project_code
                        ?? '-',

                    'seconds' => (int)
                        $group->sum(
                            fn(TimeEntry $entry) =>
                                $entry->liveSeconds()
                        ),

                    'sessions' =>
                        $group->count(),
                ];
            })
            ->sortByDesc('seconds')
            ->values();

        $projects = $canViewAll
            ? Project::query()
                ->orderBy('name')
                ->get([
                    'id',
                    'project_code',
                    'name',
                ])
            : Project::query()
                ->where(
                    'project_manager_id',
                    $loggedInUser->id
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'project_code',
                    'name',
                ]);

        $users = $canViewAll
            ? User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ])
            : User::query()
                ->where(function ($userQuery) use (
                    $loggedInUser
                ) {
                    $userQuery
                        ->whereHas(
                            'projects',
                            fn($projectQuery) =>
                                $projectQuery->where(
                                    'projects.project_manager_id',
                                    $loggedInUser->id
                                )
                        )
                        ->orWhereKey(
                            $loggedInUser->id
                        );
                })
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ]);

        return view('timetracking::report', [
            'entries' => $entries,
            'totalSeconds' => $totalSeconds,
            'userSummary' => $userSummary,
            'roleSummary' => $roleSummary,
            'projectSummary' => $projectSummary,
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'projects' => $projects,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filters' => $validated,
            'pageTitle' => 'Time Tracking Report',
        ]);
    }
}