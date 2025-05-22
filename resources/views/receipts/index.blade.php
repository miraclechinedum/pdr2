{{-- resources/views/receipts/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
  <h1 class="text-2xl font-bold">All Receipts</h1>
  <a href="{{ route('receipts.create') }}" class="px-4 py-2 bg-gray-700 text-white rounded">
    + Generate Receipt
  </a>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
  {{ session('success') }}
</div>
@endif

<div class="bg-white p-6 rounded-lg shadow">
  <table id="receipts-table" class="min-w-full">
    <thead>
      <tr>
        <th>S/N</th>
        <th>Reference</th>
        <th>Seller</th>
        <th>Customer</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>
@endsection

@push('scripts')
<script>
  $(function(){
    const table = $('#receipts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route("receipts.data") !!}',
        columns: [
            { data: null, orderable: false, searchable: false }, // S/N
            { data: 'reference', name: 'reference' },
            { data: 'seller', name: 'seller' },
            { data: 'customer', name: 'customer' },
            { data: 'date', name: 'date' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        order: [[1, 'desc']],
        drawCallback(settings){
            table.column(0, { order: 'applied' }).nodes().each((cell, i) => {
                cell.innerHTML = i + 1 + settings._iDisplayStart;
            });
        }
    });

    // View button
    $('#receipts-table').on('click', '.btn-view', function(e){
        e.preventDefault();
        window.location = $(this).attr('href');
    });
});
</script>
@endpush