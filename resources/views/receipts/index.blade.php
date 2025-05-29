{{-- resources/views/receipts/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
  <h1 class="text-2xl font-bold">All Receipts</h1>
  @hasanyrole('Business Owner|Business Staff')
  <a href="{{ route('receipts.create') }}" class="px-4 py-2 bg-gray-700 text-white rounded">
    + Generate Receipt
  </a>
  @endhasanyrole
</div>

{{-- Blade‐rendered flash as fallback --}}
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

{{-- AJAX alert placeholder --}}
<div id="ajax-alert" class="mb-4 hidden max-w-6xl mx-auto"></div>

<div class="bg-white p-6 rounded-lg shadow">
  <table id="receipts-table" class="min-w-full">
    <thead>
      <tr>
        <th>S/N</th>
        <th>Reference</th>
        <th>Seller</th>
        <th>Customer</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>
@endsection

@push('scripts')
<script>
  // AJAX alert helper (copied from products page)
  function showAjaxAlert(type, message) {
    // type = 'success' or 'error'
    const colors = {
      success: 'bg-green-100 text-green-800',
      error:   'bg-red-100 text-red-800'
    };
    const html = `
      <div class="p-3 rounded ${colors[type]}">
        ${message}
      </div>`;
    $('#ajax-alert')
      .html(html)
      .removeClass('hidden')
      .stop(true)
      .fadeIn()
      .delay(3000)
      .fadeOut(() => $('#ajax-alert').addClass('hidden'));
  }

  $(function(){
    // Initialize DataTable
    const table = $('#receipts-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{!! route("receipts.data") !!}',
      columns: [
        { data: null, orderable: false, searchable: false }, // S/N
        { data: 'reference', name: 'reference_number' },
        { data: 'seller',    name: 'seller.name' },
        { data: 'customer',  name: 'customer.name' },
        { data: 'date',      name: 'created_at' },
      ],
      order: [[1, 'desc']],
      drawCallback(settings){
        table.column(0, { order: 'applied' })
             .nodes()
             .each((cell, i) => {
               cell.innerHTML = i + 1 + settings._iDisplayStart;
             });
      }
    });

    // 1) Check localStorage for our flash
    const msg = localStorage.getItem('receiptSuccess');
    if (msg) {
      showAjaxAlert('success', msg);
      localStorage.removeItem('receiptSuccess');
    }

    // 2) Optional: still show server-side flashes if any
    @if(session('error'))
      showAjaxAlert('error', @json(session('error')));
    @endif
  });
</script>
@endpush