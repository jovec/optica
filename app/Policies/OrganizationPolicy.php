<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine whether the user can change the organization's details.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->hasRoleIn($organization->id, OrganizationRole::Owner);
    }
}
