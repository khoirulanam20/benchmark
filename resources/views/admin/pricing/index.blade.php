@extends('layouts.app')
@section('page-title', 'Pricing Management')

@section('content')
<div class="space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#1e293b]">Token Pricing Management</h3>
        <p class="mt-1 text-sm text-[#64748b]">Update per-token pricing for each LLM model. Changes apply to future benchmark cost calculations.</p>
    </div>

    <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#e2e8f0] bg-[#f8fafc]">
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Model</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Provider</th>
                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Input $/1k</th>
                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Output $/1k</th>
                    <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#64748b]">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1f5f9]">
                @foreach($models as $model)
                <tr>
                    <form method="POST" action="{{ route('admin.pricing.update', $model) }}">
                        @csrf @method('PUT')
                        <td class="px-5 py-3">
                            <p class="text-sm font-medium text-[#1e293b]">{{ $model->full_name }}</p>
                            <p class="text-[10px] text-[#94a3b8] font-mono">{{ $model->model_name }}</p>
                        </td>
                        <td class="px-5 py-3 text-sm text-[#64748b]">{{ $model->provider }}</td>
                        <td class="px-5 py-3 text-right">
                            <input type="number" name="input_price_per_1k_tokens" value="{{ $model->input_price_per_1k_tokens }}" step="0.000001" min="0" required
                                class="w-28 rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-3 py-1.5 text-right text-sm font-mono text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                        </td>
                        <td class="px-5 py-3 text-right">
                            <input type="number" name="output_price_per_1k_tokens" value="{{ $model->output_price_per_1k_tokens }}" step="0.000001" min="0" required
                                class="w-28 rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-3 py-1.5 text-right text-sm font-mono text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                        </td>
                        <td class="px-5 py-3 text-center">
                            <button type="submit" class="rounded-lg bg-[#2563eb] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#1d4ed8] transition-colors">Save</button>
                        </td>
                    </form>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="">{{ $models->links() }}</div>
</div>
@endsection
