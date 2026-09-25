@extends('layouts.app')
@section('page-title', 'Benchmark Result')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#1e293b]">{{ $benchmark->title }}</h3>
            <p class="mt-1 text-sm text-[#64748b]">Created {{ $benchmark->created_at->format('M d, Y H:i') }}</p>
        </div>
        @php $statusColor = match($benchmark->status->value) { 'completed' => 'bg-[#10b981]/10 text-[#10b981]', 'processing' => 'bg-[#2563eb]/10 text-[#2563eb]', 'failed' => 'bg-[#ef4444]/10 text-[#ef4444]', default => 'bg-[#f59e0b]/10 text-[#f59e0b]' }; @endphp
        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-semibold {{ $statusColor }}" id="status-badge">
            @if($benchmark->status->value === 'processing')
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            @endif
            {{ $benchmark->status->label() }}
        </span>
    </div>

    {{-- Prompt --}}
    <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-[#64748b] mb-2">Original Prompt</p>
        <pre class="whitespace-pre-wrap rounded-lg bg-[#f8fafc] p-4 text-sm font-mono text-[#1e293b]">{{ $benchmark->prompt_text }}</pre>
    </div>

    {{-- Results Grid --}}
    <h4 class="text-lg font-bold text-[#1e293b]">Model Comparison</h4>
    @if($benchmark->results->isEmpty())
    <p class="text-sm text-[#64748b]">No results yet.</p>
    @else
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @foreach($benchmark->results as $result)
        <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
            {{-- Model Header --}}
            <div class="flex items-center justify-between border-b border-[#e2e8f0] bg-[#f8fafc] px-5 py-3">
                <div>
                    <p class="text-sm font-bold text-[#1e293b]">{{ $result->model->full_name }}</p>
                    <p class="text-[10px] text-[#94a3b8]">{{ $result->model->provider }}</p>
                </div>
                @if($result->effective_score)
                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $result->effective_score >= 7 ? 'bg-[#10b981]/10 text-[#10b981]' : ($result->effective_score >= 4 ? 'bg-[#f59e0b]/10 text-[#f59e0b]' : 'bg-[#ef4444]/10 text-[#ef4444]') }}">
                    <span class="text-sm font-bold">{{ number_format($result->effective_score, 1) }}</span>
                </div>
                @endif
            </div>

            {{-- Output --}}
            <div class="p-5">
                @if($result->error_message)
                <div class="rounded-lg border border-[#ef4444]/20 bg-[#ef4444]/5 p-3">
                    <p class="text-xs font-semibold text-[#ef4444]">Error</p>
                    <p class="mt-1 text-xs text-[#64748b]">{{ $result->error_message }}</p>
                </div>
                @elseif($result->output_content)
                <div class="max-h-64 overflow-y-auto rounded-lg bg-[#f8fafc] p-4">
                    <pre class="whitespace-pre-wrap text-sm font-mono text-[#1e293b]">{{ $result->output_content }}</pre>
                </div>
                @else
                <div class="flex items-center gap-2 py-8 justify-center">
                    <svg class="h-5 w-5 animate-spin text-[#2563eb]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-sm text-[#64748b]">Waiting for response...</span>
                </div>
                @endif
            </div>

            {{-- Metrics Footer --}}
            @if($result->output_content)
            <div class="grid grid-cols-3 divide-x divide-[#e2e8f0] border-t border-[#e2e8f0] bg-[#f8fafc]">
                <div class="p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748b]">Latency</p>
                    <p class="mt-1 text-sm font-bold text-[#1e293b]">{{ $result->latency_seconds ? number_format($result->latency_seconds, 2) . 's' : '-' }}</p>
                </div>
                <div class="p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748b]">Cost</p>
                    <p class="mt-1 text-sm font-bold text-[#1e293b]">${{ number_format($result->cost, 6) }}</p>
                </div>
                <div class="p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#64748b]">Tokens</p>
                    <p class="mt-1 text-sm font-bold text-[#1e293b]">{{ number_format($result->prompt_tokens + $result->completion_tokens) }}</p>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Comparison Table --}}
    @if($benchmark->results->whereNotNull('output_content')->count() > 1)
    <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-[#e2e8f0] bg-[#f8fafc]">
            <h4 class="text-sm font-bold text-[#1e293b]">Metrics Comparison</h4>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#e2e8f0]">
                    <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Model</th>
                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Score</th>
                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Latency</th>
                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Cost</th>
                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Tokens</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1f5f9]">
                @foreach($benchmark->results->sortByDesc('effective_score') as $result)
                <tr>
                    <td class="px-4 py-2.5 text-sm font-medium text-[#1e293b]">{{ $result->model->full_name }}</td>
                    <td class="px-4 py-2.5 text-right text-sm font-bold {{ ($result->effective_score ?? 0) >= 7 ? 'text-[#10b981]' : (($result->effective_score ?? 0) >= 4 ? 'text-[#f59e0b]' : 'text-[#ef4444]') }}">
                        {{ $result->effective_score ? number_format($result->effective_score, 1) . '/10' : '-' }}
                    </td>
                    <td class="px-4 py-2.5 text-right text-sm text-[#1e293b]">{{ $result->latency_seconds ? number_format($result->latency_seconds, 2) . 's' : '-' }}</td>
                    <td class="px-4 py-2.5 text-right text-sm text-[#1e293b]">${{ number_format($result->cost, 6) }}</td>
                    <td class="px-4 py-2.5 text-right text-sm text-[#64748b]">{{ number_format($result->prompt_tokens + $result->completion_tokens) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@push('scripts')
@if(in_array($benchmark->status->value, ['pending', 'processing']))
<script>
    const poll = setInterval(async () => {
        try {
            const res = await fetch('{{ route("benchmarks.status", $benchmark) }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(poll);
                    window.location.reload();
                }
            }
        } catch (e) {}
    }, 3000);
</script>
@endif
@endpush
@endsection
