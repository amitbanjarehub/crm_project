@php
    $statusColor =
        $lead
            ->statusDefinition
                ?->color
        ?? '#64748B';

    $statusName =
        $lead
            ->statusDefinition
                ?->name
        ?? ucfirst(
            str_replace(
                '_',
                ' ',
                $lead->status
            )
        );

    $priorityColor =
        $lead
            ->priorityDefinition
                ?->color
        ?? '#64748B';

    $priorityName =
        $lead
            ->priorityDefinition
                ?->name
        ?? ucfirst(
            str_replace(
                '_',
                ' ',
                $lead->priority
            )
        );

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

    $isOverdue =
        $lead->next_follow_up_at
        && $lead
            ->next_follow_up_at
            ->lt(now());

    $canDrag =
        !$lead->isConverted()
        && auth()
            ->user()
            ->hasPermission(
                'leads.edit'
            );
@endphp

<article class="kanban-lead-card" data-lead-id="{{ $lead->id }}" data-kanban-version="{{ $lead
    ->kanban_version }}" data-status="{{ $lead->status }}" data-priority="{{ $lead->priority }}" tabindex="0">

    <div class="kanban-card-top">

        @if($canDrag)

            <button type="button" class="kanban-card-drag-handle" draggable="true" title="Drag Lead" aria-label="Drag Lead"
                data-no-drawer>
                ⠿
            </button>

        @else

            <span class="kanban-card-lock" title="This Lead cannot be moved">
                🔒
            </span>

        @endif

        <div class="kanban-card-heading">

            <strong>
                {{ $lead->name }}
            </strong>

            <span>
                Lead #{{ $lead->id }}
            </span>

        </div>

        @if($lead->isConverted())

            <span class="kanban-converted-badge">
                Converted
            </span>

        @endif

    </div>

    @if($lead->company)

        <div class="kanban-card-company">
            {{ $lead->company }}
        </div>

    @endif

    <div class="kanban-card-contact">

        <a href="tel:{{ $lead->phone }}" data-no-drawer>
            📞 {{ $lead->phone }}
        </a>

        @if($lead->email)

            <a href="mailto:{{ $lead->email }}" data-no-drawer>
                ✉ {{ $lead->email }}
            </a>

        @endif

    </div>

    <div class="kanban-card-badges">

        <span class="kanban-card-status" style="--kanban-badge-color:
                {{ $statusColor }};">
            {{ $statusName }}
        </span>

        <span class="kanban-card-priority" style="--kanban-badge-color:
                {{ $priorityColor }};">
            {{ $priorityName }}
        </span>

    </div>

    <div class="kanban-card-information">

        <div>
            <span>Source</span>

            <strong>
                {{ \App\Modules\Lead\Models\Lead::sources()[
    $lead->source
]
    ?? ucfirst(
        $lead->source
    ) }}
            </strong>
        </div>

        <div>
            <span>Assigned</span>

            <strong>
                {{ $lead
    ->assignedUser
        ?->name
    ?? 'Unassigned' }}
            </strong>
        </div>

    </div>

    <div class="kanban-followup
            {{ $isOverdue
    ? 'overdue'
    : '' }}">

        <span>
            Next Follow-up
        </span>

        <strong>

            @if(
                        $lead
                            ->next_follow_up_at
                    )

                    {{ $lead
                ->next_follow_up_at
                ->format(
                    'd M Y, h:i A'
                ) }}

            @else

                Not scheduled

            @endif

        </strong>

        @if($isOverdue)
            <small>
                Overdue
            </small>
        @endif

    </div>

    @if($lead->last_followed_up_at)

        <div class="kanban-last-activity">
            Last contacted:
            {{ \Illuminate\Support\Carbon::parse(
            $lead->last_followed_up_at
        )->format(
                'd M Y, h:i A'
            ) }}
        </div>

    @endif

    <div class="kanban-card-actions" data-no-drawer>

        <a href="{{ route(
    'lead.show',
    $lead->id
) }}" class="kanban-action view js-kanban-return-link" data-no-drawer>
            View
        </a>

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
            ) }}" class="kanban-action followup js-kanban-return-link" data-no-drawer>
                    Follow-up
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
            ) }}" class="kanban-action edit js-kanban-return-link" data-no-drawer>
                    Edit
                </a>

        @endif

        @if($whatsappNumber !== '')

            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer"
                class="kanban-action whatsapp" data-no-drawer>
                WhatsApp
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
                        );" data-no-drawer>
                    @csrf

                    <button type="submit" class="kanban-action convert" data-no-drawer>
                        Convert
                    </button>
                </form>

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
                        );" data-no-drawer>
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="kanban-action delete js-kanban-return-link" data-no-drawer>
                        Delete
                    </button>
                </form>

        @endif

    </div>

</article>