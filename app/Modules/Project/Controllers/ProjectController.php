<?php

namespace App\Modules\Project\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Client\Models\Client;
use App\Modules\Notification\Support\CrmNotifier;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Support\AuthorizesProjectAccess;
use App\Modules\Project\Support\ProjectActivityLogger;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Modules\Task\Models\Task;

class ProjectController extends Controller
{
    use AuthorizesProjectAccess;

    public function index(Request $request)
    {
        $allowedPerPage = [
            10,
            25,
            50,
            100,
        ];

        $perPage = (int) $request->query(
            'per_page',
            10
        );

        if (
            !in_array(
                $perPage,
                $allowedPerPage,
                true
            )
        ) {
            $perPage = 10;
        }

        $user = $request->user();

        $canViewAll = $this->canViewAllProjects(
            $user
        );

        $search = trim(
            $request->query('search', '')
        );

        $status = trim(
            $request->query('status', '')
        );

        $priority = trim(
            $request->query('priority', '')
        );

        $clientId = (int) $request->query(
            'client_id',
            0
        );

        $managerId = (int) $request->query(
            'manager_id',
            0
        );

        $cancelledTaskStatus =
            Task::cancelledStatus();

        $completedTaskStatus =
            Task::completedStatus();

        $query = Project::query()
            ->with([
                'client:id,name,company',
                'manager:id,name,email',
            ])
            ->withCount([
                'tasks as total_tasks' =>
                    function ($query) use ($cancelledTaskStatus) {
                        $query->where(
                            'status',
                            '!=',
                            $cancelledTaskStatus
                        );
                    },

                'tasks as completed_tasks' =>
                    function ($query) use ($completedTaskStatus) {
                        $query->where(
                            'status',
                            $completedTaskStatus
                        );
                    },
            ])
            ->latest();

        if (!$canViewAll) {
            $query->where(
                function (Builder $q) use ($user) {
                    $q->where(
                        'project_manager_id',
                        $user->id
                    )->orWhereHas(
                            'members',
                            fn(Builder $memberQuery) =>
                            $memberQuery->where(
                                'users.id',
                                $user->id
                            )
                        );
                }
            );
        }

        if ($search !== '') {
            $query->where(
                function (Builder $q) use ($search) {
                    $q->where(
                        'project_code',
                        'LIKE',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'name',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'client',
                            fn(Builder $clientQuery) =>
                            $clientQuery
                                ->where(
                                    'name',
                                    'LIKE',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'company',
                                    'LIKE',
                                    "%{$search}%"
                                )
                        );
                }
            );
        }

        if (
            $status !== ''
            && array_key_exists(
                $status,
                Project::statuses()
            )
        ) {
            $query->where(
                'status',
                $status
            );
        }

        if (
            $priority !== ''
            && array_key_exists(
                $priority,
                Project::priorities()
            )
        ) {
            $query->where(
                'priority',
                $priority
            );
        }

        if ($clientId > 0) {
            $query->where(
                'client_id',
                $clientId
            );
        }

        if (
            $managerId > 0
            && $canViewAll
        ) {
            $query->where(
                'project_manager_id',
                $managerId
            );
        }

        $projects = $query
            ->paginate($perPage)
            ->withQueryString();

        return view('project::index', [
            'projects' => $projects,

            'clients' => $this->accessibleClients(
                $user
            ),

            'managers' => $canViewAll
                ? $this->activeUsers()
                : collect(),

            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'priority' => $priority,
            'clientId' => $clientId,
            'managerId' => $managerId,
            'canViewAll' => $canViewAll,
            'statuses' => Project::statuses(),
            'priorities' => Project::priorities(),
            'pageTitle' => 'Project Management',
        ]);
    }

    public function create(Request $request)
    {
        return view('project::create', [
            'clients' => $this->accessibleClients(
                $request->user()
            ),

            'users' => $this->activeUsers(),

            'statuses' => Project::statuses(),

            'priorities' => Project::priorities(),

            'pageTitle' => 'Add Project',
        ]);
    }

