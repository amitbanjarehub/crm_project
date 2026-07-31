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

<div class="report-page executive-report-page">

    <div class="report-page-header">

        <div>
            <h1>Executive Dashboard Report</h1>

            <p>
                High-level project, task and workforce analytics.
            </p>
        </div>

        <span class="report-access-badge">
            {{ auth()->user()->hasPermission(
                'reports.executive.view_all'
            ) || auth()->user()->isSuperAdmin()
                ? 'All Business Data'
                : 'Accessible Projects Only' }}
        </span>

    </div>

    {{-- Date Filter --}}
    {{-- Date Filter --}}
<form
    method="GET"
    action="{{ route('report.executive') }}"
    class="report-filter-form executive-date-filter"
>
    <div class="executive-date-field">
        <label for="executive_date_from">
            From Date
        </label>

        <input
            type="date"
            id="executive_date_from"
            name="date_from"
            value="{{ $dateFrom->format('Y-m-d') }}"
        >
    </div>

    <div class="executive-date-field">
        <label for="executive_date_to">
            To Date
        </label>

        <input
            type="date"
            id="executive_date_to"
            name="date_to"
            value="{{ $dateTo->format('Y-m-d') }}"
        >
    </div>

    <div class="executive-filter-actions">
        <button
            type="submit"
            class="executive-apply-btn"
        >
            <span>✓</span>
            Apply Report
        </button>

        <a
            href="{{ route('report.executive') }}"
            class="executive-reset-btn"
        >
            Reset
        </a>
    </div>
