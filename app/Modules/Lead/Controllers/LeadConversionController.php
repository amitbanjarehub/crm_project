<?php

namespace App\Modules\Lead\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Client\Models\Client;
use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Support\AuthorizesLeadAccess;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Lead\Models\LeadStatus;
use App\Support\LeadReturnUrl;

class LeadConversionController extends Controller
{
    use AuthorizesLeadAccess;

    public function store(
        Request $request,
        Lead $lead
    ) {

        $returnUrl = LeadReturnUrl::resolve(
            $request,
            route('lead.index')
        );

        $this->ensureCanAccessLead(
            $request->user(),
            $lead
        );

        try {
            $client = DB::transaction(
                function () use ($request, $lead) {
                    /*
                     * Same lead par simultaneous double conversion
                     * ko prevent karega.
                     */
                    $lockedLead = Lead::query()
                        ->whereKey($lead->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $convertedStatus =
                        LeadStatus::systemSlug(
                            'converted'
                        );

                    if (!$convertedStatus) {
                        throw new DomainException(
                            'Converted Lead status CRM Settings me configured nahi hai.'
                        );
                    }

                    $this->ensureCanAccessLead(
                        $request->user(),
                        $lockedLead
                    );

                    if ($lockedLead->client()->exists()) {
                        throw new DomainException(
                            'This lead is already converted into a client.'
                        );
                    }

                    $client = Client::create([
                        'lead_id' => $lockedLead->id,
                        'name' => $lockedLead->name,
                        'phone' => $lockedLead->phone,
                        'email' => $lockedLead->email,
                        'company' => $lockedLead->company,
                        'status' => 'active',
                        'assigned_to' => $lockedLead->assigned_to,
                        'created_by' => $request->user()->id,
                        'notes' => $lockedLead->notes,
                    ]);

                    $lockedLead->update([
                        'status' => $convertedStatus,
                        'converted_at' => now(),
                        'converted_by' => $request->user()->id,
                        'next_follow_up_at' => null,
                    ]);

                    FollowUp::create([
                        'lead_id' => $lockedLead->id,
                        'user_id' => $request->user()->id,
                        'type' => 'other',
                        'followed_up_at' => now(),
                        'outcome' => 'converted',
                        'notes' => 'Lead converted into client.',
                        'next_follow_up_at' => null,
                    ]);

                    return $client;
                },
                3
            );
        } catch (DomainException $exception) {
            return redirect()
                ->route(
                    'lead.show',
                    [
                        'lead' =>
                            $lead->id,

                        'return_url' =>
                            $returnUrl,
                    ]
                )
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }

        if ($request->user()->hasPermission('clients.view')) {
            return redirect()
                ->route(
                    'client.show',
                    [
                        'client' =>
                            $client->id,

                        'return_url' =>
                            $returnUrl,
                    ]
                )
                ->with(
                    'success',
                    'Lead converted into client successfully.'
                );
        }

        return redirect()
            ->route(
                'lead.show',
                [
                    'lead' =>
                        $lead->id,

                    'return_url' =>
                        $returnUrl,
                ]
            )
            ->with(
                'success',
                'Lead converted into client successfully.'
            );
    }
}