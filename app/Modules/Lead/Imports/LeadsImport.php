<?php

namespace App\Modules\Lead\Imports;

use App\Modules\Lead\Models\Lead;
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
use App\Modules\Lead\Models\LeadPriority;
use App\Modules\Lead\Models\LeadStatus;
use RuntimeException;
use Throwable;

class LeadsImport extends StringValueBinder implements
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

    private array $issues = [];

    private string $defaultStatus;

    private string $defaultPriority;

    public function __construct(
        private User $importedBy,
        private bool $canAssign,
        private string $duplicateMode
    ) {
        $this->defaultStatus =
            Lead::defaultStatus();

        $this->defaultPriority =
            Lead::defaultPriority();
    }

    public function collection(
        Collection $rows
    ): void {
        if ($rows->count() > 5000) {
            throw new RuntimeException(
                'Lead rows can be imported.'
            );
        }

        /*
         * Required headings check.
         */
        if ($rows->isNotEmpty()) {
            $firstRow = $this->rowToArray(
                $rows->first()
            );

            foreach (
                [
                    'name',
                    'phone',
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
             * Heading row 1 hai, isliye actual Excel
             * row number index + 2 hoga.
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
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'phone' => [
                        'required',
                        'string',
                        'max:25',
                    ],

                    'email' => [
                        'nullable',
                        'email',
                        'max:255',
                    ],

                    'company' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'source' => [
                        'required',
                        Rule::in(
                            array_keys(
                                Lead::sources()
                            )
                        ),
                    ],

                    'status' => [
                        'required',
                        Rule::in(
                            array_keys(
                                Lead::editableStatuses()
                            )
                        ),
                    ],

                    'priority' => [
                        'required',

                        Rule::in(
                            array_keys(
                                Lead::activePriorities()
                            )
                        ),
                    ],

                    'assigned_employee_email' => [
                        'nullable',
                        'email',
                        'max:255',
                    ],

                    'next_follow_up_at' => [
                        'nullable',
                        'date',
                    ],

                    'notes' => [
                        'nullable',
                        'string',
                        'max:5000',
                    ],
                ],
                [
                    'status.in' =>
                        'Invalid or inactive Lead status. Please use an active editable status from CRM Settings.',

                    'source.in' =>
                        'Invalid Lead source value.',

                    'priority.in' =>
                        'Invalid or inactive Lead priority. Please use an active priority from CRM Settings.',
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

            $duplicate = $this->findDuplicate(
                $data['phone'],
                $data['email'] ?? null
            );

            /*
             * Converted Lead ko Excel se update nahi
             * karna hai.
             */
            if (
                $duplicate
                && $duplicate->isConverted()
            ) {
                $this->skippedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Skipped',
                    $data,
                    [
                        'Converted leads cannot be updated via Excel.',
                    ]
                );

                continue;
            }

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
                        'A lead with the same phone number or email already exists.',
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
                $assignedEmployee = User::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [
                            strtolower(
                                $data[
                                    'assigned_employee_email'
                                ]
                            ),
                        ]
                    )
                    ->first();

                if (!$assignedEmployee) {
                    $this->failedRows++;

                    $this->addIssue(
                        $rowNumber,
                        'Failed',
                        $data,
                        [
                            'The assigned employees email was not found in the CRM.',
                        ]
                    );

                    continue;
                }

                if (!$assignedEmployee->is_active) {
                    $this->failedRows++;

                    $this->addIssue(
                        $rowNumber,
                        'Failed',
                        $data,
                        [
                            'Assigned employee inactive.',
                        ]
                    );

                    continue;
                }
            }

            /*
             * Normal employee ki imported Lead
             * automatically usi ko assign hogi.
             *
             * Admin blank email chhode to:
             * New Lead = unassigned
             * Existing Lead = current assignment preserve
             */
            if (!$this->canAssign) {
                $assignedTo =
                    $this->importedBy->id;
            } elseif ($assignedEmployee) {
                $assignedTo =
                    $assignedEmployee->id;
            } elseif ($duplicate) {
                $assignedTo =
                    $duplicate->assigned_to;
            } else {
                $assignedTo = null;
            }

            /*
             * Closed Lead status par next follow-up clear.
             *
             * Closed status Settings database se
             * determine hoga. Isliye yahan "lost"
             * hardcoded nahi hai.
             */
            $statusDefinition =
                LeadStatus::query()
                    ->where(
                        'slug',
                        $data['status']
                    )
                    ->first();

            if (
                $statusDefinition
                && $statusDefinition->is_closed
            ) {
                $data[
                    'next_follow_up_at'
                ] = null;
            }

            if (!$this->canAssign) {
                $assignedTo =
                    $this->importedBy->id;
            } elseif ($assignedEmployee) {
                $assignedTo =
                    $assignedEmployee->id;
            } elseif ($duplicate) {
                $assignedTo =
                    $duplicate->assigned_to;
            } else {
                $assignedTo = null;
            }

            $payload = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' =>
                    $data['email'] ?: null,
                'company' =>
                    $data['company'] ?: null,
                'source' => $data['source'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'assigned_to' => $assignedTo,
                'next_follow_up_at' =>
                    $data['next_follow_up_at']
                    ?: null,
                'notes' =>
                    $data['notes'] ?: null,
            ];

            try {
                DB::transaction(
                    function () use ($duplicate, $payload) {
                        if ($duplicate) {
                            $lead = Lead::query()
                                ->whereKey(
                                    $duplicate->id
                                )
                                ->lockForUpdate()
                                ->firstOrFail();

                            if ($lead->isConverted()) {
                                throw new RuntimeException(
                                    'Converted leads cannot be updated.'
                                );
                            }

                            $lead->update($payload);

                            return;
                        }

                        Lead::create([
                            ...$payload,

                            'created_by' =>
                                $this->importedBy->id,
                        ]);
                    }
                );

                if ($duplicate) {
                    $this->updatedRows++;
                } else {
                    $this->importedRows++;
                }
            } catch (Throwable $exception) {
                report($exception);

                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'The row could not be imported due to a database error.',
                    ]
                );
            }
        }
    }

    public function result(): array
    {
        return [
            'total' => $this->totalRows,
            'imported' => $this->importedRows,
            'updated' => $this->updatedRows,
            'skipped' => $this->skippedRows,
            'failed' => $this->failedRows,
            'issues' => $this->issues,
        ];
    }

    private function findDuplicate(
        string $phone,
        ?string $email
    ): ?Lead {
        return Lead::query()
            ->where(
                function ($query) use ($phone, $email) {
                    $query->where(
                        'phone',
                        $phone
                    );

                    if ($email) {
                        $query->orWhereRaw(
                            'LOWER(email) = ?',
                            [
                                strtolower($email),
                            ]
                        );
                    }
                }
            )
            ->first();
    }

    private function normalizeRow(
        array $row
    ): array {
        return [
            'name' => trim(
                (string) (
                    $row['name'] ?? ''
                )
            ),

            'phone' => trim(
                (string) (
                    $row['phone'] ?? ''
                )
            ),

            'email' => strtolower(
                trim(
                    (string) (
                        $row['email'] ?? ''
                    )
                )
            ),

            'company' => trim(
                (string) (
                    $row['company'] ?? ''
                )
            ),

            'source' => $this->normalizeOption(
                $row['source'] ?? '',
                'other'
            ),

            'status' => $this->normalizeOption(
                $row['status'] ?? '',
                $this->defaultStatus
            ),

            'priority' => $this->normalizeOption(
                $row['priority'] ?? '',
                $this->defaultPriority
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

            'next_follow_up_at' =>
                $this->normalizeDate(
                    $row[
                        'next_follow_up_at'
                    ] ?? null
                ),

            'notes' => trim(
                (string) (
                    $row['notes'] ?? ''
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

    private function normalizeDate(
        mixed $value
    ): ?string {
        if (
            $value === null
            || trim((string) $value) === ''
        ) {
            return null;
        }

        /*
         * Excel serial date support.
         */
        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject(
                        (float) $value
                    )
                )->format('Y-m-d H:i:s');
            } catch (Throwable) {
                // Normal date parser try karega.
            }
        }

        try {
            return Carbon::parse(
                (string) $value
            )->format('Y-m-d H:i:s');
        } catch (Throwable) {
            /*
             * Invalid value validator ko pass hogi,
             * jahan proper error generate hoga.
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
                trim((string) $value) !== ''
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
         * Session bahut large na ho,
         * maximum 100 issues display hongi.
         */
        if (count($this->issues) >= 100) {
            return;
        }

        $this->issues[] = [
            'row' => $rowNumber,
            'type' => $type,
            'name' =>
                $row['name'] ?? '',
            'phone' =>
                $row['phone'] ?? '',
            'messages' => $messages,
        ];
    }
}