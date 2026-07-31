@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/project.css') }}?v={{ time() }}">
@endpush

@section('content')
    <div class="content-card">
        <div class="page-card-header">
            <div>
                <h1>Edit Project</h1>
                <p>Update project details and assignment.</p>
            </div>

            <a href="{{ route('project.index') }}" class="secondary-btn">
                Back
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('project::partials.form', [
            'project' => $project
        ])
        </div>
@endsection