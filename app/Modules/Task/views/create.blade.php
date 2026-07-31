@extends('admin::layouts.app')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/modules/user.css') }}?v={{ time() }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/modules/project.css') }}?v={{ time() }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/modules/task.css') }}?v={{ time() }}"
    >
@endpush

@section('content')

<div class="content-card">

    <div class="page-card-header">
        <div>
            <h1>Add Task</h1>

            <p>
                Create a task under
                <strong>{{ $projectService->name }}</strong>
                service.
            </p>
        </div>

        <a
            href="{{ route('project.show', $project->id) }}"
            class="secondary-btn"
        >
            Back to Project
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>
                Please fix the following errors:
            </strong>

            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('task::partials.form', [
        'task' => null,
        'project' => $project,
        'projectService' => $projectService,
        'users' => $users,
        'priorities' => $priorities,
    ])

</div>

@endsection