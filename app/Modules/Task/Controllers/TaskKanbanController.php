<?php

namespace App\Modules\Task\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskKanbanPreference;
use App\Modules\Task\Support\TaskKanbanService;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskKanbanController extends Controller
{
    public function __construct(
        private readonly TaskKanbanService $kanbanService
    ) {
    }

    public function index(
        Request $request
    ) {
        $user =
            $request->user();

        $preference =
            TaskKanbanPreference::forUser(
                $user
            );

        $groupBy =
            $request->query(
                'group_by',
                $preference->group_by
            );

        if (
            !in_array(
                $groupBy,
                [
                    'status',
                    'priority',
                ],
                true
            )
        ) {
            $groupBy = 'status';
        }

        $filters =
            $this->filters(
                $request
            );

        $board =
            $this->kanbanService
                ->buildBoard(
                    $user,
                    $groupBy,
                    $filters,
                    $preference
                );

        $canViewAll =
            $user->hasPermission(
                'tasks.view_all'
            );

        $users =
            $canViewAll
                ? User::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                        'email',
                    ])
                : collect();

        return view(
            'task::kanban.index',
            array_merge(
                $board,
                [
                    'preference' =>
                        $preference,

                    'filters' =>
                        $filters,

                    'statuses' =>
                        Task::statuses(),

                    'priorities' =>
                        Task::priorities(),

                    'users' =>
                        $users,

                    'canViewAll' =>
                        $canViewAll,

                    'pageTitle' =>
                        'Task Kanban Board',
                ]
            )
        );
    }

    public function board(
        Request $request
    ): JsonResponse {
        $request->validate([
            'group_by' => [
                'nullable',
                Rule::in([
                    'status',
                    'priority',
                ]),
            ],

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'string',
                'max:100',
            ],

            'priority' => [
                'nullable',
                'string',
                'max:100',
            ],

            'due' => [
                'nullable',
                Rule::in([
                    'today',
                    'overdue',
                    'upcoming',
                ]),
            ],
        ]);

        $user =
            $request->user();

        $preference =
            TaskKanbanPreference::forUser(
                $user
            );

        $groupBy =
            $request->query(
                'group_by',
                $preference->group_by
            );

        $board =
            $this->kanbanService
                ->buildBoard(
                    $user,
                    $groupBy,
                    $this->filters(
                        $request
                    ),
                    $preference
                );

        return response()->json([
            'html' =>
                view(
                    'task::kanban.partials.board',
                    $board
                )->render(),

            'total' =>
                $board['totalTasks'],

            'updated_at' =>
                now()->format(
                    'h:i:s A'
                ),
        ]);
    }

    public function details(
        Request $request,
        Task $task
    ): JsonResponse {
        $user =
            $request->user();

        $task->load([
            'project.client',
            'projectService',
            'assignedUser',
            'creator',
            'reviewer',
            'statusDefinition',
            'priorityDefinition',
        ]);

        $this->ensureCanAccessTask(
            $user,
            $task
        );

        return response()->json([
            'html' =>
                view(
                    'task::kanban.partials.drawer',
                    [
                        'task' =>
                            $task,
                    ]
                )->render(),
        ]);
    }

    public function move(
        Request $request,
        Task $task
    ): JsonResponse {
        $validated =
            $request->validate([
                'group_by' => [
                    'required',
                    Rule::in([
                        'status',
                        'priority',
                    ]),
                ],

                'target_column' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'before_id' => [
                    'nullable',
                    'integer',
                ],

                'after_id' => [
                    'nullable',
                    'integer',
                ],
            ]);

        $updated =
            $this->kanbanService
                ->moveTask(
                    $request->user(),
                    $task,
                    $validated
                );

        return response()->json([
            'message' =>
                'Task position updated successfully.',

            'task' =>
                $updated,
        ]);
    }

    public function saveColumnOrder(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'group_by' => [
                    'required',
                    Rule::in([
                        'status',
                        'priority',
                    ]),
                ],

                'columns' => [
                    'required',
                    'array',
                ],

                'columns.*' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ]);

        $order =
            $this->kanbanService
                ->saveColumnOrder(
                    $request->user(),
                    $validated['group_by'],
                    $validated['columns']
                );

        return response()->json([
            'message' =>
                'Column order saved successfully.',

            'columns' =>
                $order,
        ]);
    }

    public function savePreference(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'group_by' => [
                    'sometimes',
                    Rule::in([
                        'status',
                        'priority',
                    ]),
                ],

                'hide_empty_columns' => [
                    'sometimes',
                    'boolean',
                ],
            ]);

        $preference =
            $this->kanbanService
                ->savePreference(
                    $request->user(),
                    $validated
                );

        return response()->json([
            'message' =>
                'Kanban preference saved.',

            'preference' => [
                'group_by' =>
                    $preference->group_by,

                'hide_empty_columns' =>
                    $preference->hide_empty_columns,
            ],
        ]);
    }

    private function filters(
        Request $request
    ): array {
        return [
            'search' =>
                trim(
                    (string)
                    $request->query(
                        'search',
                        ''
                    )
                ),

            'status' =>
                trim(
                    (string)
                    $request->query(
                        'status',
                        ''
                    )
                ),

            'priority' =>
                trim(
                    (string)
                    $request->query(
                        'priority',
                        ''
                    )
                ),

            'due' =>
                trim(
                    (string)
                    $request->query(
                        'due',
                        ''
                    )
                ),
        ];
    }

    private function ensureCanAccessTask(
        User $user,
        Task $task
    ): void {
        if (
            $user->hasPermission(
                'tasks.view_all'
            )
        ) {
            return;
        }

        $allowed =
            (int) $task->assigned_to
            === (int) $user->id
            || (
                $task->project
                && (
                    (int)
                    $task->project
                        ->project_manager_id
                    === (int) $user->id
                    || $task->project
                        ->members()
                        ->where(
                            'users.id',
                            $user->id
                        )
                        ->exists()
                )
            );

        abort_unless(
            $allowed,
            403
        );
    }
}