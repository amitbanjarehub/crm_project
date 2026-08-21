<div
    id="taskKanbanBoard"
    class="task-kanban-board"
>

    @forelse(
        $columns as $column
    )

        <section
            class="task-kanban-column"
            data-column-slug="{{ $column['slug'] }}"
            style="
                --task-kanban-column-color:
                {{ $column['color'] }};
            "
        >

            <header class="task-kanban-column-header">

                <button
                    type="button"
                    class="task-kanban-column-handle"
                    draggable="true"
                    title="Move column"
                >
                    ⋮⋮
                </button>

                <div class="task-kanban-column-title">

                    <span
                        class="task-kanban-column-color"
                    ></span>

                    <strong>
                        {{ $column['name'] }}
                    </strong>

                </div>

                <span
                    class="task-kanban-column-count"
                >
                    {{ $column['count'] }}
                </span>

            </header>

            <div
                class="task-kanban-card-list"
                data-column-slug="{{ $column['slug'] }}"
            >

                @forelse(
                    $column['tasks'] as $task
                )

                    @include(
                        'task::kanban.partials.card',
                        [
                            'task' => $task,
                            'groupBy' => $groupBy,
                        ]
                    )

                @empty

                    <div class="task-kanban-empty-column">
                        Drop a Task here
                    </div>

                @endforelse

            </div>

        </section>

    @empty

        <div class="task-kanban-board-empty">

            <strong>
                No tasks found.
            </strong>

            <span>
                Try changing your filters.
            </span>

        </div>

    @endforelse

</div>