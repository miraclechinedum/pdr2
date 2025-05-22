@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">All Businesses</h1>
    <a href="{{ route('businesses.create') }}" class="btn btn-primary px-4 py-2">
        <i class="fa-solid fa-plus"></i> Add New Business
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
    {{ session('success') }}
</div>
@endif

<div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow">
    <table id="businesses-table" class="min-w-full">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Name</th>
                <th>RC Number</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Owner</th>
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
    const table = $('#businesses-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: '{!! route('businesses.data') !!}',
      columns: [
        { data: null, orderable:false, searchable:false },
        { data: 'business_name', name: 'business_name' },
        { data: 'rc_number',     name: 'rc_number' },
        { data: 'email',         name: 'email' },
        { data: 'phone',         name: 'phone' },
        { data: 'owner',         name: 'owner', orderable:false, searchable:false },
        { data: 'actions',       orderable:false, searchable:false },
      ],
      order: [[1,'asc']],
      drawCallback(settings){
        table.column(0,{order:'applied'}).nodes().each((cell,i)=>{
          cell.innerHTML = i+1 + settings._iDisplayStart;
        });
      }
    });
  });
</script>
@endpush