<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'   => Customer::factory(),
            'purchase_date' => $this->faker->dateTimeBetween('-60 days', 'now'),
        ];
    }

    /**
     * Adjunta automáticamente entre 1 y 3 medicamentos existentes como order_items.
     * Requiere que ya existan Medication en BD antes de crear Orders.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            Medication::inRandomOrder()->take(rand(1, 3))->get()->each(function ($medication) use ($order) {
                $order->orderItems()->create([
                    'medication_id' => $medication->id,
                    'quantity'      => fake()->numberBetween(1, 3),
                    'unit_price'    => fake()->randomFloat(2, 5, 200),
                ]);
            });
        });
    }

    /**
     * Estado para forzar órdenes dentro del rango de 30 días.
     */
    public function recent(): static
    {
        return $this->state(fn () => [
            'purchase_date' => $this->faker->dateTimeBetween('-25 days', 'now'),
        ]);
    }

    /**
     * Estado para forzar órdenes fuera del rango de 30 días.
     */
    public function old(): static
    {
        return $this->state(fn () => [
            'purchase_date' => $this->faker->dateTimeBetween('-90 days', '-31 days'),
        ]);
    }

    /**
     * Estado para forzar que la orden contenga el lote objetivo (951357).
     */
    public function withTargetLot(): static
    {
        return $this->afterCreating(function (Order $order) {
            $medication = Medication::where('lot_number', '951357')->first()
                ?? Medication::factory()->targetLot()->create();

            $order->orderItems()->create([
                'medication_id' => $medication->id,
                'quantity'      => $this->faker->numberBetween(1, 3),
                'unit_price'    => $this->faker->randomFloat(2, 5, 200),
            ]);
        });
    }
}
