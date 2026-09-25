<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ApiKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_keys_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/api-keys');
        $response->assertStatus(200);
        $response->assertSee('API Key Management');
    }

    public function test_user_can_store_api_key(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/api-keys', [
            'provider_name' => 'openai',
            'api_key' => 'sk-test-1234567890abcdef',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('api_keys', [
            'user_id' => $user->id,
            'provider_name' => 'openai',
        ]);

        $apiKey = ApiKey::where('user_id', $user->id)->first();
        $this->assertEquals('sk-test-1234567890abcdef', Crypt::decryptString($apiKey->encrypted_key));
    }

    public function test_user_can_delete_api_key(): void
    {
        $user = User::factory()->create();
        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'provider_name' => 'openai',
            'encrypted_key' => Crypt::encryptString('sk-test'),
        ]);

        $response = $this->actingAs($user)->delete("/api-keys/{$apiKey->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('api_keys', ['id' => $apiKey->id]);
    }

    public function test_user_cannot_delete_other_users_key(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $apiKey = ApiKey::create([
            'user_id' => $other->id,
            'provider_name' => 'openai',
            'encrypted_key' => Crypt::encryptString('sk-test'),
        ]);

        $response = $this->actingAs($user)->delete("/api-keys/{$apiKey->id}");
        $response->assertStatus(403);
    }
}
