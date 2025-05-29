@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
  <h1 class="text-2xl font-bold">All Users</h1>
  <a href="{{ route('users.create') }}" class="btn btn-primary px-4 py-2">
    <i class="fa-solid fa-plus"></i> Add New User
  </a>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
  {{ session('success') }}
</div>
@endif


<div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow">
  <table id="users-table" class="min-w-full">
    <thead>
      <tr>
        <th>S/N</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        {{-- <th>Date</th> --}}
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      {{-- DataTables will render rows --}}
    </tbody>
  </table>
</div>

{{-- Permissions Modal (unchanged) --}}
<div id="permissions-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-lg max-w-md mx-auto w-full">
    <div class="p-4 border-b flex justify-between items-center">
      <h2 class="text-lg font-semibold">User Permissions</h2>
      <button id="modal-close" class="text-gray-600 hover:text-gray-800">&times;</button>
    </div>
    <div class="p-4" id="modal-body">
      <p class="text-gray-600">Loading…</p>
    </div>
    <div class="flex justify-end p-4 border-t">
      <button id="modal-close-2" class="px-4 py-2 bg-gray-300 rounded mr-2">Close</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- DataTables CSS/JS already included in layout -->
<script>
  $(function() {
  const table = $('#users-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{!! route('users.data') !!}',
    columns: [
      { data: null, name: 'sn', orderable: false, searchable: false },
      { data: 'name', name: 'name' },
      { data: 'email', name: 'email' },
      { data: 'phone_number', name: 'phone_number' },
      { data: 'role', name: 'role', orderable: false, searchable: false },
      // { data: 'date_created', name: 'date_created' },
      { data: 'actions', name: 'actions', orderable: false, searchable: false },
    ],
    order: [[1, 'asc']],
    drawCallback: function(settings) {
      // compute S/N after draw
      table.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1 + settings._iDisplayStart;
      });
    }
  });

  // Modal logic
  const modal     = document.getElementById('permissions-modal');
  const modalBody = document.getElementById('modal-body');
  ['modal-close','modal-close-2'].forEach(id =>
    document.getElementById(id)
      .addEventListener('click', () => modal.classList.add('hidden'))
  );

  $('#users-table').on('click', '.view-btn', function() {
  const uuid = this.dataset.uuid;
  modal.classList.remove('hidden');
  modalBody.innerHTML = '<p class="text-gray-600">Loading…</p>';

  fetch(`/users/${uuid}/permissions`)
    .then(r => {
      if (!r.ok) throw new Error('Failed to load permissions');
      return r.json();
    })
    .then(perms => {
      if (!perms.length) {
        modalBody.innerHTML =
          '<p class="text-gray-600">No permissions assigned.</p>';
      } else {
        const ul = document.createElement('ul');
        ul.className = 'list-disc list-inside space-y-1';
       perms.forEach(p => {
        const li = document.createElement('li');
        li.textContent = p;
        li.classList.add('list-disc', 'list-inside', 'capitalize');
        ul.append(li);
       });
        modalBody.innerHTML = '';
        modalBody.appendChild(ul);
      }
    })
    .catch(() => {
      modalBody.innerHTML =
        '<p class="text-red-600">Could not load permissions. Please try again.</p>';
    });
});
});
</script>
@endpush