@extends('admin::layouts.app')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset(
            'css/modules/user.css'
        ) }}?v={{ time() }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset(
            'css/modules/client.css'
        ) }}?v={{ time() }}"
    >
@endpush

@section('content')

<div class="content-card client-import-page">

    {{-- Header --}}
    <div class="page-card-header">

        <div>
            <h1>Import Clients</h1>

            <p>
                Import clients into your CRM in bulk using Excel or CSV files.
            </p>
        </div>

        <div class="client-header-actions">

            <a
                href="{{ route(
                    'client.import.template'
                ) }}"
                class="secondary-btn"
            >
                Download Template
            </a>

            <a
                href="{{ route(
                    'client.index'
                ) }}"
                class="secondary-btn"
            >
                Back to Clients
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

    {{-- Import Results --}}
    @if(session('import_result'))

        @php
            $result =
                session('import_result');
        @endphp

        <section class="client-import-result">

            <div class="client-import-result-grid">

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

                <div class="client-import-issues">

                    <div class="client-import-section-header">

                        <h2>
                            Skipped and Failed Rows
                        </h2>

                        <p>
                            A maximum of 100 issues will be displayed here.
                        </p>

                    </div>

                    <div class="table-wrapper">

                        <table class="admin-table client-import-issue-table">

                            <thead>
                                <tr>
                                    <th>Excel Row</th>
                                    <th>Type</th>
                                    <th>Client</th>
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
                                            <span
                                                class="client-import-issue-badge
                                                    {{ strtolower(
                                                        $issue['type']
                                                    ) }}"
                                            >
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
                                            <ul class="client-import-message-list">

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

    <div class="client-import-layout">

        {{-- Upload Card --}}
        <section class="client-import-card">

            <div class="client-import-section-header">

                <h2>Upload Client Excel File</h2>

                <p>
                    Supported formats:
                    XLSX, XLS and CSV.
                </p>

            </div>

            <form
                action="{{ route(
                    'client.import.store'
                ) }}"
                method="POST"
                enctype="multipart/form-data"
                class="client-import-form"
            >
                @csrf

                <div class="form-group">

                    <label for="client_import_file">
                        Excel File
                        <span>*</span>
                    </label>

                    <input
                        type="file"
                        id="client_import_file"
                        name="file"
                        accept=".xlsx,.xls,.csv"
                        required
                    >

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

                    <select
                        id="duplicate_mode"
                        name="duplicate_mode"
                        required
                    >
                        <option
                            value="skip"
                            @selected(
                                old(
                                    'duplicate_mode',
                                    'skip'
                                ) === 'skip'
                            )
                        >
                            Skip Duplicate Records
                        </option>

                        <option
                            value="update"
                            @selected(
                                old(
                                    'duplicate_mode'
                                ) === 'update'
                            )
                        >
                            Update Existing Records
                        </option>
                    </select>

                    <small>
                        Duplicate matching will be performed using the phone number and email address.
                    </small>

                </div>

                <div class="client-import-warning">

                    <span>⚠</span>

                    <div>
                        <strong>
                            Important Client Rule
                        </strong>

                        <p>
                            Excel imported Clients manual
                            Clients ki tarah create honge.
                            Lead-to-Client relation Excel se
                            create nahi hogi.
                        </p>
                    </div>

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="primary-btn"
                    >
                        Import Clients
                    </button>

                    <a
                        href="{{ route(
                            'client.index'
                        ) }}"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </section>

        {{-- Column Guide --}}
        <section class="client-import-card">

            <div class="client-import-section-header">

                <h2>Excel Column Guide</h2>

                <p>
                    Please do not change the template headings.
                </p>

            </div>

            <div class="client-import-column-list">

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
                    <strong>status</strong>
                    <span>Default: active</span>
                </div>

                <div>
                    <strong>
                        assigned_employee_email
                    </strong>

                    <span>
                        {{ $canAssign
                            ? 'Optional'
                            : 'Ignored — assigned to you' }}
                    </span>
                </div>

                <div>
                    <strong>notes</strong>
                    <span>Optional</span>
                </div>

            </div>

            <div class="client-import-values">

                <h3>
                    Allowed Status Values
                </h3>

                <p>
                    {{ implode(
                        ', ',
                        array_keys($statuses)
                    ) }}
                </p>

                <h3>
                    Duplicate Matching
                </h3>

                <p>
                    Phone is matched first, then Email. If they belong to different clients, the row will fail.
                </p>

            </div>

        </section>

    </div>

</div>

@endsection