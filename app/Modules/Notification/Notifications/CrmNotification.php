<?php

namespace App\Modules\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CrmNotification extends Notification
{
    use Queueable;

    public function __construct(
        public array $payload
    ) {
    }

    /**
     * Notification sirf CRM database me save hogi.
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
        ];
    }

    /**
     * notifications table ke data JSON column me
     * ye array store hoga.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->payload;
    }

    /**
     * Database notification type readable rakho.
     */
    public function databaseType(
        object $notifiable
    ): string {
        return 'crm-notification';
    }
}