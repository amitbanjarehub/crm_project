<?php

namespace App\Modules\Notification\Support;

use App\Modules\Task\Models\Task;

class TaskReminderService
{
    public function __construct(
        private CrmNotifier $notifier
    ) {
    }

    public function send(): array
    {
        return [
            'tomorrow' =>
                $this->sendDueTomorrow(),

            'today' =>
                $this->sendDueToday(),

            'overdue' =>
                $this->sendOverdue(),
        ];
    }

    private function baseQuery()
    {
        return Task::query()
            ->with([
                'assignedUser:id,name,email,is_active',
                'project:id,name,project_code',
            ])
            ->whereNotNull('assigned_to')
            ->whereNotNull('due_at')
            ->whereNotIn(
                'status',
                [
                    'completed',
                    'cancelled',
                ]
            );
    }

    private function sendDueTomorrow(): int
    {
        $sent = 0;

        $date = now()
            ->addDay()
            ->toDateString();

        $this->baseQuery()
            ->whereDate('due_at', $date)
            ->chunkById(
                100,
                function ($tasks) use (
                    &$sent,
                    $date
                ) {
                    foreach ($tasks as $task) {
                        $wasSent = $this->notifier->send(
                            $task->assignedUser,
                            [
                                'kind' => 'task_due_tomorrow',
                                'title' => 'Task Due Tomorrow',
                                'message' =>
                                    "Task \"{$task->title}\" is due tomorrow at {$task->due_at->format('h:i A')}.",
                                'url' => route(
                                    'task.show',
                                    $task->id,
                                    false
                                ),
                                'icon' => '📅',
                                'level' => 'warning',
                                'task_id' => $task->id,
                                'project_id' =>
                                    $task->project_id,
                            ],
                            "task-due-tomorrow:{$task->id}:{$date}"
                        );

                        if ($wasSent) {
                            $sent++;
                        }
                    }
                }
            );

        return $sent;
    }

    private function sendDueToday(): int
    {
        $sent = 0;

        $date = today()->toDateString();

        $this->baseQuery()
            ->whereDate('due_at', $date)
            ->chunkById(
                100,
                function ($tasks) use (
                    &$sent,
                    $date
                ) {
                    foreach ($tasks as $task) {
                        $wasSent = $this->notifier->send(
                            $task->assignedUser,
                            [
                                'kind' => 'task_due_today',
                                'title' => 'Task Due Today',
                                'message' =>
                                    "Task \"{$task->title}\" is due today at {$task->due_at->format('h:i A')}.",
                                'url' => route(
                                    'task.show',
                                    $task->id,
                                    false
                                ),
                                'icon' => '⏰',
                                'level' => 'warning',
                                'task_id' => $task->id,
                                'project_id' =>
                                    $task->project_id,
                            ],
                            "task-due-today:{$task->id}:{$date}"
                        );

                        if ($wasSent) {
                            $sent++;
                        }
                    }
                }
            );

        return $sent;
    }

    private function sendOverdue(): int
    {
        $sent = 0;

        $today = today()->toDateString();

        /*
         * Today wali task alag "Due Today" notification legi.
         * Overdue me sirf previous dates include hongi.
         */
        $this->baseQuery()
            ->whereDate(
                'due_at',
                '<',
                $today
            )
            ->chunkById(
                100,
                function ($tasks) use (
                    &$sent,
                    $today
                ) {
                    foreach ($tasks as $task) {
                        $wasSent = $this->notifier->send(
                            $task->assignedUser,
                            [
                                'kind' => 'task_overdue',
                                'title' => 'Task Overdue',
                                'message' =>
                                    "Task \"{$task->title}\" was due on {$task->due_at->format('d M Y, h:i A')}.",
                                'url' => route(
                                    'task.show',
                                    $task->id,
                                    false
                                ),
                                'icon' => '⚠️',
                                'level' => 'danger',
                                'task_id' => $task->id,
                                'project_id' =>
                                    $task->project_id,
                            ],
                            "task-overdue:{$task->id}:{$today}"
                        );

                        if ($wasSent) {
                            $sent++;
                        }
                    }
                }
            );

        return $sent;
    }
}