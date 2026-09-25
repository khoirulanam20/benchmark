@extends('layouts.app')
@section('page-title', 'Benchmark Result')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <nav class="flex items-center gap-2 text-sm text-[#64748b]">
        <a href="{{ route('benchmarks.index') }}" class="font-medium hover:text-[#2563eb]">History</a>
        <span>/</span>
        <span class="truncate text-[#1e293b]">{{ $benchmark->title }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('benchmarks.index') }}" class="mb-2 inline-flex items-center gap-1 text-sm font-medium text-[#64748b] hover:text-[#2563eb]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Back to history
            </a>
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

    {{-- Scoreboard --}}
    <div class="rounded-xl border border-[#e2e8f0] bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h4 class="text-lg font-bold text-[#1e293b]">Scoreboard</h4>
                <p class="mt-1 text-xs text-[#64748b]">Peringkat berdasarkan skor kualitas, lalu latency terendah.</p>
            </div>
            <button type="button" id="compare-selected" disabled
                class="rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white opacity-50 transition hover:bg-[#1d4ed8] disabled:cursor-not-allowed">
                Compare selected (0)
            </button>
        </div>
        <div id="scoreboard-list" class="mt-4 space-y-2"></div>
    </div>

    {{-- Results List --}}
    <div class="flex items-center justify-between">
        <h4 class="text-lg font-bold text-[#1e293b]">Model Results</h4>
        <span class="text-xs text-[#64748b]">{{ $benchmark->results->count() }} model</span>
    </div>
    @if($benchmark->results->isEmpty())
    <p class="text-sm text-[#64748b]">No results yet.</p>
    @else
    <div id="results-list" class="space-y-3">
        @foreach($benchmark->results as $result)
        @php
            $benchmarkRunning = in_array($benchmark->status->value, ['pending', 'processing'], true);
            $waitingForResponse = $benchmarkRunning && $result->latency_seconds === null && ! $result->error_message;
            $hasOutput = filled($result->output_content);
            $showMetrics = $hasOutput || $result->error_message || $result->latency_seconds !== null;
        @endphp
        <div id="result-card-{{ $result->id }}" class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden" data-result-id="{{ $result->id }}">
            {{-- Model Header --}}
            <div class="flex items-center justify-between border-b border-[#e2e8f0] bg-[#f8fafc] px-5 py-3">
                <div>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" class="result-selector h-4 w-4 rounded border-[#cbd5e1] text-[#2563eb] focus:ring-[#2563eb]" value="{{ $result->id }}">
                        <span>
                            <p class="text-sm font-bold text-[#1e293b]">{{ $result->model->full_name }}</p>
                            <p class="text-[10px] text-[#94a3b8]">{{ $result->model->model_name }}</p>
                        </span>
                    </label>
                </div>
                <div id="result-score-badge-{{ $result->id }}">
                @if($result->effective_score)
                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $result->effective_score >= 7 ? 'bg-[#10b981]/10 text-[#10b981]' : ($result->effective_score >= 4 ? 'bg-[#f59e0b]/10 text-[#f59e0b]' : 'bg-[#ef4444]/10 text-[#ef4444]') }}">
                    <span class="text-sm font-bold">{{ number_format($result->effective_score, 1) }}</span>
                </div>
                @endif
                </div>
            </div>

            <details class="group">
                <summary class="flex cursor-pointer list-none items-center justify-between border-b border-[#e2e8f0] px-5 py-3 text-sm font-semibold text-[#334155]">
                    <span>Hasil generate</span>
                    <span class="text-xs text-[#64748b] transition group-open:rotate-180">⌄</span>
                </summary>
                {{-- Output --}}
                <div class="p-5" id="result-output-{{ $result->id }}">
                @if($result->error_message)
                <div class="rounded-lg border border-[#ef4444]/20 bg-[#ef4444]/5 p-3">
                    <p class="text-xs font-semibold text-[#ef4444]">Error</p>
                    <p class="mt-1 text-xs text-[#64748b]">{{ $result->error_message }}</p>
                </div>
                @elseif($hasOutput)
                <div class="max-h-64 overflow-y-auto rounded-lg bg-[#f8fafc] p-4">
                    <pre class="whitespace-pre-wrap text-sm font-mono text-[#1e293b]">{{ $result->output_content }}</pre>
                </div>
                @elseif($waitingForResponse)
                <div class="flex items-center gap-2 py-8 justify-center">
                    <svg class="h-5 w-5 animate-spin text-[#2563eb]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-sm text-[#64748b]">Menunggu respons…</span>
                </div>
                @else
                <div class="rounded-lg border border-[#f59e0b]/20 bg-[#f59e0b]/5 p-4 text-center">
                    <p class="text-sm font-semibold text-[#f59e0b]">Respons kosong</p>
                    <p class="mt-1 text-xs text-[#64748b]">API merespons tanpa teks. Periksa Base URL, nama model, dan format respons endpoint.</p>
                </div>
                @endif
                </div>

                {{-- Metrics Footer --}}
                <div id="result-metrics-{{ $result->id }}" class="grid grid-cols-3 divide-x divide-[#e2e8f0] border-t border-[#e2e8f0] bg-[#f8fafc] {{ $showMetrics ? '' : 'hidden' }}">
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
            </details>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Comparison Table --}}
    @if($benchmark->results->count() > 1 && $benchmark->results->contains(fn ($r) => $r->latency_seconds !== null || filled($r->output_content)))
    <div id="metrics-comparison" class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
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
            <tbody id="metrics-comparison-body" class="divide-y divide-[#f1f5f9]">
                @foreach($benchmark->results->sortByDesc('effective_score') as $result)
                <tr data-result-row="{{ $result->id }}">
                    <td class="px-4 py-2.5 text-sm font-medium text-[#1e293b]">{{ $result->model->full_name }}</td>
                    <td class="px-4 py-2.5 text-right text-sm font-bold {{ ($result->effective_score ?? 0) >= 7 ? 'text-[#10b981]' : (($result->effective_score ?? 0) >= 4 ? 'text-[#f59e0b]' : 'text-[#ef4444]') }}" data-metric-score>
                        {{ $result->effective_score ? number_format($result->effective_score, 1) . '/10' : '-' }}
                    </td>
                    <td class="px-4 py-2.5 text-right text-sm text-[#1e293b]" data-metric-latency>{{ $result->latency_seconds ? number_format($result->latency_seconds, 2) . 's' : '-' }}</td>
                    <td class="px-4 py-2.5 text-right text-sm text-[#1e293b]" data-metric-cost>${{ number_format($result->cost, 6) }}</td>
                    <td class="px-4 py-2.5 text-right text-sm text-[#64748b]" data-metric-tokens>{{ number_format($result->prompt_tokens + $result->completion_tokens) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@php
    $shouldPoll = in_array($benchmark->status->value, ['pending', 'processing'], true)
        || $benchmark->results->contains(fn ($r) => filled($r->output_content) && $r->score_status->value === 'pending');

    $initialResults = $benchmark->results->map(fn ($result) => [
        'id' => $result->id,
        'model' => $result->model->full_name,
        'provider' => $result->model->provider,
        'output_content' => $result->output_content,
        'error_message' => $result->error_message,
        'quality_score' => $result->effective_score,
        'cost' => (float) $result->cost,
        'latency_seconds' => $result->latency_seconds,
        'prompt_tokens' => $result->prompt_tokens,
        'completion_tokens' => $result->completion_tokens,
        'score_status' => $result->score_status->value,
    ])->values();
@endphp
@push('scripts')
<script>
(function () {
    const statusUrl = @json(route('benchmarks.status', $benchmark));
    const shouldPoll = @json($shouldPoll);
    let currentResults = @json($initialResults);

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function scoreColor(score) {
        if (score >= 7) return 'text-[#10b981]';
        if (score >= 4) return 'text-[#f59e0b]';
        return 'text-[#ef4444]';
    }

    function scoreBadgeHtml(score) {
        const bg = score >= 7 ? 'bg-[#10b981]/10 text-[#10b981]' : (score >= 4 ? 'bg-[#f59e0b]/10 text-[#f59e0b]' : 'bg-[#ef4444]/10 text-[#ef4444]');
        return `<div class="flex h-10 w-10 items-center justify-center rounded-full ${bg}"><span class="text-sm font-bold">${Number(score).toFixed(1)}</span></div>`;
    }

    function renderScoreboard(results) {
        const list = document.getElementById('scoreboard-list');
        if (!list) return;

        const ranked = [...results].sort((a, b) => {
            const scoreDiff = (Number(b.quality_score) || 0) - (Number(a.quality_score) || 0);
            if (scoreDiff !== 0) return scoreDiff;
            return (Number(a.latency_seconds) || Number.MAX_SAFE_INTEGER) - (Number(b.latency_seconds) || Number.MAX_SAFE_INTEGER);
        });

        list.innerHTML = ranked.map((result, index) => {
            const score = result.quality_score ? `${Number(result.quality_score).toFixed(1)}/10` : '-';
            const latency = result.latency_seconds != null ? `${Number(result.latency_seconds).toFixed(2)}s` : '-';
            const medal = index < 3 ? ['1st', '2nd', '3rd'][index] : `#${index + 1}`;
            return `<div class="flex items-center gap-3 rounded-lg bg-[#f8fafc] px-3 py-2">
                <span class="w-8 text-center text-xs font-bold text-[#64748b]">${medal}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[#1e293b]">${escapeHtml(result.model)}</p>
                    <p class="text-[10px] text-[#94a3b8]">${escapeHtml(result.provider || '')}</p>
                </div>
                <span class="text-sm font-bold ${scoreColor(Number(result.quality_score) || 0)}">${score}</span>
                <span class="hidden w-16 text-right text-xs text-[#64748b] sm:block">${latency}</span>
            </div>`;
        }).join('');
    }

    function updateCompareButton() {
        const selected = document.querySelectorAll('.result-selector:checked').length;
        const button = document.getElementById('compare-selected');
        if (!button) return;
        button.textContent = `Compare selected (${selected})`;
        button.disabled = selected < 2;
        button.classList.toggle('opacity-50', selected < 2);
    }

    function openCompare() {
        const selectedIds = [...document.querySelectorAll('.result-selector:checked')].map((input) => Number(input.value));
        if (selectedIds.length < 2) return;

        const selected = currentResults.filter((result) => selectedIds.includes(Number(result.id)));
        let panel = document.getElementById('compare-panel');
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'compare-panel';
            document.body.appendChild(panel);
        }
        panel.className = 'fixed inset-0 z-50 overflow-y-auto bg-[#0f172a]/60 p-4 sm:p-8';
        panel.innerHTML = `<div class="mx-auto max-w-6xl rounded-xl bg-[#f8fafc] shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#e2e8f0] bg-white px-5 py-4">
                <div>
                    <h3 class="text-lg font-bold text-[#1e293b]">Compare hasil generate</h3>
                    <p class="mt-1 text-xs text-[#64748b]">Prompt yang sama, dibandingkan berdampingan.</p>
                </div>
                <button type="button" id="close-compare" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#64748b] hover:bg-[#f1f5f9]">Tutup</button>
            </div>
            <div class="grid gap-4 p-5 md:grid-cols-2">${selected.map((result) => `
                <article class="overflow-hidden rounded-lg border border-[#e2e8f0] bg-white">
                    <div class="border-b border-[#e2e8f0] bg-[#f8fafc] px-4 py-3">
                        <p class="text-sm font-bold text-[#1e293b]">${escapeHtml(result.model)}</p>
                        <div class="mt-2 flex flex-wrap gap-3 text-xs text-[#64748b]">
                            <span>Score: <strong>${result.quality_score ? Number(result.quality_score).toFixed(1) + '/10' : '-'}</strong></span>
                            <span>Latency: <strong>${result.latency_seconds != null ? Number(result.latency_seconds).toFixed(2) + 's' : '-'}</strong></span>
                            <span>Cost: <strong>$${Number(result.cost || 0).toFixed(6)}</strong></span>
                        </div>
                    </div>
                    <div class="max-h-[55vh] overflow-y-auto p-4">
                        ${result.error_message
                            ? `<p class="rounded-lg bg-[#ef4444]/5 p-3 text-sm text-[#ef4444]">${escapeHtml(result.error_message)}</p>`
                            : `<pre class="whitespace-pre-wrap text-sm leading-6 text-[#1e293b]">${escapeHtml(result.output_content || 'Respons belum tersedia.')}</pre>`}
                    </div>
                </article>`).join('')}</div>
        </div>`;
        document.getElementById('close-compare').addEventListener('click', () => panel.remove());
    }

    function renderOutput(result, isRunning) {
        if (result.error_message) {
            return `<div class="rounded-lg border border-[#ef4444]/20 bg-[#ef4444]/5 p-3"><p class="text-xs font-semibold text-[#ef4444]">Error</p><p class="mt-1 text-xs text-[#64748b]">${escapeHtml(result.error_message)}</p></div>`;
        }
        if (result.output_content) {
            return `<div class="max-h-64 overflow-y-auto rounded-lg bg-[#f8fafc] p-4"><pre class="whitespace-pre-wrap text-sm font-mono text-[#1e293b]">${escapeHtml(result.output_content)}</pre></div>`;
        }
        if (isRunning && result.latency_seconds === null) {
            return `<div class="flex items-center gap-2 py-8 justify-center"><svg class="h-5 w-5 animate-spin text-[#2563eb]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg><span class="text-sm text-[#64748b]">Menunggu respons…</span></div>`;
        }
        return `<div class="rounded-lg border border-[#f59e0b]/20 bg-[#f59e0b]/5 p-4 text-center"><p class="text-sm font-semibold text-[#f59e0b]">Respons kosong</p><p class="mt-1 text-xs text-[#64748b]">API merespons tanpa teks.</p></div>`;
    }

    function renderMetrics(result) {
        const hasMetrics = result.output_content || result.error_message || result.latency_seconds !== null;
        const el = document.getElementById(`result-metrics-${result.id}`);
        if (!el) return;
        if (!hasMetrics) {
            el.classList.add('hidden');
            return;
        }
        el.classList.remove('hidden');
        const lat = result.latency_seconds != null ? `${Number(result.latency_seconds).toFixed(2)}s` : '-';
        const tokens = (result.prompt_tokens || 0) + (result.completion_tokens || 0);
        el.innerHTML = `
            <div class="p-3 text-center"><p class="text-[10px] font-bold uppercase tracking-wider text-[#64748b]">Latency</p><p class="mt-1 text-sm font-bold text-[#1e293b]">${lat}</p></div>
            <div class="p-3 text-center"><p class="text-[10px] font-bold uppercase tracking-wider text-[#64748b]">Cost</p><p class="mt-1 text-sm font-bold text-[#1e293b]">$${Number(result.cost || 0).toFixed(6)}</p></div>
            <div class="p-3 text-center"><p class="text-[10px] font-bold uppercase tracking-wider text-[#64748b]">Tokens</p><p class="mt-1 text-sm font-bold text-[#1e293b]">${tokens.toLocaleString()}</p></div>`;
        el.className = 'grid grid-cols-3 divide-x divide-[#e2e8f0] border-t border-[#e2e8f0] bg-[#f8fafc]';
    }

    function updateBenchmarkUi(data) {
        currentResults = data.results || [];
        renderScoreboard(currentResults);
        const isRunning = data.status === 'pending' || data.status === 'processing';
        const badge = document.getElementById('status-badge');
        if (badge && data.status_label) {
            const colors = { completed: 'bg-[#10b981]/10 text-[#10b981]', processing: 'bg-[#2563eb]/10 text-[#2563eb]', failed: 'bg-[#ef4444]/10 text-[#ef4444]' };
            const spin = data.status === 'processing' ? '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>' : '';
            badge.className = `inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-semibold ${colors[data.status] || 'bg-[#f59e0b]/10 text-[#f59e0b]'}`;
            badge.innerHTML = spin + escapeHtml(data.status_label);
        }

        (data.results || []).forEach((result) => {
            const outputEl = document.getElementById(`result-output-${result.id}`);
            if (outputEl) outputEl.innerHTML = renderOutput(result, isRunning);
            renderMetrics(result);

            const scoreWrap = document.getElementById(`result-score-badge-${result.id}`);
            if (scoreWrap && result.quality_score) {
                scoreWrap.innerHTML = scoreBadgeHtml(result.quality_score);
            }

            const row = document.querySelector(`tr[data-result-row="${result.id}"]`);
            if (row) {
                const scoreCell = row.querySelector('[data-metric-score]');
                const latCell = row.querySelector('[data-metric-latency]');
                const costCell = row.querySelector('[data-metric-cost]');
                const tokCell = row.querySelector('[data-metric-tokens]');
                if (scoreCell) {
                    const s = result.quality_score;
                    scoreCell.textContent = s ? `${Number(s).toFixed(1)}/10` : '-';
                    scoreCell.className = `px-4 py-2.5 text-right text-sm font-bold ${scoreColor(s || 0)}`;
                }
                if (latCell) latCell.textContent = result.latency_seconds != null ? `${Number(result.latency_seconds).toFixed(2)}s` : '-';
                if (costCell) costCell.textContent = `$${Number(result.cost || 0).toFixed(6)}`;
                if (tokCell) tokCell.textContent = ((result.prompt_tokens || 0) + (result.completion_tokens || 0)).toLocaleString();
            }
        });
    }

    document.querySelectorAll('.result-selector').forEach((input) => input.addEventListener('change', updateCompareButton));
    document.getElementById('compare-selected')?.addEventListener('click', openCompare);
    renderScoreboard(currentResults);
    updateCompareButton();

    if (!shouldPoll) return;

    const poll = setInterval(async () => {
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();
            updateBenchmarkUi(data);
            if (data.finished && data.scoring_complete) {
                clearInterval(poll);
            }
        } catch (e) {}
    }, 2000);
})();
</script>
@endpush
@endsection
