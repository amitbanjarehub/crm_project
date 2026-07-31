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

<div class="report-page followup-report-page">

    {{-- Page Header --}}
<div class="followup-report-header">

    <div class="followup-report-heading">

        <div class="followup-report-heading-icon">
            📞
        </div>

        <div>
            <h1>Follow-up Reports</h1>

            <p>
                Analyze follow-up activities, outcomes,
                employee performance and pending schedules.
            </p>
        </div>

    </div>

    <div class="followup-report-header-actions">

        <span class="followup-report-access-badge">
            <span class="followup-access-dot"></span>

            {{ $canViewAll
                ? 'All Follow-up Data'
                : 'Assigned Leads Only' }}
        </span>

        @if(
            auth()->user()->hasPermission(
                'follow_ups.view'
            )
        )
            <a
                href="{{ route(
                    'followup.index'
                ) }}"
                class="followup-management-btn"
            >
                <span class="followup-management-icon">
                    📋
                </span>

                <span>
                    Follow-up Management
                </span>

                <span class="followup-management-arrow">
                    →
                </span>
            </a>
        @endif

    </div>

</div>

{{-- Filters --}}
<form
    method="GET"
    action="{{ route(
        'report.followups.index'
    ) }}"
    class="followup-report-filter-card"
>

    {{-- Filter Header --}}
    <div class="followup-filter-header">

        <div class="followup-filter-heading">

            <div class="followup-filter-icon">
                ⚙
            </div>

            <div>
                <h2>Report Filters</h2>

                <p>
                    Filter activity data and current
                    follow-up schedules.
                </p>
            </div>

        </div>

        <span class="followup-filter-period">
            {{ $dateFrom->format('d M Y') }}
            —
            {{ $dateTo->format('d M Y') }}
        </span>

    </div>

    {{-- Filter Fields --}}
    <div class="followup-filter-grid">

        {{-- From Date --}}
        <div class="followup-report-field">

            <label for="followup_report_date_from">
                From Date
            </label>

            <div class="followup-input-wrapper">

                <span class="followup-input-icon">
                    📅
                </span>

                <input
                    type="date"
                    id="followup_report_date_from"
                    name="date_from"
                    value="{{ $dateFrom->format(
                        'Y-m-d'
                    ) }}"
                >

            </div>

        </div>

        {{-- To Date --}}
        <div class="followup-report-field">

            <label for="followup_report_date_to">
                To Date
            </label>

            <div class="followup-input-wrapper">

                <span class="followup-input-icon">
                    📅
                </span>

                <input
                    type="date"
                    id="followup_report_date_to"
                    name="date_to"
                    value="{{ $dateTo->format(
                        'Y-m-d'
                    ) }}"
                >

            </div>

        </div>

        {{-- Search --}}
        <div class="followup-report-field followup-search-field">

            <label for="followup_report_search">
                Search Lead
            </label>

            <div class="followup-input-wrapper">

                <span class="followup-input-icon">
                    🔍
                </span>

                <input
                    type="text"
                    id="followup_report_search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Lead, phone, email, company or ID"
                >

            </div>

        </div>

        {{-- Type --}}
        <div class="followup-report-field">

            <label for="followup_report_type">
                Follow-up Type
            </label>

            <select
                id="followup_report_type"
                name="type"
            >
                <option value="">
                    All Types
                </option>

                @foreach(
                    $types
                    as $typeKey => $typeLabel
                )
                    <option
                        value="{{ $typeKey }}"
                        @selected(
                            $type === $typeKey
                        )
                    >
                        {{ $typeLabel }}
                    </option>
                @endforeach
            </select>

        </div>

        {{-- Outcome --}}
        <div class="followup-report-field">

            <label for="followup_report_outcome">
                Outcome
            </label>

            <select
                id="followup_report_outcome"
                name="outcome"
            >
                <option value="">
                    All Outcomes
                </option>

                @foreach(
                    $outcomes
                    as $outcomeKey => $outcomeLabel
                )
                    <option
                        value="{{ $outcomeKey }}"
                        @selected(
                            $outcome === $outcomeKey
                        )
                    >
                        {{ $outcomeLabel }}
                    </option>
                @endforeach
            </select>

        </div>

        {{-- Performed By --}}
        <div class="followup-report-field">

            <label for="followup_report_user">
                Performed By
            </label>

            <select
                id="followup_report_user"
                name="performed_by"
            >
                <option value="">
                    All Employees
                </option>

                @foreach($users as $user)
                    <option
                        value="{{ $user->id }}"
                        @selected(
                            $performedBy
                            === $user->id
                        )
                    >
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>

        </div>

        {{-- Current Schedule --}}
        <div class="followup-report-field">

            <label for="followup_report_due">
                Current Schedule
            </label>

            <select
                id="followup_report_due"
                name="due"
            >
                <option
                    value="all"
                    @selected($due === 'all')
                >
                    All Schedules
                </option>

                <option
                    value="overdue"
                    @selected($due === 'overdue')
                >
                    Overdue
                </option>

                <option
                    value="today"
                    @selected($due === 'today')
                >
                    Due Today
                </option>

                <option
                    value="upcoming"
                    @selected($due === 'upcoming')
                >
                    Upcoming
                </option>

                <option
                    value="no_schedule"
                    @selected(
                        $due === 'no_schedule'
                    )
                >
                    No Next Schedule
                </option>
            </select>

        </div>

        {{-- Per Page --}}
        <div class="followup-report-field">

            <label for="followup_report_per_page">
                Records Per Page
            </label>

            <select
                id="followup_report_per_page"
                name="per_page"
            >
                @foreach(
                    [10, 25, 50, 100]
                    as $size
                )
                    <option
                        value="{{ $size }}"
                        @selected(
                            $perPage === $size
                        )
                    >
                        {{ $size }} records
                    </option>
                @endforeach
            </select>

        </div>

    </div>

    {{-- Filter Footer --}}
    <div class="followup-filter-footer">

        <div class="followup-filter-help">
            <span>ℹ</span>

            <p>
                Date range activity analytics par apply hoga.
                Current Schedule filter latest follow-up
                schedule ko filter karega.
            </p>
        </div>

        <div class="followup-filter-actions">

            <a
                href="{{ route(
                    'report.followups.index'
                ) }}"
                class="followup-reset-btn"
            >
                <span>↻</span>
                Reset
            </a>

            <button
                type="submit"
                class="followup-apply-btn"
            >
                <span>✓</span>
                Apply Report
            </button>

        </div>

    </div>

