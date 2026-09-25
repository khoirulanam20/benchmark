<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $models = AiModel::orderBy('provider')->orderBy('model_name')->paginate(20);

        return view('admin.pricing.index', compact('models'));
    }

    public function update(Request $request, AiModel $model): RedirectResponse
    {
        $validated = $request->validate([
            'input_price_per_1k_tokens' => ['required', 'numeric', 'min:0'],
            'output_price_per_1k_tokens' => ['required', 'numeric', 'min:0'],
        ]);

        $model->update($validated);

        return back()->with('status', "Pricing updated for {$model->full_name}.");
    }
}
