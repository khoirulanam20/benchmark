<?php

namespace App\Services;

use App\Models\AiModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmProviderService
{
    public function sendPrompt(AiModel $model, string $prompt, ?string $apiKey): array
    {
        $params = $model->default_params ?? [];

        return match ($model->provider) {
            'openai' => $this->callOpenAI($model, $prompt, $apiKey, $params),
            'anthropic' => $this->callAnthropic($model, $prompt, $apiKey, $params),
            'google' => $this->callGoogle($model, $prompt, $apiKey, $params),
            default => throw new \RuntimeException("Unsupported provider: {$model->provider}"),
        };
    }

    private function callOpenAI(AiModel $model, string $prompt, ?string $apiKey, array $params): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model->model_name,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => $params['temperature'] ?? 0.7,
            'max_tokens' => $params['max_tokens'] ?? 4096,
            'top_p' => $params['top_p'] ?? 1.0,
        ]);

        if ($response->failed()) {
            Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('OpenAI API error: '.$response->body());
        }

        $data = $response->json();

        return [
            'output' => $data['choices'][0]['message']['content'] ?? '',
            'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
        ];
    }

    private function callAnthropic(AiModel $model, string $prompt, ?string $apiKey, array $params): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model->model_name,
            'max_tokens' => $params['max_tokens'] ?? 4096,
            'temperature' => $params['temperature'] ?? 0.7,
            'top_p' => $params['top_p'] ?? 1.0,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]);

        if ($response->failed()) {
            Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Anthropic API error: '.$response->body());
        }

        $data = $response->json();

        return [
            'output' => $data['content'][0]['text'] ?? '',
            'prompt_tokens' => $data['usage']['input_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['output_tokens'] ?? 0,
        ];
    }

    private function callGoogle(AiModel $model, string $prompt, ?string $apiKey, array $params): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model->model_name}:generateContent?key={$apiKey}";

        $response = Http::timeout(120)->post($url, [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => $params['temperature'] ?? 0.7,
                'maxOutputTokens' => $params['max_tokens'] ?? 4096,
                'topP' => $params['top_p'] ?? 1.0,
            ],
        ]);

        if ($response->failed()) {
            Log::error('Google AI error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Google AI error: '.$response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return [
            'output' => $text,
            'prompt_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
            'completion_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
        ];
    }
}
