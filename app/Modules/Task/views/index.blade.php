@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset('css/modules/task.css') }}?v={{ time() }}">
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

        <!-- <div class="page-card-header">

            <div>
                <h1>Task Management</h1>

                <p>
                    View all tasks available according to your access.
                </p>
            </div>

            <div class="task-header-actions">

                <a
                    href="{{ route('task.my') }}"
                    class="secondary-btn"
                >
                    My Tasks
                </a>

                @if(auth()->user()->hasPermission('projects.view'))
                    <a
                        href="{{ route('project.index') }}"
                        class="primary-btn"
                    >
                        Open Projects
                    </a>
                @endif

            </div>

        </div> -->

        <div class="page-card-header">

            <div>
                <h1>Task Management</h1>

                <p>
                    View all tasks available according
                    to your access.
                </p>
            </div>

            <div class="task-header-actions">

                @if(
                                auth()->user()->hasPermission(
                                    'tasks.import'
                                )
                            )
                            <a href="{{ route(
                        'task.import.form'
                    ) }}" class="secondary-btn">
                                Import Tasks
                            </a>
                @endif

                @if(
                                auth()->user()->hasPermission(
                                    'tasks.export'
                                )
                            )
                            <a href="{{ route(
                        'task.export',
                        array_merge(
                            request()->except([
                                'page',
                                'per_page',
                            ]),
                            [
                                'scope' => 'all',
                            ]
                        )
                    ) }}" class="task-export-btn">
                                Export Excel
                            </a>
                @endif

                <a href="{{ route('task.my') }}" class="secondary-btn">
                    My Tasks
                </a>

                @if(
                                auth()->user()->hasPermission(
                                    'projects.view'
                                )
                            )
                            <a href="{{ route(
                        'project.index'
                    ) }}" class="primary-btn">
                                Open Projects
                            </a>
                @endif

            </div>

        </div>

        @include('task::partials.task-list', [
            'onlyMyTasks' => false
        ])

    </div>

@endsection