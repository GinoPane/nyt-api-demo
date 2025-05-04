<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($this->password),
        ]);
    }

    public function test_user_can_get_token_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => $this->password,
            'device_name' => 'test_device',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);

        // Verify the token is actually stored in the database
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_user_cannot_get_token_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => 'wrong_password',
            'device_name' => 'test_device',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_is_throttled_on_too_many_attempts(): void
    {
        $throttleLimit = (int) config('auth.throttle_login_limit') - 1;

        for ($i = 0; $i < $throttleLimit; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => $this->user->email,
                'password' => 'wrong_password',
                'device_name' => 'test_device',
            ]);

            $response->assertStatus(422);
        }

        $response = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => 'wrong_password',
            'device_name' => 'test_device',
        ]);

        $response->assertStatus(429);
    }

    public function test_protected_route_is_accessible_with_valid_token(): void
    {
        // First generate a token
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => $this->password,
            'device_name' => 'test_device',
        ]);

        $token = $loginResponse->json('token');

        // Use the token to access a protected route
        $response = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
        ]);
    }

    public function test_protected_route_is_not_accessible_with_invalid_token(): void
    {
        // Try with invalid token
        $response = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer invalid_token',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_revoke_token(): void
    {
        $response = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer invalid_token',
        ]);

        $response->assertStatus(401);

        // First generate a token
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => $this->password,
            'device_name' => 'test_device',
        ]);

        $token = $loginResponse->json('token');

        $response = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer invalid_token',
        ]);

        $response->assertStatus(401);

        // Use the token to logout (revoke token)
        $response = $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        // Check the database to ensure the token is removed
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Remove any authentication explicitly for tests
        $this->app['auth']->forgetGuards();

        // Verify the token is revoked by trying to use it again
        $userResponse = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $userResponse->assertStatus(401);
    }
}
