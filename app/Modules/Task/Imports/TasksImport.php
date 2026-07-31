<?php

namespace App\Modules\Task\Imports;

use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\ProjectService;
use App\Modules\Project\Support\ProjectActivityLogger;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;
use Throwable;

class TasksImport extends StringValueBinder implements
    ToCollection,
    WithHeadingRow,
    SkipsEmptyRows,
    WithCustomValueBinder
{
    private int $totalRows = 0;

    private int $importedRows = 0;

    private int $updatedRows = 0;

    private int $skippedRows = 0;

    private int $failedRows = 0;

    private string $defaultStatus;

    private string $defaultPriority;

    private array $issues = [];

    public function __construct(
        private User $importedBy,
        private bool $canViewAllProjects,
        private bool $canAssign,
        private string $duplicateMode
    ) {
        $this->defaultStatus =
            Task::defaultStatus();

        $this->defaultPriority =
            Task::defaultPriority();
    }

    public function collection(
        Collection $rows
    ): void {
        if ($rows->count() > 2000) {
            throw new RuntimeException(
                'Only up to 2,000 task rows can be imported per Excel file.'
            );
        }

        /*
         * Required Excel headings check.
         */
        if ($rows->isNotEmpty()) {
            $firstRow = $this->rowToArray(
                $rows->first()
            );

            foreach (
                [
                    'project_code',
                    'project_service_name',
                    'title',
                ] as $requiredHeading
            ) {
                if (
                    !array_key_exists(
                        $requiredHeading,
                        $firstRow
                    )
                ) {
                    throw new RuntimeException(
                        "Required Excel heading missing: {$requiredHeading}"
                    );
                }
            }
        }

        foreach (
            $rows as $index => $excelRow
        ) {
            /*
             * Heading first row par hai.
             * Actual Excel row number index + 2 hoga.
             */
            $rowNumber = $index + 2;

            $rawRow = $this->rowToArray(
                $excelRow
            );

            if ($this->isEmptyRow($rawRow)) {
                continue;
            }

            $this->totalRows++;

            $row = $this->normalizeRow(
                $rawRow
            );

            $validator = Validator::make(
                $row,
                [
                    'project_code' => [
                        'required',
                        'string',
                        'max:30',
                    ],

                    'project_service_name' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'title' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'description' => [
                        'nullable',
                        'string',
                        'max:10000',
                    ],

                    'assigned_employee_email' => [
                        'nullable',
                        'email',
                        'max:255',
                    ],

                    'priority' => [
                        'required',

                        Rule::in(
                            array_keys(
                                Task::activePriorities()
                            )
                        ),
                    ],

                    'requires_review' => [
                        'required',
                        'boolean',
                    ],

                    'reviewer_email' => [
                        'nullable',
                        'email',
                        'max:255',
                    ],

                    'start_date' => [
                        'nullable',
                        'date',
                    ],

                    'due_at' => [
                        'nullable',
                        'date',
                    ],

                    'estimated_hours' => [
                        'nullable',
                        'numeric',
                        'min:0',
                    ],
                ],
                [
                    'priority.in' =>
                        'Invalid Task priority value.',

                    'requires_review.boolean' =>
                        'Only the first 100 rows can be displayed here.',
                ]
            );

            if ($validator->fails()) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $row,
                    $validator
                        ->errors()
                        ->all()
                );

                continue;
            }

            $data = $validator->validated();

            /*
             * Project code se Project resolve karo.
             */
            $project = Project::query()
                ->whereRaw(
                    'LOWER(project_code) = ?',
                    [
                        strtolower(
                            $data['project_code']
                        ),
                    ]
                )
                ->first();

            if (!$project) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'The CRM project code is not available.',
                    ]
                );

                continue;
            }

            if ($project->isClosed()) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'Tasks cannot be imported into completed or cancelled projects.',
                    ]
                );

                continue;
            }

            if (!$this->canAccessProject($project)) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'You don not have permission to access the selected project.',
                    ]
                );

                continue;
            }

            /*
             * Same Project ke andar exact Service name resolve karo.
             */
            $services = ProjectService::query()
                ->where(
                    'project_id',
                    $project->id
                )
                ->whereRaw(
                    'LOWER(name) = ?',
                    [
                        strtolower(
                            $data[
                                'project_service_name'
                            ]
                        ),
                    ]
                )
                ->get();

            if ($services->isEmpty()) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'The selected project service is not available for this project.',
                    ]
                );

                continue;
            }

            if ($services->count() > 1) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'Duplicate Project Service names detected. Please ensure each service name is unique.',
                    ]
                );

                continue;
            }

            $projectService = $services->first();

            /*
             * Duplicate match:
             * Project + Service + Task Title.
             */
            $duplicateTasks = Task::query()
                ->where(
                    'project_id',
                    $project->id
                )
                ->where(
                    'project_service_id',
                    $projectService->id
                )
                ->whereRaw(
                    'LOWER(title) = ?',
                    [
                        strtolower(
                            $data['title']
                        ),
                    ]
                )
                ->get();

            if ($duplicateTasks->count() > 1) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'Multiple tasks are available for the same project, service, and title.',
                    ]
                );

                continue;
            }

            $duplicate = $duplicateTasks->first();

            if (
                $duplicate
                && $this->duplicateMode === 'skip'
            ) {
                $this->skippedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Skipped',
                    $data,
                    [
                        'A task for the same Project, Service, and Title already exists.',
                    ]
                );

                continue;
            }

            if (
                $duplicate
                && (
                    $duplicate->isClosed()
                    || $duplicate->isInReview()
                )
            ) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'Completed, Cancelled, or In Review tasks cannot be updated via Excel.',
                    ]
                );

                continue;
            }

            /*
             * Assigned employee resolve karo.
             */
            $assignedEmployee = null;

            if (
                $this->canAssign
                && !empty(
                $data[
                    'assigned_employee_email'
                ]
            )
            ) {
                [
                    $assignedEmployee,
                    $assignedError,
                ] = $this->resolveProjectUser(
                            $project,
                            $data[
                                'assigned_employee_email'
                            ],
                            'Assigned employee'
                        );

                if ($assignedError) {
                    $this->failedRows++;

                    $this->addIssue(
                        $rowNumber,
                        'Failed',
                        $data,
                        [
                            $assignedError,
                        ]
                    );

                    continue;
                }
            }

            /*
             * Reviewer resolve karo.
             */
            $reviewer = null;

            if (
                $this->canAssign
                && !empty(
                $data['reviewer_email']
            )
            ) {
                [
                    $reviewer,
                    $reviewerError,
                ] = $this->resolveProjectUser(
                            $project,
                            $data['reviewer_email'],
                            'Reviewer'
                        );

                if ($reviewerError) {
                    $this->failedRows++;

                    $this->addIssue(
                        $rowNumber,
                        'Failed',
                        $data,
                        [
                            $reviewerError,
                        ]
                    );

                    continue;
                }
            }

            /*
             * Assignment handling.
             */
            if ($this->canAssign) {
                if ($assignedEmployee) {
                    $assignedTo =
                        $assignedEmployee->id;
                } elseif ($duplicate) {
                    $assignedTo =
                        $duplicate->assigned_to;
                } else {
                    $assignedTo = null;
                }
            } else {
                if ($duplicate) {
                    $assignedTo =
                        $duplicate->assigned_to;
                } elseif (
                    $this->isProjectUser(
                        $project,
                        $this->importedBy
                    )
                ) {
                    /*
                     * Normal importer ki new Task
                     * automatically usi ko assign hogi.
                     */
                    $assignedTo =
                        $this->importedBy->id;
                } else {
                    $assignedTo = null;
                }
            }

            if ($this->canAssign) {
                if ($reviewer) {
                    $reviewerId =
                        $reviewer->id;
                } elseif ($duplicate) {
                    $reviewerId =
                        $duplicate->reviewer_id;
                } else {
                    $reviewerId = null;
                }
            } else {
                $reviewerId = $duplicate
                    ? $duplicate->reviewer_id
                    : null;
            }

            $requiresReview = (bool) 
                $data['requires_review'];

            if (!$requiresReview) {
                $reviewerId = null;
            }

            $payload = [
                'title' =>
                    $data['title'],

                'description' =>
                    $data['description']
                    ?: null,

                'assigned_to' =>
                    $assignedTo,

                'priority' =>
                    $data['priority'],

                'requires_review' =>
                    $requiresReview,

                'reviewer_id' =>
                    $reviewerId,

                'start_date' =>
                    $data['start_date']
                    ?: null,

                'due_at' =>
                    $data['due_at']
                    ?: null,

                'estimated_hours' =>
                    $data['estimated_hours']
                    !== ''
                    ? $data['estimated_hours']
                    : null,
            ];

            try {
                DB::transaction(
                    function () use ($duplicate, $project, $projectService, $payload) {
                        if ($duplicate) {
                            $task = Task::query()
                                ->whereKey(
                                    $duplicate->id
                                )
                                ->lockForUpdate()
                                ->firstOrFail();

                            if (
                                $task->isClosed()
                                || $task->isInReview()
                            ) {
                                throw new RuntimeException(
                                    'Closed or In Review tasks cannot be updated.'
                                );
                            }

                            $oldAssignedTo =
                                $task->assigned_to
                                ? (int) 
                                $task->assigned_to
                                : null;

                            $newAssignedTo =
                                $payload['assigned_to']
                                ? (int) 
                                $payload[
                                    'assigned_to'
                                ]
                                : null;

                            if (
                                $oldAssignedTo
                                !== $newAssignedTo
                                && $task
                                    ->activeTimeEntries()
                                    ->exists()
                            ) {
                                throw new RuntimeException(
                                    'The timer for this task is still running, but the assignment has not been updated.'
                                );
                            }

                            $oldValues =
                                $task->toArray();

                            /*
                             * Status, progress, review history,
                             * completed_at, dependencies aur
                             * time data preserve hoga.
                             */
                            $task->update(
                                $payload
                            );

                            ProjectActivityLogger::log(
                                $project,
                                'task_excel_updated',
                                "Task {$task->title} updated through Excel import.",
                                $task,
                                $oldValues,
                                $task->toArray()
                            );

                            return;
                        }

                        $task = Task::create([
                            'project_id' =>
                                $project->id,

                            'project_service_id' =>
                                $projectService->id,

                            'parent_task_id' =>
                                null,

                            ...$payload,

                            /*
                             * Workflow state Excel se
                             * control nahi hogi.
                             */
                            'status' =>
                                $this->defaultStatus,

                            'progress_percent' => 0,

                            'submitted_for_review_at' =>
                                null,

                            'reviewed_at' => null,

                            'review_note' => null,

                            'completed_at' => null,

                            'created_by' =>
                                $this->importedBy->id,
                        ]);

                        ProjectActivityLogger::log(
                            $project,
                            'task_excel_imported',
                            "Task {$task->title} created through Excel import.",
                            $task
                        );
                    }
                );

                if ($duplicate) {
                    $this->updatedRows++;
                } else {
                    $this->importedRows++;
                }
            } catch (Throwable $exception) {
                if (
                    !$exception
                    instanceof RuntimeException
                ) {
                    report($exception);
                }

                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        $exception
                        instanceof RuntimeException
                        ? $exception->getMessage()
                        : 'The task import failed due to a database error.',
                    ]
                );
            }
        }
    }

    public function result(): array
    {
        return [
            'total' =>
                $this->totalRows,

            'imported' =>
                $this->importedRows,

            'updated' =>
                $this->updatedRows,

            'skipped' =>
                $this->skippedRows,

            'failed' =>
                $this->failedRows,

            'issues' =>
                $this->issues,
        ];
    }

    private function canAccessProject(
        Project $project
    ): bool {
        if ($this->canViewAllProjects) {
            return true;
        }

        if (
            $project->isManager(
                $this->importedBy
            )
        ) {
            return true;
        }

        return $project->hasMember(
            $this->importedBy
        );
    }

    private function resolveProjectUser(
        Project $project,
        string $email,
        string $label
    ): array {
        $user = User::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [
                    strtolower($email),
                ]
            )
            ->first();

        if (!$user) {
            return [
                null,

                "{$label} email CRM me available nahi hai.",
            ];
        }

        if (!$user->is_active) {
            return [
                null,

                "{$label} inactive hai.",
            ];
        }

        if (
            !$this->isProjectUser(
                $project,
                $user
            )
        ) {
            return [
                null,

                "{$label} Project Manager ya Project Member nahi hai.",
            ];
        }

        return [
            $user,
            null,
        ];
    }

    private function isProjectUser(
        Project $project,
        User $user
    ): bool {
        if (
            (int) $project
                ->project_manager_id
            === (int) $user->id
        ) {
            return true;
        }

        return $project
            ->members()
            ->where(
                'users.id',
                $user->id
            )
            ->exists();
    }

    private function normalizeRow(
        array $row
    ): array {
        return [
            'project_code' =>
                strtoupper(
                    trim(
                        (string) (
                            $row[
                                'project_code'
                            ] ?? ''
                        )
                    )
                ),

            'project_service_name' =>
                trim(
                    (string) (
                        $row[
                            'project_service_name'
                        ] ?? ''
                    )
                ),

            'title' =>
                trim(
                    (string) (
                        $row['title'] ?? ''
                    )
                ),

            'description' =>
                trim(
                    (string) (
                        $row[
                            'description'
                        ] ?? ''
                    )
                ),

            'assigned_employee_email' =>
                strtolower(
                    trim(
                        (string) (
                            $row[
                                'assigned_employee_email'
                            ] ?? ''
                        )
                    )
                ),

            $this->normalizeOption(
                $row['priority'] ?? '',
                $this->defaultPriority
            ),

            'requires_review' =>
                $this->normalizeBoolean(
                    $row[
                        'requires_review'
                    ] ?? 'yes'
                ),

            'reviewer_email' =>
                strtolower(
                    trim(
                        (string) (
                            $row[
                                'reviewer_email'
                            ] ?? ''
                        )
                    )
                ),

            'start_date' =>
                $this->normalizeDate(
                    $row['start_date'] ?? null,
                    false
                ),

            'due_at' =>
                $this->normalizeDate(
                    $row['due_at'] ?? null,
                    true
                ),

            'estimated_hours' =>
                trim(
                    (string) (
                        $row[
                            'estimated_hours'
                        ] ?? ''
                    )
                ),
        ];
    }

    private function normalizeOption(
        mixed $value,
        string $default
    ): string {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return $default;
        }

        $normalized = preg_replace(
            '/[^a-z0-9]+/i',
            '_',
            strtolower($value)
        );

        return trim(
            $normalized ?? '',
            '_'
        );
    }

    private function normalizeBoolean(
        mixed $value
    ): mixed {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $normalized = strtolower(
            trim((string) $value)
        );

        if (
            in_array(
                $normalized,
                [
                    'yes',
                    'true',
                    '1',
                    'required',
                ],
                true
            )
        ) {
            return 1;
        }

        if (
            in_array(
                $normalized,
                [
                    'no',
                    'false',
                    '0',
                    'not_required',
                ],
                true
            )
        ) {
            return 0;
        }

        return $value;
    }

    private function normalizeDate(
        mixed $value,
        bool $includeTime
    ): ?string {
        if (
            $value === null
            || trim((string) $value) === ''
        ) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $date = Carbon::instance(
                    ExcelDate::excelToDateTimeObject(
                        (float) $value
                    )
                );

                return $includeTime
                    ? $date->format(
                        'Y-m-d H:i:s'
                    )
                    : $date->format(
                        'Y-m-d'
                    );
            } catch (Throwable) {
                // Normal parser next try karega.
            }
        }

        try {
            $date = Carbon::parse(
                (string) $value
            );

            return $includeTime
                ? $date->format(
                    'Y-m-d H:i:s'
                )
                : $date->format(
                    'Y-m-d'
                );
        } catch (Throwable) {
            /*
             * Invalid date validator ko return karo.
             */
            return trim(
                (string) $value
            );
        }
    }

    private function rowToArray(
        mixed $row
    ): array {
        if ($row instanceof Collection) {
            return $row->toArray();
        }

        return (array) $row;
    }

    private function isEmptyRow(
        array $row
    ): bool {
        foreach ($row as $value) {
            if (
                trim((string) $value)
                !== ''
            ) {
                return false;
            }
        }

        return true;
    }

    private function addIssue(
        int $rowNumber,
        string $type,
        array $row,
        array $messages
    ): void {
        /*
         * Session size control:
         * maximum 100 issues display hongi.
         */
        if (count($this->issues) >= 100) {
            return;
        }

        $this->issues[] = [
            'row' => $rowNumber,

            'type' => $type,

            'project_code' =>
                $row['project_code'] ?? '',

            'service' =>
                $row[
                    'project_service_name'
                ] ?? '',

            'title' =>
                $row['title'] ?? '',

            'messages' =>
                $messages,
        ];
    }
}