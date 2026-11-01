<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Examination;
use App\Models\Organization;
use App\Models\User;

class ExaminationPolicy
{
    /**
     * Determine whether the user can add examinations.
     */
    public function create(User $user): bool
    {
        /** @var Organization $organization */
        $organization = app('currentOrganization');

        return $user->hasRoleIn($organization->id, OrganizationRole::Owner, OrganizationRole::Doctor);
    }

    /**
     * Determine whether the user can edit the examination.
     */
    public function update(User $user, Examination $examination): bool
    {
        return $user->hasRoleIn($examination->organization_id, OrganizationRole::Owner, OrganizationRole::Doctor);
    }
}
