<?php

namespace App\Modules\Task\Support;

use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskKanbanPreference;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskKanbanService
{
    public function buildBoard(
        User $user,
        string $groupBy,
        array $filters,
        TaskKanbanPreference $preference,
        bool $onlyMyTasks = false
    ): array {
        $columns =
            $this->columns($groupBy);

        $query =
            $this->accessibleQuery(
                $user,
                $onlyMyTasks
            );

        $this->applyFilters(
            $query,
            $filters
        );

        $tasks =
            $query
                ->orderBy(
                    $groupBy === 'status'
                        ? 'status_kanban_position'
                        : 'priority_kanban_position'
                )
                ->orderBy('id')
                ->get();

        $positionField =
            $groupBy === 'status'
                ? 'status_kanban_position'
                : 'priority_kanban_position';

        $groupField =
            $groupBy === 'status'
                ? 'status'
                : 'priority';

        $grouped =
            $tasks->groupBy(
                $groupField
            );

        $orderedColumns =
            $this->orderedColumns(
                $columns,
                $preference
            );

        $boardColumns = [];

        foreach (
            $orderedColumns as $column
        ) {
            $items =
                $grouped->get(
                    $column['slug'],
                    collect()
                );

            if (
                $preference
                    ->hide_empty_columns
                && $items->isEmpty()
            ) {
                continue;
            }

            $boardColumns[] = [
                'slug' =>
                    $column['slug'],

                'name' =>
                    $column['name'],

                'color' =>
                    $column['color'],

                'tasks' =>
                    $items,

                'count' =>
                    $items->count(),

                'drop_allowed' =>
                    $this->columnDropAllowed(
                        $column
                    ),
            ];
        }

        return [
            'columns' =>
                $boardColumns,

            'totalTasks' =>
                $tasks->count(),

            'updatedAt' =>
                now()->format(
                    'h:i:s A'
                ),

            'groupBy' =>
                $groupBy,
        ];
    }

    public function columns(
        string $groupBy
    ): array {
        if ($groupBy === 'priority') {
            return collect(
                Task::activePriorities()
            )
                ->map(
                    function (
                        $name,
                        $slug
                    ) {
                        $definition =
                            \App\Modules\Task\Models\TaskPriority::query()
                                ->where(
                                    'slug',
                                    $slug
                                )
                                ->first();

                        return [
                            'slug' =>
                                $slug,

                            'name' =>
                                $name,

                            'color' =>
                                $definition
                                    ?->color
                                    ?? '#64748b',
                        ];
                    }
                )
                ->values()
                ->all();
        }

        return collect(
            Task::activeStatuses()
        )
            ->map(
                function (
                    $name,
                    $slug
                ) {
                    $definition =
                        \App\Modules\Task\Models\TaskStatus::query()
                            ->where(
                                'slug',
                                $slug
                            )
                            ->first();

                    return [
                        'slug' =>
                            $slug,

                        'name' =>
                            $name,

                        'color' =>
                            $definition
                                ?->color
                                ?? '#64748b',
                    ];
                }
            )
            ->values()
            ->all();
    }

    private function accessibleQuery(
        User $user,
        bool $onlyMyTasks
    ): Builder {
        $query =
            Task::query()
                ->with([
                    'project:id,project_code,name,project_manager_id',
                    'projectService:id,name',
                    'assignedUser:id,name,email',
                    'statusDefinition:id,slug,name,color,is_closed,system_key',
                    'priorityDefinition:id,slug,name,color',
                ]);

        if ($onlyMyTasks) {
            return $query->where(
                'assigned_to',
                $user->id
            );
        }

        if (
            $user->hasPermission(
                'tasks.view_all'
            )
        ) {
            return $query;
        }

        return $query->where(
            function (Builder $q) use ($user) {
                $q->where(
                    'assigned_to',
                    $user->id
                )->orWhereHas(
                    'project',
                    function (
                        Builder $projectQuery
                    ) use ($user) {
                        $projectQuery
                            ->where(
                                'project_manager_id',
                                $user->id
                            )
                            ->orWhereHas(
                                'members',
                                function (
                                    Builder $memberQuery
                                ) use ($user) {
                                    $memberQuery->where(
                                        'users.id',
                                        $user->id
                                    );
                                }
                            );
                    }
                );
            }
        );
    }

    private function applyFilters(
        Builder $query,
        array $filters
    ): void {
        $search =
            trim(
                $filters['search'] ?? ''
            );

        if ($search !== '') {
            $query->where(
                function (Builder $q) use (
                    $search
                ) {
                    $q->where(
                        'title',
                        'LIKE',
                        "%{$search}%"
                    )->orWhereHas(
                        'project',
                        function (
                            Builder $projectQuery
                        ) use ($search) {
                            $projectQuery
                                ->where(
                                    'name',
                                    'LIKE',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'project_code',
                                    'LIKE',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            );
        }

        if (
            !empty($filters['status'])
            && array_key_exists(
                $filters['status'],
                Task::statuses()
            )
        ) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        if (
            !empty($filters['priority'])
            && array_key_exists(
                $filters['priority'],
                Task::priorities()
            )
        ) {
            $query->where(
                'priority',
                $filters['priority']
            );
        }

        $due =
            $filters['due'] ?? '';

        if ($due === 'today') {
            $query->whereDate(
                'due_at',
                today()
            );
        } elseif ($due === 'overdue') {
            $query
                ->whereNotNull('due_at')
                ->where(
                    'due_at',
                    '<',
                    now()
                )
                ->whereNotIn(
                    'status',
                    Task::closedStatusSlugs()
                );
        } elseif ($due === 'upcoming') {
            $query->where(
                'due_at',
                '>',
                now()
            );
        }
    }

    public function moveTask(
        User $user,
        Task $task,
        array $data
    ): Task {
        return DB::transaction(
            function () use (
                $user,
                $task,
                $data
            ) {
                $task =
                    Task::query()
                        ->with([
                            'project',
                            'projectService',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $task->id
                        );

                $this->ensureAccessible(
                    $user,
                    $task
                );

                if ($task->isClosed()) {
                    throw ValidationException::withMessages([
                        'task' =>
                            'Completed or cancelled task ko Kanban se move nahi kiya ja sakta.',
                    ]);
                }

                $groupBy =
                    $data['group_by'];

                $targetColumn =
                    $data['target_column'];

                $columns =
                    collect(
                        $this->columns(
                            $groupBy
                        )
                    );

                abort_unless(
                    $columns->contains(
                        'slug',
                        $targetColumn
                    ),
                    422,
                    'Invalid Kanban column.'
                );

                $groupField =
                    $groupBy === 'status'
                        ? 'status'
                        : 'priority';

                $positionField =
                    $groupBy === 'status'
                        ? 'status_kanban_position'
                        : 'priority_kanban_position';

                $oldColumn =
                    $task->{$groupField};

                if (
                    $groupBy === 'status'
                    && $oldColumn !== $targetColumn
                ) {
                    if (
                        $targetColumn
                        === Task::completedStatus()
                        && $task->requires_review
                    ) {
                        throw ValidationException::withMessages([
                            'task' =>
                                'Review required task ko direct Completed nahi kar sakte. Pehle In Review submit karein.',
                        ]);
                    }

                    if (
                        $task->hasIncompleteDependencies()
                        && $targetColumn
                        !== Task::cancelledStatus()
                    ) {
                        throw ValidationException::withMessages([
                            'task' =>
                                'This task is blocked. Complete all prerequisite tasks first.',
                        ]);
                    }
                }

                if (
                    $groupBy === 'status'
                    && $targetColumn
                    === Task::completedStatus()
                ) {
                    $task->progress_percent = 100;
                    $task->completed_at = now();
                } elseif (
                    $groupBy === 'status'
                    && $targetColumn
                    !== Task::completedStatus()
                ) {
                    $task->completed_at = null;
                }

                $task->{$groupField} =
                    $targetColumn;

                $task->save();

                $this->rebuildPositions(
                    $groupBy,
                    $oldColumn,
                    $targetColumn,
                    $task->id,
                    $data['before_id'] ?? null,
                    $data['after_id'] ?? null
                );

                $task->refresh();

                return $task->load([
                    'project:id,project_code,name',
                    'projectService:id,name',
                    'assignedUser:id,name,email',
                    'statusDefinition:id,slug,name,color,is_closed,system_key',
                    'priorityDefinition:id,slug,name,color',
                ]);
            }
        );
    }

    private function rebuildPositions(
        string $groupBy,
        string $sourceColumn,
        string $targetColumn,
        int $taskId,
        ?int $beforeId,
        ?int $afterId
    ): void {
        $groupField =
            $groupBy === 'status'
                ? 'status'
                : 'priority';

        $positionField =
            $groupBy === 'status'
                ? 'status_kanban_position'
                : 'priority_kanban_position';

        $sourceTasks =
            Task::query()
                ->where(
                    $groupField,
                    $sourceColumn
                )
                ->where(
                    'id',
                    '!=',
                    $taskId
                )
                ->orderBy(
                    $positionField
                )
                ->orderBy('id')
                ->get();

        if (
            $sourceColumn ===
            $targetColumn
        ) {
            $targetTasks =
                $sourceTasks;
        } else {
            $targetTasks =
                Task::query()
                    ->where(
                        $groupField,
                        $targetColumn
                    )
                    ->where(
                        'id',
                        '!=',
                        $taskId
                    )
                    ->orderBy(
                        $positionField
                    )
                    ->orderBy('id')
                    ->get();
        }

        $items =
            $targetTasks->values();

        $insertIndex =
            $items->count();

        if ($beforeId) {
            $index =
                $items->search(
                    fn($item) =>
                        (int) $item->id
                        === (int) $beforeId
                );

            if ($index !== false) {
                $insertIndex = $index;
            }
        } elseif ($afterId) {
            $index =
                $items->search(
                    fn($item) =>
                        (int) $item->id
                        === (int) $afterId
                );

            if ($index !== false) {
                $insertIndex = $index + 1;
            }
        }

        $items->splice(
            $insertIndex,
            0,
            [
                Task::find($taskId),
            ]
        );

        foreach (
            $items->values() as $index => $item
        ) {
            $item->{$positionField} =
                ($index + 1) * 10;

            $item->saveQuietly();
        }

        if (
            $sourceColumn !==
            $targetColumn
        ) {
            foreach (
                $sourceTasks->values()
                as $index => $item
            ) {
                $item->{$positionField} =
                    ($index + 1) * 10;

                $item->saveQuietly();
            }
        }
    }

    public function saveColumnOrder(
        User $user,
        string $groupBy,
        array $columns
    ): array {
        $allowed =
            collect(
                $this->columns(
                    $groupBy
                )
            )->pluck('slug');

        $clean =
            collect($columns)
                ->filter(
                    fn($column) =>
                        $allowed->contains(
                            $column
                        )
                )
                ->unique()
                ->values()
                ->all();

        $preference =
            TaskKanbanPreference::forUser(
                $user
            );

        $preference->update([
            'group_by' =>
                $groupBy,

            'column_order' =>
                $clean,
        ]);

        return $clean;
    }

    public function savePreference(
        User $user,
        array $data
    ): TaskKanbanPreference {
        $preference =
            TaskKanbanPreference::forUser(
                $user
            );

        $preference->update(
            $data
        );

        return $preference->fresh();
    }

    private function orderedColumns(
        array $columns,
        TaskKanbanPreference $preference
    ): array {
        $order =
            $preference->column_order;

        if (
            !is_array($order)
            || empty($order)
        ) {
            return $columns;
        }

        $lookup =
            collect($columns)
                ->keyBy('slug');

        $result = [];

        foreach ($order as $slug) {
            if ($lookup->has($slug)) {
                $result[] =
                    $lookup->get($slug);
            }
        }

        foreach ($columns as $column) {
            if (
                !collect($result)
                    ->contains(
                        'slug',
                        $column['slug']
                    )
            ) {
                $result[] = $column;
            }
        }

        return $result;
    }

    private function columnDropAllowed(
        array $column
    ): bool {
        return true;
    }

    private function ensureAccessible(
        User $user,
        Task $task
    ): void {
        if (
            $user->hasPermission(
                'tasks.view_all'
            )
        ) {
            return;
        }

        $allowed =
            (int) $task->assigned_to
            === (int) $user->id
            || (
                $task->project
                && (
                    (int)
                    $task->project
                        ->project_manager_id
                    === (int) $user->id
                    || $task->project
                        ->members()
                        ->where(
                            'users.id',
                            $user->id
                        )
                        ->exists()
                )
            );

        abort_unless(
            $allowed,
            403
        );
    }
}