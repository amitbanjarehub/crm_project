<?php

namespace App\Modules\Task\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Support\AuthorizesProjectAccess;
use App\Modules\Project\Support\ProjectActivityLogger;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskAttachmentController extends Controller
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
            'attachment' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip',
            ],
        ]);

        $file = $validated['attachment'];

        $storedName =
            Str::uuid() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs(
            "task-attachments/{$task->id}",
            $storedName,
            'public'
        );

        $attachment = $task->attachments()->create([
            'uploaded_by' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        ProjectActivityLogger::log(
            $task->project,
            'task_attachment_uploaded',
            "Attachment {$attachment->original_name} uploaded.",
            $attachment
        );

        return back()->with(
            'success',
            'Attachment uploaded successfully.'
        );
    }

    public function download(
        Request $request,
        Task $task,
        TaskAttachment $attachment
    ) {
        $task->load('project');

        $this->ensureCanAccessTask(
            $request->user(),
            $task
        );

        abort_unless(
            (int) $attachment->task_id === (int) $task->id,
            404
        );

        abort_unless(
            Storage::disk('public')
                ->exists($attachment->file_path),
            404,
            'Attachment file not found.'
        );

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    public function destroy(
        Request $request,
        Task $task,
        TaskAttachment $attachment
    ) {
        $task->load('project');

        $this->ensureCanAccessTask(
            $request->user(),
            $task
        );

        abort_unless(
            (int) $attachment->task_id === (int) $task->id,
            404
        );

        $canDelete =
            $request->user()->isSuperAdmin()
            || $task->project->isManager($request->user())
            || (int) $attachment->uploaded_by
                === (int) $request->user()->id;

        abort_unless($canDelete, 403);

        Storage::disk('public')
            ->delete($attachment->file_path);

        $attachment->delete();

        return back()->with(
            'success',
            'Attachment deleted successfully.'
        );
    }
}