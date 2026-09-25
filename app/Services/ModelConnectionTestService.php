<?php

namespace App\Services;

use App\Models\AiModel;

class ModelConnectionTestService
{
    public const TEST_PROMPT = 'Reply with exactly: OK';

    public function __construct(
        private LlmProviderService $llmService,
    ) {}

    /**
     * @return array{ok: bool, output?: string, latency_seconds?: float, prompt_tokens?: int, completion_tokens?: int, message?: string}
     */
    public function run(AiModel $model): array
    {
        if (! $model->hasApiKey()) {
            return ['ok' => false, 'message' => 'API key is required.'];
        }

        if ($model->provider === 'openai_compatible' && empty($model->base_url) && empty($model->effective_base_url)) {
            return ['ok' => false, 'message' => 'Base URL is required for openai_compatible models.'];
        }

        try {
            $start = microtime(true);
            $response = $this->llmService->sendPrompt($model, self::TEST_PROMPT);
            $latency = microtime(true) - $start;

            return [
                'ok' => true,
                'output' => $response['output'],
                'latency_seconds' => round($latency, 3),
                'prompt_tokens' => $response['prompt_tokens'],
                'completion_tokens' => $response['completion_tokens'],
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function runFromPayload(array $attributes): array
    {
        $model = new AiModel([
            'model_name' => $attributes['model_name'],
            'provider' => $attributes['provider'],
            'display_name' => $attributes['display_name'] ?? null,
            'base_url' => $attributes['base_url'] ?? null,
            'default_params' => $attributes['default_params'] ?? ['temperature' => 0.7, 'max_tokens' => 256, 'top_p' => 1.0],
            'input_price_per_1k_tokens' => 0,
            'output_price_per_1k_tokens' => 0,
            'is_active' => true,
        ]);
        $model->api_key = $attributes['api_key'];

        return $this->run($model);
    }
}
