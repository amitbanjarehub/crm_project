@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/project.css') }}?v={{ time() }}">
@endpush

@section('content')

<div class="content-card">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="page-card-header">
        <div>
            <h1>Project Management</h1>
            <p>
                @if($canViewAll)
                    Manage all client projects.
                @else
                    Projects managed by or assigned to you.
                @endif
            </p>
        </div>

        @if(auth()->user()->hasPermission('projects.create'))
            <a href="{{ route('project.create') }}" class="primary-btn">
                + Add Project
            </a>
        @endif
    </div>

    <form method="GET" class="project-filter-form">

        <input
            type="text"
            name="search"
            value="{{ $search }}"
            placeholder="Search code, project or client..."
        >

        <select name="status">
            <option value="">All Statuses</option>

            @foreach($statuses as $key => $label)
                <option value="{{ $key }}" @selected($status === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select name="priority">
            <option value="">All Priorities</option>

            @foreach($priorities as $key => $label)
                <option value="{{ $key }}" @selected($priority === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select name="client_id">
            <option value="">All Clients</option>

            @foreach($clients as $client)
                <option
                    value="{{ $client->id }}"
                    @selected($clientId === $client->id)
                >
                    {{ $client->name }}
                </option>
            @endforeach
        </select>

        @if($canViewAll)
            <select name="manager_id">
                <option value="">All Managers</option>

                @foreach($managers as $manager)
                    <option
                        value="{{ $manager->id }}"
                        @selected($managerId === $manager->id)
                    >
                        {{ $manager->name }}
                    </option>
                @endforeach
            </select>
        @endif

        <button type="submit" class="primary-btn">Apply</button>

        <a href="{{ route('project.index') }}" class="secondary-btn">
            Reset
        </a>
    </form>

    <div class="table-wrapper">
        <table class="admin-table project-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Manager</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($projects as $key => $project)
                    @php
                        $progress = $project->total_tasks > 0
                            ? (int) round(
                                ($project->completed_tasks /
                                $project->total_tasks) * 100
                            )
                            : 0;
                    @endphp

                    <tr>
                        <td>{{ $projects->firstItem() + $key }}</td>

                        <td>
                            <strong>{{ $project->name }}</strong>
                            <small>{{ $project->project_code }}</small>
                        </td>

                        <td>
                            <strong>{{ $project->client?->name }}</strong>
                            <small>{{ $project->client?->company }}</small>
                        </td>

                        <td>
                            {{ $project->manager?->name ?? 'Unassigned' }}
                        </td>

                        <td>
                            <span class="project-badge priority-{{ $project->priority }}">
                                {{ $priorities[$project->priority] }}
                            </span>
                        </td>

                        <td>
                            <span class="project-badge status-{{ $project->status }}">
                                {{ $statuses[$project->status] }}
                            </span>
                        </td>

                        <td>
                            <div class="project-progress">
                                <div>
                                    <span style="width: {{ $progress }}%"></span>
                                </div>
                                <small>{{ $progress }}%</small>
                            </div>
                        </td>

                        <td>
                            {{ $project->due_date
                                ? $project->due_date->format('d M Y')
                                : '-' }}
                        </td>

                        <td>
                            <div class="project-actions">
                                <a
                                    href="{{ route('project.show', $project->id) }}"
                                    class="table-btn view"
                                >
                                    View
                                </a>

                                @if(
                                    auth()->user()->hasPermission('projects.edit')
                                    && !$project->isClosed()
                                )
                                    <a
                                        href="{{ route('project.edit', $project->id) }}"
                                        class="table-btn edit"
                                    >
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-table">
                            No projects found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($projects->hasPages())
        <div class="simple-pagination">
            {{ $projects->links() }}
        </div>
    @endif

</div>

@endsection