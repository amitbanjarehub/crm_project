@extends('admin::layouts.app')

@push('styles')

    <link rel="stylesheet" href="{{ asset(
        'css/modules/task.css'
    ) }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset(
        'css/modules/task-kanban.css'
    ) }}?v={{ time() }}">

@endpush

@push('scripts')

    <script src="{{ asset(
        'js/modules/task-kanban.js'
    ) }}?v={{ time() }}" defer></script>

@endpush

@section('content')

    <div id="taskKanbanApp" data-board-url="{{ route('task.kanban.board') }}" data-move-url-template="{{ route(
        'task.kanban.move',
        ['task' => '__TASK__']
    ) }}" data-details-url-template="{{ route(
        'task.kanban.details',
        ['task' => '__TASK__']
    ) }}" data-column-order-url="{{ route(
        'task.kanban.column-order'
    ) }}" data-preference-url="{{ route(
        'task.kanban.preference'
    ) }}">

        <div class="task-kanban-header">

            <div>
                <span class="task-kanban-eyebrow">
                    TASK MANAGEMENT
                </span>

                <h1>
                    Task Kanban Board
                </h1>

                <p>
                    Manage tasks visually by status or priority.
                </p>
            </div>

            <div class="task-kanban-header-actions">

                <a href="{{ route('task.index') }}" class="secondary-btn">
                    ← Table View
                </a>

            </div>

        </div>

        <form id="taskKanbanFilterForm" class="task-kanban-toolbar">

            <div class="task-kanban-field">

                <label>
                    GROUP BY
                </label>

                <select id="taskKanbanGroupBy">
                    <option value="status" @selected(
                        $groupBy === 'status'
                    )>
                        Status
                    </option>

                    <option value="priority" @selected(
                        $groupBy === 'priority'
                    )>
                        Priority
                    </option>
                </select>

            </div>

            <div class="task-kanban-field search-field">

                <label>
                    SEARCH
                </label>

                <input type="text" id="taskKanbanSearch" name="search" value="{{ $filters['search'] }}"
                    placeholder="Search task, project or project code...">

            </div>

            <div class="task-kanban-field">

                <label>
                    STATUS
                </label>

                <select id="taskKanbanStatus" name="status">

                    <option value="">
                        All Statuses
                    </option>

                    @foreach(
                            $statuses as $slug => $name
                        )

                        <option value="{{ $slug }}" @selected(
                            $filters['status']
                            === $slug
                        )>
                            {{ $name }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="task-kanban-field">

                <label>
                    PRIORITY
                </label>

                <select id="taskKanbanPriority" name="priority">

                    <option value="">
                        All Priorities
                    </option>

                    @foreach(
                            $priorities as $slug => $name
                        )

                        <option value="{{ $slug }}" @selected(
                            $filters['priority']
                            === $slug
                        )>
                            {{ $name }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="task-kanban-field">

                <label>
                    DUE DATE
                </label>

                <select id="taskKanbanDue" name="due">

                    <option value="">
                        All Due Dates
                    </option>

                    <option value="today">
                        Due Today
                    </option>

                    <option value="overdue">
                        Overdue
                    </option>

                    <option value="upcoming">
                        Upcoming
                    </option>

                </select>

            </div>

            <div class="task-kanban-options">

                <label class="task-kanban-check">

                    <input type="checkbox" id="taskKanbanHideEmpty" @checked(
                        $preference
                            ->hide_empty_columns
                    )>

                    <span>
                        Hide empty columns
                    </span>

                </label>

            </div>

            <div class="task-kanban-actions">

                <button type="button" class="secondary-btn" id="taskKanbanReset">
                    Reset Filters
                </button>

                <button type="button" class="secondary-btn" id="taskKanbanUndo" disabled>
                    Undo Last Move
                </button>

                <button type="button" class="primary-btn" id="taskKanbanRefresh">
                    Refresh
                </button>

            </div>

        </form>

        <div class="task-kanban-statusbar">

            <div>
                <span>
                    Total Tasks:
                </span>

                <strong id="taskKanbanTotal">
                    {{ $totalTasks }}
                </strong>
            </div>

            <div>
                <span>
                    Last Updated:
                </span>

                <strong id="taskKanbanUpdated">
                    {{ $updatedAt }}
                </strong>
            </div>

            <div>
                <span id="taskKanbanSavingState" class="task-kanban-saving">
                    Saved
                </span>
            </div>

        </div>

        <div id="taskKanbanBoardContainer" class="task-kanban-board-container">

            @include(
                'task::kanban.partials.board',
                [
                    'columns' => $columns,
                    'groupBy' => $groupBy,
                ]
            )

        </div>

    </div>

    <div id="taskKanbanDrawerOverlay" class="task-kanban-drawer-overlay" aria-hidden="true">

        <aside id="taskKanbanDrawer" class="task-kanban-drawer">
        </aside>

    </div>

    <div id="taskKanbanToastContainer" class="task-kanban-toast-container"></div>

@endsection