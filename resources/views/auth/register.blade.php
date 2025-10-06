@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div>
    <h2 class="text-2xl font-semibold mb-2">Create an account</h2>
    <p class="text-[#a0a0a0] mb-6">Get started with NetMonitor</p>

    @if($errors->any())
    <div class="mb-6 bg-[#ef4444]/20 border border-[#ef4444] text-[#ef4444] px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium mb-2">Full Name</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}"
                required 
                autofocus
                class="w-full bg-[#2a2a2a] border border-[#333] rounded-lg px-4 py-2.5 focus:outline-none focus:border-[#3b82f6] transition-colors"
                placeholder="John Doe"
            >
        </div>

        <div>
            <label for="email" class="block text-sm font-medium mb-2">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}"
                required
                class="w-full bg-[#2a2a2a] border border-[#333] rounded-lg px-4 py-2.5 focus:outline-none focus:border-[#3b82f6] transition-colors"
                placeholder="john@example.com"
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

        <div>
            <label for="password_confirmation" class="block text-sm font-medium mb-2">Confirm Password</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                required
                class="w-full bg-[#2a2a2a] border border-[#333] rounded-lg px-4 py-2.5 focus:outline-none focus:border-[#3b82f6] transition-colors"
                placeholder="••••••••"
            >
        </div>

        <button type="submit" class="w-full bg-[#3b82f6] hover:bg-[#2563eb] text-white font-medium py-2.5 rounded-lg transition-colors">
            Create account
        </button>
    </form>

    <p class="text-center text-sm text-[#a0a0a0] mt-6">
        Already have an account? 
        <a href="{{ route('login') }}" class="text-[#3b82f6] hover:text-[#2563eb]">Sign in</a>
    </p>
</div>
@endsection
