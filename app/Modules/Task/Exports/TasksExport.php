<?php

namespace App\Modules\Task\Exports;

use App\Modules\Task\Models\Task;
use App\Modules\TimeTracking\Models\TimeEntry;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class TasksExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle
{
    use Exportable;

    public function __construct(
        private Builder $taskQuery
    ) {
    }

    public function query(): Builder
    {
        return $this->taskQuery
            ->with([
                'project:id,project_code,name,project_manager_id',

                'projectService:id,name',

                'assignedUser:id,name,email',

                'reviewer:id,name,email',

                'creator:id,name,email',

                'parentTask:id,title',

                'prerequisiteTasks:id,title,status',

                'activeTimeEntries:id,task_id,user_id,status',
            ])
            ->withCount([
                'comments',
                'attachments',
                'prerequisiteTasks',
                'subtasks',
            ])
            ->withSum(
                'timeEntries as tracked_seconds',
                'total_seconds'
            )
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Task ID',
            'Task Title',
            'Project Code',
            'Project Name',
            'Project Service',
            'Parent Task',
            'Assigned Employee',
            'Assigned Employee Email',
            'Reviewer',
            'Reviewer Email',
            'Priority',
            'Status',
            'Progress Percentage',
            'Requires Review',
            'Start Date',
            'Due Date',
            'Estimated Hours',
            'Tracked Time',
            'Active Timer',
            'Dependencies',
            'Dependency Count',
            'Subtask Count',
            'Comment Count',
            'Attachment Count',
            'Submitted For Review',
            'Reviewed Date',
            'Review Note',
            'Completed Date',
            'Created By',
            'Created Date',
            'Description',
        ];
    }

    public function map($task): array
    {
        $dependencies = $task
            ->prerequisiteTasks
            ->map(
                function ($dependency) {
                    $status = Task::statuses()[
                        $dependency->status
                    ] ?? ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $dependency->status
                        )
                    );

                    return '#'
                        . $dependency->id
                        . ' '
                        . $dependency->title
                        . ' ('
                        . $status
                        . ')';
                }
            )
            ->implode(' | ');

        return [
            $task->id,

            $task->title,

            $task->project?->project_code ?? '',

            $task->project?->name ?? '',

            $task->projectService?->name ?? '',

            $task->parentTask
                ? '#'
                    . $task->parentTask->id
                    . ' '
                    . $task->parentTask->title
                : '',

            $task->assignedUser?->name
                ?? 'Unassigned',

            $task->assignedUser?->email
                ?? '',

            $task->reviewer?->name
                ?? '',

            $task->reviewer?->email
                ?? '',

            Task::priorities()[
                $task->priority
            ] ?? ucfirst($task->priority),

            Task::statuses()[
                $task->status
            ] ?? ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $task->status
                )
            ),

            (int) $task->progress_percent,

            $task->requires_review
                ? 'Yes'
                : 'No',

            $task->start_date
                ?->format('Y-m-d')
                ?? '',

            $task->due_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $task->estimated_hours ?? '',

            TimeEntry::formatSeconds(
                (int) (
                    $task->tracked_seconds
                    ?? 0
                )
            ),

            $task->activeTimeEntries->isNotEmpty()
                ? 'Yes'
                : 'No',

            $dependencies,

            (int) $task
                ->prerequisite_tasks_count,

            (int) $task->subtasks_count,

            (int) $task->comments_count,

            (int) $task->attachments_count,

            $task->submitted_for_review_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $task->reviewed_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $task->review_note ?? '',

            $task->completed_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $task->creator?->name
                ?? 'Unknown User',

            $task->created_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $task->description ?? '',
        ];
    }

    public function title(): string
    {
        return 'Tasks';
    }
}