<?php

namespace App\Modules\Lead\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lead\Models\Lead;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Modules\Lead\Support\AuthorizesLeadAccess;
use App\Modules\Lead\Models\LeadPriority;
use App\Modules\Lead\Models\LeadStatus;

class LeadController extends Controller
{
    use AuthorizesLeadAccess;
    public function index(Request $request)
    {
        $allowedPerPage = [10, 25, 50, 100];

        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $loggedInUser = $request->user();

        $canViewAll = $this->canViewAllLeads($loggedInUser);

        $search = trim($request->query('search', ''));
        $status = trim($request->query('status', ''));
        $priority = trim($request->query('priority', ''));
        $source = trim($request->query('source', ''));
        $assignedTo = (int) $request->query('assigned_to', 0);

        $query = Lead::query()
            ->with([
                'assignedUser:id,name,email',
                'creator:id,name,email',
                'client:id,lead_id',
                'statusDefinition:id,slug,name,color,is_closed,system_key',
                'priorityDefinition:id,slug,name,color'
            ])
            ->latest();

        /*
         * Normal user ko sirf assigned leads milengi.
         */
        if (!$canViewAll) {
            $query->where(
                'assigned_to',
                $loggedInUser->id
            );
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('company', 'LIKE', '%' . $search . '%');

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        if (
            $status !== '' &&
            array_key_exists($status, Lead::statuses())
        ) {
            $query->where('status', $status);
        }

        if (
            $priority !== '' &&
            array_key_exists($priority, Lead::priorities())
        ) {
            $query->where('priority', $priority);
        }

        if (
            $source !== '' &&
            array_key_exists($source, Lead::sources())
        ) {
            $query->where('source', $source);
        }

        if ($canViewAll && $assignedTo > 0) {
            $query->where('assigned_to', $assignedTo);
        }

        // $leads = $query
        //     ->paginate($perPage)
        //     ->appends($request->query());

        $leads = $query
            ->paginate($perPage)
            ->withQueryString();

        $users = collect();

        if ($canViewAll) {
            $users = User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ]);
        }

