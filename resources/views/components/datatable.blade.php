@props([
'ajaxUrl', // fetch JSON from this endpoint
'columns', // JS array of { data, title }
'exportRoutes' // ['csv'=>route, 'xls'=>route, 'pdf'=>route]
])

{{-- <div class="mb-4 flex space-x-2">
    <a href="{{ $exportRoutes['csv'] }}" class="export-btn">Export CSV</a>
    <a href="{{ $exportRoutes['xls'] }}" class="export-btn">Export Excel</a>
    <a href="{{ $exportRoutes['pdf'] }}" class="export-btn">Export PDF</a>
</div> --}}

<table id="my-table" class="min-w-full bg-white">
    <thead>
        <tr>
            @foreach($columns as $col)
            <th>{{ $col['title'] }}</th>
            @endforeach
        </tr>
    </thead>
</table>

@push('scripts')
<script>
    $(function(){
  $('#my-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ $ajaxUrl }}',
    columns: {!! json_encode($columns) !!}
  });
});
</script>
@endpush