@extends('layouts.app')

@section('title', 'Add Router')

@section('content')
<div class="mb-6">
    <a href="{{ route('routers.index') }}" class="text-gray-400 hover:text-white inline-flex items-center mb-4">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Routers
    </a>
    <h1 class="text-2xl font-bold text-white">Add New Router</h1>
    <p class="text-gray-400 mt-1">Add a new MikroTik router to monitor</p>
</div>

<div class="card max-w-4xl">
    <form action="{{ route('routers.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
             Router Name 
            <div>
                <label for="nama_router" class="block text-sm font-medium text-gray-300 mb-2">
                    Router Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="nama_router" id="nama_router" 
                       value="{{ old('nama_router') }}" required
                       class="input-field @error('nama_router') border-danger @enderror"
                       placeholder="e.g., Router Jakarta">
                @error('nama_router')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             IP Address 
            <div>
                <label for="ip_address" class="block text-sm font-medium text-gray-300 mb-2">
                    IP Address <span class="text-danger">*</span>
                </label>
                <input type="text" name="ip_address" id="ip_address" 
                       value="{{ old('ip_address') }}" required
                       class="input-field @error('ip_address') border-danger @enderror"
                       placeholder="e.g., 192.168.1.1">
                @error('ip_address')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             Username 
            <div>
                <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                    Username <span class="text-danger">*</span>
                </label>
                <input type="text" name="username" id="username" 
                       value="{{ old('username', 'admin') }}" required
                       class="input-field @error('username') border-danger @enderror">
                @error('username')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             Password 
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                    Password <span class="text-danger">*</span>
                </label>
                <input type="password" name="password" id="password" required
                       class="input-field @error('password') border-danger @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             Port 
            <div>
                <label for="port" class="block text-sm font-medium text-gray-300 mb-2">
                    API Port <span class="text-danger">*</span>
                </label>
                <input type="number" name="port" id="port" 
                       value="{{ old('port', 8728) }}" required
                       class="input-field @error('port') border-danger @enderror">
                @error('port')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             Category 
            <div>
                <label for="router_category_id" class="block text-sm font-medium text-gray-300 mb-2">
                    Category <span class="text-danger">*</span>
                </label>
                <select name="router_category_id" id="router_category_id" required
                        class="input-field @error('router_category_id') border-danger @enderror">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('router_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                @error('router_category_id')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             Location 
            <div class="md:col-span-2">
                <label for="location" class="block text-sm font-medium text-gray-300 mb-2">
                    Location
                </label>
                <input type="text" name="location" id="location" 
                       value="{{ old('location') }}"
                       class="input-field @error('location') border-danger @enderror"
                       placeholder="e.g., Jakarta, Indonesia">
                @error('location')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             Latitude 
            <div>
                <label for="latitude" class="block text-sm font-medium text-gray-300 mb-2">
                    Latitude
                </label>
                <input type="text" name="latitude" id="latitude" 
                       value="{{ old('latitude') }}"
                       class="input-field @error('latitude') border-danger @enderror"
                       placeholder="e.g., -6.2088">
                @error('latitude')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             Longitude 
            <div>
                <label for="longitude" class="block text-sm font-medium text-gray-300 mb-2">
                    Longitude
                </label>
                <input type="text" name="longitude" id="longitude" 
                       value="{{ old('longitude') }}"
                       class="input-field @error('longitude') border-danger @enderror"
                       placeholder="e.g., 106.8456">
                @error('longitude')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

             Active Status 
            <div class="md:col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-primary bg-dark-lighter border-dark-border rounded focus:ring-primary focus:ring-2">
                    <span class="ml-2 text-sm text-gray-300">Active (Enable monitoring)</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('routers.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Router
            </button>
        </div>
    </form>
</div>
@endsection
