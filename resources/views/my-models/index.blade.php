@extends('layouts.app')
@section('page-title', 'My Models')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-[#1e293b]">My Models & API Keys</h3>
            <p class="mt-1 text-sm text-[#64748b]">Manage your LLM models with their API keys and custom base URLs.</p>
        </div>
        <a href="{{ route('my-models.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4ed8] transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Model
        </a>
    </div>

    @if($models->isEmpty())
    <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm">
        <div class="flex flex-col items-center justify-center py-16">
            <svg class="h-12 w-12 text-[#94a3b8]" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="mt-4 text-sm font-medium text-[#64748b]">No models configured yet</p>
            <p class="mt-1 text-xs text-[#94a3b8]">Add a model with your API key to start running benchmarks.</p>
            <a href="{{ route('my-models.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4ed8]">
                Add Model
            </a>
        </div>
    </div>
    @else
    @foreach($models as $provider => $providerModels)
    <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-[#e2e8f0] bg-[#f8fafc]">
            <h4 class="text-xs font-bold uppercase tracking-wider text-[#64748b]">{{ ucfirst($provider) }}</h4>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#e2e8f0]">
                    <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Model</th>
                    <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Base URL</th>
                    <th class="px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-[#64748b]">API Key</th>
                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Pricing</th>
                    <th class="px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-[#64748b]">Status</th>
                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1f5f9]">
                @foreach($providerModels as $model)
                <tr>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-[#1e293b]">{{ $model->display_name ?? $model->model_name }}</p>
                        <p class="text-[10px] text-[#94a3b8] font-mono">{{ $model->model_name }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @if($model->base_url)
                        <span class="text-xs font-mono text-[#64748b]">{{ Str::limit($model->base_url, 35) }}</span>
                        @else
                        <span class="text-xs text-[#94a3b8]">Default</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($model->hasApiKey())
                        <span class="inline-flex rounded-full bg-[#10b981]/10 px-2.5 py-1 text-xs font-semibold text-[#10b981]">Configured</span>
                        @else
                        <span class="inline-flex rounded-full bg-[#ef4444]/10 px-2.5 py-1 text-xs font-semibold text-[#ef4444]">Missing</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <p class="text-xs text-[#64748b]">In: ${{ number_format($model->input_price_per_1k_tokens, 4) }}/1k</p>
                        <p class="text-xs text-[#64748b]">Out: ${{ number_format($model->output_price_per_1k_tokens, 4) }}/1k</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($model->is_active)
                        <span class="inline-flex rounded-full bg-[#10b981]/10 px-2.5 py-1 text-xs font-semibold text-[#10b981]">Active</span>
                        @else
                        <span class="inline-flex rounded-full bg-[#94a3b8]/10 px-2.5 py-1 text-xs font-semibold text-[#94a3b8]">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button type="button" class="text-sm font-medium text-[#64748b] hover:text-[#2563eb]" onclick="runModelSavedTest('{{ route('my-models.test', $model) }}')">Tes</button>
                        <a href="{{ route('my-models.edit', $model) }}" class="text-sm font-medium text-[#2563eb] hover:underline">Edit</a>
                        <form method="POST" action="{{ route('my-models.destroy', $model) }}" class="inline" onsubmit="return confirm('Delete this model?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-medium text-[#ef4444] hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
    @endif

    @include('partials.model-connection-test')
</div>
@endsection
