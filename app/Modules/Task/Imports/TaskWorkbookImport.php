<?php

namespace App\Modules\Task\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TaskWorkbookImport implements WithMultipleSheets
{
    public function __construct(
        private TasksImport $tasksImport
    ) {
    }

    public function sheets(): array
    {
        /*
         * Sirf first worksheet process hogi.
         * Additional Instructions sheets ignore hongi.
         */
        return [
            0 => $this->tasksImport,
        ];
    }
}