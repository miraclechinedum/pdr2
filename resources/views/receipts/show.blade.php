{{-- resources/views/receipts/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-[40px] font-bold">Police Issued Receipt</h1>
        <a href="/" class=" w-52 logo">
            <img class="w-full" src="{{ asset('images/police_logo.png') }}" alt="Logo">
        </a>
    </div>

    <div class="flex justify-between my-6">
        {{-- Seller Info --}}
        <div class="mb-6">
            <h2 class="font-semibold mb-2">Seller Information</h2>
            <p><strong>Name:</strong> {{ $receipt->seller->name }}</p>
            <p><strong>NIN:</strong> {{ $receipt->seller->nin }}</p>
            <p><strong>Email:</strong> {{ $receipt->seller->email }}</p>
            <p><strong>Phone:</strong> {{ $receipt->seller->phone_number }}</p>
        </div>

        {{-- Customer Info --}}
        <div class="mb-6">
            <h2 class="font-semibold mb-2">Customer Information</h2>
            <p><strong>Name:</strong> {{ $receipt->customer->name }}</p>
            <p><strong>NIN:</strong> {{ $receipt->customer->nin }}</p>
            <p><strong>Email:</strong> {{ $receipt->customer->email }}</p>
            <p><strong>Phone:</strong> {{ $receipt->customer->phone_number }}</p>
        </div>

        {{-- Receipt Date --}}
        <div class="mb-6">
            <p><strong>Receipt #:</strong> {{ $receipt->reference_number }}</p>
            <p><strong>Issued On:</strong> {{ $receipt->created_at->format('jS F, Y g:ia') }}</p>
        </div>
    </div>

    {{-- Product Table --}}
    <div>
        <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
            <thead class="bg-gray-700 text-white">
                <tr>
                    <th class="px-2 py-2"></th>
                    <th class="px-4 py-2 text-left">S/N</th>
                    <th class="px-4 py-2 text-left">Product Name</th>
                    <th class="px-4 py-2 text-left">Serial Number</th>
                    <th class="px-4 py-2 text-left">Business Name</th>
                    <th class="px-4 py-2 text-left">Branch Name</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($receipt->products as $index => $item)
                @php
                $product = $item->product;
                $branch = $product->branch;
                $business = $product->business;
                @endphp
                <tr class="group">
                    <td class="text-center align-top">
                        <button type="button" class="toggle-details text-gray-500 hover:text-gray-700">
                            <span class="chevron">▶</span>
                        </button>
                    </td>
                    <td class="px-4 py-2 align-top">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 align-top">{{ $product->name }}</td>
                    <td class="px-4 py-2 align-top">{{ $product->unique_identifier }}</td>
                    <td class="px-4 py-2 align-top">{{ $business->business_name ?? 'N/A' }}</td>
                    <td class="px-4 py-2 align-top">{{ $branch->branch_name ?? 'N/A' }}</td>
                </tr>
                <tr class="details-row hidden bg-gray-50">
                    <td colspan="6" class="px-6 py-4 text-sm text-gray-700">
                        <div><strong>Branch Address:</strong> {{ $branch->address ?? 'N/A' }}</div>
                        <div><strong>LGA:</strong> {{ optional($branch->lga)->name ?? 'N/A' }}</div>
                        <div><strong>State:</strong> {{ optional($branch->state)->name ?? 'N/A' }}</div>
                        <div class="mt-2"><strong>Business Email:</strong> {{ $business->email ?? 'N/A' }}</div>
                        <div><strong>Business Phone:</strong> {{ $business->phone ?? 'N/A' }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>



    <div class="flex justify-between items-center my-6">
        <div class="my-4">
            {!! QrCode::size(150)->generate(route('receipts.show', $receipt->uuid)) !!}
        </div>

        <button onclick="window.print()" class="px-4 py-2 bg-gray-700 text-white rounded">
            Print Receipt
        </button>
    </div>

</div>
@endsection


@push('scripts')
<script>
    document.querySelectorAll('.toggle-details').forEach(button => {
    button.addEventListener('click', () => {
        const row = button.closest('tr');
        const nextRow = row.nextElementSibling;
        const chevron = button.querySelector('.chevron');

        nextRow.classList.toggle('hidden');
        chevron.textContent = nextRow.classList.contains('hidden') ? '▶' : '▼';
    });
});
</script>
@endpush