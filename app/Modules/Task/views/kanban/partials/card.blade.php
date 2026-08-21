@php
    $statusColor =
        $task->statusDefinition?->color
        ?? '#64748b';

    $priorityColor =
        $task->priorityDefinition?->color
        ?? '#64748b';

    $isOverdue =
        $task->due_at
        && $task->due_at->isPast()
        && !$task->isClosed();
@endphp

<article class="task-kanban-card" data-task-id="{{ $task->id }}" data-status="{{ $task->status }}"
    data-priority="{{ $task->priority }}" tabindex="0">

    <div class="task-kanban-card-top">

        <button type="button" class="task-kanban-card-handle" draggable="true" data-no-drawer title="Drag task">
            ⋮⋮
        </button>

        <div class="task-kanban-card-heading">

            <strong>
                {{ $task->title }}
            </strong>

            <span>
                Task #{{ $task->id }}
            </span>

        </div>

        @if($task->isClosed())

            <span class="task-kanban-lock">
                🔒
            </span>

        @endif

    </div>

    <div class="task-kanban-project">

        {{ $task->project?->name ?? 'No Project' }}

    </div>

    <div class="task-kanban-service">

        {{ $task->projectService?->name ?? 'No Service' }}

    </div>

    <div class="task-kanban-badges">

        <span class="task-kanban-badge" style="
                --task-kanban-badge-color:
                {{ $statusColor }};
            ">
            {{ $task->statusDefinition?->name
    ?? $task->status }}
        </span>

        <span class="task-kanban-badge" style="
                --task-kanban-badge-color:
                {{ $priorityColor }};
            ">
            {{ $task->priorityDefinition?->name
    ?? $task->priority }}
        </span>

    </div>

    <div class="task-kanban-progress">

        <div class="task-kanban-progress-label">

            <span>
                Progress
            </span>

            <strong>
                {{ $task->progress_percent }}%
            </strong>

        </div>

        <div class="task-kanban-progress-track">

            <span style="
                    width:
                    {{ $task->progress_percent }}%;
                "></span>

        </div>

    </div>

    <div class="task-kanban-information">

        <div>

            <span>
                Assigned
            </span>

            <strong>
                {{ $task->assignedUser?->name
    ?? 'Unassigned' }}
            </strong>

        </div>

        <div>

            <span>
                Due
            </span>

            <strong class="{{ $isOverdue
    ? 'task-kanban-overdue-text'
    : '' }}">
                {{ $task->due_at
    ? $task->due_at
        ->format('d M Y h:i A')
    : 'No due date' }}
            </strong>

        </div>

    </div>

    @if($task->description)

        <div class="task-kanban-description">

            {{ \Illuminate\Support\Str::limit(
            $task->description,
            120
        ) }}

        </div>

    @endif

    <div class="task-kanban-actions">

        <!-- <a
            href="{{ route(
                'task.show',
                $task->id
            ) }}"
            class="task-kanban-action view"
            data-no-drawer
        >
            View
        </a> -->

        <a href="{{ route('task.show', ['task' => $task->id, 'from' => 'kanban']) }}" class="task-kanban-action view"
            data-no-drawer>
            View
        </a>

        @if(
                auth()->user()->hasPermission(
                    'tasks.edit'
                )
                && !$task->isClosed()
            )

            <!-- <a href="{{ route(
                            'task.edit',
                            $task->id
                        ) }}" class="task-kanban-action edit" data-no-drawer>
                                Edit
                            </a> -->

            <a href="{{ route('task.edit', ['task' => $task->id, 'from' => 'kanban']) }}" class="task-kanban-action edit"
                data-no-drawer>
                Edit
            </a>

        @endif

        @if(
                auth()->user()->hasPermission(
                    'time_tracking.use'
                )
                && !$task->isClosed()
            )

            <!-- <a href="{{ route(
                            'task.show',
                            $task->id
                        ) }}" class="task-kanban-action timer" data-no-drawer>
                                Timer
                            </a> -->

            <a href="{{ route('task.show', ['task' => $task->id, 'from' => 'kanban']) }}" class="task-kanban-action timer"
                data-no-drawer>
                Timer
            </a>

        @endif

    </div>

</article>