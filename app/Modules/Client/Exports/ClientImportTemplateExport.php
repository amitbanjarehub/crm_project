<?php

namespace App\Modules\Client\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClientImportTemplateExport implements
    FromArray,
    WithHeadings,
    WithTitle
{
    public function array(): array
    {
        /*
         * Sample row nahi rakhenge.
         * Isse dummy Client accidentally import nahi hoga.
         */
        return [];
    }

    public function headings(): array
    {
        return [
            'name',
            'phone',
            'email',
            'company',
            'status',
            'assigned_employee_email',
            'notes',
        ];
    }

    public function title(): string
    {
        return 'Clients Import';
    }
}