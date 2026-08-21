<?php

namespace App\Modules\Lead\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Models\LeadKanbanPreference;
use App\Modules\Lead\Support\AuthorizesLeadAccess;
use App\Modules\Lead\Support\LeadKanbanService;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadKanbanController extends Controller
{
    use AuthorizesLeadAccess;

    public function __construct(
        private readonly
        LeadKanbanService $kanbanService
    ) {
    }

    public function index(
        Request $request
    ) {
        $user = $request->user();

        $preference =
            LeadKanbanPreference::forUser(
                $user
            );

        $groupBy = $request->query(
            'group_by',
            $preference->group_by
        );

        if (
            !in_array(
                $groupBy,
                [
                    'status',
                    'priority',
                ],
                true
            )
        ) {
            $groupBy = 'status';
        }

        $filters =
            $this->filters(
                $request
            );

        $board =
            $this->kanbanService
                ->buildBoard(
                    $user,
                    $groupBy,
                    $filters,
                    $preference
                );

        $canViewAll =
            $this->canViewAllLeads(
                $user
            );

        $users = $canViewAll
            ? User::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ])
            : collect();

        return view(
            'lead::kanban.index',
            array_merge(
                $board,
                [
                    'preference' =>
                        $preference,

                    'filters' =>
                        $filters,

                    'users' =>
                        $users,

                    'canViewAll' =>
                        $canViewAll,

                    'statuses' =>
                        Lead::statuses(),

                    'priorities' =>
                        Lead::priorities(),

                    'sources' =>
                        Lead::sources(),

                    'pageTitle' =>
                        'Lead Kanban Board',
                ]
            )
        );
    }

    public function board(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'group_by' => [
                    'nullable',
                    Rule::in([
                        'status',
                        'priority',
                    ]),
                ],

                'search' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'status' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'priority' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'source' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'assigned_to' => [
                    'nullable',
                    'integer',
                ],
            ]);

        $user = $request->user();

        $preference =
            LeadKanbanPreference::forUser(
                $user
            );

        $groupBy =
            $validated['group_by']
            ?? $preference->group_by;

        $board =
            $this->kanbanService
                ->buildBoard(
                    $user,
                    $groupBy,
                    $this->filters(
                        $request
                    ),
                    $preference
                );

        return response()->json([
            'html' => view(
                'lead::kanban.partials.board',
                $board
            )->render(),

            'total' =>
                $board['totalLeads'],

            'updated_at' =>
                now()->format(
                    'h:i:s A'
                ),
        ]);
    }

    public function details(
        Request $request,
        Lead $lead
    ): JsonResponse {
        $this->ensureCanAccessLead(
            $request->user(),
            $lead
        );

        $lead->load([
            'assignedUser:id,name,email',
            'creator:id,name,email',
            'convertedBy:id,name,email',
            'client:id,lead_id,name,status',

            'statusDefinition:id,slug,name,color,is_closed,system_key',

            'priorityDefinition:id,slug,name,color',
        ]);

        $recentFollowUps =
            $lead->followUps()
                ->with(
                    'user:id,name,email'
                )
                ->latest(
                    'followed_up_at'
                )
                ->limit(5)
                ->get();

        return response()->json([
            'html' => view(
                'lead::kanban.partials.drawer',
                [
                    'lead' =>
                        $lead,

                    'recentFollowUps' =>
                        $recentFollowUps,

                    'followUpTypes' =>
                        FollowUp::types(),

                    'followUpOutcomes' =>
                        FollowUp::outcomes(),

                    'sources' =>
                        Lead::sources(),
                ]
            )->render(),
        ]);
    }

    public function move(
        Request $request,
        Lead $lead
    ): JsonResponse {
        $this->ensureCanAccessLead(
            $request->user(),
            $lead
        );

        $validated =
            $request->validate([
                'group_by' => [
                    'required',
                    Rule::in([
                        'status',
                        'priority',
                    ]),
                ],

                'target_column' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'before_id' => [
                    'nullable',
                    'integer',
                ],

                'after_id' => [
                    'nullable',
                    'integer',
                ],

                'expected_version' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ]);

        $updatedLead =
            $this->kanbanService
                ->moveLead(
                    $request->user(),
                    $lead,
                    $validated
                );

        return response()->json([
            'message' =>
                'Lead position updated successfully.',

            'lead' =>
                $updatedLead,
        ]);
    }

    public function saveColumnOrder(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'group_by' => [
                    'required',
                    Rule::in([
                        'status',
                        'priority',
                    ]),
                ],

                'columns' => [
                    'required',
                    'array',
                ],

                'columns.*' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ]);

        $order =
            $this->kanbanService
                ->saveColumnOrder(
                    $request->user(),
                    $validated[
                        'group_by'
                    ],
                    $validated[
                        'columns'
                    ]
                );

        return response()->json([
            'message' =>
                'Column order saved successfully.',

            'columns' =>
                $order,
        ]);
    }

    public function savePreference(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'group_by' => [
                    'sometimes',
                    Rule::in([
                        'status',
                        'priority',
                    ]),
                ],

                'hide_empty_columns' => [
                    'sometimes',
                    'boolean',
                ],
            ]);

        $preference =
            $this->kanbanService
                ->savePreference(
                    $request->user(),
                    $validated
                );

        return response()->json([
            'message' =>
                'Kanban preference saved.',

            'preference' => [
                'group_by' =>
                    $preference->group_by,

                'hide_empty_columns' =>
                    $preference
                        ->hide_empty_columns,
            ],
        ]);
    }

    private function filters(
        Request $request
    ): array {
        return [
            'search' => trim(
                (string) $request
                    ->query(
                        'search',
                        ''
                    )
            ),

            'status' => trim(
                (string) $request
                    ->query(
                        'status',
                        ''
                    )
            ),

            'priority' => trim(
                (string) $request
                    ->query(
                        'priority',
                        ''
                    )
            ),

            'source' => trim(
                (string) $request
                    ->query(
                        'source',
                        ''
                    )
            ),

            'assigned_to' =>
                (int) $request
                    ->query(
                        'assigned_to',
                        0
                    ),
        ];
    }
}