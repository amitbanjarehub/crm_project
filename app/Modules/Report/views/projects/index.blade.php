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

<div class="report-page project-report-index">

    <div class="report-page-header">

        <div>
            <h1>Project Reports</h1>

            <p>
                Analyze project health, progress,
                deadlines and tracked effort.
            </p>
        </div>

        @if(
    auth()->user()->hasPermission(
        'reports.executive.view'
    )
)
    <a
        href="{{ route('report.executive') }}"
        class="executive-dashboard-btn"
    >
        <span class="executive-dashboard-btn-icon">
            📊
        </span>

        <span>
            Executive Dashboard
        </span>

        <span class="executive-dashboard-btn-arrow">
            →
        </span>
    </a>
@endif

    </div>

    <div class="report-card-grid five">

        <div class="report-stat-card">
            <span>Total Projects</span>
            <strong>{{ $summary['total'] }}</strong>
        </div>

        <div class="report-stat-card">
            <span>Active</span>
            <strong>{{ $summary['active'] }}</strong>
        </div>

        <div class="report-stat-card success">
            <span>Completed</span>
            <strong>{{ $summary['completed'] }}</strong>
        </div>

        <div class="report-stat-card warning">
            <span>On Hold</span>
            <strong>{{ $summary['on_hold'] }}</strong>
        </div>

        <div class="report-stat-card danger">
            <span>Delayed</span>
            <strong>{{ $summary['delayed'] }}</strong>
        </div>

    </div>

    <form
        method="GET"
        action="{{ route(
            'report.projects.index'
        ) }}"
        class="report-project-filter"
    >

        <input
            type="text"
            name="search"
            value="{{ $search }}"
            placeholder="Search project, code or client..."
        >

        <select name="status">
            <option value="">All Statuses</option>

            @foreach($statuses as $key => $label)
                <option
                    value="{{ $key }}"
                    @selected(
                        $status === $key
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select name="priority">
            <option value="">All Priorities</option>

            @foreach($priorities as $key => $label)
                <option
                    value="{{ $key }}"
                    @selected(
                        $priority === $key
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select name="manager_id">
            <option value="">All Managers</option>

            @foreach($managers as $manager)
                <option
                    value="{{ $manager->id }}"
                    @selected(
                        $managerId
                        === $manager->id
                    )
                >
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>

        <select name="client_id">
            <option value="">All Clients</option>

            @foreach($clients as $client)
                <option
                    value="{{ $client->id }}"
                    @selected(
                        $clientId
                        === $client->id
                    )
                >
                    {{ $client->name }}
                </option>
            @endforeach
        </select>

        <select name="per_page">
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
                    {{ $size }} / page
                </option>
            @endforeach
        </select>

        <button
            type="submit"
            class="primary-btn"
        >
            Apply
        </button>

        <a
            href="{{ route(
                'report.projects.index'
            ) }}"
            class="secondary-btn"
        >
            Reset
        </a>

    </form>

    <section class="report-panel">

        <div class="table-wrapper">

            <table class="admin-table report-project-table">

                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Manager</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Tasks</th>
                        <th>Overdue</th>
                        <th>Estimated</th>
                        <th>Tracked</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse(
                        $projects as $project
                    )
                        <tr>

                            <td>
                                <strong>
                                    {{ $project->name }}
                                </strong>

                                <small>
                                    {{ $project->project_code }}
                                </small>
                            </td>

                            <td>
                                <strong>
                                    {{ $project->client?->name
                                        ?? '-' }}
                                </strong>

                                <small>
                                    {{ $project->client?->company }}
                                </small>
                            </td>

                            <td>
                                {{ $project->manager?->name
                                    ?? 'Unassigned' }}
                            </td>

                            <td>
                                <span
                                    class="report-status-badge status-{{ $project->status }}"
                                >
                                    {{ $statuses[
                                        $project->status
                                    ] }}
                                </span>
                            </td>

                            <td>
                                <div class="report-progress">

                                    <div>
                                        <span
                                            style="width: {{ $project->report_progress }}%"
                                        ></span>
                                    </div>

                                    <small>
                                        {{ $project->report_progress }}%
                                    </small>

                                </div>
                            </td>

                            <td>
                                {{ $project->completed_tasks }}
                                /
                                {{ $project->total_tasks }}
                            </td>

                            <td>
                                <span class="{{ $project->overdue_tasks > 0
                                    ? 'report-negative'
                                    : 'report-positive' }}">
                                    {{ $project->overdue_tasks }}
                                </span>
                            </td>

                            <td>
                                {{ number_format(
                                    (float) (
                                        $project->estimated_hours
                                        ?? 0
                                    ),
                                    2
                                ) }}
                                hrs
                            </td>

                            <td>
                                {{ \App\Modules\TimeTracking\Models\TimeEntry::formatSeconds(
                                    (int) (
                                        $project->tracked_seconds
                                        ?? 0
                                    )
                                ) }}
                            </td>

                            <td>
                                {{ $project->due_date
                                    ?->format('d M Y')
                                    ?? '-' }}
                            </td>

                            <td>
                                <a
                                    href="{{ route(
                                        'report.projects.show',
                                        $project->id
                                    ) }}"
                                    class="table-btn view"
                                >
                                    Open Report
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="11"
                                class="empty-table"
                            >
                                No project report found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($projects->hasPages())
            <div class="report-pagination">
                {{ $projects->links() }}
            </div>
        @endif

    </section>

</div>

@endsection