</form>

    {{-- Activity Cards --}}
    <div class="report-card-grid followup-summary-grid">

        <div class="report-stat-card">
            <span>Total Activities</span>

            <strong>
                {{ $totalActivities }}
            </strong>

            <small>
                Actual follow-ups in selected period
            </small>
        </div>

        <div class="report-stat-card">
            <span>Unique Leads</span>

            <strong>
                {{ $uniqueLeads }}
            </strong>

            <small>
                Leads contacted in selected period
            </small>
        </div>

        <div class="report-stat-card success">
            <span>Positive Outcome Rate</span>

            <strong>
                {{ $positiveOutcomeRate }}%
            </strong>

            <small>
                Interested, meeting, qualified or converted
            </small>
        </div>

        <div class="report-stat-card success">
            <span>Conversions</span>

            <strong>
                {{ $convertedOutcomes }}
            </strong>

            <small>
                Converted outcomes in selected period
            </small>
        </div>

        <div class="report-stat-card danger">
            <span>Overdue</span>

            <strong>
                {{ $overdueSchedules }}
            </strong>

            <small>
                Current schedules past due
            </small>
        </div>

        <div class="report-stat-card warning">
            <span>Due Today</span>

            <strong>
                {{ $dueTodaySchedules }}
            </strong>

            <small>
                Remaining follow-ups for today
            </small>
        </div>

        <div class="report-stat-card">
            <span>Upcoming</span>

            <strong>
                {{ $upcomingSchedules }}
            </strong>

            <small>
                Scheduled after today
            </small>
        </div>

        <div class="report-stat-card">
            <span>No Next Schedule</span>

            <strong>
                {{ $noScheduleCount }}
            </strong>

            <small>
                Active leads without next follow-up
            </small>
        </div>

    </div>

    {{-- Distribution --}}
    <div class="report-two-column">

        {{-- Type Distribution --}}
        <section class="report-panel">

            <div class="report-panel-header">
                <div>
                    <h2>Follow-up Type Distribution</h2>

                    <p>
                        Communication methods used during
                        the selected period.
                    </p>
                </div>
            </div>

            <div class="report-bar-list">

                @foreach(
                    $types
                    as $typeKey => $typeLabel
                )
                    @php
                        $count =
                            $typeCounts[
                                $typeKey
                            ] ?? 0;

                        $width = (
                            $count
                            / $typeCountMax
                        ) * 100;
                    @endphp

                    <div class="report-bar-item">

                        <div>
                            <span>
                                {{ $typeLabel }}
                            </span>

                            <strong>
                                {{ $count }}
                            </strong>
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

        {{-- Outcome Distribution --}}
        <section class="report-panel">

            <div class="report-panel-header">
                <div>
                    <h2>Outcome Distribution</h2>

                    <p>
                        Results of completed follow-up
                        activities.
                    </p>
                </div>
            </div>

            <div class="report-bar-list">

                @foreach(
                    $outcomes
                    as $outcomeKey => $outcomeLabel
                )
                    @php
                        $count =
                            $outcomeCounts[
                                $outcomeKey
                            ] ?? 0;

                        $width = (
                            $count
                            / $outcomeCountMax
                        ) * 100;
                    @endphp

                    <div class="report-bar-item">

                        <div>
                            <span>
                                {{ $outcomeLabel }}
                            </span>

                            <strong>
                                {{ $count }}
                            </strong>
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

    </div>

    {{-- Employee Follow-up Performance --}}
