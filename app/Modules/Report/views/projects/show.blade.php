@extends('admin::layouts.app')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset(
            'css/modules/report.css'
        ) }}?v={{ filemtime(
            public_path(
                'css/modules/report.css'
            )
        ) }}"
    >
@endpush

@section('content')

<div class="report-page project-report-detail">

    <div class="report-page-header">

        <div>
            <h1>{{ $project->name }}</h1>

            <p>
                {{ $project->project_code }}
                · Project Performance Report
            </p>
        </div>

        <div class="report-header-actions">

            <a
                href="{{ route(
                    'report.projects.index'
                ) }}"
                class="secondary-btn"
            >
                Back to Reports
            </a>

            @if(
                auth()->user()->hasPermission(
                    'projects.view'
                )
            )
                <a
                    href="{{ route(
                        'project.show',
                        $project->id
                    ) }}"
                    class="primary-btn"
                >
                    Open Project
                </a>
            @endif

        </div>

    </div>

    <form
        method="GET"
        action="{{ route(
            'report.projects.show',
            $project->id
        ) }}"
        class="report-filter-form"
    >

        <div>
            <label>Tracked Time From</label>

            <input
                type="date"
                name="date_from"
                value="{{ $dateFrom->format(
                    'Y-m-d'
                ) }}"
            >
        </div>

        <div>
            <label>Tracked Time To</label>

            <input
                type="date"
                name="date_to"
                value="{{ $dateTo->format(
                    'Y-m-d'
                ) }}"
            >
        </div>

        <button
            type="submit"
            class="primary-btn"
        >
            Apply
        </button>

        <a
            href="{{ route(
                'report.projects.show',
                $project->id
            ) }}"
            class="secondary-btn"
        >
            Reset
        </a>

    </form>

    {{-- Project Information --}}
    <div class="report-project-info-grid">

        <div>
            <span>Client</span>
            <strong>
                {{ $project->client?->name
                    ?? '-' }}
            </strong>
        </div>

        <div>
            <span>Project Manager</span>
            <strong>
                {{ $project->manager?->name
                    ?? 'Unassigned' }}
            </strong>
        </div>

        <div>
            <span>Status</span>
            <strong>
                {{ \App\Modules\Project\Models\Project::statuses()[
                    $project->status
                ] }}
            </strong>
        </div>

        <div>
            <span>Priority</span>
            <strong>
                {{ \App\Modules\Project\Models\Project::priorities()[
                    $project->priority
                ] }}
            </strong>
        </div>

        <div>
            <span>Start Date</span>
            <strong>
                {{ $project->start_date
                    ?->format('d M Y')
                    ?? '-' }}
            </strong>
        </div>

        <div>
            <span>Due Date</span>
            <strong>
                {{ $project->due_date
                    ?->format('d M Y')
                    ?? '-' }}
            </strong>
        </div>

        <div>
            <span>Budget</span>
            <strong>
                {{ $project->budget !== null
                    ? number_format(
                        (float) $project->budget,
                        2
                    )
                    : '-' }}
            </strong>
        </div>

        <div>
            <span>Active Timers</span>
            <strong>
                {{ $summary['active_timers'] }}
            </strong>
        </div>

    </div>

    {{-- Project Summary --}}
    <div class="report-card-grid">

        <div class="report-stat-card">
            <span>Total Tasks</span>
            <strong>
                {{ $summary['total_tasks'] }}
            </strong>
        </div>

        <div class="report-stat-card success">
            <span>Completed</span>
            <strong>
                {{ $summary['completed_tasks'] }}
            </strong>
        </div>

        <div class="report-stat-card danger">
            <span>Overdue</span>
            <strong>
                {{ $summary['overdue_tasks'] }}
            </strong>
        </div>

        <div class="report-stat-card warning">
            <span>Blocked</span>
            <strong>
                {{ $summary['blocked_tasks'] }}
            </strong>
        </div>

        <div class="report-stat-card">
            <span>In Review</span>
            <strong>
                {{ $summary['in_review_tasks'] }}
            </strong>
        </div>

        <div class="report-stat-card success">
            <span>Progress</span>
            <strong>
                {{ $summary['progress_percent'] }}%
            </strong>
        </div>

        <div class="report-stat-card">
            <span>Estimated Time</span>
            <strong>
                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                    $summary[
                        'estimated_seconds'
                    ]
                ) }}
            </strong>
        </div>

        <div class="report-stat-card">
            <span>Tracked Time</span>
            <strong>
                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                    $summary[
                        'tracked_seconds'
                    ]
                ) }}
            </strong>
        </div>

    </div>

    <div class="report-two-column">

        {{-- Task Status --}}
        <section class="report-panel">

            <div class="report-panel-header">
                <div>
                    <h2>Task Status Distribution</h2>
                    <p>Current project task health</p>
                </div>
            </div>

            <div class="report-bar-list">

                @foreach(
                    $taskStatuses
                    as $statusKey => $statusLabel
                )
                    @php
                        $count =
                            $taskStatusCounts[
                                $statusKey
                            ] ?? 0;

                        $width = (
                            $count
                            / $taskStatusMax
                        ) * 100;
                    @endphp

                    <div class="report-bar-item">

                        <div>
                            <span>{{ $statusLabel }}</span>
                            <strong>{{ $count }}</strong>
                        </div>

                        <div class="report-bar-track">
                            <span
                                style="width: {{ $width }}%"
                            ></span>
                        </div>

                    </div>
                @endforeach

            </div>

        </section>

        {{-- Effort --}}
        <section class="report-panel">

            <div class="report-panel-header">
                <div>
                    <h2>Time Variance</h2>
                    <p>Estimated versus actual effort</p>
                </div>
            </div>

            <div class="report-effort-stack">

                <div>
                    <span>Estimated</span>

                    <strong>
                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                            $summary[
                                'estimated_seconds'
                            ]
                        ) }}
                    </strong>
                </div>

                <div>
                    <span>Actual</span>

                    <strong>
                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                            $summary[
                                'tracked_seconds'
                            ]
                        ) }}
                    </strong>
                </div>

                <div>
                    <span>Variance</span>

                    <strong class="{{ $summary['variance_seconds'] > 0
                        ? 'report-negative'
                        : 'report-positive' }}">

                        {{ $summary['variance_seconds'] > 0
                            ? '+'
                            : '-' }}

                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                            abs(
                                $summary[
                                    'variance_seconds'
                                ]
                            )
                        ) }}
                    </strong>
                </div>

            </div>

        </section>

    </div>

    {{-- Service Report --}}
    {{-- Service Report --}}
