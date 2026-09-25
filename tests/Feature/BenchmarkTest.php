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
        AiModel::factory()->create();

        $response = $this->actingAs($user)->get('/benchmarks/create');
        $response->assertStatus(200);
        $response->assertSee('Create New Benchmark');
    }

    public function test_user_can_submit_benchmark(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $model = AiModel::factory()->create();

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
        $response->assertJsonStructure(['status', 'results']);
    }
}
