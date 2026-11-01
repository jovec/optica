<?php

namespace Database\Factories;

use App\Models\Examination;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Examination>
 */
class ExaminationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sphere = $this->diopter(-8, 4);

        return [
            'patient_id' => Patient::factory(),
            'organization_id' => fn (array $attributes) => Patient::withoutGlobalScopes()
                ->whereKey($attributes['patient_id'])
                ->value('organization_id'),
            'examined_at' => fake()->dateTimeBetween('-5 years'),
            ...$this->eye('od', $sphere),
            ...$this->eye('os', $sphere),
            'pd' => fake()->numberBetween(108, 144) / 2,
        ];
    }

    /**
     * Add reading power for patients who need help seeing up close.
     */
    public function withAddition(): static
    {
        $addition = $this->diopter(0.75, 3);

        return $this->state([
            'od_addition' => $addition,
            'os_addition' => $addition,
        ]);
    }

    /**
     * Get the values for one eye, close to the other eye's sphere.
     *
     * @return array<string, float|int|null>
     */
    protected function eye(string $eye, float $sphere): array
    {
        $cylinder = fake()->boolean(60) ? $this->diopter(-2.5, -0.25) : null;

        return [
            "{$eye}_sphere" => $sphere + $this->diopter(-0.5, 0.5),
            "{$eye}_cylinder" => $cylinder,
            "{$eye}_axis" => $cylinder ? fake()->numberBetween(1, 36) * 5 : null,
        ];
    }

    /**
     * Get a random diopter value in steps of 0.25.
     */
    protected function diopter(float $min, float $max): float
    {
        return fake()->numberBetween((int) ($min * 4), (int) ($max * 4)) / 4;
    }
}
