<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'date_of_birth' => fake()->optional(0.9)->dateTimeBetween('-90 years', '-5 years'),
            'phone' => fake()->optional(0.8)->numerify('06########'),
            'email' => fake()->optional(0.5)->safeEmail(),
        ];
    }
}