    /**
     * New Project create karega.
     *
     * Project Manager assign hone par manager ko
     * In-App Notification bhi bhejega.
     */
    public function store(
        Request $request,
        CrmNotifier $notifier
    ) {
        $validated = $request->validate(
            $this->validationRules()
        );

        $client = $this
            ->accessibleClients(
                $request->user()
            )
            ->firstWhere(
                'id',
                (int) $validated['client_id']
            );

        abort_unless(
            $client,
            403,
            'You cannot create a project for this client.'
        );

        $project = DB::transaction(
            function () use ($request, $validated) {
                $project = Project::create([
                    'project_code' => null,

                    'client_id' =>
                        $validated['client_id'],

                    'name' =>
                        $validated['name'],

                    'description' =>
                        $validated['description']
                        ?? null,

                    'project_manager_id' =>
                        $validated['project_manager_id']
                        ?? null,

                    'priority' =>
                        $validated['priority'],

                    'status' =>
                        $validated['status'],

                    'start_date' =>
                        $validated['start_date']
                        ?? null,

                    'due_date' =>
                        $validated['due_date']
                        ?? null,

                    'budget' =>
                        $validated['budget']
                        ?? null,

                    'created_by' =>
                        $request->user()->id,
                ]);

                $project->update([
                    'project_code' => sprintf(
                        'PRJ-%s-%04d',
                        now()->format('Y'),
                        $project->id
                    ),
                ]);

                /*
                 * Project Manager ko project members me bhi add karo.
                 */
                if ($project->project_manager_id) {
                    $project
                        ->members()
                        ->syncWithoutDetaching([
                            $project->project_manager_id => [
                                'member_role' =>
                                    'Project Manager',

                                'added_by' =>
                                    $request->user()->id,
                            ],
                        ]);
                }

                ProjectActivityLogger::log(
                    $project,
                    'project_created',
                    "Project {$project->project_code} created.",
                    $project
                );

                return $project;
            }
        );

        /*
         * Transaction successful hone ke baad
         * assigned Project Manager ko notification bhejo.
         */
        $project->load([
            'manager:id,name,email,is_active',
        ]);

        $notifier->send(
            $project->manager,
            [
                'kind' => 'project_assigned',

                'title' => 'Project Assigned',

                'message' =>
                    "You have been assigned as Project Manager for {$project->project_code} — {$project->name}.",

                'url' => route(
                    'project.show',
                    $project->id,
                    false
                ),

                'icon' => '📁',

                'level' => 'info',

                'project_id' => $project->id,
            ],
            null,
            $request->user()
        );

        return redirect()
            ->route(
                'project.show',
                $project->id
            )
            ->with(
                'success',
                'Project created successfully.'
            );
    }

    public function show(
        Request $request,
        Project $project
    ) {
        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        $project->load([
            'client:id,name,phone,email,company',

            'manager:id,name,email',

            'creator:id,name,email',

            'members:id,name,email,is_active',

            'services' => fn($q) =>
                $q->orderBy('sort_order')
                    ->orderBy('id'),

            'services.assignedUser:id,name,email',

            'services.tasks.assignedUser:id,name,email',

            'services.tasks.prerequisiteTasks:id,title,status,project_id',

            'services.tasks.statusDefinition:id,slug,name,color,is_closed,system_key',

            'services.tasks.priorityDefinition:id,slug,name,color',

            'services.tasks.prerequisiteTasks.statusDefinition:id,slug,name,color,is_closed,system_key',
        ]);

        $activities = $project
            ->activities()
            ->with(
                'user:id,name,email'
            )
            ->latest()
            ->limit(20)
            ->get();

        $projectTimeEntries = $project
            ->timeEntries()
            ->with([
                'user:id,name,email',
                'role:id,name',
            ])
            ->get();

        $projectTrackedSeconds = (int) 
            $projectTimeEntries->sum(
                fn($entry) =>
                $entry->liveSeconds()
            );

        $projectTimeByUser = $projectTimeEntries
            ->groupBy('user_id')
            ->map(function ($entries) {
                $first = $entries->first();

                return [
                    'name' =>
                        $first->user?->name
                        ?? $first->user_name_snapshot
                        ?? 'Deleted User',

                    'role' =>
                        $first->role?->name
                        ?? $first->role_name_snapshot
                        ?? '-',

                    'seconds' => (int) 
                        $entries->sum(
                            fn($entry) =>
                            $entry->liveSeconds()
                        ),
                ];
            })
            ->sortByDesc('seconds')
            ->values();

        $availableUsers = $this
            ->activeUsers()
            ->reject(
                fn(User $user) =>
                $project
                    ->members
                    ->contains(
                        'id',
                        $user->id
                    )
            );

        return view('project::show', [
            'project' => $project,

            'activities' => $activities,

            'availableUsers' => $availableUsers,

            'projectTrackedSeconds' =>
                $projectTrackedSeconds,

            'projectTimeByUser' =>
                $projectTimeByUser,

            'projectStatuses' =>
                Project::statuses(),

            'projectPriorities' =>
                Project::priorities(),

            'serviceStatuses' =>
                \App\Modules\Project\Models\ProjectService::statuses(),

            'taskStatuses' =>
                \App\Modules\Task\Models\Task::statuses(),

            'taskPriorities' =>
                \App\Modules\Task\Models\Task::priorities(),

            'pageTitle' => 'Project Details',
        ]);
    }

