@extends('layouts.app')
@section('page-title', 'Edit Model')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#1e293b]">Edit Model</h3>
        <p class="mt-1 text-sm text-[#64748b]">Update configuration for {{ $model->full_name }}.</p>
    </div>
    <div class="rounded-xl border border-[#e2e8f0] bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('my-models.update', $model) }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#1e293b]">Provider</label>
                <p class="mt-1 text-sm text-[#64748b]">{{ ucfirst($model->provider) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#1e293b]">Model ID</label>
                <p class="mt-1 text-sm text-[#64748b] font-mono">{{ $model->model_name }}</p>
            </div>
            <div>
                <label for="display_name" class="block text-sm font-medium text-[#1e293b]">Display Name</label>
                <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $model->display_name) }}"
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
            </div>
            <div>
                <label for="api_key" class="block text-sm font-medium text-[#1e293b]">API Key</label>
                <input type="password" name="api_key" id="api_key"
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                    placeholder="Leave empty to keep current key">
                <p class="mt-1 text-[10px] text-[#94a3b8]">Current: {{ $model->hasApiKey() ? '••••••••' . substr($model->decrypted_api_key ?? '', -4) : 'Not set' }}</p>
            </div>
            <div>
                <label for="base_url" class="block text-sm font-medium text-[#1e293b]">Base URL</label>
                <input type="url" name="base_url" id="base_url" value="{{ old('base_url', $model->base_url) }}"
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                    placeholder="https://api.openai.com">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="input_price_per_1k_tokens" class="block text-sm font-medium text-[#1e293b]">Input Price / 1k tokens ($)</label>
                    <input type="number" name="input_price_per_1k_tokens" value="{{ old('input_price_per_1k_tokens', $model->input_price_per_1k_tokens) }}" step="0.000001" min="0"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
                <div>
                    <label for="output_price_per_1k_tokens" class="block text-sm font-medium text-[#1e293b]">Output Price / 1k tokens ($)</label>
                    <input type="number" name="output_price_per_1k_tokens" value="{{ old('output_price_per_1k_tokens', $model->output_price_per_1k_tokens) }}" step="0.000001" min="0"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="temperature" class="block text-sm font-medium text-[#1e293b]">Temperature</label>
                    <input type="number" name="default_params[temperature]" value="{{ old('default_params.temperature', $model->default_params['temperature'] ?? 0.7) }}" step="0.1" min="0" max="2"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
                <div>
                    <label for="max_tokens" class="block text-sm font-medium text-[#1e293b]">Max Tokens</label>
                    <input type="number" name="default_params[max_tokens]" value="{{ old('default_params.max_tokens', $model->default_params['max_tokens'] ?? 4096) }}" min="1"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
                <div>
                    <label for="top_p" class="block text-sm font-medium text-[#1e293b]">Top P</label>
                    <input type="number" name="default_params[top_p]" value="{{ old('default_params.top_p', $model->default_params['top_p'] ?? 1.0) }}" step="0.1" min="0" max="1"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $model->is_active) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-[#e2e8f0] text-[#2563eb] focus:ring-[#2563eb]">
                    <span class="text-sm font-medium text-[#1e293b]">Active</span>
                </label>
            </div>
            @include('partials.model-connection-test')

            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" class="rounded-lg bg-[#2563eb] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8] transition-colors">Update Model</button>
                <button type="button" class="rounded-lg border border-[#e2e8f0] bg-white px-5 py-2.5 text-sm font-semibold text-[#1e293b] hover:bg-[#f8fafc] transition-colors"
                    onclick="runModelFormTest('{{ route('my-models.test-connection') }}', { savedModelUrl: '{{ route('my-models.test', $model) }}' })">Tes koneksi</button>
                <a href="{{ route('my-models.index') }}" class="text-sm font-medium text-[#64748b] hover:text-[#1e293b]">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
