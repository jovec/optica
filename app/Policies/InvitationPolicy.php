<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;

class InvitationPolicy
{
    /**
     * Determine whether the user can see the pending invitations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRoleIn($this->currentOrganization()->id, OrganizationRole::Owner);
    }

    /**
     * Determine whether the user can invite people.
     */
    public function create(User $user): bool
    {
        return $user->hasRoleIn($this->currentOrganization()->id, OrganizationRole::Owner);
    }

    /**
     * Determine whether the user can cancel the invitation.
     */
    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->hasRoleIn($invitation->organization_id, OrganizationRole::Owner);
    }

    /**
     * Get the organization of the current request.
     */
    protected function currentOrganization(): Organization
    {
        return app('currentOrganization');
    }
}
