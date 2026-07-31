<?php

namespace App\Modules\Task\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Support\AuthorizesProjectAccess;
use App\Modules\Task\Exports\TaskImportTemplateExport;
use App\Modules\Task\Exports\TasksExport;
use App\Modules\Task\Imports\TasksImport;
use App\Modules\Task\Imports\TaskWorkbookImport;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class TaskImportExportController extends Controller
{
    use AuthorizesProjectAccess;

    public function importForm(
        Request $request
    ) {
        $defaultStatus =
            Task::defaultStatus();

        $defaultPriority =
            Task::defaultPriority();

        return view(
            'task::import',
            [
                'canAssign' =>
                    $this->canAssignTasks(
                        $request->user()
                    ),

                'priorities' =>
                    Task::activePriorities(),

                'defaultStatus' =>
                    $defaultStatus,

                'defaultStatusLabel' =>
                    Task::statuses()[
                        $defaultStatus
                    ] ?? $defaultStatus,

                'defaultPriority' =>
                    $defaultPriority,

                'pageTitle' =>
                    'Import Tasks',
            ]
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new TaskImportTemplateExport(),

            'task-import-template.xlsx'
        );
    }

    public function export(
        Request $request
    ) {
        $user = $request->user();

        $scope = trim(
            (string) $request->query(
                'scope',
                'all'
            )
        );

        if (
            !in_array(
                $scope,
                [
                    'all',
                    'my',
                ],
                true
            )
        ) {
            $scope = 'all';
        }

        $onlyMyTasks =
            $scope === 'my';

        $query = $this->accessibleTaskQuery(
            $user,
            $onlyMyTasks
        );

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $status = trim(
            (string) $request->query(
                'status',
                ''
            )
        );

        $priority = trim(
            (string) $request->query(
                'priority',
                ''
            )
        );

        $due = trim(
            (string) $request->query(
                'due',
                ''
            )
        );

        $this->applyExportFilters(
            $query,
            $search,
            $status,
            $priority,
            $due
        );

        $prefix = $onlyMyTasks
            ? 'my-tasks'
            : 'tasks';

        $fileName =
            $prefix
            . '-'
            . now()->format(
                'Y-m-d_H-i-s'
            )
            . '.xlsx';

        return Excel::download(
            new TasksExport($query),
            $fileName
        );
    }

    public function import(
        Request $request
    ) {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],

            'duplicate_mode' => [
                'required',

                Rule::in([
                    'skip',
                    'update',
                ]),
            ],
        ]);

        $user = $request->user();

        $tasksImport = new TasksImport(
            $user,

            $this->canViewAllProjects(
                $user
            ),

            $this->canAssignTasks(
                $user
            ),

            $validated[
                'duplicate_mode'
            ]
        );

        $workbookImport =
            new TaskWorkbookImport(
                $tasksImport
            );

        try {
            Excel::import(
                $workbookImport,

                $request->file('file')
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'task.import.form'
                )
                ->with(
                    'error',

                    $exception->getMessage()
                    ?: 'The Task  Excel file could not be imported.'
                );
        }

        return redirect()
            ->route(
                'task.import.form'
            )
            ->with(
                'success',

                'Task import process complete.'
            )
            ->with(
                'import_result',

                $tasksImport->result()
            );
    }

    private function accessibleTaskQuery(
        User $user,
        bool $onlyMyTasks
    ): Builder {
        $query = Task::query();

        if ($onlyMyTasks) {
            return $query->where(
                'assigned_to',
                $user->id
            );
        }

        if ($this->canViewAllTasks($user)) {
            return $query;
        }

        return $query->where(
            function (Builder $taskQuery) use ($user) {
                $taskQuery
                    ->where(
                        'assigned_to',
                        $user->id
                    )
                    ->orWhereHas(
                        'project',

                        function (Builder $projectQuery) use ($user) {
                            $projectQuery
                                ->where(
                                    'project_manager_id',
                                    $user->id
                                )
                                ->orWhereHas(
                                    'members',

                                    fn(
                                    Builder $memberQuery
                                ) =>
                                    $memberQuery
                                        ->where(
                                            'users.id',
                                            $user->id
                                        )
                                );
                        }
                    );
            }
        );
    }

    private function applyExportFilters(
        Builder $query,
        string $search,
        string $status,
        string $priority,
        string $due
    ): void {
        if ($search !== '') {
            $query->where(
                function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where(
                            'title',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'project',

                            fn(
                            Builder $projectQuery
                        ) =>
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
                }
            );
        }

        if (
            $status !== ''
            && array_key_exists(
                $status,
                Task::statuses()
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
                Task::priorities()
            )
        ) {
            $query->where(
                'priority',
                $priority
            );
        }

        if ($due === 'today') {
            $query->whereDate(
                'due_at',
                today()
            );
        } elseif ($due === 'overdue') {
            $query
                ->whereNotNull('due_at')
                ->where(
                    'due_at',
                    '<',
                    now()
                )
                ->whereNotIn(
                    'status',
                    Task::closedStatusSlugs()
                );
        } elseif ($due === 'upcoming') {
            $query->where(
                'due_at',
                '>',
                now()
            );
        }
    }

    private function canAssignTasks(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission(
                'tasks.assign'
            );
    }
}