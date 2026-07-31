<?php

namespace App\Modules\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Client\Models\Client;
use App\Modules\Project\Models\Project;
use App\Modules\Report\Support\AuthorizesReportAccess;
use App\Modules\Task\Models\Task;
use App\Modules\TimeTracking\Models\TimeEntry;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectReportController extends Controller
{
    use AuthorizesReportAccess;

    public function index(Request $request)
    {
        $loggedInUser = $request->user();

        $allowedPerPage = [
            10,
            25,
            50,
            100,
        ];

        $perPage = (int)
            $request->query(
                'per_page',
                10
            );

        if (
            !in_array(
                $perPage,
                $allowedPerPage,
                true
            )
        ) {
            $perPage = 10;
        }

        $search = trim(
            $request->query(
                'search',
                ''
            )
        );

        $status = trim(
            $request->query(
                'status',
                ''
            )
        );

        $priority = trim(
            $request->query(
                'priority',
                ''
            )
        );

        $managerId = (int)
            $request->query(
                'manager_id',
                0
            );

        $clientId = (int)
            $request->query(
                'client_id',
                0
            );

        $query =
            $this->accessibleProjectsQuery(
                $loggedInUser
            )
                ->with([
                    'client:id,name,company',
                    'manager:id,name,email',
                ])
                ->withCount([
                    'tasks as total_tasks' =>
                        fn($taskQuery) =>
                            $taskQuery->where(
                                'status',
                                '!=',
                                'cancelled'
                            ),

                    'tasks as completed_tasks' =>
                        fn($taskQuery) =>
                            $taskQuery->where(
                                'status',
                                'completed'
                            ),

                    'tasks as overdue_tasks' =>
                        fn($taskQuery) =>
                            $taskQuery
                                ->whereNotNull(
                                    'due_at'
                                )
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
                                ),
                ])
                ->withSum(
                    [
                        'tasks as estimated_hours' =>
                            fn($taskQuery) =>
                                $taskQuery->where(
                                    'status',
                                    '!=',
                                    'cancelled'
                                ),
                    ],
                    'estimated_hours'
                )
                ->withSum(
                    'timeEntries as tracked_seconds',
                    'total_seconds'
                );

        if ($search !== '') {
            $query->where(
                function (Builder $searchQuery) use (
                    $search
                ) {
                    $searchQuery
                        ->where(
                            'project_code',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'name',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'client',
                            fn(Builder $clientQuery) =>
                                $clientQuery
                                    ->where(
                                        'name',
                                        'LIKE',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'company',
                                        'LIKE',
                                        "%{$search}%"
                                    )
                        );
                }
            );
        }

        if (
            $status !== ''
            && array_key_exists(
                $status,
                Project::statuses()
            )
        ) {
            $query->where(
                'status',
                $status
            );
        }

        if (
            $priority !== ''
            && array_key_exists(
                $priority,
                Project::priorities()
            )
        ) {
            $query->where(
                'priority',
                $priority
            );
        }

        if ($managerId > 0) {
            $query->where(
                'project_manager_id',
                $managerId
            );
        }

        if ($clientId > 0) {
            $query->where(
                'client_id',
                $clientId
            );
        }

        /*
         * Current filters ke according summary.
         */
        $summaryQuery = clone $query;

        $summary = [
            'total' =>
                (clone $summaryQuery)->count(),

            'active' =>
                (clone $summaryQuery)
                    ->where(
                        'status',
                        'active'
                    )
                    ->count(),

            'completed' =>
                (clone $summaryQuery)
                    ->where(
                        'status',
                        'completed'
                    )
                    ->count(),

            'on_hold' =>
                (clone $summaryQuery)
                    ->where(
                        'status',
                        'on_hold'
                    )
                    ->count(),

            'delayed' =>
                (clone $summaryQuery)
                    ->whereNotNull(
                        'due_date'
                    )
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
                    ->count(),
        ];

        $projects = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        /*
         * Running timers ke current live seconds
         * withSum me included nahi hote.
         */
        $pageProjectIds = $projects
            ->getCollection()
            ->pluck('id');

        $liveTimerAdjustments =
            TimeEntry::query()
                ->whereIn(
                    'project_id',
                    $pageProjectIds
                )
                ->where(
                    'status',
                    'running'
                )
                ->whereNotNull(
                    'last_started_at'
                )
                ->get([
                    'project_id',
                    'last_started_at',
                ])
                ->groupBy('project_id')
                ->map(
                    fn($entries) =>
                        (int) $entries->sum(
                            fn(TimeEntry $entry) =>
                                max(
                                    0,
                                    (int) $entry
                                        ->last_started_at
                                        ->diffInSeconds(
                                            now(),
                                            false
                                        )
                                )
                        )
                );

        $projects->setCollection(
            $projects
                ->getCollection()
                ->map(
                    function (Project $project) use (
                        $liveTimerAdjustments
                    ) {
                        $project->tracked_seconds =
                            (int) (
                                $project
                                    ->tracked_seconds
                                ?? 0
                            )
                            + (int) (
                                $liveTimerAdjustments[
                                    $project->id
                                ] ?? 0
                            );

                        $project->report_progress =
                            $project->total_tasks > 0
                                ? (int) round(
                                    (
                                        $project
                                            ->completed_tasks
                                        / $project
                                            ->total_tasks
                                    ) * 100
                                )
                                : 0;

                        return $project;
                    }
                )
        );

        /*
         * Filter dropdown values sirf accessible
         * projects se niklenge.
         */
        $accessibleFilterQuery =
            $this->accessibleProjectsQuery(
                $loggedInUser
            );

        $managerIds =
            (clone $accessibleFilterQuery)
                ->whereNotNull(
                    'project_manager_id'
                )
                ->distinct()
                ->pluck(
                    'project_manager_id'
                );

        $clientIds =
            (clone $accessibleFilterQuery)
                ->distinct()
                ->pluck('client_id');

        return view(
            'report::projects.index',
            [
                'projects' => $projects,
                'summary' => $summary,

                'managers' =>
                    User::query()
                        ->whereIn(
                            'id',
                            $managerIds
                        )
                        ->orderBy('name')
                        ->get([
                            'id',
                            'name',
                            'email',
                        ]),

                'clients' =>
                    Client::query()
                        ->whereIn(
                            'id',
                            $clientIds
                        )
                        ->orderBy('name')
                        ->get([
                            'id',
                            'name',
                            'company',
                        ]),

                'statuses' =>
                    Project::statuses(),

                'priorities' =>
                    Project::priorities(),

                'search' => $search,
                'status' => $status,
                'priority' => $priority,
                'managerId' => $managerId,
                'clientId' => $clientId,
                'perPage' => $perPage,

                'pageTitle' =>
                    'Project Reports',
            ]
        );
    }

    public function show(
        Request $request,
        Project $project
    ) {
        $this->ensureCanAccessProjectReport(
            $request->user(),
            $project
        );

        [$dateFrom, $dateTo] =
            $this->resolveDateRange($request);

        $project->load([
            'client:id,name,company,email,phone',

            'manager:id,name,email,role_id',

            'manager.role:id,name',

            'members:id,name,email,role_id',

            'members.role:id,name',

            'services' =>
                fn($query) =>
                    $query
                        ->orderBy('sort_order')
                        ->orderBy('id'),
        ]);

        /*
         * Project ki current tasks.
         */
        $tasks = Task::query()
            ->where(
                'project_id',
                $project->id
            )
            ->with([
                'assignedUser:id,name,email,role_id',

                'assignedUser.role:id,name',

                'projectService:id,name',
            ])
            ->orderBy('due_at')
            ->get();

        /*
         * Selected date period ki time entries.
         */
        $timeEntries = TimeEntry::query()
            ->where(
                'project_id',
                $project->id
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
                'task:id,title',
                'projectService:id,name',
            ])
            ->get();

        $activeTimers = TimeEntry::query()
            ->active()
            ->where(
                'project_id',
                $project->id
            )
            ->count();

        $validTasks = $tasks->where(
            'status',
            '!=',
            'cancelled'
        );

        $completedTasks = $validTasks
            ->where(
                'status',
                'completed'
            )
            ->count();

        $overdueTasks = $validTasks
            ->filter(
                fn(Task $task) =>
                    $task->due_at
                    && $task->due_at->isPast()
                    && !$task->isClosed()
            )
            ->count();

        $estimatedSeconds = (int) round(
            $validTasks->sum(
                fn(Task $task) =>
                    (float) (
                        $task->estimated_hours
                        ?? 0
                    )
            ) * 3600
        );

        $trackedSeconds = (int)
            $timeEntries->sum(
                fn(TimeEntry $entry) =>
                    $entry->liveSeconds()
            );

        $taskStatusCounts = array_fill_keys(
            array_keys(Task::statuses()),
            0
        );

        foreach (
            $tasks->groupBy('status')
            as $status => $group
        ) {
            if (
                array_key_exists(
                    $status,
                    $taskStatusCounts
                )
            ) {
                $taskStatusCounts[$status] =
                    $group->count();
            }
        }

        $summary = [
            'total_tasks' =>
                $validTasks->count(),

            'completed_tasks' =>
                $completedTasks,

            'overdue_tasks' =>
                $overdueTasks,

            'blocked_tasks' =>
                $taskStatusCounts['blocked']
                ?? 0,

            'in_review_tasks' =>
                $taskStatusCounts['in_review']
                ?? 0,

            'progress_percent' =>
                $validTasks->count() > 0
                    ? (int) round(
                        (
                            $completedTasks
                            / $validTasks->count()
                        ) * 100
                    )
                    : 0,

            'estimated_seconds' =>
                $estimatedSeconds,

            'tracked_seconds' =>
                $trackedSeconds,

            'variance_seconds' =>
                $trackedSeconds
                - $estimatedSeconds,

            'active_timers' =>
                $activeTimers,
        ];

        /*
         * Task-wise tracked time.
         */
        $timeByTask = $timeEntries
            ->whereNotNull('task_id')
            ->groupBy('task_id')
            ->map(
                fn($entries) =>
                    (int) $entries->sum(
                        fn(TimeEntry $entry) =>
                            $entry->liveSeconds()
                    )
            );

        $taskRows = $tasks->map(
            function (Task $task) use (
                $timeByTask
            ) {
                $estimatedSeconds =
                    (int) round(
                        (float) (
                            $task->estimated_hours
                            ?? 0
                        ) * 3600
                    );

                $trackedSeconds = (int) (
                    $timeByTask[$task->id]
                    ?? 0
                );

                return [
                    'task' => $task,

                    'estimated_seconds' =>
                        $estimatedSeconds,

                    'tracked_seconds' =>
                        $trackedSeconds,

                    'variance_seconds' =>
                        $trackedSeconds
                        - $estimatedSeconds,

                    'is_overdue' =>
                        $task->due_at
                        && $task->due_at
                            ->isPast()
                        && !$task->isClosed(),
                ];
            }
        );

        /*
         * Service-wise report.
         */
        $timeByService = $timeEntries
            ->whereNotNull(
                'project_service_id'
            )
            ->groupBy(
                'project_service_id'
            );

        $serviceRows = $project
            ->services
            ->map(
                function ($service) use (
                    $tasks,
                    $timeByService
                ) {
                    $serviceTasks =
                        $tasks->where(
                            'project_service_id',
                            $service->id
                        );

                    $validServiceTasks =
                        $serviceTasks->where(
                            'status',
                            '!=',
                            'cancelled'
                        );

                    $completed =
                        $validServiceTasks
                            ->where(
                                'status',
                                'completed'
                            )
                            ->count();

                    $estimatedSeconds =
                        (int) round(
                            $validServiceTasks
                                ->sum(
                                    fn(Task $task) =>
                                        (float) (
                                            $task
                                                ->estimated_hours
                                            ?? 0
                                        )
                                ) * 3600
                        );

                    $serviceEntries =
                        $timeByService->get(
                            $service->id,
                            collect()
                        );

                    $trackedSeconds =
                        (int) $serviceEntries
                            ->sum(
                                fn(TimeEntry $entry) =>
                                    $entry
                                        ->liveSeconds()
                            );

                    return [
                        'service' => $service,

                        'total_tasks' =>
                            $validServiceTasks
                                ->count(),

                        'completed_tasks' =>
                            $completed,

                        'progress_percent' =>
                            $validServiceTasks
                                ->count() > 0
                                ? (int) round(
                                    (
                                        $completed
                                        / $validServiceTasks
                                            ->count()
                                    ) * 100
                                )
                                : 0,

                        'estimated_seconds' =>
                            $estimatedSeconds,

                        'tracked_seconds' =>
                            $trackedSeconds,

                        'variance_seconds' =>
                            $trackedSeconds
                            - $estimatedSeconds,
                    ];
                }
            );

        /*
         * Team report.
         */
        $teamUsers = $project
            ->members
            ->concat([
                $project->manager,
            ])
            ->filter()
            ->unique('id')
            ->values();

        $entriesByUser = $timeEntries
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        $teamRows = $teamUsers
            ->map(
                function (User $user) use (
                    $tasks,
                    $entriesByUser
                ) {
                    $assignedTasks =
                        $tasks->where(
                            'assigned_to',
                            $user->id
                        );

                    $validAssignedTasks =
                        $assignedTasks->where(
                            'status',
                            '!=',
                            'cancelled'
                        );

                    $completedTasks =
                        $validAssignedTasks
                            ->where(
                                'status',
                                'completed'
                            )
                            ->count();

                    $overdueTasks =
                        $validAssignedTasks
                            ->filter(
                                fn(Task $task) =>
                                    $task->due_at
                                    && $task->due_at
                                        ->isPast()
                                    && !$task->isClosed()
                            )
                            ->count();

                    $estimatedSeconds =
                        (int) round(
                            $validAssignedTasks
                                ->sum(
                                    fn(Task $task) =>
                                        (float) (
                                            $task
                                                ->estimated_hours
                                            ?? 0
                                        )
                                ) * 3600
                        );

                    $userEntries =
                        $entriesByUser->get(
                            $user->id,
                            collect()
                        );

                    $trackedSeconds =
                        (int) $userEntries
                            ->sum(
                                fn(TimeEntry $entry) =>
                                    $entry
                                        ->liveSeconds()
                            );

                    return [
                        'user' => $user,

                        'project_role' =>
                            $user->pivot
                                ?->member_role,

                        'assigned_tasks' =>
                            $validAssignedTasks
                                ->count(),

                        'completed_tasks' =>
                            $completedTasks,

                        'overdue_tasks' =>
                            $overdueTasks,

                        'estimated_seconds' =>
                            $estimatedSeconds,

                        'tracked_seconds' =>
                            $trackedSeconds,

                        'variance_seconds' =>
                            $trackedSeconds
                            - $estimatedSeconds,
                    ];
                }
            )
            ->sortByDesc(
                'tracked_seconds'
            )
            ->values();

        /*
         * Project Manager ka role manually correct karo.
         */
        $teamRows = $teamRows->map(
            function ($row) use ($project) {
                if (
                    (int) $row['user']->id
                    === (int) $project
                        ->project_manager_id
                ) {
                    $row['project_role'] =
                        'Project Manager';
                }

                $row['project_role'] =
                    $row['project_role']
                    ?: 'Team Member';

                return $row;
            }
        );

        $activities = $project
            ->activities()
            ->with(
                'user:id,name,email'
            )
            ->latest()
            ->limit(20)
            ->get();

        return view(
            'report::projects.show',
            [
                'project' => $project,

                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,

                'summary' => $summary,

                'taskStatuses' =>
                    Task::statuses(),

                'taskStatusCounts' =>
                    $taskStatusCounts,

                'taskStatusMax' =>
                    max(
                        1,
                        ...array_values(
                            $taskStatusCounts
                        )
                    ),

                'serviceRows' =>
                    $serviceRows,

                'teamRows' =>
                    $teamRows,

                'taskRows' =>
                    $taskRows,

                'activities' =>
                    $activities,

                'pageTitle' =>
                    'Project Report',
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
}