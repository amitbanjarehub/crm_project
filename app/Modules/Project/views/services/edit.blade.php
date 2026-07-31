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
@endpush

@section('content')

@php
    $hasTasks = $projectService
        ->tasks()
        ->exists();
@endphp

<div class="content-card">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-error">

            <strong>
                Please fix the following errors:
            </strong>

            <ul class="error-list">
                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>

        </div>
    @endif

    {{-- Page Header --}}
    <div class="page-card-header">

        <div>
            <h1>Edit Project Service</h1>

            <p>
                Update service details for
                <strong>
                    {{ $project->project_code }}
                </strong>
                —
                {{ $project->name }}
            </p>
        </div>

        <div class="project-actions">

            <a
                href="{{ route(
                    'project.show',
                    $project->id
                ) }}"
                class="secondary-btn"
            >
                Back to Project
            </a>

            @if(
                auth()->user()->hasPermission(
                    'project_services.delete'
                )
                && !$project->isClosed()
                && !$hasTasks
            )
                <form
                    method="POST"
                    action="{{ route(
                        'project.services.destroy',
                        [
                            $project->id,
                            $projectService->id
                        ]
                    ) }}"
                    onsubmit="return confirm(
                        'Are you sure you want to delete this service?'
                    );"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="danger-btn"
                    >
                        Delete Service
                    </button>
                </form>
            @endif

        </div>

    </div>

    {{-- Service Context --}}
    <div class="project-service-edit-context">

        <div>
            <span>Project</span>

            <strong>
                {{ $project->name }}
            </strong>

            <small>
                {{ $project->project_code }}
            </small>
        </div>

        <div>
            <span>Service</span>

            <strong>
                {{ $projectService->name }}
            </strong>

            <small>
                Service #{{ $projectService->id }}
            </small>
        </div>

        <div>
            <span>Tasks</span>

            <strong>
                {{ $projectService->tasks()->count() }}
            </strong>

            <small>
                Total service tasks
            </small>
        </div>

    </div>

    @if($hasTasks)
        <div class="project-service-delete-warning">

            <span>⚠️</span>

            <div>
                <strong>
                    This service contains tasks
                </strong>

                <p>
                    Service delete karne se pehle is service
                    ki saari tasks delete ya move karein.
                </p>
            </div>

        </div>
    @endif

    {{-- Edit Form --}}
    <section class="project-service-create-section">

        <div class="project-section-header">

            <div>
                <h2>Service Information</h2>

                <p>
                    Update assignment, priority, status and schedule.
                </p>
            </div>

        </div>

        <form
            method="POST"
            action="{{ route(
                'project.services.update',
                [
                    $project->id,
                    $projectService->id
                ]
            ) }}"
            class="project-service-create-form"
        >
            @csrf
            @method('PUT')

            {{-- Service Name --}}
            <div class="project-service-field full-width">

                <label for="service_name">
                    Service Name
                    <span>*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    id="service_name"
                    value="{{ old(
                        'name',
                        $projectService->name
                    ) }}"
                    maxlength="255"
                    placeholder="Enter service name"
                    required
                >

                @error('name')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- Assigned To --}}
            <div class="project-service-field">

                <label for="service_assigned_to">
                    Assigned To
                </label>

                <select
                    name="assigned_to"
                    id="service_assigned_to"
                >
                    <option value="">
                        Unassigned
                    </option>

                    @foreach($project->members as $member)

                        <option
                            value="{{ $member->id }}"
                            @selected(
                                (string) old(
                                    'assigned_to',
                                    $projectService->assigned_to
                                )
                                === (string) $member->id
                            )
                        >
                            {{ $member->name }}

                            @if($member->pivot?->member_role)
                                —
                                {{ $member->pivot->member_role }}
                            @endif
                        </option>

                    @endforeach
                </select>

                <small class="field-help">
                    Only Project Manager or Project Member
                    can be assigned.
                </small>

                @error('assigned_to')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- Priority --}}
            <div class="project-service-field">

                <label for="service_priority">
                    Priority
                    <span>*</span>
                </label>

                <select
                    name="priority"
                    id="service_priority"
                    required
                >
                    @foreach($priorities as $key => $label)

                        <option
                            value="{{ $key }}"
                            @selected(
                                old(
                                    'priority',
                                    $projectService->priority
                                ) === $key
                            )
                        >
                            {{ $label }}
                        </option>

                    @endforeach
                </select>

                @error('priority')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- Status --}}
            <div class="project-service-field">

                <label for="service_status">
                    Status
                    <span>*</span>
                </label>

                <select
                    name="status"
                    id="service_status"
                    required
                >
                    @foreach($statuses as $key => $label)

                        <option
                            value="{{ $key }}"
                            @selected(
                                old(
                                    'status',
                                    $projectService->status
                                ) === $key
                            )
                        >
                            {{ $label }}
                        </option>

                    @endforeach
                </select>

                @error('status')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- Sort Order --}}
            <div class="project-service-field">

                <label for="service_sort_order">
                    Sort Order
                </label>

                <input
                    type="number"
                    name="sort_order"
                    id="service_sort_order"
                    value="{{ old(
                        'sort_order',
                        $projectService->sort_order
                    ) }}"
                    min="0"
                    step="1"
                >

                <small class="field-help">
                    Lower number wali service pehle show hogi.
                </small>

                @error('sort_order')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- Start Date --}}
            <div class="project-service-field">

                <label for="service_start_date">
                    Start Date
                </label>

                <input
                    type="date"
                    name="start_date"
                    id="service_start_date"
                    value="{{ old(
                        'start_date',
                        $projectService->start_date
                            ? $projectService
                                ->start_date
                                ->format('Y-m-d')
                            : ''
                    ) }}"
                    @if($project->start_date)
                        min="{{ $project
                            ->start_date
                            ->format('Y-m-d') }}"
                    @endif
                    @if($project->due_date)
                        max="{{ $project
                            ->due_date
                            ->format('Y-m-d') }}"
                    @endif
                >

                @error('start_date')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- Due Date --}}
            <div class="project-service-field">

                <label for="service_due_date">
                    Due Date
                </label>

                <input
                    type="date"
                    name="due_date"
                    id="service_due_date"
                    value="{{ old(
                        'due_date',
                        $projectService->due_date
                            ? $projectService
                                ->due_date
                                ->format('Y-m-d')
                            : ''
                    ) }}"
                    @if($project->start_date)
                        min="{{ $project
                            ->start_date
                            ->format('Y-m-d') }}"
                    @endif
                    @if($project->due_date)
                        max="{{ $project
                            ->due_date
                            ->format('Y-m-d') }}"
                    @endif
                >

                @error('due_date')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- Description --}}
            <div class="project-service-field full-width">

                <label for="service_description">
                    Service Description
                </label>

                <textarea
                    name="description"
                    id="service_description"
                    rows="6"
                    maxlength="5000"
                    placeholder="Enter service requirements and scope"
                >{{ old(
                    'description',
                    $projectService->description
                ) }}</textarea>

                @error('description')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- Form Actions --}}
            <div class="project-service-actions full-width">

                <a
                    href="{{ route(
                        'project.show',
                        $project->id
                    ) }}"
                    class="secondary-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="primary-btn"
                >
                    Update Service
                </button>

            </div>

        </form>

    </section>

</div>

@endsection