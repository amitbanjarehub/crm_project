<?php

namespace App\Modules\Task\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Support\AuthorizesProjectAccess;
use App\Modules\Project\Support\ProjectActivityLogger;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    use AuthorizesProjectAccess;

    public function store(
        Request $request,
        Task $task
    ) {
        $task->load('project');

        $this->ensureCanAccessTask(
            $request->user(),
            $task
        );

        $validated = $request->validate([
            'comment' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        ProjectActivityLogger::log(
            $task->project,
            'task_comment_added',
            "Comment added to task {$task->title}.",
            $comment
        );

        return back()->with(
            'success',
            'Comment added successfully.'
        );
    }

    public function destroy(
        Request $request,
        Task $task,
        TaskComment $comment
    ) {
        $task->load('project');

        $this->ensureCanAccessTask(
            $request->user(),
            $task
        );

        abort_unless(
            (int) $comment->task_id === (int) $task->id,
            404
        );

        $canDelete =
            $request->user()->isSuperAdmin()
            || $task->project->isManager($request->user())
            || (int) $comment->user_id
                === (int) $request->user()->id;

        abort_unless($canDelete, 403);

        $comment->delete();

        return back()->with(
            'success',
            'Comment deleted successfully.'
        );
    }
}