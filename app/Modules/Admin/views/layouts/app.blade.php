@php
    $crmSettings = collect(
        $crmSettings ?? []
    );

    $crmCompanyName =
        $crmSettings->get(
            'company_name',
            'PRO CRM'
        );

    $crmSidebarSubtitle =
        $crmSettings->get(
            'sidebar_subtitle',
            'Admin Panel'
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

    $crmCompanyLogo =
        $crmSettings->get(
            'company_logo'
        );

    $crmFavicon =
        $crmSettings->get(
            'favicon'
        );

    $crmShowLogo =
        filter_var(
            $crmSettings->get(
                'show_company_logo',
                '1'
            ),
            FILTER_VALIDATE_BOOLEAN
        );

    $crmFooterText =
        $crmSettings->get(
            'footer_text',
            'Powered by PRO CRM'
        );
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{ $pageTitle ?? $title ?? 'Dashboard' }}
        | {{ $crmCompanyName }}
    </title>
    @if($crmFavicon)
        <link rel="icon" href="{{ asset(
            'storage/' . $crmFavicon
        ) }}">
    @endif

    <!-- <link rel="stylesheet" href="{{ asset('css/admin.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    <link rel="stylesheet" href="{{ asset(
    'css/modules/time-tracking.css'
) }}?v={{ filemtime(
    public_path(
        'css/modules/time-tracking.css'
    )
) }}">
    @stack('styles')
    <!-- <script src="{{ asset('js/admin.js') }}" defer></script> -->
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>

    <script src="{{ asset(
    'js/time-tracking.js'
) }}?v={{ filemtime(
    public_path(
        'js/time-tracking.js'
    )
) }}" defer></script>

    <style>
        :root {
            --crm-primary:
                {{ $crmPrimaryColor }}
            ;

            --crm-secondary:
                {{ $crmSecondaryColor }}
            ;
        }

        .sidebar-brand-logo {
            display: block;
            max-width: 150px;
            max-height: 60px;

            margin: 0 auto 8px;

            object-fit: contain;
        }

        .crm-layout-footer {
            padding: 16px 24px;

            color: #94a3b8;

            font-size: 11px;
            text-align: center;
        }

        .primary-btn {
            background:
                var(--crm-primary);
        }

        .sidebar-menu a.active {
            border-color:
                var(--crm-primary);

            background:
                var(--crm-primary);
        }
    </style>

</head>

