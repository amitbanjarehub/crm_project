@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset('css/modules/task.css') }}?v={{ time() }}">
@endpush

@section('content')

            @php
                $loggedInUser = auth()->user();

                $canViewAllTasks =
                    $loggedInUser->isSuperAdmin()
                    || $loggedInUser->hasPermission('tasks.view_all');

                $backRoute = $canViewAllTasks
                    ? route('task.index')
                    : route('task.my');

                $isProjectManager =
                    (int) $task->project->project_manager_id
                    === (int) $loggedInUser->id;

                $canReviewTask =
                    $loggedInUser->isSuperAdmin()
                    || $isProjectManager
                    || (
                        $task->reviewer_id
                        && (int) $task->reviewer_id
                        === (int) $loggedInUser->id
                    );

                $isOverdue = $task->isOverdue();

                $completedStatusSlug =
                    \App\Modules\Task\Models\Task::completedStatus();

                $pendingPrerequisites = $task
                    ->prerequisiteTasks
                    ->filter(
                        fn($prerequisite) =>
                        !$prerequisite->isCompleted()
                    );

                $hasPendingDependencies =
                    $pendingPrerequisites->isNotEmpty();

                $canManageDependencies =
                    (
                        $loggedInUser->isSuperAdmin()
                        || $loggedInUser->hasPermission(
                            'tasks.manage_dependencies'
                        )
                    )
                    && !$task->isClosed()
                    && !$task->isInReview();

                $canUseTimeTracking =
                    $loggedInUser->hasPermission(
                        'time_tracking.use'
                    );

                $isTaskAssignedToCurrentUser =
                    (int) $task->assigned_to
                    === (int) $loggedInUser->id;

                $isCurrentTimerOnThisTask =
                    $currentUserActiveEntry
                    && (int) $currentUserActiveEntry->task_id
                    === (int) $task->id;

                $canStartTaskTimer =
                    $canUseTimeTracking
                    && $isTaskAssignedToCurrentUser
                    && !$task->isClosed()
                    && !$task->isBlocked()
                    && !$task->isInReview()
                    && !$hasPendingDependencies;
            @endphp

            <div class="content-card">

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Error Message --}}
                @if(session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="alert alert-error">
                        <strong>
                            Please fix the following errors:
                        </strong>

                        <ul class="error-list">
                            @foreach($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Task Header --}}
                <div class="page-card-header">

                    <div>
                        <h1>
                            {{ $task->title }}
                        </h1>

                        <p>
                            Task #{{ $task->id }}
                            · {{ $task->project->project_code }}
                            · {{ $task->project->name }}
                        </p>
                    </div>

                    <div class="task-header-actions">

                        <a href="{{ $backRoute }}" class="secondary-btn">
                            Back
                        </a>

                        <a href="{{ route(
        'project.show',
        $task->project_id
    ) }}" class="secondary-btn">
                            Open Project
                        </a>

                        @if(
                                $loggedInUser->hasPermission('tasks.edit')
                                && !$task->isClosed()
                            )
                                    <a href="{{ route('task.edit', $task->id) }}" class="primary-btn">
                                        Edit Task
                                    </a>
                        @endif

                        @if(
                                                $loggedInUser->hasPermission('tasks.delete')
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

                                                                            <button type="submit" class="danger-btn">
                                                                                Delete Task
                                                                            </button>
                                                                        </form>
                        @endif

                    </div>

                </div>

                {{-- Task Summary --}}
                <section class="task-detail-section">

                    <div class="task-section-heading">
                        <div>
                            <h2>Task Summary</h2>

                            <p>
                                Task assignment, status and deadline information.
                            </p>
                        </div>
                    </div>

                    <div class="task-summary-grid">

                        <div class="task-summary-box">
                            <span>Project</span>

                            <strong>
                                {{ $task->project->name }}
                            </strong>

                            <small>
                                {{ $task->project->project_code }}
                            </small>
                        </div>

                        <div class="task-summary-box">
                            <span>Service</span>

                            <strong>
                                {{ $task->projectService?->name ?? '-' }}
                            </strong>
                        </div>

                        <div class="task-summary-box">
                            <span>Assigned To</span>

                            <strong>
                                {{ $task->assignedUser?->name ?? 'Unassigned' }}
                            </strong>

                            @if($task->assignedUser)
                                <small>
                                    {{ $task->assignedUser->email }}
                                </small>
                            @endif
                        </div>

                        <div class="task-summary-box">
                            <span>Priority</span>

                            <strong>
                                <span class="dynamic-task-option-badge" style="--task-option-color:
                                                        {{ $task
        ->priorityDefinition
            ?->color
        ?? '#64748B' }}">
                                    {{ $task
        ->priorityDefinition
            ?->name
        ?? ucfirst(
            str_replace(
                '_',
                ' ',
                $task->priority
            )
        ) }}
                                </span>
                            </strong>
                        </div>
                        <div class="task-summary-box">
                            <span>Status</span>

                            <strong>
                                <span class="dynamic-task-option-badge" style="--task-option-color:
                                                    {{ $task
        ->statusDefinition
            ?->color
        ?? '#64748B' }}">
                                    {{ $task
        ->statusDefinition
            ?->name
        ?? ucfirst(
            str_replace(
                '_',
                ' ',
                $task->status
            )
        ) }}
                                </span>
                            </strong>
                        </div>

                        <div class="task-summary-box">
                            <span>Progress</span>

                            <strong>
                                {{ $task->progress_percent }}%
                            </strong>

                            <div class="task-summary-progress">
                                <span style="width: {{ $task->progress_percent }}%"></span>
                            </div>
                        </div>

                        <div class="task-summary-box">
                            <span>Start Date</span>

                            <strong>
                                {{ $task->start_date
        ? $task->start_date->format('d M Y')
        : '-' }}
                            </strong>
                        </div>

                        <div class="task-summary-box">
                            <span>Due Date</span>

                            <strong>
                                {{ $task->due_at
        ? $task->due_at->format(
            'd M Y, h:i A'
        )
        : '-' }}
                            </strong>

                            @if($isOverdue)
                                <small class="task-overdue-text">
                                    This task is overdue.
                                </small>
                            @endif
                        </div>

                        <div class="task-summary-box">
                            <span>Estimated Hours</span>

                            <strong>
                                {{ $task->estimated_hours
        ? $task->estimated_hours . ' Hours'
        : '-' }}
                            </strong>
                        </div>

                        <div class="task-summary-box">
                            <span>Review Required</span>

                            <strong>
                                {{ $task->requires_review
        ? 'Yes'
        : 'No' }}
                            </strong>
                        </div>

                        <div class="task-summary-box">
                            <span>Reviewer</span>

                            <strong>
                                {{ $task->reviewer?->name
        ?? $task->project->manager?->name
        ?? 'Project Manager' }}
                            </strong>
                        </div>

                        <div class="task-summary-box">
                            <span>Created By</span>

                            <strong>
                                {{ $task->creator?->name ?? 'Unknown User' }}
                            </strong>

                            <small>
                                {{ $task->created_at->format(
        'd M Y, h:i A'
    ) }}
                            </small>
                        </div>

                    </div>

                    @if($task->description)
                        <div class="task-description-card">
                            <h3>Description</h3>

                            <p>
                                {!! nl2br(e($task->description)) !!}
                            </p>
                        </div>
                    @endif

                </section>

                {{-- Time Tracking --}}
                <section class="task-detail-section time-tracking-task-panel" data-time-task-panel="{{ $task->id }}"
                    data-total-seconds="{{ $taskTrackedSeconds }}">

                    <div class="task-section-heading">

                        <div>
                            <h2>Time Tracking</h2>

                            <p>
                                Start, pause, resume and end work for this task.
                            </p>
                        </div>

                        <span class="time-tracking-status-badge" data-time-task-status>
                            @if($isCurrentTimerOnThisTask)
                                                                                        {{ ucfirst(
                                    $currentUserActiveEntry->status
                                ) }}
                            @else
                                Inactive
                            @endif
                        </span>

                    </div>

                    <div class="time-tracking-task-grid">

                        <div class="time-tracking-stat-card">
                            <span>Current Session</span>

                            <strong data-time-task-clock>
                                @if($isCurrentTimerOnThisTask)
                                                                                                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                        $currentUserActiveEntry->liveSeconds()
                                    ) }}
                                @else
                                    00:00:00
                                @endif
                            </strong>
                        </div>

                        <div class="time-tracking-stat-card">
                            <span>Total Task Time</span>

                            <strong data-time-task-total>
                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
        $taskTrackedSeconds
    ) }}
                            </strong>
                        </div>

                        <div class="time-tracking-stat-card">
                            <span>Estimated Time</span>

                            <strong>
                                {{ $task->estimated_hours
        ? $task->estimated_hours . ' Hours'
        : 'Not Set' }}
                            </strong>
                        </div>

                        <div class="time-tracking-stat-card">
                            <span>Assigned User</span>

                            <strong>
                                {{ $task->assignedUser?->name
        ?? 'Unassigned' }}
                            </strong>
                        </div>

                    </div>

                    @if($canUseTimeTracking)

                        <div class="time-tracking-work-note">
                            <label>
                                Work Note
                            </label>

                            <textarea rows="3" maxlength="2000" data-time-note
                                placeholder="Optional: What are you working on?"></textarea>
                        </div>

                        <div class="time-tracking-task-actions">

                            @if(
                                                    $canStartTaskTimer
                                                    && !$currentUserActiveEntry
                                                )
                                                                        <button type="button" class="time-start-btn" data-time-start-url="{{ route(
                                    'timetracking.start',
                                    $task->id
                                ) }}">
                                                                            ▶ Start Work
                                                                        </button>
                            @endif

                            @if($isCurrentTimerOnThisTask)

                                                            <button type="button" class="time-pause-btn
                                                                                                                                                                                        {{ $currentUserActiveEntry->status === 'running'
                                ? ''
                                : 'time-tracking-hidden' }}" data-time-action="pause">
                                                                Pause
                                                            </button>

                                                            <button type="button" class="time-resume-btn
                                                                                                                                                                                        {{ $currentUserActiveEntry->status === 'paused'
                                ? ''
                                : 'time-tracking-hidden' }}" data-time-action="resume">
                                                                Resume
                                                            </button>

                                                            <button type="button" class="time-stop-btn" data-time-action="stop">
                                                                End Work
                                                            </button>

                            @elseif($currentUserActiveEntry)

                                                                        <div class="time-tracking-other-task-warning">

                                                                            <strong>
                                                                                Another timer is active
                                                                            </strong>

                                                                            <span>
                                                                                {{ $currentUserActiveEntry
                                    ->task?->title }}
                                                                            </span>

                                                                            <a href="{{ route(
                                    'task.show',
                                    $currentUserActiveEntry->task_id
                                ) }}">
                                                                                Open Running Task
                                                                            </a>

                                                                        </div>

                            @elseif(!$canStartTaskTimer)

                                <div class="time-tracking-unavailable">

                                    @if(!$isTaskAssignedToCurrentUser)
                                        Timer can only be started by the assigned user.
                                    @elseif($hasPendingDependencies)
                                        Complete all prerequisite tasks first.
                                    @elseif($task->isBlocked())
                                        Blocked task cannot be tracked.
                                    @elseif($task->isInReview())
                                        Task is currently in review.
                                    @else
                                        Time tracking is unavailable for this task.
                                    @endif

                                </div>

                            @endif

                        </div>

                    @endif

                    {{-- Session History --}}
                    <div class="time-tracking-history">

                        <h3>Work Sessions</h3>

                        <div class="table-wrapper">

                            <table class="admin-table">

                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Started</th>
                                        <th>Ended</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($task->timeEntries as $entry)

                                                                                                                        <tr>
                                                                                                                            <td>
                                                                                                                                {{ $entry->user?->name
                                            ?? $entry->user_name_snapshot
                                            ?? 'Deleted User' }}
                                                                                                                            </td>

                                                                                                                            <td>
                                                                                                                                {{ $entry->role?->name
                                            ?? $entry->role_name_snapshot
                                            ?? '-' }}
                                                                                                                            </td>

                                                                                                                            <td>
                                                                                                                                {{ $entry->started_at
                                                ?->format(
                                                'd M Y, h:i A'
                                            ) }}
                                                                                                                            </td>

                                                                                                                            <td>
                                                                                                                                {{ $entry->stopped_at
                                                ?->format(
                                                'd M Y, h:i A'
                                            )
                                            ?? '-' }}
                                                                                                                            </td>

                                                                                                                            <td>
                                                                                                                                <span class="time-entry-status status-{{ $entry->status }}">
                                                                                                                                    {{ ucfirst($entry->status) }}
                                                                                                                                </span>
                                                                                                                            </td>

                                                                                                                            <td>
                                                                                                                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                            $entry->liveSeconds()
                                        ) }}
                                                                                                                            </td>

                                                                                                                            <td>
                                                                                                                                {{ $entry->notes ?? '-' }}
                                                                                                                            </td>
                                                                                                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="7" class="empty-table">
                                                No work sessions recorded.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </section>

                {{-- Task Dependencies --}}
                <section class="task-detail-section task-dependency-panel">

                    <div class="task-section-heading">

                        <div>
                            <h2>Task Dependencies</h2>

                            <p>
                                Tasks that must be completed before this task can start.
                            </p>
                        </div>

                        @if($task->prerequisiteTasks->isNotEmpty())
                            <span class="task-record-count">
                                {{ $task->prerequisiteTasks->count() }}
                                dependencies
                            </span>
                        @endif

                    </div>

                    {{-- Dependency Current State --}}
                    @if($hasPendingDependencies)

                                            <div class="task-dependency-warning">
                                                <span class="task-dependency-warning-icon">
                                                    ⏳
                                                </span>

                                                <div>
                                                    <strong>
                                                        This task is blocked
                                                    </strong>

                                                    <p>
                                                        {{ $pendingPrerequisites->count() }}
                                                        prerequisite
                                                        {{ $pendingPrerequisites->count() === 1
                        ? 'task is'
                        : 'tasks are' }}
                                                        still incomplete.
                                                    </p>
                                                </div>
                                            </div>

                    @elseif($task->prerequisiteTasks->isNotEmpty())

                        <div class="task-dependency-ready">
                            <span>✓</span>

                            <div>
                                <strong>
                                    All dependencies completed
                                </strong>

                                <p>
                                    This task is ready to start.
                                </p>
                            </div>
                        </div>

                    @else

                        <div class="task-dependency-independent">
                            <span>○</span>

                            <div>
                                <strong>
                                    Independent Task
                                </strong>

                                <p>
                                    This task does not depend on another task.
                                </p>
                            </div>
                        </div>

                    @endif

                    {{-- Waiting For --}}
                    @if($task->prerequisiteTasks->isNotEmpty())

                                <div class="task-dependency-group">

                                    <h3>Waiting For</h3>

                                    <div class="task-dependency-list">

                                        @foreach(
                                                                            $task->prerequisiteTasks
                                                                            as $prerequisite
                                                                        )

                                                                                                                <article class="task-dependency-item">

                                                                                                                    <div class="task-dependency-item-main">

                                                                                                                        <<div class="task-dependency-icon">
                                                                                                                            {{ $prerequisite->isCompleted()
                                                ? '✓'
                                                : '⏳' }}
                                                                                                                    </div>

                                                                                                                    <div>
                                                                                                                        <strong>
                                                                                                                            {{ $prerequisite->title }}
                                                                                                                        </strong>

                                                                                                                        <span>
                                                                                                                            Task #{{ $prerequisite->id }}

                                                                                                                            @if($prerequisite->projectService)
                                                                                                                                ·
                                                                                                                                {{ $prerequisite->projectService->name }}
                                                                                                                            @endif
                                                                                                                        </span>

                                                                                                                        <small>
                                                                                                                            Assigned To:
                                                                                                                            {{ $prerequisite->assignedUser?->name
                                                ?? 'Unassigned' }}
                                                                                                                        </small>
                                                                                                                    </div>

                                                                                                            </div>

                                                                                                            <div class="task-dependency-actions">

                                                                                                                <span class="dynamic-task-option-badge" style="--task-option-color:
                                                                                                                                                                                                {{ $prerequisite
                                                ->statusDefinition
                                                    ?->color
                                                ?? '#64748B' }}">
                                                                                                                    {{ $prerequisite
                                                ->statusDefinition
                                                    ?->name
                                                ?? ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $prerequisite->status
                                                    )
                                                ) }}
                                                                                                                </span>

                                                                                                                <a href="{{ route(
                                                'task.show',
                                                $prerequisite->id
                                            ) }}" class="table-btn view">
                                                                                                                    Open
                                                                                                                </a>

                                                                                                                @if($canManageDependencies)
                                                                                                                                                                                                                                <form method="POST" action="{{ route(
                                                                                                                        'task.dependencies.destroy',
                                                                                                                        [
                                                                                                                            $task->id,
                                                                                                                            $prerequisite->id
                                                                                                                        ]
                                                                                                                    ) }}"
                                                                                                                                                                                                                                    onsubmit="return confirm(
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'Remove this task dependency?'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        );">
                                                                                                                                                                                                                                    @csrf
                                                                                                                                                                                                                                    @method('DELETE')

                                                                                                                                                                                                                                    <button type="submit" class="table-btn delete">
                                                                                                                                                                                                                                        Remove
                                                                                                                                                                                                                                    </button>
                                                                                                                                                                                                                                </form>
                                                                                                                @endif

                                                                                                            </div>

                                                                                                            </article>

                                        @endforeach

                                </div>

                        </div>

                    @endif

            {{-- Tasks blocked by current Task --}}
            @if($task->dependentTasks->isNotEmpty())

                <div class="task-dependency-group">

                    <h3>Blocks These Tasks</h3>

                    <div class="task-dependency-list">

                        @foreach(
                                            $task->dependentTasks
                                            as $dependentTask
                                        )

                                                        <article class="task-dependency-item">

                                                            <div class="task-dependency-item-main">

                                                                <div class="task-dependency-icon">
                                                                    🔒
                                                                </div>

                                                                <div>
                                                                    <strong>
                                                                        {{ $dependentTask->title }}
                                                                    </strong>

                                                                    <span>
                                                                        Task #{{ $dependentTask->id }}

                                                                        @if($dependentTask->projectService)
                                                                            ·
                                                                            {{ $dependentTask->projectService->name }}
                                                                        @endif
                                                                    </span>

                                                                    <small>
                                                                        Assigned To:
                                                                        {{ $dependentTask->assignedUser?->name
                                ?? 'Unassigned' }}
                                                                    </small>
                                                                </div>

                                                            </div>

                                                            <div class="task-dependency-actions">

                                                                <span class="dynamic-task-option-badge" style="--task-option-color:
                                                                                    {{ $dependentTask
                                ->statusDefinition
                                    ?->color
                                ?? '#64748B' }}">
                                                                    {{ $dependentTask
                                ->statusDefinition
                                    ?->name
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $dependentTask->status
                                    )
                                ) }}
                                                                </span>

                                                                <a href="{{ route(
                                'task.show',
                                $dependentTask->id
                            ) }}" class="table-btn view">
                                                                    Open
                                                                </a>

                                                            </div>

                                                        </article>

                        @endforeach

                    </div>

                </div>

            @endif

            {{-- Add Dependency --}}
            @if($canManageDependencies)

                <div class="task-add-dependency">

                    <div>
                        <h3>Add Dependency</h3>

                        <p>
                            Select another task from this project that must be completed first.
                        </p>
                    </div>

                    @if($availableDependencyTasks->isNotEmpty())

                                            <form method="POST" action="{{ route(
                            'task.dependencies.store',
                            $task->id
                        ) }}" class="task-dependency-form">
                                                @csrf

                                                <select name="depends_on_task_id" required>
                                                    <option value="">
                                                        Select prerequisite task
                                                    </option>

                                                    @foreach(
                                                                                $availableDependencyTasks
                                                                                as $availableTask
                                                                            )
                                                                                            <option value="{{ $availableTask->id }}">
                                                                                                #{{ $availableTask->id }}
                                                                                                —
                                                                                                {{ $availableTask->title }}

                                                                                                @if($availableTask->projectService)
                                                                                                    —
                                                                                                    {{ $availableTask->projectService->name }}
                                                                                                @endif

                                                                                                —
                                                                                                {{ $availableTask
                                                        ->statusDefinition
                                                            ?->name
                                                        ?? ucfirst(
                                                            str_replace(
                                                                '_',
                                                                ' ',
                                                                $availableTask->status
                                                            )
                                                        ) }}
                                                                                            </option>
                                                    @endforeach
                                                </select>

                                                <button type="submit" class="primary-btn">
                                                    Add Dependency
                                                </button>

                                            </form>

                    @else

                        <div class="task-empty-panel">
                            <strong>
                                No tasks available for dependency.
                            </strong>

                            <span>
                                Create another task in this project first.
                            </span>
                        </div>

                    @endif

                </div>

            @endif

            </section>

            {{-- Status Update --}}
            @if(
                        $loggedInUser->hasPermission(
                            'tasks.update_status'
                        )
                        && !$task->isClosed()
                        && !$task->isInReview()
                        && !$task->isBlocked()
                        && !$hasPendingDependencies
                    )
                            <section class="task-detail-section task-status-panel">

                                <div class="task-section-heading">
                                    <div>
                                        <h2>Update Task Status</h2>

                                        <p>
                                            Update current task progress and working status.
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route(
                    'task.status.update',
                    $task->id
                ) }}" class="task-status-form">
                                    @csrf
                                    @method('PATCH')

                                    <div class="form-group">
                                        <label for="task_status">
                                            Status
                                        </label>

                                        <select name="status" id="task_status" required>
                                            @foreach(
                                                    $manualStatuses
                                                    as $statusKey => $statusLabel
                                                )

                                                        @php
                                                            /*
                                                             * Review-required Task direct
                                                             * Completed nahi hogi.
                                                             */
                                                            $hideCompleted =
                                                                $task->requires_review
                                                                && $statusKey
                                                                === $completedStatusSlug;
                                                        @endphp

                                                        @continue($hideCompleted)

                                                        <option value="{{ $statusKey }}" @selected(
                                                            $task->status
                                                            === $statusKey
                                                        )>
                                                            {{ $statusLabel }}
                                                        </option>

                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="progress_percent">
                                            Progress Percentage
                                        </label>

                                        <input type="number" name="progress_percent" id="progress_percent" min="0" max="100"
                                            value="{{ $task->progress_percent }}">
                                    </div>

                                    <div class="task-status-action">
                                        <button type="submit" class="primary-btn">
                                            Update Status
                                        </button>
                                    </div>

                                </form>

                            </section>
            @endif

            {{-- Submit For Review --}}
            @if(
                        $task->requires_review
                        && $task->isInProgress()
                        && $loggedInUser->hasPermission(
                            'tasks.update_status'
                        )
                        && !$hasPendingDependencies
                    )
                            <section class="task-detail-section task-review-submit-panel">

                                <div class="task-section-heading">
                                    <div>
                                        <h2>Submit Task For Review</h2>

                                        <p>
                                            Work complete hone ke baad task reviewer ko submit karein.
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route(
                    'task.submit-review',
                    $task->id
                ) }}" onsubmit="return confirm(
                                                                                                                        'Submit this task for review?'
                                                                                                                    );">
                                    @csrf

                                    <button type="submit" class="primary-btn">
                                        Submit For Review
                                    </button>
                                </form>

                            </section>
            @endif

            {{-- Review and Approval --}}
            @if(
                        $task->isInReview()
                        && $loggedInUser->hasPermission(
                            'tasks.review'
                        )
                        && $canReviewTask
                    )
                            <section class="task-detail-section task-review-panel">

                                <div class="task-section-heading">
                                    <div>
                                        <h2>Review Task</h2>

                                        <p>
                                            Task approve karein ya required changes ke saath return karein.
                                        </p>
                                    </div>
                                </div>

                                <div class="task-review-grid">

                                    {{-- Approve --}}
                                    <form method="POST" action="{{ route(
                    'task.approve',
                    $task->id
                ) }}" class="task-review-form approve" onsubmit="return confirm(
                                                                                                                            'Approve and complete this task?'
                                                                                                                        );">
                                        @csrf

                                        <h3>Approve Task</h3>

                                        <textarea name="review_note" rows="4"
                                            placeholder="Optional approval note">{{ old('review_note') }}</textarea>

                                        <button type="submit" class="primary-btn">
                                            Approve Task
                                        </button>
                                    </form>

                                    {{-- Reject --}}
                                    <form method="POST" action="{{ route(
                    'task.reject',
                    $task->id
                ) }}" class="task-review-form reject">
                                        @csrf

                                        <h3>Return For Changes</h3>

                                        <textarea name="review_note" rows="4" required
                                            placeholder="Explain required changes">{{ old('review_note') }}</textarea>

                                        <button type="submit" class="secondary-btn">
                                            Return For Changes
                                        </button>
                                    </form>

                                </div>

                            </section>
            @endif

            {{-- Existing Review Note --}}
            @if(
                    $task->review_note
                    || $task->reviewed_at
                )
                        <section class="task-detail-section">

                            <div class="task-section-heading">
                                <div>
                                    <h2>Review Information</h2>
                                </div>
                            </div>

                            <div class="task-review-information">

                                <strong>
                                    Reviewed By:
                                    {{ $task->reviewer?->name
                ?? 'Unknown User' }}
                                </strong>

                                @if($task->reviewed_at)
                                                                    <small>
                                                                        {{ $task->reviewed_at->format(
                                        'd M Y, h:i A'
                                    ) }}
                                                                    </small>
                                @endif

                                @if($task->review_note)
                                    <p>
                                        {!! nl2br(e($task->review_note)) !!}
                                    </p>
                                @endif

                            </div>

                        </section>
            @endif

            {{-- Comments --}}
            <section class="task-detail-section task-comment-panel">

                <div class="task-section-heading">
                    <div>
                        <h2>Comments & Work Updates</h2>

                        <p>
                            Team members ke work updates aur discussion.
                        </p>
                    </div>

                    <span class="task-record-count">
                        {{ $task->comments->count() }} comments
                    </span>
                </div>

                @if(
                                $loggedInUser->hasPermission(
                                    'task_comments.create'
                                )
                            )
                                        <form method="POST" action="{{ route(
                        'task.comments.store',
                        $task->id
                    ) }}" class="task-comment-form">
                                            @csrf

                                            <textarea name="comment" rows="4" required
                                                placeholder="Add work update or comment">{{ old('comment') }}</textarea>

                                            <button type="submit" class="primary-btn">
                                                Add Comment
                                            </button>
                                        </form>
                @endif

                <div class="task-comment-list">

                    @forelse(
                                        $task->comments->sortByDesc('created_at')
                                        as $comment
                                    )

                                                        @php
                                                            $canDeleteComment =
                                                                $loggedInUser->isSuperAdmin()
                                                                || $isProjectManager
                                                                || (
                                                                    (int) $comment->user_id
                                                                    === (int) $loggedInUser->id
                                                                );
                                                        @endphp

                                                        <article class="task-comment-item">

                                                            <div class="task-comment-avatar">
                                                                {{ strtoupper(
                            substr(
                                $comment->user?->name ?? 'U',
                                0,
                                1
                            )
                        ) }}
                                                            </div>

                                                            <div class="task-comment-content">

                                                                <div class="task-comment-header">

                                                                    <div>
                                                                        <strong>
                                                                            {{ $comment->user?->name
                            ?? 'Deleted User' }}
                                                                        </strong>

                                                                        <small>
                                                                            {{ $comment->created_at->format(
                            'd M Y, h:i A'
                        ) }}
                                                                        </small>
                                                                    </div>

                                                                    @if(
                                                                                                                    $loggedInUser->hasPermission(
                                                                                                                        'task_comments.delete'
                                                                                                                    )
                                                                                                                    && $canDeleteComment
                                                                                                                )
                                                                                                                                                            <form method="POST" action="{{ route(
                                                                            'task.comments.destroy',
                                                                            [
                                                                                $task->id,
                                                                                $comment->id
                                                                            ]
                                                                        ) }}"
                                                                                                                                                                onsubmit="return confirm(
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    'Delete this comment?'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                );">
                                                                                                                                                                @csrf
                                                                                                                                                                @method('DELETE')

                                                                                                                                                                <button type="submit" class="table-btn delete">
                                                                                                                                                                    Delete
                                                                                                                                                                </button>
                                                                                                                                                            </form>
                                                                    @endif

                                                                </div>

                                                                <p>
                                                                    {!! nl2br(e($comment->comment)) !!}
                                                                </p>

                                                            </div>

                                                        </article>

                    @empty

                        <div class="task-empty-panel">
                            <strong>
                                No comments yet.
                            </strong>

                            <span>
                                Work updates and team discussion will appear here.
                            </span>
                        </div>

                    @endforelse

                </div>

            </section>

            {{-- Attachments --}}
            <section class="task-detail-section task-attachment-panel">

                <div class="task-section-heading">

                    <div>
                        <h2>Attachments</h2>

                        <p>
                            Task documents, screenshots and work files.
                        </p>
                    </div>

                    <span class="task-record-count">
                        {{ $task->attachments->count() }} files
                    </span>

                </div>

                @if(
                                $loggedInUser->hasPermission(
                                    'task_attachments.upload'
                                )
                            )
                                        <form method="POST" enctype="multipart/form-data" action="{{ route(
                        'task.attachments.store',
                        $task->id
                    ) }}" class="task-attachment-form">
                                            @csrf

                                            <div class="form-group">
                                                <label for="attachment">
                                                    Select File
                                                </label>

                                                <input type="file" name="attachment" id="attachment" required
                                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip">

                                                <small>
                                                    PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, JPEG or ZIP.
                                                    Maximum 10 MB.
                                                </small>
                                            </div>

                                            <button type="submit" class="primary-btn">
                                                Upload Attachment
                                            </button>

                                        </form>
                @endif

                <div class="task-attachment-list">

                    @forelse(
                                        $task->attachments->sortByDesc('created_at')
                                        as $attachment
                                    )

                                                        @php
                                                            $canDeleteAttachment =
                                                                $loggedInUser->isSuperAdmin()
                                                                || $isProjectManager
                                                                || (
                                                                    (int) $attachment->uploaded_by
                                                                    === (int) $loggedInUser->id
                                                                );

                                                            $fileSize = $attachment->file_size >= 1048576
                                                                ? number_format(
                                                                    $attachment->file_size / 1048576,
                                                                    2
                                                                ) . ' MB'
                                                                : number_format(
                                                                    $attachment->file_size / 1024,
                                                                    2
                                                                ) . ' KB';
                                                        @endphp

                                                        <article class="task-attachment-item">

                                                            <div class="task-file-icon">
                                                                📎
                                                            </div>

                                                            <div class="task-file-information">

                                                                <strong>
                                                                    {{ $attachment->original_name }}
                                                                </strong>

                                                                <span>
                                                                    Uploaded by
                                                                    {{ $attachment->uploader?->name
                            ?? 'Deleted User' }}
                                                                </span>

                                                                <small>
                                                                    {{ $fileSize }}
                                                                    ·
                                                                    {{ $attachment->created_at->format(
                            'd M Y, h:i A'
                        ) }}
                                                                </small>

                                                            </div>

                                                            <div class="task-file-actions">

                                                                @if(
                                                                                                            $loggedInUser->hasPermission(
                                                                                                                'task_attachments.download'
                                                                                                            )
                                                                                                        )
                                                                                                                                            <a href="{{ route(
                                                                        'task.attachments.download',
                                                                        [
                                                                            $task->id,
                                                                            $attachment->id
                                                                        ]
                                                                    ) }}" class="table-btn view">
                                                                                                                                                Download
                                                                                                                                            </a>
                                                                @endif

                                                                @if(
                                                                                                            $loggedInUser->hasPermission(
                                                                                                                'task_attachments.delete'
                                                                                                            )
                                                                                                            && $canDeleteAttachment
                                                                                                        )
                                                                                                                                            <form method="POST" action="{{ route(
                                                                        'task.attachments.destroy',
                                                                        [
                                                                            $task->id,
                                                                            $attachment->id
                                                                        ]
                                                                    ) }}"
                                                                                                                                                onsubmit="return confirm(
                                                                                                                                                                                                                                                                                                                                                                                                                                                'Delete this attachment?'
                                                                                                                                                                                                                                                                                                                                                                                                                                            );">
                                                                                                                                                @csrf
                                                                                                                                                @method('DELETE')

                                                                                                                                                <button type="submit" class="table-btn delete">
                                                                                                                                                    Delete
                                                                                                                                                </button>
                                                                                                                                            </form>
                                                                @endif

                                                            </div>

                                                        </article>

                    @empty

                        <div class="task-empty-panel">
                            <strong>
                                No attachments uploaded.
                            </strong>

                            <span>
                                Task-related documents and files will appear here.
                            </span>
                        </div>

                    @endforelse

                </div>

            </section>

            </div>

@endsection