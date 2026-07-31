@extends('admin::layouts.app')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/modules/setting.css') }}?v={{ time() }}"
    >
@endpush

@section('content')

<div class="content-card setting-page lead-option-page">

    <div class="page-card-header lead-option-page-header">
        <div>
            <h1>Lead Status &amp; Priority Settings</h1>
            <p>
               Manage lead forms, filters, Excel import/export, and dynamic reporting options here.
            </p>
        </div>

        <a href="{{ route('setting.index') }}" class="secondary-btn">
            Back to Settings
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <strong>Please fix these errors:</strong>

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
                <strong>Delete rules</strong>
                <p>
                    Every record will clearly show whether it can be deleted or is locked.
                </p>
            </div>
        </div>

        <div class="lead-option-rule-list">
            <span><i class="rule-dot system"></i>The core system cannot be deleted.</span>
            <span><i class="rule-dot default"></i>Default: Please select the second default option first.</span>
            <span><i class="rule-dot used"></i>In Use: Assigned to Existing Leads</span>
            <span><i class="rule-dot deletable"></i>Custom + Unused: delete allowed</span>
        </div>
    </section>

    {{-- ==================== LEAD STATUSES ==================== --}}
    <section class="lead-option-section">
        <div class="lead-option-section-header">
            <div class="lead-option-title-row">
                <span class="lead-option-section-icon">S</span>

                <div>
                    <h2>Lead Statuses</h2>
                    <p>
                       Control the status name, slug, color, order, and behavior.
                    </p>
                </div>
            </div>

            <span class="lead-option-count">
                {{ $leadStatuses->count() }}
                {{ $leadStatuses->count() === 1 ? 'Status' : 'Statuses' }}
            </span>
        </div>

        @if(auth()->user()->hasPermission('settings.update'))
            <details class="lead-option-add-panel">
                <summary>
                    <span>
                        <strong>+ Add New Status</strong>
                        <small>Create Custom Lead Statuses</small>
                    </span>
                    <i>⌄</i>
                </summary>

                <form
                    method="POST"
                    action="{{ route('setting.lead-statuses.store') }}"
                    class="lead-option-add-form"
                >
                    @csrf

                    <div class="lead-option-form-field">
                        <label for="new_status_name">Status Name</label>
                        <input
                            type="text"
                            id="new_status_name"
                            name="name"
                            placeholder="Example: Proposal Sent"
                            required
                        >
                    </div>

                    <div class="lead-option-form-field">
                        <label for="new_status_slug">Slug</label>
                        <input
                            type="text"
                            id="new_status_slug"
                            name="slug"
                            placeholder="proposal_sent"
                            pattern="[a-z0-9_]+"
                            required
                        >
                        <small>Lowercase letters, numbers & underscore only.</small>
                    </div>

                    <div class="lead-option-form-field compact">
                        <label for="new_status_color">Colour</label>
                        <input
                            type="color"
                            id="new_status_color"
                            name="color"
                            value="#2563EB"
                            required
                        >
                    </div>

                    <div class="lead-option-form-field compact">
                        <label for="new_status_order">Order</label>
                        <input
                            type="number"
                            id="new_status_order"
                            name="sort_order"
                            value="100"
                            min="0"
                            max="9999"
                            required
                        >
                    </div>

                    <div class="lead-option-check-grid">
                        <label class="lead-option-check">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>
                                <strong>Active</strong>
                                <small>In Dropdown Available</small>
                            </span>
                        </label>

                        <label class="lead-option-check">
                            <input type="checkbox" name="is_default" value="1">
                            <span>
                                <strong>Default</strong>
                                <small>New Lead Initial Status</small>
                            </span>
                        </label>

                        <label class="lead-option-check">
                            <input type="checkbox" name="is_closed" value="1">
                            <span>
                                <strong>Closed</strong>
                                <small>Follow-up clear</small>
                            </span>
                        </label>
                    </div>

                    <div class="lead-option-add-actions">
                        <button type="submit" class="lead-option-btn primary">
                            Add Status
                        </button>
                    </div>
                </form>
            </details>
        @endif

        <div class="lead-option-list">
            @forelse($leadStatuses as $leadStatus)
                @php
                    $statusCanDelete =
                        !$leadStatus->is_system
                        && !$leadStatus->is_default
                        && (int) $leadStatus->leads_count === 0;

                    if ($leadStatus->is_system) {
                        $statusDeleteReason =
                            'The core system status cannot be deleted.';
                    } elseif ($leadStatus->is_default) {
                        $statusDeleteReason =
                            'Please make another status the default before proceeding.';
                    } elseif ((int) $leadStatus->leads_count > 0) {
                        $statusDeleteReason =
                            'This status is being used by existing leads.';
                    } else {
                        $statusDeleteReason =
                            'The custom unused status can be deleted.';
                    }
                @endphp

                <article
                    class="lead-option-record
                        {{ $leadStatus->is_system ? 'is-system' : '' }}
                        {{ !$leadStatus->is_active ? 'is-inactive' : '' }}"
                >
                    <div class="lead-option-record-summary">
                        <div class="lead-option-record-identity">
                            <span
                                class="dynamic-lead-option-badge"
                                style="--lead-option-color: {{ $leadStatus->color }}"
                            >
                                {{ $leadStatus->name }}
                            </span>

                            <div class="lead-option-record-name">
                                <strong>{{ $leadStatus->slug }}</strong>
                                <small>Display order: {{ $leadStatus->sort_order }}</small>
                            </div>
                        </div>

                        <div class="lead-option-state-badges">
                            @if($leadStatus->is_system)
                                <span class="lead-option-state system">Protected</span>
                            @else
                                <span class="lead-option-state custom">Custom</span>
                            @endif

                            @if($leadStatus->is_default)
                                <span class="lead-option-state default">Default</span>
                            @endif

                            @if($leadStatus->is_closed)
                                <span class="lead-option-state closed">Closed</span>
                            @endif

                            @if(!$leadStatus->is_active)
                                <span class="lead-option-state inactive">Inactive</span>
                            @endif
                        </div>

                        <div class="lead-option-usage">
                            <span>Used by</span>
                            <strong>
                                {{ $leadStatus->leads_count }}
                                {{ (int) $leadStatus->leads_count === 1 ? 'Lead' : 'Leads' }}
                            </strong>
                        </div>

                        <div class="lead-option-delete-state">
                            @if($statusCanDelete)
                                <span class="can-delete">Delete allowed</span>
                            @else
                                <span class="cannot-delete">Delete locked</span>
                            @endif
                            <small>{{ $statusDeleteReason }}</small>
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
                                    'setting.lead-statuses.update',
                                    $leadStatus->id
                                ) }}"
                                class="lead-option-update-form"
                            >
                                @csrf
                                @method('PUT')

                                <div class="lead-option-fields">
                                    <div class="lead-option-form-field">
                                        <label for="status_name_{{ $leadStatus->id }}">
                                            Name
                                        </label>
                                        <input
                                            type="text"
                                            id="status_name_{{ $leadStatus->id }}"
                                            name="name"
                                            value="{{ $leadStatus->name }}"
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field">
                                        <label for="status_slug_{{ $leadStatus->id }}">
                                            Slug
                                        </label>
                                        <input
                                            type="text"
                                            id="status_slug_{{ $leadStatus->id }}"
                                            name="slug"
                                            value="{{ $leadStatus->slug }}"
                                            {{ $leadStatus->is_system ? 'readonly' : '' }}
                                            required
                                        >

                                        @if($leadStatus->is_system)
                                            <small>The core status slug cannot be changed.</small>
                                        @endif
                                    </div>

                                    <div class="lead-option-form-field compact">
                                        <label for="status_color_{{ $leadStatus->id }}">
                                            Colour
                                        </label>
                                        <input
                                            type="color"
                                            id="status_color_{{ $leadStatus->id }}"
                                            name="color"
                                            value="{{ $leadStatus->color }}"
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field compact">
                                        <label for="status_order_{{ $leadStatus->id }}">
                                            Order
                                        </label>
                                        <input
                                            type="number"
                                            id="status_order_{{ $leadStatus->id }}"
                                            name="sort_order"
                                            value="{{ $leadStatus->sort_order }}"
                                            min="0"
                                            max="9999"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="lead-option-check-grid">
                                    <label
                                        class="lead-option-check
                                            {{ $leadStatus->is_system ? 'is-disabled' : '' }}"
                                    >
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            @checked($leadStatus->is_active)
                                            @disabled($leadStatus->is_system)
                                        >
                                        <span>
                                            <strong>Active</strong>
                                            <small>The Inactive option will be hidden in new forms.</small>
                                        </span>
                                    </label>

                                    <label class="lead-option-check">
                                        <input
                                            type="checkbox"
                                            name="is_default"
                                            value="1"
                                            @checked($leadStatus->is_default)
                                        >
                                        <span>
                                            <strong>Default</strong>
                                            <small>New Lead  initial status</small>
                                        </span>
                                    </label>

                                    <label
                                        class="lead-option-check
                                            {{ $leadStatus->is_system ? 'is-disabled' : '' }}"
                                    >
                                        <input
                                            type="checkbox"
                                            name="is_closed"
                                            value="1"
                                            @checked($leadStatus->is_closed)
                                            @disabled($leadStatus->is_system)
                                        >
                                        <span>
                                            <strong>Closed</strong>
                                            <small>The follow-up will be cleared once it is closed.</small>
                                        </span>
                                    </label>
                                </div>

                                @if(auth()->user()->hasPermission('settings.update'))
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

                                @if(auth()->user()->hasPermission('settings.update'))
                                    @if($statusCanDelete)
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'setting.lead-statuses.destroy',
                                                $leadStatus->id
                                            ) }}"
                                            onsubmit="return confirm(
                                                'Delete this Lead status permanently?'
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
                                            title="{{ $statusDeleteReason }}"
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
                    No Lead statuses configured.
                </div>
            @endforelse
        </div>
    </section>

    {{-- ==================== LEAD PRIORITIES ==================== --}}
    <section class="lead-option-section">
        <div class="lead-option-section-header">
            <div class="lead-option-title-row">
                <span class="lead-option-section-icon priority">P</span>

                <div>
                    <h2>Lead Priorities</h2>
                    <p>
                        Manage lead urgency labels, colors, display order, and default priority.
                    </p>
                </div>
            </div>

            <span class="lead-option-count">
                {{ $leadPriorities->count() }}
                {{ $leadPriorities->count() === 1 ? 'Priority' : 'Priorities' }}
            </span>
        </div>

        @if(auth()->user()->hasPermission('settings.update'))
            <details class="lead-option-add-panel">
                <summary>
                    <span>
                        <strong>+ Add New Priority</strong>
                        <small>Create Custom Lead Priorities</small>
                    </span>
                    <i>⌄</i>
                </summary>

                <form
                    method="POST"
                    action="{{ route('setting.lead-priorities.store') }}"
                    class="lead-option-add-form"
                >
                    @csrf

                    <div class="lead-option-form-field">
                        <label for="new_priority_name">Priority Name</label>
                        <input
                            type="text"
                            id="new_priority_name"
                            name="name"
                            placeholder="Example: Critical"
                            required
                        >
                    </div>

                    <div class="lead-option-form-field">
                        <label for="new_priority_slug">Slug</label>
                        <input
                            type="text"
                            id="new_priority_slug"
                            name="slug"
                            placeholder="critical"
                            pattern="[a-z0-9_]+"
                            required
                        >
                    </div>

                    <div class="lead-option-form-field compact">
                        <label for="new_priority_color">Colour</label>
                        <input
                            type="color"
                            id="new_priority_color"
                            name="color"
                            value="#64748B"
                            required
                        >
                    </div>

                    <div class="lead-option-form-field compact">
                        <label for="new_priority_order">Order</label>
                        <input
                            type="number"
                            id="new_priority_order"
                            name="sort_order"
                            value="100"
                            min="0"
                            max="9999"
                            required
                        >
                    </div>

                    <div class="lead-option-check-grid priority">
                        <label class="lead-option-check">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>
                                <strong>Active</strong>
                                <small>Listed in the dropdown</small>
                            </span>
                        </label>

                        <label class="lead-option-check">
                            <input type="checkbox" name="is_default" value="1">
                            <span>
                                <strong>Default</strong>
                                <small>Initial Priority for New Leads</small>
                            </span>
                        </label>
                    </div>

                    <div class="lead-option-add-actions">
                        <button type="submit" class="lead-option-btn primary">
                            Add Priority
                        </button>
                    </div>
                </form>
            </details>
        @endif

        <div class="lead-option-list">
            @forelse($leadPriorities as $leadPriority)
                @php
                    $priorityCanDelete =
                        !$leadPriority->is_system
                        && !$leadPriority->is_default
                        && (int) $leadPriority->leads_count === 0;

                    if ($leadPriority->is_system) {
                        $priorityDeleteReason =
                            'The core system priority cannot be deleted.';
                    } elseif ($leadPriority->is_default) {
                        $priorityDeleteReason =
                            'Set another priority as the default first.';
                    } elseif ((int) $leadPriority->leads_count > 0) {
                        $priorityDeleteReason =
                            'This priority is in use by existing leads.';
                    } else {
                        $priorityDeleteReason =
                            'This custom unused priority can be deleted.';
                    }
                @endphp

                <article
                    class="lead-option-record
                        {{ $leadPriority->is_system ? 'is-system' : '' }}
                        {{ !$leadPriority->is_active ? 'is-inactive' : '' }}"
                >
                    <div class="lead-option-record-summary">
                        <div class="lead-option-record-identity">
                            <span
                                class="dynamic-lead-option-badge"
                                style="--lead-option-color: {{ $leadPriority->color }}"
                            >
                                {{ $leadPriority->name }}
                            </span>

                            <div class="lead-option-record-name">
                                <strong>{{ $leadPriority->slug }}</strong>
                                <small>Display order: {{ $leadPriority->sort_order }}</small>
                            </div>
                        </div>

                        <div class="lead-option-state-badges">
                            @if($leadPriority->is_system)
                                <span class="lead-option-state system">Protected</span>
                            @else
                                <span class="lead-option-state custom">Custom</span>
                            @endif

                            @if($leadPriority->is_default)
                                <span class="lead-option-state default">Default</span>
                            @endif

                            @if(!$leadPriority->is_active)
                                <span class="lead-option-state inactive">Inactive</span>
                            @endif
                        </div>

                        <div class="lead-option-usage">
                            <span>Used by</span>
                            <strong>
                                {{ $leadPriority->leads_count }}
                                {{ (int) $leadPriority->leads_count === 1 ? 'Lead' : 'Leads' }}
                            </strong>
                        </div>

                        <div class="lead-option-delete-state">
                            @if($priorityCanDelete)
                                <span class="can-delete">Delete allowed</span>
                            @else
                                <span class="cannot-delete">Delete locked</span>
                            @endif
                            <small>{{ $priorityDeleteReason }}</small>
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
                                    'setting.lead-priorities.update',
                                    $leadPriority->id
                                ) }}"
                                class="lead-option-update-form"
                            >
                                @csrf
                                @method('PUT')

                                <div class="lead-option-fields">
                                    <div class="lead-option-form-field">
                                        <label for="priority_name_{{ $leadPriority->id }}">
                                            Name
                                        </label>
                                        <input
                                            type="text"
                                            id="priority_name_{{ $leadPriority->id }}"
                                            name="name"
                                            value="{{ $leadPriority->name }}"
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field">
                                        <label for="priority_slug_{{ $leadPriority->id }}">
                                            Slug
                                        </label>
                                        <input
                                            type="text"
                                            id="priority_slug_{{ $leadPriority->id }}"
                                            name="slug"
                                            value="{{ $leadPriority->slug }}"
                                            {{ $leadPriority->is_system ? 'readonly' : '' }}
                                            required
                                        >

                                        @if($leadPriority->is_system)
                                            <small>The core priority slug cannot be changed.</small>
                                        @endif
                                    </div>

                                    <div class="lead-option-form-field compact">
                                        <label for="priority_color_{{ $leadPriority->id }}">
                                            Colour
                                        </label>
                                        <input
                                            type="color"
                                            id="priority_color_{{ $leadPriority->id }}"
                                            name="color"
                                            value="{{ $leadPriority->color }}"
                                            required
                                        >
                                    </div>

                                    <div class="lead-option-form-field compact">
                                        <label for="priority_order_{{ $leadPriority->id }}">
                                            Order
                                        </label>
                                        <input
                                            type="number"
                                            id="priority_order_{{ $leadPriority->id }}"
                                            name="sort_order"
                                            value="{{ $leadPriority->sort_order }}"
                                            min="0"
                                            max="9999"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="lead-option-check-grid priority">
                                    <label
                                        class="lead-option-check
                                            {{ $leadPriority->is_system ? 'is-disabled' : '' }}"
                                    >
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            @checked($leadPriority->is_active)
                                            @disabled($leadPriority->is_system)
                                        >
                                        <span>
                                            <strong>Active</strong>
                                            <small>The inactive option will be hidden in new forms.</small>
                                        </span>
                                    </label>

                                    <label class="lead-option-check">
                                        <input
                                            type="checkbox"
                                            name="is_default"
                                            value="1"
                                            @checked($leadPriority->is_default)
                                        >
                                        <span>
                                            <strong>Default</strong>
                                            <small>Initial Priority for New Leads</small>
                                        </span>
                                    </label>
                                </div>

                                @if(auth()->user()->hasPermission('settings.update'))
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
                                    <strong>Delete priority</strong>
                                    <small>{{ $priorityDeleteReason }}</small>
                                </div>

                                @if(auth()->user()->hasPermission('settings.update'))
                                    @if($priorityCanDelete)
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'setting.lead-priorities.destroy',
                                                $leadPriority->id
                                            ) }}"
                                            onsubmit="return confirm(
                                                'Delete this Lead priority permanently?'
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
                                            title="{{ $priorityDeleteReason }}"
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
                    No Lead priorities configured.
                </div>
            @endforelse
        </div>
    </section>

</div>

@endsection