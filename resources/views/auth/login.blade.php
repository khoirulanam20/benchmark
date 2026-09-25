@extends('layouts.app')
@section('page-title', 'Login')
@section('content')
<div class="w-full max-w-md">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold"><span class="text-[#2563eb]">AI</span> Benchmark Hub</h1>
        <p class="mt-2 text-sm text-[#64748b]">Sign in to your account</p>
    </div>
    <div class="rounded-xl border border-[#e2e8f0] bg-white p-8 shadow-sm">
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-[#1e293b]">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors"
                    placeholder="you@example.com">
                @error('email')
                <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-[#1e293b]">Password</label>
                <input type="password" name="password" id="password" required
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors"
                    placeholder="••••••••">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="h-4 w-4 rounded border-[#e2e8f0] text-[#2563eb] focus:ring-[#2563eb]">
                <label for="remember" class="text-sm text-[#64748b]">Remember me</label>
            </div>
            <button type="submit" class="w-full rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8] active:bg-[#1e40af] transition-colors focus:ring-2 focus:ring-[#2563eb]/50 focus:outline-none">
                Sign In
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-[#64748b]">
            Don't have an account? <a href="{{ route('register') }}" class="font-medium text-[#2563eb] hover:underline">Register</a>
        </p>
    </div>
    <div class="mt-4 rounded-lg border border-[#e2e8f0] bg-white p-4 text-xs text-[#64748b]">
        <p class="font-semibold text-[#1e293b] mb-1">Demo Accounts:</p>
        <p>Admin: admin@aibench.local / password</p>
        <p>User: user@aibench.local / password</p>
    </div>
</div>
@endsection
