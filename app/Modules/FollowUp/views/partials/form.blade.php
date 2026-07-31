@php
    $isEdit = isset($followUp) && $followUp;

    $followedUpValue = old(
        'followed_up_at',
        $isEdit && $followUp->followed_up_at
            ? $followUp->followed_up_at->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i')
    );

    $nextFollowUpValue = old(
        'next_follow_up_at',
        $isEdit && $followUp->next_follow_up_at
            ? $followUp->next_follow_up_at->format('Y-m-d\TH:i')
            : ''
    );
@endphp

<form
    action="{{ $isEdit
        ? route('followup.update', $followUp->id)
        : route('followup.store', $lead->id) }}"
    method="POST"
    class="followup-form"
>
    @csrf

    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="followup-lead-summary">
        <strong>{{ $lead->name }}</strong>
        <span>{{ $lead->phone }}</span>
        <span>{{ $lead->company ?: 'No company' }}</span>
    </div>

    <div class="followup-form-grid">

        <div class="form-group">
            <label for="type">Follow-up Type *</label>

            <select id="type" name="type" required>
                @foreach($types as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(
                            old(
                                'type',
                                $followUp->type ?? 'call'
                            ) === $key
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="followed_up_at">
                Follow-up Date & Time *
            </label>

            <input
                type="datetime-local"
                id="followed_up_at"
                name="followed_up_at"
                value="{{ $followedUpValue }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="outcome">Outcome *</label>

            <select id="outcome" name="outcome" required>
                @foreach($outcomes as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(
                            old(
                                'outcome',
                                $followUp->outcome ?? 'interested'
                            ) === $key
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="next_follow_up_at">
                Next Follow-up
            </label>

            <input
                type="datetime-local"
                id="next_follow_up_at"
                name="next_follow_up_at"
                value="{{ $nextFollowUpValue }}"
            >
        </div>

        <div class="form-group full-width">
            <label for="notes">Discussion Notes *</label>

            <textarea
                id="notes"
                name="notes"
                rows="6"
                required
                placeholder="What was discussed with the lead?"
            >{{ old('notes', $followUp->notes ?? '') }}</textarea>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="primary-btn">
            {{ $isEdit ? 'Update Follow-up' : 'Save Follow-up' }}
        </button>

        <a
            href="{{ route('lead.show', $lead->id) }}"
            class="cancel-btn"
        >
            Cancel
        </a>
    </div>
</form>