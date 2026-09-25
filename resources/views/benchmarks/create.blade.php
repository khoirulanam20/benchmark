@extends('layouts.app')
@section('page-title', 'Run Benchmark')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#1e293b]">Create New Benchmark</h3>
        <p class="mt-1 text-sm text-[#64748b]">Send the same prompt to multiple LLM models and compare their performance.</p>
    </div>

    <form method="POST" action="{{ route('benchmarks.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div>
            <label for="title" class="block text-sm font-medium text-[#1e293b]">Title (optional)</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}"
                class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors"
                placeholder="e.g., Math Reasoning Test">
        </div>

        {{-- Input Mode Tabs --}}
        <div>
            <div class="flex gap-4 mb-3" id="input-tabs">
                <button type="button" onclick="switchTab('manual')" id="tab-manual" class="rounded-lg px-4 py-2 text-sm font-semibold bg-[#2563eb] text-white transition-colors">Manual Prompt</button>
                <button type="button" onclick="switchTab('json')" id="tab-json" class="rounded-lg px-4 py-2 text-sm font-semibold bg-[#f1f5f9] text-[#64748b] transition-colors">Import JSON Dataset</button>
            </div>
            <div id="panel-manual">
                <label for="prompt_text" class="block text-sm font-medium text-[#1e293b]">Prompt</label>
                <textarea name="prompt_text" id="prompt_text" rows="8"
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-3 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors font-mono"
                    placeholder="Enter your prompt here...">{{ old('prompt_text') }}</textarea>
                @error('prompt_text')
                <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                @enderror
            </div>
            <div id="panel-json" class="hidden">
                <label for="dataset_file" class="block text-sm font-medium text-[#1e293b]">JSON File</label>
                <div class="mt-1 rounded-lg border-2 border-dashed border-[#e2e8f0] bg-[#f8fafc] p-6 text-center hover:border-[#2563eb] transition-colors">
                    <svg class="mx-auto h-8 w-8 text-[#94a3b8]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <p class="mt-2 text-sm text-[#64748b]">Upload a JSON file with prompts</p>
                    <p class="mt-1 text-[10px] text-[#94a3b8]">Format: <code class="font-mono">["prompt1", "prompt2"]</code> or <code class="font-mono">[{"prompt": "..."}]</code></p>
                    <input type="file" name="dataset_file" id="dataset_file" accept=".json" class="mt-3 block mx-auto text-sm text-[#64748b] file:mr-4 file:rounded-lg file:border-0 file:bg-[#2563eb] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#1d4ed8]">
                </div>
                @error('dataset_file')
                <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Model Selection --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <label class="text-sm font-medium text-[#1e293b]">Select Models to Compare</label>
                <a href="{{ route('my-models.create') }}" class="text-xs font-medium text-[#2563eb] hover:underline">+ Add new model</a>
            </div>
            @error('model_ids')
            <p class="mb-2 text-xs text-[#ef4444]">{{ $message }}</p>
            @enderror

            {{-- User's Models --}}
            @if($userModels->isNotEmpty())
            <div class="mb-4">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-[#64748b]">Your Models</p>
                <div class="space-y-2">
                    @foreach($userModels as $provider => $models)
                    <div class="rounded-xl border border-[#2563eb]/20 bg-[#2563eb]/5 p-4">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-[#2563eb]">{{ ucfirst($provider) }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($models as $model)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#e2e8f0] bg-white p-3 transition-colors hover:border-[#2563eb] has-[:checked]:border-[#2563eb] has-[:checked]:bg-[#2563eb]/5">
                                <input type="checkbox" name="model_ids[]" value="{{ $model->id }}" class="h-4 w-4 rounded border-[#e2e8f0] text-[#2563eb] focus:ring-[#2563eb]"
                                    {{ in_array($model->id, old('model_ids', [])) ? 'checked' : '' }}>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-[#1e293b]">{{ $model->display_name ?? $model->model_name }}</p>
                                    <p class="text-[10px] text-[#94a3b8]">
                                        @if($model->hasApiKey()) <span class="text-[#10b981]">Key set</span> @else <span class="text-[#ef4444]">No key</span> @endif
                                        @if($model->base_url) &middot; Custom URL @endif
                                    </p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Preset Models (Admin) --}}
            @if($presetModels->isNotEmpty())
            <div>
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-[#64748b]">Preset Models (Admin)</p>
                <div class="space-y-2">
                    @foreach($presetModels as $provider => $models)
                    <div class="rounded-xl border border-[#e2e8f0] bg-white p-4">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-[#64748b]">{{ ucfirst($provider) }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($models as $model)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-3 transition-colors hover:border-[#2563eb] has-[:checked]:border-[#2563eb] has-[:checked]:bg-[#2563eb]/5">
                                <input type="checkbox" name="model_ids[]" value="{{ $model->id }}" class="h-4 w-4 rounded border-[#e2e8f0] text-[#2563eb] focus:ring-[#2563eb]"
                                    {{ in_array($model->id, old('model_ids', [])) ? 'checked' : '' }}>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-[#1e293b]">{{ $model->display_name ?? $model->model_name }}</p>
                                    <p class="text-[10px] text-[#94a3b8]">In: ${{ number_format($model->input_price_per_1k_tokens, 4) }}/1k &middot; Out: ${{ number_format($model->output_price_per_1k_tokens, 4) }}/1k</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($userModels->isEmpty() && $presetModels->isEmpty())
            <div class="rounded-xl border border-[#e2e8f0] bg-white p-8 text-center">
                <p class="text-sm text-[#64748b]">No models available.</p>
                <a href="{{ route('my-models.create') }}" class="mt-2 inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4ed8]">
                    Add Your First Model
                </a>
            </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8] active:bg-[#1e40af] transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                Run Benchmark
            </button>
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-[#64748b] hover:text-[#1e293b] transition-colors">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function switchTab(tab) {
    const manualPanel = document.getElementById('panel-manual');
    const jsonPanel = document.getElementById('panel-json');
    const manualTab = document.getElementById('tab-manual');
    const jsonTab = document.getElementById('tab-json');
    if (tab === 'manual') {
        manualPanel.classList.remove('hidden');
        jsonPanel.classList.add('hidden');
        manualTab.className = 'rounded-lg px-4 py-2 text-sm font-semibold bg-[#2563eb] text-white transition-colors';
        jsonTab.className = 'rounded-lg px-4 py-2 text-sm font-semibold bg-[#f1f5f9] text-[#64748b] transition-colors';
        document.getElementById('prompt_text').required = true;
        document.getElementById('dataset_file').required = false;
    } else {
        manualPanel.classList.add('hidden');
        jsonPanel.classList.remove('hidden');
        jsonTab.className = 'rounded-lg px-4 py-2 text-sm font-semibold bg-[#2563eb] text-white transition-colors';
        manualTab.className = 'rounded-lg px-4 py-2 text-sm font-semibold bg-[#f1f5f9] text-[#64748b] transition-colors';
        document.getElementById('prompt_text').required = false;
        document.getElementById('dataset_file').required = true;
    }
}
</script>
@endpush
@endsection
