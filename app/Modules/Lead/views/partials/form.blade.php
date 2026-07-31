@php
    $isEdit = isset($lead) && $lead;

    /*
     * Create page:
     * Database me configured default status use hoga.
     *
     * Edit page:
     * Lead ka existing status use hoga.
     *
     * Validation fail:
     * User ka previously selected old value preserve hoga.
     */
    $selectedStatus = old(
        'status',
        $isEdit
            ? $lead->status
            : $defaultStatus
    );

    /*
     * Create page:
     * Database me configured default priority use hogi.
     *
     * Edit page:
     * Lead ki existing priority use hogi.
     *
     * Validation fail:
     * User ka previously selected old value preserve hoga.
     */
    $selectedPriority = old(
        'priority',
        $isEdit
            ? $lead->priority
            : $defaultPriority
    );

    /*
     * Validation fail hone par old follow-up value
     * preserve hogi.
     */
    $followUpValue = old(
        'next_follow_up_at'
    );

    /*
     * Edit page par old value nahi hai to existing
     * Lead follow-up date show hogi.
     */
    if (
        $followUpValue === null
        && $isEdit
        && $lead->next_follow_up_at
    ) {
        $followUpValue = $lead
            ->next_follow_up_at
            ->format('Y-m-d\TH:i');
    }
@endphp

<form
    action="{{ $isEdit
        ? route(
            'lead.update',
            $lead->id
        )
        : route('lead.store') }}"
    method="POST"
    class="lead-form"
>
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="lead-form-grid">

        {{-- Lead Name --}}
        <div class="form-group">

            <label for="name">
                Lead Name
                <span>*</span>
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old(
                    'name',
                    $lead->name ?? ''
                ) }}"
                placeholder="Enter lead name"
                required
            >

        </div>

        {{-- Phone Number --}}
        <div class="form-group">

            <label for="phone">
                Phone Number
                <span>*</span>
            </label>

            <input
                type="text"
                id="phone"
                name="phone"
                value="{{ old(
                    'phone',
                    $lead->phone ?? ''
                ) }}"
                placeholder="Enter phone number"
                required
            >

        </div>

        {{-- Email Address --}}
        <div class="form-group">

            <label for="email">
                Email Address
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old(
                    'email',
                    $lead->email ?? ''
                ) }}"
                placeholder="Enter email address"
            >

        </div>

        {{-- Company Name --}}
        <div class="form-group">

            <label for="company">
                Company Name
            </label>

            <input
                type="text"
                id="company"
                name="company"
                value="{{ old(
                    'company',
                    $lead->company ?? ''
                ) }}"
                placeholder="Enter company name"
            >

        </div>

        {{-- Lead Source --}}
        <div class="form-group">

            <label for="source">
                Lead Source
                <span>*</span>
            </label>

            <select
                id="source"
                name="source"
                required
            >
                @foreach(
                    $sources
                    as $sourceKey => $sourceLabel
                )
                    <option
                        value="{{ $sourceKey }}"
                        @selected(
                            old(
                                'source',
                                $lead->source ?? 'other'
                            ) === $sourceKey
                        )
                    >
                        {{ $sourceLabel }}
                    </option>
                @endforeach
            </select>

        </div>

        {{-- Dynamic Lead Status --}}
        <div class="form-group">

            <label for="status">
                Lead Status
                <span>*</span>
            </label>

            <select
                id="status"
                name="status"
                required
            >
                @foreach(
                    $statuses
                    as $statusKey => $statusLabel
                )
                    <option
                        value="{{ $statusKey }}"
                        @selected(
                            $selectedStatus
                                === $statusKey
                        )
                    >
                        {{ $statusLabel }}
                    </option>
                @endforeach
            </select>

        </div>

        {{-- Dynamic Lead Priority --}}
        <div class="form-group">

            <label for="priority">
                Priority
                <span>*</span>
            </label>

            <select
                id="priority"
                name="priority"
                required
            >
                @foreach(
                    $priorities
                    as $priorityKey => $priorityLabel
                )
                    <option
                        value="{{ $priorityKey }}"
                        @selected(
                            $selectedPriority
                                === $priorityKey
                        )
                    >
                        {{ $priorityLabel }}
                    </option>
                @endforeach
            </select>

        </div>

        {{-- Lead Assignment --}}
        @if($canAssign)

            <div class="form-group">

                <label for="assigned_to">
                    Assign To
                </label>

                <select
                    id="assigned_to"
                    name="assigned_to"
                >
                    <option value="">
                        Unassigned
                    </option>

                    @foreach($users as $user)

                        <option
                            value="{{ $user->id }}"
                            @selected(
                                (string) old(
                                    'assigned_to',
                                    $lead->assigned_to ?? ''
                                )
                                ===
                                (string) $user->id
                            )
                        >
                            {{ $user->name }}
                            ({{ $user->email }})

                            @if(!$user->is_active)
                                - Inactive
                            @endif
                        </option>

                    @endforeach
                </select>

            </div>

        @else

            <div class="form-group">

                <label>
                    Assigned To
                </label>

                <div class="readonly-field">
                    This lead will remain assigned to you.
                </div>

            </div>

        @endif

        {{-- Next Follow-up --}}
        <div class="form-group">

            <label for="next_follow_up_at">
                Next Follow-up
            </label>

            <input
                type="datetime-local"
                id="next_follow_up_at"
                name="next_follow_up_at"
                value="{{ $followUpValue }}"
            >

        </div>

        {{-- Lead Notes --}}
        <div class="form-group full-width">

            <label for="notes">
                Lead Notes
            </label>

            <textarea
                id="notes"
                name="notes"
                rows="5"
                placeholder="Enter lead requirement, conversation or other notes"
            >{{ old(
                'notes',
                $lead->notes ?? ''
            ) }}</textarea>

        </div>

    </div>

    {{-- Form Actions --}}
    <div class="form-actions">

        <button
            type="submit"
            class="primary-btn"
        >
            {{ $isEdit
                ? 'Update Lead'
                : 'Save Lead' }}
        </button>

        <a
            href="{{ route('lead.index') }}"
            class="cancel-btn"
        >
            Cancel
        </a>

    </div>

</form>