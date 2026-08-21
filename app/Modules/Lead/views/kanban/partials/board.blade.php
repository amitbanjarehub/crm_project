<div
    class="lead-kanban-board"
    id="leadKanbanBoard"
    data-group-by="{{ $groupBy }}"
>

    @forelse(
        $columns
        as $column
    )

        @php
            $dropBlocked =
                $groupBy === 'status'
                && (
                    $column[
                        'system_key'
                    ]
                    ?? null
                ) === 'converted';
        @endphp

        <section
            class="lead-kanban-column
                {{ $dropBlocked
                    ? 'drop-blocked'
                    : '' }}"

            data-column-slug="{{ $column[
                'slug'
            ] }}"

            data-drop-allowed="{{ $dropBlocked
                ? '0'
                : '1' }}"

            style="--kanban-column-color:
                {{ $column[
                    'color'
                ] }};"
        >

            <header class="kanban-column-header">

                <button
                    type="button"
                    class="kanban-column-drag-handle"
                    draggable="true"
                    title="Drag column"
                    aria-label="Drag column"
                >
                    ⠿
                </button>

                <div class="kanban-column-title">

                    <span class="kanban-column-color"></span>

                    <strong>
                        {{ $column[
                            'name'
                        ] }}
                    </strong>

                    @if(
                        !$column[
                            'is_active'
                        ]
                    )
                        <small>
                            Inactive
                        </small>
                    @endif

                </div>

                <span
                    class="kanban-column-count"
                >
                    {{ $column[
                        'count'
                    ] }}
                </span>

            </header>

            @if($dropBlocked)

                <div class="kanban-column-notice">
                    Use the Convert action to move a Lead here.
                </div>

            @endif

            <div
                class="kanban-card-list"
                data-column-slug="{{ $column[
                    'slug'
                ] }}"
            >

                @forelse(
                    $column['leads']
                    as $lead
                )

                    @include(
                        'lead::kanban.partials.card',
                        [
                            'lead' =>
                                $lead,

                            'groupBy' =>
                                $groupBy,
                        ]
                    )

                @empty

                    <div class="kanban-empty-column">
                        Drop a Lead here
                    </div>

                @endforelse

            </div>

        </section>

    @empty

        <div class="kanban-board-empty">
            <strong>
                No Kanban columns available.
            </strong>

            <span>
                Configure Lead statuses or priorities in CRM Settings.
            </span>
        </div>

    @endforelse

</div>