    public function edit(
        Request $request,
        Project $project
    ) {
        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        if ($project->isClosed()) {
            return redirect()
                ->route(
                    'project.show',
                    $project->id
                )
                ->with(
                    'error',
                    'Completed or cancelled project cannot be edited.'
                );
        }

        return view('project::edit', [
            'project' => $project,

            'clients' => $this->accessibleClients(
                $request->user()
            ),

            'users' => $this->activeUsers(
                $project->project_manager_id
            ),

            'statuses' => Project::statuses(),

            'priorities' => Project::priorities(),

            'pageTitle' => 'Edit Project',
        ]);
    }

    /**
     * Existing Project update karega.
     *
     * Project Manager change hone par naye manager ko
     * In-App Notification bhejega.
     */
    public function update(
        Request $request,
        Project $project,
        CrmNotifier $notifier
    ) {
        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        abort_if(
            $project->isClosed(),
            422,
            'Closed project cannot be edited.'
        );

        $validated = $request->validate(
            $this->validationRules(
                $project
            )
        );

        $oldValues = $project->only([
            'client_id',
            'name',
            'project_manager_id',
            'priority',
            'status',
            'start_date',
            'due_date',
            'budget',
        ]);

        /*
         * Update se pehle old manager ID save karo.
         */
        $oldManagerId =
            $project->project_manager_id
            ? (int) $project->project_manager_id
            : null;

        DB::transaction(
            function () use ($request, $project, $validated, $oldValues) {
                $project->update([
                    'client_id' =>
                        $validated['client_id'],

                    'name' =>
                        $validated['name'],

                    'description' =>
                        $validated['description']
                        ?? null,

                    'project_manager_id' =>
                        $validated['project_manager_id']
                        ?? null,

                    'priority' =>
                        $validated['priority'],

                    'status' =>
                        $validated['status'],

                    'start_date' =>
                        $validated['start_date']
                        ?? null,

                    'due_date' =>
                        $validated['due_date']
                        ?? null,

                    'budget' =>
                        $validated['budget']
                        ?? null,
                ]);

                /*
                 * Naye Project Manager ko project member bhi banao.
                 */
                if ($project->project_manager_id) {
                    $project
                        ->members()
                        ->syncWithoutDetaching([
                            $project->project_manager_id => [
                                'member_role' =>
                                    'Project Manager',

                                'added_by' =>
                                    $request->user()->id,
                            ],
                        ]);
                }

                ProjectActivityLogger::log(
                    $project,
                    'project_updated',
                    "Project {$project->project_code} updated.",
                    $project,
                    $oldValues,
                    $project->only(
                        array_keys($oldValues)
                    )
                );
            }
        );

        /*
         * Update ke baad new manager ID nikalo.
         */
        $newManagerId =
            $project->project_manager_id
            ? (int) $project->project_manager_id
            : null;

        /*
         * Notification tabhi bhejo jab:
         *
         * 1. New manager selected ho.
         * 2. Manager actually change hua ho.
         */
        if (
            $newManagerId
            && $newManagerId !== $oldManagerId
        ) {
            $project->load([
                'manager:id,name,email,is_active',
            ]);

            $notifier->send(
                $project->manager,
                [
                    'kind' => 'project_assigned',

                    'title' => 'Project Assigned',

                    'message' =>
                        "You are now the Project Manager for {$project->project_code} — {$project->name}.",

                    'url' => route(
                        'project.show',
                        $project->id,
                        false
                    ),

                    'icon' => '📁',

                    'level' => 'info',

                    'project_id' =>
                        $project->id,
                ],
                null,
                $request->user()
            );
        }

        return redirect()
            ->route(
                'project.show',
                $project->id
            )
            ->with(
                'success',
                'Project updated successfully.'
            );
    }

