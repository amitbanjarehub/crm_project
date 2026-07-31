<?php

namespace App\Modules\Client\Imports;

use App\Modules\Client\Models\Client;
use App\Modules\User\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use RuntimeException;
use Throwable;

class ClientsImport extends StringValueBinder implements
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

    public function __construct(
        private User $importedBy,
        private bool $canViewAll,
        private bool $canAssign,
        private string $duplicateMode
    ) {
    }

    public function collection(
        Collection $rows
    ): void {
        if ($rows->count() > 5000) {
            throw new RuntimeException(
                'Maximum import limit: 5,000 client rows per Excel file.'
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
             * Heading row first row hai.
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

            /*
             * Normal employee ke liye assignment
             * email completely ignore hogi.
             */
            if (!$this->canAssign) {
                $row[
                    'assigned_employee_email'
                ] = '';
            }

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

                    'status' => [
                        'required',

                        Rule::in(
                            array_keys(
                                Client::statuses()
                            )
                        ),
                    ],

                    'assigned_employee_email' => [
                        'nullable',
                        'email',
                        'max:255',
                    ],

                    'notes' => [
                        'nullable',
                        'string',
                        'max:5000',
                    ],
                ],
                [
                    'status.in' =>
                        'Invalid Client status value.',
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

            [
                $duplicate,
                $duplicateError,
            ] = $this->resolveDuplicate(
                $data['phone'],
                $data['email'] ?? null
            );

            /*
             * Phone aur email alag-alag existing
             * Clients se match kar rahe hain.
             */
            if ($duplicateError) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        $duplicateError,
                    ]
                );

                continue;
            }

            /*
             * Normal employee doosre employee ka
             * Client update nahi kar sakta.
             */
            if (
                $duplicate
                && !$this->canAccessClient(
                    $duplicate
                )
            ) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'A client with the same phone number or email is already assigned to another employee.',
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
                        'A client with the same phone number or email already exists.',
                    ]
                );

                continue;
            }

            /*
             * Assigned employee email resolve karo.
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
                            'The assigned employees email is not available in the CRM.',
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
                            'Assigned employee inactive',
                        ]
                    );

                    continue;
                }
            }

            /*
             * Assignment logic:
             *
             * Normal user:
             * New Client = logged-in employee
             * Existing Client = existing assignment
             *
             * Admin/Manager:
             * Email present = selected employee
             * New + blank = unassigned
             * Existing + blank = existing assignment preserved
             */
            if (!$this->canAssign) {
                $assignedTo = $duplicate
                    ? $duplicate->assigned_to
                    : $this->importedBy->id;
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

                'status' =>
                    $data['status'],

                'assigned_to' =>
                    $assignedTo,

                'notes' =>
                    $data['notes'] ?: null,
            ];

            try {
                DB::transaction(
                    function () use (
                        $duplicate,
                        $payload
                    ) {
                        if ($duplicate) {
                            $client = Client::query()
                                ->whereKey(
                                    $duplicate->id
                                )
                                ->lockForUpdate()
                                ->firstOrFail();

                            if (
                                !$this->canAccessClient(
                                    $client
                                )
                            ) {
                                throw new RuntimeException(
                                    'Client access denied.'
                                );
                            }

                            /*
                             * lead_id aur created_by payload me nahi hain.
                             * Isliye converted Client ka Lead relation
                             * aur original creator preserve rahenge.
                             */
                            $client->update(
                                $payload
                            );

                            return;
                        }

                        Client::create([
                            ...$payload,

                            /*
                             * Excel imported Client manual
                             * Client ki tarah create hoga.
                             */
                            'lead_id' => null,

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
                        'The client could not be imported due to a database error.',
                    ]
                );
            }
        }
    }

    public function result(): array
    {
        return [
            'total' => $this->totalRows,

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

    /**
     * Phone aur email duplicate safely resolve karega.
     */
    private function resolveDuplicate(
        string $phone,
        ?string $email
    ): array {
        $phoneClient = Client::query()
            ->where(
                'phone',
                $phone
            )
            ->first();

        $emailClient = null;

        if ($email) {
            $emailClient = Client::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        strtolower($email),
                    ]
                )
                ->first();
        }

        if (
            $phoneClient
            && $emailClient
            && (int) $phoneClient->id
                !== (int) $emailClient->id
        ) {
            return [
                null,

                'The phone number and email match two different existing clients.',
            ];
        }

        return [
            $phoneClient ?? $emailClient,

            null,
        ];
    }

    private function canAccessClient(
        Client $client
    ): bool {
        if ($this->canViewAll) {
            return true;
        }

        return (int) $client->assigned_to
            === (int) $this->importedBy->id;
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

            'status' => $this->normalizeOption(
                $row['status'] ?? '',
                'active'
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
         * Session size control ke liye maximum
         * 100 issues show hongi.
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

            'messages' =>
                $messages,
        ];
    }
}