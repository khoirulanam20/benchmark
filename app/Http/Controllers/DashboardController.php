<?php

namespace App\Http\Controllers;

use App\Enums\BenchmarkStatus;
use App\Models\Benchmark;
use App\Models\BenchmarkResult;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $benchmarks = Benchmark::where('user_id', $user->id)
            ->withCount('results')
            ->withSum('results', 'cost')
            ->withAvg('results', 'quality_score')
            ->latest()
            ->paginate(5);

        $stats = [
            'total_benchmarks' => Benchmark::where('user_id', $user->id)->count(),
            'completed' => Benchmark::where('user_id', $user->id)->where('status', BenchmarkStatus::Completed)->count(),
            'total_cost' => BenchmarkResult::whereHas('benchmark', fn ($q) => $q->where('user_id', $user->id))->sum('cost'),
            'avg_quality' => BenchmarkResult::whereHas('benchmark', fn ($q) => $q->where('user_id', $user->id))->whereNotNull('quality_score')->avg('quality_score'),
        ];

        return view('dashboard', compact('benchmarks', 'stats'));
    }
}
