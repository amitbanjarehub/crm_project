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

        <div class="page-card-header">

            <div>
                <h1>My Tasks</h1>

                <p>
                    Tasks currently assigned to you.
                </p>
            </div>

            <div class="task-header-actions">

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
                                'scope' => 'my',
                            ]
                        )
                    ) }}" class="task-export-btn">
                                Export My Tasks
                            </a>
                @endif

                @if(
                                auth()->user()->hasPermission(
                                    'tasks.view_all'
                                )
                            )
                            <a href="{{ route(
                        'task.index'
                    ) }}" class="secondary-btn">
                                All Tasks
                            </a>
                @endif

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
            'onlyMyTasks' => true
        ])

    </div>

@endsection