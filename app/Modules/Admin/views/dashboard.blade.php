


@extends('admin::layouts.app')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/modules/dashboard.css') }}?v={{ filemtime(public_path('css/modules/dashboard.css')) }}"
    >
@endpush

@section('content')

<div class="page-heading dashboard-heading">
    <div>
        <h1>Dashboard</h1>

        <p>
             Your CRM overview, follow-up reminders and project task deadlines.
        </p>
    </div>

    <div class="dashboard-current-date">
        <span>Today</span>

        <strong>
            {{ now()->format('d M Y') }}
        </strong>
    </div>
</div>

@if ($showReminderSection)

    <section class="dashboard-reminder-section">

        <div class="dashboard-section-heading">
            <div>
                <h2>Follow-up Reminder Overview</h2>

                <p>
                    @if ($canViewAllLeads)
                        Showing reminders for all accessible CRM leads.
                    @else
                        Showing reminders only for leads assigned to you.
                    @endif
                </p>
            </div>

            <a
                href="{{ route('followup.index') }}"
                class="dashboard-section-link"
            >
                Open Follow-up Management
            </a>
        </div>

        <div class="reminder-card-grid">

            <div class="reminder-card reminder-today">
                <div class="reminder-card-icon">
                    📅
                </div>

                <div class="reminder-card-content">
                    <span>Due Today</span>

                    <strong>
                        {{ number_format($reminderSummary['today']) }}
                    </strong>

                    <small>
                        Follow-ups scheduled today
                    </small>
                </div>
            </div>

            <div class="reminder-card reminder-overdue">
                <div class="reminder-card-icon">
                    ⚠️
                </div>

                <div class="reminder-card-content">
                    <span>Overdue</span>

                    <strong>
                        {{ number_format($reminderSummary['overdue']) }}
                    </strong>

                    <small>
                        Follow-ups pending before today
                    </small>
                </div>
            </div>

            <div class="reminder-card reminder-upcoming">
                <div class="reminder-card-icon">
                    🗓️
                </div>

                <div class="reminder-card-content">
                    <span>Next 7 Days</span>

                    <strong>
                        {{ number_format($reminderSummary['next_seven_days']) }}
                    </strong>

                    <small>
                        Upcoming scheduled follow-ups
                    </small>
                </div>
            </div>

            <div class="reminder-card reminder-priority">
                <div class="reminder-card-icon">
                    🔥
                </div>

                <div class="reminder-card-content">
                    <span>High Priority</span>

                    <strong>
                        {{ number_format($reminderSummary['high_priority']) }}
                    </strong>

                    <small>
                        High and urgent pending leads
                    </small>
                </div>
            </div>

        </div>

        <div class="dashboard-work-grid">

            {{-- Today and Overdue Follow-ups --}}
            <div class="dashboard-panel dashboard-followup-panel">

                <div class="dashboard-panel-header">
                    <div>
                        <h3>Today & Overdue Follow-ups</h3>

                        <p>
                            Most urgent scheduled lead follow-ups
                        </p>
                    </div>

                    <span class="dashboard-record-count">
                        {{ $dueLeads->count() }} shown
                    </span>
                </div>

                <div class="dashboard-table-wrapper">
                    <table class="dashboard-reminder-table">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Schedule</th>
                                <th>Priority</th>
                                <th>Assigned To</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($dueLeads as $lead)
                                @php
                                    $isOverdue = $lead->next_follow_up_at
                                        && $lead->next_follow_up_at->lt(
                                            now()->startOfDay()
                                        );

                                    $phoneDigits = preg_replace(
                                        '/\D+/',
                                        '',
                                        $lead->phone
                                    );

                                    if (
                                        strlen($phoneDigits) === 10
                                    ) {
                                        $whatsappNumber =
                                            '91' . $phoneDigits;
                                    } elseif (
                                        strlen($phoneDigits) === 11
                                        && str_starts_with(
                                            $phoneDigits,
                                            '0'
                                        )
                                    ) {
                                        $whatsappNumber =
                                            '91' . substr(
                                                $phoneDigits,
                                                1
                                            );
                                    } else {
                                        $whatsappNumber =
                                            $phoneDigits;
                                    }
                                @endphp

                                <tr>
                                    <td>
                                        <div class="dashboard-lead-cell">
                                            <strong>
                                                {{ $lead->name }}
                                            </strong>

                                            <span>
                                                {{ $lead->phone }}
                                            </span>

                                            <small>
                                                Lead #{{ $lead->id }}
                                                @if ($lead->company)
                                                    · {{ $lead->company }}
                                                @endif
                                            </small>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="dashboard-schedule-cell">
                                            <strong>
                                                {{ $lead->next_follow_up_at->format('d M Y') }}
                                            </strong>

                                            <span>
                                                {{ $lead->next_follow_up_at->format('h:i A') }}
                                            </span>

                                            @if ($isOverdue)
                                                <small class="due-badge overdue">
                                                    Overdue
                                                </small>
                                            @else
                                                <small class="due-badge today">
                                                    Due Today
                                                </small>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <span class="dashboard-priority-badge {{ $lead->priority }}">
                                            {{ $leadPriorities[$lead->priority] ?? ucfirst($lead->priority) }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="dashboard-user-cell">
                                            <strong>
                                                {{ $lead->assignedUser?->name ?? 'Unassigned' }}
                                            </strong>

                                            @if ($lead->assignedUser)
                                                <small>
                                                    {{ $lead->assignedUser->email }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="dashboard-row-actions">

                                            <a
                                                href="{{ route('lead.show', $lead->id) }}"
                                                class="dashboard-action view"
                                            >
                                                View
                                            </a>

                                            @if (
                                                auth()->user()->hasPermission('follow_ups.create')
                                            )
                                                <a
                                                    href="{{ route('followup.create', $lead->id) }}"
                                                    class="dashboard-action followup"
                                                >
                                                    Follow-up
                                                </a>
                                            @endif

                                            @if ($whatsappNumber !== '')
                                                <a
                                                    href="https://wa.me/{{ $whatsappNumber }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="dashboard-action whatsapp"
                                                >
                                                    WhatsApp
                                                </a>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="dashboard-empty-state"
                                    >
                                        <strong>
                                            No follow-ups due today.
                                        </strong>

                                        <span>
                                            There are no overdue or today reminders.
                                        </span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- High Priority Pending Leads --}}
            <div class="dashboard-panel high-priority-panel">

                <div class="dashboard-panel-header">
                    <div>
                        <h3>High-Priority Leads</h3>

                        <p>
                            High and urgent leads needing attention
                        </p>
                    </div>
                </div>

                <div class="high-priority-list">

                    @forelse ($highPriorityLeads as $lead)
                        @php
                            $priorityDueClass = 'upcoming';
                            $priorityDueText = 'Upcoming';

                            if (!$lead->next_follow_up_at) {
                                $priorityDueClass = 'unscheduled';
                                $priorityDueText = 'No follow-up scheduled';
                            } elseif (
                                $lead->next_follow_up_at->lt(
                                    now()->startOfDay()
                                )
                            ) {
                                $priorityDueClass = 'overdue';
                                $priorityDueText = 'Overdue';
                            } elseif (
                                $lead->next_follow_up_at->isToday()
                            ) {
                                $priorityDueClass = 'today';
                                $priorityDueText = 'Due today';
                            } else {
                                $priorityDueText =
                                    $lead->next_follow_up_at
                                        ->format('d M Y');
                            }
                        @endphp

                        <div class="high-priority-item">

                            <div class="high-priority-item-top">
                                <div>
                                    <strong>
                                        {{ $lead->name }}
                                    </strong>

                                    <span>
                                        {{ $lead->company ?: $lead->phone }}
                                    </span>
                                </div>

                                <span class="dashboard-priority-badge {{ $lead->priority }}">
                                    {{ $leadPriorities[$lead->priority] ?? ucfirst($lead->priority) }}
                                </span>
                            </div>

                            <div class="high-priority-item-meta">
                                <span class="priority-due-text {{ $priorityDueClass }}">
                                    {{ $priorityDueText }}
                                </span>

                                <span>
                                    {{ $leadStatuses[$lead->status] ?? ucfirst($lead->status) }}
                                </span>

                                <span>
                                    {{ $lead->assignedUser?->name ?? 'Unassigned' }}
                                </span>
                            </div>

                            <div class="high-priority-item-actions">
                                <a
                                    href="{{ route('lead.show', $lead->id) }}"
                                    class="dashboard-action view"
                                >
                                    Open Lead
                                </a>

                                @if (
                                    auth()->user()->hasPermission('follow_ups.create')
                                )
                                    <a
                                        href="{{ route('followup.create', $lead->id) }}"
                                        class="dashboard-action followup"
                                    >
                                        Add Follow-up
                                    </a>
                                @endif
                            </div>

                        </div>
                    @empty
                        <div class="dashboard-empty-card">
                            <strong>No high-priority pending leads</strong>

                            <span>
                                High and urgent leads will appear here.
                            </span>
                        </div>
                    @endforelse

                </div>

            </div>

        </div>

    </section>

