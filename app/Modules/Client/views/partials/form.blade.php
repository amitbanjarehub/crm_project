@php
    $isEdit = isset($client) && $client;
@endphp

<form
    method="POST"
    action="{{ $isEdit
        ? route('client.update', $client->id)
        : route('client.store') }}"
    class="client-form"
>
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="client-form-grid">

        <div class="form-group">
            <label>Client Name *</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $client->name ?? '') }}"
                required
            >
        </div>

        <div class="form-group">
            <label>Phone *</label>
            <input
                type="text"
                name="phone"
                value="{{ old('phone', $client->phone ?? '') }}"
                required
            >
        </div>

        <div class="form-group">
            <label>Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email', $client->email ?? '') }}"
            >
        </div>

        <div class="form-group">
            <label>Company</label>
            <input
                type="text"
                name="company"
                value="{{ old('company', $client->company ?? '') }}"
            >
        </div>

        <div class="form-group">
            <label>Status *</label>
            <select name="status" required>
                @foreach($statuses as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(
                            old(
                                'status',
                                $client->status ?? 'active'
                            ) === $key
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($canAssign)
            <div class="form-group">
                <label>Assigned To</label>

                <select name="assigned_to">
                    <option value="">Unassigned</option>

                    @foreach($users as $user)
                        <option
                            value="{{ $user->id }}"
                            @selected(
                                (string) old(
                                    'assigned_to',
                                    $client->assigned_to ?? ''
                                ) === (string) $user->id
                            )
                        >
                            {{ $user->name }}
                            @if(!$user->is_active)
                                - Inactive
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group full-width">
            <label>Notes</label>

            <textarea
                name="notes"
                rows="5"
            >{{ old('notes', $client->notes ?? '') }}</textarea>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="primary-btn">
            {{ $isEdit ? 'Update Client' : 'Save Client' }}
        </button>

        <a href="{{ route('client.index') }}" class="cancel-btn">
            Cancel
        </a>
    </div>
</form>