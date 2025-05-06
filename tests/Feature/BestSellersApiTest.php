<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BestSellersApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->token = $user->createToken('test_device_name')->plainTextToken;
    }

    public function test_get_list_works_for_isbn(): void
    {
        $response = $this->getJson('/api/v1/best-sellers?isbn[]=9780385537858', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $this->assertSame(1, $response->json('data.count'));
        $this->assertCount(1, $response->json('data.results'));
        $this->assertSame('INFERNO', $response->json('data.results')[0]['title']);
    }

    public function test_get_list_works_for_author(): void
    {
        $response = $this->getJson('/api/v1/best-sellers?author=Dan%20Brown', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $this->assertSame(5, $response->json('data.count'));
        $this->assertCount(5, $response->json('data.results'));
        $this->assertSame('ANGELS AND DEMONS', $response->json('data.results')[0]['title']);
    }

    public function test_get_list_works_for_title(): void
    {
        $response = $this->getJson('/api/v1/best-sellers?title=Angels%20and%20Demons', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $this->assertSame(1, $response->json('data.count'));
        $this->assertCount(1, $response->json('data.results'));
        $this->assertSame('ANGELS AND DEMONS', $response->json('data.results')[0]['title']);
    }
}
