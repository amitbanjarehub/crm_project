@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/followup.css') }}?v={{ time() }}">
@endpush

@section('content')

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
               <h1>Follow-up Management</h1>

               <p>
                   Track completed, upcoming and overdue follow-ups.
               </p>
           </div>

            @if(
                auth()->user()->hasPermission(
                    'reports.followups.view'
                )
            )
                <a
                    href="{{ route(
                        'report.followups.index'
                    ) }}"
                    class="secondary-btn"
                >
                    View Follow-up Report
                </a>
            @endif

        </div> -->

        <div class="page-card-header">

            <div>
                <h1>Follow-up Management</h1>

                <p>
                    Track completed, upcoming and
                    overdue follow-ups.
                </p>
            </div>

            <div class="followup-header-actions">

                @if(
                                auth()->user()->hasPermission(
                                    'follow_ups.import'
                                )
                            )
                            <a href="{{ route(
                        'followup.import.form'
                    ) }}" class="secondary-btn">
                                Import Follow-ups
                            </a>
                @endif

                @if(
                                auth()->user()->hasPermission(
                                    'follow_ups.export'
                                )
                            )
                            <a href="{{ route(
                        'followup.export',
                        request()->except([
                            'page',
                            'per_page',
                        ])
                    ) }}" class="followup-export-btn">
                                Export Excel
                            </a>
                @endif

                @if(
                                auth()->user()->hasPermission(
                                    'reports.followups.view'
                                )
                            )
                            <a href="{{ route(
                        'report.followups.index'
                    ) }}" class="secondary-btn">
                                View Follow-up Report
                            </a>
                @endif

            </div>

        </div>

        <form method="GET" action="{{ route('followup.index') }}" class="followup-filter-form">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search lead, phone, company or ID">

            <select name="due">
                <option value="all" @selected($due === 'all')>
                    All Follow-ups
                </option>
                <option value="today" @selected($due === 'today')>
                    Due Today
                </option>
                <option value="overdue" @selected($due === 'overdue')>
                    Overdue
                </option>
                <option value="upcoming" @selected($due === 'upcoming')>
                    Upcoming
                </option>
                <option value="no_schedule" @selected($due === 'no_schedule')>
                    No Next Schedule
                </option>
            </select>

            <select name="type">
                <option value="">All Types</option>

                @foreach($types as $key => $label)
                    <option value="{{ $key }}" @selected($type === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @if ($canViewAll)
                <select name="assigned_to">
                    <option value="">All Assigned Users</option>

                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($assignedTo === $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="per_page">
                @foreach([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($perPage === $size)>
                        {{ $size }} / page
                    </option>
                @endforeach
            </select>

            <button type="submit" class="primary-btn">
                Apply
            </button>

            <a href="{{ route('followup.index') }}" class="secondary-btn">
                Reset
            </a>
        </form>

        <div class="table-wrapper">
            <table class="admin-table followup-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lead</th>
                        <th>Type</th>
                        <th>Followed Up At</th>
                        <th>Outcome</th>
                        <th>Next Follow-up</th>
                        <th>Added By</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($followUps as $key => $followUp)
                        @php
                            $canModifyRecord =
                                auth()->user()->isSuperAdmin()
                                || auth()->user()->hasPermission('follow_ups.view_all')
                                || (int) $followUp->user_id === (int) auth()->id();
                        @endphp

                        <tr>
                            <td>{{ $followUps->firstItem() + $key }}</td>

                            <td>
                                <a href="{{ route('lead.show', $followUp->lead_id) }}">
                                    <strong>{{ $followUp->lead->name }}</strong>
                                </a>
                                <small>{{ $followUp->lead->phone }}</small>
                            </td>

                            <td>
                                {{ $types[$followUp->type] ?? ucfirst($followUp->type) }}
                            </td>

                            <td>
                                {{ $followUp->followed_up_at->format('d M Y, h:i A') }}
                            </td>

                            <td>
                                {{ $outcomes[$followUp->outcome] ?? ucfirst($followUp->outcome) }}
                            </td>

                            <td>
                                @if($followUp->next_follow_up_at)
                                    {{ $followUp->next_follow_up_at->format('d M Y, h:i A') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                {{ $followUp->user?->name ?? 'Deleted User' }}
                            </td>

                            <td>
                                <div class="followup-actions">

                                    <a href="{{ route('lead.show', $followUp->lead_id) }}" class="table-btn view">
                                        View
                                    </a>

                                    @if(
                                            $canModifyRecord
                                            && auth()->user()->hasPermission('follow_ups.edit')
                                        )
                                        <a href="{{ route('followup.edit', $followUp->id) }}" class="table-btn edit">
                                            Edit
                                        </a>
                                    @endif

                                    @if(
                                            $canModifyRecord
                                            && auth()->user()->hasPermission('follow_ups.delete')
                                        )
                                        <form method="POST" action="{{ route('followup.destroy', $followUp->id) }}"
                                            onsubmit="return confirm('Delete this follow-up?');">
                                            @csrf
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
                            <td colspan="8" class="empty-table">
                                No follow-ups found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($followUps->hasPages())
            <div class="simple-pagination">
                @if($followUps->onFirstPage())
                    <span class="page-link disabled">Previous</span>
                @else
                    <a href="{{ $followUps->previousPageUrl() }}" class="page-link">
                        Previous
                    </a>
                @endif

                <span>
                    Page {{ $followUps->currentPage() }}
                    of {{ $followUps->lastPage() }}
                </span>

                @if($followUps->hasMorePages())
                    <a href="{{ $followUps->nextPageUrl() }}" class="page-link">
                        Next
                    </a>
                @else
                    <span class="page-link disabled">Next</span>
                @endif
            </div>
        @endif

    </div>

@endsection