@endif


{{-- Project Task Reminder Section --}}
@if(auth()->user()->hasPermission('tasks.view'))

    <section class="dashboard-reminder-section dashboard-task-section">

        <div class="dashboard-section-heading">

            <div>
                <h2>Task Reminder Overview</h2>

                <p>
                    @if(
                        auth()->user()->isSuperAdmin()
                        || auth()->user()->hasPermission('tasks.view_all')
                    )
                        Showing project tasks available according to your access.
                    @else
                        Showing project tasks currently assigned to you.
                    @endif
                </p>
            </div>

            <a
                href="{{ route('task.my') }}"
                class="dashboard-section-link"
            >
                Open My Tasks
            </a>

        </div>

        {{-- Task Summary Cards --}}
        <div class="reminder-card-grid">

            {{-- Due Today --}}
            <div class="reminder-card reminder-today">

                <div class="reminder-card-icon">
                    ✅
                </div>

                <div class="reminder-card-content">
                    <span>Tasks Due Today</span>

                    <strong>
                        {{ number_format(
                            $taskSummary['due_today']
                        ) }}
                    </strong>

                    <small>
                        Open project tasks due today
                    </small>
                </div>

            </div>

            {{-- Overdue --}}
            <div class="reminder-card reminder-overdue">

                <div class="reminder-card-icon">
                    ⚠️
                </div>

                <div class="reminder-card-content">
                    <span>Overdue Tasks</span>

                    <strong>
                        {{ number_format(
                            $taskSummary['overdue']
                        ) }}
                    </strong>

                    <small>
                        Tasks pending before today
                    </small>
                </div>

            </div>

            {{-- In Review --}}
            <div class="reminder-card reminder-upcoming">

                <div class="reminder-card-icon">
                    🔍
                </div>

                <div class="reminder-card-content">
                    <span>In Review</span>

                    <strong>
                        {{ number_format(
                            $taskSummary['in_review']
                        ) }}
                    </strong>

                    <small>
                        Tasks waiting for approval
                    </small>
                </div>

            </div>

            {{-- Completed Today --}}
            <div class="reminder-card reminder-priority">

                <div class="reminder-card-icon">
                    🎯
                </div>

                <div class="reminder-card-content">
                    <span>Completed Today</span>

                    <strong>
                        {{ number_format(
                            $taskSummary['completed_today']
                        ) }}
                    </strong>

                    <small>
                        Tasks completed today
                    </small>
                </div>

            </div>

        </div>

        {{-- Pending Task List --}}
        <div class="dashboard-panel dashboard-task-panel">

            <div class="dashboard-panel-header">

                <div>
                    <h3>Pending Project Tasks</h3>

                    <p>
                        Upcoming and overdue tasks requiring attention
                    </p>
                </div>

                <span class="dashboard-record-count">
                    {{ $dashboardTasks->count() }} shown
                </span>

            </div>

            <div class="dashboard-table-wrapper">

                <table class="dashboard-reminder-table dashboard-task-table">

                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Service</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($dashboardTasks as $task)

                            @php
                                $taskDueClass = 'upcoming';
                                $taskDueText = 'Upcoming';

                                if (!$task->due_at) {
                                    $taskDueClass = 'unscheduled';
                                    $taskDueText = 'No deadline';
                                } elseif (
                                    $task->due_at->lt(
                                        now()->startOfDay()
                                    )
                                ) {
                                    $taskDueClass = 'overdue';
                                    $taskDueText = 'Overdue';
                                } elseif (
                                    $task->due_at->isToday()
                                ) {
                                    $taskDueClass = 'today';
                                    $taskDueText = 'Due Today';
                                } else {
                                    $taskDueText = 'Upcoming';
                                }

                                $taskStatusLabel = ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $task->status
                                    )
                                );
                            @endphp

                            <tr>

                                {{-- Task --}}
                                <td>
                                    <div class="dashboard-lead-cell">

                                        <strong>
                                            {{ $task->title }}
                                        </strong>

                                        <span>
                                            Task #{{ $task->id }}
                                        </span>

                                        <small>
                                            Progress:
                                            {{ $task->progress_percent }}%
                                        </small>

                                    </div>
                                </td>

                                {{-- Project --}}
                                <td>
                                    <div class="dashboard-user-cell">

                                        <strong>
                                            {{ $task->project?->name
                                                ?? 'Unknown Project' }}
                                        </strong>

                                        <small>
                                            {{ $task->project?->project_code
                                                ?? '-' }}
                                        </small>

                                    </div>
                                </td>

                                {{-- Service --}}
                                <td>
                                    {{ $task->projectService?->name
                                        ?? '-' }}
                                </td>

                                {{-- Due Date --}}
                                <td>
                                    <div class="dashboard-schedule-cell">

                                        @if($task->due_at)
                                            <strong>
                                                {{ $task->due_at->format(
                                                    'd M Y'
                                                ) }}
                                            </strong>

                                            <span>
                                                {{ $task->due_at->format(
                                                    'h:i A'
                                                ) }}
                                            </span>
                                        @else
                                            <strong>
                                                No deadline
                                            </strong>
                                        @endif

                                        <small
                                            class="due-badge {{ $taskDueClass }}"
                                        >
                                            {{ $taskDueText }}
                                        </small>

                                    </div>
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span
                                        class="dashboard-task-status status-{{ $task->status }}"
                                    >
                                        {{ $taskStatusLabel }}
                                    </span>
                                </td>

                                {{-- Assigned User --}}
                                <td>
                                    <div class="dashboard-user-cell">

                                        <strong>
                                            {{ $task->assignedUser?->name
                                                ?? 'Unassigned' }}
                                        </strong>

                                        @if($task->assignedUser)
                                            <small>
                                                {{ $task->assignedUser->email }}
                                            </small>
                                        @endif

                                    </div>
                                </td>

                                {{-- Action --}}
                                <td>
                                    <div class="dashboard-row-actions">

                                        <a
                                            href="{{ route(
                                                'task.show',
                                                $task->id
                                            ) }}"
                                            class="dashboard-action view"
                                        >
                                            Open Task
                                        </a>

                                        @if(
                                            $task->project
                                            && auth()->user()->hasPermission(
                                                'projects.view'
                                            )
                                        )
                                            <a
                                                href="{{ route(
                                                    'project.show',
                                                    $task->project_id
                                                ) }}"
                                                class="dashboard-action followup"
                                            >
                                                Project
                                            </a>
                                        @endif

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="7"
                                    class="dashboard-empty-state"
                                >
                                    <strong>
                                        No pending project tasks.
                                    </strong>

                                    <span>
                                        Assigned and upcoming tasks will appear here.
                                    </span>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </section>

