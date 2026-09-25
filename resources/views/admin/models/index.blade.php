@extends('layouts.app')
@section('page-title', 'Model Presets')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-[#1e293b]">Model Presets</h3>
        <a href="{{ route('admin.models.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4ed8] transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Model
        </a>
    </div>

    @foreach($grouped as $provider => $models)
    <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-[#e2e8f0] bg-[#f8fafc]">
            <h4 class="text-sm font-bold uppercase tracking-wider text-[#64748b]">{{ $provider }}</h4>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#e2e8f0]">
                    <th class="px-5 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Model</th>
                    <th class="px-5 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Input $/1k</th>
                    <th class="px-5 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Output $/1k</th>
                    <th class="px-5 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-[#64748b]">Status</th>
                    <th class="px-5 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1f5f9]">
                @foreach($models as $model)
                <tr>
                    <td class="px-5 py-3">
                        <p class="text-sm font-medium text-[#1e293b]">{{ $model->display_name ?? $model->model_name }}</p>
                        <p class="text-[10px] text-[#94a3b8] font-mono">{{ $model->model_name }}</p>
                    </td>
                    <td class="px-5 py-3 text-right text-sm text-[#1e293b]">${{ number_format($model->input_price_per_1k_tokens, 4) }}</td>
                    <td class="px-5 py-3 text-right text-sm text-[#1e293b]">${{ number_format($model->output_price_per_1k_tokens, 4) }}</td>
                    <td class="px-5 py-3 text-center">
                        @if($model->is_active)
                        <span class="inline-flex rounded-full bg-[#10b981]/10 px-2.5 py-1 text-xs font-semibold text-[#10b981]">Active</span>
                        @else
                        <span class="inline-flex rounded-full bg-[#94a3b8]/10 px-2.5 py-1 text-xs font-semibold text-[#94a3b8]">Inactive</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right space-x-3">
                        <a href="{{ route('admin.models.edit', $model) }}" class="text-sm font-medium text-[#2563eb] hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.models.destroy', $model) }}" class="inline" onsubmit="return confirm('Delete this model?')">
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
</div>
@endsection
