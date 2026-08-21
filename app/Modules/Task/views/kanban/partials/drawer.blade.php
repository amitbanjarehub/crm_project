<div class="task-kanban-drawer-header">

    <div>

        <span>
            TASK #{{ $task->id }}
        </span>

        <h2>
            {{ $task->title }}
        </h2>

        <p>
            {{ $task->project?->name ?? 'No Project' }}
        </p>

    </div>

    <button type="button" id="taskKanbanDrawerClose" class="task-kanban-drawer-close">
        ×
    </button>

</div>

<div class="task-kanban-drawer-body">

    <div class="task-kanban-drawer-badges">

        <span style="
                --task-kanban-badge-color:
                {{ $task->statusDefinition?->color
    ?? '#64748b' }};
            ">
            {{ $task->statusDefinition?->name
    ?? $task->status }}
        </span>

        <span style="
                --task-kanban-badge-color:
                {{ $task->priorityDefinition?->color
    ?? '#64748b' }};
            ">
            {{ $task->priorityDefinition?->name
    ?? $task->priority }}
        </span>

    </div>

    <section class="task-kanban-drawer-section">

        <h3>
            Task Information
        </h3>

        <div class="task-kanban-detail-grid">

            <div>
                <span>
                    Project
                </span>

                <strong>
                    {{ $task->project?->name ?? '-' }}
                </strong>
            </div>

            <div>
                <span>
                    Service
                </span>

                <strong>
                    {{ $task->projectService?->name ?? '-' }}
                </strong>
            </div>

            <div>
                <span>
                    Assigned To
                </span>

                <strong>
                    {{ $task->assignedUser?->name
    ?? 'Unassigned' }}
                </strong>
            </div>

            <div>
                <span>
                    Progress
                </span>

                <strong>
                    {{ $task->progress_percent }}%
                </strong>
            </div>

            <div>
                <span>
                    Start Date
                </span>

                <strong>
                    {{ $task->start_date?->format(
    'd M Y'
) ?? '-' }}
                </strong>
            </div>

            <div>
                <span>
                    Due Date
                </span>

                <strong>
                    {{ $task->due_at?->format(
    'd M Y h:i A'
) ?? '-' }}
                </strong>
            </div>

        </div>

    </section>

    @if($task->description)

        <section class="task-kanban-drawer-section">

            <h3>
                Description
            </h3>

            <div class="task-kanban-notes">
                {{ $task->description }}
            </div>

        </section>

    @endif

    <section class="task-kanban-drawer-section">

        <h3>
            Review
        </h3>

        <div class="task-kanban-detail-grid">

            <div>
                <span>
                    Requires Review
                </span>

                <strong>
                    {{ $task->requires_review
    ? 'Yes'
    : 'No' }}
                </strong>
            </div>

            <div>
                <span>
                    Reviewer
                </span>

                <strong>
                    {{ $task->reviewer?->name ?? '-' }}
                </strong>
            </div>

        </div>

    </section>

</div>

<div class="task-kanban-drawer-footer">

    <!-- <a
        href="{{ route(
            'task.show',
            $task->id
        ) }}"
        class="primary-btn task-kanban-full-btn"
    >
        View Full Task
    </a> -->

    <a href="{{ route('task.show', ['task' => $task->id, 'from' => 'kanban']) }}"
        class="primary-btn task-kanban-full-btn">
        View Full Task
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
                ) }}" class="secondary-btn task-kanban-full-btn">
                    Edit Task
                </a> -->

        <a href="{{ route('task.edit', ['task' => $task->id, 'from' => 'kanban']) }}"
            class="secondary-btn task-kanban-full-btn">
            Edit Task
        </a>

    @endif

</div>