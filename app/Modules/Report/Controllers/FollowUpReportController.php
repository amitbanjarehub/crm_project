<?php

namespace App\Modules\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FollowUpReportController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = $request->user();

        [$dateFrom, $dateTo] =
            $this->resolveDateRange($request);

        /*
         * Pagination
         */
        $allowedPerPage = [
            10,
            25,
            50,
            100,
        ];

        $perPage = (int) $request->query(
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

        /*
         * Filters
         */
        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $type = trim(
            (string) $request->query(
                'type',
                ''
            )
        );

        $outcome = trim(
            (string) $request->query(
                'outcome',
                ''
            )
        );

        $performedBy = (int) $request->query(
            'performed_by',
            0
        );

        $due = trim(
            (string) $request->query(
                'due',
                'all'
            )
        );

        if (
            $type !== ''
            && !array_key_exists(
                $type,
                FollowUp::types()
            )
        ) {
            $type = '';
        }

        if (
            $outcome !== ''
            && !array_key_exists(
                $outcome,
                FollowUp::outcomes()
            )
        ) {
            $outcome = '';
        }

        $allowedDueFilters = [
            'all',
            'overdue',
            'today',
            'upcoming',
            'no_schedule',
        ];

        if (
            !in_array(
                $due,
                $allowedDueFilters,
                true
            )
        ) {
            $due = 'all';
        }

        /*
         * --------------------------------------------------
         * ACTIVITY REPORT
         *
         * Selected date range me actual completed
         * follow-up activities.
         * --------------------------------------------------
         */
        $activityQuery =
            $this->accessibleFollowUpsQuery(
                $loggedInUser
            )
                ->whereBetween(
                    'followed_up_at',
                    [
                        $dateFrom,
                        $dateTo,
                    ]
                );

        $this->applyCommonFilters(
            $activityQuery,
            $search,
            $type,
            $outcome,
            $performedBy
        );

        $totalActivities =
            (clone $activityQuery)->count();

        $uniqueLeads =
            (clone $activityQuery)
                ->distinct()
                ->count('lead_id');

        $positiveOutcomes =
            (clone $activityQuery)
                ->whereIn(
                    'outcome',
                    [
                        'interested',
                        'meeting_scheduled',
                        'qualified',
                        'converted',
                    ]
                )
                ->count();

        $convertedOutcomes =
            (clone $activityQuery)
                ->where(
                    'outcome',
                    'converted'
                )
                ->count();

        $noResponseCount =
            (clone $activityQuery)
                ->where(
                    'outcome',
                    'no_response'
                )
                ->count();

        $positiveOutcomeRate =
            $totalActivities > 0
                ? (int) round(
                    (
                        $positiveOutcomes
                        / $totalActivities
                    ) * 100
                )
                : 0;

        /*
         * Type and outcome distribution.
         */
        $typeCounts = $this->groupedCounts(
            clone $activityQuery,
            'type',
            FollowUp::types()
        );

        $outcomeCounts = $this->groupedCounts(
            clone $activityQuery,
            'outcome',
            FollowUp::outcomes()
        );

        /*
         * --------------------------------------------------
         * EMPLOYEE PERFORMANCE
         * --------------------------------------------------
         */
        $employeeStats =
            (clone $activityQuery)
                ->select('user_id')
                ->selectRaw(
                    'COUNT(*) as total_follow_ups'
                )
                ->selectRaw(
                    'COUNT(DISTINCT lead_id) as unique_leads'
                )
                ->selectRaw("
                    SUM(
                        CASE
                            WHEN outcome IN (
                                'interested',
                                'meeting_scheduled',
                                'qualified',
                                'converted'
                            )
                            THEN 1
                            ELSE 0
                        END
                    ) as positive_outcomes
                ")
                ->selectRaw("
                    SUM(
                        CASE
                            WHEN outcome = 'converted'
                            THEN 1
                            ELSE 0
                        END
                    ) as conversions
                ")
                ->selectRaw("
                    SUM(
                        CASE
                            WHEN outcome = 'no_response'
                            THEN 1
                            ELSE 0
                        END
                    ) as no_responses
                ")
                ->groupBy('user_id')
                ->orderByDesc('total_follow_ups')
                ->get();

        $employeeIds = $employeeStats
            ->pluck('user_id')
            ->filter()
            ->map(
                fn($id) => (int) $id
            )
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

        $employeePerformance =
            $employeeStats->map(
                function ($row) use ($employees) {
                    $userId = $row->user_id
                        ? (int) $row->user_id
                        : null;

                    $user = $userId
                        ? $employees->get($userId)
                        : null;

                    $total = (int)
                        $row->total_follow_ups;

                    $positive = (int)
                        $row->positive_outcomes;

                    return [
                        'user_id' => $userId,

                        'name' =>
                            $user?->name
                            ?? 'Deleted User',

                        'email' =>
                            $user?->email,

                        'role' =>
                            $user?->role?->name
                            ?? 'No Role',

                        'total_follow_ups' =>
                            $total,

                        'unique_leads' =>
                            (int) $row->unique_leads,

                        'positive_outcomes' =>
                            $positive,

                        'conversions' =>
                            (int) $row->conversions,

                        'no_responses' =>
                            (int) $row->no_responses,

                        'positive_rate' =>
                            $total > 0
                                ? (int) round(
                                    (
                                        $positive
                                        / $total
                                    ) * 100
                                )
                                : 0,
                    ];
                }
            );

        /*
         * --------------------------------------------------
         * CURRENT FOLLOW-UP SCHEDULE
         *
         * Har lead ka latest follow-up record.
         * Old historical schedule report me duplicate
         * overdue nahi banegi.
         * --------------------------------------------------
         */
        $scheduleBaseQuery =
            $this->latestAccessibleFollowUpsQuery(
                $loggedInUser
            )
                ->whereHas(
                    'lead',
                    function (Builder $leadQuery) {
                        $leadQuery->whereNotIn(
                            'status',
                            [
                                'converted',
                                'lost',
                            ]
                        );
                    }
                );

        $this->applyCommonFilters(
            $scheduleBaseQuery,
            $search,
            $type,
            $outcome,
            $performedBy
        );

        /*
         * Current schedule summary.
         */
        $overdueSchedules =
            (clone $scheduleBaseQuery)
                ->whereNotNull(
                    'next_follow_up_at'
                )
                ->where(
                    'next_follow_up_at',
                    '<',
                    now()
                )
                ->count();

        $dueTodaySchedules =
            (clone $scheduleBaseQuery)
                ->whereBetween(
                    'next_follow_up_at',
                    [
                        now(),
                        today()->endOfDay(),
                    ]
                )
                ->count();

        $upcomingSchedules =
            (clone $scheduleBaseQuery)
                ->where(
                    'next_follow_up_at',
                    '>',
                    today()->endOfDay()
                )
                ->count();

        $noScheduleCount =
            (clone $scheduleBaseQuery)
                ->whereNull(
                    'next_follow_up_at'
                )
                ->count();

        /*
         * Due filter only current schedule table par
         * apply hoga.
         */
        $scheduleQuery =
            clone $scheduleBaseQuery;

        $this->applyDueFilter(
            $scheduleQuery,
            $due
        );

        $followUps = $scheduleQuery
            ->with([
                'lead:id,name,phone,email,company,status,assigned_to',

                'lead.assignedUser:id,name,email',

                'lead.client:id,lead_id,name,company,status',

                'user:id,name,email',
            ])
            ->orderByRaw(
                'next_follow_up_at IS NULL'
            )
            ->orderBy(
                'next_follow_up_at'
            )
            ->paginate($perPage)
            ->withQueryString();

        /*
         * Performed By dropdown me sirf accessible
         * follow-up users show honge.
         */
        $accessibleUserIds =
            $this->accessibleFollowUpsQuery(
                $loggedInUser
            )
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id');

        $users = User::query()
            ->whereIn(
                'id',
                $accessibleUserIds
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        return view(
            'report::followups.index',
            [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,

                'followUps' => $followUps,
                'employeePerformance' =>
                    $employeePerformance,

                'types' => FollowUp::types(),
                'outcomes' => FollowUp::outcomes(),

                'typeCounts' => $typeCounts,
                'outcomeCounts' => $outcomeCounts,

                'typeCountMax' => max(
                    1,
                    ...array_values($typeCounts)
                ),

                'outcomeCountMax' => max(
                    1,
                    ...array_values($outcomeCounts)
                ),

                'totalActivities' =>
                    $totalActivities,

                'uniqueLeads' =>
                    $uniqueLeads,

                'positiveOutcomeRate' =>
                    $positiveOutcomeRate,

                'convertedOutcomes' =>
                    $convertedOutcomes,

                'noResponseCount' =>
                    $noResponseCount,

                'overdueSchedules' =>
                    $overdueSchedules,

                'dueTodaySchedules' =>
                    $dueTodaySchedules,

                'upcomingSchedules' =>
                    $upcomingSchedules,

                'noScheduleCount' =>
                    $noScheduleCount,

                'users' => $users,

                'search' => $search,
                'type' => $type,
                'outcome' => $outcome,
                'performedBy' => $performedBy,
                'due' => $due,
                'perPage' => $perPage,

                'canViewAll' =>
                    $this->canViewAllFollowUpReports(
                        $loggedInUser
                    ),

                'pageTitle' =>
                    'Follow-up Reports',
            ]
        );
    }

    /**
     * Admin/View All ko sab follow-ups.
     * Normal employee ko assigned leads ke follow-ups.
     */
    private function accessibleFollowUpsQuery(
        User $user
    ): Builder {
        $query = FollowUp::query();

        if (
            $this->canViewAllFollowUpReports(
                $user
            )
        ) {
            return $query;
        }

        return $query->whereHas(
            'lead',
            function (Builder $leadQuery) use ($user) {
                $leadQuery->where(
                    'assigned_to',
                    $user->id
                );
            }
        );
    }

    /**
     * Har lead ka latest follow-up record.
     */
    private function latestAccessibleFollowUpsQuery(
        User $user
    ): Builder {
        $latestFollowUpIds =
            FollowUp::query()
                ->selectRaw('MAX(id)')
                ->groupBy('lead_id');

        $query = FollowUp::query()
            ->whereIn(
                'id',
                $latestFollowUpIds
            );

        if (
            $this->canViewAllFollowUpReports(
                $user
            )
        ) {
            return $query;
        }

        return $query->whereHas(
            'lead',
            function (Builder $leadQuery) use ($user) {
                $leadQuery->where(
                    'assigned_to',
                    $user->id
                );
            }
        );
    }

    private function canViewAllFollowUpReports(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission(
                'reports.followups.view_all'
            );
    }

    private function applyCommonFilters(
        Builder $query,
        string $search,
        string $type,
        string $outcome,
        int $performedBy
    ): void {
        if ($search !== '') {
            $query->whereHas(
                'lead',
                function (
                    Builder $leadQuery
                ) use ($search) {
                    $leadQuery->where(
                        function (
                            Builder $searchQuery
                        ) use ($search) {
                            $searchQuery
                                ->where(
                                    'name',
                                    'LIKE',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'phone',
                                    'LIKE',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'LIKE',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'company',
                                    'LIKE',
                                    "%{$search}%"
                                );

                            if (is_numeric($search)) {
                                $searchQuery->orWhere(
                                    'id',
                                    (int) $search
                                );
                            }
                        }
                    );
                }
            );
        }

        if ($type !== '') {
            $query->where(
                'type',
                $type
            );
        }

        if ($outcome !== '') {
            $query->where(
                'outcome',
                $outcome
            );
        }

        if ($performedBy > 0) {
            $query->where(
                'user_id',
                $performedBy
            );
        }
    }

    private function applyDueFilter(
        Builder $query,
        string $due
    ): void {
        switch ($due) {
            case 'overdue':
                $query
                    ->whereNotNull(
                        'next_follow_up_at'
                    )
                    ->where(
                        'next_follow_up_at',
                        '<',
                        now()
                    );
                break;

            case 'today':
                $query->whereBetween(
                    'next_follow_up_at',
                    [
                        now(),
                        today()->endOfDay(),
                    ]
                );
                break;

            case 'upcoming':
                $query->where(
                    'next_follow_up_at',
                    '>',
                    today()->endOfDay()
                );
                break;

            case 'no_schedule':
                $query->whereNull(
                    'next_follow_up_at'
                );
                break;
        }
    }

    private function groupedCounts(
        Builder $query,
        string $column,
        array $availableValues
    ): array {
        $result = array_fill_keys(
            array_keys($availableValues),
            0
        );

        $counts = $query
            ->select($column)
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->groupBy($column)
            ->pluck(
                'total',
                $column
            );

        foreach (
            $counts as $value => $count
        ) {
            if (
                array_key_exists(
                    $value,
                    $result
                )
            ) {
                $result[$value] =
                    (int) $count;
            }
        }

        return $result;
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