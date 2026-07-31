<x-guest-layout>
    @php
        $crmSettings = collect(
            $crmSettings ?? []
        );

        $crmCompanyName =
            $crmSettings->get(
                'company_name',
                'PRO CRM'
            );

        $crmCompanyLogo =
            $crmSettings->get(
                'company_logo'
            );

        $crmShowLogo =
            filter_var(
                $crmSettings->get(
                    'show_company_logo',
                    '1'
                ),
                FILTER_VALIDATE_BOOLEAN
            );

        $crmPrimaryColor =
            $crmSettings->get(
                'primary_color',
                '#2563EB'
            );

        $crmSecondaryColor =
            $crmSettings->get(
                'secondary_color',
                '#0F172A'
            );

        $crmLoginHeading =
            $crmSettings->get(
                'login_heading',
                'Welcome Back, Admin'
            );

        $crmLoginDescription =
            $crmSettings->get(
                'login_description',
                'Login to manage your CRM securely.'
            );

        $crmSidebarSubtitle =
            $crmSettings->get(
                'sidebar_subtitle',
                'Admin Panel'
            );
    @endphp
    <style>
        :root {
            --crm-primary:
                {{ $crmPrimaryColor }}
            ;

            --crm-secondary:
                {{ $crmSecondaryColor }}
            ;
        }

        body {
            margin: 0;
            background: #eef3f9;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Breeze default wrapper ko clean karne ke liye */
        body>div>div:first-child {
            display: none !important;
        }

        [class*="sm:max-w-md"] {
            max-width: 980px !important;
            width: calc(100% - 40px) !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            overflow: visible !important;
            border-radius: 0 !important;
        }

        [class*="min-h-screen"] {
            background: linear-gradient(135deg, #eaf1fb 0%, #f8fafc 45%, #eef4ff 100%) !important;
            padding: 24px !important;
        }

        .login-wrapper {
            width: 100%;
            min-height: 580px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.14);
        }

        .login-left {
            background: linear-gradient(145deg,
                    #020617 0%,
                    var(--crm-secondary) 55%,
                    var(--crm-primary) 100%);
            padding: 52px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.35);
            right: -80px;
            top: -80px;
        }

        .login-left::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            left: -60px;
            bottom: -60px;
        }

        .brand-box {
            position: relative;
            z-index: 2;
        }

        .brand-box h1 {
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 1px;
            margin: 0;
        }

        .brand-box p {
            margin-top: 8px;
            color: #cbd5e1;
            font-size: 15px;
        }

        .login-info {
            position: relative;
            z-index: 2;
            margin-top: 90px;
        }

        .login-info h2 {
            font-size: 42px;
            line-height: 1.15;
            font-weight: 900;
            margin: 0;
        }

        .login-info p {
            margin-top: 18px;
            color: #dbeafe;
            font-size: 16px;
            line-height: 1.7;
            max-width: 420px;
        }

        .feature-list {
            position: relative;
            z-index: 2;
            margin-top: 42px;
            display: grid;
            gap: 14px;
        }

        .feature-list span {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #e0f2fe;
            font-size: 15px;
        }

        .login-right {
            padding: 58px 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
        }

        .login-form-box {
            width: 100%;
            max-width: 390px;
        }

        .form-heading {
            margin-bottom: 30px;
        }

        .form-heading h2 {
            font-size: 32px;
            font-weight: 900;
            color: #020617;
            margin: 0;
        }

        .form-heading p {
            margin-top: 8px;
            color: #64748b;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .login-label {
            display: block !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            margin-bottom: 8px !important;
        }

        .login-input {
            width: 100% !important;
            height: 48px !important;
            border-radius: 12px !important;
            border: 1px solid #cbd5e1 !important;
            background: #f8fafc !important;
            color: #0f172a !important;
            padding: 0 15px !important;
            font-size: 15px !important;
            outline: none !important;
            box-shadow: none !important;
            transition: 0.2s ease !important;
        }

        .login-input:focus {
            border-color: #2563eb !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12) !important;
        }

        /* Password Eye Icon */
        .password-field {
            position: relative;
        }

        .password-field .login-input {
            padding-right: 52px !important;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 0;
        }

        .password-toggle:hover {
            background: #eef2ff;
            color: #2563eb;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        .password-toggle .eye-off-icon {
            display: none;
        }

        .password-toggle.active .eye-icon {
            display: none;
        }

        .password-toggle.active .eye-off-icon {
            display: block;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 6px;
            margin-bottom: 24px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
        }

        .remember-label input {
            width: 16px;
            height: 16px;
            accent-color: #2563eb;
        }

        .remember-label span {
            font-size: 14px;
            color: #475569;
        }

        .forgot-link {
            font-size: 14px;
            color: #2563eb !important;
            font-weight: 700;
            text-decoration: none !important;
        }

        .forgot-link:hover {
            color: #1d4ed8 !important;
            text-decoration: underline !important;
        }

        .login-submit {
            width: 100% !important;
            height: 50px !important;
            border-radius: 14px !important;
            background: var(--crm-primary) !important;
            color: #ffffff !important;
            font-size: 15px !important;
            font-weight: 800 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: none !important;
            cursor: pointer !important;
            margin: 0 !important;
            transition: 0.2s ease !important;
            text-transform: none !important;
            letter-spacing: 0 !important;
        }

        .login-submit:hover {
            filter: brightness(0.9);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.28);
        }

        .security-note {
            margin-top: 22px;
            padding: 14px;
            border-radius: 14px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-text,
        .mt-2 {
            font-size: 13px !important;
            color: #dc2626 !important;
        }

        @media (max-width: 850px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-left {
                padding: 34px;
            }

            .login-info {
                margin-top: 45px;
            }

            .login-info h2 {
                font-size: 32px;
            }

            .login-right {
                padding: 38px 28px;
            }
        }

        .brand-box img {
            display: block;

            max-width: 220px;
            max-height: 85px;

            object-fit: contain;
        }

        .brand-box-logo-name {
            margin-top: 12px !important;

            color: #ffffff !important;

            font-size: 18px !important;
            font-weight: 800;
        }
    </style>

    <div class="login-wrapper">

        <div class="login-left">
            <div class="brand-box">

                @if(
                                    $crmShowLogo
                                    && $crmCompanyLogo
                                )
                                <img src="{{ asset(
                        'storage/'
                        . $crmCompanyLogo
                    ) }}" alt="{{ $crmCompanyName }}">

                                <p class="brand-box-logo-name">
                                    {{ $crmCompanyName }}
                                </p>
                @else
                    <h1>
                        {{ $crmCompanyName }}
                    </h1>
                @endif

                <p>
                    {{ $crmSidebarSubtitle }}
                </p>

            </div>

            <div class="login-info">

                <h2>
                    {{ $crmLoginHeading }}
                </h2>

                <p>
                    {{ $crmLoginDescription }}
                </p>

            </div>

            <div class="feature-list">
                <span>✅ Secure admin access</span>
                <span>✅ Manage CRM modules easily</span>
                <span>✅ Clean and professional dashboard</span>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-box">

                <div class="form-heading">
                    <h2>Sign In</h2>
                    <p>Enter your login details to continue.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="form-group">
                        <x-input-label for="email" :value="__('Email')" class="login-label" />

                        <x-text-input id="email" class="login-input" type="email" name="email" :value="old('email')"
                            required autofocus autocomplete="username" />

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <x-input-label for="password" :value="__('Password')" class="login-label" />

                        <div class="password-field">
                            <x-text-input id="password" class="login-input" type="password" name="password" required
                                autocomplete="current-password" />

                            <button type="button" class="password-toggle" id="togglePassword"
                                aria-label="Show password">
                                <!-- Eye Icon -->
                                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>

                                <!-- Eye Off Icon -->
                                <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95M6.228 6.228A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.197M9.88 9.88a3 3 0 104.24 4.24" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me + Forgot Password -->
                    <div class="remember-row">
                        <label for="remember_me" class="remember-label">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <x-primary-button class="login-submit">
                        {{ __('Log in') }}
                    </x-primary-button>

                    <div class="security-note">
                        🔐 Your CRM access is protected. Please logout after completing your work.
                    </div>
                </form>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("password");
            const togglePassword = document.getElementById("togglePassword");

            if (passwordInput && togglePassword) {
                togglePassword.addEventListener("click", function () {
                    const isPasswordHidden = passwordInput.getAttribute("type") === "password";

                    passwordInput.setAttribute("type", isPasswordHidden ? "text" : "password");
                    togglePassword.classList.toggle("active");

                    togglePassword.setAttribute(
                        "aria-label",
                        isPasswordHidden ? "Hide password" : "Show password"
                    );
                });
            }
        });
    </script>
</x-guest-layout>