<section class="report-panel project-detail-panel service-report-panel">

    <div class="project-detail-panel-header">

        <div class="project-detail-heading">

            <div class="project-detail-heading-icon service-icon">
                ⚙
            </div>

            <div>
                <h2>Service-wise Report</h2>

                <p>
                    Progress and time comparison by service.
                </p>
            </div>

        </div>

        <span class="project-detail-record-count">
            {{ $serviceRows->count() }}
            {{ $serviceRows->count() === 1
                ? 'Service'
                : 'Services' }}
        </span>

    </div>

    <div class="project-detail-table-wrapper">

        <table class="project-detail-table service-report-table">

            <thead>
                <tr>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Tasks</th>
                    <th>Completed</th>
                    <th>Progress</th>
                    <th>Estimated</th>
                    <th>Tracked</th>
                    <th>Variance</th>
                </tr>
            </thead>

            <tbody>

                @forelse($serviceRows as $row)

                    @php
                        $serviceStatus = $row['service']->status;

                        $serviceStatusLabel = ucwords(
                            str_replace(
                                '_',
                                ' ',
                                $serviceStatus
                            )
                        );

                        $isOverEstimate =
                            $row['variance_seconds'] > 0;
                    @endphp

                    <tr>

                        {{-- Service --}}
                        <td>
                            <div class="project-detail-name-cell">

                                <div class="project-detail-avatar service-avatar">
                                    {{ strtoupper(
                                        substr(
                                            $row['service']->name,
                                            0,
                                            1
                                        )
                                    ) }}
                                </div>

                                <div>
                                    <strong>
                                        {{ $row['service']->name }}
                                    </strong>

                                    <small>
                                        Service #{{ $row['service']->id }}
                                    </small>
                                </div>

                            </div>
                        </td>

                        {{-- Status --}}
                        <td>
                            <span
                                class="project-detail-status
                                    service-status-{{ $serviceStatus }}"
                            >
                                {{ $serviceStatusLabel }}
                            </span>
                        </td>

                        {{-- Total Tasks --}}
                        <td>
                            <span class="project-detail-number-badge neutral">
                                {{ $row['total_tasks'] }}
                            </span>
                        </td>

                        {{-- Completed Tasks --}}
                        <td>
                            <span class="project-detail-number-badge success">
                                {{ $row['completed_tasks'] }}
                            </span>
                        </td>

                        {{-- Progress --}}
                        <td>
                            <div class="project-detail-progress">

                                <div class="project-detail-progress-track">
                                    <span
                                        style="width: {{ min(
                                            100,
                                            max(
                                                0,
                                                $row['progress_percent']
                                            )
                                        ) }}%"
                                    ></span>
                                </div>

                                <strong>
                                    {{ $row['progress_percent'] }}%
                                </strong>

                            </div>
                        </td>

                        {{-- Estimated --}}
                        <td>
                            <span class="project-detail-time">
                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    $row['estimated_seconds']
                                ) }}
                            </span>
                        </td>

                        {{-- Tracked --}}
                        <td>
                            <span class="project-detail-time tracked">
                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    $row['tracked_seconds']
                                ) }}
                            </span>
                        </td>

                        {{-- Variance --}}
                        <td>
                            <span
                                class="project-detail-variance
                                    {{ $isOverEstimate
                                        ? 'over'
                                        : 'under' }}"
                            >
                                {{ $isOverEstimate ? '+' : '-' }}

                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    abs(
                                        $row['variance_seconds']
                                    )
                                ) }}
                            </span>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8">

                            <div class="project-detail-empty-state">

                                <span>⚙</span>

                                <strong>
                                    No Services Found
                                </strong>

                                <p>
                                    No services are available for this project.
                                </p>

                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</section>

    {{-- Team Performance --}}
    {{-- Team Performance --}}
