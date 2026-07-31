@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/role.css') }}?v={{ time() }}">
@endpush

@section('content')

    @php
        $totalRoles = $roles->count();
        $totalAssignedUsers = $roles->sum('users_count');
    @endphp

    <div class="role-page">

        {{-- Success Message --}}
        @if (session('success'))
            <div class="role-alert role-alert-success">
                <span class="role-alert-icon">✓</span>

                <div>
                    <strong>Success</strong>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Error Message --}}
        @if (session('error'))
            <div class="role-alert role-alert-error">
                <span class="role-alert-icon">!</span>

                <div>
                    <strong>Error</strong>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="role-card">

            {{-- Page Header --}}
            <!-- <div class="role-card-header">

                <div class="role-heading">
                    <div class="role-heading-icon">
                        🛡️
                    </div>

                    <div>
                        <h1>Role Management</h1>
                        <p>
                            Manage CRM roles and control their access permissions.
                        </p>
                    </div>
                </div>

                <div class="role-summary">

                    <div class="role-summary-item">
                        <span>Total Roles</span>
                        <strong>{{ $totalRoles }}</strong>
                    </div>

                    <div class="role-summary-divider"></div>

                    <div class="role-summary-item">
                        <span>Assigned Users</span>
                        <strong>{{ $totalAssignedUsers }}</strong>
                    </div>

                </div>

            </div> -->

            {{-- Page Header --}}
            <div class="role-card-header">

                <div class="role-heading">
                    <div class="role-heading-icon">
                        🛡️
                    </div>

                    <div>
                        <h1>Role Management</h1>

                        <p>
                            Manage CRM roles and control their access permissions.
                        </p>
                    </div>
                </div>

                <div class="role-header-right">

                    <div class="role-summary">

                        <div class="role-summary-item">
                            <span>Total Roles</span>

                            <strong>
                                {{ $totalRoles }}
                            </strong>
                        </div>

                        <div class="role-summary-divider"></div>

                        <div class="role-summary-item">
                            <span>Assigned Users</span>

                            <strong>
                                {{ $totalAssignedUsers }}
                            </strong>
                        </div>

                    </div>

                    @if(auth()->user()->hasPermission('roles.create'))
                        <a href="{{ route('role.create') }}" class="role-create-btn">
                            <span>＋</span>
                            Add New Role
                        </a>
                    @endif

                </div>

            </div>

            {{-- Information Bar --}}
            <div class="role-info-bar">
                <span class="role-info-icon">ℹ</span>

                <p>
                    Click the <strong>Manage Permissions</strong> button to select which CRM modules a role can access.
                </p>
            </div>

            {{-- Role Table --}}
            <div class="role-table-wrapper">

                <table class="role-table">

                    <thead>
                        <tr>
                            <th class="role-serial-column">#</th>
                            <th>Role</th>
                            <th>Assigned Users</th>
                            <th>Permissions</th>
                            <th class="role-action-column">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($roles as $key => $role)

                            <tr>

                                {{-- Serial Number --}}
                                <td>
                                    <span class="role-serial">
                                        {{ $key + 1 }}
                                    </span>
                                </td>

                                {{-- Role Name --}}
                                <td>
                                    <div class="role-name-box">

                                        <div class="role-avatar">
                                            {{ strtoupper(substr($role->name, 0, 1)) }}
                                        </div>

                                        <div class="role-name-content">
                                            <div class="role-name-line">

                                                <strong>
                                                    {{ $role->name }}
                                                </strong>

                                                @if ($role->isAdminRole())
                                                    <span class="role-full-access-badge">
                                                        Full Access
                                                    </span>
                                                @endif

                                            </div>

                                            <small>
                                                @if ($role->isAdminRole())
                                                    Complete CRM access
                                                @else
                                                    Custom permission role
                                                @endif
                                            </small>
                                        </div>

                                    </div>
                                </td>

                                {{-- Assigned Users --}}
                                <td>
                                    <div class="role-count-box">
                                        <span class="role-count-icon">👤</span>

                                        <div>
                                            <strong>
                                                {{ $role->users_count }}
                                            </strong>

                                            <small>
                                                {{ $role->users_count == 1 ? 'User' : 'Users' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                {{-- Permission Count --}}
                                <td>
                                    @if ($role->isAdminRole())

                                        <span class="role-permission-status role-permission-all">
                                            <span>✓</span>
                                            All Permissions
                                        </span>

                                    @elseif ($role->permissions_count > 0)

                                        <span class="role-permission-status role-permission-assigned">
                                            <span>🔐</span>
                                            {{ $role->permissions_count }}
                                            {{ $role->permissions_count == 1 ? 'Permission' : 'Permissions' }}
                                        </span>

                                    @else

                                        <span class="role-permission-status role-permission-empty">
                                            <span>○</span>
                                            Not Assigned
                                        </span>

                                    @endif
                                </td>

                                {{-- Action --}}
                                <td>
                                    @if (auth()->user()->hasPermission('roles.manage_permissions'))

                                        <a href="{{ route('role.permissions.edit', $role->id) }}" class="role-permission-btn">
                                            <span class="role-button-icon">🔐</span>
                                            <span>Manage Permissions</span>
                                            <span class="role-button-arrow">→</span>
                                        </a>

                                    @else

                                        <span class="role-no-access">
                                            No Access
                                        </span>

                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5">
                                    <div class="role-empty-state">

                                        <div class="role-empty-icon">
                                            🛡️
                                        </div>

                                        <h3>No Roles Available</h3>

                                        <p>
                                            No CRM roles have been created yet.
                                        </p>

                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Bottom Footer --}}
            @if ($roles->count() > 0)
                <div class="role-card-footer">
                    <p>
                        Showing <strong>{{ $roles->count() }}</strong>
                        {{ $roles->count() == 1 ? 'role' : 'roles' }}
                    </p>

                    <span>
                        Role-based access control
                    </span>
                </div>
            @endif

        </div>

    </div>

@endsection