@extends('layouts.app')
@section('page-title', 'Add Model')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#1e293b]">Add New Model</h3>
        <p class="mt-1 text-sm text-[#64748b]">Configure an LLM model with your API key. You can use custom base URLs for proxies or self-hosted endpoints.</p>
    </div>
    <div class="rounded-xl border border-[#e2e8f0] bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('my-models.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="provider" class="block text-sm font-medium text-[#1e293b]">Provider</label>
                    <select name="provider" id="provider" required class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                        @foreach($providers as $p)
                        <option value="{{ $p }}" {{ old('provider') === $p ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $p)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="model_name" class="block text-sm font-medium text-[#1e293b]">Model ID</label>
                    <input type="text" name="model_name" id="model_name" value="{{ old('model_name') }}" required
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                        placeholder="gpt-4o">
                    @error('model_name')
                    <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="display_name" class="block text-sm font-medium text-[#1e293b]">Display Name (optional)</label>
                <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}"
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                    placeholder="My GPT-4o">
            </div>

            <div>
                <label for="api_key" class="block text-sm font-medium text-[#1e293b]">API Key</label>
                <input type="password" name="api_key" id="api_key" required
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                    placeholder="sk-...">
                @error('api_key')
                <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="base_url" class="block text-sm font-medium text-[#1e293b]">Base URL (optional)</label>
                <input type="url" name="base_url" id="base_url" value="{{ old('base_url') }}"
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                    placeholder="https://api.openai.com (leave empty for default)">
                <p class="mt-1 text-[10px] text-[#94a3b8]">Leave empty to use the provider's default URL. Use this for proxies or self-hosted endpoints.</p>
                @error('base_url')
                <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="input_price_per_1k_tokens" class="block text-sm font-medium text-[#1e293b]">Input Price / 1k tokens ($)</label>
                    <input type="number" name="input_price_per_1k_tokens" id="input_price_per_1k_tokens" value="{{ old('input_price_per_1k_tokens', '0') }}" step="0.000001" min="0"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
                <div>
                    <label for="output_price_per_1k_tokens" class="block text-sm font-medium text-[#1e293b]">Output Price / 1k tokens ($)</label>
                    <input type="number" name="output_price_per_1k_tokens" id="output_price_per_1k_tokens" value="{{ old('output_price_per_1k_tokens', '0') }}" step="0.000001" min="0"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="temperature" class="block text-sm font-medium text-[#1e293b]">Temperature</label>
                    <input type="number" name="default_params[temperature]" id="temperature" value="{{ old('default_params.temperature', '0.7') }}" step="0.1" min="0" max="2"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
                <div>
                    <label for="max_tokens" class="block text-sm font-medium text-[#1e293b]">Max Tokens</label>
                    <input type="number" name="default_params[max_tokens]" id="max_tokens" value="{{ old('default_params.max_tokens', '4096') }}" min="1"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
                <div>
                    <label for="top_p" class="block text-sm font-medium text-[#1e293b]">Top P</label>
                    <input type="number" name="default_params[top_p]" id="top_p" value="{{ old('default_params.top_p', '1.0') }}" step="0.1" min="0" max="1"
                        class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] font-mono focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                </div>
            </div>

            @include('partials.model-connection-test')

            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" class="rounded-lg bg-[#2563eb] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8] transition-colors">Save Model</button>
                <button type="button" class="rounded-lg border border-[#e2e8f0] bg-white px-5 py-2.5 text-sm font-semibold text-[#1e293b] hover:bg-[#f8fafc] transition-colors"
                    onclick="runModelFormTest('{{ route('my-models.test-connection') }}', {})">Tes koneksi</button>
                <a href="{{ route('my-models.index') }}" class="text-sm font-medium text-[#64748b] hover:text-[#1e293b]">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
