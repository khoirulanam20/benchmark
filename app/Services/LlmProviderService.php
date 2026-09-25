<?php

namespace App\Services;

use App\Models\AiModel;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmProviderService
{
    public function sendPrompt(AiModel $model, string $prompt): array
    {
        $apiKey = $model->decrypted_api_key;
        $params = $model->default_params ?? [];
        $baseUrl = $this->normalizeBaseUrl($model->effective_base_url);

        if (empty($apiKey)) {
            throw new \RuntimeException("No API key configured for model: {$model->full_name}");
        }

        if ($model->provider === 'openai_compatible' && $baseUrl === '') {
            throw new \RuntimeException("Base URL is required for model: {$model->full_name}");
        }

        return match ($model->provider) {
            'openai' => $this->callOpenAI($model, $prompt, $apiKey, $params, $baseUrl),
            'anthropic' => $this->callAnthropic($model, $prompt, $apiKey, $params, $baseUrl),
            'google' => $this->callGoogle($model, $prompt, $apiKey, $params, $baseUrl),
            'openai_compatible' => $this->callOpenAICompatible($model, $prompt, $apiKey, $params, $baseUrl),
            default => throw new \RuntimeException("Unsupported provider: {$model->provider}"),
        };
    }

    private function callOpenAI(AiModel $model, string $prompt, string $apiKey, array $params, string $baseUrl): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(120)->post($this->chatCompletionsUrl($baseUrl), $this->buildChatCompletionPayload($model, $prompt, $params));

        if ($response->failed()) {
            Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException($this->formatApiError('OpenAI API error', $response));
        }

        return $this->parseChatCompletionResponse($response->json(), $model->model_name, $baseUrl);
    }

    private function callAnthropic(AiModel $model, string $prompt, string $apiKey, array $params, string $baseUrl): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post($baseUrl.'/v1/messages', [
            'model' => $model->model_name,
            'max_tokens' => (int) ($params['max_tokens'] ?? 4096),
            'temperature' => (float) ($params['temperature'] ?? 0.7),
            'top_p' => (float) ($params['top_p'] ?? 1.0),
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]);

        if ($response->failed()) {
            Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException($this->formatApiError('Anthropic API error', $response));
        }

        $data = $response->json();

        return [
            'output' => $data['content'][0]['text'] ?? '',
            'prompt_tokens' => $data['usage']['input_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['output_tokens'] ?? 0,
        ];
    }

    private function callGoogle(AiModel $model, string $prompt, string $apiKey, array $params, string $baseUrl): array
    {
        $response = Http::timeout(120)->post($baseUrl.'/v1beta/models/'.$model->model_name.':generateContent?key='.$apiKey, [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => (float) ($params['temperature'] ?? 0.7),
                'maxOutputTokens' => (int) ($params['max_tokens'] ?? 4096),
                'topP' => (float) ($params['top_p'] ?? 1.0),
            ],
        ]);

        if ($response->failed()) {
            Log::error('Google AI error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException($this->formatApiError('Google AI error', $response));
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return [
            'output' => $text,
            'prompt_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
            'completion_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
        ];
    }

    private function callOpenAICompatible(AiModel $model, string $prompt, string $apiKey, array $params, string $baseUrl): array
    {
        $url = $this->chatCompletionsUrl($baseUrl);
        $payload = $this->buildChatCompletionPayload($model, $prompt, $params);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(120)->post($url, $payload);

        if ($response->failed()) {
            Log::error('OpenAI Compatible API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
                'model' => $model->model_name,
            ]);
            throw new \RuntimeException($this->formatApiError('API error', $response));
        }

        return $this->parseChatCompletionResponse($response->json(), $model->model_name, $baseUrl);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function buildChatCompletionPayload(AiModel $model, string $prompt, array $params): array
    {
        $maxTokens = (int) ($params['max_tokens'] ?? 4096);
        if ($maxTokens < 1) {
            $maxTokens = 4096;
        }

        $payload = [
            'model' => $model->model_name,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'stream' => false,
        ];

        if (array_key_exists('temperature', $params) && $params['temperature'] !== null && $params['temperature'] !== '') {
            $payload['temperature'] = (float) $params['temperature'];
        }

        if ($this->prefersMaxCompletionTokens($model->model_name)) {
            $payload['max_completion_tokens'] = $maxTokens;
        } else {
            $payload['max_tokens'] = $maxTokens;
        }

        if (array_key_exists('top_p', $params) && $params['top_p'] !== null && $params['top_p'] !== '') {
            $payload['top_p'] = (float) $params['top_p'];
        }

        return $payload;
    }

    private function prefersMaxCompletionTokens(string $modelName): bool
    {
        $name = strtolower($modelName);

        return preg_match('/(?:^|[\/\-_.])(?:o[1349]|gpt-5|gpt-6)/', $name) === 1;
    }

    private function normalizeBaseUrl(?string $baseUrl): string
    {
        if ($baseUrl === null || trim($baseUrl) === '') {
            return '';
        }

        return rtrim(trim($baseUrl), '/');
    }

    private function chatCompletionsUrl(string $baseUrl): string
    {
        if ($baseUrl === '') {
            return '/v1/chat/completions';
        }

        if (preg_match('#/v1$#i', $baseUrl)) {
            return $baseUrl.'/chat/completions';
        }

        return $baseUrl.'/v1/chat/completions';
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @return array{output: string, prompt_tokens: int, completion_tokens: int}
     */
    private function parseChatCompletionResponse(?array $data, string $modelName, string $baseUrl): array
    {
        $data ??= [];
        $choice = $data['choices'][0] ?? [];
        $message = is_array($choice['message'] ?? null) ? $choice['message'] : [];
        $output = $this->extractChatCompletionContent($message);

        if ($output === '' && isset($choice['text']) && is_string($choice['text'])) {
            $output = $choice['text'];
        }

        if ($output === '' && isset($data['output']) && is_string($data['output'])) {
            $output = $data['output'];
        }

        if ($output === '' && isset($data['response']) && is_string($data['response'])) {
            $output = $data['response'];
        }

        if ($output === '' && isset($message['refusal']) && is_string($message['refusal'])) {
            $output = $message['refusal'];
        }

        if ($output === '') {
            Log::warning('Chat completion returned empty content', [
                'model' => $modelName,
                'base_url' => $baseUrl,
                'finish_reason' => $choice['finish_reason'] ?? null,
                'message_keys' => array_keys($message),
                'body_preview' => mb_substr(json_encode($data, JSON_UNESCAPED_UNICODE) ?: '', 0, 500),
            ]);
        }

        return [
            'output' => $output,
            'prompt_tokens' => (int) ($data['usage']['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($data['usage']['completion_tokens'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function extractChatCompletionContent(array $message): string
    {
        $content = $message['content'] ?? null;

        if (is_string($content) && $content !== '') {
            return $content;
        }

        if (is_array($content)) {
            $parts = [];
            foreach ($content as $part) {
                if (is_string($part)) {
                    $parts[] = $part;
                } elseif (is_array($part)) {
                    if (isset($part['text']) && is_string($part['text']) && $part['text'] !== '') {
                        $parts[] = $part['text'];
                    } elseif (isset($part['content']) && is_string($part['content']) && $part['content'] !== '') {
                        $parts[] = $part['content'];
                    }
                }
            }
            $joined = trim(implode("\n", $parts));
            if ($joined !== '') {
                return $joined;
            }
        }

        foreach (['reasoning', 'reasoning_content', 'thinking'] as $field) {
            if (isset($message[$field]) && is_string($message[$field]) && $message[$field] !== '') {
                return $message[$field];
            }
        }

        return is_string($content) ? $content : '';
    }

    private function formatApiError(string $prefix, Response $response): string
    {
        $body = $response->body();
        $decoded = json_decode($body, true);

        if (is_array($decoded)) {
            $message = $decoded['error']['message'] ?? $decoded['message'] ?? null;
            if (is_string($message) && $message !== '') {
                if (str_contains($message, 'max_tokens') && str_contains($message, 'max_completion_tokens')) {
                    return $prefix.': Model ini membutuhkan max_completion_tokens (bukan max_tokens). Coba perbarui nama model atau hubungi penyedia gateway.';
                }

                return $prefix.': '.$message;
            }
        }

        return $prefix.': '.$body;
    }
}
