@extends('layouts.selfservice')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Serial Lookup: {{ $serial }}</h1>
</div>

<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">

    @switch($status)

    @case('reported')
    <div class="alert alert-danger">
        <h3 class="font-semibold">Status: Reported Stolen</h3>
        <p><strong>When:</strong> {{ $detail['reported_at'] }}</p>
        <p><strong>Note:</strong> {{ $detail['description'] }}</p>
    </div>
    @break

    @case('sold')
    <div class="alert alert-warning">
        <h3 class="font-semibold">Status: Sold</h3>
        <p><strong>Date:</strong> {{ $detail['sold_on'] }}</p>
        <p><strong>Sold By:</strong> {{ $detail['sold_by'] }}</p>
        <p><strong>Sold To:</strong> {{ $detail['sold_to'] }}</p>
    </div>
    @break

    @case('in_stock')
    <div class="alert alert-success">
        <h3 class="font-semibold">Status: In Stock</h3>
        <p><strong>Product:</strong> {{ $detail['product_name'] }}</p>
        <p><strong>Location:</strong> {{ $detail['stored_in'] }}</p>
        <p><strong>Quantity Left:</strong> {{ $detail['qty_remaining'] }}</p>
    </div>
    @break

    @default
    <div class="alert alert-secondary">
        <h3 class="font-semibold">Status: Not Found</h3>
        <p>No records match that serial number.</p>
    </div>
    @endswitch

    @if(count($history))
    <div class="mt-6">
        <h4 class="font-semibold mb-2">Product History</h4>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($history as $log)
            <li>
                <span class="font-medium"> <strong> {{ $log['date'] }}: </strong></span>
                {{ $log['description'] }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mt-6 flex justify-end">
        <a href="{{ url()->previous() }}" class="btn btn-primary px-4 py-2">Back</a>
    </div>
</div>
@endsection