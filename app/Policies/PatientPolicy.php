<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can see the list of patients.
     */
    public function viewAny(User $user): bool
    {
        return $user->roleIn($this->currentOrganization()->id) !== null;
    }

    /**
     * Determine whether the user can see the patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        return $user->roleIn($patient->organization_id) !== null;
    }

    /**
     * Determine whether the user can add patients.
     */
    public function create(User $user): bool
    {
        return $this->canManage($user, $this->currentOrganization()->id);
    }

    /**
     * Determine whether the user can edit the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $this->canManage($user, $patient->organization_id);
    }

    /**
     * Determine whether the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $this->canManage($user, $patient->organization_id);
    }

    /**
     * Only owners and doctors can add, edit and delete patients.
     */
    protected function canManage(User $user, int $organizationId): bool
    {
        return in_array($user->roleIn($organizationId), [
            OrganizationRole::Owner,
            OrganizationRole::Doctor,
        ]);
    }

    /**
     * Get the organization of the current request.
     */
    protected function currentOrganization(): Organization
    {
        return app('currentOrganization');
    }
}
