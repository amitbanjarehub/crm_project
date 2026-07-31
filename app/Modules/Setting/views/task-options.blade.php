@extends('admin::layouts.app')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset(
            'css/modules/setting.css'
        ) }}?v={{ time() }}"
    >
@endpush

@section('content')

<div class="content-card setting-page lead-option-page">

    <div class="page-card-header lead-option-page-header">

        <div>
            <h1>
                Task Status &amp; Priority Settings
            </h1>

            <p>
                Manage Task workflow statuses,
                priorities, colours and defaults.
            </p>
        </div>

        <a
            href="{{ route(
                'setting.index'
            ) }}"
            class="secondary-btn"
        >
            Back to Settings
        </a>

    </div>

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

    @if($errors->any())
        <div class="alert alert-error">

            <strong>
                Please fix these errors:
            </strong>

            <ul class="error-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    <section class="lead-option-help-card">

        <div class="lead-option-help-heading">

            <span>i</span>

            <div>
                <strong>Task workflow protection</strong>

                <p>
                    Core workflow statuses cannot be deleted,
                    deactivated or have their slugs changed.
                </p>
            </div>

        </div>

        <div class="lead-option-rule-list">

            <span>
                <i class="rule-dot system"></i>
                Core system status protected
            </span>

            <span>
                <i class="rule-dot default"></i>
                Default option direct delete nahi hoga
            </span>

            <span>
                <i class="rule-dot used"></i>
                Existing Tasks me used option locked
            </span>

            <span>
                <i class="rule-dot deletable"></i>
                Custom unused option delete allowed
            </span>

        </div>

    </section>

    {{-- Task Statuses --}}
    <section class="lead-option-section">

        <div class="lead-option-section-header">

            <div class="lead-option-title-row">

                <span class="lead-option-section-icon">
                    S
                </span>

                <div>
                    <h2>Task Statuses</h2>

                    <p>
                        Workflow status, colour, order
                        and behaviour manage karein.
                    </p>
                </div>

            </div>

            <span class="lead-option-count">
                {{ $taskStatuses->count() }}
                Statuses
            </span>

        </div>

        @if(
            auth()->user()->hasPermission(
                'settings.update'
            )
        )
            <details class="lead-option-add-panel">

                <summary>
                    <span>
                        <strong>+ Add New Status</strong>

                        <small>
                            Create custom Task status
                        </small>
                    </span>

                    <i>⌄</i>
                </summary>

                <form
                    method="POST"
                    action="{{ route(
                        'setting.task-statuses.store'
                    ) }}"
                    class="lead-option-add-form"
                >
                    @csrf

                    <div class="lead-option-form-field">

                        <label>Status Name</label>

                        <input
                            type="text"
                            name="name"
                            placeholder="Example: Client Approval"
                            required
                        >

                    </div>

                    <div class="lead-option-form-field">

                        <label>Slug</label>

                        <input
                            type="text"
                            name="slug"
                            placeholder="client_approval"
                            pattern="[a-z0-9_]+"
                            required
                        >

                    </div>

                    <div class="lead-option-form-field compact">

                        <label>Colour</label>

                        <input
                            type="color"
                            name="color"
                            value="#2563EB"
                            required
                        >

                    </div>

                    <div class="lead-option-form-field compact">

                        <label>Order</label>

                        <input
                            type="number"
                            name="sort_order"
                            value="100"
                            min="0"
                            max="9999"
                            required
                        >

                    </div>

                    <div class="lead-option-check-grid task-status-check-grid">

                        <label class="lead-option-check">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked
                            >

                            <span>
                                <strong>Active</strong>
                                <small>Dropdown me visible</small>
                            </span>
                        </label>

                        <label class="lead-option-check">
                            <input
                                type="checkbox"
                                name="is_default"
                                value="1"
                            >

                            <span>
                                <strong>Default</strong>
                                <small>New Task initial status</small>
                            </span>
                        </label>

                        <label class="lead-option-check">
                            <input
                                type="checkbox"
                                name="is_closed"
                                value="1"
                            >

                            <span>
                                <strong>Closed</strong>
                                <small>Task editing lock</small>
                            </span>
                        </label>

                        <label class="lead-option-check">
                            <input
                                type="checkbox"
                                name="is_manual_selectable"
                                value="1"
                                checked
                            >

                            <span>
                                <strong>Manual Selection</strong>
                                <small>Status dropdown me available</small>
                            </span>
                        </label>

                    </div>

                    <div class="lead-option-add-actions">

                        <button
                            type="submit"
                            class="lead-option-btn primary"
                        >
                            Add Status
                        </button>

                    </div>

                </form>

            </details>
        @endif

        <div class="lead-option-list">

            @forelse($taskStatuses as $taskStatus)

                @php
                    $statusCanDelete =
                        !$taskStatus->is_system
                        && !$taskStatus->is_default
                        && (int) $taskStatus->tasks_count === 0;

                    if ($taskStatus->is_system) {
                        $statusDeleteReason =
                            'Core system status cannot be deleted.';
                    } elseif ($taskStatus->is_default) {
                        $statusDeleteReason =
                            'Select another default status first.';
                    } elseif (
                        (int) $taskStatus->tasks_count > 0
                    ) {
                        $statusDeleteReason =
                            'Status is used by existing Tasks.';
                    } else {
                        $statusDeleteReason =
                            'Custom unused status can be deleted.';
                    }
                @endphp

                <article
                    class="lead-option-record
                        {{ $taskStatus->is_system
                            ? 'is-system'
                            : '' }}
                        {{ !$taskStatus->is_active
                            ? 'is-inactive'
                            : '' }}"
                >

                    <div class="lead-option-record-summary">

                        <div class="lead-option-record-identity">

                            <span
                                class="dynamic-lead-option-badge"
                                style="--lead-option-color:
                                    {{ $taskStatus->color }}"
                            >
                                {{ $taskStatus->name }}
                            </span>

                            <div class="lead-option-record-name">

                                <strong>
                                    {{ $taskStatus->slug }}
                                </strong>

                                <small>
                                    Order:
                                    {{ $taskStatus->sort_order }}
                                </small>

                            </div>

                        </div>

                        <div class="lead-option-state-badges">

                            @if($taskStatus->is_system)
                                <span class="lead-option-state system">
                                    Protected
                                </span>
                            @else
                                <span class="lead-option-state custom">
                                    Custom
                                </span>
                            @endif

                            @if($taskStatus->is_default)
                                <span class="lead-option-state default">
                                    Default
                                </span>
                            @endif

                            @if($taskStatus->is_closed)
                                <span class="lead-option-state closed">
                                    Closed
                                </span>
                            @endif

                            @if(!$taskStatus->is_manual_selectable)
                                <span class="lead-option-state inactive">
                                    Automatic
                                </span>
                            @endif

                            @if(!$taskStatus->is_active)
                                <span class="lead-option-state inactive">
                                    Inactive
                                </span>
                            @endif

                        </div>

                        <div class="lead-option-usage">

                            <span>Used by</span>

                            <strong>
                                {{ $taskStatus->tasks_count }}
                                Tasks
                            </strong>

                        </div>

                        <div class="lead-option-delete-state">

                            <span class="{{ $statusCanDelete
                                ? 'can-delete'
                                : 'cannot-delete' }}"
                            >
                                {{ $statusCanDelete
                                    ? 'Delete allowed'
                                    : 'Delete locked' }}
                            </span>

                            <small>
                                {{ $statusDeleteReason }}
                            </small>

                        </div>

                    </div>

                    <details class="lead-option-edit-panel">

                        <summary>
                            <span>Edit Status</span>
                            <i>⌄</i>
                        </summary>

                        <div class="lead-option-edit-content">

                            <form
                                method="POST"
                                action="{{ route(
                                    'setting.task-statuses.update',
                                    $taskStatus->id
                                ) }}"
                                class="lead-option-update-form"
                            >
                                @csrf
                                @method('PUT')

                                <div class="lead-option-fields">

                                    <div class="lead-option-form-field">
                                        <label>Name</label>

                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ $taskStatus->name }}"
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field">
                                        <label>Slug</label>

                                        <input
                                            type="text"
                                            name="slug"
                                            value="{{ $taskStatus->slug }}"
                                            {{ $taskStatus->is_system
                                                ? 'readonly'
                                                : '' }}
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field compact">
                                        <label>Colour</label>

                                        <input
                                            type="color"
                                            name="color"
                                            value="{{ $taskStatus->color }}"
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field compact">
                                        <label>Order</label>

                                        <input
                                            type="number"
                                            name="sort_order"
                                            value="{{ $taskStatus->sort_order }}"
                                            min="0"
                                            max="9999"
                                            required
                                        >
                                    </div>

                                </div>

                                <div class="lead-option-check-grid task-status-check-grid">

                                    <label class="lead-option-check">

                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            @checked($taskStatus->is_active)
                                            @disabled($taskStatus->is_system)
                                        >

                                        <span>
                                            <strong>Active</strong>
                                            <small>Dropdown availability</small>
                                        </span>

                                    </label>

                                    <label class="lead-option-check">

                                        <input
                                            type="checkbox"
                                            name="is_default"
                                            value="1"
                                            @checked($taskStatus->is_default)
                                        >

                                        <span>
                                            <strong>Default</strong>
                                            <small>New Task initial status</small>
                                        </span>

                                    </label>

                                    <label class="lead-option-check">

                                        <input
                                            type="checkbox"
                                            name="is_closed"
                                            value="1"
                                            @checked($taskStatus->is_closed)
                                            @disabled($taskStatus->is_system)
                                        >

                                        <span>
                                            <strong>Closed</strong>
                                            <small>Task editing lock</small>
                                        </span>

                                    </label>

                                    <label class="lead-option-check">

                                        <input
                                            type="checkbox"
                                            name="is_manual_selectable"
                                            value="1"
                                            @checked(
                                                $taskStatus
                                                    ->is_manual_selectable
                                            )
                                            @disabled(
                                                $taskStatus->is_system
                                            )
                                        >

                                        <span>
                                            <strong>Manual Selection</strong>
                                            <small>Status dropdown option</small>
                                        </span>

                                    </label>

                                </div>

                                @if(
                                    auth()->user()->hasPermission(
                                        'settings.update'
                                    )
                                )
                                    <div class="lead-option-edit-actions">

                                        <button
                                            type="submit"
                                            class="lead-option-btn primary"
                                        >
                                            Save Changes
                                        </button>

                                    </div>
                                @endif

                            </form>

                            <div class="lead-option-danger-zone">

                                <div>
                                    <strong>Delete status</strong>
                                    <small>{{ $statusDeleteReason }}</small>
                                </div>

                                @if(
                                    auth()->user()->hasPermission(
                                        'settings.update'
                                    )
                                )
                                    @if($statusCanDelete)

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'setting.task-statuses.destroy',
                                                $taskStatus->id
                                            ) }}"
                                            onsubmit="return confirm(
                                                'Delete this Task status permanently?'
                                            );"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="lead-option-btn danger"
                                            >
                                                Delete Status
                                            </button>

                                        </form>

                                    @else

                                        <button
                                            type="button"
                                            class="lead-option-btn danger disabled"
                                            disabled
                                        >
                                            Delete Locked
                                        </button>

                                    @endif
                                @endif

                            </div>

                        </div>

                    </details>

                </article>

            @empty

                <div class="lead-option-empty">
                    No Task statuses configured.
                </div>

            @endforelse

        </div>

    </section>

    {{-- Task Priorities --}}
    <section class="lead-option-section">

        <div class="lead-option-section-header">

            <div class="lead-option-title-row">

                <span class="lead-option-section-icon priority">
                    P
                </span>

                <div>
                    <h2>Task Priorities</h2>

                    <p>
                        Priority label, colour, order
                        and default value manage karein.
                    </p>
                </div>

            </div>

            <span class="lead-option-count">
                {{ $taskPriorities->count() }}
                Priorities
            </span>

        </div>

        @if(
            auth()->user()->hasPermission(
                'settings.update'
            )
        )
            <details class="lead-option-add-panel">

                <summary>
                    <span>
                        <strong>+ Add New Priority</strong>
                        <small>Create custom Task priority</small>
                    </span>

                    <i>⌄</i>
                </summary>

                <form
                    method="POST"
                    action="{{ route(
                        'setting.task-priorities.store'
                    ) }}"
                    class="lead-option-add-form"
                >
                    @csrf

                    <div class="lead-option-form-field">
                        <label>Priority Name</label>

                        <input
                            type="text"
                            name="name"
                            placeholder="Example: Critical"
                            required
                        >
                    </div>

                    <div class="lead-option-form-field">
                        <label>Slug</label>

                        <input
                            type="text"
                            name="slug"
                            placeholder="critical"
                            pattern="[a-z0-9_]+"
                            required
                        >
                    </div>

                    <div class="lead-option-form-field compact">
                        <label>Colour</label>

                        <input
                            type="color"
                            name="color"
                            value="#64748B"
                            required
                        >
                    </div>

                    <div class="lead-option-form-field compact">
                        <label>Order</label>

                        <input
                            type="number"
                            name="sort_order"
                            value="100"
                            min="0"
                            max="9999"
                            required
                        >
                    </div>

                    <div class="lead-option-check-grid priority">

                        <label class="lead-option-check">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked
                            >

                            <span>
                                <strong>Active</strong>
                                <small>Dropdown me available</small>
                            </span>
                        </label>

                        <label class="lead-option-check">
                            <input
                                type="checkbox"
                                name="is_default"
                                value="1"
                            >

                            <span>
                                <strong>Default</strong>
                                <small>New Task default priority</small>
                            </span>
                        </label>

                    </div>

                    <div class="lead-option-add-actions">

                        <button
                            type="submit"
                            class="lead-option-btn primary"
                        >
                            Add Priority
                        </button>

                    </div>

                </form>

            </details>
        @endif

        <div class="lead-option-list">

            @forelse($taskPriorities as $taskPriority)

                @php
                    $priorityCanDelete =
                        !$taskPriority->is_system
                        && !$taskPriority->is_default
                        && (int) $taskPriority->tasks_count === 0;

                    if ($taskPriority->is_system) {
                        $priorityDeleteReason =
                            'Core system priority cannot be deleted.';
                    } elseif ($taskPriority->is_default) {
                        $priorityDeleteReason =
                            'Select another default priority first.';
                    } elseif (
                        (int) $taskPriority->tasks_count > 0
                    ) {
                        $priorityDeleteReason =
                            'Priority is used by existing Tasks.';
                    } else {
                        $priorityDeleteReason =
                            'Custom unused priority can be deleted.';
                    }
                @endphp

                <article
                    class="lead-option-record
                        {{ $taskPriority->is_system
                            ? 'is-system'
                            : '' }}
                        {{ !$taskPriority->is_active
                            ? 'is-inactive'
                            : '' }}"
                >

                    <div class="lead-option-record-summary">

                        <div class="lead-option-record-identity">

                            <span
                                class="dynamic-lead-option-badge"
                                style="--lead-option-color:
                                    {{ $taskPriority->color }}"
                            >
                                {{ $taskPriority->name }}
                            </span>

                            <div class="lead-option-record-name">

                                <strong>
                                    {{ $taskPriority->slug }}
                                </strong>

                                <small>
                                    Order:
                                    {{ $taskPriority->sort_order }}
                                </small>

                            </div>

                        </div>

                        <div class="lead-option-state-badges">

                            @if($taskPriority->is_system)
                                <span class="lead-option-state system">
                                    Protected
                                </span>
                            @else
                                <span class="lead-option-state custom">
                                    Custom
                                </span>
                            @endif

                            @if($taskPriority->is_default)
                                <span class="lead-option-state default">
                                    Default
                                </span>
                            @endif

                            @if(!$taskPriority->is_active)
                                <span class="lead-option-state inactive">
                                    Inactive
                                </span>
                            @endif

                        </div>

                        <div class="lead-option-usage">

                            <span>Used by</span>

                            <strong>
                                {{ $taskPriority->tasks_count }}
                                Tasks
                            </strong>

                        </div>

                        <div class="lead-option-delete-state">

                            <span class="{{ $priorityCanDelete
                                ? 'can-delete'
                                : 'cannot-delete' }}"
                            >
                                {{ $priorityCanDelete
                                    ? 'Delete allowed'
                                    : 'Delete locked' }}
                            </span>

                            <small>
                                {{ $priorityDeleteReason }}
                            </small>

                        </div>

                    </div>

                    <details class="lead-option-edit-panel">

                        <summary>
                            <span>Edit Priority</span>
                            <i>⌄</i>
                        </summary>

                        <div class="lead-option-edit-content">

                            <form
                                method="POST"
                                action="{{ route(
                                    'setting.task-priorities.update',
                                    $taskPriority->id
                                ) }}"
                                class="lead-option-update-form"
                            >
                                @csrf
                                @method('PUT')

                                <div class="lead-option-fields">

                                    <div class="lead-option-form-field">
                                        <label>Name</label>

                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ $taskPriority->name }}"
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field">
                                        <label>Slug</label>

                                        <input
                                            type="text"
                                            name="slug"
                                            value="{{ $taskPriority->slug }}"
                                            {{ $taskPriority->is_system
                                                ? 'readonly'
                                                : '' }}
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field compact">
                                        <label>Colour</label>

                                        <input
                                            type="color"
                                            name="color"
                                            value="{{ $taskPriority->color }}"
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field compact">
                                        <label>Order</label>

                                        <input
                                            type="number"
                                            name="sort_order"
                                            value="{{ $taskPriority->sort_order }}"
                                            min="0"
                                            max="9999"
                                            required
                                        >
                                    </div>

                                </div>

                                <div class="lead-option-check-grid priority">

                                    <label class="lead-option-check">

                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            @checked($taskPriority->is_active)
                                            @disabled($taskPriority->is_system)
                                        >

                                        <span>
                                            <strong>Active</strong>
                                            <small>Dropdown availability</small>
                                        </span>

                                    </label>

                                    <label class="lead-option-check">

                                        <input
                                            type="checkbox"
                                            name="is_default"
                                            value="1"
                                            @checked($taskPriority->is_default)
                                        >

                                        <span>
                                            <strong>Default</strong>
                                            <small>New Task default priority</small>
                                        </span>

                                    </label>

                                </div>

                                <div class="lead-option-edit-actions">

                                    <button
                                        type="submit"
                                        class="lead-option-btn primary"
                                    >
                                        Save Changes
                                    </button>

                                </div>

                            </form>

                            <div class="lead-option-danger-zone">

                                <div>
                                    <strong>Delete priority</strong>
                                    <small>{{ $priorityDeleteReason }}</small>
                                </div>

                                @if($priorityCanDelete)

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'setting.task-priorities.destroy',
                                            $taskPriority->id
                                        ) }}"
                                        onsubmit="return confirm(
                                            'Delete this Task priority permanently?'
                                        );"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="lead-option-btn danger"
                                        >
                                            Delete Priority
                                        </button>

                                    </form>

                                @else

                                    <button
                                        type="button"
                                        class="lead-option-btn danger disabled"
                                        disabled
                                    >
                                        Delete Locked
                                    </button>

                                @endif

                            </div>

                        </div>

                    </details>

                </article>

            @empty

                <div class="lead-option-empty">
                    No Task priorities configured.
                </div>

            @endforelse

        </div>

    </section>

</div>

@endsection