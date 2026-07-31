<?php

namespace App\Modules\Lead\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lead\Exports\LeadImportTemplateExport;
use App\Modules\Lead\Exports\LeadsExport;
use App\Modules\Lead\Imports\LeadsImport;
use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Support\AuthorizesLeadAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class LeadImportExportController extends Controller
{
    use AuthorizesLeadAccess;

    public function importForm(
        Request $request
    ) {
        return view(
            'lead::import',
            [
                'canAssign' =>
                    $this->canAssignLeads(
                        $request->user()
                    ),

                'sources' =>
                    Lead::sources(),

                'statuses' =>
                    Lead::editableStatuses(),

                'priorities' =>
                    Lead::activePriorities(),

                'defaultStatus' =>
                    Lead::defaultStatus(),

                'defaultPriority' =>
                    Lead::defaultPriority(),

                'pageTitle' =>
                    'Import Leads',
            ]
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new LeadImportTemplateExport(),
            'lead-import-template.xlsx'
        );
    }

    public function export(
        Request $request
    ) {
        $loggedInUser =
            $request->user();

        $canViewAll =
            $this->canViewAllLeads(
                $loggedInUser
            );

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $status = trim(
            (string) $request->query(
                'status',
                ''
            )
        );

        $priority = trim(
            (string) $request->query(
                'priority',
                ''
            )
        );

        $source = trim(
            (string) $request->query(
                'source',
                ''
            )
        );

        $assignedTo = (int) 
            $request->query(
                'assigned_to',
                0
            );

        $query = Lead::query();

        /*
         * Normal user sirf assigned Leads
         * export kar sakta hai.
         */
        if (!$canViewAll) {
            $query->where(
                'assigned_to',
                $loggedInUser->id
            );
        }

        $this->applyExportFilters(
            $query,
            $search,
            $status,
            $priority,
            $source,
            $assignedTo,
            $canViewAll
        );

        $fileName =
            'leads-'
            . now()->format(
                'Y-m-d_H-i-s'
            )
            . '.xlsx';

        return Excel::download(
            new LeadsExport($query),
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

        $import = new LeadsImport(
            $request->user(),

            $this->canAssignLeads(
                $request->user()
            ),

            $validated['duplicate_mode']
        );

        try {
            Excel::import(
                $import,
                $request->file('file')
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('lead.import.form')
                ->with(
                    'error',
                    $exception->getMessage()
                    ?: 'The Excel file could not be imported.'
                );
        }

        return redirect()
            ->route('lead.import.form')
            ->with(
                'success',
                'Lead import process complete.'
            )
            ->with(
                'import_result',
                $import->result()
            );
    }

    private function applyExportFilters(
        Builder $query,
        string $search,
        string $status,
        string $priority,
        string $source,
        int $assignedTo,
        bool $canViewAll
    ): void {
        if ($search !== '') {
            $query->where(
                function (Builder $searchQuery) use ($search) {
                    $searchQuery
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
                        $searchQuery->orWhere(
                            'id',
                            (int) $search
                        );
                    }
                }
            );
        }

        if (
            $status !== ''
            && array_key_exists(
                $status,
                Lead::statuses()
            )
        ) {
            $query->where(
                'status',
                $status
            );
        }

        if (
            $priority !== ''
            && array_key_exists(
                $priority,
                Lead::priorities()
            )
        ) {
            $query->where(
                'priority',
                $priority
            );
        }

        if (
            $source !== ''
            && array_key_exists(
                $source,
                Lead::sources()
            )
        ) {
            $query->where(
                'source',
                $source
            );
        }

        if (
            $canViewAll
            && $assignedTo > 0
        ) {
            $query->where(
                'assigned_to',
                $assignedTo
            );
        }
    }
}