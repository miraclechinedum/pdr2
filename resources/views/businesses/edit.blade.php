@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Edit Business</h1>
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
    <form id="business-edit-form" action="{{ route('businesses.update',$business->uuid) }}" method="POST"
        class="space-y-4">
        @csrf @method('PUT')

        <!-- Business Owner (disabled) -->
        <div>
            <label class="block mb-1">Business Owner</label>
            <input type="text" class="w-full form-input bg-gray-100 cursor-not-allowed" disabled
                value="{{ $business->owner->name }} ({{ $business->owner->nin }})">
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">Business Name</label>
                <input name="business_name" type="text" class="w-full form-input"
                    value="{{ old('business_name',$business->business_name) }}" required>
                @error('business_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1">RC Number</label>
                <input name="rc_number" type="text" class="w-full form-input"
                    value="{{ old('rc_number',$business->rc_number) }}">
                @error('rc_number') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">Email</label>
                <input name="email" type="email" class="w-full form-input" value="{{ old('email',$business->email) }}">
                @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1">Phone</label>
                <input name="phone" type="text" class="w-full form-input" value="{{ old('phone',$business->phone) }}">
                @error('phone') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Address -->
        <div>
            <label class="block mb-1">Address</label>
            <input type="text" name="address" class="w-full form-input" value="{{ old('address', $business->address) }}"
                required>
            @error('address') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <!-- State & LGA -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">State</label>
                <select id="state-select" name="state_id" class="w-full form-select" required>
                    <option value="">— Select State —</option>
                    @foreach($states as $s)
                    <option value="{{ $s->id }}" {{ old('state_id', $business->state_id) == $s->id ? 'selected' : '' }}
                        >{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('state_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1">LGA</label>
                <select id="lga-select" name="lga_id" class="w-full form-select" required>
                    <option value="">Loading…</option>
                </select>
                @error('lga_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button id="business-update-btn" type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Update
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
  const stateSelect = document.getElementById('state-select');
  const lgaSelect   = document.getElementById('lga-select');

  function loadLgas(selected = null){
    const sid = stateSelect.value;
    if(!sid){
      lgaSelect.innerHTML = '<option value="">— Select LGA —</option>';
      return;
    }
    lgaSelect.innerHTML = '<option>Loading…</option>';
    fetch(`/state/${sid}/lgas`)
      .then(r => r.json())
      .then(list => {
        let html = '<option value="">— Select LGA —</option>';
        list.forEach(i => {
          html += `<option value="${i.id}" ${selected==i.id?'selected':''}>
                     ${i.name}
                   </option>`;
        });
        lgaSelect.innerHTML = html;
      });
  }

  // when state changes
  stateSelect.addEventListener('change', () => loadLgas());

  // initial load
  const initLga = "{{ old('lga_id', $business->lga_id) }}";
  if(stateSelect.value) loadLgas(initLga);

  // submit loading
  document.getElementById('business-edit-form')
    .addEventListener('submit', function(){
      const btn = document.getElementById('business-update-btn');
      btn.disabled = true;
      btn.textContent = 'Updating…';
    });
});
</script>
@endpush