@extends('layouts.app')
@section('page-title', 'API Keys')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#1e293b]">API Key Management</h3>
        <p class="mt-1 text-sm text-[#64748b]">Manage your API keys for LLM providers. Keys are stored encrypted.</p>
    </div>

    {{-- Add New Key --}}
    <div class="rounded-xl border border-[#e2e8f0] bg-white p-6 shadow-sm">
        <h4 class="text-sm font-bold text-[#1e293b] mb-4">Add / Update API Key</h4>
        <form method="POST" action="{{ route('api-keys.store') }}" class="flex gap-4 items-end">
            @csrf
            <div class="flex-1">
                <label for="provider_name" class="block text-xs font-medium text-[#64748b] mb-1">Provider</label>
                <select name="provider_name" id="provider_name" class="block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
                    @foreach($providers as $provider)
                    <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-[2]">
                <label for="api_key" class="block text-xs font-medium text-[#64748b] mb-1">API Key</label>
                <input type="password" name="api_key" id="api_key" required
                    class="block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                    placeholder="sk-...">
            </div>
            <button type="submit" class="rounded-lg bg-[#2563eb] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8] transition-colors whitespace-nowrap">
                Save Key
            </button>
        </form>
    </div>

    {{-- Existing Keys --}}
    <div class="rounded-xl border border-[#e2e8f0] bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-[#e2e8f0] bg-[#f8fafc]">
            <h4 class="text-sm font-bold text-[#1e293b]">Your API Keys</h4>
        </div>
        @if($apiKeys->isEmpty())
        <div class="py-12 text-center">
            <svg class="mx-auto h-10 w-10 text-[#94a3b8]" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
            <p class="mt-3 text-sm text-[#64748b]">No API keys configured yet.</p>
            <p class="mt-1 text-xs text-[#94a3b8]">Add a key above to start running benchmarks.</p>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-[#e2e8f0]">
                    <th class="px-5 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Provider</th>
                    <th class="px-5 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Status</th>
                    <th class="px-5 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-[#64748b]">Updated</th>
                    <th class="px-5 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-[#64748b]">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1f5f9]">
                @foreach($apiKeys as $key)
                <tr>
                    <td class="px-5 py-3 text-sm font-medium text-[#1e293b]">{{ ucfirst($key->provider_name) }}</td>
                    <td class="px-5 py-3">
                        @if($key->isExpired())
                        <span class="inline-flex rounded-full bg-[#ef4444]/10 px-2.5 py-1 text-xs font-semibold text-[#ef4444]">Expired</span>
                        @else
                        <span class="inline-flex rounded-full bg-[#10b981]/10 px-2.5 py-1 text-xs font-semibold text-[#10b981]">Active</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-[#64748b]">{{ $key->updated_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        <form method="POST" action="{{ route('api-keys.destroy', $key) }}" onsubmit="return confirm('Delete this API key?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-medium text-[#ef4444] hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
