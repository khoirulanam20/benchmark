<?php

namespace App\Jobs;

use App\Enums\BenchmarkStatus;
use App\Enums\ScoreStatus;
use App\Models\Benchmark;
use App\Services\LlmProviderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunBenchmarkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public Benchmark $benchmark,
    ) {}

    public function handle(LlmProviderService $llmService): void
    {
        $this->benchmark->update([
            'status' => BenchmarkStatus::Processing,
            'started_at' => now(),
        ]);

        $results = $this->benchmark->results()->with('model')->get();
        $hasError = false;

        foreach ($results as $result) {
            try {
                if (! $result->model->hasApiKey()) {
                    $result->update(['error_message' => 'No API key configured for model: '.$result->model->full_name]);
                    $hasError = true;

                    continue;
                }

                $start = microtime(true);
                $response = $llmService->sendPrompt($result->model, $this->benchmark->prompt_text);
                $latency = microtime(true) - $start;

                $cost = ($response['prompt_tokens'] / 1000 * (float) $result->model->input_price_per_1k_tokens)
                    + ($response['completion_tokens'] / 1000 * (float) $result->model->output_price_per_1k_tokens);

                $result->update([
                    'output_content' => $response['output'],
                    'prompt_tokens' => $response['prompt_tokens'],
                    'completion_tokens' => $response['completion_tokens'],
                    'cost' => round($cost, 8),
                    'latency_seconds' => round($latency, 3),
                    'score_status' => ScoreStatus::Pending,
                ]);

                ScoreBenchmarkResultJob::dispatch($result);

            } catch (\Throwable $e) {
                Log::error('Benchmark result failed', ['result_id' => $result->id, 'error' => $e->getMessage()]);
                $result->update(['error_message' => $e->getMessage()]);
                $hasError = true;
            }
        }

        $this->benchmark->update([
            'status' => $hasError ? BenchmarkStatus::Failed : BenchmarkStatus::Completed,
            'completed_at' => now(),
        ]);

        // Track monthly cost
        $totalCost = $this->benchmark->results()->sum('cost');
        if ($totalCost > 0) {
            $this->benchmark->user()->increment('current_month_cost', $totalCost);
        }
    }
}
