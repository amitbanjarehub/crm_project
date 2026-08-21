@php
    $statusColor =
        $lead
            ->statusDefinition
                ?->color
        ?? '#64748B';

    $priorityColor =
        $lead
            ->priorityDefinition
                ?->color
        ?? '#64748B';

    $phoneDigits =
        preg_replace(
            '/\D+/',
            '',
            $lead->phone
        );

    if (
        strlen($phoneDigits) === 10
    ) {
        $whatsappNumber =
            '91' . $phoneDigits;
    } elseif (
        strlen($phoneDigits) === 11
        && str_starts_with(
            $phoneDigits,
            '0'
        )
    ) {
        $whatsappNumber =
            '91'
            . substr(
                $phoneDigits,
                1
            );
    } else {
        $whatsappNumber =
            $phoneDigits;
    }
@endphp

<div class="kanban-drawer-header">

    <div>
        <span>
            Lead #{{ $lead->id }}
        </span>

        <h2>
            {{ $lead->name }}
        </h2>

        <p>
            {{ $lead->company
    ?: 'No company added' }}
        </p>
    </div>

    <button type="button" class="kanban-drawer-close" id="kanbanDrawerClose" aria-label="Close Lead details">
        ×
    </button>

</div>

<div class="kanban-drawer-body">

    <div class="kanban-drawer-badges">

        <span style="--kanban-badge-color:
                {{ $statusColor }};">
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

        <span style="--kanban-badge-color:
                {{ $priorityColor }};">
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

    </div>

    <div class="kanban-drawer-action-grid">

        <a href="tel:{{ $lead->phone }}">
            Call
        </a>

        @if($whatsappNumber !== '')

            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer">
                WhatsApp
            </a>

        @endif

        @if($lead->email)

            <a href="mailto:{{ $lead->email }}">
                Email
            </a>

        @endif

        <a href="{{ route(
    'lead.show',
    $lead->id
) }}" class="js-kanban-return-link">
            Full Details
        </a>

    </div>

    <section class="kanban-drawer-section">

        <h3>Lead Overview</h3>

        <div class="kanban-drawer-detail-grid">

            <div>
                <span>Phone</span>
                <strong>
                    {{ $lead->phone }}
                </strong>
            </div>

            <div>
                <span>Email</span>
                <strong>
                    {{ $lead->email
    ?: '-' }}
                </strong>
            </div>

            <div>
                <span>Company</span>
                <strong>
                    {{ $lead->company
    ?: '-' }}
                </strong>
            </div>

            <div>
                <span>Source</span>
                <strong>
                    {{ $sources[
    $lead->source
]
    ?? ucfirst(
        $lead->source
    ) }}
                </strong>
            </div>

            <div>
                <span>Assigned To</span>
                <strong>
                    {{ $lead
    ->assignedUser
        ?->name
    ?? 'Unassigned' }}
                </strong>
            </div>

            <div>
                <span>Created By</span>
                <strong>
                    {{ $lead
    ->creator
        ?->name
    ?? 'Unknown' }}
                </strong>
            </div>

            <div>
                <span>Next Follow-up</span>
                <strong>
                    {{ $lead
    ->next_follow_up_at
        ?->format(
        'd M Y, h:i A'
    )
    ?? '-' }}
                </strong>
            </div>

            <div>
                <span>Created</span>
                <strong>
                    {{ $lead
    ->created_at
        ?->format(
        'd M Y, h:i A'
    )
    ?? '-' }}
                </strong>
            </div>

        </div>

    </section>

    @if($lead->notes)

        <section class="kanban-drawer-section">

            <h3>Lead Notes</h3>

            <div class="kanban-drawer-notes">
                {{ $lead->notes }}
            </div>

        </section>

    @endif

    <section class="kanban-drawer-section">

        <div class="kanban-drawer-section-heading">

            <h3>Recent Follow-ups</h3>

            <span>
                {{ $recentFollowUps
    ->count() }}
                shown
            </span>

        </div>

        <div class="kanban-drawer-timeline">

            @forelse(
                            $recentFollowUps
                            as $followUp
                        )

                        <article>

                            <div>
                                <strong>
                                    {{ $followUpTypes[
                    $followUp->type
                ]
                    ?? ucfirst(
                        $followUp->type
                    ) }}
                                </strong>

                                <span>
                                    {{ $followUp
                    ->followed_up_at
                    ->format(
                        'd M Y, h:i A'
                    ) }}
                                </span>
                            </div>

                            <p>
                                {{ $followUp->notes }}
                            </p>

                            <small>
                                Outcome:
                                {{ $followUpOutcomes[
                    $followUp->outcome
                ]
                    ?? ucfirst(
                        $followUp->outcome
                    ) }}

                                · By
                                {{ $followUp
                    ->user
                        ?->name
                    ?? 'Deleted User' }}
                            </small>

                        </article>

            @empty

                <div class="kanban-drawer-empty">
                    No follow-up history available.
                </div>

            @endforelse

        </div>

    </section>

</div>

<div class="kanban-drawer-footer">

    @if(
            !$lead->isConverted()
            && auth()
                ->user()
                ->hasPermission(
                    'follow_ups.create'
                )
        )

        <a href="{{ route(
            'followup.create',
            $lead->id
        ) }}" class="primary-btn js-kanban-return-link">
            + Add Follow-up
        </a>

    @endif

    @if(
            !$lead->isConverted()
            && auth()
                ->user()
                ->hasPermission(
                    'leads.edit'
                )
        )

        <a href="{{ route(
            'lead.edit',
            $lead->id
        ) }}" class="secondary-btn js-kanban-return-link">
            Edit Lead
        </a>

    @endif

    @if(
            !$lead->isConverted()
            && auth()
                ->user()
                ->hasPermission(
                    'leads.convert'
                )
        )

        <form method="POST" action="{{ route(
            'lead.convert',
            $lead->id
        ) }}" class="js-kanban-return-form" onsubmit="return confirm(
            'Convert this Lead into a Client?'
        );">
            @csrf

            <button type="submit" class="kanban-drawer-convert">
                Convert
            </button>
        </form>

    @endif

    @if(
            $lead->client
            && auth()
                ->user()
                ->hasPermission(
                    'clients.view'
                )
        )

        <a href="{{ route(
            'client.show',
            $lead->client->id
        ) }}" class="primary-btn js-kanban-return-link">
            Open Client
        </a>

    @endif

    @if(
            !$lead->isConverted()
            && auth()
                ->user()
                ->hasPermission(
                    'leads.delete'
                )
        )

        <form method="POST" action="{{ route(
            'lead.destroy',
            $lead->id
        ) }}" onsubmit="return confirm(
                        'Delete this Lead?'
                    );">
            @csrf
            @method('DELETE')

            <button type="submit" class="kanban-drawer-delete js-kanban-return-link">
                Delete
            </button>
        </form>

    @endif

</div>