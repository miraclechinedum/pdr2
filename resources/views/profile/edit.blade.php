{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Update Profile</h1>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $msg)
        <li>{{ $msg }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow" x-data="{ tab: 'basic' }">
    <div class="mb-6 border-b">
        <button @click="tab = 'basic'" :class="tab==='basic'
                    ? 'border-b-2 border-blue-600 text-blue-600'
                    : 'text-gray-600 hover:text-gray-800'" class="py-2 px-4 font-medium">
            Basic Information
        </button>
        <button @click="tab = 'password'" :class="tab==='password'
                    ? 'border-b-2 border-blue-600 text-blue-600'
                    : 'text-gray-600 hover:text-gray-800'" class="py-2 px-4 font-medium">
            Change Password
        </button>
    </div>

    {{-- Basic Information Form --}}
    <form x-show="tab==='basic'" x-cloak action="{{ route('profile.update') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1">Name</label>
            <input name="name" type="text" class="w-full form-input" value="{{ old('name', $user->name) }}" required>
            @error('name')
            <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">Email</label>
            <input name="email" type="email" class="w-full form-input" value="{{ old('email', $user->email) }}"
                required>
            @error('email')
            <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">Phone Number</label>
            <input name="phone_number" type="text" class="w-full form-input"
                value="{{ old('phone_number', $user->phone_number) }}">
            @error('phone_number')
            <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Save Changes
            </button>
        </div>
    </form>

    {{-- Change Password Form --}}
    <form x-show="tab==='password'" x-cloak action="{{ route('profile.password.update') }}" method="POST"
        class="space-y-4">
        @csrf

        {{-- <div class="grid md:grid-cols-2 gap-4"> --}}
            <div>
                <label class="block mb-1">New Password</label>
                <input name="password" type="password" class="w-full form-input" required>
                @error('password')
                <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-1">Confirm Password</label>
                <input name="password_confirmation" type="password" class="w-full form-input" required>
            </div>
            {{--
        </div> --}}

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                Change Password
            </button>
        </div>
    </form>
</div>
@endsection