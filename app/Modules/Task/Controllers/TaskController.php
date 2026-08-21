<?php

namespace App\Modules\Task\Controllers;

use App\Modules\Notification\Support\CrmNotifier;
use App\Http\Controllers\Controller;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\ProjectService;
use App\Modules\Project\Support\AuthorizesProjectAccess;
use App\Modules\Project\Support\ProjectActivityLogger;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Modules\Task\Support\TaskDependencyManager;
use App\Modules\TimeTracking\Support\TimeTrackingManager;
use App\Modules\Task\Models\TaskPriority;
use App\Modules\Task\Models\TaskStatus;

class TaskController extends Controller
{
    use AuthorizesProjectAccess;

    public function index(Request $request)
    {
        return $this->taskListing(
            $request,
            false
        );
    }

    public function myTasks(Request $request)
    {
        return $this->taskListing(
            $request,
            true
        );
    }

    private function taskListing(
        Request $request,
        bool $onlyMyTasks
    ) {
        $allowedPerPage = [10, 25, 50, 100];

        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $user = $request->user();

        $search = trim($request->query('search', ''));
        $status = trim($request->query('status', ''));
        $priority = trim($request->query('priority', ''));
        $due = trim($request->query('due', ''));

        $query = Task::query()
            ->with([
                'project:id,project_code,name,project_manager_id',
                'projectService:id,name',
                'assignedUser:id,name,email',
                'prerequisiteTasks:id,title,status,project_id',
                'activeTimeEntries.user:id,name,email',
                'statusDefinition:id,slug,name,color,is_closed,system_key,is_manual_selectable',
                'priorityDefinition:id,slug,name,color',
                'prerequisiteTasks.statusDefinition:id,slug,name,color,is_closed,system_key',
            ])
            ->withSum(
                'timeEntries as tracked_seconds',
                'total_seconds'
            )
            ->latest();

        if ($onlyMyTasks) {
            $query->where('assigned_to', $user->id);
        } elseif (!$this->canViewAllTasks($user)) {
            $query->where(function (Builder $q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhereHas(
                        'project',
                        fn(Builder $projectQuery) =>
                        $projectQuery
                            ->where(
                                'project_manager_id',
                                $user->id
                            )
                            ->orWhereHas(
                                'members',
                                fn(Builder $memberQuery) =>
                                $memberQuery->where(
                                    'users.id',
                                    $user->id
                                )
                            )
                    );
            });
        }

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhereHas(
                        'project',
                        fn(Builder $projectQuery) =>
                        $projectQuery
                            ->where(
                                'name',
                                'LIKE',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'project_code',
                                'LIKE',
                                "%{$search}%"
                            )
                    );
            });
        }

        if (
            $status !== ''
            && array_key_exists($status, Task::statuses())
        ) {
            $query->where('status', $status);
        }

        if (
            $priority !== ''
            && array_key_exists($priority, Task::priorities())
        ) {
            $query->where('priority', $priority);
        }

        if ($due === 'today') {
            $query->whereDate('due_at', today());
        } elseif ($due === 'overdue') {
            $query->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->whereNotIn(
                    'status',
                    Task::closedStatusSlugs()
                );
        } elseif ($due === 'upcoming') {
            $query->where('due_at', '>', now());
        }

        $tasks = $query
            ->paginate($perPage)
            ->withQueryString();

        $view = $onlyMyTasks
            ? 'task::my-tasks'
            : 'task::index';

        return view($view, [
            'tasks' => $tasks,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'priority' => $priority,
            'due' => $due,
            'statuses' => Task::statuses(),
            'manualStatuses' =>
                Task::manualStatuses(),
            'priorities' => Task::priorities(),
            'onlyMyTasks' => $onlyMyTasks,
            'pageTitle' => $onlyMyTasks
                ? 'My Tasks'
                : 'Task Management',
        ]);
    }

    public function create(
        Request $request,
        ProjectService $projectService
    ) {
        $projectService->load('project.members');

        $this->ensureCanAccessProject(
            $request->user(),
            $projectService->project
        );

        abort_if(
            $projectService->project->isClosed(),
            422,
            'Closed project me task create nahi kar sakte.'
        );

        // return view('task::create', [
        //     'project' => $projectService->project,
        //     'projectService' => $projectService,
        //     'users' => $this->projectUsers(
        //         $projectService->project
        //     ),
        //     'statuses' => Task::statuses(),
        //     'priorities' => Task::priorities(),
        //     'pageTitle' => 'Add Task',
        // ]);

        return view(
            'task::create',
            [
                'project' =>
                    $projectService->project,

                'projectService' =>
                    $projectService,

                'users' =>
                    $this->projectUsers(
                        $projectService->project
                    ),

                /*
                 * New Task form me sirf active
                 * priorities show hongi.
                 */
                'priorities' =>
                    Task::activePriorities(),

                /*
                 * Settings me selected default values.
                 */
                'defaultPriority' =>
                    Task::defaultPriority(),

                'defaultStatus' =>
                    Task::defaultStatus(),

                'pageTitle' =>
                    'Add Task',
            ]
        );
    }

    public function store(
        Request $request,
        ProjectService $projectService,
        CrmNotifier $notifier
    ) {
        $projectService->load('project.members');

        $project = $projectService->project;

        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        $validated = $request->validate(
            $this->validationRules()
        );

        $this->validateTaskAssignee(
            $project,
            $validated['assigned_to'] ?? null
        );

        $task = $projectService->tasks()->create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'description' =>
                $validated['description'] ?? null,
            'assigned_to' =>
                $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'priority' => $validated['priority'],
            'status' => Task::defaultStatus(),
            'progress_percent' => 0,
            'requires_review' =>
                $request->boolean('requires_review'),
            'reviewer_id' =>
                $validated['reviewer_id'] ?? null,
            'start_date' =>
                $validated['start_date'] ?? null,
            'due_at' =>
                $validated['due_at'] ?? null,
            'estimated_hours' =>
                $validated['estimated_hours'] ?? null,
        ]);

        ProjectActivityLogger::log(
            $project,
            'task_created',
            "Task {$task->title} created.",
            $task
        );

        $task->load([
            'assignedUser:id,name,email,is_active',
            'project:id,name,project_code',
        ]);

        $notifier->send(
            $task->assignedUser,
            [
                'kind' => 'task_assigned',
                'title' => 'New Task Assigned',
                'message' =>
                    "You have been assigned task \"{$task->title}\" in project {$task->project->project_code}.",
                'url' => route(
                    'task.show',
                    $task->id,
                    false
                ),
                'icon' => '✅',
                'level' => 'info',
                'task_id' => $task->id,
                'project_id' => $task->project_id,
            ],
            null,
            $request->user()
        );

        return redirect()
            ->route('task.show', $task->id)
            ->with('success', 'Task created successfully.');
    }



    public function show(
        Request $request,
        Task $task
    ) {
        $task->load([
            'project.client:id,name,company',
            'project.manager:id,name,email',
            'projectService:id,name',
            'assignedUser:id,name,email',
            'creator:id,name,email',
            'reviewer:id,name,email',
            'comments.user:id,name,email',
            'attachments.uploader:id,name,email',
            'statusDefinition:id,slug,name,color,is_closed,system_key,is_manual_selectable',
            'priorityDefinition:id,slug,name,color',
            'timeEntries' => fn($query) =>
                $query
                    ->with([
                        'user:id,name,email',
                        'role:id,name',
                        'breaks',
                    ])
                    ->latest('started_at')
                    ->limit(30),

            'prerequisiteTasks' => fn($query) =>
                $query
                    ->select([
                        'tasks.id',
                        'tasks.project_id',
                        'tasks.project_service_id',
                        'tasks.title',
                        'tasks.status',
                        'tasks.assigned_to',
                    ])
                    ->with([
                        'projectService:id,name',
                        'assignedUser:id,name,email',
                        'statusDefinition:id,slug,name,color,is_closed,system_key',
                    ]),

            'dependentTasks' => fn($query) =>
                $query
                    ->select([
                        'tasks.id',
                        'tasks.project_id',
                        'tasks.project_service_id',
                        'tasks.title',
                        'tasks.status',
                        'tasks.assigned_to',
                    ])
                    ->with([
                        'projectService:id,name',
                        'assignedUser:id,name,email',
                        'statusDefinition:id,slug,name,color,is_closed,system_key',
                    ]),
        ]);

        $this->ensureCanAccessTask(
            $request->user(),
            $task
        );

        $excludedTaskIds = $task
            ->prerequisiteTasks
            ->pluck('id')
            ->push($task->id)
            ->unique()
            ->all();

        /*
         * Dependency select me sirf same Project ki Tasks.
         * Cancelled Tasks exclude rahengi.
         */
        $availableDependencyTasks = Task::query()
            ->where(
                'project_id',
                $task->project_id
            )
            ->whereNotIn(
                'id',
                $excludedTaskIds
            )
            ->where(
                'status',
                '!=',
                Task::cancelledStatus()
            )
            ->with([
                'projectService:id,name',
                'statusDefinition:id,slug,name,color,is_closed,system_key',
            ])
            ->orderBy('title')
            ->get([
                'id',
                'project_id',
                'project_service_id',
                'title',
                'status',
            ]);

        $currentUserActiveEntry = $request
            ->user()
            ->activeTimeEntry()
            ->with([
                'task:id,title,status',
                'project:id,project_code,name',
            ])
            ->first();

        $taskTimeEntries = $task
            ->timeEntries()
            ->get();

        $taskTrackedSeconds = (int) 
            $taskTimeEntries->sum(
                fn($entry) =>
                $entry->liveSeconds()
            );

        return view('task::show', [
            'task' => $task,
            'availableDependencyTasks' =>
                $availableDependencyTasks,

            'currentUserActiveEntry' =>
                $currentUserActiveEntry,

            'taskTrackedSeconds' =>
                $taskTrackedSeconds,
            'statuses' =>
                Task::statuses(),

            'manualStatuses' =>
                Task::manualStatuses(),

            'priorities' =>
                Task::priorities(),

            'pageTitle' =>
                'Task Details',

        ]);
    }

    // public function edit(
    //     Request $request,
    //     Task $task
    // ) {
    //     $task->load([
    //         'project.members',
    //         'projectService',
    //     ]);

    //     $this->ensureCanModifyTask(
    //         $request->user(),
    //         $task
    //     );

    //     abort_if(
    //         $task->isClosed(),
    //         422,
    //         'Closed task cannot be edited.'
    //     );

    //     return view('task::edit', [
    //         'task' => $task,
    //         'project' => $task->project,
    //         'projectService' => $task->projectService,
    //         'users' => $this->projectUsers($task->project),
    //         'statuses' => Task::statuses(),
    //         'priorities' => Task::priorities(),
    //         'pageTitle' => 'Edit Task',
    //     ]);
    // }

    public function edit(
        Request $request,
        Task $task
    ) {
        $task->load([
            'project.members',
            'projectService',
            'priorityDefinition',
            'statusDefinition',
        ]);

        $this->ensureCanModifyTask(
            $request->user(),
            $task
        );

        abort_if(
            $task->isClosed(),
            422,
            'Closed task cannot be edited.'
        );

        /*
         * Edit form me active priorities show hongi.
         */
        $priorities =
            Task::activePriorities();

        /*
         * Task ki current priority inactive ho chuki
         * ho to bhi edit form me preserve hogi.
         */
        if (
            !array_key_exists(
                $task->priority,
                $priorities
            )
        ) {
            $currentPriority =
                TaskPriority::query()
                    ->where(
                        'slug',
                        $task->priority
                    )
                    ->value('name');

            if ($currentPriority) {
                $priorities[
                    $task->priority
                ] = $currentPriority;
            }
        }

        return view(
            'task::edit',
            [
                'task' =>
                    $task,

                'project' =>
                    $task->project,

                'projectService' =>
                    $task->projectService,

                'users' =>
                    $this->projectUsers(
                        $task->project
                    ),

                'priorities' =>
                    $priorities,

                'defaultPriority' =>
                    Task::defaultPriority(),

                'pageTitle' =>
                    'Edit Task',
            ]
        );
    }

    // public function update(
    //     Request $request,
    //     Task $task,

    // ) {
    //     $task->load('project.members');

    //     $this->ensureCanModifyTask(
    //         $request->user(),
    //         $task
    //     );

    //     $validated = $request->validate(
    //         $this->validationRules()
    //     );

    //     $this->validateTaskAssignee(
    //         $task->project,
    //         $validated['assigned_to'] ?? null
    //     );

    //     $oldValues = $task->toArray();

    //     $task->update([
    //         'title' => $validated['title'],
    //         'description' =>
    //             $validated['description'] ?? null,
    //         'assigned_to' =>
    //             $validated['assigned_to'] ?? null,
    //         'priority' => $validated['priority'],
    //         'requires_review' =>
    //             $request->boolean('requires_review'),
    //         'reviewer_id' =>
    //             $validated['reviewer_id'] ?? null,
    //         'start_date' =>
    //             $validated['start_date'] ?? null,
    //         'due_at' =>
    //             $validated['due_at'] ?? null,
    //         'estimated_hours' =>
    //             $validated['estimated_hours'] ?? null,
    //     ]);

    //     ProjectActivityLogger::log(
    //         $task->project,
    //         'task_updated',
    //         "Task {$task->title} updated.",
    //         $task,
    //         $oldValues,
    //         $task->toArray()
    //     );



    //     return redirect()
    //         ->route('task.show', $task->id)
    //         ->with('success', 'Task updated successfully.');
    // }

    public function update(
        Request $request,
        Task $task,
        CrmNotifier $notifier
    ) {
        $task->load('project.members');

        $this->ensureCanModifyTask(
            $request->user(),
            $task
        );

        /*
         * Update se pehle old assigned user save karo.
         * Isse pata chalega ki task reassign hui ya nahi.
         */
        $oldAssignedTo = $task->assigned_to
            ? (int) $task->assigned_to
            : null;

        $validated = $request->validate(
            $this->validationRules(
                $task
            )
        );

        $this->validateTaskAssignee(
            $task->project,
            $validated['assigned_to'] ?? null
        );

        $oldValues = $task->toArray();

        $newAssignedTo = !empty(
            $validated['assigned_to']
        )
            ? (int) $validated['assigned_to']
            : null;

        if (
            $newAssignedTo !== $oldAssignedTo
            && $task->activeTimeEntries()->exists()
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Task currently has an active timer. End the timer before reassigning the task.'
                );
        }

        $task->update([
            'title' => $validated['title'],

            'description' =>
                $validated['description'] ?? null,

            'assigned_to' =>
                $validated['assigned_to'] ?? null,

            'priority' => $validated['priority'],

            'requires_review' =>
                $request->boolean('requires_review'),

            'reviewer_id' =>
                $validated['reviewer_id'] ?? null,

            'start_date' =>
                $validated['start_date'] ?? null,

            'due_at' =>
                $validated['due_at'] ?? null,

            'estimated_hours' =>
                $validated['estimated_hours'] ?? null,
        ]);

        ProjectActivityLogger::log(
            $task->project,
            'task_updated',
            "Task {$task->title} updated.",
            $task,
            $oldValues,
            $task->toArray()
        );

        /*
         * Update ke baad new assigned user check karo.
         */
        $newAssignedTo = $task->assigned_to
            ? (int) $task->assigned_to
            : null;

        /*
         * Notification sirf tab bhejni hai jab:
         * 1. Task kisi user ko assigned ho.
         * 2. Assigned user change hua ho.
         */
        if (
            $newAssignedTo
            && $newAssignedTo !== $oldAssignedTo
        ) {
            $task->load([
                'assignedUser:id,name,email,is_active',
                'project:id,name,project_code',
            ]);

            $notifier->send(
                $task->assignedUser,
                [
                    'kind' => 'task_reassigned',

                    'title' => 'Task Assigned to You',

                    'message' =>
                        "Task \"{$task->title}\" has been assigned to you in {$task->project->project_code}.",

                    'url' => route(
                        'task.show',
                        $task->id,
                        false
                    ),

                    'icon' => '🔄',
                    'level' => 'info',
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                ],
                null,
                $request->user()
            );
        }

        // return redirect()
        //     ->route('task.show', $task->id)
        //     ->with(
        //         'success',
        //         'Task updated successfully.'
        //     );

        return redirect()
            ->route('task.show', [
                'task' => $task->id,
                'from' => $request->input('from'),
            ])
            ->with('success', 'Task updated successfully.');
    }



    // public function updateStatus(
    //     Request $request,
    //     Task $task,
    //     TaskDependencyManager $dependencyManager,
    //     CrmNotifier $notifier,
    //     TimeTrackingManager $timeTrackingManager
    // ) {
    //     $task->load([
    //         'project.manager:id,name,email,is_active',
    //         'projectService',
    //         'assignedUser:id,name,email,is_active',
    //     ]);

    //     $this->ensureCanModifyTask(
    //         $request->user(),
    //         $task
    //     );

    //     abort_if(
    //         $task->isClosed(),
    //         422,
    //         'Completed or cancelled task status cannot be changed.'
    //     );

    //     $validated = $request->validate([
    //         'status' => [
    //             'required',
    //             Rule::in([
    //                 'to_do',
    //                 'in_progress',
    //                 'completed',
    //                 'cancelled',
    //             ]),
    //         ],

    //         'progress_percent' => [
    //             'nullable',
    //             'integer',
    //             'min:0',
    //             'max:100',
    //         ],
    //     ]);

    //     $newStatus = $validated['status'];

    //     /*
    //      * Pending dependency ke saath task manually
    //      * start ya complete nahi ho sakti.
    //      *
    //      * Cancel allowed rahega.
    //      */
    //     if (
    //         $task->hasIncompleteDependencies()
    //         && $newStatus !== 'cancelled'
    //     ) {
    //         $dependencyManager->syncTaskStatus(
    //             $task
    //         );

    //         return back()->with(
    //             'error',
    //             'This task is blocked. Complete all prerequisite tasks first.'
    //         );
    //     }

    //     /*
    //      * Review-required task direct complete nahi hogi.
    //      */
    //     if (
    //         $newStatus === 'completed'
    //         && $task->requires_review
    //     ) {
    //         return back()->with(
    //             'error',
    //             'Review required task ko pehle In Review submit karein.'
    //         );
    //     }

    //     $oldStatus = $task->status;

    //     if (
    //         in_array(
    //             $newStatus,
    //             [
    //                 'completed',
    //                 'cancelled',
    //             ],
    //             true
    //         )
    //     ) {
    //         $timeTrackingManager->stopTaskTimers(
    //             $task,
    //             $request->user(),
    //             "Task status changed to {$newStatus}"
    //         );
    //     }



    //     $task->update([
    //         'status' => $newStatus,

    //         'progress_percent' =>
    //             $newStatus === 'completed'
    //             ? 100
    //             : (
    //                 $validated['progress_percent']
    //                 ?? $task->progress_percent
    //             ),

    //         'completed_at' =>
    //             $newStatus === 'completed'
    //             ? now()
    //             : null,
    //     ]);

    //     $this->syncServiceStatus(
    //         $task->projectService
    //     );

    //     /*
    //      * Current task ka status change hone ke baad
    //      * dependent tasks ko sync karo.
    //      */
    //     $dependencyManager->syncDependentTasks(
    //         $task
    //     );

    //     ProjectActivityLogger::log(
    //         $task->project,
    //         'task_status_updated',
    //         "Task {$task->title} moved from {$oldStatus} to {$newStatus}.",
    //         $task,
    //         [
    //             'status' => $oldStatus,
    //         ],
    //         [
    //             'status' => $newStatus,
    //         ]
    //     );

    //     /*
    //      * Without-review task complete hone par
    //      * Project Manager ko notification.
    //      */
    //     if ($newStatus === 'completed') {
    //         $assigneeName =
    //             $task->assignedUser?->name
    //             ?? 'A team member';

    //         $notifier->send(
    //             $task->project->manager,
    //             [
    //                 'kind' => 'task_completed',

    //                 'title' => 'Task Completed',

    //                 'message' =>
    //                     "{$assigneeName} completed task \"{$task->title}\".",

    //                 'url' => route(
    //                     'task.show',
    //                     $task->id,
    //                     false
    //                 ),

    //                 'icon' => '✅',
    //                 'level' => 'success',
    //                 'task_id' => $task->id,
    //                 'project_id' => $task->project_id,
    //             ],
    //             null,
    //             $request->user()
    //         );
    //     }

    //     /*
    //      * Task cancel hone par assigned user ko notification.
    //      */
    //     if ($newStatus === 'cancelled') {
    //         $notifier->send(
    //             $task->assignedUser,
    //             [
    //                 'kind' => 'task_cancelled',

    //                 'title' => 'Task Cancelled',

    //                 'message' =>
    //                     "Task \"{$task->title}\" has been cancelled.",

    //                 'url' => route(
    //                     'task.show',
    //                     $task->id,
    //                     false
    //                 ),

    //                 'icon' => '🚫',
    //                 'level' => 'danger',
    //                 'task_id' => $task->id,
    //                 'project_id' => $task->project_id,
    //             ],
    //             null,
    //             $request->user()
    //         );
    //     }

    //     return back()->with(
    //         'success',
    //         'Task status updated successfully.'
    //     );
    // }

    // public function updateStatus(
    //     Request $request,
    //     Task $task,
    //     TaskDependencyManager $dependencyManager,
    //     CrmNotifier $notifier,
    //     TimeTrackingManager $timeTrackingManager
    // ) {
    //     $task->load([
    //         'project.manager:id,name,email,is_active',

    //         'projectService',

    //         'assignedUser:id,name,email,is_active',

    //         'statusDefinition:id,slug,name,color,is_closed,system_key,is_manual_selectable',
    //     ]);

    //     $this->ensureCanModifyTask(
    //         $request->user(),
    //         $task
    //     );

    //     abort_if(
    //         $task->isClosed(),
    //         422,
    //         'Closed Task status cannot be changed.'
    //     );

    //     $validated = $request->validate([
    //         'status' => [
    //             'required',

    //             /*
    //              * Settings me active aur manually
    //              * selectable statuses hi allowed.
    //              */
    //             Rule::in(
    //                 array_keys(
    //                     Task::manualStatuses()
    //                 )
    //             ),
    //         ],

    //         'progress_percent' => [
    //             'nullable',
    //             'integer',
    //             'min:0',
    //             'max:100',
    //         ],
    //     ]);

    //     $statusDefinition =
    //         TaskStatus::query()
    //             ->active()
    //             ->manual()
    //             ->where(
    //                 'slug',
    //                 $validated['status']
    //             )
    //             ->firstOrFail();

    //     $newStatus =
    //         $statusDefinition->slug;

    //     $systemKey =
    //         $statusDefinition->system_key;

    //     $isCompleted =
    //         $systemKey === 'completed';

    //     $isCancelled =
    //         $systemKey === 'cancelled';

    //     /*
    //      * Incomplete dependency wali Task kisi
    //      * working status me move nahi hogi.
    //      *
    //      * Cancel karna allowed rahega.
    //      */
    //     if (
    //         $task->hasIncompleteDependencies()
    //         && !$isCancelled
    //     ) {
    //         $dependencyManager->syncTaskStatus(
    //             $task
    //         );

    //         return back()->with(
    //             'error',
    //             'This task is blocked. Complete all prerequisite tasks first.'
    //         );
    //     }

    //     /*
    //      * Review required Task direct complete
    //      * nahi ho sakti.
    //      */
    //     if (
    //         $isCompleted
    //         && $task->requires_review
    //     ) {
    //         return back()->with(
    //             'error',
    //             'Review required task ko pehle In Review submit karein.'
    //         );
    //     }

    //     $oldStatus =
    //         $task->status;

    //     /*
    //      * Kisi bhi closed status par Task timer stop.
    //      */
    //     if ($statusDefinition->is_closed) {
    //         $timeTrackingManager
    //             ->stopTaskTimers(
    //                 $task,
    //                 $request->user(),
    //                 "Task status changed to {$newStatus}"
    //             );
    //     }

    //     $task->update([
    //         'status' =>
    //             $newStatus,

    //         'progress_percent' =>
    //             $isCompleted
    //             ? 100
    //             : (
    //                 $validated[
    //                     'progress_percent'
    //                 ]
    //                 ?? $task
    //                     ->progress_percent
    //             ),

    //         'completed_at' =>
    //             $isCompleted
    //             ? now()
    //             : null,
    //     ]);

    //     $this->syncServiceStatus(
    //         $task->projectService
    //     );

    //     /*
    //      * Status change ke baad dependent
    //      * Tasks ka status sync karo.
    //      */
    //     $dependencyManager
    //         ->syncDependentTasks(
    //             $task
    //         );

    //     ProjectActivityLogger::log(
    //         $task->project,
    //         'task_status_updated',
    //         "Task {$task->title} moved from {$oldStatus} to {$newStatus}.",
    //         $task,
    //         [
    //             'status' =>
    //                 $oldStatus,
    //         ],
    //         [
    //             'status' =>
    //                 $newStatus,
    //         ]
    //     );

    //     /*
    //      * Completed notification.
    //      */
    //     if ($isCompleted) {
    //         $assigneeName =
    //             $task->assignedUser?->name
    //             ?? 'A team member';

    //         $notifier->send(
    //             $task->project->manager,
    //             [
    //                 'kind' =>
    //                     'task_completed',

    //                 'title' =>
    //                     'Task Completed',

    //                 'message' =>
    //                     "{$assigneeName} completed task \"{$task->title}\".",

    //                 'url' => route(
    //                     'task.show',
    //                     $task->id,
    //                     false
    //                 ),

    //                 'icon' =>
    //                     '✅',

    //                 'level' =>
    //                     'success',

    //                 'task_id' =>
    //                     $task->id,

    //                 'project_id' =>
    //                     $task->project_id,
    //             ],
    //             null,
    //             $request->user()
    //         );
    //     }

    //     /*
    //      * Cancelled notification.
    //      */
    //     if ($isCancelled) {
    //         $notifier->send(
    //             $task->assignedUser,
    //             [
    //                 'kind' =>
    //                     'task_cancelled',

    //                 'title' =>
    //                     'Task Cancelled',

    //                 'message' =>
    //                     "Task \"{$task->title}\" has been cancelled.",

    //                 'url' => route(
    //                     'task.show',
    //                     $task->id,
    //                     false
    //                 ),

    //                 'icon' =>
    //                     '🚫',

    //                 'level' =>
    //                     'danger',

    //                 'task_id' =>
    //                     $task->id,

    //                 'project_id' =>
    //                     $task->project_id,
    //             ],
    //             null,
    //             $request->user()
    //         );
    //     }

    //     return back()->with(
    //         'success',
    //         'Task status updated successfully.'
    //     );
    // }

    public function updateStatus(
        Request $request,
        Task $task,
        TaskDependencyManager $dependencyManager,
        CrmNotifier $notifier,
        TimeTrackingManager $timeTrackingManager
    ) {
        $task->load([
            'project.manager:id,name,email,is_active',
            'projectService',
            'assignedUser:id,name,email,is_active',
            'statusDefinition',
        ]);

        $this->ensureCanModifyTask(
            $request->user(),
            $task
        );

        abort_if(
            $task->isClosed(),
            422,
            'Closed Task status cannot be changed.'
        );

        $validated = $request->validate([
            'status' => [
                'required',

                Rule::in(
                    array_keys(
                        Task::manualStatuses()
                    )
                ),
            ],

            'progress_percent' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
        ]);

        $statusDefinition =
            TaskStatus::query()
                ->active()
                ->manual()
                ->where(
                    'slug',
                    $validated['status']
                )
                ->firstOrFail();

        $newStatus =
            $statusDefinition->slug;

        $newSystemKey =
            $statusDefinition->system_key;

        $isCompleted =
            $newSystemKey === 'completed';

        $isCancelled =
            $newSystemKey === 'cancelled';

        /*
         * Pending dependency ke saath Task
         * start, custom status ya complete nahi hogi.
         *
         * Cancel allowed rahega.
         */
        if (
            $task->hasIncompleteDependencies()
            && !$isCancelled
        ) {
            $dependencyManager->syncTaskStatus(
                $task
            );

            return back()->with(
                'error',
                'This task is blocked. Complete all prerequisite tasks first.'
            );
        }

        /*
         * Review-required Task direct complete nahi hogi.
         */
        if (
            $isCompleted
            && $task->requires_review
        ) {
            return back()->with(
                'error',
                'Review required Task ko pehle In Review submit karein.'
            );
        }

        $oldStatus =
            $task->status;

        /*
         * Closed status par running timers stop.
         */
        if ($statusDefinition->is_closed) {
            $timeTrackingManager->stopTaskTimers(
                $task,
                $request->user(),
                "Task status changed to {$newStatus}"
            );
        }

        $task->update([
            'status' =>
                $newStatus,

            'progress_percent' =>
                $isCompleted
                ? 100
                : (
                    $validated[
                        'progress_percent'
                    ]
                    ?? $task
                        ->progress_percent
                ),

            'completed_at' =>
                $isCompleted
                ? now()
                : null,
        ]);

        $this->syncServiceStatus(
            $task->projectService
        );

        $dependencyManager->syncDependentTasks(
            $task
        );

        ProjectActivityLogger::log(
            $task->project,
            'task_status_updated',
            "Task {$task->title} moved from {$oldStatus} to {$newStatus}.",
            $task,
            [
                'status' => $oldStatus,
            ],
            [
                'status' => $newStatus,
            ]
        );

        if ($isCompleted) {
            $assigneeName =
                $task->assignedUser?->name
                ?? 'A team member';

            $notifier->send(
                $task->project->manager,
                [
                    'kind' => 'task_completed',

                    'title' => 'Task Completed',

                    'message' =>
                        "{$assigneeName} completed task \"{$task->title}\".",

                    'url' => route(
                        'task.show',
                        $task->id,
                        false
                    ),

                    'icon' => '✅',
                    'level' => 'success',
                    'task_id' => $task->id,
                    'project_id' =>
                        $task->project_id,
                ],
                null,
                $request->user()
            );
        }

        if ($isCancelled) {
            $notifier->send(
                $task->assignedUser,
                [
                    'kind' => 'task_cancelled',

                    'title' => 'Task Cancelled',

                    'message' =>
                        "Task \"{$task->title}\" has been cancelled.",

                    'url' => route(
                        'task.show',
                        $task->id,
                        false
                    ),

                    'icon' => '🚫',
                    'level' => 'danger',
                    'task_id' => $task->id,
                    'project_id' =>
                        $task->project_id,
                ],
                null,
                $request->user()
            );
        }

        return back()->with(
            'success',
            'Task status updated successfully.'
        );
    }

    public function submitReview(
        Request $request,
        Task $task,
        TaskDependencyManager $dependencyManager,
        CrmNotifier $notifier,
        TimeTrackingManager $timeTrackingManager
    ) {
        $task->load([
            'project.manager:id,name,email,is_active',
            'assignedUser:id,name,email,is_active',
        ]);

        $this->ensureCanModifyTask(
            $request->user(),
            $task
        );

        if (!$task->requires_review) {
            return back()->with(
                'error',
                'This task does not require review.'
            );
        }

        /*
         * Blocked task review me submit nahi hogi.
         */
        if ($task->hasIncompleteDependencies()) {
            $dependencyManager->syncTaskStatus(
                $task
            );

            return back()->with(
                'error',
                'Complete all prerequisite tasks before submitting this task for review.'
            );
        }

        // abort_unless(
        //     $task->status === 'in_progress',
        //     422,
        //     'Only an In Progress task can be submitted for review.'
        // );

        abort_unless(
            $task->isInProgress(),
            422,
            'Only an In Progress task can be submitted for review.'
        );

        $timeTrackingManager->stopTaskTimers(
            $task,
            $request->user(),
            'Task submitted for review'
        );

        $task->update([
            // 'status' =>
            //     Task::inReviewStatus(),
            'status' =>
                Task::inReviewStatus(),
            'progress_percent' => 100,
            'submitted_for_review_at' => now(),
            'reviewed_at' => null,
            'review_note' => null,
        ]);

        ProjectActivityLogger::log(
            $task->project,
            'task_submitted_for_review',
            "Task {$task->title} submitted for review.",
            $task
        );

        /*
         * Specific reviewer select kiya hai to use notification.
         * Otherwise Project Manager ko notification.
         */
        $reviewer = null;

        if ($task->reviewer_id) {
            $reviewer = User::query()
                ->find($task->reviewer_id);
        }

        if (!$reviewer) {
            $reviewer = $task->project->manager;
        }

        $submitterName =
            $task->assignedUser?->name
            ?? $request->user()->name;

        $notifier->send(
            $reviewer,
            [
                'kind' => 'task_review',

                'title' => 'Task Submitted for Review',

                'message' =>
                    "{$submitterName} submitted \"{$task->title}\" for review.",

                'url' => route(
                    'task.show',
                    $task->id,
                    false
                ),

                'icon' => '🔍',
                'level' => 'warning',
                'task_id' => $task->id,
                'project_id' => $task->project_id,
            ],
            null,
            $request->user()
        );

        return back()->with(
            'success',
            'Task submitted for review.'
        );
    }

    // public function approve(
    //     Request $request,
    //     Task $task,
    //     TaskDependencyManager $dependencyManager
    // ) {
    //     $task->load('project');

    //     $this->ensureCanAccessProject(
    //         $request->user(),
    //         $task->project
    //     );

    //     abort_unless(
    //         $task->status === 'in_review',
    //         422,
    //         'Only task in review can be approved.'
    //     );

    //     $validated = $request->validate([
    //         'review_note' => [
    //             'nullable',
    //             'string',
    //             'max:3000',
    //         ],
    //     ]);

    //     $task->update([
    //         'status' => 'completed',
    //         'progress_percent' => 100,
    //         'reviewer_id' => $request->user()->id,
    //         'reviewed_at' => now(),
    //         'review_note' =>
    //             $validated['review_note'] ?? null,
    //         'completed_at' => now(),
    //     ]);

    //     $this->syncServiceStatus(
    //         $task->projectService
    //     );

    //     /*
    //      * Review approval ke baad Task Completed ho gayi.
    //      * Ab dependent Tasks automatically unlock ho sakti hain.
    //      */
    //     $dependencyManager->syncDependentTasks(
    //         $task
    //     );

    //     ProjectActivityLogger::log(
    //         $task->project,
    //         'task_approved',
    //         "Task {$task->title} approved and completed.",
    //         $task
    //     );

    //     return back()->with(
    //         'success',
    //         'Task approved successfully.'
    //     );
    // }

    public function approve(
        Request $request,
        Task $task,
        TaskDependencyManager $dependencyManager,
        CrmNotifier $notifier
    ) {
        $task->load([
            'project',
            'projectService',
            'assignedUser:id,name,email,is_active',
        ]);

        $this->ensureCanAccessProject(
            $request->user(),
            $task->project
        );

        // abort_unless(
        //     $task->status === 'in_review',
        //     422,
        //     'Only task in review can be approved.'
        // );

        abort_unless(
            $task->isInReview(),
            422,
            'Only task in review can be approved.'
        );

        $validated = $request->validate([
            'review_note' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ]);

        $task->update([
            'status' =>
                Task::completedStatus(),
            'progress_percent' => 100,
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),

            'review_note' =>
                $validated['review_note'] ?? null,

            'completed_at' => now(),
        ]);

        $this->syncServiceStatus(
            $task->projectService
        );

        /*
         * Task complete hone ke baad dependent tasks
         * automatically unlock ho sakti hain.
         */
        $dependencyManager->syncDependentTasks(
            $task
        );

        ProjectActivityLogger::log(
            $task->project,
            'task_approved',
            "Task {$task->title} approved and completed.",
            $task
        );

        /*
         * Assigned user ko approval notification.
         */
        $notifier->send(
            $task->assignedUser,
            [
                'kind' => 'task_approved',

                'title' => 'Task Approved',

                'message' =>
                    "Your task \"{$task->title}\" has been approved and completed.",

                'url' => route(
                    'task.show',
                    $task->id,
                    false
                ),

                'icon' => '🎉',
                'level' => 'success',
                'task_id' => $task->id,
                'project_id' => $task->project_id,
            ],
            null,
            $request->user()
        );

        return back()->with(
            'success',
            'Task approved successfully.'
        );
    }


    public function reject(
        Request $request,
        Task $task,
        CrmNotifier $notifier
    ) {
        $task->load([
            'project',
            'assignedUser:id,name,email,is_active',
        ]);

        $this->ensureCanAccessProject(
            $request->user(),
            $task->project
        );

        /*
         * Sirf In Review task reject/return hogi.
         */
        abort_unless(
            $task->isInReview(),
            422,
            'Only task in review can be returned for changes.'
        );

        $validated = $request->validate([
            'review_note' => [
                'required',
                'string',
                'max:3000',
            ],
        ]);

        $task->update([
            'status' =>
                Task::inProgressStatus(),
            'progress_percent' => 90,
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'],
            'completed_at' => null,
        ]);

        ProjectActivityLogger::log(
            $task->project,
            'task_rejected',
            "Task {$task->title} returned for changes.",
            $task
        );

        /*
         * Assigned user ko changes-requested notification.
         */
        $notifier->send(
            $task->assignedUser,
            [
                'kind' => 'task_changes_requested',

                'title' => 'Changes Requested',

                'message' =>
                    "Changes were requested for task \"{$task->title}\". Review the feedback and update your work.",

                'url' => route(
                    'task.show',
                    $task->id,
                    false
                ),

                'icon' => '↩️',
                'level' => 'warning',
                'task_id' => $task->id,
                'project_id' => $task->project_id,
            ],
            null,
            $request->user()
        );

        return back()->with(
            'success',
            'Task returned for changes.'
        );
    }

    public function destroy(
        Request $request,
        Task $task
    ) {
        $task->load('project');

        $this->ensureCanModifyTask(
            $request->user(),
            $task
        );

        if ($task->activeTimeEntries()->exists()) {
            return back()->with(
                'error',
                'This task has an active timer. End the timer before deleting the task.'
            );
        }

        /*
         * Dependency relation wali Task ko direct delete mat karo.
         * Pehle dependency remove karni hogi.
         */
        if (
            $task->dependencyLinks()->exists()
            || $task->dependentLinks()->exists()
        ) {
            return back()->with(
                'error',
                'This task is connected to task dependencies. Remove its dependencies before deleting it.'
            );
        }

        ProjectActivityLogger::log(
            $task->project,
            'task_deleted',
            "Task {$task->title} deleted.",
            $task
        );

        $task->delete();

        return redirect()
            ->route('project.show', $task->project_id)
            ->with('success', 'Task deleted successfully.');
    }

    // private function validationRules(): array
    // {
    //     return [
    //         'title' => [
    //             'required',
    //             'string',
    //             'max:255',
    //         ],
    //         'description' => [
    //             'nullable',
    //             'string',
    //             'max:10000',
    //         ],
    //         'assigned_to' => [
    //             'nullable',
    //             'integer',
    //             'exists:users,id',
    //         ],
    //         'priority' => [
    //             'required',
    //             Rule::in(
    //                 array_keys(Task::priorities())
    //             ),
    //         ],
    //         'requires_review' => [
    //             'nullable',
    //             'boolean',
    //         ],
    //         'reviewer_id' => [
    //             'nullable',
    //             'integer',
    //             'exists:users,id',
    //         ],
    //         'start_date' => [
    //             'nullable',
    //             'date',
    //         ],
    //         'due_at' => [
    //             'nullable',
    //             'date',
    //         ],
    //         'estimated_hours' => [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ],
    //     ];
    // }

    private function validationRules(
        ?Task $task = null
    ): array {
        /*
         * New Task ke liye sirf active priorities.
         */
        $allowedPriorities =
            array_keys(
                Task::activePriorities()
            );

        /*
         * Existing Task edit karte waqt uski
         * inactive priority preserve ho sake.
         */
        if ($task) {
            $allowedPriorities[] =
                $task->priority;
        }

        $allowedPriorities =
            array_values(
                array_unique(
                    $allowedPriorities
                )
            );

        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'priority' => [
                'required',

                Rule::in(
                    $allowedPriorities
                ),
            ],

            'requires_review' => [
                'nullable',
                'boolean',
            ],

            'reviewer_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'due_at' => [
                'nullable',
                'date',
            ],

            'estimated_hours' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    private function projectUsers(Project $project)
    {
        $memberIds = $project->members
            ->pluck('id');

        if ($project->project_manager_id) {
            $memberIds->push(
                $project->project_manager_id
            );
        }

        return User::query()
            ->whereIn('id', $memberIds->unique())
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
                'is_active',
            ]);
    }

    private function validateTaskAssignee(
        Project $project,
        ?int $userId
    ): void {
        if (!$userId) {
            return;
        }

        $allowed = (int) $project->project_manager_id
            === $userId
            || $project->members()
                ->where('users.id', $userId)
                ->exists();

        abort_unless(
            $allowed,
            422,
            'Task sirf Project Manager ya Project Member ko assign ho sakti hai.'
        );
    }

    // private function syncServiceStatus(
    //     ProjectService $service
    // ): void {
    //     $total = $service->tasks()
    //         ->where('status', '!=', 'cancelled')
    //         ->count();

    //     if ($total === 0) {
    //         $service->update([
    //             'status' => 'pending',
    //             'completed_at' => null,
    //         ]);

    //         return;
    //     }

    //     $completed = $service->tasks()
    //         ->where('status', 'completed')
    //         ->count();

    //     $inReview = $service->tasks()
    //         ->where('status', 'in_review')
    //         ->exists();

    //     if ($completed === $total) {
    //         $service->update([
    //             'status' => 'completed',
    //             'completed_at' => now(),
    //         ]);
    //     } elseif ($inReview) {
    //         $service->update([
    //             'status' => 'in_review',
    //             'completed_at' => null,
    //         ]);
    //     } else {
    //         $service->update([
    //             'status' => 'in_progress',
    //             'completed_at' => null,
    //         ]);
    //     }
    // }

    private function syncServiceStatus(
        ProjectService $service
    ): void {
        /*
         * Task status master table se core
         * workflow slugs resolve karo.
         */
        $cancelledStatus =
            Task::cancelledStatus();

        $completedStatus =
            Task::completedStatus();

        $inReviewStatus =
            Task::inReviewStatus();

        $total = $service
            ->tasks()
            ->where(
                'status',
                '!=',
                $cancelledStatus
            )
            ->count();

        if ($total === 0) {
            $service->update([
                'status' =>
                    'pending',

                'completed_at' =>
                    null,
            ]);

            return;
        }

        $completed = $service
            ->tasks()
            ->where(
                'status',
                $completedStatus
            )
            ->count();

        $inReview = $service
            ->tasks()
            ->where(
                'status',
                $inReviewStatus
            )
            ->exists();

        if ($completed === $total) {
            $service->update([
                'status' =>
                    'completed',

                'completed_at' =>
                    now(),
            ]);
        } elseif ($inReview) {
            $service->update([
                'status' =>
                    'in_review',

                'completed_at' =>
                    null,
            ]);
        } else {
            $service->update([
                'status' =>
                    'in_progress',

                'completed_at' =>
                    null,
            ]);
        }
    }
}