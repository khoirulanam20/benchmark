<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\BenchmarkResult;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScoringService
{
    private const JUDGE_PROMPT = <<<'PROMPT'
You are an expert AI output evaluator. Rate the following AI-generated response on a scale of 1-10 based on these criteria:
- Accuracy & Factual Correctness (weight: 30%)
- Completeness (weight: 25%)
- Clarity & Coherence (weight: 20%)
- Relevance to the prompt (weight: 15%)
- Conciseness (weight: 10%)

Original Prompt:
---
{prompt}
---

AI Response to evaluate:
---
{response}
---

Reply ONLY with valid JSON in this exact format:
{"score": <float 1-10>, "accuracy": <float 1-10>, "completeness": <float 1-10>, "clarity": <float 1-10>, "relevance": <float 1-10>, "conciseness": <float 1-10>, "reasoning": "<brief explanation>"}
PROMPT;

    public function score(BenchmarkResult $result): void
    {
        $benchmark = $result->benchmark;
        if (! $benchmark || ! $result->output_content) {
            return;
        }

        $judgeKey = $this->findJudgeApiKey($result->benchmark->user_id);
        if ($judgeKey === null) {
            Log::warning('No judge API key found for scoring', ['result_id' => $result->id]);

            return;
        }

        $prompt = str_replace(
            ['{prompt}', '{response}'],
            [$benchmark->prompt_text, mb_substr($result->output_content, 0, 4000)],
            self::JUDGE_PROMPT,
        );

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$judgeKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.1,
                'max_tokens' => 500,
            ]);

            if ($response->failed()) {
                Log::error('Scoring API failed', ['status' => $response->status()]);

                return;
            }

            $content = $response->json()['choices'][0]['message']['content'] ?? '';
            $content = trim($content);
            if (str_starts_with($content, '```')) {
                $content = preg_replace('/^```(?:json)?\s*/', '', $content);
                $content = preg_replace('/\s*```$/', '', $content);
            }

            $scores = json_decode($content, true);
            if (! is_array($scores) || ! isset($scores['score'])) {
                Log::error('Invalid scoring response', ['content' => $content]);

                return;
            }

            $result->update([
                'quality_score' => round((float) $scores['score'], 2),
                'score_breakdown' => [
                    'accuracy' => (float) ($scores['accuracy'] ?? 0),
                    'completeness' => (float) ($scores['completeness'] ?? 0),
                    'clarity' => (float) ($scores['clarity'] ?? 0),
                    'relevance' => (float) ($scores['relevance'] ?? 0),
                    'conciseness' => (float) ($scores['conciseness'] ?? 0),
                    'reasoning' => $scores['reasoning'] ?? '',
                ],
                'score_status' => 'scored',
            ]);

        } catch (\Throwable $e) {
            Log::error('Scoring exception', ['result_id' => $result->id, 'error' => $e->getMessage()]);
        }
    }

    private function findJudgeApiKey(int $userId): ?string
    {
        $apiKey = ApiKey::where('user_id', $userId)
            ->where('provider_name', 'openai')
            ->first();

        return $apiKey ? Crypt::decryptString($apiKey->encrypted_key) : null;
    }
}
