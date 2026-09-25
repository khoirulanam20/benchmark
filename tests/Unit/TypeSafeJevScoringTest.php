<?php

namespace Tests\Unit;

use App\Models\AiModel;
use App\Models\AppSetting;
use App\Models\Benchmark;
use App\Models\BenchmarkResult;
use App\Models\User;
use App\Services\TypeSafeJevScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TypeSafeJevScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_scores_result_with_jev_api(): void
    {
        AppSetting::set('typesafe_api_key', 'ts-test-key-1234567890');
        AppSetting::set('typesafe_model', 'jev-latest');

        Http::fake([
            'api.typesafe.ai/v1/systemone' => Http::response([
                'model' => 'jev-1.13.0',
                'answers' => [
                    'accuracy' => ['type' => 'score', 'score' => 7.2],
                    'completeness' => ['type' => 'score', 'score' => 6.8],
                    'clarity' => ['type' => 'score', 'score' => 7.0],
                    'relevance' => ['type' => 'score', 'score' => 8.0],
                    'conciseness' => ['type' => 'score', 'score' => 6.5],
                    'overall' => ['type' => 'score', 'score' => 7.4],
                ],
                'usage' => ['input_tokens' => 100, 'output_tokens' => 10],
            ]),
        ]);

        $user = User::factory()->create();
        $benchmark = Benchmark::factory()->for($user)->create(['prompt_text' => 'Hello']);
        $result = BenchmarkResult::create([
            'benchmark_id' => $benchmark->id,
            'model_id' => AiModel::factory()->create()->id,
            'output_content' => 'Hi there',
            'score_status' => 'pending',
        ]);

        $service = app(TypeSafeJevScoringService::class);
        $this->assertTrue($service->score($result));

        $result->refresh();
        $this->assertSame('scored', $result->score_status->value);
        $this->assertGreaterThan(0, $result->quality_score);
    }
}