@endif

{{-- Existing CRM Summary Cards --}}
@if (count($cards) > 0)

    <section class="dashboard-summary-section">

        <div class="dashboard-section-heading">
            <div>
                <h2>CRM Summary</h2>
                <p>Overview based on your role permissions</p>
            </div>
        </div>

        <div class="stats-grid">

            @foreach ($cards as $card)
                <div class="stat-card">

                    <div class="stat-content">
                        <p>{{ $card['title'] }}</p>

                        <h2>
                            {{ number_format($card['value']) }}
                        </h2>

                        <span>
                            {{ $card['note'] }}
                        </span>
                    </div>

                    <div class="stat-icon {{ $card['color'] }}">
                        {{ $card['icon'] }}
                    </div>

                </div>
            @endforeach

        </div>

    </section>

@endif

{{-- Existing Quick Actions --}}
@if (count($quickActions) > 0)

    <div class="quick-card">

        <div class="section-title">
            <h3>Quick Actions</h3>

            <p>
                Frequently used allowed CRM sections
            </p>
        </div>

        <div class="quick-grid">

            @foreach ($quickActions as $action)
                <a
                    href="{{ $action['route'] }}"
                    class="quick-action"
                >
                    <div>
                        {{ $action['icon'] }}
                    </div>

                    <h4>
                        {{ $action['title'] }}
                    </h4>

                    <p>
                        {{ $action['description'] }}
                    </p>
                </a>
            @endforeach

        </div>

    </div>

@endif

@if (
    !$showReminderSection
    && count($cards) === 0
)

    <div class="quick-card">
        <div class="section-title">
            <h3>No Dashboard Information Available</h3>

            <p>
                Your role does not currently have access to CRM dashboard modules.
            </p>
        </div>
    </div>

@endif

@endsection