<section class="report-panel project-detail-panel team-performance-panel">

    <div class="project-detail-panel-header">

        <div class="project-detail-heading">

            <div class="project-detail-heading-icon team-icon">
                👥
            </div>

            <div>
                <h2>Team Performance</h2>

                <p>
                    Employee assignments, completion and effort.
                </p>
            </div>

        </div>

        <span class="project-detail-record-count">
            {{ $teamRows->count() }}
            {{ $teamRows->count() === 1
                ? 'Member'
                : 'Members' }}
        </span>

    </div>

    <div class="project-detail-table-wrapper">

        <table class="project-detail-table team-performance-table">

            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Project Role</th>
                    <th>System Role</th>
                    <th>Assigned</th>
                    <th>Completed</th>
                    <th>Overdue</th>
                    <th>Estimated</th>
                    <th>Tracked</th>
                    <th>Variance</th>
                </tr>
            </thead>

            <tbody>

                @forelse($teamRows as $row)

                    @php
                        $isOverEstimate =
                            $row['variance_seconds'] > 0;
                    @endphp

                    <tr>

                        {{-- Employee --}}
                        <td>
                            <div class="project-detail-name-cell">

                                <div class="project-detail-avatar team-avatar">
                                    {{ strtoupper(
                                        substr(
                                            $row['user']->name,
                                            0,
                                            1
                                        )
                                    ) }}
                                </div>

                                <div>
                                    <strong>
                                        {{ $row['user']->name }}
                                    </strong>

                                    <small>
                                        {{ $row['user']->email }}
                                    </small>
                                </div>

                            </div>
                        </td>

                        {{-- Project Role --}}
                        <td>
                            <span class="project-role-badge">
                                {{ $row['project_role'] }}
                            </span>
                        </td>

                        {{-- System Role --}}
                        <td>
                            <span class="system-role-badge">
                                {{ $row['user']->role?->name
                                    ?? 'No Role' }}
                            </span>
                        </td>

                        {{-- Assigned --}}
                        <td>
                            <span class="project-detail-number-badge neutral">
                                {{ $row['assigned_tasks'] }}
                            </span>
                        </td>

                        {{-- Completed --}}
                        <td>
                            <span class="project-detail-number-badge success">
                                {{ $row['completed_tasks'] }}
                            </span>
                        </td>

                        {{-- Overdue --}}
                        <td>
                            <span
                                class="project-detail-number-badge
                                    {{ $row['overdue_tasks'] > 0
                                        ? 'danger'
                                        : 'success' }}"
                            >
                                {{ $row['overdue_tasks'] }}
                            </span>
                        </td>

                        {{-- Estimated --}}
                        <td>
                            <span class="project-detail-time">
                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    $row['estimated_seconds']
                                ) }}
                            </span>
                        </td>

                        {{-- Tracked --}}
                        <td>
                            <span class="project-detail-time tracked">
                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    $row['tracked_seconds']
                                ) }}
                            </span>
                        </td>

                        {{-- Variance --}}
                        <td>
                            <span
                                class="project-detail-variance
                                    {{ $isOverEstimate
                                        ? 'over'
                                        : 'under' }}"
                            >
                                {{ $isOverEstimate ? '+' : '-' }}

                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    abs(
                                        $row['variance_seconds']
                                    )
                                ) }}
                            </span>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="9">

                            <div class="project-detail-empty-state">

                                <span>👥</span>

                                <strong>
                                    No Team Data Available
                                </strong>

                                <p>
                                    No team members are available for this project.
                                </p>

                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</section>

    {{-- Task Report --}}
    {{-- Task Report --}}
