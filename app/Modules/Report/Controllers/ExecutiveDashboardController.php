<?php

namespace App\Modules\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\ProjectActivity;
use App\Modules\Report\Support\AuthorizesReportAccess;
use App\Modules\Task\Models\Task;
use App\Modules\TimeTracking\Models\TimeEntry;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExecutiveDashboardController extends Controller
{
    use AuthorizesReportAccess;

    public function index(Request $request)
    {
        [$dateFrom, $dateTo] =
            $this->resolveDateRange($request);

        $loggedInUser = $request->user();

        /*
         * Executive report ke accessible projects.
         */
        $projectQuery =
            $this->accessibleProjectsQuery(
                $loggedInUser,
                true
            );

        $projectIds = (clone $projectQuery)
            ->pluck('projects.id');

        /*
         * Current project status counts.
         */
        $projectStatusCounts =
            $this->statusCounts(
                clone $projectQuery,
                Project::statuses()
            );

        $totalProjects = array_sum(
            $projectStatusCounts
        );

        $activeProjects =
            $projectStatusCounts['active'] ?? 0;

        $completedProjects =
            $projectStatusCounts['completed'] ?? 0;

        $onHoldProjects =
            $projectStatusCounts['on_hold'] ?? 0;

        $delayedProjects = (clone $projectQuery)
            ->whereNotNull('due_date')
            ->whereDate(
                'due_date',
                '<',
                today()
            )
            ->whereNotIn(
                'status',
                [
                    'completed',
                    'cancelled',
                ]
            )
            ->count();

        $totalBudget = (float)
            (clone $projectQuery)
                ->sum('budget');

        /*
         * Accessible tasks.
         */
        $taskQuery = Task::query()
            ->whereIn(
                'project_id',
                $projectIds
            );

        $taskStatusCounts =
            $this->statusCounts(
                clone $taskQuery,
                Task::statuses()
            );

        $totalTasks = (clone $taskQuery)
            ->where(
                'status',
                '!=',
                'cancelled'
            )
            ->count();

        $completedTasks =
            $taskStatusCounts['completed'] ?? 0;

        $overdueTasks = (clone $taskQuery)
            ->whereNotNull('due_at')
            ->where(
                'due_at',
                '<',
                now()
            )
            ->whereNotIn(
                'status',
                [
                    'completed',
                    'cancelled',
                ]
            )
            ->count();

        $completionRate = $totalTasks > 0
            ? (int) round(
                ($completedTasks / $totalTasks)
                * 100
            )
            : 0;

        /*
         * Estimated effort current accessible tasks ka.
         */
        $estimatedHours = (float)
            (clone $taskQuery)
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->sum('estimated_hours');

        $estimatedSeconds = (int) round(
            $estimatedHours * 3600
        );

        /*
         * Selected date range ka actual tracked time.
         */
        $timeEntries = TimeEntry::query()
            ->whereIn(
                'project_id',
                $projectIds
            )
            ->whereBetween(
                'started_at',
                [
                    $dateFrom,
                    $dateTo,
                ]
            )
            ->with([
                'user:id,name,email,role_id',
                'user.role:id,name',
                'role:id,name',
                'project:id,project_code,name',
                'task:id,title',
            ])
            ->get();

        $trackedSeconds = (int)
            $timeEntries->sum(
                fn(TimeEntry $entry) =>
                    $entry->liveSeconds()
            );

        $timeVarianceSeconds =
            $trackedSeconds - $estimatedSeconds;

        $activeTimers = TimeEntry::query()
            ->active()
            ->whereIn(
                'project_id',
                $projectIds
            )
            ->count();

        /*
         * Delayed projects list.
         */
        $delayedProjectList =
            $this->accessibleProjectsQuery(
                $loggedInUser,
                true
            )
                ->with([
                    'client:id,name,company',
                    'manager:id,name,email',
                ])
                ->withCount([
                    'tasks as total_tasks' =>
                        fn($query) =>
                            $query->where(
                                'status',
                                '!=',
                                'cancelled'
                            ),

                    'tasks as completed_tasks' =>
                        fn($query) =>
                            $query->where(
                                'status',
                                'completed'
                            ),
                ])
                ->whereNotNull('due_date')
                ->whereDate(
                    'due_date',
                    '<',
                    today()
                )
                ->whereNotIn(
                    'status',
                    [
                        'completed',
                        'cancelled',
                    ]
                )
                ->orderBy('due_date')
                ->limit(8)
                ->get();

        /*
         * Upcoming seven-day task deadlines.
         */
        $upcomingTasks = Task::query()
            ->whereIn(
                'project_id',
                $projectIds
            )
            ->with([
                'project:id,project_code,name',
                'assignedUser:id,name,email',
            ])
            ->whereBetween(
                'due_at',
                [
                    now(),
                    now()->copy()
                        ->addDays(7)
                        ->endOfDay(),
                ]
            )
            ->whereNotIn(
                'status',
                [
                    'completed',
                    'cancelled',
                ]
            )
            ->orderBy('due_at')
            ->limit(10)
            ->get();

        /*
         * Selected period me employee-wise
         * completed task count.
         */
        $completedTasksByUser = Task::query()
            ->whereIn(
                'project_id',
                $projectIds
            )
            ->whereNotNull('assigned_to')
            ->whereBetween(
                'completed_at',
                [
                    $dateFrom,
                    $dateTo,
                ]
            )
            ->select('assigned_to')
            ->selectRaw(
                'COUNT(*) as completed_count'
            )
            ->groupBy('assigned_to')
            ->pluck(
                'completed_count',
                'assigned_to'
            );

        /*
         * Employee-wise actual tracked time.
         */
        $entriesByUser = $timeEntries
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        $employeeIds = collect(
            $completedTasksByUser->keys()
        )
            ->merge(
                $entriesByUser->keys()
            )
            ->map(
                fn($id) => (int) $id
            )
            ->unique()
            ->values();

        $employees = User::query()
            ->with('role:id,name')
            ->whereIn(
                'id',
                $employeeIds
            )
            ->get([
                'id',
                'name',
                'email',
                'role_id',
            ])
            ->keyBy('id');

        $employeePerformance = $employeeIds
            ->map(function ($userId) use (
                $employees,
                $entriesByUser,
                $completedTasksByUser
            ) {
                $user = $employees->get(
                    $userId
                );

                $userEntries =
                    $entriesByUser->get(
                        $userId,
                        collect()
                    );

                $firstEntry =
                    $userEntries->first();

                return [
                    'user_id' => $userId,

                    'name' =>
                        $user?->name
                        ?? $firstEntry
                            ?->user_name_snapshot
                        ?? 'Deleted User',

                    'role' =>
                        $user?->role?->name
                        ?? $firstEntry
                            ?->role_name_snapshot
                        ?? 'No Role',

                    'completed_tasks' =>
                        (int) (
                            $completedTasksByUser[
                                $userId
                            ] ?? 0
                        ),

                    'tracked_seconds' =>
                        (int) $userEntries->sum(
                            fn(TimeEntry $entry) =>
                                $entry->liveSeconds()
                        ),
                ];
            })
            ->sortByDesc('tracked_seconds')
            ->take(10)
            ->values();

        /*
         * Recent activity.
         */
        $recentActivities =
            ProjectActivity::query()
                ->whereIn(
                    'project_id',
                    $projectIds
                )
                ->with([
                    'project:id,project_code,name',
                    'user:id,name,email',
                ])
                ->latest()
                ->limit(12)
                ->get();

        $monthlyCompletionTrend =
            $this->monthlyCompletionTrend(
                $projectIds,
                $dateFrom,
                $dateTo
            );

        return view(
            'report::executive-dashboard',
            [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,

                'totalProjects' =>
                    $totalProjects,

                'activeProjects' =>
                    $activeProjects,

                'completedProjects' =>
                    $completedProjects,

                'onHoldProjects' =>
                    $onHoldProjects,

                'delayedProjects' =>
                    $delayedProjects,

                'totalBudget' =>
                    $totalBudget,

                'totalTasks' =>
                    $totalTasks,

                'completedTasks' =>
                    $completedTasks,

                'overdueTasks' =>
                    $overdueTasks,

                'completionRate' =>
                    $completionRate,

                'estimatedSeconds' =>
                    $estimatedSeconds,

                'trackedSeconds' =>
                    $trackedSeconds,

                'timeVarianceSeconds' =>
                    $timeVarianceSeconds,

                'activeTimers' =>
                    $activeTimers,

                'projectStatuses' =>
                    Project::statuses(),

                'projectStatusCounts' =>
                    $projectStatusCounts,

                'taskStatuses' =>
                    Task::statuses(),

                'taskStatusCounts' =>
                    $taskStatusCounts,

                'projectStatusMax' =>
                    max(
                        1,
                        ...array_values(
                            $projectStatusCounts
                        )
                    ),

                'taskStatusMax' =>
                    max(
                        1,
                        ...array_values(
                            $taskStatusCounts
                        )
                    ),

                'delayedProjectList' =>
                    $delayedProjectList,

                'upcomingTasks' =>
                    $upcomingTasks,

                'employeePerformance' =>
                    $employeePerformance,

                'monthlyCompletionTrend' =>
                    $monthlyCompletionTrend,

                'recentActivities' =>
                    $recentActivities,

                'pageTitle' =>
                    'Executive Dashboard Report',
            ]
        );
    }

    private function resolveDateRange(
        Request $request
    ): array {
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
        ]);

        $dateFrom = Carbon::parse(
            $validated['date_from']
            ?? now()
                ->startOfMonth()
                ->toDateString()
        )->startOfDay();

        $dateTo = Carbon::parse(
            $validated['date_to']
            ?? today()->toDateString()
        )->endOfDay();

        if (
            $dateFrom->diffInDays(
                $dateTo
            ) > 366
        ) {
            throw ValidationException::withMessages([
                'date_to' =>
                    'Report date range cannot exceed 366 days.',
            ]);
        }

        return [
            $dateFrom,
            $dateTo,
        ];
    }

    private function statusCounts(
        Builder $query,
        array $availableStatuses
    ): array {
        $result = array_fill_keys(
            array_keys($availableStatuses),
            0
        );

        $counts = $query
            ->select('status')
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->groupBy('status')
            ->pluck(
                'total',
                'status'
            );

        foreach ($counts as $status => $count) {
            if (
                array_key_exists(
                    $status,
                    $result
                )
            ) {
                $result[$status] =
                    (int) $count;
            }
        }

        return $result;
    }

    private function monthlyCompletionTrend(
        $projectIds,
        Carbon $dateFrom,
        Carbon $dateTo
    ) {
        $completedTasks = Task::query()
            ->whereIn(
                'project_id',
                $projectIds
            )
            ->whereBetween(
                'completed_at',
                [
                    $dateFrom,
                    $dateTo,
                ]
            )
            ->get([
                'id',
                'completed_at',
            ])
            ->groupBy(
                fn(Task $task) =>
                    $task->completed_at
                        ?->format('Y-m')
            );

        $months = collect();

        $cursor = $dateFrom
            ->copy()
            ->startOfMonth();

        $lastMonth = $dateTo
            ->copy()
            ->startOfMonth();

        while (
            $cursor->lte($lastMonth)
            && $months->count() < 13
        ) {
            $monthKey =
                $cursor->format('Y-m');

            $months->push([
                'key' => $monthKey,

                'label' =>
                    $cursor->format('M Y'),

                'count' =>
                    $completedTasks
                        ->get(
                            $monthKey,
                            collect()
                        )
                        ->count(),
            ]);

            $cursor->addMonth();
        }

        $maximum = max(
            1,
            (int) $months->max('count')
        );

        return $months->map(
            function ($month) use ($maximum) {
                $month['width'] = (int) round(
                    (
                        $month['count']
                        / $maximum
                    ) * 100
                );

                return $month;
            }
        );
    }
}