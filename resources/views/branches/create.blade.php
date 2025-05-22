@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Add New Branch</h1>
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
    <form id="branch-form" action="{{ route('branches.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">Select Business</label>
                <select name="business_id" class="w-full form-select" required>
                    <option value="">-- choose --</option>
                    @foreach($businesses as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('business_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Branch Name</label>
                <input name="branch_name" type="text" class="w-full form-input" value="{{ old('branch_name') }}"
                    required>
                @error('branch_name')
                <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block mb-1">Address</label>
            <input name="address" type="text" class="w-full form-input" required>
            @error('address') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">State</label>
                <select id="state-select" name="state_id" class="w-full form-select" required>
                    <option value="">Select State</option>
                    @foreach($states as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
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
            <button id="branch-submit-btn" type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Save
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Load LGAs when state changes
  $('#state-select').on('change', function(){
    const s = this.value;
    fetch(`/state/${s}/lgas`)
      .then(r=>r.json())
      .then(list=>{
        let html = '<option value="">Select LGA</option>';
        list.forEach(l=> html += `<option value="${l.id}">${l.name}</option>`);
        $('#lga-select').html(html);
      });
  });
  $('#branch-form').on('submit', function(){
    $('#branch-submit-btn').attr('disabled',true).text('Saving…');
  });
</script>
@endpush