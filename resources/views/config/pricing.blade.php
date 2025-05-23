@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Pricing Setup</h1>
    <button id="new-service" class="btn btn-primary px-4 py-2">
        <i class="fa-solid fa-plus"></i> Set New Service Charge
    </button>
</div>

<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
    {{-- Toast container --}}
    <div id="toast-container" class="fixed top-4 right-4 space-y-2 z-50 pointer-events-none">
    </div>

    <table id="pricing-table" class="min-w-full table-auto">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Service</th>
                <th>Amount</th>
                <th>Created By</th>
                <th>Created On</th>
                {{-- <th>Active</th> --}}
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- DataTables will populate this --}}
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="pricing-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-lg w-full max-w-md p-6">
        <h2 class="text-xl font-semibold mb-4" id="modal-title">New Service Charge</h2>
        <form id="pricing-form">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <div class="mb-4">
                <label class="block mb-1">Service</label>
                <input type="text" name="service" id="service" class="form-input w-full" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Amount</label>
                <input type="number" name="amount" id="amount" step="0.01" class="form-input w-full" required>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="cancel-btn" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded flex items-center" id="save-btn">
                    <span id="save-text">Save</span>
                    <svg id="save-spinner" class="animate-spin h-5 w-5 ml-2 hidden" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function(){
    $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });

    const table = $('#pricing-table').DataTable({
  ajax: '{!! route("pricing.data") !!}',
  columns: [
    {
      data: null,
      orderable: false,
      searchable: false,
      className: 'text-center',
      defaultContent: ''
    },
    { data: 'service' },
   { 
    data: 'amount', 
    render: amt => '₦' + ((amt % 1 === 0) ? amt.toString().split('.')[0] : amt) 
    },
    { data: 'created_by' },
    { data: 'created_on' },
    //   { data: 'active' }, 
    { data: 'actions', orderable: false, searchable: false }
  ],
  order: [[1, 'asc']]
});

// Fix S/N after initialization
table.on('draw', function(){
  table.column(0).nodes().each((cell, i) => {
    cell.innerHTML = i + 1 + table.page.info().start;
  });
});


    function showModal(title, method, url, data={}) {
        $('#modal-title').text(title);
        $('#pricing-form').attr('action', url);
        $('#form-method').val(method);
        $('#service').val(data.service||'');
        $('#amount').val(data.amount||'');
        $('#pricing-modal').removeClass('hidden');
    }
    function hideModal(){
        $('#pricing-modal').addClass('hidden');
    }

    $('#new-service').click(()=> {
        showModal('New Service Charge','POST','{!! route("pricing.store") !!}');
    });
    $('#pricing-table').on('click','.edit-btn', function(){
        const id      = $(this).data('id');
        const service = $(this).data('service');
        const amount  = $(this).data('amount');
        showModal('Edit Service Charge','PUT', `/config/pricing/${id}`, {service,amount});
    });
    $('#cancel-btn').click(hideModal);

    $('#pricing-form').submit(function(e){
        e.preventDefault();
        const url    = $(this).attr('action');
        const method = $('#form-method').val();
        const payload= { service:$('#service').val(), amount:$('#amount').val() };

        // loading state
        $('#save-text').text('Saving…');
        $('#save-spinner').removeClass('hidden');
        $('#save-btn').prop('disabled', true);

        $.ajax({ url, method, data: payload })
         .always(() => {
           $('#save-text').text('Save');
           $('#save-spinner').addClass('hidden');
           $('#save-btn').prop('disabled', false);
         })
         .done(()=> {
            hideModal();
            table.ajax.reload();
         })
         .fail(xhr => alert('Error: '+xhr.responseText));
    });
});

/**
 * Create and show a toast message.
 * @param {string} message  The text to display.
 * @param {'success'|'error'} type  Border/color style.
 */
function showToast(message, type = 'success') {
  // build the toast element
  const toast = $(`
    <div class="toast toast-${type} transition transform duration-300 ease-out opacity-0 translate-y-2" style="color: #fff;
    padding: 10px;
    background: #4caf50;">
      <span>${message}</span>
    </div>
  `);

  // append and animate in
  $('#toast-container').append(toast);
  // trigger CSS transition
  setTimeout(() => {
    toast
      .removeClass('opacity-0 translate-y-2')
      .addClass('opacity-100 translate-y-0');
  }, 10);

  // remove after 3s
  setTimeout(() => {
    toast
      .addClass('opacity-0 translate-y-2')
      .on('transitionend', () => toast.remove());
  }, 3000);
}

$('#pricing-table').on('change', '.toggle-status', function() {
  const id  = $(this).data('id');
  const url = `/config/pricing/${id}/toggle`;
  const checkbox = $(this);

  $.ajax({
    url,
    method: 'PATCH',
    headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') }
  })
  .done(res => {
    const msg = res.is_active
      ? 'Service activated'
      : 'Service deactivated';
    showToast(msg, 'success');
  })
  .fail(() => {
    showToast('Could not update status', 'error');
    // revert the checkbox state
    checkbox.prop('checked', !checkbox.prop('checked'));
  });
});

</script>
@endpush