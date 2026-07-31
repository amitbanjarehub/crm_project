<?php

namespace App\Modules\Setting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskPriority;
use App\Modules\Task\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaskOptionController extends Controller
{
    public function index()
    {
        return view(
            'setting::task-options',
            [
                'taskStatuses' =>
                    TaskStatus::query()
                        ->withCount('tasks')
                        ->ordered()
                        ->get(),

                'taskPriorities' =>
                    TaskPriority::query()
                        ->withCount('tasks')
                        ->ordered()
                        ->get(),

                'pageTitle' =>
                    'Task Status and Priority Settings',
            ]
        );
    }

    public function storeStatus(
        Request $request
    ) {
        $validated = $request->validate(
            $this->statusRules()
        );

        $makeDefault =
            $request->boolean('is_default')
            || !TaskStatus::query()
                ->where('is_default', true)
                ->exists();

        $isClosed =
            $request->boolean('is_closed');

        $isManual =
            $request->boolean(
                'is_manual_selectable'
            );

        if (
            $makeDefault
            && $isClosed
        ) {
            return back()
                ->withErrors([
                    'is_default' =>
                        'Closed Task status default nahi ho sakta.',
                ])
                ->withInput();
        }

        if (
            $makeDefault
            && !$isManual
        ) {
            return back()
                ->withErrors([
                    'is_manual_selectable' =>
                        'Default status manually selectable hona chahiye.',
                ])
                ->withInput();
        }

        DB::transaction(
            function () use (
                $validated,
                $request,
                $makeDefault,
                $isClosed,
                $isManual
            ) {
                if ($makeDefault) {
                    TaskStatus::query()
                        ->update([
                            'is_default' => false,
                        ]);
                }

                TaskStatus::create([
                    'name' =>
                        trim($validated['name']),

                    'slug' =>
                        strtolower(
                            trim($validated['slug'])
                        ),

                    'system_key' =>
                        null,

                    'color' =>
                        strtoupper(
                            $validated['color']
                        ),

                    'is_default' =>
                        $makeDefault,

                    'is_active' =>
                        $makeDefault
                        || $request->boolean(
                            'is_active'
                        ),

                    'is_closed' =>
                        $isClosed,

                    'is_manual_selectable' =>
                        $isManual,

                    'is_system' =>
                        false,

                    'sort_order' =>
                        $validated['sort_order'],
                ]);
            }
        );

        return redirect()
            ->route(
                'setting.task-options.index'
            )
            ->with(
                'success',
                'Task status added successfully.'
            );
    }

    public function updateStatus(
        Request $request,
        TaskStatus $taskStatus
    ) {
        $validated = $request->validate(
            $this->statusRules(
                $taskStatus
            )
        );

        $newSlug = $taskStatus->is_system
            ? $taskStatus->slug
            : strtolower(
                trim($validated['slug'])
            );

        $makeDefault =
            $taskStatus->is_default
            || $request->boolean(
                'is_default'
            );

        /*
         * Core system behaviour locked rahega.
         */
        $isActive = $taskStatus->is_system
            ? true
            : (
                $makeDefault
                || $request->boolean(
                    'is_active'
                )
            );

        $isClosed = $taskStatus->is_system
            ? $taskStatus->is_closed
            : $request->boolean(
                'is_closed'
            );

        $isManual = $taskStatus->is_system
            ? $taskStatus
                ->is_manual_selectable
            : $request->boolean(
                'is_manual_selectable'
            );

        if (
            $makeDefault
            && $isClosed
        ) {
            return back()->withErrors([
                'is_default' =>
                    'Closed Task status default nahi ho sakta.',
            ]);
        }

        if (
            $makeDefault
            && !$isManual
        ) {
            return back()->withErrors([
                'is_manual_selectable' =>
                    'Default status manually selectable hona chahiye.',
            ]);
        }

        DB::transaction(
            function () use (
                $validated,
                $taskStatus,
                $newSlug,
                $makeDefault,
                $isActive,
                $isClosed,
                $isManual
            ) {
                if ($makeDefault) {
                    TaskStatus::query()
                        ->where(
                            'id',
                            '!=',
                            $taskStatus->id
                        )
                        ->update([
                            'is_default' => false,
                        ]);
                }

                /*
                 * Custom status slug change hone par
                 * existing Tasks bhi update hongi.
                 */
                if (
                    !$taskStatus->is_system
                    && $newSlug
                        !== $taskStatus->slug
                ) {
                    Task::query()
                        ->where(
                            'status',
                            $taskStatus->slug
                        )
                        ->update([
                            'status' => $newSlug,
                        ]);
                }

                $taskStatus->update([
                    'name' =>
                        trim($validated['name']),

                    'slug' =>
                        $newSlug,

                    'color' =>
                        strtoupper(
                            $validated['color']
                        ),

                    'is_default' =>
                        $makeDefault,

                    'is_active' =>
                        $isActive,

                    'is_closed' =>
                        $isClosed,

                    'is_manual_selectable' =>
                        $isManual,

                    'sort_order' =>
                        $validated['sort_order'],
                ]);
            }
        );

        return redirect()
            ->route(
                'setting.task-options.index'
            )
            ->with(
                'success',
                'Task status updated successfully.'
            );
    }

    public function destroyStatus(
        TaskStatus $taskStatus
    ) {
        if ($taskStatus->is_system) {
            return back()->with(
                'error',
                'Core system Task status delete nahi kiya ja sakta.'
            );
        }

        if ($taskStatus->is_default) {
            return back()->with(
                'error',
                'Default status delete karne se pehle kisi doosre status ko default banayein.'
            );
        }

        if ($taskStatus->tasks()->exists()) {
            return back()->with(
                'error',
                'Ye status existing Tasks me use ho raha hai. Pehle Tasks ka status change karein.'
            );
        }

        $taskStatus->delete();

        return redirect()
            ->route(
                'setting.task-options.index'
            )
            ->with(
                'success',
                'Task status deleted successfully.'
            );
    }

    public function storePriority(
        Request $request
    ) {
        $validated = $request->validate(
            $this->priorityRules()
        );

        $makeDefault =
            $request->boolean(
                'is_default'
            )
            || !TaskPriority::query()
                ->where('is_default', true)
                ->exists();

        DB::transaction(
            function () use (
                $validated,
                $request,
                $makeDefault
            ) {
                if ($makeDefault) {
                    TaskPriority::query()
                        ->update([
                            'is_default' => false,
                        ]);
                }

                TaskPriority::create([
                    'name' =>
                        trim($validated['name']),

                    'slug' =>
                        strtolower(
                            trim($validated['slug'])
                        ),

                    'color' =>
                        strtoupper(
                            $validated['color']
                        ),

                    'is_default' =>
                        $makeDefault,

                    'is_active' =>
                        $makeDefault
                        || $request->boolean(
                            'is_active'
                        ),

                    'is_system' =>
                        false,

                    'sort_order' =>
                        $validated['sort_order'],
                ]);
            }
        );

        return redirect()
            ->route(
                'setting.task-options.index'
            )
            ->with(
                'success',
                'Task priority added successfully.'
            );
    }

    public function updatePriority(
        Request $request,
        TaskPriority $taskPriority
    ) {
        $validated = $request->validate(
            $this->priorityRules(
                $taskPriority
            )
        );

        $newSlug = $taskPriority->is_system
            ? $taskPriority->slug
            : strtolower(
                trim($validated['slug'])
            );

        $makeDefault =
            $taskPriority->is_default
            || $request->boolean(
                'is_default'
            );

        $isActive =
            $taskPriority->is_system
            || $makeDefault
            || $request->boolean(
                'is_active'
            );

        DB::transaction(
            function () use (
                $validated,
                $taskPriority,
                $newSlug,
                $makeDefault,
                $isActive
            ) {
                if ($makeDefault) {
                    TaskPriority::query()
                        ->where(
                            'id',
                            '!=',
                            $taskPriority->id
                        )
                        ->update([
                            'is_default' => false,
                        ]);
                }

                if (
                    !$taskPriority->is_system
                    && $newSlug
                        !== $taskPriority->slug
                ) {
                    Task::query()
                        ->where(
                            'priority',
                            $taskPriority->slug
                        )
                        ->update([
                            'priority' => $newSlug,
                        ]);
                }

                $taskPriority->update([
                    'name' =>
                        trim($validated['name']),

                    'slug' =>
                        $newSlug,

                    'color' =>
                        strtoupper(
                            $validated['color']
                        ),

                    'is_default' =>
                        $makeDefault,

                    'is_active' =>
                        $isActive,

                    'sort_order' =>
                        $validated['sort_order'],
                ]);
            }
        );

        return redirect()
            ->route(
                'setting.task-options.index'
            )
            ->with(
                'success',
                'Task priority updated successfully.'
            );
    }

    public function destroyPriority(
        TaskPriority $taskPriority
    ) {
        if ($taskPriority->is_system) {
            return back()->with(
                'error',
                'Core system Task priority delete nahi ki ja sakti.'
            );
        }

        if ($taskPriority->is_default) {
            return back()->with(
                'error',
                'Default priority delete karne se pehle kisi doosri priority ko default banayein.'
            );
        }

        if ($taskPriority->tasks()->exists()) {
            return back()->with(
                'error',
                'Ye priority existing Tasks me use ho rahi hai. Pehle Tasks ki priority change karein.'
            );
        }

        $taskPriority->delete();

        return redirect()
            ->route(
                'setting.task-options.index'
            )
            ->with(
                'success',
                'Task priority deleted successfully.'
            );
    }

    private function statusRules(
        ?TaskStatus $taskStatus = null
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/',

                Rule::unique(
                    'task_statuses',
                    'slug'
                )->ignore(
                    $taskStatus?->id
                ),
            ],

            'color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'is_closed' => [
                'nullable',
                'boolean',
            ],

            'is_manual_selectable' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    private function priorityRules(
        ?TaskPriority $taskPriority = null
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/',

                Rule::unique(
                    'task_priorities',
                    'slug'
                )->ignore(
                    $taskPriority?->id
                ),
            ],

            'color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}