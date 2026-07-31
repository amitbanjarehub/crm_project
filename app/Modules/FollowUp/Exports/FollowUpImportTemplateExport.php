<?php

namespace App\Modules\FollowUp\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FollowUpImportTemplateExport implements
    FromArray,
    WithHeadings,
    WithTitle
{
    public function array(): array
    {
        /*
         * Sample row intentionally blank hai.
         * Dummy Follow-up accidentally import nahi hogi.
         */
        return [];
    }

    public function headings(): array
    {
        return [
            'lead_phone',
            'lead_email',
            'type',
            'followed_up_at',
            'outcome',
            'notes',
            'next_follow_up_at',
            'performed_by_email',
        ];
    }

    public function title(): string
    {
        return 'Follow-ups Import';
    }
}