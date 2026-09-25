<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\BenchmarkResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TypeSafeJevScoringService
{
    /** @var list<string> */
    private const SCORE_CRITERIA = [
        '1 — Sangat buruk',
        '2 — Buruk',
        '3 — Di bawah rata-rata',
        '4 — Cukup rendah',
        '5 — Cukup',
        '6 — Cukup baik',
        '7 — Baik',
        '8 — Sangat baik',
        '9 — Hampir sempurna',
        '10 — Sempurna',
    ];

    public function score(BenchmarkResult $result): bool
    {
        $apiKey = AppSetting::get('typesafe_api_key');
        if ($apiKey === null) {
            return false;
        }

        $benchmark = $result->benchmark;
        if (! $benchmark || ! filled($result->output_content)) {
            return false;
        }

        $baseUrl = rtrim(AppSetting::get('typesafe_base_url', 'https://api.typesafe.ai') ?? 'https://api.typesafe.ai', '/');
        $model = AppSetting::get('typesafe_model', 'jev-latest') ?? 'jev-latest';

        $state = "Original prompt:\n{$benchmark->prompt_text}\n\nAI response to evaluate:\n".mb_substr($result->output_content, 0, 12000);

        $questions = [
            'accuracy' => $this->scoreQuestion('Akurasi dan kebenaran fakta respons terhadap prompt.'),
            'completeness' => $this->scoreQuestion('Kelengkapan jawaban terhadap permintaan prompt.'),
            'clarity' => $this->scoreQuestion('Kejelasan dan koherensi bahasa.'),
            'relevance' => $this->scoreQuestion('Relevansi respons terhadap prompt.'),
            'conciseness' => $this->scoreQuestion('Ketepatan panjang jawaban (tidak bertele-tele).'),
            'overall' => $this->scoreQuestion('Kualitas keseluruhan respons untuk prompt tersebut.'),
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($baseUrl.'/v1/systemone', [
                'model' => $model,
                'state' => $state,
                'questions' => $questions,
            ]);

            if ($response->failed()) {
                Log::error('TypeSafe Jev scoring failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'result_id' => $result->id,
                ]);

                return false;
            }

            $data = $response->json();
            $answers = $data['answers'] ?? [];
            if (! is_array($answers)) {
                return false;
            }

            $dimensions = [];
            $sum = 0.0;
            $count = 0;
            foreach (['accuracy', 'completeness', 'clarity', 'relevance', 'conciseness'] as $dimension) {
                $value = $this->scoreAnswerToTen($answers[$dimension] ?? null);
                $dimensions[$dimension] = $value;
                $sum += $value;
                $count++;
            }

            $overall = $this->scoreAnswerToTen($answers['overall'] ?? null);
            if ($overall <= 0 && $count > 0) {
                $overall = round($sum / $count, 2);
            }

            $result->update([
                'quality_score' => round($overall, 2),
                'score_breakdown' => [
                    'accuracy' => $dimensions['accuracy'] ?? 0,
                    'completeness' => $dimensions['completeness'] ?? 0,
                    'clarity' => $dimensions['clarity'] ?? 0,
                    'relevance' => $dimensions['relevance'] ?? 0,
                    'conciseness' => $dimensions['conciseness'] ?? 0,
                    'reasoning' => 'Scored with TypeSafe Jev ('.($data['model'] ?? $model).').',
                    'jev_model' => $data['model'] ?? $model,
                ],
                'score_status' => 'scored',
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('TypeSafe Jev scoring exception', [
                'result_id' => $result->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return array{type: string, instructions: string, criteria: list<string>}
     */
    private function scoreQuestion(string $instructions): array
    {
        return [
            'type' => 'score',
            'instructions' => $instructions,
            'criteria' => self::SCORE_CRITERIA,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $answer
     */
    private function scoreAnswerToTen(?array $answer): float
    {
        if (! is_array($answer) || ($answer['type'] ?? '') !== 'score') {
            return 0.0;
        }

        $index = (float) ($answer['score'] ?? 0);

        return round(min(10.0, max(1.0, $index + 1)), 2);
    }
}
