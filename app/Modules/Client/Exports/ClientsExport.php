<?php

namespace App\Modules\Client\Exports;

use App\Modules\Client\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClientsExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle
{
    use Exportable;

    public function __construct(
        private Builder $clientQuery
    ) {
    }

    public function query(): Builder
    {
        return $this->clientQuery
            ->with([
                'assignedUser:id,name,email',

                'creator:id,name,email',

                'lead:id,name,phone,email,status,converted_at',
            ])
            ->withCount('projects')
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Client ID',
            'Name',
            'Phone',
            'Email',
            'Company',
            'Status',
            'Assigned Employee',
            'Assigned Employee Email',
            'Client Source',
            'Source Lead ID',
            'Source Lead Name',
            'Source Lead Status',
            'Lead Converted Date',
            'Created By',
            'Total Projects',
            'Notes',
            'Created Date',
            'Updated Date',
        ];
    }

    public function map($client): array
    {
        return [
            $client->id,

            $client->name,

            $client->phone,

            $client->email ?? '',

            $client->company ?? '',

            Client::statuses()[
                $client->status
            ] ?? ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $client->status
                )
            ),

            $client->assignedUser?->name
                ?? 'Unassigned',

            $client->assignedUser?->email
                ?? '',

            $client->lead_id
                ? 'Converted Lead'
                : 'Manual / Imported Client',

            $client->lead_id ?? '',

            $client->lead?->name ?? '',

            $client->lead?->status
                ? ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $client->lead->status
                    )
                )
                : '',

            $client->lead?->converted_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $client->creator?->name
                ?? 'Unknown User',

            (int) $client->projects_count,

            $client->notes ?? '',

            $client->created_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $client->updated_at
                ?->format('Y-m-d H:i:s')
                ?? '',
        ];
    }

    public function title(): string
    {
        return 'Clients';
    }
}