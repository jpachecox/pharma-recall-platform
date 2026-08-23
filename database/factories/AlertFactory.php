<?php

namespace Database\Factories;

use App\Enums\AlertChannel;
use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = Order::factory()->create();
        $status = $this->faker->randomElement(AlertStatus::cases());

        return [
            'customer_id'  => $order->customer_id,
            'order_id'     => $order->id,
            'user_id'      => User::factory(),
            'lot_number'   => strtoupper($this->faker->bothify('LOT-####')),
            'channel'      => $this->faker->randomElement(AlertChannel::cases()),
            'status'       => $status,
            'message_body' => $this->faker->paragraph(),
            'sent_at'      => $status === AlertStatus::SENT
                ? $this->faker->dateTimeThisMonth()
                : null,
        ];
    }

    /**
     * Estado para forzar una alerta ya enviada exitosamente.
     */
    public function sent(): static
    {
        return $this->state(fn () => [
            'status'  => AlertStatus::SENT,
            'sent_at' => $this->faker->dateTimeThisMonth(),
        ]);
    }

    /**
     * Estado para forzar una alerta fallida (nunca debe tener sent_at).
     */
    public function failed(): static
    {
        return $this->state(fn () => [
            'status'  => AlertStatus::FAILED,
            'sent_at' => null,
        ]);
    }

    /**
     * Estado para forzar una alerta pendiente.
     */
    public function pending(): static
    {
        return $this->state(fn () => [
            'status'  => AlertStatus::PENDING,
            'sent_at' => null,
        ]);
    }

    /**
     * Estado para forzar una alerta encolada (aceptada, aún no despachada).
     */
    public function queued(): static
    {
        return $this->state(fn () => [
            'status'  => AlertStatus::QUEUED,
            'sent_at' => null,
        ]);
    }
}
