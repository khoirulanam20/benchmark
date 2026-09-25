@extends('layouts.app')
@section('page-title', 'Run Benchmark')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#1e293b]">Create New Benchmark</h3>
        <p class="mt-1 text-sm text-[#64748b]">Send the same prompt to multiple LLM models and compare their performance.</p>
    </div>

    <form method="POST" action="{{ route('benchmarks.store') }}" class="space-y-6">
        @csrf
        {{-- Title --}}
        <div>
            <label for="title" class="block text-sm font-medium text-[#1e293b]">Title (optional)</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}"
                class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors"
                placeholder="e.g., Math Reasoning Test">
        </div>

        {{-- Prompt --}}
        <div>
            <label for="prompt_text" class="block text-sm font-medium text-[#1e293b]">Prompt</label>
            <textarea name="prompt_text" id="prompt_text" rows="8" required
                class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-3 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors font-mono"
                placeholder="Enter your prompt here...">{{ old('prompt_text') }}</textarea>
            @error('prompt_text')
            <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
            @enderror
        </div>

        {{-- Model Selection --}}
        <div>
            <label class="block text-sm font-medium text-[#1e293b] mb-3">Select Models to Compare</label>
            @error('model_ids')
            <p class="mb-2 text-xs text-[#ef4444]">{{ $message }}</p>
            @enderror
            <div class="space-y-4">
                @foreach($providerGroups as $provider => $models)
                <div class="rounded-xl border border-[#e2e8f0] bg-white p-4">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-[#64748b]">{{ ucfirst($provider) }}</p>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($models as $model)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-3 transition-colors hover:border-[#2563eb] has-[:checked]:border-[#2563eb] has-[:checked]:bg-[#2563eb]/5">
                            <input type="checkbox" name="model_ids[]" value="{{ $model->id }}" class="h-4 w-4 rounded border-[#e2e8f0] text-[#2563eb] focus:ring-[#2563eb]"
                                {{ in_array($model->id, old('model_ids', [])) ? 'checked' : '' }}>
                            <div>
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

        {{-- Submit --}}
        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8] active:bg-[#1e40af] transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                Run Benchmark
            </button>
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-[#64748b] hover:text-[#1e293b] transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
