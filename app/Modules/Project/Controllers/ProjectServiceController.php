<?php

namespace App\Modules\Project\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\ProjectService;
use App\Modules\Project\Support\AuthorizesProjectAccess;
use App\Modules\Project\Support\ProjectActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectServiceController extends Controller
{
    use AuthorizesProjectAccess;

    public function store(
        Request $request,
        Project $project
    ) {
        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        abort_if(
            $project->isClosed(),
            422,
            'Closed project me service add nahi kar sakte.'
        );

        $validated = $request->validate(
            $this->validationRules($project)
        );

        $this->validateAssignedMember(
            $project,
            $validated['assigned_to'] ?? null
        );

        $service = $project->services()->create([
            'name' => $validated['name'],
            'description' =>
                $validated['description'] ?? null,
            'assigned_to' =>
                $validated['assigned_to'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'start_date' =>
                $validated['start_date'] ?? null,
            'due_date' =>
                $validated['due_date'] ?? null,
            'sort_order' =>
                $validated['sort_order'] ?? 0,
            'created_by' => $request->user()->id,
        ]);

        ProjectActivityLogger::log(
            $project,
            'service_created',
            "Service {$service->name} created.",
            $service
        );

        return back()->with(
            'success',
            'Project service added successfully.'
        );
    }

    public function edit(
        Request $request,
        Project $project,
        ProjectService $projectService
    ) {
        $this->ensureSameProject(
            $project,
            $projectService
        );

        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        abort_if(
            $project->isClosed(),
            422,
            'Completed or cancelled project service cannot be edited.'
        );

        $project->load('members:id,name,email,is_active');

        return view('project::services.edit', [
            'project' => $project,
            'projectService' => $projectService,
            'statuses' => ProjectService::statuses(),
            'priorities' => Project::priorities(),
            'pageTitle' => 'Edit Project Service',
        ]);
    }

    public function update(
        Request $request,
        Project $project,
        ProjectService $projectService
    ) {
        $this->ensureSameProject(
            $project,
            $projectService
        );

        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        abort_if(
            $project->isClosed(),
            422,
            'Completed or cancelled project service cannot be updated.'
        );

        $validated = $request->validate(
            $this->validationRules($project)
        );

        $this->validateAssignedMember(
            $project,
            $validated['assigned_to'] ?? null
        );

        $oldValues = $projectService->toArray();

        $projectService->update([
            'name' => $validated['name'],
            'description' =>
                $validated['description'] ?? null,
            'assigned_to' =>
                $validated['assigned_to'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'start_date' =>
                $validated['start_date'] ?? null,
            'due_date' =>
                $validated['due_date'] ?? null,
            'sort_order' =>
                $validated['sort_order'] ?? 0,
        ]);

        ProjectActivityLogger::log(
            $project,
            'service_updated',
            "Service {$projectService->name} updated.",
            $projectService,
            $oldValues,
            $projectService->toArray()
        );

        return redirect()
            ->route('project.show', $project->id)
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(
        Request $request,
        Project $project,
        ProjectService $projectService
    ) {
        $this->ensureSameProject(
            $project,
            $projectService
        );

        $this->ensureCanAccessProject(
            $request->user(),
            $project
        );

        abort_if(
            $project->isClosed(),
            422,
            'Completed or cancelled project service cannot be deleted.'
        );

        if ($projectService->tasks()->exists()) {
            return back()->with(
                'error',
                'Tasks wali service ko delete nahi kar sakte.'
            );
        }

        ProjectActivityLogger::log(
            $project,
            'service_deleted',
            "Service {$projectService->name} deleted.",
            $projectService
        );

        $projectService->delete();

        return back()->with(
            'success',
            'Service deleted successfully.'
        );
    }

    // private function validationRules(
    //     Project $project
    // ): array {
    //     return [
    //         'name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //         ],
    //         'description' => [
    //             'nullable',
    //             'string',
    //             'max:5000',
    //         ],
    //         'assigned_to' => [
    //             'nullable',
    //             'integer',
    //             'exists:users,id',
    //         ],
    //         'priority' => [
    //             'required',
    //             Rule::in(
    //                 array_keys(Project::priorities())
    //             ),
    //         ],
    //         'status' => [
    //             'required',
    //             Rule::in(
    //                 array_keys(ProjectService::statuses())
    //             ),
    //         ],
    //         'start_date' => [
    //             'nullable',
    //             'date',
    //             $project->start_date
    //                 ? 'after_or_equal:' .
    //                     $project->start_date->format('Y-m-d')
    //                 : null,
    //         ],
    //         'due_date' => [
    //             'nullable',
    //             'date',
    //             'after_or_equal:start_date',
    //             $project->due_date
    //                 ? 'before_or_equal:' .
    //                     $project->due_date->format('Y-m-d')
    //                 : null,
    //         ],
    //         'sort_order' => [
    //             'nullable',
    //             'integer',
    //             'min:0',
    //         ],
    //     ];
    // }

    private function validationRules(
        Project $project
    ): array {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'priority' => [
                'required',
                Rule::in(
                    array_keys(Project::priorities())
                ),
            ],

            'status' => [
                'required',
                Rule::in(
                    array_keys(ProjectService::statuses())
                ),
            ],

            'start_date' => [
                'nullable',
                'date',

                /*
                 * Project start date available hai to
                 * service usse pehle start nahi ho sakti.
                 */
                $project->start_date
                ? 'after_or_equal:' .
                $project->start_date->format('Y-m-d')
                : null,
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',

                /*
                 * Project due date available hai to
                 * service uske baad end nahi ho sakti.
                 */
                $project->due_date
                ? 'before_or_equal:' .
                $project->due_date->format('Y-m-d')
                : null,
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];

        /*
         * Conditional rules se aane wali null values
         * ko har field ke rules array se remove karega.
         */
        return array_map(
            fn(array $fieldRules) => array_values(
                array_filter(
                    $fieldRules,
                    fn($rule) => $rule !== null
                )
            ),
            $rules
        );
    }

    private function validateAssignedMember(
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
            'Service sirf Project Manager ya Project Member ko assign ho sakti hai.'
        );
    }

    private function ensureSameProject(
        Project $project,
        ProjectService $service
    ): void {
        abort_unless(
            (int) $service->project_id
            === (int) $project->id,
            404
        );
    }
}