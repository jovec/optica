<?php

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $clearVision = $this->createOrganization('Clear Vision Optics', 'clearvision');
        $brightEyes = $this->createOrganization('Bright Eyes Optical', 'brighteyes');

        $this->addUser($clearVision, 'Sarah Mitchell', 'owner@clearvision.test', OrganizationRole::Owner);
        $this->addUser($clearVision, 'James Carter', 'doctor@clearvision.test', OrganizationRole::Doctor);
        $this->addUser($clearVision, 'Emily Davis', 'staff@clearvision.test', OrganizationRole::Staff);
        $this->addUser($brightEyes, 'Michael Brown', 'owner@brighteyes.test', OrganizationRole::Owner);

        Patient::factory()->count(30)->for($clearVision)->create();
        Patient::factory()->count(30)->for($brightEyes)->create();
    }

    /**
     * Create an organization reachable at "{slug}.localhost".
     */
    protected function createOrganization(string $name, string $slug): Organization
    {
        $organization = Organization::factory()->create([
            'name' => $name,
            'slug' => $slug,
        ]);

        $organization->domains()->create([
            'domain' => $slug.'.localhost',
            'is_primary' => true,
        ]);

        return $organization;
    }

    /**
     * Create a user who works in the organization with the given role.
     */
    protected function addUser(Organization $organization, string $name, string $email, OrganizationRole $role): void
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
        ]);

        $organization->users()->attach($user, ['role' => $role]);
    }
}
