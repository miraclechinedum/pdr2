@extends('layouts.app')

@section('content')
<div class="grid gap-6 md:grid-cols-3">

    {{-- Total Receipts Generated --}}
    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">{{ $totalReceipts }}</div>
        <div class="mt-2">Total Receipts Generated</div>
    </div>

    {{-- Total Reported Products --}}
    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">{{ $totalReported }}</div>
        <div class="mt-2">Total Reported Products</div>
    </div>

    {{-- Total Resolved Products --}}
    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">{{ $totalResolved }}</div>
        <div class="mt-2">Total Resolved Products</div>
    </div>

    @hasanyrole('Admin|Police|Business Owner')
    {{-- Total Businesses --}}
    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">{{ $totalBusinesses }}</div>
        <div class="mt-2">Total Businesses</div>
    </div>

    {{-- Total Branches --}}
    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">{{ $totalBranches }}</div>
        <div class="mt-2">Total Branches</div>
    </div>
    @endhasanyrole

</div>
@endsection