<section class="followup-performance-panel">

    {{-- Panel Header --}}
    <div class="followup-performance-header">

        <div class="followup-performance-heading">

            <div class="followup-performance-icon">
                👥
            </div>

            <div>
                <h2>
                    Employee Follow-up Performance
                </h2>

                <p>
                    Follow-up activities and outcomes during
                    the selected period.
                </p>
            </div>

        </div>

        <span class="followup-performance-count">
            {{ $employeePerformance->count() }}

            {{ $employeePerformance->count() === 1
                ? 'Employee'
                : 'Employees' }}
        </span>

    </div>

    {{-- Table --}}
    <div class="followup-performance-table-wrapper">

        <table class="followup-performance-table">

            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Activities</th>
                    <th>Unique Leads</th>
                    <th>Positive</th>
                    <th>Positive Rate</th>
                    <th>Conversions</th>
                    <th>No Response</th>
                </tr>
            </thead>

            <tbody>

                @forelse(
                    $employeePerformance
                    as $employee
                )

                    @php
                        $employeeName =
                            $employee['name']
                            ?? 'Deleted User';

                        $employeeInitial = mb_strtoupper(
                            mb_substr(
                                $employeeName,
                                0,
                                1
                            )
                        );

                        $positiveRate =
                            (int) (
                                $employee[
                                    'positive_rate'
                                ] ?? 0
                            );

                        $noResponses =
                            (int) (
                                $employee[
                                    'no_responses'
                                ] ?? 0
                            );
                    @endphp

                    <tr>

                        {{-- Employee --}}
                        <td>

                            <div class="followup-employee-profile">

                                <div class="followup-employee-avatar">
                                    {{ $employeeInitial }}
                                </div>

                                <div class="followup-employee-info">

                                    <strong>
                                        {{ $employeeName }}
                                    </strong>

                                    <small>
                                        {{ $employee['email']
                                            ?? 'Deleted employee account' }}
                                    </small>

                                    @if(
                                        !empty(
                                            $employee['user_id']
                                        )
                                    )
                                        <span>
                                            Employee ID:
                                            {{ $employee['user_id'] }}
                                        </span>
                                    @endif

                                </div>

                            </div>

                        </td>

                        {{-- Role --}}
                        <td>

                            <span class="followup-role-badge">
                                {{ $employee['role']
                                    ?? 'No Role' }}
                            </span>

                        </td>

                        {{-- Activities --}}
                        <td>

                            <div class="followup-metric-cell blue">

                                <strong>
                                    {{ $employee[
                                        'total_follow_ups'
                                    ] }}
                                </strong>

                                <span>
                                    Activities
                                </span>

                            </div>

                        </td>

                        {{-- Unique Leads --}}
                        <td>

                            <div class="followup-metric-cell neutral">

                                <strong>
                                    {{ $employee[
                                        'unique_leads'
                                    ] }}
                                </strong>

                                <span>
                                    Leads
                                </span>

                            </div>

                        </td>

                        {{-- Positive --}}
                        <td>

                            <div class="followup-metric-cell green">

                                <strong>
                                    {{ $employee[
                                        'positive_outcomes'
                                    ] }}
                                </strong>

                                <span>
                                    Positive
                                </span>

                            </div>

                        </td>

                        {{-- Positive Rate --}}
                        <td>

                            <div class="followup-positive-rate">

                                <div class="followup-rate-circle">
                                    {{ $positiveRate }}%
                                </div>

                                <div class="followup-rate-progress">

                                    <div>
                                        <span
                                            style="width: {{ min(
                                                100,
                                                max(
                                                    0,
                                                    $positiveRate
                                                )
                                            ) }}%"
                                        ></span>
                                    </div>

                                    <small>
                                        Success rate
                                    </small>

                                </div>

                            </div>

                        </td>

                        {{-- Conversions --}}
                        <td>

                            <span class="followup-conversion-badge">
                                <span>✓</span>

                                {{ $employee[
                                    'conversions'
                                ] }}
                            </span>

                        </td>

                        {{-- No Response --}}
                        <td>

                            <span
                                class="followup-response-badge
                                    {{ $noResponses > 0
                                        ? 'has-response'
                                        : 'no-response' }}"
                            >

                                <span>
                                    {{ $noResponses > 0
                                        ? '!'
                                        : '✓' }}
                                </span>

                                {{ $noResponses }}

                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8">

                            <div class="followup-performance-empty">

                                <div>
                                    👤
                                </div>

                                <strong>
                                    No Employee Activity
                                </strong>

                                <p>
                                    No employee follow-up activity
                                    was found for the selected period.
                                </p>

                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</section>

   {{-- Current Follow-up Schedule --}}
