@extends('layouts.app')
@section('page-title', 'Score Validation')

@section('content')
<div class="space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#1e293b]">Score Validation</h3>
        <p class="mt-1 text-sm text-[#64748b]">Review and validate automated LLM-as-a-judge quality scores.</p>
    </div>

    <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
        @if($results->isEmpty())
        <div class="py-12 text-center">
            <svg class="mx-auto h-10 w-10 text-[#94a3b8]" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="mt-3 text-sm text-[#64748b]">No completed benchmark results to review.</p>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#e2e8f0] bg-[#f8fafc]">
                    <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Benchmark</th>
                    <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Model</th>
                    <th class="px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-[#64748b]">Auto Score</th>
                    <th class="px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-[#64748b]">Validated</th>
                    <th class="px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-[#64748b]">Status</th>
                    <th class="px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-[#64748b]">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1f5f9]">
                @foreach($results as $result)
                <tr>
                    <td class="px-4 py-3 text-sm text-[#1e293b]">{{ $result->benchmark->title ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-[#1e293b]">{{ $result->model->full_name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($result->quality_score)
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full {{ $result->quality_score >= 7 ? 'bg-[#10b981]/10 text-[#10b981]' : ($result->quality_score >= 4 ? 'bg-[#f59e0b]/10 text-[#f59e0b]' : 'bg-[#ef4444]/10 text-[#ef4444]') }} text-xs font-bold">
                            {{ number_format($result->quality_score, 1) }}
                        </span>
                        @else
                        <span class="text-xs text-[#94a3b8]">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-bold text-[#1e293b]">
                        {{ $result->validated_quality_score ? number_format($result->validated_quality_score, 1) : '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php $color = match($result->score_status->value) { 'validated' => 'bg-[#10b981]/10 text-[#10b981]', 'scored' => 'bg-[#2563eb]/10 text-[#2563eb]', default => 'bg-[#f59e0b]/10 text-[#f59e0b]' }; @endphp
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $color }}">{{ $result->score_status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('admin.scores.update', $result) }}" class="inline-flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="number" name="validated_quality_score" min="0" max="10" step="0.1"
                                value="{{ $result->validated_quality_score ?? $result->quality_score }}"
                                class="w-16 rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-2 py-1 text-center text-sm font-mono text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                            <button type="submit" class="rounded-lg bg-[#2563eb] px-3 py-1 text-xs font-semibold text-white hover:bg-[#1d4ed8] transition-colors">Validate</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    <div class="">{{ $results->links() }}</div>
</div>
@endsection
