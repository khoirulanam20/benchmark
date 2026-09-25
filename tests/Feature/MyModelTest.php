<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_models_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/my-models');
        $response->assertStatus(200);
        $response->assertSee('My Models');
    }

    public function test_user_can_create_model(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/my-models', [
            'model_name' => 'gpt-4o',
            'provider' => 'openai',
            'display_name' => 'My GPT-4o',
            'api_key' => 'sk-test-1234567890abcdef',
            'base_url' => 'https://api.openai.com',
            'input_price_per_1k_tokens' => 0.0025,
            'output_price_per_1k_tokens' => 0.01,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('models', [
            'user_id' => $user->id,
            'model_name' => 'gpt-4o',
            'provider' => 'openai',
            'base_url' => 'https://api.openai.com',
        ]);

        $model = AiModel::where('user_id', $user->id)->first();
        $this->assertTrue($model->hasApiKey());
        $this->assertEquals('sk-test-1234567890abcdef', $model->decrypted_api_key);
    }

    public function test_user_can_update_model(): void
    {
        $user = User::factory()->create();
        $model = AiModel::factory()->forUser($user)->withApiKey('sk-old-key')->create();

        $response = $this->actingAs($user)->put("/my-models/{$model->id}", [
            'display_name' => 'Updated Name',
            'api_key' => 'sk-new-key-1234567890',
            'base_url' => 'https://custom.api.com',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $model->refresh();
        $this->assertEquals('Updated Name', $model->display_name);
        $this->assertEquals('sk-new-key-1234567890', $model->decrypted_api_key);
        $this->assertEquals('https://custom.api.com', $model->base_url);
    }

    public function test_user_can_delete_model(): void
    {
        $user = User::factory()->create();
        $model = AiModel::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->delete("/my-models/{$model->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('models', ['id' => $model->id]);
    }

    public function test_user_cannot_edit_other_users_model(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $model = AiModel::factory()->forUser($other)->create();

        $response = $this->actingAs($user)->get("/my-models/{$model->id}/edit");
        $response->assertStatus(403);
    }

    public function test_user_can_test_saved_model(): void
    {
        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'OK']]],
                'usage' => ['prompt_tokens' => 5, 'completion_tokens' => 1],
            ]),
        ]);

        $user = User::factory()->create();
        $model = AiModel::factory()->forUser($user)->withApiKey()->create([
            'provider' => 'openai',
            'model_name' => 'gpt-4o',
        ]);

        $response = $this->actingAs($user)->postJson("/my-models/{$model->id}/test");
        $response->assertOk();
        $response->assertJson(['ok' => true, 'output' => 'OK']);
    }

    public function test_user_cannot_test_other_users_model(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $model = AiModel::factory()->forUser($other)->withApiKey()->create();

        $response = $this->actingAs($user)->postJson("/my-models/{$model->id}/test");
        $response->assertStatus(403);
    }

    public function test_user_can_test_connection_from_payload(): void
    {
        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'OK']]],
                'usage' => ['prompt_tokens' => 3, 'completion_tokens' => 1],
            ]),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/my-models/test-connection', [
            'model_name' => 'gpt-4o',
            'provider' => 'openai',
            'api_key' => 'sk-test-1234567890abcdef',
            'base_url' => 'https://api.openai.com',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
    }

    public function test_user_can_create_model_with_custom_base_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/my-models', [
            'model_name' => 'llama-3-70b',
            'provider' => 'openai_compatible',
            'api_key' => 'my-proxy-key-12345',
            'base_url' => 'http://localhost:8080',
            'input_price_per_1k_tokens' => 0,
            'output_price_per_1k_tokens' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('models', [
            'user_id' => $user->id,
            'model_name' => 'llama-3-70b',
            'provider' => 'openai_compatible',
            'base_url' => 'http://localhost:8080',
        ]);
    }
}
