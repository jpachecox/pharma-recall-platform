<?php

namespace Tests\Feature\Api\V1;

use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user, 'sanctum');
    }

    public function test_returns_the_details_of_an_existing_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson(
            "/api/v1/customers/{$customer->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.id', $customer->id);
    }

    public function test_returns_404_when_customer_does_not_exist(): void
    {
        $response = $this->getJson(
            '/api/v1/customers/99999'
        );

        $response->assertStatus(404);
    }

    public function test_returns_401_when_user_is_not_authenticated(): void
    {
        $this->app['auth']->forgetGuards();

        $customer = Customer::factory()->create();

        $response = $this->getJson(
            "/api/v1/customers/{$customer->id}"
        );

        $response->assertStatus(401);
    }
}