@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset(
        'css/modules/report.css'
    ) }}?v={{ filemtime(
        public_path(
            'css/modules/report.css'
        )
    ) }}">
@endpush

@section('content')

    <div class="report-page lead-report-page">

        {{-- Header --}}
        <div class="lead-report-header">

            <div class="lead-report-heading">

                <div class="lead-report-heading-icon">
                    📌
                </div>

                <div>
                    <h1>Lead Reports</h1>

                    <p>
                        Analyze lead acquisition, conversion,
                        assignment and follow-up performance.
                    </p>
                </div>

            </div>

            <div class="lead-report-header-actions">

                <span class="lead-report-access-badge">

                    <span></span>

                    {{ $canViewAll
        ? 'All Lead Data'
        : 'Assigned Leads Only' }}

                </span>

                @if(
                                auth()->user()->hasPermission(
                                    'leads.view'
                                )
                            )
                            <a href="{{ route(
                        'lead.index'
                    ) }}" class="lead-management-btn">
                                <span>📋</span>

                                Lead Management

                                <span>→</span>
                            </a>
                @endif

            </div>

        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route(
        'report.leads.index'
    ) }}" class="lead-report-filter-card">

            <div class="lead-report-filter-header">

                <div>
                    <span>⚙</span>

                    <div>
                        <h2>Report Filters</h2>

                        <p>
                            Date range Lead Created Date par apply hoga.
                        </p>
                    </div>
                </div>

                <strong>
                    {{ $dateFrom->format('d M Y') }}
                    —
                    {{ $dateTo->format('d M Y') }}
                </strong>

            </div>

            <div class="lead-report-filter-grid">

                <div class="lead-report-field">

                    <label for="lead_report_from">
                        From Date
                    </label>

                    <input type="date" id="lead_report_from" name="date_from" value="{{ $dateFrom->format(
        'Y-m-d'
    ) }}">

                </div>

                <div class="lead-report-field">

                    <label for="lead_report_to">
                        To Date
                    </label>

                    <input type="date" id="lead_report_to" name="date_to" value="{{ $dateTo->format(
        'Y-m-d'
    ) }}">

                </div>

                <div class="lead-report-field lead-report-search">

                    <label for="lead_report_search">
                        Search Lead
                    </label>

                    <input type="text" id="lead_report_search" name="search" value="{{ $search }}"
                        placeholder="Name, phone, email, company or Lead ID">

                </div>

                <div class="lead-report-field">

                    <label for="lead_report_status">
                        Status
                    </label>

                    <select id="lead_report_status" name="status">
                        <option value="">
                            All Statuses
                        </option>

                        @foreach(
                                $statuses
                                as $key => $label
                            )
                            <option value="{{ $key }}" @selected(
                                $status === $key
                            )>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div class="lead-report-field">

                    <label for="lead_report_source">
                        Lead Source
                    </label>

                    <select id="lead_report_source" name="source">
                        <option value="">
                            All Sources
                        </option>

                        @foreach(
                                $sources
                                as $key => $label
                            )
                            <option value="{{ $key }}" @selected(
                                $source === $key
                            )>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div class="lead-report-field">

                    <label for="lead_report_priority">
                        Priority
                    </label>

                    <select id="lead_report_priority" name="priority">
                        <option value="">
                            All Priorities
                        </option>

                        @foreach(
                                $priorities
                                as $key => $label
                            )
                            <option value="{{ $key }}" @selected(
                                $priority === $key
                            )>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                @if($canViewAll)

                    <div class="lead-report-field">

                        <label for="lead_report_employee">
                            Assigned Employee
                        </label>

                        <select id="lead_report_employee" name="assigned_to">
                            <option value="">
                                All Employees
                            </option>

                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(
                                    $assignedTo
                                    === $user->id
                                )>
                                    {{ $user->name }}

                                    @if(!$user->is_active)
                                        — Inactive
                                    @endif
                                </option>
                            @endforeach
                        </select>

                    </div>

                @endif

                <div class="lead-report-field">

                    <label for="lead_report_conversion">
                        Conversion
                    </label>

                    <select id="lead_report_conversion" name="conversion">
                        <option value="all" @selected(
                            $conversion === 'all'
                        )>
                            All Leads
                        </option>

                        <option value="converted" @selected(
                            $conversion
                            === 'converted'
                        )>
                            Converted Only
                        </option>

                        <option value="not_converted" @selected(
                            $conversion
                            === 'not_converted'
                        )>
                            Not Converted
                        </option>
                    </select>

                </div>

                <div class="lead-report-field">

                    <label for="lead_report_followup">
                        Follow-up State
                    </label>

                    <select id="lead_report_followup" name="follow_up_state">
                        <option value="all" @selected(
                            $followUpState === 'all'
                        )>
                            All Follow-ups
                        </option>

                        <option value="overdue" @selected(
                            $followUpState
                            === 'overdue'
                        )>
                            Overdue
                        </option>

                        <option value="today" @selected(
                            $followUpState
                            === 'today'
                        )>
                            Due Today
                        </option>

                        <option value="upcoming" @selected(
                            $followUpState
                            === 'upcoming'
                        )>
                            Upcoming
                        </option>

                        <option value="no_schedule" @selected(
                            $followUpState
                            === 'no_schedule'
                        )>
                            No Schedule
                        </option>
                    </select>

                </div>

                <div class="lead-report-field">

                    <label for="lead_report_per_page">
                        Records Per Page
                    </label>

                    <select id="lead_report_per_page" name="per_page">
                        @foreach(
                                [10, 25, 50, 100]
                                as $size
                            )
                            <option value="{{ $size }}" @selected(
                                $perPage === $size
                            )>
                                {{ $size }} records
                            </option>
                        @endforeach
                    </select>

                </div>

            </div>

            <div class="lead-report-filter-footer">

                <p>
                    Current status and conversion results will be calculated based on the selected created date and cohort.
                </p>

                <div>

                    <a href="{{ route(
        'report.leads.index'
    ) }}" class="lead-report-reset-btn">
                        ↻ Reset
                    </a>

                    <button type="submit" class="lead-report-apply-btn">
                        ✓ Apply Report
                    </button>

                </div>

            </div>

        </form>

        {{-- Summary Cards --}}
        <div class="report-card-grid lead-report-summary-grid">

            <div class="report-stat-card">
                <span>Total Leads</span>

                <strong>
                    {{ $totalLeads }}
                </strong>

                <small>
                    Created during selected period
                </small>
            </div>

            <div class="report-stat-card">
                <span>New Leads</span>

                <strong>
                    {{ $newLeads }}
                </strong>

                <small>
                    Current status is New
                </small>
            </div>

            <div class="report-stat-card warning">
                <span>Qualified Leads</span>

                <strong>
                    {{ $qualifiedLeads }}
                </strong>

                <small>
                    Ready for conversion
                </small>
            </div>

            <div class="report-stat-card success">
                <span>Converted Leads</span>

                <strong>
                    {{ $convertedLeads }}
                </strong>

                <small>
                    Converted into clients
                </small>
            </div>

            <div class="report-stat-card success">
                <span>Conversion Rate</span>

                <strong>
                    {{ $conversionRate }}%
                </strong>

                <small>
                    Converted ÷ total leads
                </small>
            </div>

            <div class="report-stat-card danger">
                <span>Overdue Follow-ups</span>

                <strong>
                    {{ $overdueFollowUps }}
                </strong>

                <small>
                    Requires immediate attention
                </small>
            </div>

            <div class="report-stat-card warning">
                <span>Unassigned Leads</span>

                <strong>
                    {{ $unassignedLeads }}
                </strong>

                <small>
                    No lead owner assigned
                </small>
            </div>

            <div class="report-stat-card">
                <span>Follow-up Coverage</span>

                <strong>
                    {{ $followUpCoverageRate }}%
                </strong>

                <small>
                    {{ $leadsWithFollowUps }}
                    leads have follow-up history
                </small>
            </div>

        </div>

        {{-- Status and Priority --}}
        <div class="report-two-column">

            <section class="report-panel">

                <div class="report-panel-header">

                    <div>
                        <h2>Lead Status Distribution</h2>

                        <p>
                            Current status of selected leads.
                        </p>
                    </div>

                </div>

                <div class="report-bar-list">

                    @foreach(
                            $statuses
                            as $key => $label
                        )
                        @php
                            $count =
                                $statusCounts[$key] ?? 0;

                            $width =
                                ($count / $statusCountMax)
                                * 100;
                        @endphp

                        <div class="report-bar-item">

                            <div>
                                <span>{{ $label }}</span>
                                <strong>{{ $count }}</strong>
                            </div>

                            <div class="report-bar-track">
                                <span style="width: {{ $width }}%"></span>
                            </div>

                        </div>
                    @endforeach

                </div>

            </section>

            <section class="report-panel">

                <div class="report-panel-header">

                    <div>
                        <h2>Priority Distribution</h2>

                        <p>
                            Lead workload by priority.
                        </p>
                    </div>

                </div>

                <div class="report-bar-list">

                    @foreach(
                            $priorities
                            as $key => $label
                        )
                        @php
                            $count =
                                $priorityCounts[$key]
                                ?? 0;

                            $width =
                                (
                                    $count
                                    / $priorityCountMax
                                ) * 100;
                        @endphp

                        <div class="report-bar-item">

                            <div>
                                <span>{{ $label }}</span>
                                <strong>{{ $count }}</strong>
                            </div>

                            <div class="report-bar-track">
                                <span style="width: {{ $width }}%"></span>
                            </div>

                        </div>
                    @endforeach

                </div>

            </section>

        </div>

        {{-- Source Performance --}}
        <section class="lead-report-panel">

            <div class="lead-report-panel-header">

                <div>
                    <span>🌐</span>

                    <div>
                        <h2>Lead Source Performance</h2>

                        <p>
                            Identify which sources generate
                            better-quality and converted leads.
                        </p>
                    </div>
                </div>

                <strong>
                    {{ $sourceRows->count() }}
                    Sources
                </strong>

            </div>

            <div class="lead-report-table-wrapper">

                <table class="lead-source-table">

                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Total Leads</th>
                            <th>Share</th>
                            <th>Converted</th>
                            <th>Conversion Rate</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($sourceRows as $row)

                                            @php
                                                $share =
                                                    $totalLeads > 0
                                                    ? (int) round(
                                                        (
                                                            $row['total']
                                                            / $totalLeads
                                                        ) * 100
                                                    )
                                                    : 0;
                                            @endphp

                                            <tr>

                                                <td>
                                                    <span class="lead-source-name">
                                                        {{ $row['label'] }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <strong>
                                                        {{ $row['total'] }}
                                                    </strong>
                                                </td>

                                                <td>
                                                    <div class="lead-source-progress">

                                                        <div>
                                                            <span style="width: {{ $share }}%"></span>
                                                        </div>

                                                        <strong>
                                                            {{ $share }}%
                                                        </strong>

                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="lead-report-success-value">
                                                        {{ $row['converted'] }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="lead-conversion-rate">
                                                        {{ $row[
                                'conversion_rate'
                            ] }}%
                                                    </span>
                                                </td>

                                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </section>

        {{-- Trend and Follow-up Health --}}
        <div class="report-two-column">

            <section class="report-panel">

                <div class="report-panel-header">

                    <div>
                        <h2>Monthly Lead Trend</h2>

                        <p>
                            Leads created during the selected period.
                        </p>
                    </div>

                </div>

                <div class="report-trend-list">

                    @foreach(
                            $monthlyLeadTrend
                            as $month
                        )
                        <div class="report-trend-item">

                            <span>
                                {{ $month['label'] }}
                            </span>

                            <div>
                                <i style="width: {{ $month['width'] }}%"></i>
                            </div>

                            <strong>
                                {{ $month['count'] }}
                            </strong>

                        </div>
                    @endforeach

                </div>

            </section>

            <section class="report-panel">

                <div class="report-panel-header">

                    <div>
                        <h2>Follow-up Health</h2>

                        <p>
                            Current follow-up schedule summary.
                        </p>
                    </div>

                </div>

                <div class="lead-followup-health-grid">

                    <div class="overdue">
                        <span>Overdue</span>

                        <strong>
                            {{ $overdueFollowUps }}
                        </strong>
                    </div>

                    <div class="today">
                        <span>Due Today</span>

                        <strong>
                            {{ $dueTodayFollowUps }}
                        </strong>
                    </div>

                    <div class="upcoming">
                        <span>Upcoming</span>

                        <strong>
                            {{ $upcomingFollowUps }}
                        </strong>
                    </div>

                    <div class="missing">
                        <span>No Schedule</span>

                        <strong>
                            {{ $noScheduleLeads }}
                        </strong>
                    </div>

                    <div class="lost">
                        <span>Lost Leads</span>

                        <strong>
                            {{ $lostLeads }}
                        </strong>
                    </div>

                    <div class="covered">
                        <span>Coverage</span>

                        <strong>
                            {{ $followUpCoverageRate }}%
                        </strong>
                    </div>

                </div>

            </section>

        </div>

        {{-- Employee Performance --}}
        <section class="lead-report-panel">

            <div class="lead-report-panel-header">

                <div>
                    <span>👥</span>

                    <div>
                        <h2>Employee Lead Performance</h2>

                        <p>
                            Assigned leads, conversions and
                            follow-up workload.
                        </p>
                    </div>
                </div>

                <strong>
                    {{ $employeePerformance->count() }}
                    Employees
                </strong>

            </div>

            <div class="lead-report-table-wrapper">

                <table class="lead-employee-table">

                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th>Total Leads</th>
                            <th>Qualified</th>
                            <th>Converted</th>
                            <th>Lost</th>
                            <th>Overdue</th>
                            <th>Conversion Rate</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse(
                                                $employeePerformance
                                                as $employee
                                            )

                                            @php
                                                $employeeName =
                                                    $employee['name'];

                                                $initial =
                                                    mb_strtoupper(
                                                        mb_substr(
                                                            $employeeName,
                                                            0,
                                                            1
                                                        )
                                                    );
                                            @endphp

                                            <tr>

                                                <td>

                                                    <div class="lead-employee-profile">

                                                        <span>
                                                            {{ $initial }}
                                                        </span>

                                                        <div>
                                                            <strong>
                                                                {{ $employeeName }}
                                                            </strong>

                                                            <small>
                                                                {{ $employee['email']
                                ?? 'No assigned employee' }}
                                                            </small>
                                                        </div>

                                                    </div>

                                                </td>

                                                <td>
                                                    <span class="lead-role-badge">
                                                        {{ $employee['role'] }}
                                                    </span>
                                                </td>

                                                <td>
                                                    {{ $employee[
                                'total_leads'
                            ] }}
                                                </td>

                                                <td>
                                                    {{ $employee[
                                'qualified_leads'
                            ] }}
                                                </td>

                                                <td>
                                                    <span class="lead-report-success-value">
                                                        {{ $employee[
                                'converted_leads'
                            ] }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="{{ $employee['lost_leads'] > 0
                                ? 'lead-report-danger-value'
                                : '' }}">
                                                        {{ $employee[
                                'lost_leads'
                            ] }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="{{ $employee['overdue_leads'] > 0
                                ? 'lead-report-danger-value'
                                : 'lead-report-success-value' }}">
                                                        {{ $employee[
                                'overdue_leads'
                            ] }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="lead-conversion-rate">
                                                        {{ $employee[
                                'conversion_rate'
                            ] }}%
                                                    </span>
                                                </td>

                                            </tr>

                        @empty

                            <tr>
                                <td colspan="8">

                                    <div class="report-empty">
                                        No employee Lead data found.
                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        {{-- Detailed Lead Report --}}
        <section class="lead-report-panel">

            <div class="lead-report-panel-header">

                <div>
                    <span>📋</span>

                    <div>
                        <h2>Detailed Lead Report</h2>

                        <p>
                            Complete Lead, assignment, follow-up
                            and conversion information.
                        </p>
                    </div>
                </div>

                <strong>
                    {{ $leads->total() }}

                    {{ $leads->total() === 1
        ? 'Record'
        : 'Records' }}
                </strong>

            </div>

            <div class="lead-report-table-wrapper">

                <table class="lead-detail-report-table">

                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Source</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Follow-ups</th>
                            <th>Last Follow-up</th>
                            <th>Next Follow-up</th>
                            <th>Conversion</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($leads as $lead)

                                            @php
                                                $nextFollowUp =
                                                    $lead
                                                        ->next_follow_up_at;

                                                if ($lead->isConverted()) {
                                                    $followState =
                                                        'converted';

                                                    $followLabel =
                                                        'Converted';
                                                } elseif (
                                                    $lead
                                                        ->statusDefinition
                                                            ?->is_closed
                                                ) {
                                                    /*
                                                     * CSS styling ke liye existing
                                                     * lost class preserve kar rahe hain.
                                                     */
                                                    $followState =
                                                        'lost';

                                                    /*
                                                     * Database se status ka current
                                                     * dynamic name show hoga.
                                                     */
                                                    $followLabel =
                                                        $lead
                                                            ->statusDefinition
                                                                ?->name
                                                        ?? 'Closed';
                                                } elseif (
                                                    !$nextFollowUp
                                                ) {
                                                    $followState =
                                                        'no_schedule';

                                                    $followLabel =
                                                        'No Schedule';
                                                } elseif (
                                                    $nextFollowUp->lt(now())
                                                ) {
                                                    $followState =
                                                        'overdue';

                                                    $followLabel =
                                                        'Overdue';
                                                } elseif (
                                                    $nextFollowUp->isToday()
                                                ) {
                                                    $followState =
                                                        'today';

                                                    $followLabel =
                                                        'Due Today';
                                                } else {
                                                    $followState =
                                                        'upcoming';

                                                    $followLabel =
                                                        'Upcoming';
                                                }
                                            @endphp

                                            <tr>

                                                <td>

                                                    <div class="lead-report-lead-cell">

                                                        <strong>
                                                            {{ $lead->name }}
                                                        </strong>

                                                        <small>
                                                            Lead #{{ $lead->id }}
                                                        </small>

                                                        <span>
                                                            {{ $lead->phone }}
                                                        </span>

                                                        <span>
                                                            {{ $lead->company
                                ?: 'No company' }}
                                                        </span>

                                                    </div>

                                                </td>

                                                <td>
                                                    <span class="lead-source-badge">
                                                        {{ $sources[
                                $lead->source
                            ] ?? ucfirst(
                                $lead->source
                            ) }}
                                                    </span>
                                                </td>

                                                <td>

                                                    <div class="lead-report-owner">

                                                        <strong>
                                                            {{ $lead
                                ->assignedUser
                                    ?->name
                                ?? 'Unassigned' }}
                                                        </strong>

                                                        <small>
                                                            {{ $lead
                                ->assignedUser
                                    ?->email }}
                                                        </small>

                                                    </div>

                                                </td>

                                                <td>
                                                    <span class="dynamic-lead-option-badge" style="
                                                            --lead-option-color:
                                                            {{ $lead
                                ->priorityDefinition
                                    ?->color
                                ?? '#64748B' }};
                                                        ">
                                                        {{ $lead
                                ->priorityDefinition
                                    ?->name
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $lead->priority
                                    )
                                ) }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="dynamic-lead-option-badge" style="
                                    --lead-option-color:
                                    {{ $lead
                                ->statusDefinition
                                    ?->color
                                ?? '#64748B' }};
                                ">
                                                        {{ $lead
                                ->statusDefinition
                                    ?->name
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $lead->status
                                    )
                                ) }}
                                                    </span>
                                                </td>

                                                <td>

                                                    <div class="lead-report-date">

                                                        <strong>
                                                            {{ $lead
                                ->created_at
                                ->format(
                                    'd M Y'
                                ) }}
                                                        </strong>

                                                        <small>
                                                            {{ $lead
                                ->created_at
                                ->format(
                                    'h:i A'
                                ) }}
                                                        </small>

                                                    </div>

                                                </td>

                                                <td>
                                                    <span class="lead-followup-count">
                                                        {{ $lead
                                ->follow_ups_count }}
                                                    </span>
                                                </td>

                                                <td>

                                                    @if(
                                                                                $lead
                                                                                    ->last_followed_up_at
                                                                            )
                                                                            <div class="lead-report-date">

                                                                                <strong>
                                                                                    {{ \Carbon\Carbon::parse(
                                                            $lead
                                                                ->last_followed_up_at
                                                        )->format(
                                                                'd M Y'
                                                            ) }}
                                                                                </strong>

                                                                                <small>
                                                                                    {{ \Carbon\Carbon::parse(
                                                            $lead
                                                                ->last_followed_up_at
                                                        )->format(
                                                                'h:i A'
                                                            ) }}
                                                                                </small>

                                                                            </div>
                                                    @else
                                                        <span class="lead-no-value">
                                                            Never
                                                        </span>
                                                    @endif

                                                </td>

                                                <td>

                                                    @if($nextFollowUp)

                                                                        <div class="lead-report-date">

                                                                            <strong>
                                                                                {{ $nextFollowUp
                                                        ->format(
                                                            'd M Y'
                                                        ) }}
                                                                            </strong>

                                                                            <small>
                                                                                {{ $nextFollowUp
                                                        ->format(
                                                            'h:i A'
                                                        ) }}
                                                                            </small>

                                                                        </div>

                                                    @else

                                                        <span class="lead-no-value">
                                                            Not scheduled
                                                        </span>

                                                    @endif

                                                    <span class="lead-follow-state {{ $followState }}">
                                                        {{ $followLabel }}
                                                    </span>

                                                </td>

                                                <td>

                                                    @if($lead->isConverted())

                                                                        <div class="lead-conversion-info">

                                                                            <strong>
                                                                                Converted
                                                                            </strong>

                                                                            <small>
                                                                                {{ $lead
                                                        ->converted_at
                                                            ?->format(
                                                            'd M Y'
                                                        )
                                                        ?? '-' }}
                                                                            </small>

                                                                            <span>
                                                                                By
                                                                                {{ $lead
                                                        ->convertedBy
                                                            ?->name
                                                        ?? 'Unknown' }}
                                                                            </span>

                                                                        </div>

                                                    @else

                                                        <span class="lead-not-converted">
                                                            Not Converted
                                                        </span>

                                                    @endif

                                                </td>

                                                <td>

                                                    <a href="{{ route(
                                'lead.show',
                                $lead->id
                            ) }}" class="lead-report-open-btn">
                                                        Open →
                                                    </a>

                                                </td>

                                            </tr>

                        @empty

                            <tr>
                                <td colspan="11">

                                    <div class="report-empty">
                                        No Lead report matched
                                        the selected filters.
                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($leads->hasPages())

                @php
                    $currentPage = $leads->currentPage();
                    $lastPage = $leads->lastPage();

                    $startPage = max(
                        1,
                        $currentPage - 2
                    );

                    $endPage = min(
                        $lastPage,
                        $currentPage + 2
                    );
                @endphp

                <div class="lead-report-pagination">

                    {{-- Pagination Information --}}
                    <div class="lead-report-pagination-info">

                        Showing

                        <strong>
                            {{ $leads->firstItem() }}
                        </strong>

                        to

                        <strong>
                            {{ $leads->lastItem() }}
                        </strong>

                        of

                        <strong>
                            {{ $leads->total() }}
                        </strong>

                        Leads

                    </div>

                    {{-- Pagination Controls --}}
                    <nav class="lead-report-pagination-controls" aria-label="Lead report pagination">

                        {{-- Previous Button --}}
                        @if($leads->onFirstPage())

                            <span class="lead-pagination-btn disabled" aria-disabled="true">
                                <span>←</span>
                                Previous
                            </span>

                        @else

                            <a href="{{ $leads->previousPageUrl() }}" class="lead-pagination-btn" rel="prev">
                                <span>←</span>
                                Previous
                            </a>

                        @endif

                        {{-- First Page --}}
                        @if($startPage > 1)

                            <a href="{{ $leads->url(1) }}" class="lead-pagination-number">
                                1
                            </a>

                            @if($startPage > 2)
                                <span class="lead-pagination-dots">
                                    …
                                </span>
                            @endif

                        @endif

                        {{-- Page Numbers --}}
                        @for(
                                $page = $startPage;
                                $page <= $endPage;
                                $page++
                            )

                            @if($page === $currentPage)

                                <span class="lead-pagination-number active" aria-current="page">
                                    {{ $page }}
                                </span>

                            @else

                                <a href="{{ $leads->url($page) }}" class="lead-pagination-number">
                                    {{ $page }}
                                </a>

                            @endif

                        @endfor

                        {{-- Last Page --}}
                        @if($endPage < $lastPage)

                            @if($endPage < $lastPage - 1)
                                <span class="lead-pagination-dots">
                                    …
                                </span>
                            @endif

                            <a href="{{ $leads->url($lastPage) }}" class="lead-pagination-number">
                                {{ $lastPage }}
                            </a>

                        @endif

                        {{-- Next Button --}}
                        @if($leads->hasMorePages())

                            <a href="{{ $leads->nextPageUrl() }}" class="lead-pagination-btn" rel="next">
                                Next
                                <span>→</span>
                            </a>

                        @else

                            <span class="lead-pagination-btn disabled" aria-disabled="true">
                                Next
                                <span>→</span>
                            </span>

                        @endif

                    </nav>

                </div>

            @endif

        </section>

    </div>

@endsection