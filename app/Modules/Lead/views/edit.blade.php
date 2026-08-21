@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/lead.css') }}?v={{ time() }}">
@endpush

@section('content')

    <div class="content-card">

        <div class="page-card-header">
            <div>
                <h1>Edit Lead</h1>
                <p>
                    Update lead details, status and assignment.
                </p>
            </div>

            <!-- <a
                href="{{ route('lead.index') }}"
                class="secondary-btn"
            >
                Back to Leads
            </a> -->

            <a href="{{ $returnUrl }}" class="secondary-btn">
                Back to Leads
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>
                    Please fix the following errors:
                </strong>

                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('lead::partials.form', [
            'lead' => $lead
        ])

    </div>

@endsection