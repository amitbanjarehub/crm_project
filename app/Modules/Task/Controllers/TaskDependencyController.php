<?php

namespace App\Modules\Task\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Support\AuthorizesProjectAccess;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Support\TaskDependencyManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskDependencyController extends Controller
{
    use AuthorizesProjectAccess;

    public function store(
        Request $request,
        Task $task,
        TaskDependencyManager $dependencyManager
    ) {
        $task->load('project');

        $this->ensureCanManageTaskDependencies(
            $request->user(),
            $task
        );

        $validated = $request->validate([
            'depends_on_task_id' => [
                'required',
                'integer',
                'exists:tasks,id',
            ],
        ]);

        $prerequisite = Task::query()
            ->with('project')
            ->findOrFail(
                $validated['depends_on_task_id']
            );

        DB::transaction(function () use (
            $request,
            $task,
            $prerequisite,
            $dependencyManager
        ) {
            $dependencyManager->add(
                $task,
                $prerequisite,
                $request->user()
            );
        });

        return back()->with(
            'success',
            'Task dependency added successfully.'
        );
    }

    public function destroy(
        Request $request,
        Task $task,
        Task $prerequisite,
        TaskDependencyManager $dependencyManager
    ) {
        $task->load('project');

        $this->ensureCanManageTaskDependencies(
            $request->user(),
            $task
        );

        DB::transaction(function () use (
            $task,
            $prerequisite,
            $dependencyManager
        ) {
            $dependencyManager->remove(
                $task,
                $prerequisite
            );
        });

        return back()->with(
            'success',
            'Task dependency removed successfully.'
        );
    }
}