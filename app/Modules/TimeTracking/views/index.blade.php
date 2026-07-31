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

@php
    $loggedInUser = auth()->user();

    $canViewTeamReport =
        $loggedInUser->hasAnyPermission([
            'time_tracking.view_team',
            'time_tracking.view_all',
        ]);

    $isTimerRunning =
        $activeEntry
        && $activeEntry->status === 'running';

    $isTimerPaused =
        $activeEntry
        && $activeEntry->status === 'paused';
@endphp

<div class="time-my-page">

    <div class="content-card time-my-main-card">

        {{-- Page Header --}}
        <div class="time-my-header">

            <div class="time-my-heading">

                <span class="time-my-heading-icon">
                    ⏱
                </span>

                <div>
                    <h1>
                        My Time Tracking
                    </h1>

                    <p>
                        Review your work sessions and tracked hours.
                    </p>
                </div>

            </div>

            <div class="time-my-header-actions">

                <a
                    href="{{ route('task.my') }}"
                    class="time-my-secondary-btn"
                >
                    <span>✓</span>
                    My Tasks
                </a>

                @if($canViewTeamReport)
                    <a
                        href="{{ route('timetracking.report') }}"
                        class="time-my-primary-btn"
                    >
                        <span>📊</span>
                        Team Report
                    </a>
                @endif

            </div>

        </div>

        {{-- Current Active Timer --}}
        @if($activeEntry)

            <section
                class="time-my-active-timer
                    {{ $isTimerPaused
                        ? 'timer-paused'
                        : 'timer-running' }}"
            >

                <div class="time-my-active-main">

                    <span class="time-my-active-indicator">
                    </span>

                    <div class="time-my-active-information">

                        <span class="time-my-active-label">
                            {{ $isTimerPaused
                                ? 'Timer Paused'
                                : 'Currently Working' }}
                        </span>

                        <a
                            href="{{ $activeEntry->task
                                ? route(
                                    'task.show',
                                    $activeEntry->task->id
                                )
                                : '#' }}"
                        >
                            {{ $activeEntry->task?->title
                                ?? 'Deleted Task' }}
                        </a>

                        <small>
                            {{ $activeEntry->project?->project_code
                                ?? '-' }}

                            @if($activeEntry->project)
                                · {{ $activeEntry->project->name }}
                            @endif
                        </small>

                    </div>

                </div>

                <div class="time-my-active-clock">

                    <span>Current Session</span>

                    <strong>
                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                            $activeEntry->liveSeconds()
                        ) }}
                    </strong>

                </div>

                <a
                    href="{{ $activeEntry->task
                        ? route(
                            'task.show',
                            $activeEntry->task->id
                        )
                        : '#' }}"
                    class="time-my-open-task-btn"
                >
                    Open Task
                </a>

            </section>

        @else

            <section class="time-my-no-active-timer">

                <div class="time-my-no-active-icon">
                    ▶
                </div>

                <div>
                    <strong>
                        No active work timer
                    </strong>

                    <p>
                        Open an assigned task and click Start Work
                        to begin tracking your time.
                    </p>
                </div>

                <a
                    href="{{ route('task.my') }}"
                    class="time-my-start-task-btn"
                >
                    Open My Tasks
                </a>

            </section>

        @endif

        {{-- Summary Cards --}}
        <section class="time-my-summary-grid">

            <article class="time-my-summary-card">

                <div class="time-my-summary-icon blue">
                    ◷
                </div>

                <div>
                    <span>
                        Today
                    </span>

                    <strong>
                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                            $todaySeconds
                        ) }}
                    </strong>

                    <small>
                        Time tracked today
                    </small>
                </div>

            </article>

            <article class="time-my-summary-card">

                <div class="time-my-summary-icon green">
                    ⏱
                </div>

                <div>
                    <span>
                        Total Tracked
                    </span>

                    <strong>
                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                            $totalSeconds
                        ) }}
                    </strong>

                    <small>
                        All recorded work time
                    </small>
                </div>

            </article>

            <article class="time-my-summary-card">

                <div class="time-my-summary-icon purple">
                    ▦
                </div>

                <div>
                    <span>
                        Total Sessions
                    </span>

                    <strong>
                        {{ $entries->total() }}
                    </strong>

                    <small>
                        Work sessions recorded
                    </small>
                </div>

            </article>

            <article class="time-my-summary-card">

                <div class="time-my-summary-icon orange">
                    ●
                </div>

                <div>
                    <span>
                        Current Timer
                    </span>

                    <strong
                        class="time-my-timer-state
                            {{ $activeEntry
                                ? 'active'
                                : 'inactive' }}"
                    >
                        {{ $activeEntry
                            ? ucfirst($activeEntry->status)
                            : 'Inactive' }}
                    </strong>

                    <small>
                        Current tracking status
                    </small>
                </div>

            </article>

        </section>

        {{-- Work History --}}
        <section class="time-my-history-section">

            <div class="time-my-section-heading">

                <div>
                    <h2>
                        Work History
                    </h2>

                    <p>
                        Your recent task sessions and tracked time.
                    </p>
                </div>

                <span class="time-my-record-count">
                    {{ $entries->total() }}
                    {{ $entries->total() === 1
                        ? 'Session'
                        : 'Sessions' }}
                </span>

            </div>

            <div class="time-my-table-wrapper">

                <table class="time-my-table">

                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Started</th>
                            <th>Ended</th>
                            <th>Status</th>
                            <th>Tracked Time</th>
                            <th>Notes</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($entries as $entry)

                            <tr>

                                {{-- Task --}}
                                <td>
                                    <div class="time-my-task-cell">

                                        <span class="time-my-task-icon">
                                            ✓
                                        </span>

                                        <div>

                                            @if($entry->task)

                                                <a
                                                    href="{{ route(
                                                        'task.show',
                                                        $entry->task->id
                                                    ) }}"
                                                >
                                                    {{ $entry->task->title }}
                                                </a>

                                                <small>
                                                    Task #{{ $entry->task->id }}
                                                </small>

                                            @else

                                                <strong>
                                                    Deleted Task
                                                </strong>

                                                <small>
                                                    Task unavailable
                                                </small>

                                            @endif

                                        </div>

                                    </div>
                                </td>

                                {{-- Project --}}
                                <td>
                                    <div class="time-my-project-cell">

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

                                {{-- Started --}}
                                <td>
                                    <div class="time-my-date-cell">

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

                                {{-- Ended --}}
                                <td>

                                    @if($entry->stopped_at)

                                        <div class="time-my-date-cell">

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

                                        <span class="time-my-still-active">
                                            Still Active
                                        </span>

                                    @endif

                                </td>

                                {{-- Status --}}
                                <td>
                                    <span
                                        class="time-my-status-badge
                                            status-{{ $entry->status }}"
                                    >
                                        @if($entry->status === 'running')
                                            <span>●</span>
                                        @endif

                                        {{ ucfirst($entry->status) }}
                                    </span>
                                </td>

                                {{-- Tracked Time --}}
                                <td>
                                    <span class="time-my-duration">
                                        {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                            $entry->liveSeconds()
                                        ) }}
                                    </span>
                                </td>

                                {{-- Notes --}}
                                <td>
                                    @if($entry->notes)

                                        <span
                                            class="time-my-note"
                                            title="{{ $entry->notes }}"
                                        >
                                            {{ \Illuminate\Support\Str::limit(
                                                $entry->notes,
                                                55
                                            ) }}
                                        </span>

                                    @else

                                        <span class="time-my-no-note">
                                            No notes
                                        </span>

                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7">

                                    <div class="time-my-empty-state">

                                        <div class="time-my-empty-icon">
                                            ⏱
                                        </div>

                                        <h3>
                                            No time entries recorded
                                        </h3>

                                        <p>
                                            Start work from one of your
                                            assigned tasks. Your sessions
                                            will appear here.
                                        </p>

                                        <a
                                            href="{{ route('task.my') }}"
                                            class="time-my-primary-btn"
                                        >
                                            Open My Tasks
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Simple Pagination --}}
            @if($entries->hasPages())

                <div class="time-my-pagination">

                    <div class="time-my-pagination-summary">

                        Showing

                        <strong>
                            {{ $entries->firstItem() }}
                        </strong>

                        to

                        <strong>
                            {{ $entries->lastItem() }}
                        </strong>

                        of

                        <strong>
                            {{ $entries->total() }}
                        </strong>

                        sessions

                    </div>

                    <div class="time-my-pagination-actions">

                        @if($entries->onFirstPage())

                            <span class="time-my-page-btn disabled">
                                Previous
                            </span>

                        @else

                            <a
                                href="{{ $entries->previousPageUrl() }}"
                                class="time-my-page-btn"
                            >
                                Previous
                            </a>

                        @endif

                        <span class="time-my-page-number">
                            Page {{ $entries->currentPage() }}
                            of {{ $entries->lastPage() }}
                        </span>

                        @if($entries->hasMorePages())

                            <a
                                href="{{ $entries->nextPageUrl() }}"
                                class="time-my-page-btn"
                            >
                                Next
                            </a>

                        @else

                            <span class="time-my-page-btn disabled">
                                Next
                            </span>

                        @endif

                    </div>

                </div>

            @endif

        </section>

    </div>

</div>

@endsection