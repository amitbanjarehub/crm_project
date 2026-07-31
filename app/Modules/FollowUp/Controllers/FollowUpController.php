<?php

namespace App\Modules\FollowUp\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Support\AuthorizesLeadAccess;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
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
        $canViewAll = $this->canViewAllFollowUps($loggedInUser);

        $search = trim($request->query('search', ''));
        $due = trim($request->query('due', 'all'));
        $type = trim($request->query('type', ''));
        $assignedTo = (int) $request->query('assigned_to', 0);

        $query = FollowUp::query()
            ->with([
                'lead:id,name,phone,company,assigned_to,status',
                'lead.assignedUser:id,name,email',
                'user:id,name,email',
            ])
            ->latest('followed_up_at');

        if (!$canViewAll) {
            $query->whereHas('lead', function ($q) use ($loggedInUser) {
                $q->where('assigned_to', $loggedInUser->id);
            });
        }

        if ($search !== '') {
            $query->whereHas('lead', function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%')
                    ->orWhere('company', 'LIKE', '%' . $search . '%');

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        if (
            $type !== ''
            && array_key_exists($type, FollowUp::types())
        ) {
            $query->where('type', $type);
        }

        switch ($due) {
            case 'today':
                $query->whereDate(
                    'next_follow_up_at',
                    today()
                );
                break;

            case 'overdue':
                $query->whereNotNull('next_follow_up_at')
                    ->where('next_follow_up_at', '<', now());
                break;

            case 'upcoming':
                $query->whereNotNull('next_follow_up_at')
                    ->where('next_follow_up_at', '>=', now());
                break;

            case 'no_schedule':
                $query->whereNull('next_follow_up_at');
                break;
        }

        if ($canViewAll && $assignedTo > 0) {
            $query->whereHas('lead', function ($q) use ($assignedTo) {
                $q->where('assigned_to', $assignedTo);
            });
        }

        $followUps = $query
            ->paginate($perPage)
            ->withQueryString();

        $users = $canViewAll
            ? User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
            : collect();

        return view('followup::index', [
            'followUps' => $followUps,
            'users' => $users,
            'perPage' => $perPage,
            'search' => $search,
            'due' => $due,
            'type' => $type,
            'assignedTo' => $assignedTo,
            'canViewAll' => $canViewAll,
            'types' => FollowUp::types(),
            'outcomes' => FollowUp::outcomes(),
            'pageTitle' => 'Follow-up Management',
        ]);
    }

    public function create(
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
                    'Converted lead par new follow-up add nahi kiya ja sakta.'
                );
        }

        return view('followup::create', [
            'lead' => $lead,
            'types' => FollowUp::types(),
            'outcomes' => FollowUp::outcomes(),
            'pageTitle' => 'Add Follow-up',
        ]);
    }

    public function store(
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
                    'Converted lead par new follow-up add nahi kiya ja sakta.'
                );
        }

        $validated = $request->validate(
            $this->validationRules()
        );

        // DB::transaction(function () use (
        //     $request,
        //     $lead,
        //     $validated
        // ) {
        //     $lead->followUps()->create([
        //         'user_id' => $request->user()->id,
        //         'type' => $validated['type'],
        //         'followed_up_at' => $validated['followed_up_at'],
        //         'outcome' => $validated['outcome'],
        //         'notes' => $validated['notes'],
        //         'next_follow_up_at' =>
        //             $validated['next_follow_up_at'] ?? null,
        //     ]);

        //     $lead->update([
        //         'next_follow_up_at' =>
        //             $validated['next_follow_up_at'] ?? null,
        //     ]);
        // });

        DB::transaction(function () use ($request, $lead, $validated) {
            $lead->followUps()->create([
                'user_id' =>
                    $request->user()->id,

                'type' =>
                    $validated['type'],

                'followed_up_at' =>
                    $validated['followed_up_at'],

                'outcome' =>
                    $validated['outcome'],

                'notes' =>
                    $validated['notes'],

                'next_follow_up_at' =>
                    $validated[
                        'next_follow_up_at'
                    ] ?? null,
            ]);

            $this->syncLeadNextFollowUp(
                $lead
            );
        });

        return redirect()
            ->route('lead.show', $lead->id)
            ->with('success', 'Follow-up added successfully.');
    }

    public function edit(
        Request $request,
        FollowUp $followUp
    ) {
        $followUp->load('lead');

        $this->ensureCanModifyFollowUp(
            $request->user(),
            $followUp
        );

        return view('followup::edit', [
            'followUp' => $followUp,
            'lead' => $followUp->lead,
            'types' => FollowUp::types(),
            'outcomes' => FollowUp::outcomes(),
            'pageTitle' => 'Edit Follow-up',
        ]);
    }

    public function update(
        Request $request,
        FollowUp $followUp
    ) {
        $followUp->load('lead');

        $this->ensureCanModifyFollowUp(
            $request->user(),
            $followUp
        );

        $validated = $request->validate(
            $this->validationRules()
        );

        DB::transaction(function () use ($followUp, $validated) {
            $followUp->update([
                'type' => $validated['type'],
                'followed_up_at' => $validated['followed_up_at'],
                'outcome' => $validated['outcome'],
                'notes' => $validated['notes'],
                'next_follow_up_at' =>
                    $validated['next_follow_up_at'] ?? null,
            ]);

            $this->syncLeadNextFollowUp(
                $followUp->lead
            );
        });

        return redirect()
            ->route('lead.show', $followUp->lead_id)
            ->with('success', 'Follow-up updated successfully.');
    }

    public function destroy(
        Request $request,
        FollowUp $followUp
    ) {
        $followUp->load('lead');

        $this->ensureCanModifyFollowUp(
            $request->user(),
            $followUp
        );

        $lead = $followUp->lead;

        DB::transaction(function () use ($followUp, $lead) {
            $followUp->delete();

            $this->syncLeadNextFollowUp($lead);
        });

        return redirect()
            ->route('lead.show', $lead->id)
            ->with('success', 'Follow-up deleted successfully.');
    }

    private function validationRules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in(array_keys(FollowUp::types())),
            ],

            'followed_up_at' => [
                'required',
                'date',
            ],

            'outcome' => [
                'required',
                Rule::in(array_keys(FollowUp::outcomes())),
            ],

            'notes' => [
                'required',
                'string',
                'max:5000',
            ],

            'next_follow_up_at' => [
                'nullable',
                'date',
                'after_or_equal:followed_up_at',
            ],
        ];
    }

    private function canViewAllFollowUps(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission('follow_ups.view_all');
    }

    private function ensureCanModifyFollowUp(
        User $user,
        FollowUp $followUp
    ): void {
        if ($this->canViewAllFollowUps($user)) {
            return;
        }

        $this->ensureCanAccessLead(
            $user,
            $followUp->lead
        );

        if ((int) $followUp->user_id !== (int) $user->id) {
            abort(
                403,
                'You cannot modify another user follow-up.'
            );
        }
    }

    private function syncLeadNextFollowUp(
        Lead $lead
    ): void {
        /*
         * Latest history record ki schedule value
         * Lead table ke summary field me rahegi.
         */
        // $latestFollowUp = FollowUp::query()
        //     ->where('lead_id', $lead->id)
        //     ->latest('id')
        //     ->first();

        $latestFollowUp = FollowUp::query()
            ->where(
                'lead_id',
                $lead->id
            )
            ->orderByDesc(
                'followed_up_at'
            )
            ->orderByDesc('id')
            ->first();

        $lead->update([
            'next_follow_up_at' =>
                $latestFollowUp?->next_follow_up_at,
        ]);
    }
}