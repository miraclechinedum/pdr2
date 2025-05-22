@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
  <h1 class="text-2xl font-bold">All Branches</h1>
  <a href="{{ route('branches.create') }}" class="btn btn-primary px-4 py-2">
    <i class="fa-solid fa-plus"></i> Add New Branch
  </a>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
  {{ session('success') }}
</div>
@endif

<div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow">
  <table id="branches-table" class="min-w-full">
    <thead>
      <tr>
        <th>S/N</th>
        <th>Business</th>
        <th>Branch Name</th>
        <th>Address</th>
        <th>State</th>
        <th>LGA</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      {{-- AJAX‐populated --}}
    </tbody>
  </table>
</div>
@endsection

@push('scripts')
<script>
  $(function(){
    const table = $('#branches-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{!! route('branches.data') !!}',
      columns: [
        { data: null, orderable:false, searchable:false },
        { data: 'business', name: 'business', orderable:false, searchable:false },
        { data: 'branch_name',  name: 'branch_name' },
        { data: 'address',  name: 'address' },
        { data: 'state',    name: 'state' },
        { data: 'lga',      name: 'lga' },
        // { data: 'status',   name: 'status' },
        { data: 'actions',  orderable:false, searchable:false },
      ],
      order: [[2,'asc']],
      drawCallback(settings){
        table.column(0,{order:'applied'}).nodes().each((cell,i)=>{
          cell.innerHTML = i+1 + settings._iDisplayStart;
        });
      }
    });
  });
</script>
@endpush