<section class="report-panel project-detail-panel task-analytics-panel">

    <div class="project-detail-panel-header">

        <div class="project-detail-heading">

            <div class="project-detail-heading-icon task-icon">
                ✓
            </div>

            <div>
                <h2>Task Analytics</h2>

                <p>
                    Detailed project task performance.
                </p>
            </div>

        </div>

        <span class="project-detail-record-count">
            {{ $taskRows->count() }}
            {{ $taskRows->count() === 1
                ? 'Task'
                : 'Tasks' }}
        </span>

    </div>

    <div class="project-detail-table-wrapper">

        <table class="project-detail-table task-analytics-table">

            <thead>
                <tr>
                    <th>Task</th>
                    <th>Service</th>
                    <th>Employee</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Due Date</th>
                    <th>Estimated</th>
                    <th>Tracked</th>
                    <th>Variance</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($taskRows as $row)

                    @php
                        $task = $row['task'];

                        $isOverEstimate =
                            $row['variance_seconds'] > 0;
                    @endphp

                    <tr>

                        {{-- Task --}}
                        <td>
                            <div class="task-report-name">

                                <strong>
                                    {{ $task->title }}
                                </strong>

                                <small>
                                    Task #{{ $task->id }}
                                </small>

                            </div>
                        </td>

                        {{-- Service --}}
                        <td>
                            <div class="task-report-secondary-cell">

                                <span>
                                    {{ $task->projectService?->name
                                        ?? 'No Service' }}
                                </span>

                            </div>
                        </td>

                        {{-- Employee --}}
                        <td>
                            <div class="task-report-employee">

                                <span class="task-report-employee-avatar">
                                    {{ $task->assignedUser
                                        ? strtoupper(
                                            substr(
                                                $task->assignedUser->name,
                                                0,
                                                1
                                            )
                                        )
                                        : '?' }}
                                </span>

                                <strong>
                                    {{ $task->assignedUser?->name
                                        ?? 'Unassigned' }}
                                </strong>

                            </div>
                        </td>

                        {{-- Status --}}
                        <td>
                            <span
                                class="project-detail-status
                                    task-status-{{ $task->status }}"
                            >
                                {{ $taskStatuses[$task->status]
                                    ?? ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $task->status
                                        )
                                    ) }}
                            </span>
                        </td>

                        {{-- Progress --}}
                        <td>
                            <div class="project-detail-progress task-progress">

                                <div class="project-detail-progress-track">
                                    <span
                                        style="width: {{ min(
                                            100,
                                            max(
                                                0,
                                                $task->progress_percent
                                            )
                                        ) }}%"
                                    ></span>
                                </div>

                                <strong>
                                    {{ $task->progress_percent }}%
                                </strong>

                            </div>
                        </td>

                        {{-- Due Date --}}
                        <td>
                            @if($task->due_at)

                                <div
                                    class="task-report-due-date
                                        {{ $row['is_overdue']
                                            ? 'overdue'
                                            : '' }}"
                                >
                                    <strong>
                                        {{ $task->due_at->format(
                                            'd M Y'
                                        ) }}
                                    </strong>

                                    <small>
                                        {{ $task->due_at->format(
                                            'h:i A'
                                        ) }}
                                    </small>

                                    @if($row['is_overdue'])
                                        <span>
                                            Overdue
                                        </span>
                                    @endif
                                </div>

                            @else

                                <span class="task-report-no-date">
                                    No due date
                                </span>

                            @endif
                        </td>

                        {{-- Estimated --}}
                        <td>
                            <span class="project-detail-time">
                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    $row['estimated_seconds']
                                ) }}
                            </span>
                        </td>

                        {{-- Tracked --}}
                        <td>
                            <span class="project-detail-time tracked">
                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    $row['tracked_seconds']
                                ) }}
                            </span>
                        </td>

                        {{-- Variance --}}
                        <td>
                            <span
                                class="project-detail-variance
                                    {{ $isOverEstimate
                                        ? 'over'
                                        : 'under' }}"
                            >
                                {{ $isOverEstimate ? '+' : '-' }}

                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    abs(
                                        $row['variance_seconds']
                                    )
                                ) }}
                            </span>
                        </td>

                        {{-- Action --}}
                        <td>
                            <a
                                href="{{ route(
                                    'task.show',
                                    $task->id
                                ) }}"
                                class="task-report-open-btn"
                            >
                                <span>View</span>
                                <span>→</span>
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10">

                            <div class="project-detail-empty-state">

                                <span>✓</span>

                                <strong>
                                    No Tasks Found
                                </strong>

                                <p>
                                    No tasks are available for this project.
                                </p>

                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</section>

    {{-- Activity --}}
    <section class="report-panel">

        <div class="report-panel-header">
            <div>
                <h2>Recent Project Activity</h2>
                <p>Latest project audit events</p>
            </div>
        </div>

        <div class="report-activity-list">

            @forelse($activities as $activity)

                <div class="report-activity-item">

                    <span>●</span>

                    <div>
                        <strong>
                            {{ $activity->user?->name
                                ?? 'System' }}
                        </strong>

                        <p>
                            {{ $activity->description }}
                        </p>

                        <small>
                            {{ $activity->created_at
                                ->format(
                                    'd M Y h:i A'
                                ) }}
                        </small>
                    </div>

                </div>

            @empty

                <div class="report-empty">
                    No project activity found.
                </div>

            @endforelse

        </div>

    </section>

</div>

@endsection