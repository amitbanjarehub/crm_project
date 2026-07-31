@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset(
        'css/modules/user.css'
    ) }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset(
        'css/modules/task.css'
    ) }}?v={{ time() }}">
@endpush

@section('content')

    <div class="content-card task-import-page">

        {{-- Header --}}
        <div class="page-card-header">

            <div>
                <h1>Import Tasks</h1>

                <p>
                    Bulk create project service tasks by importing an Excel or CSV file.
                </p>
            </div>

            <div class="task-header-actions">

                <a href="{{ route(
        'task.import.template'
    ) }}" class="secondary-btn">
                    Download Template
                </a>

                <a href="{{ route(
        'task.index'
    ) }}" class="secondary-btn">
                    Back to Tasks
                </a>

            </div>

        </div>

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

        @if($errors->any())
            <div class="alert alert-error">

                <strong>
                    Please fix these errors:
                </strong>

                <ul class="error-list">
                    @foreach(
                            $errors->all()
                            as $error
                        )
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>

            </div>
        @endif

        {{-- Import Result --}}
        @if(session('import_result'))

            @php
                $result =
                    session('import_result');
            @endphp

            <section class="task-import-result">

                <div class="task-import-result-grid">

                    <div>
                        <span>Total Rows</span>

                        <strong>
                            {{ $result['total'] }}
                        </strong>
                    </div>

                    <div class="success">
                        <span>Imported</span>

                        <strong>
                            {{ $result['imported'] }}
                        </strong>
                    </div>

                    <div class="updated">
                        <span>Updated</span>

                        <strong>
                            {{ $result['updated'] }}
                        </strong>
                    </div>

                    <div class="warning">
                        <span>Skipped</span>

                        <strong>
                            {{ $result['skipped'] }}
                        </strong>
                    </div>

                    <div class="danger">
                        <span>Failed</span>

                        <strong>
                            {{ $result['failed'] }}
                        </strong>
                    </div>

                </div>

                @if(!empty($result['issues']))

                    <div class="task-import-issues">

                        <div class="task-import-section-header">

                            <h2>
                                Skipped and Failed Rows
                            </h2>

                            <p>
                                Only up to 100 rows can be displayed here.
                            </p>

                        </div>

                        <div class="table-wrapper">

                            <table class="admin-table task-import-issue-table">

                                <thead>
                                    <tr>
                                        <th>Excel Row</th>
                                        <th>Type</th>
                                        <th>Project</th>
                                        <th>Service</th>
                                        <th>Task</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach(
                                                        $result['issues']
                                                        as $issue
                                                    )
                                                    <tr>

                                                        <td>
                                                            {{ $issue['row'] }}
                                                        </td>

                                                        <td>
                                                            <span class="task-import-issue-badge
                                                                                                            {{ strtolower(
                                            $issue['type']
                                        ) }}">
                                                                {{ $issue['type'] }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            {{ $issue[
                                            'project_code'
                                        ] ?: '-' }}
                                                        </td>

                                                        <td>
                                                            {{ $issue[
                                            'service'
                                        ] ?: '-' }}
                                                        </td>

                                                        <td>
                                                            {{ $issue[
                                            'title'
                                        ] ?: '-' }}
                                                        </td>

                                                        <td>
                                                            <ul class="task-import-message-list">

                                                                @foreach(
                                                                        $issue['messages']
                                                                        as $message
                                                                    )
                                                                    <li>
                                                                        {{ $message }}
                                                                    </li>
                                                                @endforeach

                                                            </ul>
                                                        </td>

                                                    </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @endif

            </section>

        @endif

        <div class="task-import-layout">

            {{-- Upload Card --}}
            <section class="task-import-card">

                <div class="task-import-section-header">

                    <h2>
                        Upload Task Excel File
                    </h2>

                    <p>
                        Supported formats:
                        XLSX, XLS and CSV.
                    </p>

                </div>

                <form action="{{ route(
        'task.import.store'
    ) }}" method="POST" enctype="multipart/form-data" class="task-import-form">
                    @csrf

                    <div class="form-group">

                        <label for="task_import_file">
                            Excel File
                            <span>*</span>
                        </label>

                        <input type="file" id="task_import_file" name="file" accept=".xlsx,.xls,.csv" required>

                        <small>
                            Maximum size: 10 MB.
                            Maximum records: 2000.
                        </small>

                    </div>

                    <div class="form-group">

                        <label for="duplicate_mode">
                            Duplicate Handling
                            <span>*</span>
                        </label>

                        <select id="duplicate_mode" name="duplicate_mode" required>
                            <option value="skip" @selected(
                                old(
                                    'duplicate_mode',
                                    'skip'
                                ) === 'skip'
                            )>
                                Skip Duplicate Records
                            </option>

                            <option value="update" @selected(
                                old(
                                    'duplicate_mode'
                                ) === 'update'
                            )>
                                Update Existing Records
                            </option>
                        </select>

                        <small>
                            Duplicate matching:
                            Project + Service + Task Title.
                        </small>

                    </div>

                    <div class="task-import-warning">

                        <span>⚠</span>

                        <div>
                            <strong>
                                Safe Workflow Import
                            </strong>

                            <p>
                                New Tasks will be created with
                                “{{ $defaultStatusLabel }}” status
                                and 0% progress.

                                Status, dependencies, comments,
                                attachments and tracked time
                                will not be imported.
                            </p>
                        </div>

                    </div>

                    <div class="form-actions">

                        <button type="submit" class="primary-btn">
                            Import Tasks
                        </button>

                        <a href="{{ route(
        'task.index'
    ) }}" class="cancel-btn">
                            Cancel
                        </a>

                    </div>

                </form>

            </section>

            {{-- Column Guide --}}
            <section class="task-import-card">

                <div class="task-import-section-header">

                    <h2>
                        Excel Column Guide
                    </h2>

                    <p>
                        Please do not change the template heading names.
                    </p>

                </div>

                <div class="task-import-column-list">

                    <div>
                        <strong>project_code</strong>
                        <span>Required</span>
                    </div>

                    <div>
                        <strong>
                            project_service_name
                        </strong>
                        <span>Required</span>
                    </div>

                    <div>
                        <strong>title</strong>
                        <span>Required</span>
                    </div>

                    <div>
                        <strong>description</strong>
                        <span>Optional</span>
                    </div>

                    <div>
                        <strong>
                            assigned_employee_email
                        </strong>

                        <span>
                            {{ $canAssign
        ? 'Optional'
        : 'Ignored / self-assigned' }}
                        </span>
                    </div>

                    <div>
                        <strong>priority</strong>

                        <span>
                            Default:
                            {{ $defaultPriority }}
                        </span>
                    </div>

                    <div>
                        <strong>requires_review</strong>
                        <span>Default: yes</span>
                    </div>

                    <div>
                        <strong>reviewer_email</strong>

                        <span>
                            {{ $canAssign
        ? 'Optional'
        : 'Ignored' }}
                        </span>
                    </div>

                    <div>
                        <strong>start_date</strong>
                        <span>YYYY-MM-DD</span>
                    </div>

                    <div>
                        <strong>due_at</strong>
                        <span>YYYY-MM-DD HH:MM</span>
                    </div>

                    <div>
                        <strong>estimated_hours</strong>
                        <span>Example: 4.5</span>
                    </div>

                </div>

                <div class="task-import-values">

                    <h3>
                        Allowed Priority Values
                    </h3>

                    <p>
                        {{ implode(
        ', ',
        array_keys($priorities)
    ) }}
                    </p>

                    <h3>
                        Requires Review Values
                    </h3>

                    <p>
                        yes, no, 1, 0, true, false
                    </p>

                    <h3>
                        Assignment Rule
                    </h3>

                    <p>
                        The assigned employee and reviewer must be either the Project Manager or a Project Member of the
                        selected project.
                    </p>

                </div>

            </section>

        </div>

    </div>

@endsection