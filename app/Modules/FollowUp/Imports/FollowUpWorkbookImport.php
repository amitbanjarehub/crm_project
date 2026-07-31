<?php

namespace App\Modules\FollowUp\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FollowUpWorkbookImport implements
    WithMultipleSheets
{
    public function __construct(
        private FollowUpsImport $followUpsImport
    ) {
    }

    public function sheets(): array
    {
        /*
         * Sirf first worksheet process hogi.
         * Baaki sheets ignore hongi.
         */
        return [
            0 => $this->followUpsImport,
        ];
    }
}