<?php

namespace App\Modules\FollowUp\Imports;

use App\Modules\FollowUp\Models\FollowUp;
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
use RuntimeException;
use Throwable;

class FollowUpsImport extends StringValueBinder implements
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

            $requiredHeadings = [
                'lead_phone',
                'lead_email',
                'type',
                'followed_up_at',
                'outcome',
                'notes',
                'next_follow_up_at',
                'performed_by_email',
            ];

            foreach (
                $requiredHeadings
                as $requiredHeading
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
             * Normal employee supplied performer email
             * use nahi kar sakta.
             */
            if (!$this->canViewAll) {
                $row[
                    'performed_by_email'
                ] = '';
            }

            $validator = Validator::make(
                $row,
                [
                    'lead_phone' => [
                        'nullable',
                        'string',
                        'max:25',
                        'required_without:lead_email',
                    ],

                    'lead_email' => [
                        'nullable',
                        'email',
                        'max:255',
                        'required_without:lead_phone',
                    ],

                    'type' => [
                        'required',

                        Rule::in(
                            array_keys(
                                FollowUp::types()
                            )
                        ),
                    ],

                    'followed_up_at' => [
                        'required',
                        'date',
                    ],

                    'outcome' => [
                        'required',

                        Rule::in(
                            array_keys(
                                FollowUp::outcomes()
                            )
                        ),
                    ],

                    'notes' => [
                        'required',
                        'string',
                        'max:5000',
                    ],

                    'next_follow_up_at' => [
                        'nullable',
                        'date',
                        'after_or_equal:followed_up_at',
                    ],

                    'performed_by_email' => [
                        'nullable',
                        'email',
                        'max:255',
                    ],
                ],
                [
                    'lead_phone.required_without' =>
                        'Please provide either a Lead Phone number or a Lead Email address.',

                    'lead_email.required_without' =>
                        'At least one of the following is required: Lead Email or Lead Phone.',

                    'type.in' =>
                        'Invalid Follow-up type value.',

                    'outcome.in' =>
                        'Invalid Follow-up outcome value.',

                    'next_follow_up_at.after_or_equal' =>
                        'Next Follow-up date cannot be before the actual Follow-up date.',
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
                $lead,
                $leadError,
            ] = $this->resolveLead(
                $data['lead_phone']
                    ?? null,

                $data['lead_email']
                    ?? null
            );

            if ($leadError) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        $leadError,
                    ]
                );

                continue;
            }

            if (!$lead) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'Matching lead is not available in the CRM.',
                    ]
                );

                continue;
            }

            /*
             * Normal employee sirf assigned Lead
             * ka Follow-up import kar sakta hai.
             */
            if (
                !$this->canViewAll
                && (int) $lead->assigned_to
                    !== (int) $this
                        ->importedBy
                        ->id
            ) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'This lead has already been assigned to another employee.',
                    ]
                );

                continue;
            }

            /*
             * Existing manual workflow bhi Converted
             * Lead par new Follow-up block karta hai.
             */
            if ($lead->isConverted()) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'Follow-up cannot be imported for a converted lead.',
                    ]
                );

                continue;
            }

            /*
             * Duplicate key:
             * Lead + Followed-up Date + Type.
             */
            $duplicates = FollowUp::query()
                ->where(
                    'lead_id',
                    $lead->id
                )
                ->where(
                    'type',
                    $data['type']
                )
                ->where(
                    'followed_up_at',
                    $data['followed_up_at']
                )
                ->get();

            if ($duplicates->count() > 1) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'Multiple follow-ups with the same lead, date, and type already exist.',
                    ]
                );

                continue;
            }

            $duplicate =
                $duplicates->first();

            if (
                $duplicate
                && !$this->canViewAll
                && (int) $duplicate->user_id
                    !== (int) $this
                        ->importedBy
                        ->id
            ) {
                $this->failedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Failed',
                    $data,
                    [
                        'You cannot update the follow-up of another employee.',
                    ]
                );

                continue;
            }

            if (
                $duplicate
                && $this->duplicateMode
                    === 'skip'
            ) {
                $this->skippedRows++;

                $this->addIssue(
                    $rowNumber,
                    'Skipped',
                    $data,
                    [
                        'The same lead, date, and follow-up type already exist.',
                    ]
                );

                continue;
            }

            /*
             * Performed By resolve karo.
             */
            $performedBy = null;

            if (
                $this->canViewAll
                && !empty(
                    $data[
                        'performed_by_email'
                    ]
                )
            ) {
                $performedBy = User::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [
                            strtolower(
                                $data[
                                    'performed_by_email'
                                ]
                            ),
                        ]
                    )
                    ->first();

                if (!$performedBy) {
                    $this->failedRows++;

                    $this->addIssue(
                        $rowNumber,
                        'Failed',
                        $data,
                        [
                            'The email address of the person who performed the action is not available in the CRM.',
                        ]
                    );

                    continue;
                }

                if (!$performedBy->is_active) {
                    $this->failedRows++;

                    $this->addIssue(
                        $rowNumber,
                        'Failed',
                        $data,
                        [
                            'Performed By user inactive hai.',
                        ]
                    );

                    continue;
                }
            }

            /*
             * Existing record update aur blank performer:
             * original performer preserve hoga.
             *
             * New record aur blank performer:
             * importing user performer hoga.
             */
            if ($performedBy) {
                $performedById =
                    $performedBy->id;
            } elseif ($duplicate) {
                $performedById =
                    $duplicate->user_id;
            } else {
                $performedById =
                    $this->importedBy->id;
            }

            $payload = [
                'user_id' =>
                    $performedById,

                'type' =>
                    $data['type'],

                'followed_up_at' =>
                    $data['followed_up_at'],

                'outcome' =>
                    $data['outcome'],

                'notes' =>
                    $data['notes'],

                'next_follow_up_at' =>
                    $data[
                        'next_follow_up_at'
                    ] ?: null,
            ];

            try {
                DB::transaction(
                    function () use (
                        $lead,
                        $duplicate,
                        $payload
                    ) {
                        $lockedLead = Lead::query()
                            ->whereKey(
                                $lead->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                        if (
                            $lockedLead
                                ->isConverted()
                        ) {
                            throw new RuntimeException(
                                'Converted Lead par Follow-up import nahi kiya ja sakta.'
                            );
                        }

                        if (
                            !$this->canViewAll
                            && (int) $lockedLead
                                ->assigned_to
                                !== (int) $this
                                    ->importedBy
                                    ->id
                        ) {
                            throw new RuntimeException(
                                'Lead assignment has been changed. Access denied.'
                            );
                        }

                        if ($duplicate) {
                            $followUp =
                                FollowUp::query()
                                    ->whereKey(
                                        $duplicate->id
                                    )
                                    ->lockForUpdate()
                                    ->firstOrFail();

                            if (
                                !$this->canViewAll
                                && (int) $followUp
                                    ->user_id
                                    !== (int) $this
                                        ->importedBy
                                        ->id
                            ) {
                                throw new RuntimeException(
                                    'You cannot update the follow-up of another employee.'
                                );
                            }

                            $followUp->update(
                                $payload
                            );
                        } else {
                            FollowUp::create([
                                'lead_id' =>
                                    $lockedLead->id,

                                ...$payload,
                            ]);
                        }

                        $this->syncLeadNextFollowUp(
                            $lockedLead
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
                            : 'The follow-up import failed due to a database error.',
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

    private function resolveLead(
        ?string $phone,
        ?string $email
    ): array {
        $phoneMatches = collect();

        if ($phone) {
            $phoneMatches = Lead::query()
                ->where(
                    'phone',
                    $phone
                )
                ->limit(2)
                ->get();
        }

        if ($phoneMatches->count() > 1) {
            return [
                null,

                'Multiple leads are available with the same phone number.',
            ];
        }

        $emailMatches = collect();

        if ($email) {
            $emailMatches = Lead::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [
                        strtolower($email),
                    ]
                )
                ->limit(2)
                ->get();
        }

        if ($emailMatches->count() > 1) {
            return [
                null,

                'Multiple leads are available with the same email address.',
            ];
        }

        $phoneLead =
            $phoneMatches->first();

        $emailLead =
            $emailMatches->first();

        if (
            $phoneLead
            && $emailLead
            && (int) $phoneLead->id
                !== (int) $emailLead->id
        ) {
            return [
                null,

                'The phone number and email match different leads',
            ];
        }

        return [
            $phoneLead ?? $emailLead,

            null,
        ];
    }

    private function syncLeadNextFollowUp(
        Lead $lead
    ): void {
        /*
         * Latest actual interaction ki schedule value
         * Lead summary field me rahegi.
         *
         * followed_up_at use karna historical Excel
         * import ke liye important hai.
         */
        $latestFollowUp =
            FollowUp::query()
                ->where(
                    'lead_id',
                    $lead->id
                )
                ->orderByDesc(
                    'followed_up_at'
                )
                ->orderByDesc('id')
                ->first();

        $lead->update([
            'next_follow_up_at' =>
                $latestFollowUp
                    ?->next_follow_up_at,
        ]);
    }

    private function normalizeRow(
        array $row
    ): array {
        return [
            'lead_phone' => trim(
                (string) (
                    $row[
                        'lead_phone'
                    ] ?? ''
                )
            ),

            'lead_email' => strtolower(
                trim(
                    (string) (
                        $row[
                            'lead_email'
                        ] ?? ''
                    )
                )
            ),

            'type' =>
                $this->normalizeMappedOption(
                    $row['type'] ?? '',
                    FollowUp::types()
                ),

            'followed_up_at' =>
                $this->normalizeDate(
                    $row[
                        'followed_up_at'
                    ] ?? null
                ),

            'outcome' =>
                $this->normalizeMappedOption(
                    $row['outcome'] ?? '',
                    FollowUp::outcomes()
                ),

            'notes' => trim(
                (string) (
                    $row['notes'] ?? ''
                )
            ),

            'next_follow_up_at' =>
                $this->normalizeDate(
                    $row[
                        'next_follow_up_at'
                    ] ?? null
                ),

            'performed_by_email' =>
                strtolower(
                    trim(
                        (string) (
                            $row[
                                'performed_by_email'
                            ] ?? ''
                        )
                    )
                ),
        ];
    }

    private function normalizeMappedOption(
        mixed $value,
        array $options
    ): string {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return '';
        }

        $normalizedValue =
            $this->slugValue($value);

        /*
         * Excel me exact database key:
         * call, whatsapp, interested...
         */
        if (
            array_key_exists(
                $normalizedValue,
                $options
            )
        ) {
            return $normalizedValue;
        }

        /*
         * Visible label bhi accept hoga:
         * Phone Call, Callback Required...
         */
        foreach (
            $options as $key => $label
        ) {
            if (
                $this->slugValue($label)
                === $normalizedValue
            ) {
                return $key;
            }
        }

        return $normalizedValue;
    }

    private function slugValue(
        string $value
    ): string {
        $normalized = preg_replace(
            '/[^a-z0-9]+/i',
            '_',
            strtolower(
                trim($value)
            )
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

        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject(
                        (float) $value
                    )
                )->format(
                    'Y-m-d H:i:s'
                );
            } catch (Throwable) {
                // Normal parser next try karega.
            }
        }

        try {
            return Carbon::parse(
                (string) $value
            )->format(
                'Y-m-d H:i:s'
            );
        } catch (Throwable) {
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
        if (count($this->issues) >= 100) {
            return;
        }

        $this->issues[] = [
            'row' => $rowNumber,

            'type' => $type,

            'lead_phone' =>
                $row['lead_phone'] ?? '',

            'lead_email' =>
                $row['lead_email'] ?? '',

            'follow_up_type' =>
                $row['type'] ?? '',

            'messages' =>
                $messages,
        ];
    }
}