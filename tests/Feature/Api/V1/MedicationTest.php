<?php

namespace Tests\Feature\Api\V1;

use App\Models\Medication;
use App\Models\User;
use Tests\TestCase;

class MedicationTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user, 'sanctum');
    }

    public function test_allows_searching_medications_by_lot_number(): void
    {
        Medication::factory()->create([
            'lot_number' => 'LOT-1234',
        ]);

        $response = $this->getJson(
            '/api/v1/medications/search?lot=LOT-1234'
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }
}