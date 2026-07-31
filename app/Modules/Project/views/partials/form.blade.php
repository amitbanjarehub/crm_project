@php
    $isEdit = isset($project) && $project;
@endphp

<form
    action="{{ $isEdit
        ? route('project.update', $project->id)
        : route('project.store') }}"
    method="POST"
    class="project-form"
>
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="project-form-grid">

        <div class="form-group">
            <label>Client <span>*</span></label>

            <select name="client_id" required>
                <option value="">Select Client</option>

                @foreach($clients as $client)
                    <option
                        value="{{ $client->id }}"
                        @selected(
                            (string) old(
                                'client_id',
                                request(
                                    'client_id',
                                    $project->client_id ?? ''
                                )
                            ) === (string) $client->id
                        )
                    >
                        {{ $client->name }}
                        @if($client->company)
                            — {{ $client->company }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Project Name <span>*</span></label>

            <input
                type="text"
                name="name"
                value="{{ old('name', $project->name ?? '') }}"
                required
            >
        </div>

        <div class="form-group">
            <label>Project Manager</label>

            <select name="project_manager_id">
                <option value="">Select Manager</option>

                @foreach($users as $user)
                    <option
                        value="{{ $user->id }}"
                        @selected(
                            (string) old(
                                'project_manager_id',
                                $project->project_manager_id ?? ''
                            ) === (string) $user->id
                        )
                    >
                        {{ $user->name }} — {{ $user->email }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Priority <span>*</span></label>

            <select name="priority" required>
                @foreach($priorities as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(
                            old(
                                'priority',
                                $project->priority ?? 'medium'
                            ) === $key
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Status <span>*</span></label>

            <select name="status" required>
                @foreach($statuses as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(
                            old(
                                'status',
                                $project->status ?? 'draft'
                            ) === $key
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Budget</label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="budget"
                value="{{ old('budget', $project->budget ?? '') }}"
            >
        </div>

        <div class="form-group">
            <label>Start Date</label>

            <input
                type="date"
                name="start_date"
                value="{{ old(
                    'start_date',
                    isset($project) && $project->start_date
                        ? $project->start_date->format('Y-m-d')
                        : ''
                ) }}"
            >
        </div>

        <div class="form-group">
            <label>Due Date</label>

            <input
                type="date"
                name="due_date"
                value="{{ old(
                    'due_date',
                    isset($project) && $project->due_date
                        ? $project->due_date->format('Y-m-d')
                        : ''
                ) }}"
            >
        </div>

        <div class="form-group full-width">
            <label>Description</label>

            <textarea
                name="description"
                rows="6"
            >{{ old('description', $project->description ?? '') }}</textarea>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="primary-btn">
            {{ $isEdit ? 'Update Project' : 'Save Project' }}
        </button>

        <a
            href="{{ route('project.index') }}"
            class="cancel-btn"
        >
            Cancel
        </a>
    </div>
</form>