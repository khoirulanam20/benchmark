<?php

namespace App\Http\Controllers;

use App\Enums\BenchmarkStatus;
use App\Jobs\RunBenchmarkJob;
use App\Models\AiModel;
use App\Models\Benchmark;
use App\Models\BenchmarkResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BenchmarkController extends Controller
{
    public function create(): View
    {
        $models = AiModel::active()->orderBy('provider')->orderBy('model_name')->get();
        $providerGroups = $models->groupBy('provider');

        return view('benchmarks.create', compact('providerGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'prompt_text' => ['required_without:dataset_file', 'nullable', 'string', 'max:50000'],
            'dataset_file' => ['required_without:prompt_text', 'nullable', 'file', 'mimes:json', 'max:1024'],
            'model_ids' => ['required', 'array', 'min:1'],
            'model_ids.*' => ['exists:models,id'],
        ]);

        $prompts = [];

        if ($request->hasFile('dataset_file')) {
            $content = file_get_contents($request->file('dataset_file')->getRealPath());
            $data = json_decode($content, true);

            if (! is_array($data)) {
                return back()->withErrors(['dataset_file' => 'Invalid JSON file.'])->withInput();
            }

            foreach ($data as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $prompts[] = trim($item);
                } elseif (is_array($item) && isset($item['prompt']) && trim($item['prompt']) !== '') {
                    $prompts[] = trim($item['prompt']);
                }
            }

            if (empty($prompts)) {
                return back()->withErrors(['dataset_file' => 'No valid prompts found in JSON file.'])->withInput();
            }
        } else {
            $prompts[] = $validated['prompt_text'];
        }

        $created = [];
        foreach ($prompts as $index => $prompt) {
            $benchmark = Benchmark::create([
                'user_id' => $request->user()->id,
                'title' => ($validated['title'] ?? 'Benchmark '.now()->format('Y-m-d H:i'))
                    .(count($prompts) > 1 ? ' #'.($index + 1) : ''),
                'prompt_text' => $prompt,
                'status' => BenchmarkStatus::Pending,
            ]);

            foreach ($validated['model_ids'] as $modelId) {
                BenchmarkResult::create([
                    'benchmark_id' => $benchmark->id,
                    'model_id' => $modelId,
                    'score_status' => 'pending',
                ]);
            }

            RunBenchmarkJob::dispatch($benchmark);
            $created[] = $benchmark;
        }

        if (count($created) === 1) {
            return redirect()->route('benchmarks.show', $created[0])
                ->with('status', 'Benchmark submitted. Processing in background.');
        }

        return redirect()->route('dashboard')
            ->with('status', count($created).' benchmarks submitted from dataset.');
    }

    public function show(Request $request, Benchmark $benchmark): View
    {
        abort_unless($benchmark->user_id === $request->user()->id, 403);

        $benchmark->load(['results.model', 'results.validator']);

        return view('benchmarks.show', compact('benchmark'));
    }

    public function status(Request $request, Benchmark $benchmark): array
    {
        abort_unless($benchmark->user_id === $request->user()->id, 403);

        return [
            'status' => $benchmark->status,
            'results' => $benchmark->results->map(fn (BenchmarkResult $r) => [
                'id' => $r->id,
                'model' => $r->model->full_name,
                'quality_score' => $r->effective_score,
                'cost' => $r->cost,
                'latency' => $r->latency_seconds,
                'score_status' => $r->score_status,
            ]),
        ];
    }

    public function estimateCost(Request $request): array
    {
        $promptTokens = (int) ceil(str_word_count($request->input('prompt_text', '')) * 1.3);
        $modelIds = $request->input('model_ids', []);

        $estimates = [];
        $total = 0;

        foreach ($modelIds as $modelId) {
            $model = AiModel::find($modelId);
            if (! $model) {
                continue;
            }

            $outputTokens = min(($model->default_params['max_tokens'] ?? 4096), 1000);
            $inputCost = ($promptTokens / 1000) * (float) $model->input_price_per_1k_tokens;
            $outputCost = ($outputTokens / 1000) * (float) $model->output_price_per_1k_tokens;
            $cost = round($inputCost + $outputCost, 6);
            $total += $cost;

            $estimates[] = [
                'model_id' => $model->id,
                'model_name' => $model->full_name,
                'estimated_input_tokens' => $promptTokens,
                'estimated_output_tokens' => $outputTokens,
                'estimated_cost' => $cost,
            ];
        }

        $user = $request->user();
        $remaining = max(0, (float) $user->monthly_cost_limit - (float) $user->current_month_cost);

        return [
            'estimates' => $estimates,
            'total_estimated_cost' => round($total, 6),
            'monthly_limit' => (float) $user->monthly_cost_limit,
            'current_month_cost' => (float) $user->current_month_cost,
            'remaining_budget' => round($remaining, 6),
            'within_budget' => $total <= $remaining,
        ];
    }
}
