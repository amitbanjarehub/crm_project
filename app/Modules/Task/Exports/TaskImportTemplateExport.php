<?php

namespace App\Modules\Task\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TaskImportTemplateExport implements
    FromArray,
    WithHeadings,
    WithTitle
{
    public function array(): array
    {
        /*
         * Sample row intentionally blank hai.
         * Isse dummy task import nahi hogi.
         */
        return [];
    }

    public function headings(): array
    {
        return [
            'project_code',
            'project_service_name',
            'title',
            'description',
            'assigned_employee_email',
            'priority',
            'requires_review',
            'reviewer_email',
            'start_date',
            'due_at',
            'estimated_hours',
        ];
    }

    public function title(): string
    {
        return 'Tasks Import';
    }
}