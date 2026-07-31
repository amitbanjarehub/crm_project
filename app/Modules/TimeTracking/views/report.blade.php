@extends('admin::layouts.app')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/modules/time-tracking.css') }}?v={{ filemtime(
            public_path('css/modules/time-tracking.css')
        ) }}"
    >
@endpush

@section('content')

<div class="time-report-page">

    <div class="content-card time-report-main-card">

        {{-- Header --}}
        <div class="time-report-header">

            <div class="time-report-heading">

                <span class="time-report-heading-icon">
                    ⏱
                </span>

                <div>
                    <h1>
                        Time Tracking Report
                    </h1>

                    <p>
                        Employee, role and project-wise work report.
                    </p>
                </div>

            </div>

            <a
                href="{{ route('timetracking.index') }}"
                class="time-report-back-btn"
            >
                <span>←</span>
                My Time
            </a>

        </div>

        {{-- Filters --}}
        <section class="time-report-filter-card">

            <div class="time-report-section-heading">

                <div>
                    <h2>Report Filters</h2>

                    <p>
                        Select date range, employee, role or project.
                    </p>
                </div>

                <span class="time-report-date-range">
                    {{ $dateFrom->format('d M Y') }}
                    —
                    {{ $dateTo->format('d M Y') }}
                </span>

            </div>

            <form
                method="GET"
                action="{{ route('timetracking.report') }}"
                class="time-report-filter-form"
            >

                {{-- From Date --}}
                <div class="time-report-filter-field">

                    <label for="time_date_from">
                        From Date
                    </label>

                    <input
                        type="date"
                        name="date_from"
                        id="time_date_from"
                        value="{{ request(
                            'date_from',
                            $dateFrom->format('Y-m-d')
                        ) }}"
                    >

                </div>

                {{-- To Date --}}
                <div class="time-report-filter-field">

                    <label for="time_date_to">
                        To Date
                    </label>

                    <input
                        type="date"
                        name="date_to"
                        id="time_date_to"
                        value="{{ request(
                            'date_to',
                            $dateTo->format('Y-m-d')
                        ) }}"
                    >

                </div>

                {{-- Employee --}}
                <div class="time-report-filter-field">

                    <label for="time_user_id">
                        Employee
                    </label>

                    <select
                        name="user_id"
                        id="time_user_id"
                    >
                        <option value="">
                            All Employees
                        </option>

                        @foreach($users as $user)

                            <option
                                value="{{ $user->id }}"
                                @selected(
                                    (string) request(
                                        'user_id',
                                        $filters['user_id']
                                            ?? ''
                                    )
                                    ===
                                    (string) $user->id
                                )
                            >
                                {{ $user->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- Role --}}
                <div class="time-report-filter-field">

                    <label for="time_role_id">
                        Role
                    </label>

                    <select
                        name="role_id"
                        id="time_role_id"
                    >
                        <option value="">
                            All Roles
                        </option>

                        @foreach($roles as $role)

                            <option
                                value="{{ $role->id }}"
                                @selected(
                                    (string) request(
                                        'role_id',
                                        $filters['role_id']
                                            ?? ''
                                    )
                                    ===
                                    (string) $role->id
                                )
                            >
                                {{ $role->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- Project --}}
                <div class="time-report-filter-field">

                    <label for="time_project_id">
                        Project
                    </label>

                    <select
                        name="project_id"
                        id="time_project_id"
                    >
                        <option value="">
                            All Projects
                        </option>

                        @foreach($projects as $project)

                            <option
                                value="{{ $project->id }}"
                                @selected(
                                    (string) request(
                                        'project_id',
                                        $filters['project_id']
                                            ?? ''
                                    )
                                    ===
                                    (string) $project->id
                                )
                            >
                                {{ $project->project_code }}
                                —
                                {{ $project->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- Buttons --}}
                <div class="time-report-filter-actions">

                    <button
                        type="submit"
                        class="time-report-apply-btn"
                    >
                        <span>🔍</span>
                        Apply Filters
                    </button>

                    <a
                        href="{{ route('timetracking.report') }}"
                        class="time-report-reset-btn"
                    >
                        Reset
                    </a>

                </div>

            </form>

        </section>

        {{-- Summary Cards --}}
        <section class="time-report-summary-grid">

            <article class="time-report-summary-card">

                <div class="time-report-summary-icon blue">
                    ⏱
                </div>

                <div>
                    <span>Total Time</span>

                    <strong>
                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                            $totalSeconds
                        ) }}
                    </strong>

                    <small>
                        Selected report duration
                    </small>
                </div>

            </article>

            <article class="time-report-summary-card">

                <div class="time-report-summary-icon purple">
                    ▦
                </div>

                <div>
                    <span>Sessions</span>

                    <strong>
                        {{ $entries->count() }}
                    </strong>

                    <small>
                        Work sessions recorded
                    </small>
                </div>

            </article>

            <article class="time-report-summary-card">

                <div class="time-report-summary-icon green">
                    👤
                </div>

                <div>
                    <span>Employees</span>

                    <strong>
                        {{ $userSummary->count() }}
                    </strong>

                    <small>
                        Employees who tracked time
                    </small>
                </div>

            </article>

            <article class="time-report-summary-card">

                <div class="time-report-summary-icon orange">
                    📁
                </div>

                <div>
                    <span>Projects</span>

                    <strong>
                        {{ $projectSummary->count() }}
                    </strong>

                    <small>
                        Projects with tracked work
                    </small>
                </div>

            </article>

        </section>

        {{-- Employee Summary --}}
        <section class="time-report-section">

            <div class="time-report-section-heading">

                <div>
                    <h2>Employee-wise Summary</h2>

                    <p>
                        Total working time recorded by each employee.
                    </p>
                </div>

                <span class="time-report-count">
                    {{ $userSummary->count() }}
                    Employees
                </span>

            </div>

            <div class="time-report-table-wrapper">

                <table class="time-report-table">

                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th>Sessions</th>
                            <th>Total Time</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($userSummary as $item)

                            <tr>

                                <td>
                                    <div class="time-report-user-cell">

                                        <span class="time-report-user-avatar">
                                            {{ strtoupper(
                                                substr(
                                                    $item['name'] ?? 'U',
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </span>

                                        <div>
                                            <strong>
                                                {{ $item['name'] }}
                                            </strong>

                                            <small>
                                                CRM Employee
                                            </small>
                                        </div>

                                    </div>
                                </td>

                                <td>
                                    <span class="time-report-role-badge">
                                        {{ $item['role'] }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="time-report-number">
                                        {{ $item['sessions'] }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="time-report-duration">
                                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                            $item['seconds']
                                        ) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4">

                                    <div class="time-report-empty">

                                        <span>👥</span>

                                        <strong>
                                            No employee data found
                                        </strong>

                                        <p>
                                            Change filters or start tracking work.
                                        </p>

                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        {{-- Role Summary --}}
        <section class="time-report-section">

            <div class="time-report-section-heading">

                <div>
                    <h2>Role-wise Summary</h2>

                    <p>
                        Working time grouped by CRM user role.
                    </p>
                </div>

                <span class="time-report-count">
                    {{ $roleSummary->count() }}
                    Roles
                </span>

            </div>

            <div class="time-report-table-wrapper">

                <table class="time-report-table">

                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Sessions</th>
                            <th>Total Time</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($roleSummary as $item)

                            <tr>

                                <td>
                                    <div class="time-report-role-cell">

                                        <span>
                                            🛡
                                        </span>

                                        <strong>
                                            {{ $item['role'] }}
                                        </strong>

                                    </div>
                                </td>

                                <td>
                                    <strong class="time-report-number">
                                        {{ $item['sessions'] }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="time-report-duration">
                                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                            $item['seconds']
                                        ) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3">

                                    <div class="time-report-empty">

                                        <span>🛡</span>

                                        <strong>
                                            No role data found
                                        </strong>

                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        {{-- Project Summary --}}
        <section class="time-report-section">

            <div class="time-report-section-heading">

                <div>
                    <h2>Project-wise Summary</h2>

                    <p>
                        Total tracked time for every project.
                    </p>
                </div>

                <span class="time-report-count">
                    {{ $projectSummary->count() }}
                    Projects
                </span>

            </div>

            <div class="time-report-table-wrapper">

                <table class="time-report-table">

                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Project Code</th>
                            <th>Sessions</th>
                            <th>Total Time</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($projectSummary as $item)

                            <tr>

                                <td>
                                    <div class="time-report-project-cell">

                                        <span>
                                            📁
                                        </span>

                                        <strong>
                                            {{ $item['project'] }}
                                        </strong>

                                    </div>
                                </td>

                                <td>
                                    <span class="time-report-project-code">
                                        {{ $item['code'] }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="time-report-number">
                                        {{ $item['sessions'] }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="time-report-duration">
                                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                            $item['seconds']
                                        ) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4">

                                    <div class="time-report-empty">

                                        <span>📁</span>

                                        <strong>
                                            No project data found
                                        </strong>

                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        {{-- Detailed Sessions --}}
        <section class="time-report-section">

            <div class="time-report-section-heading">

                <div>
                    <h2>Detailed Sessions</h2>

                    <p>
                        Complete start, end and tracked-time history.
                    </p>
                </div>

                <span class="time-report-count">
                    {{ $entries->count() }}
                    Sessions
                </span>

            </div>

            <div class="time-report-table-wrapper">

                <table class="time-report-table time-report-detail-table">

                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Project</th>
                            <th>Task</th>
                            <th>Started</th>
                            <th>Ended</th>
                            <th>Status</th>
                            <th>Tracked Time</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($entries as $entry)

                            <tr>

                                <td>
                                    <div class="time-report-user-cell">

                                        <span class="time-report-user-avatar small">
                                            {{ strtoupper(
                                                substr(
                                                    $entry->user?->name
                                                    ?? $entry->user_name_snapshot
                                                    ?? 'U',
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </span>

                                        <strong>
                                            {{ $entry->user?->name
                                                ?? $entry->user_name_snapshot
                                                ?? 'Deleted User' }}
                                        </strong>

                                    </div>
                                </td>

                                <td>
                                    <span class="time-report-role-badge">
                                        {{ $entry->role?->name
                                            ?? $entry->role_name_snapshot
                                            ?? 'No Role' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="time-report-project-information">

                                        <strong>
                                            {{ $entry->project?->name
                                                ?? 'Deleted Project' }}
                                        </strong>

                                        <small>
                                            {{ $entry->project?->project_code
                                                ?? '-' }}
                                        </small>

                                    </div>
                                </td>

                                <td>
                                    @if($entry->task)

                                        <a
                                            href="{{ route(
                                                'task.show',
                                                $entry->task->id
                                            ) }}"
                                            class="time-report-task-link"
                                        >
                                            {{ $entry->task->title }}
                                        </a>

                                    @else

                                        <span class="time-report-deleted-text">
                                            Deleted Task
                                        </span>

                                    @endif
                                </td>

                                <td>
                                    <div class="time-report-date-cell">

                                        <strong>
                                            {{ $entry->started_at
                                                ?->format('d M Y') }}
                                        </strong>

                                        <small>
                                            {{ $entry->started_at
                                                ?->format('h:i A') }}
                                        </small>

                                    </div>
                                </td>

                                <td>
                                    @if($entry->stopped_at)

                                        <div class="time-report-date-cell">

                                            <strong>
                                                {{ $entry->stopped_at
                                                    ->format('d M Y') }}
                                            </strong>

                                            <small>
                                                {{ $entry->stopped_at
                                                    ->format('h:i A') }}
                                            </small>

                                        </div>

                                    @else

                                        <span class="time-report-running-text">
                                            Still Active
                                        </span>

                                    @endif
                                </td>

                                <td>
                                    <span
                                        class="time-report-status status-{{ $entry->status }}"
                                    >
                                        @if($entry->status === 'running')
                                            ●
                                        @endif

                                        {{ ucfirst($entry->status) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="time-report-duration">
                                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                            $entry->liveSeconds()
                                        ) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8">

                                    <div class="time-report-empty">

                                        <span>⏱</span>

                                        <strong>
                                            No sessions found
                                        </strong>

                                        <p>
                                            No time entries match the selected filters.
                                        </p>

                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

    </div>

</div>

@endsection