<?php

namespace App\Modules\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lead\Models\Lead;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Modules\Lead\Models\LeadStatus;

class LeadReportController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = $request->user();

        $canViewAll = $this->canViewAllLeadReports(
            $loggedInUser
        );

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

        $status = trim(
            (string) $request->query(
                'status',
                ''
            )
        );

        $source = trim(
            (string) $request->query(
                'source',
                ''
            )
        );

        $priority = trim(
            (string) $request->query(
                'priority',
                ''
            )
        );

        $assignedTo = (int) $request->query(
            'assigned_to',
            0
        );

        $conversion = trim(
            (string) $request->query(
                'conversion',
                'all'
            )
        );

        $followUpState = trim(
            (string) $request->query(
                'follow_up_state',
                'all'
            )
        );

        /*
         * Invalid filter values reset honge.
         */
        if (
            $status !== ''
            && !array_key_exists(
                $status,
                Lead::statuses()
            )
        ) {
            $status = '';
        }

        if (
            $source !== ''
            && !array_key_exists(
                $source,
                Lead::sources()
            )
        ) {
            $source = '';
        }

        if (
            $priority !== ''
            && !array_key_exists(
                $priority,
                Lead::priorities()
            )
        ) {
            $priority = '';
        }

        if (
            !in_array(
                $conversion,
                [
                    'all',
                    'converted',
                    'not_converted',
                ],
                true
            )
        ) {
            $conversion = 'all';
        }

        if (
            !in_array(
                $followUpState,
                [
                    'all',
                    'overdue',
                    'today',
                    'upcoming',
                    'no_schedule',
                ],
                true
            )
        ) {
            $followUpState = 'all';
        }

        if (!$canViewAll) {
            $assignedTo = 0;
        }

        $newStatusSlug =
            LeadStatus::systemSlug(
                'new'
            );

        $qualifiedStatusSlug =
            LeadStatus::systemSlug(
                'qualified'
            );

        $convertedStatusSlug =
            LeadStatus::systemSlug(
                'converted'
            );

        $lostStatusSlug =
            LeadStatus::systemSlug(
                'lost'
            );

        $activeLeadStatuses =
            LeadStatus::openSlugs();

        $closedLeadStatuses =
            LeadStatus::closedSlugs();

        /*
         * Date range Lead Created Date par apply hoga.
         */
        $reportQuery =
            $this->accessibleLeadsQuery(
                $loggedInUser
            )
                ->whereBetween(
                    'created_at',
                    [
                        $dateFrom,
                        $dateTo,
                    ]
                );

        $this->applyFilters(
            $reportQuery,
            $search,
            $status,
            $source,
            $priority,
            $assignedTo,
            $conversion,
            $followUpState,
            $canViewAll
        );

        /*
         * Main summary.
         */
        $totalLeads =
            (clone $reportQuery)->count();

        $newLeads = $newStatusSlug
            ? (clone $reportQuery)
                ->where(
                    'status',
                    $newStatusSlug
                )
                ->count()
            : 0;

        $qualifiedLeads =
            $qualifiedStatusSlug
            ? (clone $reportQuery)
                ->where(
                    'status',
                    $qualifiedStatusSlug
                )
                ->count()
            : 0;

        $convertedLeads =
            (clone $reportQuery)
                ->where(
                    function (Builder $query) use ($convertedStatusSlug) {
                        if (
                            $convertedStatusSlug
                        ) {
                            $query->where(
                                'status',
                                $convertedStatusSlug
                            );
                        } else {
                            $query->whereRaw(
                                '1 = 0'
                            );
                        }

                        $query->orWhereNotNull(
                            'converted_at'
                        );
                    }
                )
                ->count();

        $lostLeads =
            $lostStatusSlug
            ? (clone $reportQuery)
                ->where(
                    'status',
                    $lostStatusSlug
                )
                ->count()
            : 0;

        $unassignedLeads =
            (clone $reportQuery)
                ->whereNull(
                    'assigned_to'
                )
                ->count();

        $conversionRate =
            $totalLeads > 0
            ? (int) round(
                (
                    $convertedLeads
                    / $totalLeads
                ) * 100
            )
            : 0;

        /*
         * Follow-up health.
         */
        // $activeLeadStatuses = [
        //     'new',
        //     'contacted',
        //     'follow_up',
        //     'qualified',
        // ];

        $overdueFollowUps =
            (clone $reportQuery)
                ->whereIn(
                    'status',
                    $activeLeadStatuses
                )
                ->whereNotNull(
                    'next_follow_up_at'
                )
                ->where(
                    'next_follow_up_at',
                    '<',
                    now()
                )
                ->count();

        $dueTodayFollowUps =
            (clone $reportQuery)
                ->whereIn(
                    'status',
                    $activeLeadStatuses
                )
                ->whereBetween(
                    'next_follow_up_at',
                    [
                        now(),
                        today()->endOfDay(),
                    ]
                )
                ->count();

        $upcomingFollowUps =
            (clone $reportQuery)
                ->whereIn(
                    'status',
                    $activeLeadStatuses
                )
                ->where(
                    'next_follow_up_at',
                    '>',
                    today()->endOfDay()
                )
                ->count();

        $noScheduleLeads =
            (clone $reportQuery)
                ->whereIn(
                    'status',
                    $activeLeadStatuses
                )
                ->whereNull(
                    'next_follow_up_at'
                )
                ->count();

        $leadsWithFollowUps =
            (clone $reportQuery)
                ->whereHas(
                    'followUps'
                )
                ->count();

        $followUpCoverageRate =
            $totalLeads > 0
            ? (int) round(
                (
                    $leadsWithFollowUps
                    / $totalLeads
                ) * 100
            )
            : 0;

        /*
         * Status, source aur priority distributions.
         */
        $statusCounts =
            $this->groupedCounts(
                clone $reportQuery,
                'status',
                Lead::statuses()
            );

        $sourceCounts =
            $this->groupedCounts(
                clone $reportQuery,
                'source',
                Lead::sources()
            );

        $priorityCounts =
            $this->groupedCounts(
                clone $reportQuery,
                'priority',
                Lead::priorities()
            );

        /*
         * Source-wise converted count.
         */
        $sourceConvertedCounts =
            (clone $reportQuery)
                ->where(
                    function (Builder $query) {
                        $query
                            ->where(
                                'status',
                                'converted'
                            )
                            ->orWhereNotNull(
                                'converted_at'
                            );
                    }
                )
                ->select('source')
                ->selectRaw(
                    'COUNT(*) as total'
                )
                ->groupBy('source')
                ->pluck(
                    'total',
                    'source'
                );

        $sourceRows = collect(
            Lead::sources()
        )
            ->map(
                function (string $sourceLabel, string $sourceKey) use ($sourceCounts, $sourceConvertedCounts) {
                    $total = (int) (
                        $sourceCounts[
                            $sourceKey
                        ] ?? 0
                    );

                    $converted = (int) (
                        $sourceConvertedCounts[
                            $sourceKey
                        ] ?? 0
                    );

                    return [
                        'key' => $sourceKey,
                        'label' => $sourceLabel,
                        'total' => $total,
                        'converted' => $converted,

                        'conversion_rate' =>
                            $total > 0
                            ? (int) round(
                                (
                                    $converted
                                    / $total
                                ) * 100
                            )
                            : 0,
                    ];
                }
            )
            ->sortByDesc('total')
            ->values();

        /*
         * Employee-wise Lead performance.
         */
        $employeeStats =
            (clone $reportQuery)
                ->select('assigned_to')
                ->selectRaw(
                    'COUNT(*) as total_leads'
                )
                ->selectRaw("
                    SUM(
                        CASE
                            WHEN status = 'qualified'
                            THEN 1
                            ELSE 0
                        END
                    ) as qualified_leads
                ")
                ->selectRaw("
                    SUM(
                        CASE
                            WHEN status = 'converted'
                                OR converted_at IS NOT NULL
                            THEN 1
                            ELSE 0
                        END
                    ) as converted_leads
                ")
                ->selectRaw("
                    SUM(
                        CASE
                            WHEN status = 'lost'
                            THEN 1
                            ELSE 0
                        END
                    ) as lost_leads
                ")
                ->selectRaw(
                    "
                    SUM(
                        CASE
                            WHEN status IN (
                                'new',
                                'contacted',
                                'follow_up',
                                'qualified'
                            )
                            AND next_follow_up_at IS NOT NULL
                            AND next_follow_up_at < ?
                            THEN 1
                            ELSE 0
                        END
                    ) as overdue_leads
                    ",
                    [
                        now(),
                    ]
                )
                ->groupBy('assigned_to')
                ->orderByDesc('total_leads')
                ->get();

        $employeeIds = $employeeStats
            ->pluck('assigned_to')
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
            $employeeStats
                ->map(
                    function ($row) use ($employees) {
                        $employeeId =
                            $row->assigned_to
                            ? (int) $row
                                ->assigned_to
                            : null;

                        $employee = $employeeId
                            ? $employees->get(
                                $employeeId
                            )
                            : null;

                        $total = (int) 
                            $row->total_leads;

                        $converted = (int) 
                            $row->converted_leads;

                        return [
                            'user_id' =>
                                $employeeId,

                            'name' =>
                                $employee?->name
                                ?? 'Unassigned',

                            'email' =>
                                $employee?->email,

                            'role' =>
                                $employee?->role?->name
                                ?? (
                                    $employeeId
                                    ? 'No Role'
                                    : 'No Employee'
                                ),

                            'total_leads' =>
                                $total,

                            'qualified_leads' =>
                                (int) $row
                                    ->qualified_leads,

                            'converted_leads' =>
                                $converted,

                            'lost_leads' =>
                                (int) $row
                                    ->lost_leads,

                            'overdue_leads' =>
                                (int) $row
                                    ->overdue_leads,

                            'conversion_rate' =>
                                $total > 0
                                ? (int) round(
                                    (
                                        $converted
                                        / $total
                                    ) * 100
                                )
                                : 0,
                        ];
                    }
                )
                ->values();

        /*
         * Monthly Lead creation trend.
         */
        $monthlyLeadTrend =
            $this->monthlyLeadTrend(
                clone $reportQuery,
                $dateFrom,
                $dateTo
            );

        /*
         * Detailed Lead table.
         */
        $leads =
            (clone $reportQuery)
                ->with([
                    'assignedUser:id,name,email',

                    'convertedBy:id,name,email',

                    'client:id,lead_id,name,status',

                    'statusDefinition:id,slug,name,color,is_closed,system_key',

                    'priorityDefinition:id,slug,name,color',
                ])
                ->withCount(
                    'followUps'
                )
                ->withMax(
                    'followUps as last_followed_up_at',
                    'followed_up_at'
                )
                ->latest('created_at')
                ->paginate($perPage)
                ->withQueryString();

        /*
         * Assigned employee filter.
         */
        $users = collect();

        if ($canViewAll) {
            $accessibleAssignedUserIds =
                $this->accessibleLeadsQuery(
                    $loggedInUser
                )
                    ->whereNotNull(
                        'assigned_to'
                    )
                    ->distinct()
                    ->pluck(
                        'assigned_to'
                    );

            $users = User::query()
                ->whereIn(
                    'id',
                    $accessibleAssignedUserIds
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                    'is_active',
                ]);
        }

        return view(
            'report::leads.index',
            [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,

                'leads' => $leads,
                'users' => $users,

                'statuses' =>
                    Lead::statuses(),

                'sources' =>
                    Lead::sources(),

                'priorities' =>
                    Lead::priorities(),

                'statusCounts' =>
                    $statusCounts,

                'sourceCounts' =>
                    $sourceCounts,

                'priorityCounts' =>
                    $priorityCounts,

                'sourceRows' =>
                    $sourceRows,

                'employeePerformance' =>
                    $employeePerformance,

                'monthlyLeadTrend' =>
                    $monthlyLeadTrend,

                'statusCountMax' =>
                    max(
                        1,
                        ...array_values(
                            $statusCounts
                        )
                    ),

                'priorityCountMax' =>
                    max(
                        1,
                        ...array_values(
                            $priorityCounts
                        )
                    ),

                'totalLeads' =>
                    $totalLeads,

                'newLeads' =>
                    $newLeads,

                'qualifiedLeads' =>
                    $qualifiedLeads,

                'convertedLeads' =>
                    $convertedLeads,

                'lostLeads' =>
                    $lostLeads,

                'unassignedLeads' =>
                    $unassignedLeads,

                'conversionRate' =>
                    $conversionRate,

                'overdueFollowUps' =>
                    $overdueFollowUps,

                'dueTodayFollowUps' =>
                    $dueTodayFollowUps,

                'upcomingFollowUps' =>
                    $upcomingFollowUps,

                'noScheduleLeads' =>
                    $noScheduleLeads,

                'leadsWithFollowUps' =>
                    $leadsWithFollowUps,

                'followUpCoverageRate' =>
                    $followUpCoverageRate,

                'search' => $search,
                'status' => $status,
                'source' => $source,
                'priority' => $priority,
                'assignedTo' => $assignedTo,
                'conversion' => $conversion,
                'followUpState' =>
                    $followUpState,
                'perPage' => $perPage,

                'canViewAll' =>
                    $canViewAll,

                'pageTitle' =>
                    'Lead Reports',
            ]
        );
    }

    /**
     * Admin/View All ko sab Leads.
     * Normal user ko sirf assigned Leads.
     */
    private function accessibleLeadsQuery(
        User $user
    ): Builder {
        $query = Lead::query();

        if (
            $this->canViewAllLeadReports(
                $user
            )
        ) {
            return $query;
        }

        return $query->where(
            'assigned_to',
            $user->id
        );
    }

    private function canViewAllLeadReports(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission(
                'reports.leads.view_all'
            );
    }

    private function applyFilters(
        Builder $query,
        string $search,
        string $status,
        string $source,
        string $priority,
        int $assignedTo,
        string $conversion,
        string $followUpState,
        bool $canViewAll
    ): void {
        if ($search !== '') {
            $query->where(
                function (Builder $searchQuery) use ($search) {
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

        if ($status !== '') {
            $query->where(
                'status',
                $status
            );
        }

        if ($source !== '') {
            $query->where(
                'source',
                $source
            );
        }

        if ($priority !== '') {
            $query->where(
                'priority',
                $priority
            );
        }

        if (
            $canViewAll
            && $assignedTo > 0
        ) {
            $query->where(
                'assigned_to',
                $assignedTo
            );
        }

        if ($conversion === 'converted') {
            $query->where(
                function (Builder $conversionQuery) {
                    $conversionQuery
                        ->where(
                            'status',
                            'converted'
                        )
                        ->orWhereNotNull(
                            'converted_at'
                        );
                }
            );
        }

        if (
            $conversion
            === 'not_converted'
        ) {
            $query
                ->whereNull(
                    'converted_at'
                )
                ->where(
                    'status',
                    '!=',
                    'converted'
                );
        }

        switch ($followUpState) {
            case 'overdue':
                $query
                    ->whereNotIn(
                        'status',
                        LeadStatus::closedSlugs()
                    )
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
                $query
                    ->whereNotIn(
                        'status',
                        [
                            'converted',
                            'lost',
                        ]
                    )
                    ->whereBetween(
                        'next_follow_up_at',
                        [
                            today()->startOfDay(),
                            today()->endOfDay(),
                        ]
                    );
                break;

            case 'upcoming':
                $query
                    ->whereNotIn(
                        'status',
                        [
                            'converted',
                            'lost',
                        ]
                    )
                    ->where(
                        'next_follow_up_at',
                        '>',
                        today()->endOfDay()
                    );
                break;

            case 'no_schedule':
                $query
                    ->whereNotIn(
                        'status',
                        [
                            'converted',
                            'lost',
                        ]
                    )
                    ->whereNull(
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
            array_keys(
                $availableValues
            ),
            0
        );

        $counts = $query
            ->reorder()
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

    private function monthlyLeadTrend(
        Builder $query,
        Carbon $dateFrom,
        Carbon $dateTo
    ) {
        $counts = $query
            ->reorder()
            ->selectRaw(
                "
                DATE_FORMAT(
                    created_at,
                    '%Y-%m'
                ) as month_key
                "
            )
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->groupBy('month_key')
            ->pluck(
                'total',
                'month_key'
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
                    $cursor->format(
                        'M Y'
                    ),

                'count' =>
                    (int) (
                        $counts[
                            $monthKey
                        ] ?? 0
                    ),
            ]);

            $cursor->addMonth();
        }

        $maximum = max(
            1,
            (int) $months->max(
                'count'
            )
        );

        return $months->map(
            function ($month) use ($maximum) {
                $month['width'] =
                    (int) round(
                        (
                            $month['count']
                            / $maximum
                        ) * 100
                    );

                return $month;
            }
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
                    'Lead Report date range cannot exceed 366 days.',
            ]);
        }

        return [
            $dateFrom,
            $dateTo,
        ];
    }
}