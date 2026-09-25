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
            'prompt_text' => ['required', 'string', 'max:50000'],
            'model_ids' => ['required', 'array', 'min:1'],
            'model_ids.*' => ['exists:models,id'],
        ]);

        $benchmark = Benchmark::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'] ?? 'Benchmark '.now()->format('Y-m-d H:i'),
            'prompt_text' => $validated['prompt_text'],
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

        return redirect()->route('benchmarks.show', $benchmark)
            ->with('status', 'Benchmark submitted. Processing in background.');
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
}
