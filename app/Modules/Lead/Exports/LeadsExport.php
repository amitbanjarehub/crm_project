<?php

namespace App\Modules\Lead\Exports;

use App\Modules\Lead\Models\Lead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LeadsExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle
{
    use Exportable;

    public function __construct(
        private Builder $leadQuery
    ) {
    }

    public function query(): Builder
    {
        return $this->leadQuery
            ->with([
                'assignedUser:id,name,email',
                'creator:id,name,email',
                'convertedBy:id,name,email',
                'client:id,lead_id',
            ])
            ->withCount('followUps')
            ->withMax(
                'followUps as last_followed_up_at',
                'followed_up_at'
            )
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Lead ID',
            'Name',
            'Phone',
            'Email',
            'Company',
            'Source',
            'Status',
            'Priority',
            'Assigned Employee',
            'Assigned Employee Email',
            'Next Follow-up',
            'Follow-up Count',
            'Last Follow-up',
            'Converted Date',
            'Converted By',
            'Notes',
            'Created By',
            'Created Date',
        ];
    }

    public function map($lead): array
    {
        $lastFollowUp = $lead->last_followed_up_at
            ? Carbon::parse(
                $lead->last_followed_up_at
            )->format('Y-m-d H:i:s')
            : '';

        return [
            $lead->id,
            $lead->name,
            $lead->phone,
            $lead->email ?? '',
            $lead->company ?? '',

            Lead::sources()[$lead->source]
                ?? ucfirst($lead->source),

            Lead::statuses()[$lead->status]
                ?? ucfirst($lead->status),

            Lead::priorities()[$lead->priority]
                ?? ucfirst($lead->priority),

            $lead->assignedUser?->name
                ?? 'Unassigned',

            $lead->assignedUser?->email
                ?? '',

            $lead->next_follow_up_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            (int) $lead->follow_ups_count,

            $lastFollowUp,

            $lead->converted_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $lead->convertedBy?->name
                ?? '',

            $lead->notes ?? '',

            $lead->creator?->name
                ?? '',

            $lead->created_at
                ?->format('Y-m-d H:i:s')
                ?? '',
        ];
    }

    public function title(): string
    {
        return 'Leads';
    }
}