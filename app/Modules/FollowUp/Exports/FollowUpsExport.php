<?php

namespace App\Modules\FollowUp\Exports;

use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\Lead\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class FollowUpsExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle
{
    use Exportable;

    public function __construct(
        private Builder $followUpQuery
    ) {
    }

    public function query(): Builder
    {
        return $this->followUpQuery
            ->with([
                'lead:id,name,phone,email,company,status,assigned_to',

                'lead.assignedUser:id,name,email',

                'user:id,name,email',
            ])
            ->orderByDesc('followed_up_at')
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'Follow-up ID',
            'Lead ID',
            'Lead Name',
            'Lead Phone',
            'Lead Email',
            'Company',
            'Lead Status',
            'Lead Owner',
            'Lead Owner Email',
            'Follow-up Type',
            'Followed-up Date',
            'Outcome',
            'Notes',
            'Next Follow-up Date',
            'Schedule State',
            'Performed By',
            'Performed By Email',
            'Created Date',
            'Updated Date',
        ];
    }

    public function map($followUp): array
    {
        return [
            $followUp->id,

            $followUp->lead_id,

            $followUp->lead?->name ?? '',

            $followUp->lead?->phone ?? '',

            $followUp->lead?->email ?? '',

            $followUp->lead?->company ?? '',

            $followUp->lead?->status
                ? (
                    Lead::statuses()[
                        $followUp->lead->status
                    ] ?? ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $followUp->lead->status
                        )
                    )
                )
                : '',

            $followUp
                ->lead
                ?->assignedUser
                ?->name
                ?? 'Unassigned',

            $followUp
                ->lead
                ?->assignedUser
                ?->email
                ?? '',

            FollowUp::types()[
                $followUp->type
            ] ?? ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $followUp->type
                )
            ),

            $followUp->followed_up_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            FollowUp::outcomes()[
                $followUp->outcome
            ] ?? ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $followUp->outcome
                )
            ),

            $followUp->notes,

            $followUp->next_follow_up_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $this->scheduleState(
                $followUp
            ),

            $followUp->user?->name
                ?? 'Deleted User',

            $followUp->user?->email
                ?? '',

            $followUp->created_at
                ?->format('Y-m-d H:i:s')
                ?? '',

            $followUp->updated_at
                ?->format('Y-m-d H:i:s')
                ?? '',
        ];
    }

    public function title(): string
    {
        return 'Follow-ups';
    }

    private function scheduleState(
        FollowUp $followUp
    ): string {
        if (
            in_array(
                $followUp->lead?->status,
                [
                    'converted',
                    'lost',
                ],
                true
            )
        ) {
            return 'Closed Lead';
        }

        if (!$followUp->next_follow_up_at) {
            return 'No Schedule';
        }

        if (
            $followUp
                ->next_follow_up_at
                ->isToday()
        ) {
            return 'Due Today';
        }

        if (
            $followUp
                ->next_follow_up_at
                ->isPast()
        ) {
            return 'Overdue';
        }

        return 'Upcoming';
    }
}