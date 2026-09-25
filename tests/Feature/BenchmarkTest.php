<?php

namespace Tests\Feature;

use App\Jobs\RunBenchmarkJob;
use App\Models\AiModel;
use App\Models\Benchmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_benchmark_page_is_displayed(): void
    {
        $user = User::factory()->create();
        AiModel::factory()->withApiKey()->create();

        $response = $this->actingAs($user)->get('/benchmarks/create');
        $response->assertStatus(200);
        $response->assertSee('Create New Benchmark');
    }

    public function test_user_can_submit_benchmark(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $model = AiModel::factory()->forUser($user)->withApiKey()->create();

        $response = $this->actingAs($user)->post('/benchmarks', [
            'title' => 'Test Benchmark',
            'prompt_text' => 'What is 2+2?',
            'model_ids' => [$model->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('benchmarks', [
            'user_id' => $user->id,
            'title' => 'Test Benchmark',
            'prompt_text' => 'What is 2+2?',
        ]);

        Queue::assertPushed(RunBenchmarkJob::class);
    }

    public function test_user_can_view_benchmark_history(): void
    {
        $user = User::factory()->create();
        Benchmark::factory()->for($user)->create(['title' => 'History Item']);

        $response = $this->actingAs($user)->get('/benchmarks');
        $response->assertStatus(200);
        $response->assertSee('Benchmark History');
        $response->assertSee('History Item');
    }

    public function test_guest_cannot_view_benchmark_history(): void
    {
        $response = $this->get('/benchmarks');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_own_benchmark(): void
    {
        $user = User::factory()->create();
        $benchmark = Benchmark::factory()->for($user)->create(['status' => 'completed']);

        $response = $this->actingAs($user)->get("/benchmarks/{$benchmark->id}");
        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_benchmark(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $benchmark = Benchmark::factory()->for($other)->create();

        $response = $this->actingAs($user)->get("/benchmarks/{$benchmark->id}");
        $response->assertStatus(403);
    }

    public function test_benchmark_status_endpoint(): void
    {
        $user = User::factory()->create();
        $benchmark = Benchmark::factory()->for($user)->create(['status' => 'processing']);

        $response = $this->actingAs($user)->getJson("/benchmarks/{$benchmark->id}/status");
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'status_label', 'finished', 'scoring_complete', 'results']);
    }

    public function test_user_cannot_submit_benchmark_without_api_key(): void
    {
        $user = User::factory()->create();
        $model = AiModel::factory()->forUser($user)->create(); // no API key

        $response = $this->actingAs($user)->post('/benchmarks', [
            'title' => 'Test',
            'prompt_text' => 'Hello',
            'model_ids' => [$model->id],
        ]);

        $response->assertSessionHasErrors('model_ids');
    }
}
