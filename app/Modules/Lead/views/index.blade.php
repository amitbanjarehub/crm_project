@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset('css/modules/lead.css') }}?v={{ time() }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/modules/lead.js') }}?v={{ time() }}" defer></script>
@endpush

@section('content')

    @php
        $hasActiveFilters =
            $search !== '' ||
            $status !== '' ||
            $priority !== '' ||
            $source !== '' ||
            $assignedTo > 0;

        $leadReturnUrl =
            request()->fullUrl();
    @endphp

    <div class="content-card">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- <div class="page-card-header">

              <div>
                  <h1>Lead Management</h1>

                  <p>
                      @if($canViewAll)
                          Manage and assign all business leads.
                      @else
                          View and manage leads assigned to you.
                      @endif
                  </p>
              </div>

              <div class="lead-header-actions">

            @if(
                auth()->user()->hasPermission(
                    'reports.leads.view'
                )
            )
                <a
                    href="{{ route(
                        'report.leads.index'
                    ) }}"
                    class="secondary-btn"
                >
                    📊 View Lead Report
                </a>
            @endif

            @if(
                auth()->user()->hasPermission(
                    'leads.create'
                )
            )
                <a
                    href="{{ route(
                        'lead.create'
                    ) }}"
                    class="primary-btn"
                >
                    + Add Lead
                </a>
            @endif

              </div>

            </div> -->

        <div class="page-card-header">

            <div>
                <h1>Lead Management</h1>

                <p>
                    @if($canViewAll)
                        Manage and assign all business leads.
                    @else
                        View and manage leads assigned to you.
                    @endif
                </p>
            </div>

            <div class="lead-header-actions">

                <a
                    href="{{ route(
                        'lead.kanban.index'
                    ) }}"
                    class="secondary-btn"
                >
                    Kanban View
                </a>

                @if(
                                auth()->user()->hasPermission(
                                    'leads.import'
                                )
                            )
                            <a href="{{ route(
                        'lead.import.form'
                    ) }}" class="secondary-btn">
                                Import Leads
                            </a>
                @endif

                @if(
                                auth()->user()->hasPermission(
                                    'leads.export'
                                )
                            )
                            <a href="{{ route(
                        'lead.export',
                        request()->except([
                            'page',
                            'per_page',
                        ])
                    ) }}" class="lead-export-btn" id="leadExportButton" data-export-url="{{ route(
                        'lead.export'
                    ) }}">
                                Export Excel
                            </a>
                @endif

                @if(
                                auth()->user()->hasPermission(
                                    'reports.leads.view'
                                )
                            )
                            <a href="{{ route(
                        'report.leads.index'
                    ) }}" class="secondary-btn">
                                View Lead Report
                            </a>
                @endif

                @if(
                                auth()->user()->hasPermission(
                                    'leads.create'
                                )
                            )
                            <a href="{{ route(
    'lead.create',
    [
        'return_url' =>
            $leadReturnUrl,
    ]
) }}" class="primary-btn">
                                + Add Lead
                            </a>
                @endif

            </div>

        </div>

        {{-- Live Search and Filters --}}
        <form action="{{ route('lead.index') }}" method="GET" class="lead-filter-form" id="leadFilterForm">
            <input type="hidden" name="per_page" id="leadPerPageInput" value="{{ $perPage }}">

            <div class="lead-search-box">
                <span class="lead-search-icon">🔍</span>

                <input type="text" name="search" id="leadSearchInput" value="{{ $search }}"
                    placeholder="Search name, phone, email, company or ID..." autocomplete="off">

                <button type="button" id="clearLeadSearch" class="lead-search-clear {{ $search === '' ? 'is-hidden' : '' }}"
                    aria-label="Clear search" title="Clear search">
                    ×
                </button>
            </div>

            <select name="status" class="lead-auto-filter">
                <option value="">All Statuses</option>

                @foreach ($statuses as $statusKey => $statusLabel)
                    <option value="{{ $statusKey }}" @selected($status === $statusKey)>
                        {{ $statusLabel }}
                    </option>
                @endforeach
            </select>

            <select name="priority" class="lead-auto-filter">
                <option value="">All Priorities</option>

                @foreach ($priorities as $priorityKey => $priorityLabel)
                    <option value="{{ $priorityKey }}" @selected($priority === $priorityKey)>
                        {{ $priorityLabel }}
                    </option>
                @endforeach
            </select>

            <select name="source" class="lead-auto-filter">
                <option value="">All Sources</option>

                @foreach ($sources as $sourceKey => $sourceLabel)
                    <option value="{{ $sourceKey }}" @selected($source === $sourceKey)>
                        {{ $sourceLabel }}
                    </option>
                @endforeach
            </select>

            @if ($canViewAll)
                <select name="assigned_to" class="lead-auto-filter">
                    <option value="">All Users</option>

                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($assignedTo === $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            @endif
        </form>

        {{-- AJAX me sirf ye section replace hoga --}}
        <div id="leadResultsArea" class="lead-results-area">
            <div class="table-wrapper">

                <table class="admin-table lead-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Lead</th>
                            <th>Contact</th>
                            <th>Company</th>
                            <th>Source</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Next Follow-up</th>
                            <th width="340">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($leads as $key => $lead)
                            <tr>
                                <td>
                                    {{ $leads->firstItem() + $key }}
                                </td>

                                <td>
                                    <div class="lead-name">
                                        <strong>
                                            {{ $lead->name }}
                                        </strong>

                                        <small>
                                            Lead #{{ $lead->id }}
                                        </small>
                                    </div>
                                </td>

                                <td>
                                    <div class="lead-contact">
                                        <span>
                                            {{ $lead->phone }}
                                        </span>

                                        <small>
                                            {{ $lead->email ?: 'No email' }}
                                        </small>
                                    </div>
                                </td>

                                <td>
                                    {{ $lead->company ?: '-' }}
                                </td>

                                <td>
                                    {{ $sources[$lead->source] ?? ucfirst($lead->source) }}
                                </td>

                                <td>
                                    @if ($lead->assignedUser)
                                        <div class="assigned-user">
                                            <strong>
                                                {{ $lead->assignedUser->name }}
                                            </strong>

                                            <small>
                                                {{ $lead->assignedUser->email }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="unassigned-badge">
                                            Unassigned
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @php
                                        /*
                                         * Lead priority ki colour aur display name
                                         * lead_priorities table se aayegi.
                                         *
                                         * Relation missing hone par safe fallback
                                         * colour aur formatted slug show hoga.
                                         */
                                        $priorityColor =
                                            $lead
                                                ->priorityDefinition
                                                    ?->color
                                            ?? '#64748B';

                                        $priorityLabel =
                                            $lead
                                                ->priorityDefinition
                                                    ?->name
                                            ?? ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $lead->priority
                                                )
                                            );
                                    @endphp

                                    <span class="dynamic-lead-option-badge" style="--lead-option-color: {{ $priorityColor }};">
                                        {{ $priorityLabel }}
                                    </span>
                                </td>

                                

                              <td>
    @php
        /*
         * Lead status ka colour aur display name
         * lead_statuses table se aayega.
         *
         * Relation missing hone par safe fallback
         * colour aur formatted slug show hoga.
         */
        $statusColor =
            $lead
                ->statusDefinition
                ?->color
            ?? '#64748B';

        $statusLabel =
            $lead
                ->statusDefinition
                ?->name
            ?? ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $lead->status
                )
            );
    @endphp

    @if($lead->isConverted())

        {{-- Converted Lead ka status manually editable nahi hoga --}}
        <span
            class="dynamic-lead-option-badge"
            style="--lead-option-color: {{ $statusColor }};"
        >
            {{ $statusLabel }}
        </span>

    @elseif(
        auth()->user()->hasPermission(
            'leads.edit'
        )
    )

        {{-- Editable Lead status dropdown --}}
        <form
            action="{{ route(
                'lead.status.update',
                $lead->id
            ) }}"
            method="POST"
            class="status-update-form"
        >
            @csrf
            <input
                type="hidden"
                name="return_url"
                value="{{ $leadReturnUrl }}"
            >
            @method('PATCH')

            <select
                name="status"
                class="status-select dynamic-status-select"
                style="--lead-option-color: {{ $statusColor }};"
                onchange="this.form.submit()"
            >
                @foreach(
                    $editableStatuses
                    as $statusKey => $statusLabelOption
                )
                    <option
                        value="{{ $statusKey }}"
                        @selected(
                            $lead->status
                                === $statusKey
                        )
                    >
                        {{ $statusLabelOption }}
                    </option>
                @endforeach
            </select>

        </form>

    @else

        {{-- Edit permission nahi hai to badge show hoga --}}
        <span
            class="dynamic-lead-option-badge"
            style="--lead-option-color: {{ $statusColor }};"
        >
            {{ $statusLabel }}
        </span>

    @endif
