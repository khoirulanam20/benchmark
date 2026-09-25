@extends('layouts.app')
@section('page-title', 'Benchmark History')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-[#1e293b]">Benchmark History</h3>
            <p class="mt-1 text-sm text-[#64748b]">All benchmarks you have run.</p>
        </div>
        <a href="{{ route('benchmarks.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4ed8] transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Benchmark
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#e2e8f0] bg-white shadow-sm">
        @if($benchmarks->isEmpty())
        <div class="flex flex-col items-center justify-center py-16">
            <svg class="h-12 w-12 text-[#94a3b8]" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
            <p class="mt-4 text-sm font-medium text-[#64748b]">No benchmarks yet</p>
            <p class="mt-1 text-xs text-[#94a3b8]">Run your first benchmark to get started</p>
            <a href="{{ route('benchmarks.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4ed8] transition-colors">
                Run Benchmark
            </a>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#e2e8f0] bg-[#f8fafc]">
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Models</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Avg Score</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1f5f9]">
                @foreach($benchmarks as $bm)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3.5 text-sm font-medium text-[#1e293b]">{{ $bm->title }}</td>
                    <td class="px-4 py-3.5">
                        @php $statusColor = match($bm->status->value) { 'completed' => 'bg-[#10b981]/10 text-[#10b981]', 'processing' => 'bg-[#2563eb]/10 text-[#2563eb]', 'failed' => 'bg-[#ef4444]/10 text-[#ef4444]', default => 'bg-[#f59e0b]/10 text-[#f59e0b]' }; @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColor }}">
                            @if($bm->status->value === 'processing')
                            <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            @endif
                            {{ $bm->status->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-[#64748b]">{{ $bm->results_count }}</td>
                    <td class="px-4 py-3.5 text-sm font-semibold text-[#1e293b]">{{ $bm->results_avg_quality_score ? number_format($bm->results_avg_quality_score, 1) . '/10' : '-' }}</td>
                    <td class="px-4 py-3.5 text-sm text-[#1e293b]">${{ number_format($bm->results_sum_cost ?? 0, 4) }}</td>
                    <td class="px-4 py-3.5 text-sm text-[#94a3b8]">{{ $bm->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('benchmarks.show', $bm) }}" class="text-sm font-medium text-[#2563eb] hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <div>{{ $benchmarks->links() }}</div>
</div>
@endsection
