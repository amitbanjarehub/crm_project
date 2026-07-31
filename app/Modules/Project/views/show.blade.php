@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/project.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/task.css') }}?v={{ time() }}">
@endpush

@section('content')

    <div class="content-card">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="page-card-header">
            <div>
                <h1>{{ $project->name }}</h1>
                <p>
                    {{ $project->project_code }}
                    · {{ $project->client->name }}
                </p>
            </div>

            <div class="project-actions">
                <a href="{{ route('project.index') }}" class="secondary-btn">
                    Back
                </a>

                @if(
                                auth()->user()->hasPermission(
                                    'reports.projects.view'
                                )
                            )
                            <a href="{{ route(
                        'report.projects.show',
                        $project->id
                    ) }}" class="secondary-btn">
                                View Report
                            </a>
                @endif

                @if(
                        auth()->user()->hasPermission('projects.edit')
                        && !$project->isClosed()
                    )
                    <a href="{{ route('project.edit', $project->id) }}" class="secondary-btn">
                        Edit Project
                    </a>
                @endif

                @if(
                        auth()->user()->hasPermission('projects.complete')
                        && !$project->isClosed()
                    )
                    <form method="POST" action="{{ route('project.complete', $project->id) }}"
                        onsubmit="return confirm('Complete this project?');">
                        @csrf
                        <button type="submit" class="primary-btn">
                            Complete Project
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="project-summary-grid">
            <div class="project-summary-box">
                <span>Client</span>
                <strong>{{ $project->client->name }}</strong>
            </div>

            <div class="project-summary-box">
                <span>Manager</span>
                <strong>{{ $project->manager?->name ?? 'Unassigned' }}</strong>
            </div>

            <div class="project-summary-box">
                <span>Status</span>
                <strong>{{ $projectStatuses[$project->status] }}</strong>
            </div>

            <div class="project-summary-box">
                <span>Priority</span>
                <strong>{{ $projectPriorities[$project->priority] }}</strong>
            </div>

            <div class="project-summary-box">
                <span>Due Date</span>
                <strong>
                    {{ $project->due_date
        ? $project->due_date->format('d M Y')
        : '-' }}
                </strong>
            </div>

            <div class="project-summary-box">
                <span>Progress</span>
                <strong>{{ $project->progressPercentage() }}%</strong>
            </div>
        </div>

        @if(
                auth()->user()->hasAnyPermission([
                    'time_tracking.view_team',
                    'time_tracking.view_all',
                ])
            )
            <section class="project-section">

                <div class="project-section-header">

                    <div>
                        <h2>Project Time Summary</h2>

                        <p>
                            Total employee time recorded for this project.
                        </p>
                    </div>

                    <a href="{{ route(
                'timetracking.report',
                [
                    'project_id' =>
                        $project->id
                ]
            ) }}" class="secondary-btn">
                        Open Full Report
                    </a>

                </div>

                <div class="time-tracking-summary-grid">

                    <div class="time-tracking-stat-card">
                        <span>Total Tracked Time</span>

                        <strong>
                            {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                $projectTrackedSeconds
            ) }}
                        </strong>
                    </div>

                    <div class="time-tracking-stat-card">
                        <span>Team Members Tracked</span>

                        <strong>
                            {{ $projectTimeByUser->count() }}
                        </strong>
                    </div>

                </div>

                <div class="table-wrapper">

                    <table class="admin-table">

                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Role</th>
                                <th>Total Time</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($projectTimeByUser as $item)

                                            <tr>
                                                <td>{{ $item['name'] }}</td>
                                                <td>{{ $item['role'] }}</td>

                                                <td>
                                                    {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    $item['seconds']
                                ) }}
                                                </td>
                                            </tr>

                            @empty

                                <tr>
                                    <td colspan="3" class="empty-table">
                                        No time tracked yet.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </section>
        @endif

        {{-- Project Members --}}
        <section class="project-section">
            <div class="project-section-header">
                <h2>Project Team</h2>
            </div>

            @if(
                    auth()->user()->hasPermission('projects.manage_members')
                    && !$project->isClosed()
                )
                <form method="POST" action="{{ route('project.members.store', $project->id) }}" class="project-member-form">
                    @csrf

                    <select name="user_id" required>
                        <option value="">Select User</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </select>

                    <input type="text" name="member_role" placeholder="Member role e.g. Developer">

                    <button type="submit" class="primary-btn">
                        Add Member
                    </button>
                </form>
            @endif

            <div class="project-member-grid">
                @foreach($project->members as $member)
                    <div class="project-member-card">
                        <div>
                            <strong>{{ $member->name }}</strong>
                            <span>
                                {{ $member->pivot->member_role ?: 'Team Member' }}
                            </span>
                        </div>

                        @if(
                                        auth()->user()->hasPermission('projects.manage_members')
                                        && (int) $project->project_manager_id !== (int) $member->id
                                    )
                                    <form method="POST" action="{{ route('project.members.destroy', [
                                $project->id,
                                $member->id
                            ]) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="table-btn delete">
                                            Remove
                                        </button>
                                    </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Add Service --}}
        <!-- @if(
                                    auth()->user()->hasPermission('project_services.create')
                                    && !$project->isClosed()
                                )
                                <section class="project-section">
                                    <div class="project-section-header">
                                        <h2>Add Project Service</h2>
                                    </div>

                                    <form method="POST" action="{{ route('project.services.store', $project->id) }}" class="project-service-form">
                                        @csrf

                                        <input name="name" placeholder="Service name" required>

                                        <select name="assigned_to">
                                            <option value="">Unassigned</option>

                                            @foreach($project->members as $member)
                                                <option value="{{ $member->id }}">
                                                    {{ $member->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="priority">
                                            @foreach($projectPriorities as $key => $label)
                                                <option value="{{ $key }}">
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="status">
                                            @foreach($serviceStatuses as $key => $label)
                                                <option value="{{ $key }}">
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <input type="date" name="start_date">
                                        <input type="date" name="due_date">

                                        <textarea name="description" placeholder="Service description"></textarea>

                                        <button type="submit" class="primary-btn">
                                            Add Service
                                        </button>
                                    </form>
                                </section>
                            @endif -->

        {{-- Add Project Service --}}
        @if(
                auth()->user()->hasPermission(
                    'project_services.create'
                )
                && !$project->isClosed()
            )
            <section class="project-section
                                                                                    project-service-create-section">

                <div class="project-section-header">

                    <div>
                        <h2>Add Project Service</h2>

                        <p>
                            Create a service, assign a team member
                            and define its schedule.
                        </p>
                    </div>

                </div>

                <form method="POST" action="{{ route(
                'project.services.store',
                $project->id
            ) }}" class="project-service-create-form">
                    @csrf

                    {{-- Service Name --}}
                    <div class="project-service-field full-width">

                        <label for="service_name">
                            Service Name
                            <span>*</span>
                        </label>

                        <input type="text" name="name" id="service_name" value="{{ old('name') }}"
                            placeholder="Example: Website Development" maxlength="255" required>

                        @error('name')
                            <small class="field-error">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Assigned Member --}}
                    <div class="project-service-field">

                        <label for="service_assigned_to">
                            Assigned To
                        </label>

                        <select name="assigned_to" id="service_assigned_to">
                            <option value="">
                                Unassigned
                            </option>

                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}" @selected(
                                    (string) old(
                                        'assigned_to'
                                    ) === (string) $member->id
                                )>
                                    {{ $member->name }}

                                    @if(
                                            $member->pivot?->member_role
                                        )
                                        —
                                        {{ $member->pivot->member_role }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        <small class="field-help">
                            Service can be assigned to a project
                            member or Project Manager.
                        </small>

                        @error('assigned_to')
                            <small class="field-error">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Priority --}}
                    <div class="project-service-field">

                        <label for="service_priority">
                            Priority
                            <span>*</span>
                        </label>

                        <select name="priority" id="service_priority" required>
                            @foreach(
                                    $projectPriorities
                                    as $key => $label
                                )
                                <option value="{{ $key }}" @selected(
                                    old(
                                        'priority',
                                        'medium'
                                    ) === $key
                                )>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('priority')
                            <small class="field-error">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Status --}}
                    <div class="project-service-field">

                        <label for="service_status">
                            Status
                            <span>*</span>
                        </label>

                        <select name="status" id="service_status" required>
                            @foreach(
                                    $serviceStatuses
                                    as $key => $label
                                )
                                <option value="{{ $key }}" @selected(
                                    old(
                                        'status',
                                        'pending'
                                    ) === $key
                                )>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('status')
                            <small class="field-error">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Start Date --}}
                    <div class="project-service-field">

                        <label for="service_start_date">
                            Start Date
                        </label>

                        <input type="date" name="start_date" id="service_start_date" value="{{ old('start_date') }}"
                            @if($project->start_date) min="{{ $project->start_date->format(
                                'Y-m-d'
                            ) }}" @endif @if($project->due_date) max="{{ $project->due_date->format(
                    'Y-m-d'
                ) }}" @endif>

                        @error('start_date')
                            <small class="field-error">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Due Date --}}
                    <div class="project-service-field">

                        <label for="service_due_date">
                            Due Date
                        </label>

                        <input type="date" name="due_date" id="service_due_date" value="{{ old('due_date') }}"
                            @if($project->start_date) min="{{ $project->start_date->format(
                                'Y-m-d'
                            ) }}" @endif @if($project->due_date) max="{{ $project->due_date->format(
                    'Y-m-d'
                ) }}" @endif>

                        @error('due_date')
                            <small class="field-error">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Description --}}
                    <div class="project-service-field full-width">

                        <label for="service_description">
                            Service Description
                        </label>

                        <textarea name="description" id="service_description" rows="5" maxlength="5000"
                            placeholder="Describe service requirements, scope and expected result">{{ old('description') }}</textarea>

                        @error('description')
                            <small class="field-error">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Submit --}}
                    <div class="project-service-actions full-width">

                        <button type="submit" class="primary-btn">
                            Add Service
                        </button>

                    </div>

                </form>

            </section>
        @endif


        {{-- Services and Tasks --}}
        <section class="project-section">
            <div class="project-section-header">
                <h2>Services & Tasks</h2>
            </div>

            <div class="project-service-list">

                @forelse($project->services as $service)
                    <article class="project-service-card">

                        <div class="service-card-header">
                            <div>
                                <h3>{{ $service->name }}</h3>
                                <p>{{ $service->description }}</p>
                            </div>

                            <div class="service-card-actions">
                                <span class="project-badge status-{{ $service->status }}">
                                    {{ $serviceStatuses[$service->status] }}
                                </span>

                                <span>{{ $service->progressPercentage() }}%</span>

                                @if(auth()->user()->hasPermission('tasks.create'))
                                    <a href="{{ route('task.create', $service->id) }}" class="primary-btn">
                                        + Add Task
                                    </a>
                                @endif

                                @if(auth()->user()->hasPermission('project_services.edit'))
                                                    <a href="{{ route('project.services.edit', [
                                        $project->id,
                                        $service->id
                                    ]) }}" class="secondary-btn">
                                                        Edit
                                                    </a>
                                @endif

                                @if(
                                        auth()->user()->hasPermission(
                                            'project_services.delete'
                                        )
                                        && !$project->isClosed()
                                    )
                                    @if($service->tasks->isEmpty())

                                                    <form method="POST" action="{{ route(
                                            'project.services.destroy',
                                            [
                                                $project->id,
                                                $service->id
                                            ]
                                        ) }}" class="project-service-delete-form"
                                                        onsubmit="return confirm(
                                                                                                                                                                                                                                                                                                        'Are you sure you want to delete this service?'
                                                                                                                                                                                                                                                                                                    );">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="table-btn delete">
                                                            Delete
                                                        </button>
                                                    </form>

                                    @else

                                        <span class="project-service-delete-disabled" title="Delete service tasks first">
                                            Delete Tasks First
                                        </span>

                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="table-wrapper">
                            <table class="admin-table project-task-table">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Assigned To</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Dependency</th>
                                        <th>Due</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($service->tasks as $task)
                                                        @php
                                                            /*
                                                             * Sirf actual system Completed status wali
                                                             * prerequisite Task complete mani jayegi.
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
                                                                <strong>{{ $task->title }}</strong>
                                                                <small>{{ $task->progress_percent }}%</small>
                                                            </td>

                                                            <td>
                                                                {{ $task->assignedUser?->name ?? 'Unassigned' }}
                                                            </td>

                                                            <td>
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
                                                            </td>

                                                            <td>
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
                                                            </td>

                                                            <td>
                                                                @if($pendingDependencyCount > 0)

                                                                                        <span class="project-task-dependency blocked">
                                                                                            Waiting for
                                                                                            {{ $pendingDependencyCount }}
                                                                                            {{ $pendingDependencyCount === 1
                                                                    ? 'task'
                                                                    : 'tasks' }}
                                                                                        </span>

                                                                @elseif($totalDependencyCount > 0)

                                                                    <span class="project-task-dependency ready">
                                                                        Ready
                                                                    </span>

                                                                @else

                                                                    <span class="project-task-dependency independent">
                                                                        Independent
                                                                    </span>

                                                                @endif
                                                            </td>

                                                            <td>
                                                                {{ $task->due_at
                                        ? $task->due_at->format('d M Y h:i A')
                                        : '-' }}
                                                            </td>

                                                            <!-- <td>
                                                                                                                                                                                                                                                                                                <a href="{{ route('task.show', $task->id) }}" class="table-btn view">
                                                                                                                                                                                                                                                                                                    View
                                                                                                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                                                                                            </td> -->

                                                            <td>
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

                                                                    @if(
                                                                                                        auth()->user()->hasPermission('tasks.delete')
                                                                                                    )
                                                                                                    <form method="POST" action="{{ route(
                                                                            'task.destroy',
                                                                            $task->id
                                                                        ) }}" class="task-inline-delete-form"
                                                                                                        onsubmit="return confirm(
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        'Are you sure you want to delete this task?'
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
                                            <td colspan="7" class="empty-table">
                                                No tasks in this service.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </article>
                @empty
                    <div class="empty-table">
                        No services added yet.
                    </div>
                @endforelse

            </div>
        </section>

        {{-- Activity Timeline --}}
        <section class="project-section">
            <div class="project-section-header">
                <h2>Project Activity</h2>
            </div>

            <div class="project-activity-list">
                @forelse($activities as $activity)
                    <div class="project-activity-item">
                        <strong>
                            {{ $activity->user?->name ?? 'System' }}
                        </strong>

                        <p>{{ $activity->description }}</p>

                        <small>
                            {{ $activity->created_at->format('d M Y h:i A') }}
                        </small>
                    </div>
                @empty
                    <div class="empty-table">
                        No activity recorded.
                    </div>
                @endforelse
            </div>
        </section>

    </div>

@endsection