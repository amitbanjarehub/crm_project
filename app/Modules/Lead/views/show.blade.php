@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/lead.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modules/followup.css') }}?v={{ time() }}">
@endpush

@section('content')

<div class="content-card">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="page-card-header">
        <div>
            <h1>{{ $lead->name }}</h1>
            <p>Lead #{{ $lead->id }} details and follow-up history</p>
        </div>

        <div class="lead-detail-actions">
            <a href="{{ route('lead.index') }}" class="secondary-btn">
                Back
            </a>

            @if(
                !$lead->isConverted()
                && auth()->user()->hasPermission('follow_ups.create')
            )
                <a
                    href="{{ route('followup.create', $lead->id) }}"
                    class="primary-btn"
                >
                    + Add Follow-up
                </a>
            @endif

            @if(
                !$lead->isConverted()
                && auth()->user()->hasPermission('leads.edit')
            )
                <a
                    href="{{ route('lead.edit', $lead->id) }}"
                    class="secondary-btn"
                >
                    Edit Lead
                </a>
            @endif

            @if(
                !$lead->isConverted()
                && auth()->user()->hasPermission('leads.convert')
            )
                <form
                    method="POST"
                    action="{{ route('lead.convert', $lead->id) }}"
                    onsubmit="return confirm('Convert this lead into a client?');"
                >
                    @csrf

                    <button type="submit" class="convert-client-btn">
                        Convert To Client
                    </button>
                </form>
            @endif

            @if(
                $lead->client
                && auth()->user()->hasPermission('clients.view')
            )
                <a
                    href="{{ route('client.show', $lead->client->id) }}"
                    class="primary-btn"
                >
                    Open Client
                </a>
            @endif
        </div>
    </div>

    <div class="lead-details-grid">

        <div class="detail-box">
            <span>Phone</span>
            <strong>{{ $lead->phone }}</strong>
        </div>

        <div class="detail-box">
            <span>Email</span>
            <strong>{{ $lead->email ?: '-' }}</strong>
        </div>

        <div class="detail-box">
            <span>Company</span>
            <strong>{{ $lead->company ?: '-' }}</strong>
        </div>

        <div class="detail-box">
            <span>Source</span>
            <strong>
                {{ $sources[$lead->source] ?? ucfirst($lead->source) }}
            </strong>
        </div>

        <div class="detail-box">
    <span>Status</span>

    <strong>
        <span
            class="dynamic-lead-option-badge"
            style="--lead-option-color:
                {{ $lead
                    ->statusDefinition
                    ?->color
                    ?? '#64748B' }}"
        >
            {{ $lead
                ->statusDefinition
                ?->name
                ?? ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $lead->status
                    )
                ) }}
        </span>
    </strong>
</div>

    <div class="detail-box">
    <span>Priority</span>

    <strong>
        <span
            class="dynamic-lead-option-badge"
            style="--lead-option-color:
                {{ $lead
                    ->priorityDefinition
                    ?->color
                    ?? '#64748B' }}"
        >
            {{ $lead
                ->priorityDefinition
                ?->name
                ?? ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $lead->priority
                    )
                ) }}
        </span>
    </strong>
</div>

        <div class="detail-box">
            <span>Assigned To</span>
            <strong>
                {{ $lead->assignedUser?->name ?? 'Unassigned' }}
            </strong>
        </div>

        <div class="detail-box">
            <span>Next Follow-up</span>
            <strong>
                {{ $lead->next_follow_up_at
                    ? $lead->next_follow_up_at->format('d M Y, h:i A')
                    : '-' }}
            </strong>
        </div>

    </div>

    @if($lead->isConverted())
        <div class="conversion-info">
            <strong>Converted To Client</strong>
            <span>
                {{ $lead->converted_at
                    ? $lead->converted_at->format('d M Y, h:i A')
                    : '-' }}
            </span>
            <span>
                By {{ $lead->convertedBy?->name ?? 'Unknown User' }}
            </span>
        </div>
    @endif

    @if($lead->notes)
        <div class="lead-note-card">
            <h3>Original Lead Notes</h3>
            <p>{{ $lead->notes }}</p>
        </div>
    @endif

    <div class="timeline-header">
        <h2>Follow-up History</h2>
        <span>{{ $followUps->total() }} records</span>
    </div>

    <div class="followup-timeline">

        @forelse($followUps as $followUp)
            @php
                $canModifyRecord =
                    auth()->user()->isSuperAdmin()
                    || auth()->user()->hasPermission('follow_ups.view_all')
                    || (int) $followUp->user_id === (int) auth()->id();
            @endphp

            <div class="timeline-item">
                <div class="timeline-dot"></div>

                <div class="timeline-card">
                    <div class="timeline-card-header">
                        <div>
                            <strong>
                                {{ $followUpTypes[$followUp->type] ?? ucfirst($followUp->type) }}
                            </strong>

                            <span>
                                {{ $followUp->followed_up_at->format('d M Y, h:i A') }}
                            </span>
                        </div>

                        <div class="followup-actions">
                            @if(
                                $canModifyRecord
                                && auth()->user()->hasPermission('follow_ups.edit')
                            )
                                <a
                                    href="{{ route('followup.edit', $followUp->id) }}"
                                    class="table-btn edit"
                                >
                                    Edit
                                </a>
                            @endif

                            @if(
                                $canModifyRecord
                                && auth()->user()->hasPermission('follow_ups.delete')
                            )
                                <form
                                    method="POST"
                                    action="{{ route('followup.destroy', $followUp->id) }}"
                                    onsubmit="return confirm('Delete this follow-up?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="table-btn delete">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <p>{{ $followUp->notes }}</p>

                    <div class="timeline-meta">
                        <span>
                            Outcome:
                            <strong>
                                {{ $followUpOutcomes[$followUp->outcome] ?? ucfirst($followUp->outcome) }}
                            </strong>
                        </span>

                        <span>
                            Added by:
                            <strong>
                                {{ $followUp->user?->name ?? 'Deleted User' }}
                            </strong>
                        </span>

                        @if($followUp->next_follow_up_at)
                            <span>
                                Next:
                                <strong>
                                    {{ $followUp->next_follow_up_at->format('d M Y, h:i A') }}
                                </strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-table">
                No follow-up history available.
            </div>
        @endforelse

    </div>

    @if($followUps->hasPages())
        <div class="simple-pagination">
            @if($followUps->onFirstPage())
                <span class="page-link disabled">Previous</span>
            @else
                <a href="{{ $followUps->previousPageUrl() }}" class="page-link">
                    Previous
                </a>
            @endif

            <span>
                Page {{ $followUps->currentPage() }}
                of {{ $followUps->lastPage() }}
            </span>

            @if($followUps->hasMorePages())
                <a href="{{ $followUps->nextPageUrl() }}" class="page-link">
                    Next
                </a>
            @else
                <span class="page-link disabled">Next</span>
            @endif
        </div>
    @endif

</div>

@endsection