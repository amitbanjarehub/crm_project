@php
    $isEdit = isset($task) && $task;

    $startDateValue = old('start_date');

    if (
        $startDateValue === null
        && $isEdit
        && $task->start_date
    ) {
        $startDateValue = $task->start_date->format('Y-m-d');
    }

    $dueAtValue = old('due_at');

    if (
        $dueAtValue === null
        && $isEdit
        && $task->due_at
    ) {
        $dueAtValue = $task->due_at->format('Y-m-d\TH:i');
    }

    $selectedReviewer = old(
        'reviewer_id',
        $task->reviewer_id
        ?? $project->project_manager_id
        ?? ''
    );

    $requiresReview = (bool) old(
        'requires_review',
        $task->requires_review ?? true
    );

    $selectedPriority = old(
        'priority',
        $task->priority
        ?? $defaultPriority
    );
@endphp

<form action="{{ $isEdit
    ? route('task.update', $task->id)
    : route('task.store', $projectService->id) }}" method="POST" class="task-form">
    @csrf

    @if ($isEdit)
        @method('PUT')
        <input type="hidden" name="from" value="{{ request('from') }}">
    @endif

    <div class="task-context-card">

        <div>
            <span>Project</span>

            <strong>
                {{ $project->name }}
            </strong>

            <small>
                {{ $project->project_code }}
            </small>
        </div>

        <div>
            <span>Service</span>

            <strong>
                {{ $projectService->name }}
            </strong>
        </div>

    </div>

    <div class="task-form-grid">

        <div class="form-group full-width">
            <label for="title">
                Task Title <span>*</span>
            </label>

            <input type="text" id="title" name="title" value="{{ old('title', $task->title ?? '') }}"
                placeholder="Enter task title" maxlength="255" required>
        </div>

        <div class="form-group">
            <label for="assigned_to">
                Assign To
            </label>

            <select id="assigned_to" name="assigned_to">
                <option value="">
                    Unassigned
                </option>

                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(
                        (string) old(
                            'assigned_to',
                            $task->assigned_to ?? ''
                        ) === (string) $user->id
                    )>
                        {{ $user->name }}
                        ({{ $user->email }})

                        @if (!$user->is_active)
                            - Inactive
                        @endif
                    </option>
                @endforeach
            </select>

            @if ($users->isEmpty())
                <small class="form-help-text">
                    Add users to the project team before assigning this task.
                </small>
            @endif
        </div>

        <!-- <div class="form-group">
            <label for="priority">
                Priority <span>*</span>
            </label>

            <select id="priority" name="priority" required>
                @foreach ($priorities as $priorityKey => $priorityLabel)
                    <option value="{{ $priorityKey }}" @selected(
                        $selectedPriority
                        === $priorityKey
                    )>
                        {{ $priorityLabel }}
                    </option>
                @endforeach
            </select>
        </div> -->

        <div class="form-group">

            <label for="priority">
                Priority <span>*</span>
            </label>

            <select id="priority" name="priority" required>
                @foreach(
                        $priorities
                        as $priorityKey => $priorityLabel
                    )
                    <option value="{{ $priorityKey }}" @selected(
                        $selectedPriority
                        === $priorityKey
                    )>
                        {{ $priorityLabel }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="form-group">
            <label for="start_date">
                Start Date
            </label>

            <input type="date" id="start_date" name="start_date" value="{{ $startDateValue }}">
        </div>

        <div class="form-group">
            <label for="due_at">
                Due Date & Time
            </label>

            <input type="datetime-local" id="due_at" name="due_at" value="{{ $dueAtValue }}">
        </div>

        <div class="form-group">
            <label for="estimated_hours">
                Estimated Hours
            </label>

            <input type="number" id="estimated_hours" name="estimated_hours" value="{{ old(
    'estimated_hours',
    $task->estimated_hours ?? ''
) }}" placeholder="Example: 4" min="0" step="0.5">
        </div>

        <div class="form-group">
            <label for="reviewer_id">
                Reviewer
            </label>

            <select id="reviewer_id" name="reviewer_id">
                <option value="">
                    No Specific Reviewer
                </option>

                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(
                        (string) $selectedReviewer
                        === (string) $user->id
                    )>
                        {{ $user->name }}
                        ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group full-width">
            <div class="task-review-option">

                <input type="hidden" name="requires_review" value="0">

                <label for="requires_review">
                    <input type="checkbox" id="requires_review" name="requires_review" value="1"
                        @checked($requiresReview)>

                    <span>
                        <strong>
                            Task requires review
                        </strong>

                        <small>
                            Assigned user task complete karke review ke liye submit karega.
                        </small>
                    </span>
                </label>

            </div>
        </div>

        <div class="form-group full-width">
            <label for="description">
                Task Description
            </label>

            <textarea id="description" name="description" rows="7"
                placeholder="Enter task requirements, instructions or expected result">{{ old('description', $task->description ?? '') }}</textarea>
        </div>

    </div>

    <div class="form-actions">

        <button type="submit" class="primary-btn">
            {{ $isEdit ? 'Update Task' : 'Save Task' }}
        </button>

        <!-- <a href="{{ $isEdit
    ? route('task.show', $task->id)
    : route('project.show', $project->id) }}" class="cancel-btn">
            Cancel
        </a> -->


        <a href="{{ $isEdit
    ? route('task.show', ['task' => $task->id, 'from' => request('from')])
    : route('project.show', $project->id) }}" class="cancel-btn">
            Cancel
        </a>
    </div>

</form>