</td>

                                <td>
                                    @if ($lead->next_follow_up_at)
                                        <div class="follow-up-date">
                                            <strong>
                                                {{ $lead->next_follow_up_at->format('d M Y') }}
                                            </strong>

                                            <small>
                                                {{ $lead->next_follow_up_at->format('h:i A') }}
                                            </small>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- <td>
                                                    <div class="lead-actions">

                                                        @if (auth()->user()->hasPermission('leads.edit'))
                                                            <a href="{{ route('lead.edit', $lead->id) }}" class="table-btn edit">
                                                                Edit
                                                            </a>
                                                        @endif

                                                        @if (auth()->user()->hasPermission('leads.delete'))
                                                            <form action="{{ route('lead.destroy', $lead->id) }}" method="POST" class="delete-form"
                                                                onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit" class="table-btn delete">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        @endif

                                                    </div>
                                                </td> -->

                                <td>
                                    <div class="lead-actions">

                                        <!-- <a href="{{ route('lead.show', $lead->id) }}" class="table-btn view">
                                            View
                                        </a> -->

                                        <a
    href="{{ route(
        'lead.show',
        [
            'lead' =>
                $lead->id,

            'return_url' =>
                $leadReturnUrl,
        ]
    ) }}"
    class="table-btn view"
>
    View
