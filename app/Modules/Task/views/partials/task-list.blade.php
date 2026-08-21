@php
    /*
     * Task Management aur My Tasks dono pages ke liye
     * reset/action route alag rahega.
     */
    $listingRoute = $onlyMyTasks
        ? route('task.my')
        : route('task.index');

    $hasActiveFilters =
        $search !== ''
        || $status !== ''
        || $priority !== ''
        || $due !== '';


    $fromParam = $onlyMyTasks ? 'my' : 'index';

@endphp

{{-- Task Filters --}}
<form method="GET" action="{{ $listingRoute }}" class="task-filter-form">
    <div class="task-search-box">
        <span>🔍</span>

        <input type="text" name="search" value="{{ $search }}" placeholder="Search task, project or project code..."
            autocomplete="off">
    </div>

    <select name="status">
        <option value="">All Statuses</option>

        @foreach($statuses as $statusKey => $statusLabel)
            <option value="{{ $statusKey }}" @selected($status === $statusKey)>
                {{ $statusLabel }}
            </option>
        @endforeach
    </select>

    <select name="priority">
        <option value="">All Priorities</option>

        @foreach($priorities as $priorityKey => $priorityLabel)
            <option value="{{ $priorityKey }}" @selected($priority === $priorityKey)>
                {{ $priorityLabel }}
            </option>
        @endforeach
    </select>

    <select name="due">
        <option value="">All Due Dates</option>

        <option value="today" @selected($due === 'today')>
            Due Today
        </option>

        <option value="overdue" @selected($due === 'overdue')>
            Overdue
        </option>

        <option value="upcoming" @selected($due === 'upcoming')>
            Upcoming
        </option>
    </select>

    <select name="per_page">
        @foreach([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" @selected($perPage === $size)>
                {{ $size }} / page
            </option>
        @endforeach
    </select>

    <button type="submit" class="primary-btn">
        Apply
    </button>

    <a href="{{ $listingRoute }}" class="secondary-btn">
        Reset
    </a>
</form>

{{-- Task Table --}}
<div class="table-wrapper">

    <table class="admin-table task-table">

        <thead>
            <tr>
                <th>#</th>
                <th>Task</th>
                <th>Project</th>
                <th>Service</th>
                <th>Assigned To</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Dependency</th>
                <th>Progress</th>
                <th>Tracked Time</th>
                <th>Due Date</th>
                <th width="250">Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse($tasks as $key => $task)

                        <!-- @php
                                                                                                    $isOverdue =
                                                                                                        $task->due_at
                                                                                                        && $task->due_at->isPast()
                                                                                                        && !in_array(
                                                                                                            $task->status,
                                                                                                            [
                                                                                                                'completed',
                                                                                                                'cancelled',
                                                                                                            ],
                                                                                                            true
                                                                                                        );

                                                                                                    $isDueToday =
                                                                                                        $task->due_at
                                                                                                        && $task->due_at->isToday()
                                                                                                        && !in_array(
                                                                                                            $task->status,
                                                                                                            [
                                                                                                                'completed',
                                                                                                                'cancelled',
                                                                                                            ],
                                                                                                            true
                                                                                                        );

                                                                                                    $pendingDependencyCount =
                                                                                                        $task->prerequisiteTasks
                                                                                                            ->where('status', '!=', 'completed')
                                                                                                            ->count();

                                                                                                    $totalDependencyCount =
                                                                                                        $task->prerequisiteTasks->count();
                                                                                                @endphp -->

                        @php
                            /*
                             * Dynamic Status and Priority display data.
                             */
                            $priorityColor =
                                $task
                                    ->priorityDefinition
                                        ?->color
                                ?? '#64748B';

                            $priorityLabel =
                                $task
                                    ->priorityDefinition
                                        ?->name
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $task->priority
                                    )
                                );

                            $statusColor =
                                $task
                                    ->statusDefinition
                                        ?->color
                                ?? '#64748B';

                            $statusLabel =
                                $task
                                    ->statusDefinition
                                        ?->name
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $task->status
                                    )
                                );

                            /*
                             * Database-configured closed status ke according
                             * overdue aur due-today calculate hoga.
                             */
                            $isClosedTask =
                                $task->isClosed();

                            $isOverdue =
                                $task->due_at
                                && $task->due_at->isPast()
                                && !$isClosedTask;

                            $isDueToday =
                                $task->due_at
                                && $task->due_at->isToday()
                                && !$isClosedTask;

                            /*
                             * Sirf system Completed status dependency
                             * satisfy karega.
                             */
                            $pendingDependencyCount =
                                $task
                                    ->prerequisiteTasks
                                    ->filter(
                                        fn($prerequisiteTask) =>
                                        !$prerequisiteTask
                                            ->isCompleted()
                                    )
                                    ->count();

                            $totalDependencyCount =
                                $task
                                    ->prerequisiteTasks
                                    ->count();
                        @endphp

                        <tr>

                            <td>
                                {{ $tasks->firstItem() + $key }}
                            </td>

                            {{-- Task --}}
                            <td>
                                <div class="task-name-cell">
                                    <strong>
                                        {{ $task->title }}
                                    </strong>

                                    <small>
                                        Task #{{ $task->id }}
                                    </small>
                                </div>
                            </td>

                            {{-- Project --}}
                            <td>
                                <div class="task-project-cell">
                                    <strong>
                                        {{ $task->project?->name ?? 'Unknown Project' }}
                                    </strong>

                                    <small>
                                        {{ $task->project?->project_code ?? '-' }}
                                    </small>
                                </div>
                            </td>

                            {{-- Service --}}
                            <td>
                                {{ $task->projectService?->name ?? '-' }}
                            </td>

                            {{-- Assigned User --}}
                            <td>
                                @if($task->assignedUser)
                                    <div class="task-user-cell">
                                        <strong>
                                            {{ $task->assignedUser->name }}
                                        </strong>

                                        <small>
                                            {{ $task->assignedUser->email }}
                                        </small>
                                    </div>
                                @else
                                    <span class="task-unassigned-badge">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            {{-- Dynamic Priority --}}
                            <td>
                                <span class="dynamic-task-option-badge"
                                    style="--task-option-color:
                                                                                                               {{ $priorityColor }}">
                                    {{ $priorityLabel }}
                                </span>
                            </td>


                            {{-- Dynamic Status --}}
                            <td>
                                <span class="dynamic-task-option-badge" style="--task-option-color:
                                                                        {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            {{-- Dependency --}}
                            <td>
                                @if($pendingDependencyCount > 0)

                                            <div class="task-dependency-table-state blocked">
                                                <strong>
                                                    Blocked
                                                </strong>

                                                <span>
                                                    Waiting for
                                                    {{ $pendingDependencyCount }}
                                                    {{ $pendingDependencyCount === 1
                                    ? 'task'
                                    : 'tasks' }}
                                                </span>
                                            </div>

                                @elseif($totalDependencyCount > 0)

                                    <div class="task-dependency-table-state ready">
                                        <strong>
                                            Ready
                                        </strong>

                                        <span>
                                            All dependencies complete
                                        </span>
                                    </div>

                                @else

                                    <div class="task-dependency-table-state independent">
                                        <strong>
                                            Independent
                                        </strong>

                                        <span>
                                            No dependency
                                        </span>
                                    </div>

                                @endif
                            </td>

                            {{-- Progress --}}
                            <td>
                                <div class="task-progress-cell">
                                    <div class="task-progress-track">
                                        <span style="width: {{ $task->progress_percent }}%"></span>
                                    </div>

                                    <small>
                                        {{ $task->progress_percent }}%
                                    </small>
                                </div>
                            </td>

                            {{-- Tracked Time --}}
                            <td>
                                <div class="task-tracked-time-cell">

                                    <strong>
                                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                    (int) ($task->tracked_seconds ?? 0)
                ) }}
                                    </strong>

                                    @if($task->activeTimeEntries->isNotEmpty())
                                        <span class="task-timer-running-badge">
                                            ● Timer Active
                                        </span>
                                    @endif

                                </div>
                            </td>

                            {{-- Due Date --}}
                            <td>
                                @if($task->due_at)
                                    <div class="task-due-cell">
                                        <strong>
                                            {{ $task->due_at->format('d M Y') }}
                                        </strong>

                                        <small>
                                            {{ $task->due_at->format('h:i A') }}
                                        </small>

                                        @if($isOverdue)
                                            <span class="task-due-badge overdue">
                                                Overdue
                                            </span>
                                        @elseif($isDueToday)
                                            <span class="task-due-badge today">
                                                Due Today
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="task-no-date">
                                        No deadline
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <!-- <td>
                                                                                                                            <div class="task-row-actions">

                                                                                                                                <a href="{{ route('task.show', $task->id) }}" class="table-btn view">
                                                                                                                                    View
                                                                                                                                </a>

                                                                                                                                @if(
                                                                                                                                        auth()->user()->hasPermission('tasks.edit')
                                                                                                                                        && !$task->isClosed()
                                                                                                                                    )
                                                                                                                                    <a href="{{ route('task.edit', $task->id) }}" class="table-btn edit">
                                                                                                                                        Edit
                                                                                                                                    </a>
                                                                                                                                @endif

                                                                                                                            </div>
                                                                                                                        </td> -->

                            {{-- Actions --}}
                            <td>
                                <div class="task-row-actions">

                                    <!-- <a href="{{ route('task.show', $task->id) }}" class="table-btn view">
                                                                View
                                                            </a> -->

                                    <a href="{{ route('task.show', ['task' => $task->id, 'from' => $fromParam]) }}"
                                        class="table-btn view">
                                        View
                                    </a>

                                    @if(
                                                            $onlyMyTasks
                                                            && auth()->user()->hasPermission(
                                                                'time_tracking.use'
                                                            )
                                                            && (int) $task->assigned_to
                                                            === (int) auth()->id()
                                                            && (
                                                                $task->isToDo()
                                                                || $task->isInProgress()
                                                            )
                                                            && $pendingDependencyCount === 0
                                                            && $task->activeTimeEntries->isEmpty()
                                                        )
                                                        <button type="button" class="table-btn time-start-table-btn" data-time-start-url="{{ route(
                                            'timetracking.start',
                                            $task->id
                                        ) }}">
                                                            Start Work
                                                        </button>
                                    @endif

                                    @if(
                                            auth()->user()->hasPermission('tasks.edit')
                                            && !$task->isClosed()
                                        )
                                        <!-- <a href="{{ route('task.edit', $task->id) }}" class="table-btn edit">
                                                            Edit
                                                        </a> -->

                                        <a href="{{ route('task.edit', ['task' => $task->id, 'from' => $fromParam]) }}"
                                            class="table-btn edit">
                                            Edit
                                        </a>
                                    @endif

                                    @if(
                                                            auth()->user()->hasPermission('tasks.delete')
                                                        )
                                                        <form method="POST" action="{{ route(
                                            'task.destroy',
                                            $task->id
                                        ) }}" class="task-inline-delete-form"
                                                            onsubmit="return confirm(
                                                                                                                                                                                                                                                                                                            'Are you sure you want to delete this task? This action will move the task to Trash.'
                                                                                                                                                                                                                                                                                                        );">
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit" class="table-btn delete">
                                                                Delete
                                                            </button>
                                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

            @empty

                <tr>
                    <td colspan="12" class="empty-table task-empty-table">
                        @if($hasActiveFilters)
                            <strong>
                                No matching tasks found.
                            </strong>

                            <span>
                                Filters change karke dobara search karein.
                            </span>
                        @elseif($onlyMyTasks)
                            <strong>
                                No tasks assigned to you.
                            </strong>

                            <span>
                                Assigned tasks yahan appear hongi.
                            </span>
                        @else
                            <strong>
                                No tasks available.
                            </strong>

                            <span>
                                Project service ke andar task create karein.
                            </span>
                        @endif
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

{{-- Pagination --}}
<div class="task-pagination-wrapper">

    <div class="task-pagination-summary">

        @if($tasks->total() > 0)
            Showing

            <strong>
                {{ $tasks->firstItem() }}
            </strong>

            to

            <strong>
                {{ $tasks->lastItem() }}
            </strong>

            of

            <strong>
                {{ $tasks->total() }}
            </strong>

            tasks
        @else
            0 tasks found
        @endif

    </div>

    @if($tasks->hasPages())

        <div class="simple-pagination">

            @if($tasks->onFirstPage())
                <span class="page-link disabled">
                    Previous
                </span>
            @else
                <a href="{{ $tasks->previousPageUrl() }}" class="page-link">
                    Previous
                </a>
            @endif

            <span class="task-page-info">
                Page {{ $tasks->currentPage() }}
                of {{ $tasks->lastPage() }}
            </span>

            @if($tasks->hasMorePages())
                <a href="{{ $tasks->nextPageUrl() }}" class="page-link">
                    Next
                </a>
            @else
                <span class="page-link disabled">
                    Next
                </span>
            @endif

        </div>

    @endif

</div>