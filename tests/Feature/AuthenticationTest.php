<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_via_api_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@cargotrace.com',
            'password' => bcrypt('password123'),
            'role' => 'operator',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'operator@cargotrace.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['user', 'token', 'token_type']
            ]);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@cargotrace.com',
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'operator@cargotrace.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_unauthenticated_user_cannot_access_protected_shipments_api(): void
    {
        $response = $this->getJson('/api/shipments');

        $response->assertStatus(401);
    }
}
