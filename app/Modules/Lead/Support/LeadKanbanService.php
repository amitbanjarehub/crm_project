<?php

namespace App\Modules\Lead\Support;

use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Models\LeadKanbanPreference;
use App\Modules\Lead\Models\LeadPriority;
use App\Modules\Lead\Models\LeadStatus;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class LeadKanbanService
{
    private const POSITION_STEP = 1000.0;

    private const MINIMUM_GAP = 0.01;

    public function buildBoard(
        User $user,
        string $groupBy,
        array $filters,
        LeadKanbanPreference $preference
    ): array {
        $this->validateGroupBy(
            $groupBy
        );

        $query = $this
            ->accessibleLeadQuery($user)
            ->with([
                'assignedUser:id,name,email',
                'creator:id,name,email',
                'client:id,lead_id,name,status',

                'statusDefinition:id,slug,name,color,is_active,is_closed,is_system,system_key',

                'priorityDefinition:id,slug,name,color,is_active,is_system',
            ])
            ->withMax(
                'followUps as last_followed_up_at',
                'followed_up_at'
            );

        $this->applyFilters(
            $query,
            $filters,
            $user
        );

        $positionColumn =
            $this->positionColumn(
                $groupBy
            );

        $query
            ->orderByRaw(
                "CASE WHEN {$positionColumn} IS NULL THEN 1 ELSE 0 END"
            )
            ->orderBy($positionColumn)
            ->orderBy('id');

        $leads = $query->get();

        $columns =
            $this->definitionColumns(
                $groupBy
            );

        /*
         * Kisi old/inactive slug ki definition missing ho,
         * tab bhi Lead board se disappear nahi hogi.
         */
        $knownSlugs =
            collect($columns)
                ->pluck('slug')
                ->all();

        foreach (
            $leads
                ->pluck($groupBy)
                ->filter()
                ->unique()
                ->values()
            as $slug
        ) {
            if (
                in_array(
                    $slug,
                    $knownSlugs,
                    true
                )
            ) {
                continue;
            }

            $columns[] = [
                'slug' => $slug,

                'name' => ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $slug
                    )
                ),

                'color' => '#64748B',
                'is_active' => false,
                'is_closed' => false,
                'is_system' => false,
                'system_key' => null,
            ];

            $knownSlugs[] = $slug;
        }

        $savedOrder =
            data_get(
                $preference->column_order,
                $groupBy,
                []
            );

        $columns =
            $this->applySavedColumnOrder(
                $columns,
                is_array($savedOrder)
                    ? $savedOrder
                    : []
            );

        $groupedLeads =
            $leads->groupBy(
                $groupBy
            );

        $columns = collect($columns)
            ->map(function (
                array $column
            ) use ($groupedLeads) {
                $column['leads'] =
                    $groupedLeads
                        ->get(
                            $column['slug'],
                            collect()
                        )
                        ->values();

                $column['count'] =
                    $column['leads']
                        ->count();

                return $column;
            });

        if (
            $preference
                ->hide_empty_columns
        ) {
            $columns = $columns
                ->filter(
                    fn (array $column) =>
                        $column['count'] > 0
                )
                ->values();
        }

        return [
            'columns' => $columns,
            'totalLeads' => $leads->count(),
            'groupBy' => $groupBy,
        ];
    }

    public function moveLead(
        User $user,
        Lead $lead,
        array $data
    ): array {
        $groupBy =
            $data['group_by'];

        $targetColumn =
            $data['target_column'];

        $beforeId =
            $data['before_id']
            ?? null;

        $afterId =
            $data['after_id']
            ?? null;

        $expectedVersion =
            (int) $data[
                'expected_version'
            ];

        $this->validateGroupBy(
            $groupBy
        );

        return DB::transaction(
            function () use (
                $user,
                $lead,
                $groupBy,
                $targetColumn,
                $beforeId,
                $afterId,
                $expectedVersion
            ) {
                $lockedLead =
                    $this
                        ->accessibleLeadQuery(
                            $user
                        )
                        ->whereKey(
                            $lead->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    (int) $lockedLead
                        ->kanban_version
                    !== $expectedVersion
                ) {
                    throw new ConflictHttpException(
                        'Lead kisi doosre request se update ho chuki hai. Board refresh karein.'
                    );
                }

                if (
                    $lockedLead
                        ->isConverted()
                ) {
                    throw ValidationException::withMessages([
                        'lead' =>
                            'Converted Lead ko Kanban se move nahi kiya ja sakta.',
                    ]);
                }

                $groupColumn =
                    $groupBy;

                $positionColumn =
                    $this->positionColumn(
                        $groupBy
                    );

                $fromColumn =
                    (string) $lockedLead
                        ->{$groupColumn};

                $fromPosition =
                    $lockedLead
                        ->{$positionColumn};

                $definition =
                    $this->validateTargetColumn(
                        $groupBy,
                        $targetColumn,
                        $fromColumn
                    );

                if (
                    $beforeId
                    && (int) $beforeId
                        === (int) $lockedLead->id
                ) {
                    $beforeId = null;
                }

                if (
                    $afterId
                    && (int) $afterId
                        === (int) $lockedLead->id
                ) {
                    $afterId = null;
                }

                if (
                    $beforeId
                    && $afterId
                    && (int) $beforeId
                        === (int) $afterId
                ) {
                    throw ValidationException::withMessages([
                        'position' =>
                            'Invalid Kanban neighbour position.',
                    ]);
                }

                $beforeLead =
                    $this->lockedNeighbour(
                        $user,
                        $beforeId,
                        $groupColumn,
                        $targetColumn
                    );

                $afterLead =
                    $this->lockedNeighbour(
                        $user,
                        $afterId,
                        $groupColumn,
                        $targetColumn
                    );

                if (
                    $this->shouldNormalize(
                        $beforeLead,
                        $afterLead,
                        $positionColumn
                    )
                ) {
                    $this->normalizeColumn(
                        $groupColumn,
                        $targetColumn,
                        $positionColumn
                    );

                    $beforeLead =
                        $this->lockedNeighbour(
                            $user,
                            $beforeId,
                            $groupColumn,
                            $targetColumn
                        );

                    $afterLead =
                        $this->lockedNeighbour(
                            $user,
                            $afterId,
                            $groupColumn,
                            $targetColumn
                        );
                }

                $newPosition =
                    $this->calculatePosition(
                        $lockedLead,
                        $beforeLead,
                        $afterLead,
                        $groupColumn,
                        $targetColumn,
                        $positionColumn
                    );

                $updates = [
                    $positionColumn =>
                        $newPosition,

                    'kanban_version' =>
                        (int) $lockedLead
                            ->kanban_version
                        + 1,
                ];

                if (
                    $targetColumn
                    !== $fromColumn
                ) {
                    $updates[
                        $groupColumn
                    ] = $targetColumn;
                }

                /*
                 * Closed status me pending follow-up
                 * active nahi rehna chahiye.
                 */
                if (
                    $groupBy === 'status'
                    && (bool) $definition
                        ->is_closed
                ) {
                    $updates[
                        'next_follow_up_at'
                    ] = null;
                }

                $lockedLead->update(
                    $updates
                );

                DB::table(
                    'lead_kanban_moves'
                )->insert([
                    'lead_id' =>
                        $lockedLead->id,

                    'user_id' =>
                        $user->id,

                    'group_by' =>
                        $groupBy,

                    'from_column' =>
                        $fromColumn,

                    'to_column' =>
                        $targetColumn,

                    'from_position' =>
                        $fromPosition,

                    'to_position' =>
                        $newPosition,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

                $lockedLead->refresh();

                $lockedLead->load([
                    'statusDefinition:id,slug,name,color,is_closed,system_key',

                    'priorityDefinition:id,slug,name,color',
                ]);

                return [
                    'id' =>
                        $lockedLead->id,

                    'kanban_version' =>
                        (int) $lockedLead
                            ->kanban_version,

                    'updated_at' =>
                        $lockedLead
                            ->updated_at
                            ?->toIso8601String(),

                    'status' => [
                        'slug' =>
                            $lockedLead->status,

                        'name' =>
                            $lockedLead
                                ->statusDefinition
                                ?->name
                            ?? ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $lockedLead
                                        ->status
                                )
                            ),

                        'color' =>
                            $lockedLead
                                ->statusDefinition
                                ?->color
                            ?? '#64748B',
                    ],

                    'priority' => [
                        'slug' =>
                            $lockedLead->priority,

                        'name' =>
                            $lockedLead
                                ->priorityDefinition
                                ?->name
                            ?? ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $lockedLead
                                        ->priority
                                )
                            ),

                        'color' =>
                            $lockedLead
                                ->priorityDefinition
                                ?->color
                            ?? '#64748B',
                    ],

                    'next_follow_up_at' =>
                        $lockedLead
                            ->next_follow_up_at
                            ?->format(
                                'd M Y, h:i A'
                            ),
                ];
            },
            3
        );
    }

    public function saveColumnOrder(
        User $user,
        string $groupBy,
        array $requestedOrder
    ): array {
        $this->validateGroupBy(
            $groupBy
        );

        $availableColumns =
            collect(
                $this->definitionColumns(
                    $groupBy
                )
            )
                ->pluck('slug')
                ->values()
                ->all();

        $requestedOrder =
            array_values(
                array_unique(
                    array_filter(
                        $requestedOrder,
                        fn ($slug) =>
                            is_string($slug)
                            && in_array(
                                $slug,
                                $availableColumns,
                                true
                            )
                    )
                )
            );

        $finalOrder = array_merge(
            $requestedOrder,

            array_values(
                array_diff(
                    $availableColumns,
                    $requestedOrder
                )
            )
        );

        $preference =
            LeadKanbanPreference::forUser(
                $user
            );

        $columnOrder =
            $preference->column_order
            ?? [];

        $columnOrder[
            $groupBy
        ] = $finalOrder;

        $preference->update([
            'column_order' =>
                $columnOrder,
        ]);

        return $finalOrder;
    }

    public function savePreference(
        User $user,
        array $data
    ): LeadKanbanPreference {
        $preference =
            LeadKanbanPreference::forUser(
                $user
            );

        $updates = [];

        if (
            array_key_exists(
                'group_by',
                $data
            )
        ) {
            $this->validateGroupBy(
                $data['group_by']
            );

            $updates['group_by'] =
                $data['group_by'];
        }

        if (
            array_key_exists(
                'hide_empty_columns',
                $data
            )
        ) {
            $updates[
                'hide_empty_columns'
            ] = (bool) $data[
                'hide_empty_columns'
            ];
        }

        if ($updates !== []) {
            $preference->update(
                $updates
            );
        }

        return $preference
            ->refresh();
    }

    private function accessibleLeadQuery(
        User $user
    ): Builder {
        $query =
            Lead::query();

        if (
            !$user->isSuperAdmin()
            && !$user->hasPermission(
                'leads.view_all'
            )
        ) {
            $query->where(
                'assigned_to',
                $user->id
            );
        }

        return $query;
    }

    private function applyFilters(
        Builder $query,
        array $filters,
        User $user
    ): void {
        $search = trim(
            (string) (
                $filters['search']
                ?? ''
            )
        );

        if ($search !== '') {
            $query->where(
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

                    if (
                        is_numeric($search)
                    ) {
                        $searchQuery
                            ->orWhere(
                                'id',
                                (int) $search
                            );
                    }
                }
            );
        }

        $status = trim(
            (string) (
                $filters['status']
                ?? ''
            )
        );

        if (
            $status !== ''
            && array_key_exists(
                $status,
                Lead::statuses()
            )
        ) {
            $query->where(
                'status',
                $status
            );
        }

        $priority = trim(
            (string) (
                $filters['priority']
                ?? ''
            )
        );

        if (
            $priority !== ''
            && array_key_exists(
                $priority,
                Lead::priorities()
            )
        ) {
            $query->where(
                'priority',
                $priority
            );
        }

        $source = trim(
            (string) (
                $filters['source']
                ?? ''
            )
        );

        if (
            $source !== ''
            && array_key_exists(
                $source,
                Lead::sources()
            )
        ) {
            $query->where(
                'source',
                $source
            );
        }

        $assignedTo = (int) (
            $filters['assigned_to']
            ?? 0
        );

        if (
            $assignedTo > 0
            && (
                $user->isSuperAdmin()
                || $user->hasPermission(
                    'leads.view_all'
                )
            )
        ) {
            $query->where(
                'assigned_to',
                $assignedTo
            );
        }
    }

    private function definitionColumns(
        string $groupBy
    ): array {
        if ($groupBy === 'status') {
            return LeadStatus::query()
                ->ordered()
                ->get([
                    'slug',
                    'name',
                    'color',
                    'is_active',
                    'is_closed',
                    'is_system',
                    'system_key',
                ])
                ->map(
                    fn (LeadStatus $status) => [
                        'slug' =>
                            $status->slug,

                        'name' =>
                            $status->name,

                        'color' =>
                            $status->color
                            ?: '#64748B',

                        'is_active' =>
                            (bool) $status
                                ->is_active,

                        'is_closed' =>
                            (bool) $status
                                ->is_closed,

                        'is_system' =>
                            (bool) $status
                                ->is_system,

                        'system_key' =>
                            $status
                                ->system_key,
                    ]
                )
                ->all();
        }

        return LeadPriority::query()
            ->ordered()
            ->get([
                'slug',
                'name',
                'color',
                'is_active',
                'is_system',
            ])
            ->map(
                fn (
                    LeadPriority $priority
                ) => [
                    'slug' =>
                        $priority->slug,

                    'name' =>
                        $priority->name,

                    'color' =>
                        $priority->color
                        ?: '#64748B',

                    'is_active' =>
                        (bool) $priority
                            ->is_active,

                    'is_closed' =>
                        false,

                    'is_system' =>
                        (bool) $priority
                            ->is_system,

                    'system_key' =>
                        null,
                ]
            )
            ->all();
    }

    private function applySavedColumnOrder(
        array $columns,
        array $savedOrder
    ): array {
        $orderMap =
            array_flip(
                $savedOrder
            );

        foreach (
            $columns
            as $index => &$column
        ) {
            $column[
                '_default_index'
            ] = $index;
        }

        unset($column);

        usort(
            $columns,
            function (
                array $first,
                array $second
            ) use ($orderMap) {
                $firstOrder =
                    $orderMap[
                        $first['slug']
                    ]
                    ?? (
                        100000
                        + $first[
                            '_default_index'
                        ]
                    );

                $secondOrder =
                    $orderMap[
                        $second['slug']
                    ]
                    ?? (
                        100000
                        + $second[
                            '_default_index'
                        ]
                    );

                return $firstOrder
                    <=> $secondOrder;
            }
        );

        foreach (
            $columns
            as &$column
        ) {
            unset(
                $column[
                    '_default_index'
                ]
            );
        }

        unset($column);

        return $columns;
    }

    private function validateTargetColumn(
        string $groupBy,
        string $targetColumn,
        string $fromColumn
    ): LeadStatus|LeadPriority {
        if ($groupBy === 'status') {
            $definition =
                LeadStatus::query()
                    ->where(
                        'slug',
                        $targetColumn
                    )
                    ->first();

            if (!$definition) {
                throw ValidationException::withMessages([
                    'target_column' =>
                        'Selected Lead status does not exist.',
                ]);
            }

            if (
                $definition->system_key
                === 'converted'
            ) {
                throw ValidationException::withMessages([
                    'target_column' =>
                        'Lead ko Converted column me drag nahi kiya ja sakta. Convert button use karein.',
                ]);
            }

            if (
                $targetColumn
                    !== $fromColumn
                && !$definition
                    ->is_active
            ) {
                throw ValidationException::withMessages([
                    'target_column' =>
                        'Inactive Lead status me Lead move nahi ki ja sakti.',
                ]);
            }

            return $definition;
        }

        $definition =
            LeadPriority::query()
                ->where(
                    'slug',
                    $targetColumn
                )
                ->first();

        if (!$definition) {
            throw ValidationException::withMessages([
                'target_column' =>
                    'Selected Lead priority does not exist.',
            ]);
        }

        if (
            $targetColumn
                !== $fromColumn
            && !$definition
                ->is_active
        ) {
            throw ValidationException::withMessages([
                'target_column' =>
                    'Inactive priority me Lead move nahi ki ja sakti.',
            ]);
        }

        return $definition;
    }

    private function lockedNeighbour(
        User $user,
        mixed $leadId,
        string $groupColumn,
        string $targetColumn
    ): ?Lead {
        if (!$leadId) {
            return null;
        }

        $neighbour =
            $this
                ->accessibleLeadQuery(
                    $user
                )
                ->whereKey(
                    (int) $leadId
                )
                ->where(
                    $groupColumn,
                    $targetColumn
                )
                ->lockForUpdate()
                ->first();

        if (!$neighbour) {
            throw ValidationException::withMessages([
                'position' =>
                    'Kanban neighbouring Lead valid nahi hai.',
            ]);
        }

        return $neighbour;
    }

    private function shouldNormalize(
        ?Lead $beforeLead,
        ?Lead $afterLead,
        string $positionColumn
    ): bool {
        if (
            $beforeLead
            && $beforeLead
                ->{$positionColumn}
                === null
        ) {
            return true;
        }

        if (
            $afterLead
            && $afterLead
                ->{$positionColumn}
                === null
        ) {
            return true;
        }

        if (
            $beforeLead
            && $afterLead
        ) {
            $beforePosition =
                (float) $beforeLead
                    ->{$positionColumn};

            $afterPosition =
                (float) $afterLead
                    ->{$positionColumn};

            return
                $beforePosition
                    >= $afterPosition
                || (
                    $afterPosition
                    - $beforePosition
                ) <= self::MINIMUM_GAP;
        }

        return false;
    }

    private function calculatePosition(
        Lead $movingLead,
        ?Lead $beforeLead,
        ?Lead $afterLead,
        string $groupColumn,
        string $targetColumn,
        string $positionColumn
    ): float {
        if (
            $beforeLead
            && $afterLead
        ) {
            return (
                (float) $beforeLead
                    ->{$positionColumn}
                +
                (float) $afterLead
                    ->{$positionColumn}
            ) / 2;
        }

        if ($beforeLead) {
            return (
                (float) $beforeLead
                    ->{$positionColumn}
            ) + self::POSITION_STEP;
        }

        if ($afterLead) {
            return (
                (float) $afterLead
                    ->{$positionColumn}
            ) - self::POSITION_STEP;
        }

        $maximumPosition =
            Lead::query()
                ->where(
                    $groupColumn,
                    $targetColumn
                )
                ->where(
                    'id',
                    '!=',
                    $movingLead->id
                )
                ->max(
                    $positionColumn
                );

        return $maximumPosition
            ? (
                (float) $maximumPosition
                + self::POSITION_STEP
            )
            : self::POSITION_STEP;
    }

    private function normalizeColumn(
        string $groupColumn,
        string $targetColumn,
        string $positionColumn
    ): void {
        $leads = Lead::query()
            ->where(
                $groupColumn,
                $targetColumn
            )
            ->orderByRaw(
                "CASE WHEN {$positionColumn} IS NULL THEN 1 ELSE 0 END"
            )
            ->orderBy(
                $positionColumn
            )
            ->orderBy('id')
            ->lockForUpdate()
            ->get([
                'id',
            ]);

        foreach (
            $leads
            as $index => $lead
        ) {
            Lead::query()
                ->whereKey(
                    $lead->id
                )
                ->update([
                    $positionColumn =>
                        (
                            $index + 1
                        )
                        * self::POSITION_STEP,
                ]);
        }
    }

    private function positionColumn(
        string $groupBy
    ): string {
        return $groupBy
            === 'status'
            ? 'status_kanban_position'
            : 'priority_kanban_position';
    }

    private function validateGroupBy(
        string $groupBy
    ): void {
        if (
            !in_array(
                $groupBy,
                [
                    'status',
                    'priority',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'group_by' =>
                    'Kanban grouping status ya priority honi chahiye.',
            ]);
        }
    }
}