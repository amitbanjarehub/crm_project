@extends('admin::layouts.app')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/modules/role.css') }}?v={{ time() }}"
    >
@endpush

@section('content')

<div class="role-create-page">

    <div class="role-create-card">

        <div class="role-create-header">

            <div class="role-heading">
                <div class="role-heading-icon">
                    ＋
                </div>

                <div>
                    <h1>Create New Role</h1>

                    <p>
                        Create a CRM role and assign the required module permissions.
                    </p>
                </div>
            </div>

            <a
                href="{{ route('role.index') }}"
                class="role-back-btn"
            >
                ← Back to Roles
            </a>

        </div>

        @if($errors->any())
            <div class="role-alert role-alert-error">
                <span class="role-alert-icon">
                    !
                </span>

                <div>
                    <strong>
                        Please fix the following errors
                    </strong>

                    <ul class="role-error-list">
                        @foreach($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form
            action="{{ route('role.store') }}"
            method="POST"
            class="role-create-form"
        >
            @csrf

            <div class="role-form-section">

                <div class="role-form-heading">
                    <h2>Role Information</h2>

                    <p>
                        Enter a clear name according to the user's responsibility.
                    </p>
                </div>

                <div class="role-form-group">
                    <label for="name">
                        Role Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Example: Project Manager"
                        maxlength="100"
                        autocomplete="off"
                        autofocus
                        required
                    >

                    <small>
                        Examples: Project Manager, Developer, Designer,
                        Sales Executive or Support Executive.
                    </small>
                </div>

            </div>

            <div class="role-create-note">
                <span>ℹ</span>

                <div>
                    <strong>
                        What happens next?
                    </strong>

                    <p>
                        After saving, the Manage Permissions page will open.
                        Select only the permissions required for this role.
                    </p>
                </div>
            </div>

            <div class="role-form-actions">

                <button
                    type="submit"
                    class="role-save-btn"
                >
                    Create Role & Assign Permissions
                </button>

                <a
                    href="{{ route('role.index') }}"
                    class="role-cancel-btn"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

@endsection