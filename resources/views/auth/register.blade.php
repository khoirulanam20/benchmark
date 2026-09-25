@extends('layouts.app')
@section('page-title', 'Register')
@section('content')
<div class="w-full max-w-md">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold"><span class="text-[#2563eb]">AI</span> Benchmark Hub</h1>
        <p class="mt-2 text-sm text-[#64748b]">Create a new account</p>
    </div>
    <div class="rounded-xl border border-[#e2e8f0] bg-white p-8 shadow-sm">
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-[#1e293b]">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors">
                @error('name')
                <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-[#1e293b]">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors">
                @error('email')
                <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-[#1e293b]">Password</label>
                <input type="password" name="password" id="password" required
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors">
                @error('password')
                <p class="mt-1 text-xs text-[#ef4444]">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-[#1e293b]">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="mt-1 block w-full rounded-lg border border-[#e2e8f0] bg-[#f1f5f9] px-4 py-2.5 text-sm text-[#1e293b] placeholder-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/20 focus:outline-none transition-colors">
            </div>
            <button type="submit" class="w-full rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1d4ed8] active:bg-[#1e40af] transition-colors">
                Create Account
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-[#64748b]">
            Already have an account? <a href="{{ route('login') }}" class="font-medium text-[#2563eb] hover:underline">Sign in</a>
        </p>
    </div>
</div>
@endsection