<section class="followup-schedule-panel">

    {{-- Panel Header --}}
    <div class="followup-schedule-header">

        <div class="followup-schedule-heading">

            <div class="followup-schedule-heading-icon">
                📅
            </div>

            <div>
                <h2>
                    Current Follow-up Schedule
                </h2>

                <p>
                    Latest follow-up schedule for each active lead.
                    Historical duplicate schedules are excluded.
                </p>
            </div>

        </div>

        <span class="followup-schedule-count">

            {{ $followUps->total() }}

            {{ $followUps->total() === 1
                ? 'Record'
                : 'Records' }}

        </span>

    </div>

    {{-- Table Wrapper --}}
    <div class="followup-schedule-table-wrapper">

        <table class="followup-schedule-table">

            <thead>
                <tr>
                    <th>Lead</th>
                    <th>Lead Owner</th>
                    <th>Performed By</th>
                    <th>Last Follow-up</th>
                    <th>Type</th>
                    <th>Outcome</th>
                    <th>Next Schedule</th>
                    <th>Due State</th>
                    <th>Notes</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse(
                    $followUps
                    as $followUp
                )

                    @php
                        $nextSchedule =
                            $followUp->next_follow_up_at;

                        if (!$nextSchedule) {
                            $dueState = 'no_schedule';
                            $dueLabel = 'No Schedule';
                        } elseif ($nextSchedule->lt(now())) {
                            $dueState = 'overdue';
                            $dueLabel = 'Overdue';
                        } elseif ($nextSchedule->isToday()) {
                            $dueState = 'today';
                            $dueLabel = 'Due Today';
                        } else {
                            $dueState = 'upcoming';
                            $dueLabel = 'Upcoming';
                        }

                        $leadName =
                            $followUp->lead?->name
                            ?? 'Deleted Lead';

                        $leadInitial = mb_strtoupper(
                            mb_substr(
                                $leadName,
                                0,
                                1
                            )
                        );

                        $leadOwnerName =
                            $followUp
                                ->lead
                                ?->assignedUser
                                ?->name
                            ?? 'Unassigned';

                        $performedByName =
                            $followUp
                                ->user
                                ?->name
                            ?? 'Deleted User';
                    @endphp

                    <tr>

                        {{-- Lead --}}
                        <td>

                            <div class="followup-schedule-lead">

                                <div class="followup-schedule-lead-avatar">
                                    {{ $leadInitial }}
                                </div>

                                <div class="followup-schedule-lead-info">

                                    <strong>
                                        {{ $leadName }}
                                    </strong>

                                    <small>
                                        {{ $followUp
                                            ->lead
                                            ?->phone
                                            ?? 'No phone' }}
                                    </small>

                                    <span>
                                        {{ $followUp
                                            ->lead
                                            ?->company
                                            ?: 'No company' }}
                                    </span>

                                    @if(
                                        $followUp
                                            ->lead
                                            ?->client
                                    )
                                        <em>
                                            Converted Client
                                        </em>
                                    @endif

                                </div>

                            </div>

                        </td>

                        {{-- Lead Owner --}}
                        <td>

                            <div class="followup-person-cell">

                                <span class="followup-person-avatar owner">
                                    {{ mb_strtoupper(
                                        mb_substr(
                                            $leadOwnerName,
                                            0,
                                            1
                                        )
                                    ) }}
                                </span>

                                <div>
                                    <strong>
                                        {{ $leadOwnerName }}
                                    </strong>

                                    <small>
                                        Lead owner
                                    </small>
                                </div>

                            </div>

                        </td>

                        {{-- Performed By --}}
                        <td>

                            <div class="followup-person-cell">

                                <span class="followup-person-avatar performer">
                                    {{ mb_strtoupper(
                                        mb_substr(
                                            $performedByName,
                                            0,
                                            1
                                        )
                                    ) }}
                                </span>

                                <div>
                                    <strong>
                                        {{ $performedByName }}
                                    </strong>

                                    <small>
                                        Follow-up by
                                    </small>
                                </div>

                            </div>

                        </td>

                        {{-- Last Follow-up --}}
                        <td>

                            <div class="followup-schedule-date">

                                <strong>
                                    {{ $followUp
                                        ->followed_up_at
                                        ->format('d M Y') }}
                                </strong>

                                <small>
                                    {{ $followUp
                                        ->followed_up_at
                                        ->format('h:i A') }}
                                </small>

                            </div>

                        </td>

                        {{-- Type --}}
                        <td>

                            <span class="followup-schedule-type">

                                {{ $types[
                                    $followUp->type
                                ] ?? ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $followUp->type
                                    )
                                ) }}

                            </span>

                        </td>

                        {{-- Outcome --}}
                        <td>

                            <span
                                class="followup-schedule-outcome
                                    outcome-{{ $followUp->outcome }}"
                            >

                                {{ $outcomes[
                                    $followUp->outcome
                                ] ?? ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $followUp->outcome
                                    )
                                ) }}

                            </span>

                        </td>

                        {{-- Next Schedule --}}
                        <td>

                            @if($nextSchedule)

                                <div class="followup-schedule-date next">

                                    <strong>
                                        {{ $nextSchedule
                                            ->format('d M Y') }}
                                    </strong>

                                    <small>
                                        {{ $nextSchedule
                                            ->format('h:i A') }}
                                    </small>

                                </div>

                            @else

                                <span class="followup-no-schedule">
                                    Not scheduled
                                </span>

                            @endif

                        </td>

                        {{-- Due State --}}
                        <td>

                            <span
                                class="followup-schedule-due
                                    {{ $dueState }}"
                            >

                                <span></span>

                                {{ $dueLabel }}

                            </span>

                        </td>

                        {{-- Notes --}}
                        <td>

                            @if(
                                filled(
                                    $followUp->notes
                                )
                            )

                                <div
                                    class="followup-schedule-notes"
                                    title="{{ $followUp->notes }}"
                                >
                                    {{ \Illuminate\Support\Str::limit(
                                        $followUp->notes,
                                        70
                                    ) }}
                                </div>

                            @else

                                <span class="followup-no-notes">
                                    No notes
                                </span>

                            @endif

                        </td>

                        {{-- Action --}}
                        <td>

                            <a
                                href="{{ route(
                                    'lead.show',
                                    $followUp->lead_id
                                ) }}"
                                class="followup-open-lead-btn"
                            >
                                <span>
                                    Open
                                </span>

                                <span>
                                    →
                                </span>
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10">

                            <div class="followup-schedule-empty">

                                <span>
                                    📅
                                </span>

                                <strong>
                                    No Follow-up Schedule Found
                                </strong>

                                <p>
                                    No current follow-up schedule
                                    matched the selected filters.
                                </p>

                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- Pagination --}}
    @if($followUps->hasPages())

        <div class="followup-schedule-pagination">

            <div>
                Showing
                <strong>
                    {{ $followUps->firstItem() }}
                </strong>
                to
                <strong>
                    {{ $followUps->lastItem() }}
                </strong>
                of
                <strong>
                    {{ $followUps->total() }}
                </strong>
                records
            </div>

            <div>
                {{ $followUps->links() }}
            </div>

        </div>

    @endif

</section>
</div>

@endsection