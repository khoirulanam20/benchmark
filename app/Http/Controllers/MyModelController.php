<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Services\ModelConnectionTestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyModelController extends Controller
{
    private const PROVIDERS = ['openai', 'anthropic', 'google', 'openai_compatible'];

    public function index(Request $request): View
    {
        $models = AiModel::forUser($request->user()->id)
            ->orderBy('provider')
            ->orderBy('model_name')
            ->get()
            ->groupBy('provider');

        return view('my-models.index', compact('models'));
    }

    public function create(): View
    {
        return view('my-models.create', ['providers' => self::PROVIDERS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'model_name' => ['required', 'string', 'max:200'],
            'provider' => ['required', 'string', 'max:50'],
            'display_name' => ['nullable', 'string', 'max:100'],
            'api_key' => ['required', 'string', 'min:5'],
            'base_url' => ['nullable', 'url', 'max:500'],
            'input_price_per_1k_tokens' => ['nullable', 'numeric', 'min:0'],
            'output_price_per_1k_tokens' => ['nullable', 'numeric', 'min:0'],
            'default_params' => ['nullable', 'array'],
            'default_params.temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'default_params.max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'default_params.top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ]);

        $model = new AiModel([
            'user_id' => $request->user()->id,
            'model_name' => $validated['model_name'],
            'provider' => $validated['provider'],
            'display_name' => $validated['display_name'] ?? null,
            'input_price_per_1k_tokens' => $validated['input_price_per_1k_tokens'] ?? 0,
            'output_price_per_1k_tokens' => $validated['output_price_per_1k_tokens'] ?? 0,
            'default_params' => $validated['default_params'] ?? ['temperature' => 0.7, 'max_tokens' => 4096, 'top_p' => 1.0],
            'base_url' => $validated['base_url'] ?? null,
            'is_active' => true,
        ]);
        $model->api_key = $validated['api_key'];
        $model->save();

        return redirect()->route('my-models.index')
            ->with('status', 'Model "'.$model->full_name.'" created with API key.');
    }

    public function edit(Request $request, AiModel $model): View
    {
        abort_unless($model->isOwnedBy($request->user()->id), 403);

        return view('my-models.edit', [
            'model' => $model,
            'providers' => self::PROVIDERS,
        ]);
    }

    public function update(Request $request, AiModel $model): RedirectResponse
    {
        abort_unless($model->isOwnedBy($request->user()->id), 403);

        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:100'],
            'api_key' => ['nullable', 'string', 'min:5'],
            'base_url' => ['nullable', 'string', 'max:500'],
            'input_price_per_1k_tokens' => ['nullable', 'numeric', 'min:0'],
            'output_price_per_1k_tokens' => ['nullable', 'numeric', 'min:0'],
            'default_params' => ['nullable', 'array'],
            'default_params.temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'default_params.max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'default_params.top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (! empty($validated['api_key'])) {
            $model->api_key = $validated['api_key'];
            unset($validated['api_key']);
        } else {
            unset($validated['api_key']);
        }

        $model->update($validated);

        return redirect()->route('my-models.index')
            ->with('status', 'Model updated.');
    }

    public function destroy(Request $request, AiModel $model): RedirectResponse
    {
        abort_unless($model->isOwnedBy($request->user()->id), 403);

        $model->delete();

        return redirect()->route('my-models.index')
            ->with('status', 'Model deleted.');
    }

    public function test(Request $request, AiModel $model, ModelConnectionTestService $testService): JsonResponse
    {
        abort_unless($model->isOwnedBy($request->user()->id), 403);

        $testModel = $this->modelWithRequestOverrides($model, $request);
        $result = $testService->run($testModel);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    public function testConnection(Request $request, ModelConnectionTestService $testService): JsonResponse
    {
        $validated = $request->validate([
            'model_name' => ['required', 'string', 'max:200'],
            'provider' => ['required', 'string', 'max:50'],
            'api_key' => ['required', 'string', 'min:5'],
            'base_url' => ['nullable', 'string', 'max:500'],
            'default_params' => ['nullable', 'array'],
        ]);

        $result = $testService->runFromPayload($validated);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    private function modelWithRequestOverrides(AiModel $model, Request $request): AiModel
    {
        if (! $request->has('base_url') && ! $request->filled('api_key')) {
            return $model;
        }

        $testModel = $model->replicate();

        if ($request->has('base_url')) {
            $testModel->base_url = $request->input('base_url') ?: null;
        }

        if ($request->filled('api_key')) {
            $testModel->api_key = $request->string('api_key')->toString();
        }

        return $testModel;
    }
}
