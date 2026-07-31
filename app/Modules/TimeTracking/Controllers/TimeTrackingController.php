<?php

namespace App\Modules\TimeTracking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Models\Task;
use App\Modules\TimeTracking\Models\TimeEntry;
use App\Modules\TimeTracking\Support\TimeTrackingManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeTrackingController extends Controller
{
    public function index(
        Request $request,
        TimeTrackingManager $manager
    ) {
        $user = $request->user();

        $entries = TimeEntry::query()
            ->where('user_id', $user->id)
            ->with([
                'task:id,title,status',
                'project:id,project_code,name',
                'projectService:id,name',
                'breaks',
            ])
            ->latest('started_at')
            ->paginate(20)
            ->withQueryString();

        $allEntries = TimeEntry::query()
            ->where('user_id', $user->id)
            ->get();

        $totalSeconds = (int)
            $allEntries->sum(
                fn(TimeEntry $entry) =>
                    $entry->liveSeconds()
            );

        $todayEntries = TimeEntry::query()
            ->where('user_id', $user->id)
            ->whereDate(
                'started_at',
                today()
            )
            ->get();

        $todaySeconds = (int)
            $todayEntries->sum(
                fn(TimeEntry $entry) =>
                    $entry->liveSeconds()
            );

        return view('timetracking::index', [
            'entries' => $entries,

            'activeEntry' =>
                $manager->current($user),

            'totalSeconds' =>
                $totalSeconds,

            'todaySeconds' =>
                $todaySeconds,

            'pageTitle' =>
                'My Time Tracking',
        ]);
    }

    public function current(
        Request $request,
        TimeTrackingManager $manager
    ): JsonResponse {
        $entry = $manager->current(
            $request->user()
        );

        return $this->stateResponse(
            $entry
        );
    }

    public function start(
        Request $request,
        Task $task,
        TimeTrackingManager $manager
    ): JsonResponse {
        $validated = $request->validate([
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $entry = $manager->start(
            $request->user(),
            $task,
            $validated['notes'] ?? null
        );

        return $this->stateResponse(
            $entry,
            'Work timer started successfully.'
        );
    }

    public function pause(
        Request $request,
        TimeTrackingManager $manager
    ): JsonResponse {
        $entry = $manager->pause(
            $request->user()
        );

        return $this->stateResponse(
            $entry,
            'Work timer paused.'
        );
    }

    public function resume(
        Request $request,
        TimeTrackingManager $manager
    ): JsonResponse {
        $entry = $manager->resume(
            $request->user()
        );

        return $this->stateResponse(
            $entry,
            'Work timer resumed.'
        );
    }

    public function stop(
        Request $request,
        TimeTrackingManager $manager
    ): JsonResponse {
        $validated = $request->validate([
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $entry = $manager->stop(
            $request->user(),
            $request->user(),
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Work timer ended successfully.',

            'active' => false,
            'entry' => null,

            'last_entry' =>
                $this->formatEntry($entry),
        ]);
    }

    private function stateResponse(
        ?TimeEntry $entry,
        string $message = ''
    ): JsonResponse {
        return response()
            ->json([
                'success' => true,

                'message' => $message,

                'active' =>
                    (bool) $entry,

                'entry' =>
                    $entry
                        ? $this->formatEntry($entry)
                        : null,
            ])
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0'
            );
    }

    private function formatEntry(
        TimeEntry $entry
    ): array {
        $entry->loadMissing([
            'task:id,title,status,project_id,project_service_id',
            'project:id,project_code,name',
            'projectService:id,name',
        ]);

        $taskTotalSeconds = $entry->task_id
            ? (int) TimeEntry::query()
                ->where(
                    'task_id',
                    $entry->task_id
                )
                ->sum('total_seconds')
            : $entry->liveSeconds();

        return [
            'id' => $entry->id,

            'status' => $entry->status,

            'seconds' =>
                $entry->liveSeconds(),

            'stored_seconds' =>
                (int) $entry->total_seconds,

            'task_total_seconds' =>
                $taskTotalSeconds,

            'started_at' =>
                $entry->started_at
                    ?->toIso8601String(),

            'task' => [
                'id' =>
                    $entry->task?->id,

                'title' =>
                    $entry->task?->title
                    ?? 'Deleted Task',

                'url' =>
                    $entry->task
                        ? route(
                            'task.show',
                            $entry->task->id,
                            false
                        )
                        : null,
            ],

            'project' => [
                'id' =>
                    $entry->project?->id,

                'code' =>
                    $entry->project?->project_code,

                'name' =>
                    $entry->project?->name
                    ?? 'Deleted Project',
            ],
        ];
    }
}