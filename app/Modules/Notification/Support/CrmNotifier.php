<?php

namespace App\Modules\Notification\Support;

use App\Modules\Notification\Notifications\CrmNotification;
use App\Modules\User\Models\User;

class CrmNotifier
{
    /**
     * Ek user ko database notification bhejo.
     */
    public function send(
        ?User $recipient,
        array $payload,
        ?string $eventKey = null,
        ?User $actor = null
    ): bool {
        /*
         * Invalid ya inactive user ko notification mat bhejo.
         */
        if (
            !$recipient
            || !$recipient->is_active
        ) {
            return false;
        }

        /*
         * User ne khud action kiya hai to normally
         * use khud ko notification bhejne ki zarurat nahi.
         */
        if (
            $actor
            && (int) $recipient->id === (int) $actor->id
        ) {
            return false;
        }

        /*
         * Scheduled reminders ko duplicate hone se roko.
         */
        if (
            $eventKey
            && $recipient->notifications()
                ->where(
                    'data->event_key',
                    $eventKey
                )
                ->exists()
        ) {
            return false;
        }

        $data = array_merge([
            'kind' => 'general',
            'title' => 'CRM Notification',
            'message' => '',
            'url' => '/dashboard',
            'icon' => '🔔',
            'level' => 'info',
            'event_key' => $eventKey,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
        ], $payload);

        $recipient->notify(
            new CrmNotification($data)
        );

        return true;
    }

    /**
     * Multiple users ko same notification bhejo.
     */
    public function sendMany(
        iterable $recipients,
        array $payload,
        ?string $eventKey = null,
        ?User $actor = null
    ): int {
        $sentCount = 0;

        foreach ($recipients as $recipient) {
            if (
                $recipient instanceof User
                && $this->send(
                    $recipient,
                    $payload,
                    $eventKey,
                    $actor
                )
            ) {
                $sentCount++;
            }
        }

        return $sentCount;
    }
}