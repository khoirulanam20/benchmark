@extends('layouts.app')
@section('page-title', 'Scoring Settings')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h3 class="text-lg font-bold text-[#1e293b]">TypeSafe Jev (Scoring)</h3>
        <p class="mt-1 text-sm text-[#64748b]">API key global untuk menilai hasil benchmark. Model Jev mengembalikan skor terstruktur, bukan teks generatif.</p>
    </div>
    <div class="rounded-xl border border-[#e2e8f0] bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label for="typesafe_api_key" class="block text-sm font-medium text-[#1e293b]">TypeSafe API Key</label>
                <input type="password" name="typesafe_api_key" id="typesafe_api_key"
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                    placeholder="{{ $has_typesafe_key ? 'Leave empty to keep current key' : 'ts_...' }}">
                <p class="mt-1 text-[10px] text-[#94a3b8]">
                    Status: {{ $has_typesafe_key ? 'Configured' : 'Not set' }} · Dapatkan key di <a href="https://typesafe.ai" class="text-[#2563eb] hover:underline" target="_blank" rel="noopener">typesafe.ai</a>
                </p>
            </div>
            <div>
                <label for="typesafe_model" class="block text-sm font-medium text-[#1e293b]">Jev Model</label>
                <input type="text" name="typesafe_model" id="typesafe_model" value="{{ old('typesafe_model', $typesafe_model) }}" required
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm font-mono text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none"
                    placeholder="jev-latest">
                <p class="mt-1 text-[10px] text-[#94a3b8]">Contoh: <code class="font-mono">jev-latest</code>, <code class="font-mono">jev-1.13.0</code></p>
            </div>
            <div>
                <label for="typesafe_base_url" class="block text-sm font-medium text-[#1e293b]">API Base URL</label>
                <input type="url" name="typesafe_base_url" id="typesafe_base_url" value="{{ old('typesafe_base_url', $typesafe_base_url) }}" required
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm font-mono text-[#1e293b] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none">
            </div>
            <button type="submit" class="rounded-lg bg-[#2563eb] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8] transition-colors">Save Settings</button>
        </form>
    </div>
</div>
@endsection