</form>

    {{-- Main Cards --}}
    <div class="report-card-grid">

        <div class="report-stat-card">
            <span>Total Projects</span>
            <strong>{{ $totalProjects }}</strong>
            <small>{{ $activeProjects }} active</small>
        </div>

        <div class="report-stat-card success">
            <span>Completed Projects</span>
            <strong>{{ $completedProjects }}</strong>
            <small>Successfully delivered</small>
        </div>

        <div class="report-stat-card danger">
            <span>Delayed Projects</span>
            <strong>{{ $delayedProjects }}</strong>
            <small>Past due date</small>
        </div>

        <div class="report-stat-card warning">
            <span>On Hold</span>
            <strong>{{ $onHoldProjects }}</strong>
            <small>Currently paused</small>
        </div>

        <div class="report-stat-card">
            <span>Total Tasks</span>
            <strong>{{ $totalTasks }}</strong>
            <small>{{ $completedTasks }} completed</small>
        </div>

        <div class="report-stat-card success">
            <span>Task Completion</span>
            <strong>{{ $completionRate }}%</strong>
            <small>{{ $overdueTasks }} overdue</small>
        </div>

        <div class="report-stat-card">
            <span>Active Timers</span>
            <strong>{{ $activeTimers }}</strong>
            <small>Running or paused</small>
        </div>

        <div class="report-stat-card">
            <span>Total Project Budget</span>
            <strong>
                {{ number_format(
                    $totalBudget,
                    2
                ) }}
            </strong>
            <small>Accessible project budget</small>
        </div>

    </div>

    {{-- Effort Summary --}}
    <section class="report-panel">

        <div class="report-panel-header">
            <div>
                <h2>Effort Analytics</h2>

                <p>
                    Estimated project effort compared with
                    actual tracked time.
                </p>
            </div>
        </div>

        <div class="report-effort-grid">

            <div>
                <span>Estimated Time</span>

                <strong>
                    {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                        $estimatedSeconds
                    ) }}
                </strong>
            </div>

            <div>
                <span>Actual Tracked Time</span>

                <strong>
                    {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                        $trackedSeconds
                    ) }}
                </strong>
            </div>

            <div>
                <span>Time Variance</span>

                <strong class="{{ $timeVarianceSeconds > 0
                    ? 'report-negative'
                    : 'report-positive' }}">

                    {{ $timeVarianceSeconds > 0
                        ? '+'
                        : '-' }}

                    {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                        abs($timeVarianceSeconds)
                    ) }}
                </strong>
            </div>

            <div>
                <span>Task Completion</span>

                <div
                    class="report-donut"
                    style="--report-percent: {{ $completionRate }}"
                >
                    <div>
                        {{ $completionRate }}%
                    </div>
                </div>
            </div>

        </div>

    </section>

    {{-- Status Analytics --}}
    <div class="report-two-column">

        <section class="report-panel">

            <div class="report-panel-header">
                <div>
                    <h2>Project Status</h2>
                    <p>Current project distribution</p>
                </div>
            </div>

            <div class="report-bar-list">

                @foreach(
                    $projectStatuses
                    as $statusKey => $statusLabel
                )
                    @php
                        $statusCount =
                            $projectStatusCounts[
                                $statusKey
                            ] ?? 0;

                        $statusWidth = (
                            $statusCount
                            / $projectStatusMax
                        ) * 100;
                    @endphp

                    <div class="report-bar-item">

                        <div>
                            <span>{{ $statusLabel }}</span>
                            <strong>{{ $statusCount }}</strong>
                        </div>

                        <div class="report-bar-track">
                            <span
                                style="width: {{ $statusWidth }}%"
                            ></span>
                        </div>

                    </div>
                @endforeach

            </div>

        </section>

        <section class="report-panel">

            <div class="report-panel-header">
                <div>
                    <h2>Task Status</h2>
                    <p>Current task distribution</p>
                </div>
            </div>

            <div class="report-bar-list">

                @foreach(
                    $taskStatuses
                    as $statusKey => $statusLabel
                )
                    @php
                        $statusCount =
                            $taskStatusCounts[
                                $statusKey
                            ] ?? 0;

                        $statusWidth = (
                            $statusCount
                            / $taskStatusMax
                        ) * 100;
                    @endphp

                    <div class="report-bar-item">

                        <div>
                            <span>{{ $statusLabel }}</span>
                            <strong>{{ $statusCount }}</strong>
                        </div>

                        <div class="report-bar-track">
                            <span
                                style="width: {{ $statusWidth }}%"
                            ></span>
                        </div>

                    </div>
                @endforeach

            </div>

        </section>

    </div>

    {{-- Monthly Trend --}}
    <section class="report-panel">

        <div class="report-panel-header">
            <div>
                <h2>Monthly Task Completion Trend</h2>

                <p>
                    Completed tasks during the selected period.
                </p>
            </div>
        </div>

        <div class="report-trend-list">

            @foreach(
                $monthlyCompletionTrend
                as $month
            )
                <div class="report-trend-item">

                    <span>
                        {{ $month['label'] }}
                    </span>

                    <div>
                        <i
                            style="width: {{ $month['width'] }}%"
                        ></i>
                    </div>

                    <strong>
                        {{ $month['count'] }}
                    </strong>

                </div>
            @endforeach

        </div>

    </section>

    {{-- Delayed and Deadlines --}}
    <div class="report-two-column">

        <section class="report-panel">

            <div class="report-panel-header">
                <div>
                    <h2>Delayed Projects</h2>
                    <p>Projects requiring attention</p>
                </div>
            </div>

            <div class="report-list">

                @forelse(
                    $delayedProjectList
                    as $project
                )
                    @php
                        $progress =
                            $project->total_tasks > 0
                            ? (int) round(
                                (
                                    $project
                                        ->completed_tasks
                                    / $project
                                        ->total_tasks
                                ) * 100
                            )
                            : 0;
                    @endphp

                    <a
                        href="{{ route(
                            'report.projects.show',
                            $project->id
                        ) }}"
                        class="report-list-item"
                    >
                        <div>
                            <strong>
                                {{ $project->project_code }}
                                — {{ $project->name }}
                            </strong>

                            <span>
                                Manager:
                                {{ $project->manager?->name
                                    ?? 'Unassigned' }}
                            </span>
                        </div>

                        <div>
                            <strong class="report-negative">
                                {{ $project->due_date
                                    ?->format('d M Y') }}
                            </strong>

                            <span>{{ $progress }}%</span>
                        </div>
                    </a>

                @empty

                    <div class="report-empty">
                        No delayed projects.
                    </div>

                @endforelse

            </div>

        </section>

        <section class="report-panel">

            <div class="report-panel-header">
                <div>
                    <h2>Upcoming Deadlines</h2>
                    <p>Tasks due in the next seven days</p>
                </div>
            </div>

            <div class="report-list">

                @forelse(
                    $upcomingTasks
                    as $task
                )
                    <a
                        href="{{ route(
                            'task.show',
                            $task->id
                        ) }}"
                        class="report-list-item"
                    >
                        <div>
                            <strong>
                                {{ $task->title }}
                            </strong>

                            <span>
                                {{ $task->project?->project_code }}
                                ·
                                {{ $task->assignedUser?->name
                                    ?? 'Unassigned' }}
                            </span>
                        </div>

                        <div>
                            <strong>
                                {{ $task->due_at
                                    ?->format('d M') }}
                            </strong>

                            <span>
                                {{ $task->due_at
                                    ?->format('h:i A') }}
                            </span>
                        </div>
                    </a>

                @empty

                    <div class="report-empty">
                        No upcoming deadlines.
                    </div>

                @endforelse

            </div>

        </section>

    </div>

    {{-- Employee Performance --}}
    {{-- Employee Performance --}}
