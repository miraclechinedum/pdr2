@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Edit Branch</h1>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $msg)
        <li>{{ $msg }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">
    <form id="branch-edit-form" action="{{ route('branches.update',$branch->uuid) }}" method="POST" class="space-y-4">
        @csrf @method('PUT')

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">Select Business</label>
                <select name="business_id" class="w-full form-select" required>
                    <option value="">-- choose --</option>
                    @foreach($businesses as $id => $name)
                    <option value="{{ $id }}" {{ old('business_id',$branch->business_id)==$id?'selected':'' }}>
                        {{ $name }}
                    </option>
                    @endforeach
                </select>
                @error('business_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Branch Name</label>
                <input name="branch_name" type="text" class="w-full form-input"
                    value="{{ old('branch_name', $branch->branch_name) }}" required>
                @error('branch_name')
                <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block mb-1">Address</label>
            <input name="address" type="text" class="w-full form-input" value="{{ old('address',$branch->address) }}"
                required>
            @error('address') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">State</label>
                <select id="state-select" name="state_id" class="w-full form-select" required>
                    <option value="">Select State</option>
                    @foreach($states as $id => $name)
                    <option value="{{ $id }}" {{ old('state_id',$branch->state_id)==$id?'selected':'' }}>
                        {{ $name }}
                    </option>
                    @endforeach
                </select>
                @error('state_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1">LGA</label>
                <select id="lga-select" name="lga_id" class="w-full form-select" required>
                    <option value="">Select LGA</option>
                </select>
                @error('lga_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex justify-end">
            <button id="branch-update-btn" type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Update
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Populate LGA on load if editing
  $(function(){
    const sel = $('#state-select').val();
    if(sel){
      fetch(`/state/${sel}/lgas`)
        .then(r=>r.json())
        .then(list=>{
          let html = '<option value="">Select LGA</option>';
          list.forEach(l=>{
            html += `<option value="${l.id}" ${l.id=={{ $branch->lga_id }}?'selected':''}>${l.name}</option>`;
          });
          $('#lga-select').html(html);
        });
    }
  });

  // on state change
  $('#state-select').on('change', function(){
    fetch(`/state/${this.value}/lgas`)
      .then(r=>r.json())
      .then(list=>{
        let html = '<option value="">Select LGA</option>';
        list.forEach(l=> html += `<option value="${l.id}">${l.name}</option>`);
        $('#lga-select').html(html);
      });
  });

  $('#branch-edit-form').on('submit', function(){
    $('#branch-update-btn').attr('disabled',true).text('Updating…');
  });
</script>
@endpush