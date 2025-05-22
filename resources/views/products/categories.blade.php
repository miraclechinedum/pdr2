@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">All Product Categories</h1>
    <button id="open-add-modal" class="btn btn-primary px-4 py-2">
        <i class="fa-solid fa-plus"></i> Add New Category
    </button>
</div>

<div id="alerts"></div>

<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <table id="categories-table" class="min-w-full">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Name</th>
                <th>Identifier</th>
                <th>Created</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

{{-- Add Modal --}}
<div id="add-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold">Add Product Category</h2>
            <button class="close-modal text-gray-600 hover:text-gray-800">&times;</button>
        </div>
        <form id="add-form" action="{{ route('categories.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block mb-1">Category Name</label>
                <input name="name" type="text" class="w-full form-input" required>
                <p class="text-red-600 text-sm add-error-name"></p>
            </div>
            <div>
                <label class="block mb-1">Item Identifier (e.g. IMEI)</label>
                <input name="identifier_label" type="text" class="w-full form-input" required>
                <p class="text-red-600 text-sm add-error-identifier_label"></p>
            </div>
            <div class="flex justify-end">
                <button id="add-submit-btn" type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold">Edit Product Category</h2>
            <button class="close-modal text-gray-600 hover:text-gray-800">&times;</button>
        </div>
        <form id="edit-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block mb-1">Category Name</label>
                <input name="name" type="text" class="w-full form-input" required>
                <p class="text-red-600 text-sm edit-error-name"></p>
            </div>
            <div>
                <label class="block mb-1">Item Identifier (e.g. IMEI)</label>
                <input name="identifier_label" type="text" class="w-full form-input" required>
                <p class="text-red-600 text-sm edit-error-identifier_label"></p>
            </div>
            <div class="flex justify-end">
                <button id="edit-submit-btn" type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')

<script>
    /**
    * Render a temporary banner in #alerts.
    * @param {'success'|'error'} type
    * @param {string} text
    */
    function showAlert(type, text) {
    const colors = {
    success: ['green', 'green'],
    error: ['red', 'red'],
    }[type];
    
    $('#alerts').html(`
    <div class="mb-4 p-3 bg-${colors[0]}-100 text-${colors[1]}-800 rounded">
        ${text}
    </div>
    `);
    
    // auto-dismiss after 5s
    setTimeout(() => { $('#alerts').empty(); }, 5000);
    }

    $(function(){
  // helper to add ordinal suffix
  function ordinal(d) {
    if (d > 3 && d < 21) return d + 'th';
    switch (d % 10) {
      case 1: return d + 'st';
      case 2: return d + 'nd';
      case 3: return d + 'rd';
      default: return d + 'th';
    }
  }
  const months = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December'
  ];

  // initialize DataTable
  const table = $('#categories-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{!! route('categories.data') !!}',
    columns: [
      { data: null, orderable:false, searchable:false },
      { data: 'name', name: 'name' },
      { data: 'identifier_label', name: 'identifier_label' },
      { 
        data: 'created_at',
        render: function(ts) {
          const d = new Date(ts);
          return `${ordinal(d.getDate())} ${months[d.getMonth()]}, ${d.getFullYear()}`;
        }
      },
      { data: 'creator', name: 'creator' },
      {
        data: 'id', 
        orderable:false,
        searchable:false,
        render: function(id) {
          return `<button class="edit-btn px-3 py-1 bg-yellow-500 text-white rounded" data-id="${id}">
                    Edit
                  </button>`;
        }
      }
    ],
    order: [[1,'asc']],
    drawCallback(settings){
      table.column(0,{order:'applied'}).nodes().each((cell,i)=>{
        cell.innerHTML = i+1 + settings._iDisplayStart;
      });
    }
  });

  // open/close helpers
  function closeAll() {
    $('#add-modal, #edit-modal').addClass('hidden');
    // clear errors
    $('.add-error-name, .add-error-identifier_label, .edit-error-name, .edit-error-identifier_label').text('');
    $('#add-form')[0].reset();
    $('#edit-form')[0].reset();
  }
  $('.close-modal').click(closeAll);

  // ADD modal
  $('#open-add-modal').click(()=> $('#add-modal').removeClass('hidden'));
 $('#add-form').on('submit', function(e){
  e.preventDefault();
  const btn = $('#add-submit-btn').attr('disabled',true).text('Saving…');

  $.post(this.action, $(this).serialize())
    .done(res => {
      closeAll();
      table.ajax.reload(null,false);
      showAlert('success', res.message);
    })
    .fail(xhr => {
      btn.attr('disabled',false).text('Save');
      if (xhr.status === 422) {
        const errs = xhr.responseJSON.errors;
        if(errs.name) $('.add-error-name').text(errs.name[0]);
        if(errs.identifier_label) $('.add-error-identifier_label').text(errs.identifier_label[0]);
      } else {
        showAlert('error','An unexpected error occurred.');
      }
    });
});

  // EDIT modal
  $('#categories-table').on('click','.edit-btn', function(){
    const id = $(this).data('id');
    // fetch single category
    $.getJSON(`/categories/${id}`, cat => {
      $('#edit-form').attr('action', `/categories/${id}`);
      $('#edit-form [name=name]').val(cat.name);
      $('#edit-form [name=identifier_label]').val(cat.identifier_label);
      $('#edit-modal').removeClass('hidden');
    });
  });
  
 $('#edit-form').on('submit', function(e){
  e.preventDefault();
  const btn = $('#edit-submit-btn').attr('disabled',true).text('Updating…');

  $.ajax({
    url: this.action,
    method: 'PUT',
    data: $(this).serialize(),
  })
  .done(res => {
    closeAll();
    table.ajax.reload(null,false);
    showAlert('success', res.message);
  })
  .fail(xhr => {
    btn.attr('disabled',false).text('Update');
    if (xhr.status === 422) {
      const errs = xhr.responseJSON.errors;
      if(errs.name) $('.edit-error-name').text(errs.name[0]);
      if(errs.identifier_label) $('.edit-error-identifier_label').text(errs.identifier_label[0]);
    } else {
      showAlert('error','An unexpected error occurred.');
    }
  });
});


});
</script>
@endpush