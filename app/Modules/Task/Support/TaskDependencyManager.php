<?php

namespace App\Modules\Task\Support;

use App\Modules\Notification\Support\CrmNotifier;
use App\Modules\Project\Support\ProjectActivityLogger;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskDependencyManager
{
    public function __construct(
        private CrmNotifier $notifier
    ) {
    }

    /*
     * Task dependency add karo.
     */
    public function add(
        Task $task,
        Task $prerequisite,
        ?User $actor = null
    ): void {
        $this->ensureTaskDependencyCanChange(
            $task
        );

        /*
         * Task khud par depend nahi kar sakti.
         */
        if (
            (int) $task->id
            === (int) $prerequisite->id
        ) {
            throw ValidationException::withMessages([
                'depends_on_task_id' =>
                    'A task cannot depend on itself.',
            ]);
        }

        /*
         * Sirf same Project ki Tasks connect hongi.
         */
        if (
            (int) $task->project_id
            !== (int) $prerequisite->project_id
        ) {
            throw ValidationException::withMessages([
                'depends_on_task_id' =>
                    'Dependency can only be added between tasks of the same project.',
            ]);
        }

        /*
         * Cancelled Task ko prerequisite nahi bana sakte.
         */
        if (
            $prerequisite->isCancelled()
        ) {
            throw ValidationException::withMessages([
                'depends_on_task_id' =>
                    'A cancelled task cannot be used as a dependency.',
            ]);
        }

        /*
         * Duplicate dependency block karo.
         */
        $alreadyExists = $task
            ->dependencyLinks()
            ->where(
                'depends_on_task_id',
                $prerequisite->id
            )
            ->exists();

        if ($alreadyExists) {
            throw ValidationException::withMessages([
                'depends_on_task_id' =>
                    'This dependency has already been added.',
            ]);
        }

        /*
         * Circular dependency block karo.
         *
         * Example:
         * Task A depends on Task B
         * Task B depends on Task A
         */
        if (
            $this->wouldCreateCircularDependency(
                $task,
                $prerequisite
            )
        ) {
            throw ValidationException::withMessages([
                'depends_on_task_id' =>
                    'This dependency would create a circular task dependency.',
            ]);
        }

        $task->dependencyLinks()->create([
            'depends_on_task_id' =>
                $prerequisite->id,

            'created_by' =>
                $actor?->id ?? auth()->id(),
        ]);

        ProjectActivityLogger::log(
            $task->project,
            'task_dependency_added',
            "Task {$task->title} now depends on {$prerequisite->title}.",
            $task,
            null,
            [
                'depends_on_task_id' =>
                    $prerequisite->id,

                'depends_on_task_title' =>
                    $prerequisite->title,
            ]
        );

        /*
         * Pending dependency ho to Task
         * automatically Blocked hogi.
         */
        $this->syncTaskStatus(
            $task
        );
    }

    /*
     * Task dependency remove karo.
     */
    public function remove(
        Task $task,
        Task $prerequisite
    ): void {
        $this->ensureTaskDependencyCanChange(
            $task
        );

        $dependency = $task
            ->dependencyLinks()
            ->where(
                'depends_on_task_id',
                $prerequisite->id
            )
            ->first();

        if (!$dependency) {
            throw ValidationException::withMessages([
                'dependency' =>
                    'The selected dependency does not exist.',
            ]);
        }

        $dependency->delete();

        ProjectActivityLogger::log(
            $task->project,
            'task_dependency_removed',
            "Dependency {$prerequisite->title} removed from task {$task->title}.",
            $task,
            [
                'depends_on_task_id' =>
                    $prerequisite->id,

                'depends_on_task_title' =>
                    $prerequisite->title,
            ],
            null
        );

        /*
         * Agar remaining dependencies complete hain,
         * Task automatically unlock hogi.
         */
        $this->syncTaskStatus(
            $task
        );
    }

    /*
     * Current Task ka blocked/unblocked status sync karo.
     */
    public function syncTaskStatus(
        Task $task
    ): void {
        /*
         * Latest database values load karo.
         */
        $task->refresh();

        /*
         * Closed aur In Review Task ka status
         * dependency manager change nahi karega.
         */
        if (
            $task->isClosed()
            || $task->isInReview()
        ) {
            return;
        }

        $hasIncompleteDependencies =
            $task->hasIncompleteDependencies();

        /*
         * Pending prerequisite hai:
         * Task ko Blocked karo.
         */
        if ($hasIncompleteDependencies) {
            /*
             * Notification sirf status transition
             * ke time bhejni hai.
             *
             * Agar Task already Blocked hai to
             * dobara notification nahi jayegi.
             */
            if (!$task->isBlocked()) {
                $oldStatus = $task->status;

                $task->update([
                    'status' =>
                        Task::blockedStatus(),
                ]);

                ProjectActivityLogger::log(
                    $task->project,
                    'task_blocked_by_dependency',
                    "Task {$task->title} was blocked because prerequisite tasks are incomplete.",
                    $task,
                    [
                        'status' => $oldStatus,
                    ],
                    [
                        'status' => 'blocked',
                    ]
                );

                /*
                 * Assigned user aur Project data load karo.
                 */
                $task->loadMissing([
                    'assignedUser:id,name,email,is_active',
                    'project:id,name,project_code',
                ]);

                /*
                 * Assigned user ko blocked notification.
                 */
                $this->notifier->send(
                    $task->assignedUser,
                    [
                        'kind' => 'task_blocked',

                        'title' => 'Task Blocked',

                        'message' =>
                            "Task \"{$task->title}\" is waiting for prerequisite tasks to be completed.",

                        'url' => route(
                            'task.show',
                            $task->id,
                            false
                        ),

                        'icon' => '🔒',
                        'level' => 'warning',
                        'task_id' => $task->id,
                        'project_id' =>
                            $task->project_id,
                    ]
                );
            }

            return;
        }

        /*
         * Sab dependencies complete ho gayi:
         * Blocked Task automatically unlock hogi.
         */
        if ($task->isBlocked()) {
            /*
             * Pehle task par kuch progress thi to
             * In Progress me wapas jayegi.
             *
             * Otherwise To Do me unlock hogi.
             */
            $newStatus =
                $task->progress_percent > 0
                ? Task::inProgressStatus()
                : Task::defaultStatus();

            $task->update([
                'status' => $newStatus,
            ]);

            ProjectActivityLogger::log(
                $task->project,
                'task_unblocked',
                "Task {$task->title} was automatically unblocked.",
                $task,
                [
                    'status' => 'blocked',
                ],
                [
                    'status' => $newStatus,
                ]
            );

            /*
             * Assigned user aur Project data load karo.
             */
            $task->loadMissing([
                'assignedUser:id,name,email,is_active',
                'project:id,name,project_code',
            ]);

            /*
             * Assigned user ko ready-to-start notification.
             */
            $this->notifier->send(
                $task->assignedUser,
                [
                    'kind' => 'task_unblocked',

                    'title' =>
                        'Task Ready to Start',

                    'message' =>
                        "All dependencies for \"{$task->title}\" are complete. You can start working now.",

                    'url' => route(
                        'task.show',
                        $task->id,
                        false
                    ),

                    'icon' => '🔓',
                    'level' => 'success',
                    'task_id' => $task->id,
                    'project_id' =>
                        $task->project_id,
                ]
            );
        }
    }

    /*
     * Prerequisite Task ke status change hone ke baad
     * us par depend karne wali Tasks sync hongi.
     */
    public function syncDependentTasks(
        Task $prerequisite
    ): void {
        $dependentTasks = $prerequisite
            ->dependentTasks()
            ->get();

        foreach (
            $dependentTasks as $dependentTask
        ) {
            $this->syncTaskStatus(
                $dependentTask
            );
        }
    }

    /*
     * Completed, Cancelled ya In Review Task ki
     * dependency modify nahi hogi.
     */
    private function ensureTaskDependencyCanChange(
        Task $task
    ): void {
        if ($task->isClosed()) {
            throw ValidationException::withMessages([
                'dependency' =>
                    'Completed or cancelled task dependencies cannot be changed.',
            ]);
        }

        if ($task->isInReview()) {
            throw ValidationException::withMessages([
                'dependency' =>
                    'Task dependencies cannot be changed while the task is in review.',
            ]);
        }
    }

    /*
     * Circular dependency detection.
     *
     * New relation:
     * $task depends on $prerequisite
     *
     * Check:
     * Kya prerequisite already task par directly
     * ya indirectly depend karti hai?
     */
    private function wouldCreateCircularDependency(
        Task $task,
        Task $prerequisite
    ): bool {
        $targetTaskId = (int) $task->id;

        $pendingTaskIds = [
            (int) $prerequisite->id,
        ];

        $visitedTaskIds = [];

        while (!empty($pendingTaskIds)) {
            $currentTaskId = array_pop(
                $pendingTaskIds
            );

            if (
                $currentTaskId
                === $targetTaskId
            ) {
                return true;
            }

            if (
                isset(
                $visitedTaskIds[
                    $currentTaskId
                ]
            )
            ) {
                continue;
            }

            $visitedTaskIds[
                $currentTaskId
            ] = true;

            $nextTaskIds = DB::table(
                'task_dependencies'
            )
                ->where(
                    'task_id',
                    $currentTaskId
                )
                ->pluck(
                    'depends_on_task_id'
                )
                ->map(
                    fn($id) => (int) $id
                )
                ->all();

            foreach (
                $nextTaskIds as $nextTaskId
            ) {
                if (
                    !isset(
                    $visitedTaskIds[
                        $nextTaskId
                    ]
                )
                ) {
                    $pendingTaskIds[] =
                        $nextTaskId;
                }
            }
        }

        return false;
    }
}