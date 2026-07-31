@extends('admin::layouts.app')

@section('content')

<div class="content-card notification-page">

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

    <div class="page-card-header">

        <div>
            <h1>Notifications</h1>

            <p>
                Review your task, project and deadline updates.
            </p>
        </div>

        @if($unreadCount > 0)
            <form
                method="POST"
                action="{{ route(
                    'notification.read-all'
                ) }}"
            >
                @csrf

                <button
                    type="submit"
                    class="secondary-btn"
                >
                    Mark All as Read
                </button>
            </form>
        @endif

    </div>

    {{-- Filters --}}
    <div class="notification-filter-tabs">

        <a
            href="{{ route(
                'notification.index',
                ['filter' => 'all']
            ) }}"
            class="{{ $filter === 'all' ? 'active' : '' }}"
        >
            All
        </a>

        <a
            href="{{ route(
                'notification.index',
                ['filter' => 'unread']
            ) }}"
            class="{{ $filter === 'unread' ? 'active' : '' }}"
        >
            Unread

            @if($unreadCount > 0)
                <span>
                    {{ $unreadCount }}
                </span>
            @endif
        </a>

        <a
            href="{{ route(
                'notification.index',
                ['filter' => 'read']
            ) }}"
            class="{{ $filter === 'read' ? 'active' : '' }}"
        >
            Read
        </a>

    </div>

    {{-- Notification List --}}
    <div class="notification-page-list">

        @forelse($notifications as $notification)

            @php
                $data = $notification->data;

                $icon = $data['icon'] ?? '🔔';

                $title = $data['title']
                    ?? 'CRM Notification';

                $message = $data['message']
                    ?? '';

                $kind = $data['kind']
                    ?? 'general';

                $isUnread = is_null(
                    $notification->read_at
                );
            @endphp

            <article
                class="notification-page-item
                    {{ $isUnread ? 'unread' : 'read' }}"
            >

                <a
                    href="{{ route(
                        'notification.open',
                        $notification->id
                    ) }}"
                    class="notification-page-main"
                >

                    <div
                        class="notification-page-icon
                            notification-kind-{{ $kind }}"
                    >
                        {{ $icon }}
                    </div>

                    <div class="notification-page-content">

                        <div class="notification-page-title">

                            <strong>
                                {{ $title }}
                            </strong>

                            @if($isUnread)
                                <span class="notification-unread-dot">
                                </span>
                            @endif

                        </div>

                        @if($message !== '')
                            <p>
                                {{ $message }}
                            </p>
                        @endif

                        <div class="notification-page-meta">

                            <span>
                                {{ $notification
                                    ->created_at
                                    ->diffForHumans() }}
                            </span>

                            <span>
                                {{ $notification
                                    ->created_at
                                    ->format(
                                        'd M Y, h:i A'
                                    ) }}
                            </span>

                        </div>

                    </div>

                </a>

                <div class="notification-page-actions">

                    @if($isUnread)
                        <form
                            method="POST"
                            action="{{ route(
                                'notification.read',
                                $notification->id
                            ) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="table-btn edit"
                            >
                                Mark Read
                            </button>
                        </form>
                    @endif

                    <form
                        method="POST"
                        action="{{ route(
                            'notification.destroy',
                            $notification->id
                        ) }}"
                        onsubmit="return confirm(
                            'Delete this notification?'
                        );"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="table-btn delete"
                        >
                            Delete
                        </button>
                    </form>

                </div>

            </article>

        @empty

            <div class="notification-empty-state">

                <div>🔔</div>

                <h3>No Notifications Found</h3>

                <p>
                    Your project and task updates will appear here.
                </p>

            </div>

        @endforelse

    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())

        <div class="notification-pagination">

            @if($notifications->onFirstPage())
                <span class="page-link disabled">
                    Previous
                </span>
            @else
                <a
                    href="{{ $notifications->previousPageUrl() }}"
                    class="page-link"
                >
                    Previous
                </a>
            @endif

            <span>
                Page {{ $notifications->currentPage() }}
                of {{ $notifications->lastPage() }}
            </span>

            @if($notifications->hasMorePages())
                <a
                    href="{{ $notifications->nextPageUrl() }}"
                    class="page-link"
                >
                    Next
                </a>
            @else
                <span class="page-link disabled">
                    Next
                </span>
            @endif

        </div>

    @endif

</div>

@endsection