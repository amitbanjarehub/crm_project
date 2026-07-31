<?php

use App\Modules\Notification\Support\TaskReminderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(
        Inspiring::quote()
    );
})->purpose(
    'Display an inspiring quote'
);

/*
 * Manual testing command:
 *
 * php artisan notifications:send-task-reminders
 */
Artisan::command(
    'notifications:send-task-reminders',
    function () {
        $counts = app(
            TaskReminderService::class
        )->send();

        $this->info(
            'Task reminder notifications processed.'
        );

        $this->line(
            "Due tomorrow: {$counts['tomorrow']}"
        );

        $this->line(
            "Due today: {$counts['today']}"
        );

        $this->line(
            "Overdue: {$counts['overdue']}"
        );
    }
)->purpose(
    'Send due and overdue task notifications'
);

/*
 * Daily 8:00 AM India time run hoga.
 */
Schedule::command(
    'notifications:send-task-reminders'
)
    ->dailyAt('08:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping();