<section class="report-panel employee-performance-panel">

    <div class="report-panel-header employee-performance-header">

        <div class="employee-performance-heading">

            <div class="employee-performance-icon">
                👥
            </div>

            <div>
                <h2>Employee Performance</h2>

                <p>
                    Tracked time and completed tasks for the selected period.
                </p>
            </div>

        </div>

        <span class="employee-performance-count">
            {{ $employeePerformance->count() }}
            {{ $employeePerformance->count() === 1
                ? 'Employee'
                : 'Employees' }}
        </span>

    </div>

    <div class="employee-performance-table-wrapper">

        <table class="employee-performance-table">

            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Completed Tasks</th>
                    <th>Tracked Time</th>
                </tr>
            </thead>

            <tbody>

                @forelse(
                    $employeePerformance
                    as $employee
                )

                    <tr>

                        {{-- Employee --}}
                        <td>
                            <div class="employee-profile-cell">

                                <div class="employee-avatar">
                                    {{ mb_strtoupper(
                                        mb_substr(
                                            $employee['name'],
                                            0,
                                            1
                                        )
                                    ) }}
                                </div>

                                <div class="employee-profile-info">
                                    <strong>
                                        {{ $employee['name'] }}
                                    </strong>

                                    <small>
                                        Employee ID:
                                        {{ $employee['user_id'] }}
                                    </small>
                                </div>

                            </div>
                        </td>

                        {{-- Role --}}
                        <td>
                            <span class="employee-role-badge">
                                {{ $employee['role'] }}
                            </span>
                        </td>

                        {{-- Completed Tasks --}}
                        <td>
                            <div class="employee-task-count">

                                <strong>
                                    {{ $employee['completed_tasks'] }}
                                </strong>

                                <span>
                                    Completed
                                </span>

                            </div>
                        </td>

                        {{-- Tracked Time --}}
                        <td>
                            <div class="employee-tracked-time">

                                <span class="employee-time-icon">
                                    ⏱
                                </span>

                                <strong>
                                    {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                        $employee['tracked_seconds']
                                    ) }}
                                </strong>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4">

                            <div class="employee-performance-empty">

                                <span>👤</span>

                                <strong>
                                    No Employee Activity
                                </strong>

                                <p>
                                    No employee activity was found for the selected period.
                                </p>

                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</section>

    {{-- Recent Activities --}}
    <section class="report-panel">

        <div class="report-panel-header">
            <div>
                <h2>Recent Project Activities</h2>
                <p>Latest project and task actions</p>
            </div>
        </div>

        <div class="report-activity-list">

            @forelse(
                $recentActivities
                as $activity
            )
                <div class="report-activity-item">

                    <span>●</span>

                    <div>
                        <strong>
                            {{ $activity->project
                                ?->project_code }}
                            —
                            {{ $activity->project
                                ?->name }}
                        </strong>

                        <p>
                            {{ $activity->description }}
                        </p>

                        <small>
                            {{ $activity->user?->name
                                ?? 'System' }}
                            ·
                            {{ $activity->created_at
                                ->format(
                                    'd M Y h:i A'
                                ) }}
                        </small>
                    </div>

                </div>

            @empty

                <div class="report-empty">
                    No activity found.
                </div>

            @endforelse

        </div>

    </section>

</div>

@endsection