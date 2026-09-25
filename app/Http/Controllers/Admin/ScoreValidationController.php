<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ScoreStatus;
use App\Http\Controllers\Controller;
use App\Models\BenchmarkResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScoreValidationController extends Controller
{
    public function index(): View
    {
        $results = BenchmarkResult::with(['benchmark', 'model', 'validator'])
            ->whereHas('benchmark', fn ($q) => $q->where('status', 'completed'))
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.scores.index', compact('results'));
    }

    public function update(Request $request, BenchmarkResult $result): RedirectResponse
    {
        $validated = $request->validate([
            'validated_quality_score' => ['required', 'numeric', 'min:0', 'max:10'],
        ]);

        $result->update([
            'validated_quality_score' => $validated['validated_quality_score'],
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
            'score_status' => ScoreStatus::Validated,
        ]);

        return back()->with('status', 'Score validated.');
    }
}