</a>

                                        @if(
                                                !$lead->isConverted()
                                                && auth()->user()->hasPermission('follow_ups.create')
                                            )
                                            <!-- <a href="{{ route('followup.create', $lead->id) }}" class="table-btn followup">
                                                Follow-up
                                            </a> -->

                                            <a
    href="{{ route(
        'followup.create',
        [
            'lead' =>
                $lead->id,

            'return_url' =>
                $leadReturnUrl,
        ]
    ) }}"
    class="table-btn followup"
>
    Follow-up
</a>
                                        @endif

                                        @if(
                                                !$lead->isConverted()
                                                && auth()->user()->hasPermission('leads.convert')
                                            )
                                            <form action="{{ route('lead.convert', $lead->id) }}" method="POST"
                                                onsubmit="return confirm('Convert this lead into a client?');">
                                                @csrf
                                                <input
                                                    type="hidden"
                                                    name="return_url"
                                                    value="{{ $leadReturnUrl }}"
                                                >

                                                <button type="submit" class="table-btn convert">
                                                    Convert
                                                </button>
                                            </form>
                                        @endif

                                        @if(
                                                !$lead->isConverted()
                                                && auth()->user()->hasPermission('leads.edit')
                                            )
                                            <!-- <a href="{{ route('lead.edit', $lead->id) }}" class="table-btn edit">
                                                Edit
                                            </a> -->

                                            <a
    href="{{ route(
        'lead.edit',
        [
            'lead' =>
                $lead->id,

            'return_url' =>
                $leadReturnUrl,
        ]
    ) }}"
    class="table-btn edit"
>
    Edit
</a>
                                        @endif

                                        @if(
                                                !$lead->isConverted()
                                                && auth()->user()->hasPermission('leads.delete')
                                            )
                                            <form action="{{ route('lead.destroy', $lead->id) }}" method="POST" class="delete-form"
                                                onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                                @csrf
                                                <input
                                                    type="hidden"
                                                    name="return_url"
                                                    value="{{ $leadReturnUrl }}"
                                                >
                                                @method('DELETE')

                                                <button type="submit" class="table-btn delete">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="empty-table lead-empty-result">
                                    @if ($hasActiveFilters)
                                        <strong>
                                            No matching leads found.
                                        </strong>

                                        @if ($search !== '')
                                            <span>
                                                No result found for
                                                “{{ $search }}”.
                                            </span>
                                        @else
                                            <span>
                                                No lead matches the selected filters.
                                            </span>
                                        @endif
                                    @else
                                        <strong>
                                            No leads available.
                                        </strong>

                                        <span>
                                            Add your first lead to get started.
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            {{-- Pagination and Rows Per Page --}}
            <div class="custom-pagination-wrapper">

                <div class="pagination-summary">
                    @if ($leads->total() > 0)
                        Showing
                        <strong>{{ $leads->firstItem() }}</strong>
                        to
                        <strong>{{ $leads->lastItem() }}</strong>
                        of
                        <strong>{{ $leads->total() }}</strong>
                        leads
                    @else
                        0 leads found
                    @endif
                </div>

                <div class="lead-pagination-controls">

                    @if ($leads->hasPages())
                        <div class="custom-pagination">

                            @if ($leads->onFirstPage())
                                <span class="page-link disabled">
                                    Previous
                                </span>
                            @else
                                <a href="{{ $leads->previousPageUrl() }}" class="page-link">
                                    Previous
                                </a>
                            @endif

                            @php
                                $currentPage = $leads->currentPage();
                                $lastPage = $leads->lastPage();
                                $start = max($currentPage - 2, 1);
                                $end = min($currentPage + 2, $lastPage);
                            @endphp

                            @if ($start > 1)
                                <a href="{{ $leads->url(1) }}" class="page-number">
                                    1
                                </a>

                                @if ($start > 2)
                                    <span class="page-dots">
                                        ...
                                    </span>
                                @endif
                            @endif

                            @for ($page = $start; $page <= $end; $page++)
                                @if ($page === $currentPage)
                                    <span class="page-number active">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $leads->url($page) }}" class="page-number">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endfor

                            @if ($end < $lastPage)
                                @if ($end < $lastPage - 1)
                                    <span class="page-dots">
                                        ...
                                    </span>
                                @endif

                                <a href="{{ $leads->url($lastPage) }}" class="page-number">
                                    {{ $lastPage }}
                                </a>
                            @endif

                            @if ($leads->hasMorePages())
                                <a href="{{ $leads->nextPageUrl() }}" class="page-link">
                                    Next
                                </a>
                            @else
                                <span class="page-link disabled">
                                    Next
                                </span>
                            @endif

                        </div>
                    @endif

                    <div class="rows-per-page-box">
                        <label for="leadPerPageSelect">
                            Rows per page
                        </label>

                        <select id="leadPerPageSelect" class="per-page-select">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected($perPage === $size)>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection