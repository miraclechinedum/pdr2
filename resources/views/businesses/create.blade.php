@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Add New Business</h1>
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
    <form id="business-form" action="{{ route('businesses.store') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Owner -->
        <div>
            <label class="block mb-1">Business Owner</label>
            <select name="owner_id" class="w-full form-select select2 form-select" required>
                <option value="">— Select Owner —</option>
                @foreach($owners as $o)
                <option value="{{ $o->id }}" {{ old('owner_id')==$o->id ? 'selected' : '' }}>
                    {{ $o->name }} ({{ $o->nin }})
                </option>
                @endforeach
            </select>
            @error('owner_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">Business Name</label>
                <input name="business_name" type="text" class="w-full form-input" value="{{ old('business_name') }}"
                    required>
                @error('business_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1">RC Number</label>
                <input name="rc_number" type="text" class="w-full form-input" value="{{ old('rc_number') }}">
                @error('rc_number') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">Email</label>
                <input name="email" type="email" class="w-full form-input" value="{{ old('email') }}">
                @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1">Phone</label>
                <input name="phone" type="text" class="w-full form-input" value="{{ old('phone') }}">
                @error('phone') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Address -->
        <div>
            <label class="block mb-1">Address</label>
            <input type="text" name="address" class="w-full form-input" value="{{ old('address') }}" required>
            @error('address') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <!-- State & LGA -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">State</label>
                <select id="state-select" name="state_id" class="w-full form-select" required>
                    <option value="">— Select State —</option>
                    @foreach($states as $s)
                    <option value="{{ $s->id }}" {{ old('state_id')==$s->id ? 'selected' : '' }}
                        >{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('state_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1">LGA</label>
                <select id="lga-select" name="lga_id" class="w-full form-select" required>
                    <option value="">Select LGA</option>
                    {{-- JS will populate --}}
                </select>
                @error('lga_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button id="business-submit-btn" type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Save
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.select2').select2({
            placeholder: "— Select Owner —",
            allowClear: true
        });
    });

    $(function(){
  // Load LGAs when state changes
  $('#state-select').on('change', function(){
    const stateId = this.value;
    $('#lga-select').html('<option>Loading…</option>');
    fetch(`/state/${stateId}/lgas`)
      .then(r => r.json())
      .then(data => {
        let html = '<option value="">— Select LGA —</option>';
        data.forEach(l => {
          html += `<option 
                      value="${l.id}"
                      ${'{{ old("lga_id") }}' == l.id ? 'selected' : ''}
                    >
                      ${l.name}
                    </option>`;
        });
        $('#lga-select').html(html);
      });
  });

  // Submit loading state
  $('#business-form').on('submit', function(){
    $('#business-submit-btn')
      .attr('disabled', true)
      .text('Saving…');
  });

  // Trigger if old state exists (e.g. validation failed)
  @if(old('state_id'))
  $('#state-select').trigger('change');
  @endif
});
</script>
@endpush