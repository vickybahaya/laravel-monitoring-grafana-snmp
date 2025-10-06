@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div>
    <h2 class="text-2xl font-semibold mb-2">Welcome back</h2>
    <p class="text-[#a0a0a0] mb-6">Enter your credentials to access your account</p>

    @if($errors->any())
    <div class="mb-6 bg-[#ef4444]/20 border border-[#ef4444] text-[#ef4444] px-4 py-3 rounded-lg text-sm">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium mb-2">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}"
                required 
                autofocus
                class="w-full bg-[#2a2a2a] border border-[#333] rounded-lg px-4 py-2.5 focus:outline-none focus:border-[#3b82f6] transition-colors"
                placeholder="admin@example.com"
            >
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-2">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                class="w-full bg-[#2a2a2a] border border-[#333] rounded-lg px-4 py-2.5 focus:outline-none focus:border-[#3b82f6] transition-colors"
                placeholder="••••••••"
            >
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="w-4 h-4 bg-[#2a2a2a] border-[#333] rounded">
                <span class="text-sm text-[#a0a0a0]">Remember me</span>
            </label>

            <a href="{{ route('password.request') }}" class="text-sm text-[#3b82f6] hover:text-[#2563eb]">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="w-full bg-[#3b82f6] hover:bg-[#2563eb] text-white font-medium py-2.5 rounded-lg transition-colors">
            Sign in
        </button>
    </form>

    <p class="text-center text-sm text-[#a0a0a0] mt-6">
        Don't have an account? 
        <a href="{{ route('register') }}" class="text-[#3b82f6] hover:text-[#2563eb]">Sign up</a>
    </p>
</div>
@endsection
