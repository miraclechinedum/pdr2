{{-- resources/views/products/index.blade.php --}}

@push('head-scripts')
<script>
  window.ProductPermissions = {
    canReport:       @json(Auth::user()->can('report-product')),
    // canResolve:      @json(Auth::user()->can('resolve-product-report')),
    isBusinessOwner: @json(Auth::user()->hasRole('Business Owner')),
  };
</script>
@endpush

@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
  <h1 class="text-2xl font-bold">All Products</h1>
  <a href="{{ route('products.create') }}" class="btn btn-primary px-4 py-2">
    <i class="fa-solid fa-plus"></i> Add New Product
  </a>
</div>

{{-- Success / Error flash --}}
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

<div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow">
  <table id="products-table" class="min-w-full">
    <thead>
      <tr>
        <th>S/N</th>
        <th>Name</th>
        <th>Category</th>
        <th>Identifier</th>
        <th>Business</th>
        <th>Branch</th>
        <th>Added By</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

{{-- Report/Resolve Modal --}}
<div id="report-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto">
    <div class="p-4 border-b flex justify-between items-center">
      <h2 class="text-lg font-semibold" id="modal-title">Report Product</h2>
      <button id="modal-close" class="text-gray-600 hover:text-gray-800">&times;</button>
    </div>
    <form id="modal-form" class="p-6 space-y-4">
      @csrf
      <input type="hidden" id="modal-product-id" name="product_id">

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-1">Product Name</label>
          <input type="text" id="modal-product-name" class="w-full form-input" readonly>
        </div>
        <div>
          <label class="block mb-1">Identifier</label>
          <input type="text" id="modal-product-identifier" class="w-full form-input" readonly>
        </div>
      </div>

      <div id="modal-description-group" class="space-y-1">
        <label class="block mb-1">Description</label>
        <textarea name="description" id="modal-description" class="w-full form-textarea" rows="4" required></textarea>
        <p class="text-red-600 text-sm" id="modal-error"></p>
      </div>

      <div class="flex justify-end">
        <button id="modal-submit-btn" type="button" class="px-4 py-2 bg-red-600 text-white rounded disabled:opacity-50">
          Submit Report
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Transfer Modal --}}
<div id="transfer-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto">
    <div class="p-4 border-b flex justify-between items-center">
      <h2 class="text-lg font-semibold">Transfer Product</h2>
      <button class="close-transfer-modal text-gray-600 hover:text-gray-800">&times;</button>
    </div>
    <form id="transfer-form" class="p-6 space-y-4">
      @csrf
      <input type="hidden" name="product_id" id="transfer-product-id">

      <div>
        <label class="block mb-1">Current Branch</label>
        <input type="text" id="transfer-current-branch" class="w-full form-input" readonly>
      </div>

      <div>
        <label class="block mb-1">New Branch</label>
        <select name="new_branch_id" id="transfer-new-branch" class="w-full form-select" required>
          <option value="">— Select New Branch —</option>
        </select>
        <p class="text-red-600 text-sm transfer-error-new_branch_id"></p>
      </div>

      <div class="flex justify-end">
        <button id="transfer-submit-btn" type="button"
          class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
          Transfer
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function showAjaxAlert(type, message) {
    const colors = {
      success: 'bg-green-100 text-green-800',
      error:   'bg-red-100 text-red-800'
    };
    $('#ajax-alert')
      .html(`<div class="p-3 rounded ${colors[type]}">${message}</div>`)
      .removeClass('hidden')
      .fadeIn().delay(3000).fadeOut(() => $('#ajax-alert').addClass('hidden'));
  }

  // CSRF for all AJAX
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  $(function(){
    // ordinal helper
    function ordinal(i){
      if (i > 3 && i < 21) return i + 'th';
      switch (i % 10) {
        case 1: return i + 'st';
        case 2: return i + 'nd';
        case 3: return i + 'rd';
        default: return i + 'th';
      }
    }
    const months = ['January','February','March','April','May','June','July',
                    'August','September','October','November','December'];

    // Initialize DataTable
    const table = $('#products-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{!! route("products.data") !!}',
      columns: [
        { data:null, orderable:false, searchable:false },           // S/N
        { data:'name',              name:'name' },
        { data:'category',          name:'category' },
        { data:'unique_identifier', name:'unique_identifier' },
        { data:'business',          name:'business' },
        { data:'branch',            name:'branch' },
        { data:'owner',             name:'owner' },
        {
          data:'created_at', name:'created_at',
          render: ts => {
            const d = new Date(ts);
            return `${ordinal(d.getDate())} ${months[d.getMonth()]}, ${d.getFullYear()}`;
          }
        },
        {
          data:null, orderable:false, searchable:false,
          render: row => {
            let html = '';

            if (!row.is_sold) {
              // REPORT
              if (window.ProductPermissions.isBusinessOwner || window.ProductPermissions.canReport) {
                if (!row.reported_status) {
                  html += `
                    <button
                      class="report-btn px-3 py-1 bg-red-600 text-white rounded"
                      data-uuid="${row.uuid}">
                      Report
                    </button> `;
                }
              }

              // RESOLVE
              if (row.reported_status) {
                html += `
                  <button
                    class="resolve-btn px-3 py-1 bg-green-600 text-white rounded"
                    data-uuid="${row.uuid}">
                    Resolve
                  </button> `;
              }

              // TRANSFER
              if (window.ProductPermissions.isBusinessOwner) {
                html += `
                  <button
                    class="transfer-btn px-3 py-1 bg-blue-600 text-white rounded"
                    data-uuid="${row.uuid}">
                    Transfer
                  </button>`;
              }

              return html || '&ndash;';
            }
          }
        }
      ],
      order:[[1,'asc']],
      drawCallback(settings){
        table.column(0,{order:'applied'}).nodes().each((cell,i)=>{
          cell.innerHTML = i + 1 + settings._iDisplayStart;
        });
      }
    });

    // Fetch Product by UUID
    function fetchProduct(uuid, cb){
      $.getJSON(`/products/${uuid}`, cb);
    }

    //
    // REPORT / RESOLVE Handlers
    //
    $('#products-table').on('click', '.report-btn, .resolve-btn', function(){
    const uuid = this.dataset.uuid;
      const mode = $(this).hasClass('report-btn') ? 'report' : 'resolve';

      fetchProduct(uuid, product => {
        $('#modal-product-id').val(product.uuid);
        $('#modal-product-name').val(product.name);
        $('#modal-product-identifier').val(product.unique_identifier);
        $('#modal-error').text('');

        if (mode === 'report') {
          $('#modal-title').text('Report Product');
          $('#modal-description-group').show();
          $('#modal-submit-btn')
            .text('Submit Report')
            .removeClass('bg-green-600')
            .addClass('bg-red-600');
        } else {
          $('#modal-title').text('Resolve Report');
          $('#modal-description-group').hide();
          $('#modal-submit-btn')
            .text('Resolve')
            .removeClass('bg-red-600')
            .addClass('bg-green-600');
        }

        $('#report-modal').removeClass('hidden').data('mode', mode);
      });
    });

    $('#modal-close').click(() => $('#report-modal').addClass('hidden'));

    $('#modal-submit-btn').click(function(){
      const mode = $('#report-modal').data('mode');
      const uuid   = $('#modal-product-id').val();
      const url  = `/products/${uuid}/${mode}`;
      const data = mode === 'report'
                   ? { description: $('#modal-description').val() }
                   : {};

      $(this).prop('disabled', true).text(mode==='report'?'Saving…':'Resolving…');

      $.ajax({ url, method: 'POST', data })
        .done(() => {
          $('#report-modal').addClass('hidden');
          table.ajax.reload(null,false);
          showAjaxAlert('success',
            mode==='report' ? 'Product reported successfully.' : 'Report resolved successfully.'
          );
        })
        .fail(xhr => {
          $('#modal-submit-btn').prop('disabled', false)
                                .text(mode==='report'?'Submit Report':'Resolve');
          const errs = xhr.responseJSON?.errors || {};
          $('#modal-error').text(errs.description?.[0] || '');
          showAjaxAlert('error', Object.values(errs).flat()[0] || 'An unexpected error occurred.');
        });
    });

    //
    // TRANSFER Handlers
    //
    $('.close-transfer-modal').click(() => $('#transfer-modal').addClass('hidden'));

    $('#products-table').on('click', '.transfer-btn', function(){
      const uuid = this.dataset.uuid;
      fetchProduct(uuid, product => {
        $('#transfer-product-id').val(uuid);
        $('#transfer-current-branch').val(product.branch_name ?? '— None —');

        // load branches
        $.getJSON(`/business/${product.business_id}/branches`, branches => {
          let opts = '<option value="">— Select New Branch —</option>';
          branches.forEach(b => {
            if (b.id !== product.branch_id) {
              opts += `<option value="${b.id}">${b.branch_name}</option>`;
            }
          });
          $('#transfer-new-branch').html(opts);
          $('#transfer-modal').removeClass('hidden');
        });
      });
    });

    $('#transfer-submit-btn').click(function(){
      const btn  = $(this).prop('disabled', true).text('Transferring…');
      const uuid = $('#transfer-product-id').val();

      $.post(`/products/${uuid}/transfer`, $('#transfer-form').serialize())
        .done(() => {
          $('#transfer-modal').addClass('hidden');
          table.ajax.reload(null,false);
          showAjaxAlert('success', 'Product transferred successfully.');
        })
        .fail(xhr => {
          btn.prop('disabled', false).text('Transfer');
          const err = xhr.responseJSON?.errors?.new_branch_id?.[0] || '';
          $('.transfer-error-new_branch_id').text(err);
          showAjaxAlert('error', err || 'Failed to transfer product.');
        });
    });
  });
</script>
@endpush