<body>

    <div class="admin-wrapper">

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">

                @if(
                                    $crmShowLogo
                                    && $crmCompanyLogo
                                )
                                <img src="{{ asset(
                        'storage/'
                        . $crmCompanyLogo
                    ) }}" alt="{{ $crmCompanyName }}" class="sidebar-brand-logo">
                @else
                    <h1>
                        {{ $crmCompanyName }}
                    </h1>
                @endif

                @if($crmSidebarSubtitle)
                    <p>
                        {{ $crmSidebarSubtitle }}
                    </p>
                @endif

            </div>



            @php
                $loggedInUser = auth()->user();
            @endphp

            <nav class="sidebar-menu">

                @if ($loggedInUser->hasPermission('dashboard.view'))
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span>📊</span> Dashboard
                    </a>
                @endif

                @if ($loggedInUser->hasPermission('users.view'))
                    <a href="{{ route('user.index') }}" class="{{ request()->routeIs('user.*') ? 'active' : '' }}">
                        <span>👥</span> User Management
                    </a>
                @endif

                @if ($loggedInUser->hasPermission('roles.view'))
                    <a href="{{ route('role.index') }}" class="{{ request()->routeIs('role.*') ? 'active' : '' }}">
                        <span>🛡️</span> Role Management
                    </a>
                @endif

                @if ($loggedInUser->hasPermission('permissions.view'))
                    <a href="{{ route('permission.index') }}"
                        class="{{ request()->routeIs('permission.*') ? 'active' : '' }}">
                        <span>🔐</span> Permission Management
                    </a>
                @endif

                @if ($loggedInUser->hasPermission('leads.view'))
                    <a href="{{ route('lead.index') }}" class="{{ request()->routeIs('lead.*') ? 'active' : '' }}">
                        <span>📌</span> Lead Management
                    </a>
                @endif

                @if ($loggedInUser->hasPermission('follow_ups.view'))
                    <a href="{{ route('followup.index') }}" class="{{ request()->routeIs('followup.*') ? 'active' : '' }}">
                        <span>📞</span> Follow-up Management
                    </a>
                @endif

                @if ($loggedInUser->hasPermission('clients.view'))
                    <a href="{{ route('client.index') }}" class="{{ request()->routeIs('client.*') ? 'active' : '' }}">
                        <span>🏢</span> Client Management
                    </a>
                @endif

                @if ($loggedInUser->hasPermission('projects.view'))
                    <a href="{{ route('project.index') }}" class="{{ request()->routeIs('project.*') ? 'active' : '' }}">
                        <span>📁</span> Project Management
                    </a>
                @endif

                @if ($loggedInUser->hasPermission('tasks.view'))
                    <a href="{{ route('task.my') }}" class="{{ request()->routeIs('task.*') ? 'active' : '' }}">
                        <span>✅</span> My Tasks
                    </a>
                @endif

                @if(
                                    $loggedInUser->hasPermission(
                                        'time_tracking.view_own'
                                    )
                                )
                                <a href="{{ route(
                        'timetracking.index'
                    ) }}" class="{{ request()->routeIs(
                        'timetracking.*'
                    ) ? 'active' : '' }}">
                                    <span>⏱️</span>
                                    Time Tracking
                                </a>
                @endif

                @if(
                                    $loggedInUser->hasPermission(
                                        'reports.executive.view'
                                    )
                                )
                                <a href="{{ route(
                        'report.executive'
                    ) }}" class="{{ request()->routeIs(
                        'report.executive'
                    ) ? 'active' : '' }}">
                                    <span>📊</span>
                                    Executive Reports
                                </a>
                @endif

                @if(
                                    $loggedInUser->hasPermission(
                                        'reports.leads.view'
                                    )
                                )
                                <a href="{{ route(
                        'report.leads.index'
                    ) }}" class="{{ request()->routeIs(
                        'report.leads.*'
                    ) ? 'active' : '' }}">
                                    <span>📌</span>
                                    Lead Reports
                                </a>
                @endif

                @if(
                                    $loggedInUser->hasPermission(
                                        'reports.projects.view'
                                    )
                                )
                                <a href="{{ route(
                        'report.projects.index'
                    ) }}" class="{{ request()->routeIs(
                        'report.projects.*'
                    ) ? 'active' : '' }}">
                                    <span>📈</span>
                                    Project Reports
                                </a>
                @endif

                @if(
                                    $loggedInUser->hasPermission(
                                        'reports.followups.view'
                                    )
                                )
                                <a href="{{ route(
                        'report.followups.index'
                    ) }}" class="{{ request()->routeIs(
                        'report.followups.*'
                    ) ? 'active' : '' }}">
                                    <span>📞</span>
                                    Follow-up Reports
                                </a>
                @endif

                @if ($loggedInUser->hasPermission('settings.view'))
                    <a href="{{ route('setting.index') }}" class="{{ request()->routeIs('setting.*') ? 'active' : '' }}">
                        <span>⚙️</span> Settings
                    </a>
                @endif

            </nav>
        </aside>

        <!-- Main -->
        <main class="main-content">




            <!-- Header -->
            <header class="topbar">

                {{-- Left Side Page Information --}}
                <div>
                    <h2>
                        {{ $pageTitle ?? 'Dashboard' }}
                    </h2>

                    <p>
                        Welcome back, manage your CRM from here.
                    </p>
                </div>

                {{--
                Current logged-in user ki notification information.

                $loggedInUser variable sidebar ke upar already
                define kiya gaya hai:

                @php
                $loggedInUser = auth()->user();
                @endphp
                --}}
                @php
                    $topbarUnreadCount = $loggedInUser
                        ->unreadNotifications()
                        ->count();

                    // $topbarNotifications = $loggedInUser
                    //     ->notifications()
                    //     ->latest()
                    //     ->limit(6)
                    //     ->get();

                    $topbarNotifications = $loggedInUser
                        ->notifications()
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->limit(6)
                        ->get();
                @endphp

                {{-- Right Side Actions --}}
                <div class="topbar-actions">
                    @if(
                                            $loggedInUser->hasPermission(
                                                'time_tracking.use'
                                            )
                                        )
                                        <div class="global-time-tracker
                                                                                                                        time-tracking-hidden"
                                            id="globalTimeTracker" data-current-url="{{ route(
                            'timetracking.current'
                        ) }}" data-pause-url="{{ route(
                            'timetracking.pause'
                        ) }}" data-resume-url="{{ route(
                            'timetracking.resume'
                        ) }}" data-stop-url="{{ route(
                            'timetracking.stop'
                        ) }}">

                                            <span class="global-time-status-dot">
                                            </span>

                                            <div class="global-time-information">

                                                <a href="#" id="globalTimeTaskLink">
                                                    Active Task
                                                </a>

                                                <small id="globalTimeProject">
                                                    Project
                                                </small>

                                            </div>

                                            <strong id="globalTimeClock">
                                                00:00:00
                                            </strong>

                                            <div class="global-time-actions">

                                                <button type="button" data-time-action="pause" id="globalTimePause">
                                                    Pause
                                                </button>

                                                <button type="button" data-time-action="resume" id="globalTimeResume"
                                                    class="time-tracking-hidden">
                                                    Resume
                                                </button>

                                                <button type="button" data-time-action="stop" id="globalTimeStop">
                                                    End
                                                </button>

                                            </div>

                                        </div>
                    @endif

                    {{-- Notification Dropdown --}}
                    <!-- <div
            class="notification-dropdown"
            id="notificationDropdown"
        > -->
                    <div class="notification-dropdown" id="notificationDropdown"
                        data-poll-url="{{ route('notification.poll') }}" data-poll-interval="4000">

                        {{-- Notification Bell --}}
                        <button type="button" class="notification-bell-btn" id="notificationDropdownBtn"
                            aria-expanded="false" aria-controls="notificationDropdownMenu" title="Notifications">
                            <span class="notification-bell-icon">
                                🔔
                            </span>

                            {{-- Unread Notification Counter --}}
                            <span class="notification-bell-badge
                        {{ $topbarUnreadCount > 0
    ? ''
    : 'notification-hidden' }}" id="notificationBellBadge">
                                {{ $topbarUnreadCount > 99
    ? '99+'
    : $topbarUnreadCount }}
                            </span>
                        </button>

                        {{-- Notification Dropdown Menu --}}
                        <div class="notification-dropdown-menu" id="notificationDropdownMenu" aria-hidden="true">

                            {{-- Dropdown Header --}}
                            <div class="notification-dropdown-header">

                                <div>
                                    <strong>
                                        Notifications
                                    </strong>

                                    <!-- <span>
                            {{ $topbarUnreadCount }}
                            {{ $topbarUnreadCount === 1
                                ? 'unread notification'
                                : 'unread notifications' }}
                        </span> -->

                                    <span id="notificationUnreadText">
                                        {{ $topbarUnreadCount }}
                                        {{ $topbarUnreadCount === 1
    ? 'unread notification'
    : 'unread notifications' }}
                                    </span>
                                </div>

                                {{-- Mark All as Read --}}
                                @if($topbarUnreadCount > 0)
                                                                <!-- <form
                                                                                                                                                                                            method="POST"
                                                                                                                                                                                            action="{{ route(
                                                                                                                                                                                                'notification.read-all'
                                                                                                                                                                                            ) }}"
                                                                                                                                                                                        >
                                                                                                                                                                                            @csrf

                                                                                                                                                                                            <button type="submit">
                                                                                                                                                                                                Mark all read
                                                                                                                                                                                            </button>
                                                                                                                                                                                        </form> -->
                                                                <form method="POST" action="{{ route(
                                        'notification.read-all'
                                    ) }}" id="notificationMarkAllForm" class="{{ $topbarUnreadCount > 0
                                        ? ''
                                        : 'notification-hidden' }}">
                                                                    @csrf

                                                                    <button type="submit">
                                                                        Mark all read
                                                                    </button>
                                                                </form>
                                @endif

                            </div>

                            {{-- Latest Notifications --}}
                            <!-- <div class="notification-dropdown-list"> -->
                            <div class="notification-dropdown-list" id="notificationDropdownList">

                                @forelse(
                                                                    $topbarNotifications
                                                                    as $notification
                                                                )

                                                                @php
                                                                    $notificationData =
                                                                        $notification->data;

                                                                    $isNotificationUnread =
                                                                        is_null(
                                                                            $notification->read_at
                                                                        );
                                                                @endphp

                                                                <!-- <a
                                                                                                                                                                                            href="{{ route(
                                                                                                                                                                                                'notification.open',
                                                                                                                                                                                                $notification->id
                                                                                                                                                                                            ) }}"
                                                                                                                                                                                            class="notification-dropdown-item
                                                                                                                                                                                                {{ $isNotificationUnread
                                                                                                                                                                                                    ? 'unread'
                                                                                                                                                                                                    : '' }}"
                                                                                                                                                                                        > -->

                                                                <a href="{{ route(
                                        'notification.open',
                                        $notification->id
                                    ) }}" class="notification-dropdown-item
                                                                                                                                                                                                {{ $isNotificationUnread
                                        ? 'unread'
                                        : '' }}" data-notification-id="{{ $notification->id }}">

                                                                    {{-- Notification Icon --}}
                                                                    <div class="notification-dropdown-icon">
                                                                        {{ $notificationData['icon']
                                        ?? '🔔' }}
                                                                    </div>

                                                                    {{-- Notification Content --}}
                                                                    <div class="notification-dropdown-content">

                                                                        <strong>
                                                                            {{ $notificationData['title']
                                        ?? 'CRM Notification' }}
                                                                        </strong>

                                                                        @if(
                                                                                    !empty(
                                                                                    $notificationData['message']
                                                                                )
                                                                            )
                                                                            <p>
                                                                                {{ $notificationData['message'] }}
                                                                            </p>
                                                                        @endif

                                                                        <small>
                                                                            {{ $notification
                                        ->created_at
                                        ->diffForHumans() }}
                                                                        </small>

                                                                    </div>

                                                                    {{-- Unread Blue Dot --}}
                                                                    @if($isNotificationUnread)
                                                                        <span class="notification-dropdown-dot">
                                                                        </span>
                                                                    @endif

                                                                </a>

                                @empty

                                    {{-- Empty Notification State --}}
                                    <div class="notification-dropdown-empty">

                                        <span>
                                            🔔
                                        </span>

                                        <strong>
                                            No notifications
                                        </strong>

                                        <p>
                                            New CRM updates will appear here.
                                        </p>

                                    </div>

                                @endforelse

                            </div>

                            {{-- Dropdown Footer --}}
                            <div class="notification-dropdown-footer">

                                <a href="{{ route('notification.index') }}">
                                    View All Notifications
                                </a>

                            </div>

                        </div>

                    </div>

                    {{-- Existing Admin User Dropdown --}}
                    <div class="admin-dropdown" id="adminDropdown">

                        <button type="button" class="admin-btn" id="adminDropdownBtn" aria-expanded="false"
                            aria-controls="adminDropdownMenu">

                            <span class="admin-avatar">
                                {{ strtoupper(
    substr(
        $loggedInUser->name ?? 'A',
        0,
        1
    )
) }}
                            </span>

                            <span class="admin-info">

                                <strong>
                                    {{ $loggedInUser->name
    ?? 'Admin' }}
                                </strong>

                                <small>
                                    {{ $loggedInUser->role?->name
    ?? 'No Role' }}
                                </small>

                            </span>

                            <span class="dropdown-arrow" id="dropdownArrow">
                                ▼
                            </span>

                        </button>

                        {{-- Admin Dropdown Menu --}}
                        <div class="dropdown-menu" id="adminDropdownMenu" aria-hidden="true">

                            <div class="dropdown-user">

                                <strong>
                                    {{ $loggedInUser->name
    ?? 'Admin' }}
                                </strong>

                                <small>
                                    {{ $loggedInUser->email
                                    ?? 'admin@crm.com' }}
                                </small>

                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button type="submit" class="logout-btn">
                                    Logout
                                </button>
                            </form>

                        </div>

                    </div>

                </div>

            </header>

            <!-- Body -->
            <section class="page-body">
                <section class="page-body">
                    @yield('content')
                </section>

                @if($crmFooterText)
                    <footer class="crm-layout-footer">
                        {{ $crmFooterText }}
                    </footer>
                @endif

        </main>

    </div>
    {{-- AJAX Live Notification Toasts --}}
    <div class="notification-toast-container" id="notificationToastContainer" aria-live="polite" aria-atomic="false">
    </div>
    @stack('scripts')
</body>

</html>