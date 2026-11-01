<?php

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use App\Models\Examination;
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
        $doctor = $this->addUser($clearVision, 'James Carter', 'doctor@clearvision.test', OrganizationRole::Doctor);
        $this->addUser($clearVision, 'Emily Davis', 'staff@clearvision.test', OrganizationRole::Staff);
        $owner = $this->addUser($brightEyes, 'Michael Brown', 'owner@brighteyes.test', OrganizationRole::Owner);

        $this->createPatients($clearVision, $doctor);
        $this->createPatients($brightEyes, $owner);
    }

    /**
     * Create 30 patients, each with up to 3 examinations by the given examiner.
     */
    protected function createPatients(Organization $organization, User $examiner): void
    {
        $patients = Patient::factory()->count(30)->for($organization)->create();

        foreach ($patients as $patient) {
            $needsAddition = $patient->date_of_birth?->age >= 40;

            Examination::factory()
                ->count(fake()->numberBetween(0, 3))
                ->for($patient)
                ->for($examiner, 'examiner')
                ->when($needsAddition, fn ($factory) => $factory->withAddition())
                ->create();
        }
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
    protected function addUser(Organization $organization, string $name, string $email, OrganizationRole $role): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
        ]);

        $organization->users()->attach($user, ['role' => $role]);

        return $user;
    }
}
