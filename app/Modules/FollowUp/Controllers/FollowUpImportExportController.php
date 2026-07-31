<?php

namespace App\Modules\FollowUp\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FollowUp\Exports\FollowUpImportTemplateExport;
use App\Modules\FollowUp\Exports\FollowUpsExport;
use App\Modules\FollowUp\Imports\FollowUpsImport;
use App\Modules\FollowUp\Imports\FollowUpWorkbookImport;
use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class FollowUpImportExportController
    extends Controller
{
    public function importForm(
        Request $request
    ) {
        return view(
            'followup::import',
            [
                'canChoosePerformer' =>
                    $this->canViewAllFollowUps(
                        $request->user()
                    ),

                'types' =>
                    FollowUp::types(),

                'outcomes' =>
                    FollowUp::outcomes(),

                'pageTitle' =>
                    'Import Follow-ups',
            ]
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new FollowUpImportTemplateExport(),

            'follow-up-import-template.xlsx'
        );
    }

    public function export(
        Request $request
    ) {
        $user = $request->user();

        $canViewAll =
            $this->canViewAllFollowUps(
                $user
            );

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $due = trim(
            (string) $request->query(
                'due',
                'all'
            )
        );

        $type = trim(
            (string) $request->query(
                'type',
                ''
            )
        );

        $assignedTo = (int)
            $request->query(
                'assigned_to',
                0
            );

        $query = FollowUp::query();

        if (!$canViewAll) {
            $query->whereHas(
                'lead',

                function (
                    Builder $leadQuery
                ) use ($user) {
                    $leadQuery->where(
                        'assigned_to',
                        $user->id
                    );
                }
            );
        }

        $this->applyExportFilters(
            $query,
            $search,
            $due,
            $type,
            $assignedTo,
            $canViewAll
        );

        $fileName =
            'follow-ups-'
            . now()->format(
                'Y-m-d_H-i-s'
            )
            . '.xlsx';

        return Excel::download(
            new FollowUpsExport($query),
            $fileName
        );
    }

    public function import(
        Request $request
    ) {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],

            'duplicate_mode' => [
                'required',

                Rule::in([
                    'skip',
                    'update',
                ]),
            ],
        ]);

        $user = $request->user();

        $followUpsImport =
            new FollowUpsImport(
                $user,

                $this->canViewAllFollowUps(
                    $user
                ),

                $validated[
                    'duplicate_mode'
                ]
            );

        $workbookImport =
            new FollowUpWorkbookImport(
                $followUpsImport
            );

        try {
            Excel::import(
                $workbookImport,

                $request->file('file')
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'followup.import.form'
                )
                ->with(
                    'error',

                    $exception->getMessage()
                    ?: 'Follow-up The client Excel file could not be imported.'
                );
        }

        $result =
            $followUpsImport->result();

        $successfulRows =
            (int) $result['imported']
            + (int) $result['updated'];

        $redirect = redirect()
            ->route(
                'followup.import.form'
            )
            ->with(
                'import_result',
                $result
            );

        /*
         * All rows failed/skipped.
         */
        if ($successfulRows === 0) {
            return $redirect->with(
                'error',

                "Cann't Follow-up import. "
                . "{$result['failed']} failed aur "
                . "{$result['skipped']} skipped rows ke reasons neeche check karein."
            );
        }

        /*
         * Partial success.
         */
        if (
            (int) $result['failed'] > 0
            || (int) $result['skipped'] > 0
        ) {
            return $redirect->with(
                'success',

                "Follow-up import partially complete hua. "
                . "{$result['imported']} imported, "
                . "{$result['updated']} updated, "
                . "{$result['skipped']} skipped aur "
                . "{$result['failed']} failed."
            );
        }

        return $redirect->with(
            'success',

            "{$successfulRows} Follow-ups successfully import/update ho gaye."
        );
    }

    private function applyExportFilters(
        Builder $query,
        string $search,
        string $due,
        string $type,
        int $assignedTo,
        bool $canViewAll
    ): void {
        if ($search !== '') {
            $query->whereHas(
                'lead',

                function (
                    Builder $leadQuery
                ) use ($search) {
                    $leadQuery
                        ->where(
                            'name',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'phone',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'company',
                            'LIKE',
                            "%{$search}%"
                        );

                    if (is_numeric($search)) {
                        $leadQuery->orWhere(
                            'id',
                            (int) $search
                        );
                    }
                }
            );
        }

        if (
            $type !== ''
            && array_key_exists(
                $type,
                FollowUp::types()
            )
        ) {
            $query->where(
                'type',
                $type
            );
        }

        switch ($due) {
            case 'today':
                $query->whereDate(
                    'next_follow_up_at',
                    today()
                );
                break;

            case 'overdue':
                $query
                    ->whereNotNull(
                        'next_follow_up_at'
                    )
                    ->where(
                        'next_follow_up_at',
                        '<',
                        now()
                    );
                break;

            case 'upcoming':
                $query
                    ->whereNotNull(
                        'next_follow_up_at'
                    )
                    ->where(
                        'next_follow_up_at',
                        '>=',
                        now()
                    );
                break;

            case 'no_schedule':
                $query->whereNull(
                    'next_follow_up_at'
                );
                break;
        }

        if (
            $canViewAll
            && $assignedTo > 0
        ) {
            $query->whereHas(
                'lead',

                function (
                    Builder $leadQuery
                ) use ($assignedTo) {
                    $leadQuery->where(
                        'assigned_to',
                        $assignedTo
                    );
                }
            );
        }
    }

    private function canViewAllFollowUps(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission(
                'follow_ups.view_all'
            );
    }
}