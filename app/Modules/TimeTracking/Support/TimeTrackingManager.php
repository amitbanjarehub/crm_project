<?php

namespace App\Modules\TimeTracking\Support;

use App\Modules\Notification\Support\CrmNotifier;
use App\Modules\Project\Support\ProjectActivityLogger;
use App\Modules\Task\Models\Task;
use App\Modules\TimeTracking\Models\TimeEntry;
use App\Modules\User\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimeTrackingManager
{
    public function __construct(
        private CrmNotifier $notifier
    ) {
    }

    /**
     * Current user ka running ya paused timer return karega.
     */
    public function current(
        User $user
    ): ?TimeEntry {
        return TimeEntry::query()
            ->active()
            ->where(
                'user_id',
                $user->id
            )
            ->with([
                'task:id,title,status,project_id,project_service_id',
                'project:id,project_code,name,status',
                'projectService:id,name,status',
            ])
            ->first();
    }

    /**
     * Assigned task par new timer start karega.
     */
    public function start(
        User $user,
        Task $task,
        ?string $notes = null
    ): TimeEntry {
        return DB::transaction(
            function () use (
                $user,
                $task,
                $notes
            ) {
                /*
                 * User row ko lock karne se same user ki
                 * simultaneous Start requests serialize hongi.
                 */
                $lockedUser = User::query()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->with('role')
                    ->firstOrFail();

                /*
                 * Task ko lock karke current database state check karo.
                 */
                $lockedTask = Task::query()
                    ->whereKey($task->id)
                    ->lockForUpdate()
                    ->with([
                        'project.manager',
                        'projectService',
                        'assignedUser',
                    ])
                    ->firstOrFail();

                $this->ensureCanStart(
                    $lockedUser,
                    $lockedTask
                );

                /*
                 * Ek user ka ek time par sirf ek active timer.
                 */
                $existingEntry = TimeEntry::query()
                    ->active()
                    ->where(
                        'user_id',
                        $lockedUser->id
                    )
                    ->with([
                        'task:id,title',
                    ])
                    ->first();

                if ($existingEntry) {
                    $runningTaskTitle =
                        $existingEntry->task?->title
                        ?? 'another task';

                    throw ValidationException::withMessages([
                        'timer' =>
                            "Your timer is already active on \"{$runningTaskTitle}\".",
                    ]);
                }

                /*
                 * Project ke andar employee ka member role
                 * snapshot ke roop me preserve karo.
                 */
                $memberRole = $lockedTask
                    ->project
                    ->members()
                    ->where(
                        'users.id',
                        $lockedUser->id
                    )
                    ->value(
                        'project_members.member_role'
                    );

                $now = now();

                $entry = TimeEntry::create([
                    'user_id' =>
                        $lockedUser->id,

                    'role_id' =>
                        $lockedUser->role_id,

                    'task_id' =>
                        $lockedTask->id,

                    'project_id' =>
                        $lockedTask->project_id,

                    'project_service_id' =>
                        $lockedTask->project_service_id,

                    /*
                     * Active entry ke liye active_key me
                     * user ID store hogi.
                     *
                     * Database unique constraint duplicate
                     * active timer prevent karega.
                     */
                    'active_key' =>
                        $lockedUser->id,

                    'status' =>
                        'running',

                    'started_at' =>
                        $now,

                    'last_started_at' =>
                        $now,

                    'paused_at' =>
                        null,

                    'stopped_at' =>
                        null,

                    'total_seconds' =>
                        0,

                    'notes' =>
                        $this->cleanText($notes),

                    /*
                     * Historical snapshots.
                     */
                    'user_name_snapshot' =>
                        $lockedUser->name,

                    'role_name_snapshot' =>
                        $lockedUser->role?->name,

                    'member_role_snapshot' =>
                        $memberRole,

                    'created_by' =>
                        $lockedUser->id,
                ]);

                /*
                 * To Do task par kaam start hote hi
                 * task In Progress ho jayegi.
                 */
                if (
                    $lockedTask->status === 'to_do'
                ) {
                    $lockedTask->update([
                        'status' =>
                            'in_progress',

                        'completed_at' =>
                            null,
                    ]);
                }

                /*
                 * Pending service par kaam start hote hi
                 * service In Progress ho jayegi.
                 */
                if (
                    $lockedTask->projectService
                    && $lockedTask
                        ->projectService
                        ->status === 'pending'
                ) {
                    $lockedTask
                        ->projectService
                        ->update([
                            'status' =>
                                'in_progress',

                            'completed_at' =>
                                null,
                        ]);
                }

                /*
                 * Draft ya Planned Project me work start
                 * hone par Project Active ho jayega.
                 */
                if (
                    in_array(
                        $lockedTask->project->status,
                        [
                            'draft',
                            'planned',
                        ],
                        true
                    )
                ) {
                    $lockedTask->project->update([
                        'status' => 'active',
                    ]);
                }

                ProjectActivityLogger::log(
                    $lockedTask->project,
                    'time_tracking_started',
                    "{$lockedUser->name} started work on task {$lockedTask->title}.",
                    $entry
                );

                return $entry
                    ->refresh()
                    ->load([
                        'task:id,title,status,project_id,project_service_id',
                        'project:id,project_code,name,status',
                        'projectService:id,name,status',
                    ]);
            },
            3
        );
    }

    /**
     * Running timer pause karega.
     */
    public function pause(
        User $user
    ): TimeEntry {
        return DB::transaction(
            function () use ($user) {
                $entry = $this
                    ->lockedActiveEntry($user);

                if (!$entry->isRunning()) {
                    throw ValidationException::withMessages([
                        'timer' =>
                            'Only a running timer can be paused.',
                    ]);
                }

                $now = now();

                /*
                 * Current running segment ke seconds.
                 */
                $workedSeconds =
                    $this->currentSegmentSeconds(
                        $entry,
                        $now
                    );

                $entry->update([
                    'status' =>
                        'paused',

                    'total_seconds' =>
                        (int) $entry->total_seconds
                        + $workedSeconds,

                    /*
                     * Running segment close ho gaya.
                     */
                    'last_started_at' =>
                        null,

                    'paused_at' =>
                        $now,
                ]);

                /*
                 * Separate break history create karo.
                 */
                $entry->breaks()->create([
                    'paused_at' =>
                        $now,

                    'resumed_at' =>
                        null,

                    'break_seconds' =>
                        0,
                ]);

                $entry->loadMissing([
                    'task',
                    'project',
                ]);

                $taskTitle =
                    $entry->task?->title
                    ?? 'Deleted Task';

                if ($entry->project) {
                    ProjectActivityLogger::log(
                        $entry->project,
                        'time_tracking_paused',
                        "{$user->name} paused work on task {$taskTitle}.",
                        $entry
                    );
                }

                return $entry
                    ->refresh()
                    ->load([
                        'task:id,title,status,project_id,project_service_id',
                        'project:id,project_code,name,status',
                        'projectService:id,name,status',
                    ]);
            },
            3
        );
    }

    /**
     * Paused timer dobara resume karega.
     */
    public function resume(
        User $user
    ): TimeEntry {
        return DB::transaction(
            function () use ($user) {
                $entry = $this
                    ->lockedActiveEntry($user);

                if (!$entry->isPaused()) {
                    throw ValidationException::withMessages([
                        'timer' =>
                            'Only a paused timer can be resumed.',
                    ]);
                }

                $now = now();

                /*
                 * Open break record ko close karo.
                 */
                $openBreak = $entry
                    ->breaks()
                    ->whereNull('resumed_at')
                    ->latest('paused_at')
                    ->lockForUpdate()
                    ->first();

                if ($openBreak) {
                    $breakSeconds = max(
                        0,
                        (int) $openBreak
                            ->paused_at
                            ->diffInSeconds(
                                $now,
                                false
                            )
                    );

                    $openBreak->update([
                        'resumed_at' =>
                            $now,

                        'break_seconds' =>
                            $breakSeconds,
                    ]);
                }

                $entry->update([
                    'status' =>
                        'running',

                    /*
                     * Naya working segment start.
                     */
                    'last_started_at' =>
                        $now,

                    'paused_at' =>
                        null,
                ]);

                $entry->loadMissing([
                    'task',
                    'project',
                ]);

                $taskTitle =
                    $entry->task?->title
                    ?? 'Deleted Task';

                if ($entry->project) {
                    ProjectActivityLogger::log(
                        $entry->project,
                        'time_tracking_resumed',
                        "{$user->name} resumed work on task {$taskTitle}.",
                        $entry
                    );
                }

                return $entry
                    ->refresh()
                    ->load([
                        'task:id,title,status,project_id,project_service_id',
                        'project:id,project_code,name,status',
                        'projectService:id,name,status',
                    ]);
            },
            3
        );
    }

    /**
     * Current user ka active timer end karega.
     */
    public function stop(
        User $user,
        ?User $actor = null,
        ?string $notes = null,
        string $reason = 'User ended work'
    ): TimeEntry {
        return DB::transaction(
            function () use (
                $user,
                $actor,
                $notes,
                $reason
            ) {
                $entry = $this
                    ->lockedActiveEntry($user);

                return $this->stopLockedEntry(
                    $entry,
                    $actor ?? $user,
                    $notes,
                    $reason
                );
            },
            3
        );
    }

    /**
     * Particular task ke sab active timers stop karega.
     *
     * Use cases:
     * - Task submitted for review
     * - Task completed
     * - Task cancelled
     */
    public function stopTaskTimers(
        Task $task,
        ?User $actor = null,
        string $reason = 'Task workflow changed'
    ): int {
        return DB::transaction(
            function () use (
                $task,
                $actor,
                $reason
            ) {
                $entries = TimeEntry::query()
                    ->active()
                    ->where(
                        'task_id',
                        $task->id
                    )
                    ->lockForUpdate()
                    ->get();

                foreach ($entries as $entry) {
                    $this->stopLockedEntry(
                        $entry,
                        $actor,
                        null,
                        $reason
                    );
                }

                return $entries->count();
            },
            3
        );
    }

    /**
     * Timer start hone se pehle saare business rules.
     */
    private function ensureCanStart(
        User $user,
        Task $task
    ): void {
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'timer' =>
                    'Inactive users cannot use time tracking.',
            ]);
        }

        if (
            !$user->hasPermission(
                'time_tracking.use'
            )
        ) {
            throw ValidationException::withMessages([
                'timer' =>
                    'You do not have permission to use time tracking.',
            ]);
        }

        /*
         * Assigned employee hi timer start karega.
         */
        if (
            (int) $task->assigned_to
            !== (int) $user->id
        ) {
            throw ValidationException::withMessages([
                'timer' =>
                    'You can start work only on tasks assigned to you.',
            ]);
        }

        if (!$task->project) {
            throw ValidationException::withMessages([
                'timer' =>
                    'The project associated with this task is unavailable.',
            ]);
        }

        if ($task->project->isClosed()) {
            throw ValidationException::withMessages([
                'timer' =>
                    'Time tracking cannot start on a completed or cancelled project.',
            ]);
        }

        if (
            $task->project->status === 'on_hold'
        ) {
            throw ValidationException::withMessages([
                'timer' =>
                    'This project is currently on hold.',
            ]);
        }

        /*
         * Closed, on-hold ya review service me
         * new timer start nahi hoga.
         */
        if (
            $task->projectService
            && in_array(
                $task->projectService->status,
                [
                    'in_review',
                    'completed',
                    'on_hold',
                    'cancelled',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'timer' =>
                    'This project service is not available for work.',
            ]);
        }

        if ($task->isClosed()) {
            throw ValidationException::withMessages([
                'timer' =>
                    'Completed or cancelled task cannot be tracked.',
            ]);
        }

        if (
            $task->status === 'in_review'
        ) {
            throw ValidationException::withMessages([
                'timer' =>
                    'A task currently in review cannot be tracked.',
            ]);
        }

        /*
         * Blocked task ya incomplete dependencies.
         */
        if (
            $task->status === 'blocked'
            || $task->hasIncompleteDependencies()
        ) {
            throw ValidationException::withMessages([
                'timer' =>
                    'Complete all prerequisite tasks before starting work.',
            ]);
        }

        if (
            !in_array(
                $task->status,
                [
                    'to_do',
                    'in_progress',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'timer' =>
                    'This task is not currently available for time tracking.',
            ]);
        }
    }

    /**
     * User ki active entry ko row-lock ke saath fetch karega.
     */
    private function lockedActiveEntry(
        User $user
    ): TimeEntry {
        $entry = TimeEntry::query()
            ->active()
            ->where(
                'user_id',
                $user->id
            )
            ->lockForUpdate()
            ->first();

        if (!$entry) {
            throw ValidationException::withMessages([
                'timer' =>
                    'No active timer was found.',
            ]);
        }

        return $entry;
    }

    /**
     * Already locked entry ko safely stop karega.
     */
    private function stopLockedEntry(
        TimeEntry $entry,
        ?User $actor,
        ?string $notes,
        string $reason
    ): TimeEntry {
        $now = now();

        $totalSeconds =
            (int) $entry->total_seconds;

        /*
         * Running timer hai to current segment add karo.
         */
        if ($entry->isRunning()) {
            $totalSeconds +=
                $this->currentSegmentSeconds(
                    $entry,
                    $now
                );
        }

        /*
         * Timer paused condition me end hua ho to
         * open break record close karo.
         */
        if ($entry->isPaused()) {
            $openBreak = $entry
                ->breaks()
                ->whereNull('resumed_at')
                ->latest('paused_at')
                ->lockForUpdate()
                ->first();

            if ($openBreak) {
                $breakSeconds = max(
                    0,
                    (int) $openBreak
                        ->paused_at
                        ->diffInSeconds(
                            $now,
                            false
                        )
                );

                $openBreak->update([
                    'resumed_at' =>
                        $now,

                    'break_seconds' =>
                        $breakSeconds,
                ]);
            }
        }

        $finalNotes = $this->mergeNotes(
            $entry->notes,
            $notes
        );

        $entry->update([
            'status' =>
                'stopped',

            /*
             * Null hone ke baad user naya timer
             * start kar sakta hai.
             */
            'active_key' =>
                null,

            'total_seconds' =>
                max(0, $totalSeconds),

            'last_started_at' =>
                null,

            'paused_at' =>
                null,

            'stopped_at' =>
                $now,

            'notes' =>
                $finalNotes,

            'stopped_by' =>
                $actor?->id,

            'stop_reason' =>
                mb_substr(
                    trim($reason),
                    0,
                    255
                ),
        ]);

        $entry->loadMissing([
            'user',
            'task.project.manager',
            'project',
        ]);

        $taskTitle =
            $entry->task?->title
            ?? 'Deleted Task';

        $userName =
            $entry->user?->name
            ?? $entry->user_name_snapshot
            ?? 'Unknown User';

        $duration =
            TimeEntry::formatSeconds(
                (int) $entry->total_seconds
            );

        if ($entry->project) {
            ProjectActivityLogger::log(
                $entry->project,
                'time_tracking_stopped',
                "{$userName} ended work on task {$taskTitle}. Tracked time: {$duration}.",
                $entry
            );
        }

        /*
         * Task ka estimated time cross hua ho
         * to Project Manager ko notification.
         */
        $this->notifyEstimatedTimeExceeded(
            $entry,
            $actor
        );

        return $entry
            ->refresh()
            ->load([
                'task:id,title,status,project_id,project_service_id,estimated_hours',
                'project:id,project_code,name,status',
                'projectService:id,name,status',
            ]);
    }

    /**
     * Current running segment ke actual seconds.
     */
    private function currentSegmentSeconds(
        TimeEntry $entry,
        CarbonInterface $now
    ): int {
        if (!$entry->last_started_at) {
            return 0;
        }

        return max(
            0,
            (int) $entry
                ->last_started_at
                ->diffInSeconds(
                    $now,
                    false
                )
        );
    }

    /**
     * Estimated hours exceed hone par notification.
     */
    private function notifyEstimatedTimeExceeded(
        TimeEntry $entry,
        ?User $actor
    ): void {
        $task = $entry->task;

        if (
            !$task
            || !$task->estimated_hours
            || (float) $task->estimated_hours <= 0
        ) {
            return;
        }

        $estimatedSeconds = (int) round(
            (float) $task->estimated_hours
            * 3600
        );

        /*
         * Task ki sab stopped entries ka total.
         */
        $totalTaskSeconds = (int)
            TimeEntry::query()
                ->where(
                    'task_id',
                    $task->id
                )
                ->sum(
                    'total_seconds'
                );

        if (
            $totalTaskSeconds
            <= $estimatedSeconds
        ) {
            return;
        }

        $task->loadMissing([
            'project.manager',
        ]);

        $projectManager =
            $task->project?->manager;

        if (!$projectManager) {
            return;
        }

        $this->notifier->send(
            $projectManager,
            [
                'kind' =>
                    'task_estimate_exceeded',

                'title' =>
                    'Task Estimate Exceeded',

                'message' =>
                    "Task \"{$task->title}\" has exceeded its estimated time.",

                'url' => route(
                    'task.show',
                    $task->id,
                    false
                ),

                'icon' =>
                    '⏱️',

                'level' =>
                    'warning',

                'task_id' =>
                    $task->id,

                'project_id' =>
                    $task->project_id,
            ],

            /*
             * Same Task ke liye duplicate warning
             * dobara create nahi hogi.
             */
            "task-estimate-exceeded:{$task->id}",

            $actor
        );
    }

    /**
     * Optional text clean karega.
     */
    private function cleanText(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== ''
            ? $value
            : null;
    }

    /**
     * Existing aur ending notes merge karega.
     */
    private function mergeNotes(
        ?string $existingNotes,
        ?string $newNotes
    ): ?string {
        $notes = collect([
            $this->cleanText(
                $existingNotes
            ),

            $this->cleanText(
                $newNotes
            ),
        ])
            ->filter()
            ->unique()
            ->values();

        if ($notes->isEmpty()) {
            return null;
        }

        return $notes->implode(
            PHP_EOL
        );
    }
}