        return view('lead::index', [
            'leads' => $leads,
            'users' => $users,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'priority' => $priority,
            'source' => $source,
            'assignedTo' => $assignedTo,
            'canViewAll' => $canViewAll,
            'statuses' => Lead::statuses(),
            'editableStatuses' => Lead::editableStatuses(),
            'priorities' => Lead::priorities(),
            'sources' => Lead::sources(),
            'pageTitle' => 'Lead Management',
        ]);
    }

    public function create(Request $request)
    {
        $canAssign = $this->canAssignLeads(
            $request->user()
        );

        $users = $canAssign
            ? $this->getAssignableUsers()
            : collect();

        // return view('lead::create', [
        //     'users' => $users,
        //     'canAssign' => $canAssign,
        //     // 'statuses' => Lead::statuses(),
        //     'statuses' => Lead::editableStatuses(),
        //     'priorities' => Lead::priorities(),
        //     'sources' => Lead::sources(),
        //     'pageTitle' => 'Add Lead',
        // ]);

        return view('lead::create', [
            'users' => $users,

            'canAssign' => $canAssign,

            'statuses' =>
                Lead::editableStatuses(),

            'priorities' =>
                Lead::activePriorities(),

            'defaultStatus' =>
                Lead::defaultStatus(),

            'defaultPriority' =>
                Lead::defaultPriority(),

            'sources' =>
                Lead::sources(),

            'pageTitle' =>
                'Add Lead',
        ]);
    }

    public function store(Request $request)
    {
        $canAssign = $this->canAssignLeads(
            $request->user()
        );

        $validated = $request->validate(
            $this->validationRules($canAssign)
        );

        /*
         * Assign permission nahi hai to lead
         * automatically creator ko assign hogi.
         */
        $assignedTo = $canAssign
            ? ($validated['assigned_to'] ?? null)
            : $request->user()->id;

        $statusDefinition =
            LeadStatus::query()
                ->where(
                    'slug',
                    $validated['status']
                )
                ->firstOrFail();

        $nextFollowUpAt =
            $statusDefinition->is_closed
            ? null
            : (
                $validated[
                    'next_follow_up_at'
                ] ?? null
            );

        Lead::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'company' => $validated['company'] ?? null,
            'source' => $validated['source'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'assigned_to' => $assignedTo,
            'created_by' => $request->user()->id,
            // 'next_follow_up_at' =>
            //     $validated['next_follow_up_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'next_follow_up_at' =>
                $nextFollowUpAt,
        ]);

        return redirect()
            ->route('lead.index')
            ->with('success', 'Lead added successfully.');
    }

    public function show(
        Request $request,
        Lead $lead
    ) {
        $this->ensureCanAccessLead(
            $request->user(),
            $lead
        );

        $lead->load([
            'assignedUser:id,name,email',
            'creator:id,name,email',
            'convertedBy:id,name,email',
            'client:id,lead_id,name,status',
        ]);

        $followUps = $lead->followUps()
            ->with('user:id,name,email')
            ->latest('followed_up_at')
            ->paginate(10)
            ->withQueryString();

        return view('lead::show', [
            'lead' => $lead,
            'followUps' => $followUps,
            'statuses' => Lead::statuses(),
            'priorities' => Lead::priorities(),
            'sources' => Lead::sources(),
            'followUpTypes' =>
                \App\Modules\FollowUp\Models\FollowUp::types(),
            'followUpOutcomes' =>
                \App\Modules\FollowUp\Models\FollowUp::outcomes(),
            'pageTitle' => 'Lead Details',
        ]);
    }

    public function edit(Request $request, Lead $lead)
    {
        $this->ensureCanAccessLead(
            $request->user(),
            $lead
        );

        if ($lead->isConverted()) {
            return redirect()
                ->route('lead.show', $lead->id)
                ->with(
                    'error',
                    'Converted lead ko Lead module se edit nahi kiya ja sakta. Client record edit karein.'
                );
        }

        $canAssign = $this->canAssignLeads(
            $request->user()
        );

        $users = $canAssign
            ? $this->getAssignableUsers($lead)
            : collect();

        $statuses =
            Lead::editableStatuses();

        if (
            !array_key_exists(
                $lead->status,
                $statuses
            )
        ) {
            $currentStatus =
                LeadStatus::query()
                    ->where(
                        'slug',
                        $lead->status
                    )
                    ->value('name');

            if ($currentStatus) {
                $statuses[
                    $lead->status
                ] = $currentStatus;
            }
        }

        $priorities =
            Lead::activePriorities();

        if (
            !array_key_exists(
                $lead->priority,
                $priorities
            )
        ) {
            $currentPriority =
                LeadPriority::query()
                    ->where(
                        'slug',
                        $lead->priority
                    )
                    ->value('name');

            if ($currentPriority) {
                $priorities[
                    $lead->priority
                ] = $currentPriority;
            }
        }

        return view('lead::edit', [
            'lead' => $lead,
            'users' => $users,
            'canAssign' => $canAssign,
            // 'statuses' => Lead::statuses(),
            'statuses' => Lead::editableStatuses(),
            'priorities' => Lead::priorities(),
            'defaultStatus' =>
                Lead::defaultStatus(),

            'defaultPriority' =>
                Lead::defaultPriority(),
            'sources' => Lead::sources(),
            'pageTitle' => 'Edit Lead',
        ]);
    }

    public function update(
        Request $request,
        Lead $lead
    ) {
        $this->ensureCanAccessLead(
            $request->user(),
            $lead
        );

        if ($lead->isConverted()) {
            return redirect()
                ->route('lead.show', $lead->id)
                ->with(
                    'error',
                    'Converted lead ko Lead module se edit nahi kiya ja sakta. Client record edit karein.'
                );
        }

        $canAssign = $this->canAssignLeads(
            $request->user()
        );

        $validated = $request->validate(
            $this->validationRules($canAssign, $lead)
        );

        /*
         * Assign permission na hone par current
         * assignment change nahi hoga.
         */
        $assignedTo = $lead->assigned_to;

        if ($canAssign) {
            $assignedTo =
                $validated['assigned_to'] ?? null;
        }

        $statusDefinition =
            LeadStatus::query()
                ->where(
                    'slug',
                    $validated['status']
                )
                ->firstOrFail();

        $nextFollowUpAt =
            $statusDefinition->is_closed
            ? null
            : (
                $validated[
                    'next_follow_up_at'
                ] ?? null
            );

        $lead->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'company' => $validated['company'] ?? null,
            'source' => $validated['source'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'assigned_to' => $assignedTo,
            // 'next_follow_up_at' =>
            //     $validated['next_follow_up_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'next_follow_up_at' =>
                $nextFollowUpAt,
        ]);

        return redirect()
            ->route('lead.index')
            ->with('success', 'Lead updated successfully.');
    }

    public function updateStatus(
        Request $request,
        Lead $lead
    ) {
        $this->ensureCanAccessLead(
            $request->user(),
            $lead
        );

        if ($lead->isConverted()) {
            return redirect()
                ->route('lead.index')
                ->with(
                    'error',
                    'Converted lead status manually change nahi kiya ja sakta.'
                );
        }

        $validated = $request->validate([
            'status' => [
                'required',

                Rule::in(
                    array_keys(
                        Lead::editableStatuses()
                    )
                ),
            ],
        ]);

        $statusDefinition =
            LeadStatus::query()
                ->where(
                    'slug',
                    $validated['status']
                )
                ->firstOrFail();

        $lead->update([
            'status' =>
                $validated['status'],

            'next_follow_up_at' =>
                $statusDefinition->is_closed
                ? null
                : $lead->next_follow_up_at,
        ]);



        return redirect()
            ->route('lead.index')
            ->with('success', 'Lead status updated successfully.');
    }

    public function destroy(
        Request $request,
        Lead $lead
    ) {
        $this->ensureCanAccessLead(
            $request->user(),
            $lead
        );

        if ($lead->isConverted()) {
            return redirect()
                ->route('lead.index')
                ->with(
                    'error',
                    'Converted lead cannot be deleted.'
                );
        }

        $lead->delete();

        return redirect()
            ->route('lead.index')
            ->with('success', 'Lead deleted successfully.');
    }

    private function validationRules(
        bool $canAssign,
        ?Lead $lead = null
    ): array {

        $allowedStatuses =
            array_keys(
                Lead::editableStatuses()
            );

        $allowedPriorities =
            array_keys(
                Lead::activePriorities()
            );

        /*
         * Existing inactive value edit form submit
         * karte waqt preserve ho sake.
         */
        if (
            $lead
            && !$lead->isConverted()
        ) {
            $allowedStatuses[] =
                $lead->status;

            $allowedPriorities[] =
                $lead->priority;
        }

        $allowedStatuses =
            array_values(
                array_unique(
                    $allowedStatuses
                )
            );

        $allowedPriorities =
            array_values(
                array_unique(
                    $allowedPriorities
                )
            );


        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:25',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'company' => [
                'nullable',
                'string',
                'max:255',
            ],

            'source' => [
                'required',
                Rule::in(
                    array_keys(Lead::sources())
                ),
            ],

            // 'status' => [
            //     'required',
            //     Rule::in(
            //         // array_keys(Lead::statuses())
            //         array_keys(Lead::editableStatuses())
            //     ),
            // ],
            'status' => [
                'required',
                Rule::in(
                    $allowedStatuses
                ),
            ],

            // 'priority' => [
            //     'required',
            //     Rule::in(
            //         array_keys(Lead::priorities())
            //     ),
            // ],

            'priority' => [
                'required',
                Rule::in(
                    $allowedPriorities
                ),
            ],

            'next_follow_up_at' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];

        if ($canAssign) {
            $rules['assigned_to'] = [
                'nullable',
                'integer',
                'exists:users,id',
            ];
        }

        return $rules;
    }

    // private function canViewAllLeads(
    //     User $user
    // ): bool {
    //     return $user->isSuperAdmin() ||
    //         $user->hasPermission('leads.view_all');
    // }

    // private function canAssignLeads(
    //     User $user
    // ): bool {
    //     /*
    //      * Assign permission ke saath view_all
    //      * hona bhi zaroori rakha gaya hai.
    //      */
    //     return $this->canViewAllLeads($user) &&
    //         (
    //             $user->isSuperAdmin() ||
    //             $user->hasPermission('leads.assign')
    //         );
    // }

    // private function ensureCanAccessLead(
    //     User $user,
    //     Lead $lead
    // ): void {
    //     if ($this->canViewAllLeads($user)) {
    //         return;
    //     }

    //     if (
    //         (int) $lead->assigned_to !==
    //         (int) $user->id
    //     ) {
    //         abort(
    //             403,
    //             'You are not authorized to access this lead.'
    //         );
    //     }
    // }

    private function getAssignableUsers(
        ?Lead $lead = null
    ) {
        return User::query()
            ->where(function ($query) use ($lead) {
                $query->where('is_active', true);

                /*
                 * Current assigned user inactive ho gaya ho,
                 * tab bhi edit dropdown me dikhega.
                 */
                if ($lead && $lead->assigned_to) {
                    $query->orWhere(
                        'id',
                        $lead->assigned_to
                    );
                }
            })
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
                'is_active',
            ]);
    }
}