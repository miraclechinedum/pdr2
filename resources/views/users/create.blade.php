@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6 bg-white p-6 rounded-[12px]">
    <h1 class="text-2xl font-bold">Add New User</h1>
</div>

<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
        {{ session('error') }}
    </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $msg)
            <li>{{ $msg }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    @include('users._form', ['user' => null])
</div>

@endsection