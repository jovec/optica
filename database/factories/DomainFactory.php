<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'domain' => fake()->unique()->domainWord().'.localhost',
            'is_primary' => false,
        ];
    }
}