    /**
     * Project complete karega.
     *
     * Completion ke baad Project Manager aur
     * sabhi Project Members ko notification bhejega.
     */
    public function complete(
        Request $request,
        Project $project,
        CrmNotifier $notifier
    ) {
        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        $activeTimerCount = $project
            ->timeEntries()
            ->whereIn(
                'status',
                [
                    'running',
                    'paused',
                ]
            )
            ->count();

        if ($activeTimerCount > 0) {
            return back()->with(
                'error',
                'Project has active work timers. End all timers before completing the project.'
            );
        }

        $cancelledTaskStatus =
            Task::cancelledStatus();

        $completedTaskStatus =
            Task::completedStatus();

        $taskCount = $project
            ->tasks()
            ->where(
                'status',
                '!=',
                $cancelledTaskStatus
            )
            ->count();

        $incompleteCount = $project
            ->tasks()
            ->whereNotIn(
                'status',
                [
                    $completedTaskStatus,
                    $cancelledTaskStatus,
                ]
            )
            ->count();

        if ($taskCount === 0) {
            return back()->with(
                'error',
                'Project complete karne ke liye kam se kam ek task honi chahiye.'
            );
        }

        if ($incompleteCount > 0) {
            return back()->with(
                'error',
                'Sab non-cancelled tasks complete hone ke baad project complete hoga.'
            );
        }

        $project->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        ProjectActivityLogger::log(
            $project,
            'project_completed',
            "Project {$project->project_code} completed.",
            $project
        );

        /*
         * Project Manager aur Project Members load karo.
         */
        $project->load([
            'manager:id,name,email,is_active',

            'members:id,name,email,is_active',
        ]);

        /*
         * Manager ko members collection ke saath combine karo.
         *
         * unique('id') duplicate recipient ko remove karega,
         * kyunki Project Manager members table me bhi ho sakta hai.
         */
        $recipients = $project
            ->members
            ->concat([
                $project->manager,
            ])
            ->filter()
            ->unique('id')
            ->values();

        $notifier->sendMany(
            $recipients,
            [
                'kind' => 'project_completed',

                'title' => 'Project Completed',

                'message' =>
                    "Project {$project->project_code} — {$project->name} has been completed.",

                'url' => route(
                    'project.show',
                    $project->id,
                    false
                ),

                'icon' => '🏆',

                'level' => 'success',

                'project_id' => $project->id,
            ],
            null,
            $request->user()
        );

        return back()->with(
            'success',
            'Project completed successfully.'
        );
    }

    public function destroy(
        Request $request,
        Project $project
    ) {
        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        if ($project->tasks()->exists()) {
            return back()->with(
                'error',
                'Tasks wale project ko delete nahi kar sakte. Project ko Cancelled mark karein.'
            );
        }

        DB::transaction(
            function () use ($project) {
                $project
                    ->services()
                    ->delete();

                $project->delete();
            }
        );

        return redirect()
            ->route('project.index')
            ->with(
                'success',
                'Project deleted successfully.'
            );
    }

    private function validationRules(
        ?Project $project = null
    ): array {
        return [
            'client_id' => [
                'required',
                'integer',
                'exists:clients,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'project_manager_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'users',
                    'id'
                )->where(
                        'is_active',
                        true
                    ),
            ],

            'priority' => [
                'required',

                Rule::in(
                    array_keys(
                        Project::priorities()
                    )
                ),
            ],

            'status' => [
                'required',

                Rule::in(
                    array_keys(
                        Project::statuses()
                    )
                ),
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'budget' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    private function activeUsers(
        ?int $includeUserId = null
    ) {
        return User::query()
            ->where(
                function ($query) use ($includeUserId) {
                    $query->where(
                        'is_active',
                        true
                    );

                    if ($includeUserId) {
                        $query->orWhere(
                            'id',
                            $includeUserId
                        );
                    }
                }
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
                'is_active',
            ]);
    }

    private function accessibleClients(
        User $user
    ) {
        $query = Client::query()
            ->orderBy('name');

        if (
            !$user->isSuperAdmin()
            && !$user->hasPermission(
                'clients.view_all'
            )
        ) {
            $query->where(
                'assigned_to',
                $user->id
            );
        }

        return $query->get([
            'id',
            'name',
            'company',
            'status',
            'assigned_to',
        ]);
    }
}