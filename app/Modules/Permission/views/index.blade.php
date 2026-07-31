@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/permission.css') }}?v={{ time() }}">
@endpush

@section('content')

@php
    $totalGroups = $permissions->count();
    $totalPermissions = $permissions->flatten(1)->count();

    $groupIcons = [
        'Dashboard' => '📊',
        'User Management' => '👥',
        'Role Management' => '🛡️',
        'Permission Management' => '🔐',
        'Lead Management' => '📌',
        'Client Management' => '🏢',
        'Settings' => '⚙️',
    ];
@endphp

<div class="permission-page">

    <div class="permission-main-card">

        {{-- Page Header --}}
        <div class="permission-page-header">

            <div class="permission-heading">

                <div class="permission-heading-icon">
                    🔐
                </div>

                <div>
                    <h1>Permission Management</h1>

                    <p>
                        View all available CRM permissions grouped by module.
                    </p>
                </div>

            </div>

            <div class="permission-summary">

                <div class="permission-summary-item">
                    <span>Total Groups</span>
                    <strong>{{ $totalGroups }}</strong>
                </div>

                <div class="permission-summary-divider"></div>

                <div class="permission-summary-item">
                    <span>Total Permissions</span>
                    <strong>{{ $totalPermissions }}</strong>
                </div>

            </div>

        </div>

        {{-- Information Message --}}
        <div class="permission-info-bar">

            <span class="permission-info-icon">
                ℹ
            </span>

            <p>
                These permissions are assigned to roles from the
                <strong>Role Management</strong> section.
            </p>

        </div>

        {{-- Permission Groups --}}
        <div class="permission-groups-grid">

            @forelse($permissions as $groupName => $groupPermissions)

                @php
                    $groupIcon = $groupIcons[$groupName] ?? '🔑';
                @endphp

                <div class="permission-group-card">

                    {{-- Group Header --}}
                    <div class="permission-group-card-header">

                        <div class="permission-group-title">

                            <div class="permission-group-icon">
                                {{ $groupIcon }}
                            </div>

                            <div>
                                <h3>{{ $groupName }}</h3>

                                <p>
                                    CRM access permissions
                                </p>
                            </div>

                        </div>

                        <span class="permission-count-badge">
                            {{ $groupPermissions->count() }}
                            {{ $groupPermissions->count() === 1 ? 'Permission' : 'Permissions' }}
                        </span>

                    </div>

                    {{-- Permission List --}}
                    <div class="permission-list">

                        <div class="permission-list-heading">

                            <span>#</span>
                            <span>Permission Details</span>
                            <span>Permission Slug</span>

                        </div>

                        @foreach($groupPermissions as $permission)

                            <div class="permission-list-row">

                                <div>
                                    <span class="permission-serial">
                                        {{ $loop->iteration }}
                                    </span>
                                </div>

                                <div class="permission-name-box">

                                    <span class="permission-key-icon">
                                        🔑
                                    </span>

                                    <div>
                                        <strong>
                                            {{ $permission->name }}
                                        </strong>

                                        <small>
                                            Allows access to this CRM action
                                        </small>
                                    </div>

                                </div>

                                <div>
                                    <code class="permission-slug">
                                        {{ $permission->slug }}
                                    </code>
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            @empty

                <div class="permission-empty-state">

                    <div class="permission-empty-icon">
                        🔐
                    </div>

                    <h3>No Permissions Found</h3>

                    <p>
                        No CRM permissions are currently available.
                    </p>

                </div>

            @endforelse

        </div>

        {{-- Footer --}}
        @if ($totalPermissions > 0)

            <div class="permission-card-footer">

                <p>
                    Showing
                    <strong>{{ $totalPermissions }}</strong>
                    CRM permissions in
                    <strong>{{ $totalGroups }}</strong>
                    groups.
                </p>

                <span>
                    Role-Based Access Control
                </span>

            </div>

        @endif

    </div>

</div>

@endsection