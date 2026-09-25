<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_model_presets(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/models');
        $response->assertStatus(200);
        $response->assertSee('Model Presets');
    }

    public function test_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/models');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_model(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/models', [
            'model_name' => 'test-model',
            'provider' => 'openai',
            'display_name' => 'Test Model',
            'input_price_per_1k_tokens' => 0.001,
            'output_price_per_1k_tokens' => 0.002,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('models', ['model_name' => 'test-model', 'provider' => 'openai']);
    }

    public function test_admin_can_update_preset_credentials(): void
    {
        $admin = User::factory()->admin()->create();
        $model = AiModel::factory()->create(['user_id' => null]);

        $response = $this->actingAs($admin)->put("/admin/models/{$model->id}", [
            'display_name' => 'Preset With Key',
            'base_url' => 'http://localhost:11434',
            'api_key' => 'ollama-test-key-12345',
            'input_price_per_1k_tokens' => 0,
            'output_price_per_1k_tokens' => 0,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $model->refresh();
        $this->assertTrue($model->hasApiKey());
        $this->assertEquals('http://localhost:11434', $model->base_url);
        $this->assertEquals('Preset With Key', $model->display_name);
    }

    public function test_admin_can_test_preset_model(): void
    {
        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'OK']]],
                'usage' => ['prompt_tokens' => 2, 'completion_tokens' => 1],
            ]),
        ]);

        $admin = User::factory()->admin()->create();
        $model = AiModel::factory()->withApiKey()->create([
            'user_id' => null,
            'provider' => 'openai_compatible',
            'model_name' => 'llama3',
            'base_url' => 'http://localhost:11434',
        ]);

        $response = $this->actingAs($admin)->postJson("/admin/models/{$model->id}/test");
        $response->assertOk();
        $response->assertJson(['ok' => true, 'output' => 'OK']);
    }

    public function test_admin_can_update_pricing(): void
    {
        $admin = User::factory()->admin()->create();
        $model = AiModel::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/pricing/{$model->id}", [
            'input_price_per_1k_tokens' => 0.005,
            'output_price_per_1k_tokens' => 0.01,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('models', ['id' => $model->id, 'input_price_per_1k_tokens' => 0.005]);
    }

    public function test_admin_can_view_pricing_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/pricing');
        $response->assertStatus(200);
        $response->assertSee('Token Pricing Management');
    }

    public function test_admin_can_update_scoring_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put('/admin/settings', [
            'typesafe_api_key' => 'ts-test-key-1234567890',
            'typesafe_model' => 'jev-latest',
            'typesafe_base_url' => 'https://api.typesafe.ai',
        ]);

        $response->assertRedirect();
        $this->assertTrue(AppSetting::hasSecret('typesafe_api_key'));
        $this->assertSame('jev-latest', AppSetting::get('typesafe_model'));
    }

    public function test_admin_can_view_score_validation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/scores');
        $response->assertStatus(200);
        $response->assertSee('Score Validation');
    }
}
