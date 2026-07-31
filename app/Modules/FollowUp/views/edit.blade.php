@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/followup.css') }}?v={{ time() }}">
@endpush

@section('content')

<div class="content-card">

    <div class="page-card-header">
        <div>
            <h1>Edit Follow-up</h1>
            <p>Update the follow-up record.</p>
        </div>

        <a
            href="{{ route('lead.show', $lead->id) }}"
            class="secondary-btn"
        >
            Back to Lead
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul class="error-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('followup::partials.form')

</div>

@endsection