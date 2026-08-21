@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/client.css') }}?v={{ time() }}">
@endpush

@section('content')

    <div class="content-card">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="page-card-header">
            <div>
                <h1>{{ $client->name }}</h1>
                <p>Client #{{ $client->id }}</p>
            </div>

            <!-- <div class="client-actions">
                        <a href="{{ route('client.index') }}" class="secondary-btn">
                            Back
                        </a>

                        @if(auth()->user()->hasPermission('clients.edit'))
                            <a
                                href="{{ route('client.edit', $client->id) }}"
                                class="primary-btn"
                            >
                                Edit Client
                            </a>
                        @endif

                        @if(
                            $client->lead
                            && auth()->user()->hasPermission('leads.view')
                        )
                            <a
                                href="{{ route('lead.show', $client->lead_id) }}"
                                class="secondary-btn"
                            >
                                Origin Lead
                            </a>
                        @endif
                    </div> -->

            <div class="client-actions">

                <!-- <a href="{{ route('client.index') }}" class="secondary-btn">
                            Back
                        </a> -->

                <a href="{{ $returnUrl }}" class="secondary-btn">
                    Back
                </a>

                {{-- Is client ke saare projects dekhein --}}
                @if(auth()->user()->hasPermission('projects.view'))
                            <a href="{{ route('project.index', [
                        'client_id' => $client->id
                    ]) }}" class="secondary-btn">
                                Client Projects
                            </a>
                @endif

                {{-- Isi client ke liye naya project create karein --}}
                @if(auth()->user()->hasPermission('projects.create'))
                            <a href="{{ route('project.create', [
                        'client_id' => $client->id
                    ]) }}" class="primary-btn">
                                + Create Project
                            </a>
                @endif

                @if(auth()->user()->hasPermission('clients.edit'))
                    <a href="{{ route('client.edit', $client->id) }}" class="primary-btn">
                        Edit Client
                    </a>
                @endif

                @if(
                                $client->lead
                                && auth()->user()->hasPermission('leads.view')
                            )
                            <!-- <a href="{{ route('lead.show', $client->lead_id) }}" class="secondary-btn">
                                        Origin Lead
                                    </a> -->

                            <a href="{{ route(
                        'lead.show',
                        [
                            'lead' =>
                                $client->lead_id,

                            'return_url' =>
                                $returnUrl,
                        ]
                    ) }}" class="secondary-btn">
                                Origin Lead
                            </a>
                @endif

            </div>
        </div>

        <div class="client-details-grid">

            <div class="detail-box">
                <span>Phone</span>
                <strong>{{ $client->phone }}</strong>
            </div>

            <div class="detail-box">
                <span>Email</span>
                <strong>{{ $client->email ?: '-' }}</strong>
            </div>

            <div class="detail-box">
                <span>Company</span>
                <strong>{{ $client->company ?: '-' }}</strong>
            </div>

            <div class="detail-box">
                <span>Status</span>
                <strong>
                    {{ $statuses[$client->status] ?? ucfirst($client->status) }}
                </strong>
            </div>

            <div class="detail-box">
                <span>Assigned To</span>
                <strong>
                    {{ $client->assignedUser?->name ?? 'Unassigned' }}
                </strong>
            </div>

            <div class="detail-box">
                <span>Created By</span>
                <strong>
                    {{ $client->creator?->name ?? 'Unknown User' }}
                </strong>
            </div>

            <div class="detail-box">
                <span>Client Source</span>
                <strong>
                    {{ $client->lead_id
        ? 'Converted From Lead #' . $client->lead_id
        : 'Manual Client' }}
                </strong>
            </div>

            <div class="detail-box">
                <span>Created Date</span>
                <strong>
                    {{ $client->created_at->format('d M Y, h:i A') }}
                </strong>
            </div>

        </div>

        @if($client->notes)
            <div class="client-note-card">
                <h3>Notes</h3>
                <p>{{ $client->notes }}</p>
            </div>
        @endif

    </div>

@endsection