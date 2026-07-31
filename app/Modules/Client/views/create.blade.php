@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/client.css') }}?v={{ time() }}">
@endpush

@section('content')

<div class="content-card">
    <div class="page-card-header">
        <div>
            <h1>Add Client</h1>
            <p>Create a client manually.</p>
        </div>

        <a href="{{ route('client.index') }}" class="secondary-btn">
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

    @include('client::partials.form', [
        'client' => null
    ])
</div>

@endsection