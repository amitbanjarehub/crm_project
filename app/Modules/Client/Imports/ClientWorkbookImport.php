<?php

namespace App\Modules\Client\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ClientWorkbookImport implements WithMultipleSheets
{
    public function __construct(
        private ClientsImport $clientsImport
    ) {
    }

    public function sheets(): array
    {
        /*
         * Sirf "Clients Import" worksheet process hogi.
         * Baaki Instructions ya other sheets ignore hongi.
         */
        return [
            'Clients Import' =>
                $this->clientsImport,
        ];
    }
}