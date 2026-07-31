<?php

namespace App\Modules\Notification\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Current logged-in user ki notifications listing.
     */
    public function index(Request $request)
    {
        $filter = trim(
            (string) $request->query(
                'filter',
                'all'
            )
        );

        if (
            !in_array(
                $filter,
                [
                    'all',
                    'unread',
                    'read',
                ],
                true
            )
        ) {
            $filter = 'all';
        }

        $user = $request->user();

        $query = $user
            ->notifications()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query
            ->paginate(15)
            ->withQueryString();

        return view('notification::index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unreadCount' =>
                $user->unreadNotifications()->count(),
            'pageTitle' => 'Notifications',
        ]);
    }

    /**
     * AJAX polling endpoint.
     *
     * Logged-in user ki latest 6 notifications aur
     * unread count JSON format me return karega.
     */
    public function poll(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $latestNotifications = $user
            ->notifications()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $notifications = $latestNotifications
            ->map(
                fn(DatabaseNotification $notification) =>
                    $this->formatForAjax(
                        $notification
                    )
            )
            ->values();

        $response = response()->json([
            'success' => true,

            'unread_count' =>
                $user
                    ->unreadNotifications()
                    ->count(),

            'notifications' =>
                $notifications,

            'checked_at' =>
                now()->toIso8601String(),
        ]);

        /*
         * Browser ya proxy purana AJAX response
         * cache nahi karega.
         */
        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0'
        );

        $response->headers->set(
            'Pragma',
            'no-cache'
        );

        return $response;
    }

    /**
     * Notification open karke read mark karo
     * aur related CRM page par redirect karo.
     */
    public function open(
        Request $request,
        string $notification
    ) {
        $item = $request->user()
            ->notifications()
            ->where(
                'id',
                $notification
            )
            ->firstOrFail();

        if (!$item->read_at) {
            $item->markAsRead();
        }

        $url = $item->data['url'] ?? null;

        /*
         * Sirf internal relative URL allow karo.
         * External redirect block rahega.
         */
        if (
            is_string($url)
            && str_starts_with($url, '/')
            && !str_starts_with($url, '//')
        ) {
            return redirect($url);
        }

        return redirect()
            ->route('notification.index');
    }

    /**
     * Single notification read mark karo.
     */
    public function markAsRead(
        Request $request,
        string $notification
    ) {
        $item = $request->user()
            ->notifications()
            ->where(
                'id',
                $notification
            )
            ->firstOrFail();

        if (!$item->read_at) {
            $item->markAsRead();
        }

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    /**
     * Current user ki sari unread notifications read karo.
     */
    public function markAllAsRead(
        Request $request
    ) {
        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

    /**
     * Current user ki single notification delete karo.
     */
    public function destroy(
        Request $request,
        string $notification
    ) {
        $item = $request->user()
            ->notifications()
            ->where(
                'id',
                $notification
            )
            ->firstOrFail();

        $item->delete();

        return back()->with(
            'success',
            'Notification deleted successfully.'
        );
    }

    /**
     * Database notification ko frontend-friendly
     * safe array me convert karo.
     */
    private function formatForAjax(
        DatabaseNotification $notification
    ): array {
        $data = is_array($notification->data)
            ? $notification->data
            : [];

        $allowedLevels = [
            'info',
            'success',
            'warning',
            'danger',
        ];

        $level = $data['level'] ?? 'info';

        if (
            !in_array(
                $level,
                $allowedLevels,
                true
            )
        ) {
            $level = 'info';
        }

        return [
            'id' => (string) $notification->id,

            'title' => (string) (
                $data['title']
                ?? 'CRM Notification'
            ),

            'message' => (string) (
                $data['message']
                ?? ''
            ),

            'icon' => (string) (
                $data['icon']
                ?? '🔔'
            ),

            'kind' => (string) (
                $data['kind']
                ?? 'general'
            ),

            'level' => $level,

            'is_unread' =>
                is_null($notification->read_at),

            'open_url' => route(
                'notification.open',
                $notification->id,
                false
            ),

            'time_ago' =>
                $notification
                    ->created_at
                    ?->diffForHumans()
                ?? 'Just now',

            'created_at' =>
                $notification
                    ->created_at
                    ?->toIso8601String(),
        ];
    }
}