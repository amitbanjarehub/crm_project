<?php

namespace App\Modules\Client\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Client\Exports\ClientImportTemplateExport;
use App\Modules\Client\Exports\ClientsExport;
use App\Modules\Client\Imports\ClientsImport;
use App\Modules\Client\Models\Client;
use App\Modules\User\Models\User;
use App\Modules\Client\Imports\ClientWorkbookImport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ClientImportExportController extends Controller
{
    public function importForm(
        Request $request
    ) {
        return view(
            'client::import',
            [
                'canAssign' =>
                    $this->canAssignClients(
                        $request->user()
                    ),

                'statuses' =>
                    Client::statuses(),

                'pageTitle' =>
                    'Import Clients',
            ]
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new ClientImportTemplateExport(),

            'client-import-template.xlsx'
        );
    }

    public function export(
        Request $request
    ) {
        $loggedInUser =
            $request->user();

        $canViewAll =
            $this->canViewAllClients(
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

        $assignedTo = (int) 
            $request->query(
                'assigned_to',
                0
            );

        $query = Client::query();

        /*
         * Normal employee sirf assigned
         * Clients export karega.
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
            $assignedTo,
            $canViewAll
        );

        $fileName =
            'clients-'
            . now()->format(
                'Y-m-d_H-i-s'
            )
            . '.xlsx';

        return Excel::download(
            new ClientsExport($query),
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

        $loggedInUser =
            $request->user();

        $import = new ClientsImport(
            $loggedInUser,

            $this->canViewAllClients(
                $loggedInUser
            ),

            $this->canAssignClients(
                $loggedInUser
            ),

            $validated[
                'duplicate_mode'
            ]
        );

        $workbookImport =
            new ClientWorkbookImport(
                $import
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
                    'client.import.form'
                )
                ->with(
                    'error',

                    $exception->getMessage()
                    ?: 'The client Excel file could not be imported.'
                );
        }

        return redirect()
            ->route(
                'client.import.form'
            )
            ->with(
                'success',

                'Client import process complete.'
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
                Client::statuses()
            )
        ) {
            $query->where(
                'status',
                $status
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

    private function canViewAllClients(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission(
                'clients.view_all'
            );
    }

    private function canAssignClients(
        User $user
    ): bool {
        return $this->canViewAllClients(
            $user
        )
            && (
                $user->isSuperAdmin()
                || $user->hasPermission(
                    'clients.assign'
                )
            );
    }
}