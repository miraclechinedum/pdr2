@extends('layouts.app')

@section('content')
<div class="grid gap-6 md:grid-cols-3">

    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">₦0.00</div>
        <div class="mt-2">Today Transactions</div>
    </div>

    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">23,763</div>
        <div class="mt-2">Total Plates Generated</div>
    </div>

    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">23,763</div>
        <div class="mt-2">Stock Level</div>
    </div>

    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">23,758</div>
        <div class="mt-2">Total Plates Unassigned</div>
    </div>

    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">5</div>
        <div class="mt-2">Total Plates Assigned</div>
    </div>

    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">0</div>
        <div class="mt-2">Total Plates Sold</div>
    </div>

    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">5</div>
        <div class="mt-2">MLA Plate Remaining</div>
    </div>

    <div class="card p-6 rounded shadow">
        <div class="text-2xl font-bold">0</div>
        <div class="mt-2">Total Duplicate Plates</div>
    </div>

</div>
@endsection