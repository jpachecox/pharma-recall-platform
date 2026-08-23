<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
//use App\Models\Medication;
use App\Models\Order;
use App\Models\User;

class OrderTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);
    }

    public function test_returns_details_of_an_existing_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_returns_error_when_attempting_to_access_a_nonexistent_order(): void
    {
        $response = $this->getJson('/api/v1/orders/99999');

        $response->assertStatus(404);
    }
}
