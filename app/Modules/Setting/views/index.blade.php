@extends('admin::layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset(
        'css/modules/setting.css'
    ) }}?v={{ time() }}">
@endpush

@section('content')

    <div class="content-card setting-page">

        <div class="page-card-header">

            <div>
                <h1>CRM Settings</h1>

                <p>
                    Manage company information,
                    regional preferences and CRM branding.
                </p>
            </div>

            <span class="setting-security-badge">
                Admin Configuration
            </span>

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
                    Please fix the following errors:
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

        <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data" class="setting-form">
            @csrf
            @method('PUT')

            <div class="setting-layout">

                {{-- Left Settings Navigation --}}
                <aside class="setting-navigation">

                    <a href="#general-settings">
                        <span>🏢</span>

                        <div>
                            <strong>General</strong>
                            <small>Company information</small>
                        </div>
                    </a>

                    <a href="#regional-settings">
                        <span>🌍</span>

                        <div>
                            <strong>Regional</strong>
                            <small>Timezone and currency</small>
                        </div>
                    </a>

                    <a href="#branding-settings">
                        <span>🎨</span>

                        <div>
                            <strong>Branding</strong>
                            <small>Logo and colours</small>
                        </div>
                    </a>

                    <a href="#login-settings">
                        <span>🔐</span>

                        <div>
                            <strong>Login Page</strong>
                            <small>Login branding</small>
                        </div>
                    </a>

                    <a href="{{ route(
        'setting.lead-options.index'
    ) }}">
                        <span>📌</span>

                        <div>
                            <strong>Lead Options</strong>

                            <small>
                                Status and priority
                            </small>
                        </div>
                    </a>

                    <a href="{{ route(
        'setting.task-options.index'
    ) }}">
                        <span>✅</span>

                        <div>
                            <strong>Task Options</strong>

                            <small>
                                Status and priority
                            </small>
                        </div>
                    </a>

                </aside>

                {{-- Right Settings Content --}}
                <main class="setting-content">

                    {{-- General --}}
                    <section class="setting-section" id="general-settings">

                        <div class="setting-section-header">

                            <div>
                                <h2>
                                    General Company Information
                                </h2>

                                <p>
                                    This information will be used for CRM branding and the company profile.
                                </p>
                            </div>

                            <span>01</span>

                        </div>

                        <div class="setting-form-grid">

                            <div class="form-group">

                                <label for="company_name">
                                    Company Name
                                    <span>*</span>
                                </label>

                                <input type="text" id="company_name" name="company_name" maxlength="150" value="{{ old(
        'company_name',
        $settings->get(
            'company_name'
        )
    ) }}" required>

                            </div>

                            <div class="form-group">

                                <label for="company_email">
                                    Business Email
                                </label>

                                <input type="email" id="company_email" name="company_email" value="{{ old(
        'company_email',
        $settings->get(
            'company_email'
        )
    ) }}" placeholder="info@example.com">

                            </div>

                            <div class="form-group">

                                <label for="company_phone">
                                    Business Phone
                                </label>

                                <input type="text" id="company_phone" name="company_phone" maxlength="30" value="{{ old(
        'company_phone',
        $settings->get(
            'company_phone'
        )
    ) }}" placeholder="+91 98765 43210">

                            </div>

                            <div class="form-group">

                                <label for="company_website">
                                    Website
                                </label>

                                <input type="url" id="company_website" name="company_website" value="{{ old(
        'company_website',
        $settings->get(
            'company_website'
        )
    ) }}" placeholder="https://example.com">

                            </div>

                            <div class="form-group full-width">

                                <label for="company_address">
                                    Company Address
                                </label>

                                <textarea id="company_address" name="company_address" rows="4" maxlength="2000"
                                    placeholder="Enter complete business address">{{ old(
        'company_address',
        $settings->get(
            'company_address'
        )
    ) }}</textarea>

                            </div>

                        </div>

                    </section>

                    {{-- Regional --}}
                    <section class="setting-section" id="regional-settings">

                        <div class="setting-section-header">

                            <div>
                                <h2>
                                    Regional Settings
                                </h2>

                                <p>
                                    CRM date, time and currency
                                    presentation control karein.
                                </p>
                            </div>

                            <span>02</span>

                        </div>

                        <div class="setting-form-grid">

                            <div class="form-group full-width">

                                <label for="timezone">
                                    Timezone
                                    <span>*</span>
                                </label>

                                <select id="timezone" name="timezone" required>
                                    @foreach(
                                            $timezones
                                            as $timezone
                                        )
                                        <option value="{{ $timezone }}" @selected(
                                            old(
                                                'timezone',
                                                $settings->get(
                                                    'timezone'
                                                )
                                            ) === $timezone
                                        )>
                                            {{ $timezone }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="form-group">

                                <label for="date_format">
                                    Date Format
                                    <span>*</span>
                                </label>

                                <select id="date_format" name="date_format" required>
                                    @foreach(
                                            $dateFormats
                                            as $format => $label
                                        )
                                        <option value="{{ $format }}" @selected(
                                            old(
                                                'date_format',
                                                $settings->get(
                                                    'date_format'
                                                )
                                            ) === $format
                                        )>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="form-group">

                                <label for="time_format">
                                    Time Format
                                    <span>*</span>
                                </label>

                                <select id="time_format" name="time_format" required>
                                    @foreach(
                                            $timeFormats
                                            as $format => $label
                                        )
                                        <option value="{{ $format }}" @selected(
                                            old(
                                                'time_format',
                                                $settings->get(
                                                    'time_format'
                                                )
                                            ) === $format
                                        )>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="form-group">

                                <label for="currency_code">
                                    Currency Code
                                    <span>*</span>
                                </label>

                                <input type="text" id="currency_code" name="currency_code" maxlength="3" value="{{ old(
        'currency_code',
        $settings->get(
            'currency_code'
        )
    ) }}" placeholder="INR" required>

                            </div>

                            <div class="form-group">

                                <label for="currency_symbol">
                                    Currency Symbol
                                    <span>*</span>
                                </label>

                                <input type="text" id="currency_symbol" name="currency_symbol" maxlength="10" value="{{ old(
        'currency_symbol',
        $settings->get(
            'currency_symbol'
        )
    ) }}" placeholder="₹" required>

                            </div>

                        </div>

                    </section>

                    {{-- Branding --}}
                    <section class="setting-section" id="branding-settings">

                        <div class="setting-section-header">

                            <div>
                                <h2>
                                    CRM Branding
                                </h2>

                                <p>
                                    Sidebar, browser and CRM theme
                                    branding customize karein.
                                </p>
                            </div>

                            <span>03</span>

                        </div>

                        <div class="setting-upload-grid">

                            {{-- Company Logo --}}
                            <div class="setting-upload-card">

                                <div class="setting-file-preview">

                                    @if(
                                                                        $settings->get(
                                                                            'company_logo'
                                                                        )
                                                                    )
                                                                    <img src="{{ asset(
                                            'storage/'
                                            . $settings->get(
                                                'company_logo'
                                            )
                                        ) }}" alt="Company Logo">
                                    @else
                                        <span>LOGO</span>
                                    @endif

                                </div>

                                <div class="form-group">

                                    <label for="company_logo">
                                        Company Logo
                                    </label>

                                    <input type="file" id="company_logo" name="company_logo" accept=".png,.jpg,.jpeg,.webp">

                                    <small>
                                        PNG, JPG, JPEG or WEBP.
                                        Maximum 2 MB.
                                    </small>

                                </div>

                                @if(
                                        $settings->get(
                                            'company_logo'
                                        )
                                    )
                                    <label class="setting-remove-option">

                                        <input type="checkbox" name="remove_company_logo" value="1">

                                        Remove current logo

                                    </label>
                                @endif

                            </div>

                            {{-- Favicon --}}
                            <div class="setting-upload-card">

                                <div class="setting-file-preview favicon">

                                    @if(
                                                                        $settings->get(
                                                                            'favicon'
                                                                        )
                                                                    )
                                                                    <img src="{{ asset(
                                            'storage/'
                                            . $settings->get(
                                                'favicon'
                                            )
                                        ) }}" alt="Favicon">
                                    @else
                                        <span>ICON</span>
                                    @endif

                                </div>

                                <div class="form-group">

                                    <label for="favicon">
                                        Browser Favicon
                                    </label>

                                    <input type="file" id="favicon" name="favicon" accept=".png,.jpg,.jpeg,.webp,.ico">

                                    <small>
                                        PNG, ICO, JPG or WEBP.
                                        Maximum 1 MB.
                                    </small>

                                </div>

                                @if(
                                        $settings->get(
                                            'favicon'
                                        )
                                    )
                                    <label class="setting-remove-option">

                                        <input type="checkbox" name="remove_favicon" value="1">

                                        Remove current favicon

                                    </label>
                                @endif

                            </div>

                        </div>

                        <div class="setting-form-grid">

                            <div class="form-group">

                                <label for="primary_color">
                                    Primary Brand Color
                                    <span>*</span>
                                </label>

                                <div class="setting-color-field">

                                    <input type="color" id="primary_color_picker" value="{{ old(
        'primary_color',
        $settings->get(
            'primary_color'
        )
    ) }}" oninput="
                                                document.getElementById(
                                                    'primary_color'
                                                ).value = this.value.toUpperCase()
                                            ">

                                    <input type="text" id="primary_color" name="primary_color" maxlength="7" value="{{ old(
        'primary_color',
        $settings->get(
            'primary_color'
        )
    ) }}" required>

                                </div>

                            </div>

                            <div class="form-group">

                                <label for="secondary_color">
                                    Secondary Brand Color
                                    <span>*</span>
                                </label>

                                <div class="setting-color-field">

                                    <input type="color" id="secondary_color_picker" value="{{ old(
        'secondary_color',
        $settings->get(
            'secondary_color'
        )
    ) }}" oninput="
                                                document.getElementById(
                                                    'secondary_color'
                                                ).value = this.value.toUpperCase()
                                            ">

                                    <input type="text" id="secondary_color" name="secondary_color" maxlength="7" value="{{ old(
        'secondary_color',
        $settings->get(
            'secondary_color'
        )
    ) }}" required>

                                </div>

                            </div>

                            <div class="form-group">

                                <label for="sidebar_subtitle">
                                    Sidebar Subtitle
                                </label>

                                <input type="text" id="sidebar_subtitle" name="sidebar_subtitle" maxlength="100" value="{{ old(
        'sidebar_subtitle',
        $settings->get(
            'sidebar_subtitle'
        )
    ) }}" placeholder="Admin Panel">

                            </div>

                            <div class="form-group">

                                <label for="footer_text">
                                    Footer Text
                                </label>

                                <input type="text" id="footer_text" name="footer_text" maxlength="255" value="{{ old(
        'footer_text',
        $settings->get(
            'footer_text'
        )
    ) }}" placeholder="Powered by PRO CRM">

                            </div>

                            <div class="form-group full-width">

                                <input type="hidden" name="show_company_logo" value="0">

                                <label class="setting-toggle-option">

                                    <input type="checkbox" name="show_company_logo" value="1" @checked(
                                        old(
                                            'show_company_logo',
                                            $settings->get(
                                                'show_company_logo'
                                            )
                                        )
                                    )>

                                    <span>
                                        <strong>
                                            Show company logo
                                        </strong>

                                        <small>
                                            The uploaded logo will be displayed on the Admin Sidebar and Login Page.
                                        </small>
                                    </span>

                                </label>

                            </div>

                        </div>

                    </section>

                    {{-- Login --}}
                    <section class="setting-section" id="login-settings">

                        <div class="setting-section-header">

                            <div>
                                <h2>
                                    Login Page Settings
                                </h2>

                                <p>
                                    Customize the login screen heading and message.
                                </p>
                            </div>

                            <span>04</span>

                        </div>

                        <div class="setting-form-grid">

                            <div class="form-group full-width">

                                <label for="login_heading">
                                    Login Heading
                                    <span>*</span>
                                </label>

                                <input type="text" id="login_heading" name="login_heading" maxlength="150" value="{{ old(
        'login_heading',
        $settings->get(
            'login_heading'
        )
    ) }}" required>

                            </div>

                            <div class="form-group full-width">

                                <label for="login_description">
                                    Login Description
                                    <span>*</span>
                                </label>

                                <textarea id="login_description" name="login_description" rows="5" maxlength="1000"
                                    required>{{ old(
        'login_description',
        $settings->get(
            'login_description'
        )
    ) }}</textarea>

                            </div>

                        </div>

                    </section>

                    {{-- Save Bar --}}
                    <div class="setting-save-bar">

                        <div>
                            <strong>
                                Save CRM Configuration
                            </strong>

                            <span>
                                The changes will be applied in the CRM on the next page load.
                            </span>
                        </div>

                        @if(
                                auth()->user()->hasPermission(
                                    'settings.update'
                                )
                            )
                            <button type="submit" class="primary-btn">
                                Save Settings
                            </button>
                        @endif

                    </div>

                </main>

            </div>

        </form>

    </div>

@endsection