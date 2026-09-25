<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModelPresetController extends Controller
{
    public function index(): View
    {
        $models = AiModel::orderBy('provider')->orderBy('model_name')->get();
        $grouped = $models->groupBy('provider');

        return view('admin.models.index', compact('grouped'));
    }

    public function create(): View
    {
        return view('admin.models.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'model_name' => ['required', 'string', 'max:100', 'unique:models,model_name'],
            'provider' => ['required', 'string', 'max:50'],
            'display_name' => ['nullable', 'string', 'max:100'],
            'input_price_per_1k_tokens' => ['required', 'numeric', 'min:0'],
            'output_price_per_1k_tokens' => ['required', 'numeric', 'min:0'],
            'default_params' => ['nullable', 'array'],
            'default_params.temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'default_params.max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'default_params.top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ]);

        AiModel::create($validated);

        return redirect()->route('admin.models.index')
            ->with('status', 'Model preset created.');
    }

    public function edit(AiModel $model): View
    {
        return view('admin.models.edit', compact('model'));
    }

    public function update(Request $request, AiModel $model): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:100'],
            'input_price_per_1k_tokens' => ['required', 'numeric', 'min:0'],
            'output_price_per_1k_tokens' => ['required', 'numeric', 'min:0'],
            'default_params' => ['nullable', 'array'],
            'default_params.temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'default_params.max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'default_params.top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $model->update($validated);

        return redirect()->route('admin.models.index')
            ->with('status', 'Model preset updated.');
    }

    public function destroy(AiModel $model): RedirectResponse
    {
        $model->delete();

        return redirect()->route('admin.models.index')
            ->with('status', 'Model preset deleted.');
    }
}
