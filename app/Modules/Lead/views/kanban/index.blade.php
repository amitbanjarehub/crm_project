@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset(
        'css/modules/user.css'
    ) }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset(
        'css/modules/lead.css'
    ) }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset(
        'css/modules/lead-kanban.css'
    ) }}?v={{ time() }}">
@endpush

@push('scripts')
    <script src="{{ asset(
        'js/modules/lead-kanban.js'
    ) }}?v={{ time() }}" defer></script>
@endpush

@section('content')

    <div class="lead-kanban-page" id="leadKanbanApp" data-board-url="{{ route(
        'lead.kanban.board'
    ) }}" data-details-url-template="{{ route(
        'lead.kanban.details',
        [
            'lead' => '__LEAD__',
        ]
    ) }}" data-move-url-template="{{ route(
        'lead.kanban.move',
        [
            'lead' => '__LEAD__',
        ]
    ) }}" data-column-order-url="{{ route(
        'lead.kanban.column-order'
    ) }}" data-preference-url="{{ route(
        'lead.kanban.preference'
    ) }}" data-can-edit="{{ auth()
        ->user()
        ->hasPermission('leads.edit')
        ? '1'
        : '0' }}">

        <div class="content-card">

            <div class="page-card-header lead-kanban-header">

                <div>
                    <h1>Lead Kanban Board</h1>

                    <p>
                        Drag Leads between columns and manage the complete Lead workflow.
                    </p>
                </div>

                <div class="lead-kanban-header-actions">

                    <a href="{{ route(
        'lead.index'
    ) }}" class="secondary-btn">
                        Table View
                    </a>

                    @if(
                                        auth()->user()->hasPermission(
                                            'leads.create'
                                        )
                                    )
                                    <!-- <a
                                            href="{{ route(
                                                'lead.create'
                                            ) }}"
                                            class="primary-btn"
                                        >
                                            + Add Lead
                                        </a> -->
                                    <a href="{{ route(
                            'lead.create'
                        ) }}" class="primary-btn js-kanban-return-link">
                                        + Add Lead
                                    </a>
                    @endif

                </div>

            </div>

            <form class="lead-kanban-toolbar" id="leadKanbanFilterForm">

                <div class="kanban-toolbar-field group-field">

                    <label for="kanbanGroupBy">
                        Group Columns By
                    </label>

                    <select id="kanbanGroupBy" name="group_by">
                        <option value="status" @selected(
                            $groupBy
                            === 'status'
                        )>
                            Lead Status
                        </option>

                        <option value="priority" @selected(
                            $groupBy
                            === 'priority'
                        )>
                            Lead Priority
                        </option>
                    </select>

                </div>

                <div class="kanban-toolbar-field search-field">

                    <label for="kanbanSearch">
                        Search
                    </label>

                    <input type="text" id="kanbanSearch" name="search" value="{{ $filters[
        'search'
    ] }}" placeholder="Lead, phone, email, company or ID" autocomplete="off">

                </div>

                <div class="kanban-toolbar-field">

                    <label for="kanbanStatusFilter">
                        Status
                    </label>

                    <select id="kanbanStatusFilter" name="status">
                        <option value="">
                            All Statuses
                        </option>

                        @foreach(
                                $statuses
                                as $key => $label
                            )
                            <option value="{{ $key }}" @selected(
                                $filters[
                                    'status'
                                ] === $key
                            )>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div class="kanban-toolbar-field">

                    <label for="kanbanPriorityFilter">
                        Priority
                    </label>

                    <select id="kanbanPriorityFilter" name="priority">
                        <option value="">
                            All Priorities
                        </option>

                        @foreach(
                                $priorities
                                as $key => $label
                            )
                            <option value="{{ $key }}" @selected(
                                $filters[
                                    'priority'
                                ] === $key
                            )>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div class="kanban-toolbar-field">

                    <label for="kanbanSourceFilter">
                        Source
                    </label>

                    <select id="kanbanSourceFilter" name="source">
                        <option value="">
                            All Sources
                        </option>

                        @foreach(
                                $sources
                                as $key => $label
                            )
                            <option value="{{ $key }}" @selected(
                                $filters[
                                    'source'
                                ] === $key
                            )>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                </div>

                @if($canViewAll)

                    <div class="kanban-toolbar-field">

                        <label for="kanbanAssignedFilter">
                            Assigned To
                        </label>

                        <select id="kanbanAssignedFilter" name="assigned_to">
                            <option value="">
                                All Users
                            </option>

                            @foreach(
                                    $users
                                    as $user
                                )
                                <option value="{{ $user->id }}" @selected(
                                    (int) $filters[
                                        'assigned_to'
                                    ]
                                    ===
                                    (int) $user->id
                                )>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                @endif

                <div class="kanban-toolbar-options">

                    <label class="kanban-check-option">

                        <input type="checkbox" id="kanbanHideEmpty" @checked(
                            $preference
                                ->hide_empty_columns
                        )>

                        <span>
                            Hide empty columns
                        </span>

                    </label>

                </div>

                <div class="kanban-toolbar-actions">

                    <button type="button" class="secondary-btn" id="kanbanResetFilters">
                        Reset Filters
                    </button>

                    <button type="button" class="secondary-btn" id="kanbanUndoButton" disabled>
                        Undo Last Move
                    </button>

                    <button type="button" class="primary-btn" id="kanbanRefreshButton">
                        Refresh
                    </button>

                </div>

            </form>

            <div class="lead-kanban-statusbar">

                <div>
                    <span>
                        Total Leads:
                    </span>

                    <strong id="kanbanTotalLeads">
                        {{ $totalLeads }}
                    </strong>
                </div>

                <div>
                    <span>
                        Last updated:
                    </span>

                    <strong id="kanbanLastUpdated">
                        {{ now()->format(
        'h:i:s A'
    ) }}
                    </strong>
                </div>

                <div class="kanban-saving-state" id="kanbanSavingState">
                    Saved
                </div>

            </div>

            <div class="lead-kanban-board-container" id="leadKanbanBoardContainer">
                @include(
                    'lead::kanban.partials.board',
                    [
                        'columns' =>
                            $columns,

                        'groupBy' =>
                            $groupBy,

                        'totalLeads' =>
                            $totalLeads,
                    ]
                )
            </div>

        </div>

        <div class="kanban-drawer-overlay" id="kanbanDrawerOverlay" aria-hidden="true">
            <aside class="kanban-lead-drawer" id="kanbanLeadDrawer" aria-label="Lead details">

                <div class="kanban-drawer-loading">
                    Loading Lead details...
                </div>

            </aside>
        </div>

        <div class="kanban-toast-container" id="kanbanToastContainer" aria-live="polite"></div>

    </div>

@endsection