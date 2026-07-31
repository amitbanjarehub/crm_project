@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/role.css') }}">
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

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Please fix the following errors:</strong>

            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="page-card-header">
        <div>
            <h1>{{ $role->name }} Permissions</h1>
            <p>
                Check or uncheck permissions for this role.
            </p>
        </div>

        <a href="{{ route('role.index') }}" class="secondary-btn">
            Back to Roles
        </a>
    </div>

    @if ($isAdminRole)
        <div class="admin-permission-notice">
            <strong>Admin Role:</strong>
            Admin always has full CRM access. Its permissions cannot be removed.
        </div>
    @endif

    @php
        $selectedPermissions = array_map(
            'intval',
            old('permissions', $assignedPermissions)
        );
    @endphp

    <form
        action="{{ route('role.permissions.update', $role->id) }}"
        method="POST"
        class="permission-form"
    >
        @csrf
        @method('PUT')

        @if (!$isAdminRole)
            <div class="permission-toolbar">
                <button type="button" id="selectAllPermissions" class="secondary-btn">
                    Select All
                </button>

                <button type="button" id="clearAllPermissions" class="secondary-btn">
                    Clear All
                </button>
            </div>
        @endif

        <div class="permission-groups">

            @forelse($permissions as $groupName => $groupPermissions)

                <div class="permission-group">

                    <div class="permission-group-header">
                        <h3>{{ $groupName }}</h3>

                        @if (!$isAdminRole)
                            <label class="group-select-label">
                                <input
                                    type="checkbox"
                                    class="group-select-checkbox"
                                >
                                Select Group
                            </label>
                        @endif
                    </div>

                    <div class="permission-grid">

                        @foreach($groupPermissions as $permission)
                            <label class="permission-item">

                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    class="permission-checkbox"
                                    @checked(in_array(
                                        (int) $permission->id,
                                        $selectedPermissions,
                                        true
                                    ))
                                    @disabled($isAdminRole)
                                >

                                <span class="permission-content">
                                    <strong>{{ $permission->name }}</strong>
                                    <small>{{ $permission->slug }}</small>
                                </span>

                            </label>
                        @endforeach

                    </div>

                </div>

            @empty

                <div class="empty-table">
                    No permissions found. Run PermissionSeeder first.
                </div>

            @endforelse

        </div>

        <div class="form-actions">

            @if (!$isAdminRole)
                <button type="submit" class="primary-btn">
                    Save Permissions
                </button>
            @endif

            <a href="{{ route('role.index') }}" class="cancel-btn">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allPermissionCheckboxes = document.querySelectorAll(
        '.permission-checkbox:not(:disabled)'
    );

    const selectAllButton = document.getElementById(
        'selectAllPermissions'
    );

    const clearAllButton = document.getElementById(
        'clearAllPermissions'
    );

    if (selectAllButton) {
        selectAllButton.addEventListener('click', function () {
            allPermissionCheckboxes.forEach(function (checkbox) {
                checkbox.checked = true;
            });

            updateGroupCheckboxes();
        });
    }

    if (clearAllButton) {
        clearAllButton.addEventListener('click', function () {
            allPermissionCheckboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });

            updateGroupCheckboxes();
        });
    }

    document.querySelectorAll('.permission-group').forEach(function (group) {
        const groupCheckbox = group.querySelector(
            '.group-select-checkbox'
        );

        const permissionCheckboxes = group.querySelectorAll(
            '.permission-checkbox:not(:disabled)'
        );

        if (!groupCheckbox) {
            return;
        }

        groupCheckbox.addEventListener('change', function () {
            permissionCheckboxes.forEach(function (checkbox) {
                checkbox.checked = groupCheckbox.checked;
            });
        });

        permissionCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                updateGroupCheckbox(group);
            });
        });

        updateGroupCheckbox(group);
    });

    function updateGroupCheckbox(group) {
        const groupCheckbox = group.querySelector(
            '.group-select-checkbox'
        );

        const permissionCheckboxes = Array.from(
            group.querySelectorAll(
                '.permission-checkbox:not(:disabled)'
            )
        );

        if (!groupCheckbox || permissionCheckboxes.length === 0) {
            return;
        }

        const checkedCount = permissionCheckboxes.filter(
            checkbox => checkbox.checked
        ).length;

        groupCheckbox.checked =
            checkedCount === permissionCheckboxes.length;

        groupCheckbox.indeterminate =
            checkedCount > 0 &&
            checkedCount < permissionCheckboxes.length;
    }

    function updateGroupCheckboxes() {
        document.querySelectorAll('.permission-group')
            .forEach(function (group) {
                updateGroupCheckbox(group);
            });
    }
});
</script>
@endpush