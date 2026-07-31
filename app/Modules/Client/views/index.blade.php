@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/client.css') }}?v={{ time() }}">
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
                <h1>Client Management</h1>
                <p>Manage converted and manually added clients.</p>
            </div>

            @if(auth()->user()->hasPermission('clients.create'))
                <a href="{{ route('client.create') }}" class="primary-btn">
                    + Add Client
                </a>
            @endif
        </div> -->

        <div class="page-card-header">

    <div>
        <h1>Client Management</h1>

        <p>
            Manage converted and manually added clients.
        </p>
    </div>

    <div class="client-header-actions">

        @if(
            auth()->user()->hasPermission(
                'clients.import'
            )
        )
            <a
                href="{{ route(
                    'client.import.form'
                ) }}"
                class="secondary-btn"
            >
                Import Clients
            </a>
        @endif

        @if(
            auth()->user()->hasPermission(
                'clients.export'
            )
        )
            <a
                href="{{ route(
                    'client.export',
                    request()->except([
                        'page',
                        'per_page',
                    ])
                ) }}"
                class="client-export-btn"
            >
                Export Excel
            </a>
        @endif

        @if(
            auth()->user()->hasPermission(
                'clients.create'
            )
        )
            <a
                href="{{ route(
                    'client.create'
                ) }}"
                class="primary-btn"
            >
                + Add Client
            </a>
        @endif

    </div>

</div>

        <form method="GET" class="client-filter-form">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, phone, email, company or ID">

            <select name="status">
                <option value="">All Statuses</option>

                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @if($canViewAll)
                <select name="assigned_to">
                    <option value="">All Users</option>

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

            <button type="submit" class="primary-btn">Apply</button>

            <a href="{{ route('client.index') }}" class="secondary-btn">
                Reset
            </a>
        </form>

        <div class="table-wrapper">
            <table class="admin-table client-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Company</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($clients as $key => $client)
                                <tr>
                                    <td>{{ $clients->firstItem() + $key }}</td>

                                    <td>
                                        <strong>{{ $client->name }}</strong>
                                        <small>Client #{{ $client->id }}</small>
                                    </td>

                                    <td>
                                        {{ $client->phone }}
                                        <small>{{ $client->email ?: 'No email' }}</small>
                                    </td>

                                    <td>{{ $client->company ?: '-' }}</td>

                                    <td>
                                        {{ $client->assignedUser?->name ?? 'Unassigned' }}
                                    </td>

                                    <td>
                                        <span class="client-status {{ $client->status }}">
                                            {{ $statuses[$client->status] ?? ucfirst($client->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $client->lead_id
                        ? 'Converted Lead'
                        : 'Manual Client' }}
                                    </td>

                                    <td>
                                        <div class="client-actions">

                                            <a href="{{ route('client.show', $client->id) }}" class="table-btn view">
                                                View
                                            </a>

                                            @if(auth()->user()->hasPermission('clients.edit'))
                                                <a href="{{ route('client.edit', $client->id) }}" class="table-btn edit">
                                                    Edit
                                                </a>
                                            @endif

                                            <!-- @if(
                                                    !$client->lead_id
                                                    && auth()->user()->hasPermission('clients.delete')
                                                )
                                                    <form
                                                        method="POST"
                                                        action="{{ route('client.destroy', $client->id) }}"
                                                        onsubmit="return confirm('Delete this client?');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="table-btn delete">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif -->

                                            @if(
                                                                        auth()->user()->hasPermission('clients.delete')
                                                                        && (
                                                                            !$client->lead_id
                                                                            || auth()->user()->isSuperAdmin()
                                                                        )
                                                                    )
                                                                    <form method="POST" action="{{ route('client.destroy', $client->id) }}" onsubmit="return confirm(
                                                    '{{ $client->lead_id
                                                    ? 'This client was converted from a lead. Deleting it will restore the original lead. Continue?'
                                                    : 'Delete this client?' }}'
                                                );">
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
                                No clients found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
            <div class="simple-pagination">
                @if($clients->onFirstPage())
                    <span class="page-link disabled">Previous</span>
                @else
                    <a href="{{ $clients->previousPageUrl() }}" class="page-link">
                        Previous
                    </a>
                @endif

                <span>
                    Page {{ $clients->currentPage() }}
                    of {{ $clients->lastPage() }}
                </span>

                @if($clients->hasMorePages())
                    <a href="{{ $clients->nextPageUrl() }}" class="page-link">
                        Next
                    </a>
                @else
                    <span class="page-link disabled">Next</span>
                @endif
            </div>
        @endif

    </div>

@endsection