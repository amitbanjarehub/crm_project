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
            'css/modules/followup.css'
        ) }}?v={{ time() }}"
    >
@endpush

@section('content')

<div class="content-card followup-import-page">

    <div class="page-card-header">

        <div>
            <h1>Import Follow-ups</h1>

            <p>
                Import lead interaction history from Excel or CSV files.
            </p>
        </div>

        <div class="followup-header-actions">

            <a
                href="{{ route(
                    'followup.import.template'
                ) }}"
                class="secondary-btn"
            >
                Download Template
            </a>

            <a
                href="{{ route(
                    'followup.index'
                ) }}"
                class="secondary-btn"
            >
                Back to Follow-ups
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

    @if(session('import_result'))

        @php
            $result =
                session('import_result');
        @endphp

        <section class="followup-import-result">

            <div class="followup-import-result-grid">

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

                <div class="followup-import-issues">

                    <div class="followup-import-section-header">

                        <h2>
                            Skipped and Failed Rows
                        </h2>

                        <p>
                            Only the first 100 rows will be displayed.
                        </p>

                    </div>

                    <div class="table-wrapper">

                        <table class="admin-table followup-import-issue-table">

                            <thead>
                                <tr>
                                    <th>Excel Row</th>
                                    <th>Type</th>
                                    <th>Lead Phone</th>
                                    <th>Lead Email</th>
                                    <th>Follow-up Type</th>
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
                                                class="followup-import-issue-badge
                                                {{ strtolower(
                                                    $issue['type']
                                                ) }}"
                                            >
                                                {{ $issue['type'] }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $issue[
                                                'lead_phone'
                                            ] ?: '-' }}
                                        </td>

                                        <td>
                                            {{ $issue[
                                                'lead_email'
                                            ] ?: '-' }}
                                        </td>

                                        <td>
                                            {{ $issue[
                                                'follow_up_type'
                                            ] ?: '-' }}
                                        </td>

                                        <td>
                                            <ul class="followup-import-message-list">

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

    <div class="followup-import-layout">

        <section class="followup-import-card">

            <div class="followup-import-section-header">

                <h2>
                    Upload Follow-up Excel File
                </h2>

                <p>
                    Supported formats:
                    XLSX, XLS and CSV.
                </p>

            </div>

            <form
                action="{{ route(
                    'followup.import.store'
                ) }}"
                method="POST"
                enctype="multipart/form-data"
                class="followup-import-form"
            >
                @csrf

                <div class="form-group">

                    <label for="followup_import_file">
                        Excel File
                        <span>*</span>
                    </label>

                    <input
                        type="file"
                        id="followup_import_file"
                        name="file"
                        accept=".xlsx,.xls,.csv"
                        required
                    >

                    <small>
                        Maximum size: 10 MB.
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
                        Duplicate matching:
                        Lead + Follow-up Date + Type.
                    </small>

                </div>

                <div class="followup-import-warning">

                    <span>⚠</span>

                    <div>
                        <strong>
                            Important Follow-up Rule
                        </strong>

                        <p>
                            Matching leads must be available in the CRM. Follow-up imports will not be applied to converted leads.
                        </p>
                    </div>

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="primary-btn"
                    >
                        Import Follow-ups
                    </button>

                    <a
                        href="{{ route(
                            'followup.index'
                        ) }}"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </section>

        <section class="followup-import-card">

            <div class="followup-import-section-header">

                <h2>Excel Column Guide</h2>

                <p>
                    Please do not change the template headings.
                </p>

            </div>

            <div class="followup-import-column-list">

                <div>
                    <strong>lead_phone</strong>

                    <span>
                        Phone or Email required
                    </span>
                </div>

                <div>
                    <strong>lead_email</strong>

                    <span>
                        Email or Phone required
                    </span>
                </div>

                <div>
                    <strong>type</strong>
                    <span>Required</span>
                </div>

                <div>
                    <strong>followed_up_at</strong>
                    <span>Required date/time</span>
                </div>

                <div>
                    <strong>outcome</strong>
                    <span>Required</span>
                </div>

                <div>
                    <strong>notes</strong>
                    <span>Required</span>
                </div>

                <div>
                    <strong>next_follow_up_at</strong>
                    <span>Optional date/time</span>
                </div>

                <div>
                    <strong>performed_by_email</strong>

                    <span>
                        {{ $canChoosePerformer
                            ? 'Optional'
                            : 'Ignored — current user used' }}
                    </span>
                </div>

            </div>

            <div class="followup-import-values">

                <h3>
                    Allowed Type Values
                </h3>

                <p>
                    {{ implode(
                        ', ',
                        array_keys($types)
                    ) }}
                </p>

                <h3>
                    Allowed Outcome Values
                </h3>

                <p>
                    {{ implode(
                        ', ',
                        array_keys($outcomes)
                    ) }}
                </p>

                <h3>
                    Accepted Date Format
                </h3>

                <p>
                    2026-07-30 11:30
                </p>

            </div>

        </section>

    </div>

</div>

@endsection