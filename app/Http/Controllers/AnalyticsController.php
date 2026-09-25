<?php

namespace App\Http\Controllers;

use App\Models\Benchmark;
use App\Models\BenchmarkResult;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $completedBenchmarkIds = Benchmark::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('id');

        $results = BenchmarkResult::whereIn('benchmark_id', $completedBenchmarkIds)
            ->with('model')
            ->get();

        // Per-model aggregated metrics
        $modelMetrics = $results->groupBy('model_id')->map(function ($group) {
            $model = $group->first()->model;

            return [
                'model' => $model,
                'count' => $group->count(),
                'avg_score' => round($group->whereNotNull('quality_score')->avg('quality_score') ?? 0, 2),
                'avg_latency' => round($group->whereNotNull('latency_seconds')->avg('latency_seconds') ?? 0, 2),
                'total_cost' => round($group->sum('cost'), 6),
                'avg_cost' => round($group->avg('cost'), 6),
                'total_tokens' => $group->sum(fn ($r) => $r->prompt_tokens + $r->completion_tokens),
            ];
        })->sortByDesc('avg_score')->values();

        // Cost vs Quality scatter data
        $scatterData = $modelMetrics->map(fn ($m) => [
            'label' => $m['model']->full_name,
            'cost' => $m['avg_cost'],
            'score' => $m['avg_score'],
            'latency' => $m['avg_latency'],
        ]);

        // Overall stats
        $overallStats = [
            'total_benchmarks' => $completedBenchmarkIds->count(),
            'total_results' => $results->count(),
            'total_cost' => round($results->sum('cost'), 4),
            'best_model' => $modelMetrics->first()?->model?->full_name ?? 'N/A',
            'fastest_model' => $modelMetrics->sortBy('avg_latency')->first()?->model?->full_name ?? 'N/A',
            'cheapest_model' => $modelMetrics->sortBy('avg_cost')->first()?->model?->full_name ?? 'N/A',
        ];

        return view('analytics.index', compact('modelMetrics', 'scatterData', 'overallStats'));
    }
}
