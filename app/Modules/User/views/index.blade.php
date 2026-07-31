@extends('admin::layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
@endpush
@push('scripts')
    <script src="{{ asset('js/modules/user.js') }}?v={{ time() }}" defer></script>
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
        <div class="page-card-header">
            <div>
                <h1>User Management</h1>
                <p>Manage all CRM users from here.</p>
            </div>

            <!-- <a href="{{ route('user.create') }}" class="primary-btn">
                                        + Add User
                                    </a> -->

            @if (auth()->user()->hasPermission('users.create'))
                <a href="{{ route('user.create') }}" class="primary-btn">
                    + Add User
                </a>
            @endif
        </div>

        <div class="user-table-toolbar">
            <form action="{{ route('user.index') }}" method="GET" class="search-form" id="userSearchForm">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div class="search-input-box">
                    <span class="search-icon">🔍</span>

                    <input type="text" name="search" id="userSearchInput" value="{{ $search ?? '' }}"
                        placeholder="Search by name, email or ID..." autocomplete="off">

                    @if (!empty($search))
                        <a href="{{ route('user.index', ['per_page' => $perPage]) }}" class="clear-search-btn">
                            ×
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Created Date</th>
                        <th>Status</th>
                        <th width="260">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $key => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $key }}</td>
                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                @if ($user->is_active)
                                    <span class="status-badge active">
                                        Active
                                    </span>
                                @else
                                    <span class="status-badge inactive">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <!-- <a href="{{ route('user.edit', $user->id) }}" class="table-btn edit">
                                                                            Edit
                                                                        </a> -->

                                @if (
                                                        auth()->user()->hasPermission('users.toggle_status') &&
                                                        auth()->id() !== $user->id
                                                    )

                                                    <form action="{{ route('user.status.update', $user->id) }}" method="POST" class="status-form"
                                                        onsubmit="return confirm(
                                        '{{ $user->is_active
                                        ? 'Are you sure you want to deactivate this user?'
                                        : 'Are you sure you want to activate this user?' }}'
                                    );">
                                                        @csrf
                                                        @method('PATCH')

                                                        <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">

                                                        <button type="submit" class="table-btn {{ $user->is_active ? 'deactivate' : 'activate' }}">
                                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                    </form>


                                @endif

                                @if (auth()->user()->hasPermission('users.edit'))
                                    <a href="{{ route('user.edit', $user->id) }}" class="table-btn edit">
                                        Edit
                                    </a>
                                @endif

                                <!-- <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="delete-form"
                                                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button type="submit" class="table-btn delete">
                                                                        Delete
                                                                    </button>
                                                                </form> -->

                                @if (auth()->user()->hasPermission('users.delete'))
                                    <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="delete-form"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="table-btn delete">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-table">
                                @if (!empty($search))
                                    No matching user found for "{{ $search }}".
                                @else
                                    No user data available.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="custom-pagination-wrapper">

                <div class="custom-pagination">

                    @if ($users->onFirstPage())
                        <span class="page-link disabled">Previous</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="page-link">Previous</a>
                    @endif

                    @php
                        $currentPage = $users->currentPage();
                        $lastPage = $users->lastPage();
                        $start = max($currentPage - 2, 1);
                        $end = min($currentPage + 2, $lastPage);
                    @endphp

                    @if ($start > 1)
                        <a href="{{ $users->url(1) }}" class="page-number">1</a>

                        @if ($start > 2)
                            <span class="page-dots">...</span>
                        @endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $currentPage)
                            <span class="page-number active">{{ $page }}</span>
                        @else
                            <a href="{{ $users->url($page) }}" class="page-number">{{ $page }}</a>
                        @endif
                    @endfor

                    @if ($end < $lastPage)
                        @if ($end < $lastPage - 1)
                            <span class="page-dots">...</span>
                        @endif

                        <a href="{{ $users->url($lastPage) }}" class="page-number">{{ $lastPage }}</a>
                    @endif

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="page-link">Next</a>
                    @else
                        <span class="page-link disabled">Next</span>
                    @endif

                </div>

                <div class="per-page-box">
                    <select id="perPageSelect" class="per-page-select">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / page</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / page</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / page</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / page</option>
                    </select>
                </div>

            </div>
        @endif

    </div>

@endsection