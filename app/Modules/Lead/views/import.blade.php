@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset(
        'css/modules/user.css'
    ) }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset(
        'css/modules/lead.css'
    ) }}?v={{ time() }}">
@endpush

@section('content')

    <div class="content-card lead-import-page">

        {{-- Header --}}
        <div class="page-card-header">

            <div>
                <h1>Import Leads</h1>

                <p>
                    Import bulk leads into your CRM using Excel or CSV files.
                </p>
            </div>

            <div class="lead-header-actions">

                <a href="{{ route(
        'lead.import.template'
    ) }}" class="secondary-btn">
                    Download Template
                </a>

                <a href="{{ route(
        'lead.index'
    ) }}" class="secondary-btn">
                    Back to Leads
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
                        <li>{{ $error }}</li>
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

            <section class="lead-import-result">

                <div class="lead-import-result-grid">

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

                    <div class="lead-import-issues">

                        <div class="lead-import-section-header">
                            <div>
                                <h2>
                                    Skipped and Failed Rows
                                </h2>

                                <p>
                                    Only up to 100 rows can be displayed here.
                                </p>
                            </div>
                        </div>

                        <div class="table-wrapper">

                            <table class="admin-table lead-import-issue-table">

                                <thead>
                                    <tr>
                                        <th>Excel Row</th>
                                        <th>Type</th>
                                        <th>Lead</th>
                                        <th>Phone</th>
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
                                                            <span class="lead-import-issue-badge
                                                                                                            {{ strtolower(
                                            $issue['type']
                                        ) }}">
                                                                {{ $issue['type'] }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            {{ $issue['name']
                                            ?: '-' }}
                                                        </td>

                                                        <td>
                                                            {{ $issue['phone']
                                            ?: '-' }}
                                                        </td>

                                                        <td>
                                                            <ul class="lead-import-message-list">

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

        <div class="lead-import-layout">

            {{-- Upload Form --}}
            <section class="lead-import-card">

                <div class="lead-import-section-header">

                    <div>
                        <h2>Upload Lead Excel File</h2>

                        <p>
                            Supported formats:
                            XLSX, XLS and CSV.
                        </p>
                    </div>

                </div>

                <form action="{{ route(
        'lead.import.store'
    ) }}" method="POST" enctype="multipart/form-data" class="lead-import-form">
                    @csrf

                    <div class="form-group">

                        <label for="lead_import_file">
                            Excel File
                            <span>*</span>
                        </label>

                        <input type="file" id="lead_import_file" name="file" accept=".xlsx,.xls,.csv" required>

                        <small>
                            Maximum file size: 10 MB.
                            Maximum records: 5000.
                        </small>

                    </div>

                    <div class="form-group">

                        <label for="duplicate_mode">
                            Duplicate Handling
                            <span>*</span>
                        </label>

                        <select id="duplicate_mode" name="duplicate_mode" required>
                            <option value="skip">
                                Skip Duplicate Records
                            </option>

                            <option value="update">
                                Update Existing Records
                            </option>
                        </select>

                        <small>
                            Duplicate matching will be performed using the phone number and email address.
                        </small>

                    </div>

                    <div class="lead-import-warning">

                        <span>⚠</span>

                        <div>
                            <strong>
                                Important Import Rule
                            </strong>

                            <p>
                                Lead conversion via Excel is not supported. Please use the CRM "Convert" button.
                            </p>
                        </div>

                    </div>

                    <div class="form-actions">

                        <button type="submit" class="primary-btn">
                            Import Leads
                        </button>

                        <a href="{{ route(
        'lead.index'
    ) }}" class="cancel-btn">
                            Cancel
                        </a>

                    </div>

                </form>

            </section>

            {{-- Instructions --}}
            <section class="lead-import-card">

                <div class="lead-import-section-header">

                    <div>
                        <h2>Excel Column Guide</h2>

                        <p>
                            Please do not change the template headings..
                        </p>
                    </div>

                </div>

                <div class="lead-import-column-list">

                    <div>
                        <strong>name</strong>
                        <span>Required</span>
                    </div>

                    <div>
                        <strong>phone</strong>
                        <span>Required</span>
                    </div>

                    <div>
                        <strong>email</strong>
                        <span>Optional</span>
                    </div>

                    <div>
                        <strong>company</strong>
                        <span>Optional</span>
                    </div>

                    <div>
                        <strong>source</strong>
                        <span>Default: other</span>
                    </div>

                    <div>
                        <strong>status</strong>
                        <span>
                            Default:
                            {{ $defaultStatus }}
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
                        <strong>assigned_employee_email</strong>

                        <span>
                            {{ $canAssign
        ? 'Optional'
        : 'Ignored — assigned to you' }}
                        </span>
                    </div>

                    <div>
                        <strong>next_follow_up_at</strong>
                        <span>YYYY-MM-DD HH:MM</span>
                    </div>

                    <div>
                        <strong>notes</strong>
                        <span>Optional</span>
                    </div>

                </div>

                <div class="lead-import-values">

                    <h3>Allowed Source Values</h3>

                    <p>
                        {{ implode(
        ', ',
        array_keys($sources)
    ) }}
                    </p>

                    <h3>Allowed Status Values</h3>

                    <p>
                        {{ implode(
        ', ',
        array_keys($statuses)
    ) }}
                    </p>

                    <h3>Allowed Priority Values</h3>

                    <p>
                        {{ implode(
        ', ',
        array_keys($priorities)
    ) }}
                    </p>

                </div>

            </section>

        </div>

    </div>

@endsection