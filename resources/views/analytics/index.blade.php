@extends('layouts.app')
@section('page-title', 'Comparative Analytics')

@section('content')
<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64748b]">Benchmarks Analyzed</p>
            <p class="mt-2 text-2xl font-bold text-[#1e293b]">{{ $overallStats['total_benchmarks'] }}</p>
        </div>
        <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64748b]">Best Quality</p>
            <p class="mt-2 text-lg font-bold text-[#10b981]">{{ $overallStats['best_model'] }}</p>
        </div>
        <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64748b]">Fastest</p>
            <p class="mt-2 text-lg font-bold text-[#2563eb]">{{ $overallStats['fastest_model'] }}</p>
        </div>
        <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64748b]">Cheapest</p>
            <p class="mt-2 text-lg font-bold text-[#f59e0b]">{{ $overallStats['cheapest_model'] }}</p>
        </div>
    </div>

    {{-- Model Comparison Table --}}
    <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-[#e2e8f0] bg-[#f8fafc]">
            <h4 class="text-sm font-bold text-[#1e293b]">Model Performance Comparison</h4>
        </div>
        @if($modelMetrics->isEmpty())
        <div class="py-12 text-center">
            <p class="text-sm text-[#64748b]">No completed benchmarks yet. Run a benchmark to see analytics.</p>
            <a href="{{ route('benchmarks.create') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4ed8]">
                Run Benchmark
            </a>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#e2e8f0]">
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Model</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Evaluations</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Avg Score</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Avg Latency</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Avg Cost</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Total Cost</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Total Tokens</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1f5f9]">
                @foreach($modelMetrics as $metric)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3">
                        <p class="text-sm font-semibold text-[#1e293b]">{{ $metric['model']->full_name }}</p>
                        <p class="text-[10px] text-[#94a3b8]">{{ $metric['model']->provider }}</p>
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-[#64748b]">{{ $metric['count'] }}</td>
                    <td class="px-4 py-3 text-right">
                        @php $scoreColor = $metric['avg_score'] >= 7 ? 'text-[#10b981]' : ($metric['avg_score'] >= 4 ? 'text-[#f59e0b]' : 'text-[#ef4444]'); @endphp
                        <span class="text-sm font-bold {{ $scoreColor }}">{{ $metric['avg_score'] }}/10</span>
                        {{-- Score Bar --}}
                        <div class="mt-1 h-1.5 w-full rounded-full bg-[#f1f5f9]">
                            <div class="h-1.5 rounded-full {{ $metric['avg_score'] >= 7 ? 'bg-[#10b981]' : ($metric['avg_score'] >= 4 ? 'bg-[#f59e0b]' : 'bg-[#ef4444]') }}" style="width: {{ $metric['avg_score'] * 10 }}%"></div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-mono text-[#1e293b]">{{ $metric['avg_latency'] }}s</td>
                    <td class="px-4 py-3 text-right text-sm font-mono text-[#1e293b]">${{ number_format($metric['avg_cost'], 6) }}</td>
                    <td class="px-4 py-3 text-right text-sm font-mono text-[#1e293b]">${{ number_format($metric['total_cost'], 4) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-[#64748b]">{{ number_format($metric['total_tokens']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Latency Chart (CSS-only bar chart) --}}
    @if($modelMetrics->isNotEmpty())
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Latency Comparison --}}
        <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm">
            <h4 class="text-sm font-bold text-[#1e293b] mb-4">Latency Comparison (seconds)</h4>
            <div class="space-y-3">
                @php $maxLatency = $modelMetrics->max('avg_latency') ?: 1; @endphp
                @foreach($modelMetrics->sortBy('avg_latency') as $metric)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-[#64748b]">{{ $metric['model']->display_name ?? $metric['model']->model_name }}</span>
                        <span class="text-xs font-bold text-[#1e293b] font-mono">{{ $metric['avg_latency'] }}s</span>
                    </div>
                    <div class="h-4 w-full rounded bg-[#f1f5f9] overflow-hidden">
                        <div class="h-4 rounded bg-[#2563eb] transition-all" style="width: {{ ($metric['avg_latency'] / $maxLatency) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Cost Comparison --}}
        <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm">
            <h4 class="text-sm font-bold text-[#1e293b] mb-4">Average Cost Comparison ($)</h4>
            <div class="space-y-3">
                @php $maxCost = $modelMetrics->max('avg_cost') ?: 1; @endphp
                @foreach($modelMetrics->sortBy('avg_cost') as $metric)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-[#64748b]">{{ $metric['model']->display_name ?? $metric['model']->model_name }}</span>
                        <span class="text-xs font-bold text-[#1e293b] font-mono">${{ number_format($metric['avg_cost'], 6) }}</span>
                    </div>
                    <div class="h-4 w-full rounded bg-[#f1f5f9] overflow-hidden">
                        <div class="h-4 rounded bg-[#f59e0b] transition-all" style="width: {{ ($metric['avg_cost'] / $maxCost) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quality Score Chart --}}
        <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm lg:col-span-2">
            <h4 class="text-sm font-bold text-[#1e293b] mb-4">Quality Score Comparison</h4>
            <div class="flex items-end gap-4 h-48">
                @foreach($modelMetrics->sortByDesc('avg_score') as $metric)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <span class="text-xs font-bold text-[#1e293b]">{{ $metric['avg_score'] }}</span>
                    <div class="w-full rounded-t {{ $metric['avg_score'] >= 7 ? 'bg-[#10b981]' : ($metric['avg_score'] >= 4 ? 'bg-[#f59e0b]' : 'bg-[#ef4444]') }} transition-all" style="height: {{ ($metric['avg_score'] / 10) * 100 }}%"></div>
                    <span class="text-[10px] text-[#64748b] text-center leading-tight">{{ Str::limit($metric['model']->display_name ?? $metric['model']->model_name, 12) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
