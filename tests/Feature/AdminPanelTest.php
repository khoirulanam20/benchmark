<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_admin_can_view_score_validation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/scores');
        $response->assertStatus(200);
        $response->assertSee('Score Validation');
    }
}
