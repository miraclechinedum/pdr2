{{-- resources/views/sgd/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">SGD Records</h1>
</div>

{{-- AJAX alert placeholder --}}
<div id="ajax-alert" class="mb-4 hidden max-w-6xl mx-auto"></div>

<div class="bg-white p-6 rounded-lg shadow">
    <table id="sgd-table" class="min-w-full">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Product Name</th>
                <th>Unique Identifier</th>
                <th>Importer Name</th>
                <th>Importer Contact</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    // AJAX alert helper
  function showAjaxAlert(type, message) {
    const colors = {
      success: 'bg-green-100 text-green-800',
      error:   'bg-red-100 text-red-800'
    };
    $('#ajax-alert')
      .html(`<div class="p-3 rounded ${colors[type]}">${message}</div>`)
      .removeClass('hidden')
      .fadeIn()
      .delay(3000)
      .fadeOut(() => $('#ajax-alert').addClass('hidden'));
  }

  $(function(){
    const table = $('#sgd-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{!! route("sgd.data") !!}',
      columns: [
        { data: null, orderable: false, searchable: false },
        { data: 'product_name',      name: 'product_name' },
        { data: 'unique_identifier', name: 'unique_identifier' },
        { data: 'importer_name',     name: 'importer_name' },
        { data: 'importer_contact',  name: 'importer_contact' },
        { data: 'date',              name: 'date' },
      ],
      order: [[1, 'asc']],
      drawCallback(settings) {
        table.column(0, { order:'applied' }).nodes().each((cell,i) => {
          cell.innerHTML = i + 1 + settings._iDisplayStart;
        });
      }
    });
  });
</script>
@endpush