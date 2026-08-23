<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@farmacia.com',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@farmacia.com',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'test@farmacia.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'test@farmacia.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'test@farmacia.com',
            'password' => 'contrasena_incorrecta',
        ]);

        $response->assertStatus(401)
            ->assertJsonMissing(['access_token']);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Sesión cerrada correctamente.']);
    }
}
