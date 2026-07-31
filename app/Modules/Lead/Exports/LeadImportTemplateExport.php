<?php

namespace App\Modules\Lead\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LeadImportTemplateExport implements
    FromArray,
    WithHeadings,
    WithTitle
{
    public function array(): array
    {
        /*
         * Template me sample record nahi rakha gaya,
         * taki galti se dummy Lead import na ho.
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
            'source',
            'status',
            'priority',
            'assigned_employee_email',
            'next_follow_up_at',
            'notes',
        ];
    }

    public function title(): string
    {
        return 'Leads Import';
    }
}