<?php

namespace App\Modules\Lead\Support;

use App\Modules\Lead\Models\Lead;
use App\Modules\User\Models\User;

trait AuthorizesLeadAccess
{
    protected function canViewAllLeads(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasPermission('leads.view_all');
    }

    protected function canAssignLeads(User $user): bool
    {
        return $this->canViewAllLeads($user)
            && (
                $user->isSuperAdmin()
                || $user->hasPermission('leads.assign')
            );
    }

    protected function ensureCanAccessLead(
        User $user,
        Lead $lead
    ): void {
        if ($this->canViewAllLeads($user)) {
            return;
        }

        if ((int) $lead->assigned_to !== (int) $user->id) {
            abort(
                403,
                'You are not authorized to access this lead.'
            );
        }
    }
}