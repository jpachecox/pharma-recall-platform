<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medication>
 */
class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(2, true) . ' ' . $this->faker->randomElement(['500mg', '100mg', '10mg', '250mg']),
            'description' => $this->faker->sentence(),
            'lot_number'  => strtoupper($this->faker->bothify('LOT-####-??')),
        ];
    }

    public function targetLot(): static
    {
        return $this->state(fn () => ['lot_number' => '951357']);
    }
}
