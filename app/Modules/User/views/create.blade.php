@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}">
@endpush

@section('content')

    <div class="content-card">

        <div class="page-card-header">
            <div>
                <h1>Add User</h1>
                <p>Create a new CRM user from here.</p>
            </div>

            <a href="{{ route('user.index') }}" class="secondary-btn">
                Back to Users
            </a>
        </div>

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

        <form action="{{ route('user.store') }}" method="POST" class="admin-form">
            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label>User Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter user name" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                    <small>Minimum 8 characters.</small>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="Confirm password" required>
                </div>

                <div class="form-group">
                    <label>User Role</label>

                    <select name="role_id" required>
                        <option value="">Select Role</option>

                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="primary-btn">
                    Save User
                </button>

                <a href="{{ route('user.index') }}" class="cancel-btn">
                    Cancel
                </a>
            </div>
        </form>

    </div>

@endsection