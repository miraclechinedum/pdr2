@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Add New Product</h1>
</div>

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
</div>
@endif

<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">
    <form id="product-form" action="{{ route('products.store') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Product Name -->
        <div>
            <label class="block mb-1">Product Name</label>
            <input name="name" value="{{ old('name') }}" class="w-full form-input" required>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Category</label>
                <select id="category-select" name="category_id" class="w-full form-select" required>
                    <option value="">— Select Category —</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ old('category_id')==$c->id ? 'selected':'' }}>
                        {{ $c->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label id="identifier-label" class="block mb-1">Unique Identifier</label>
                <input name="unique_identifier" value="{{ old('unique_identifier') }}" class="w-full form-input"
                    required>
            </div>
        </div>

        <!-- Business & Branch -->
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Business</label>
                <select id="business-select" name="business_id" class="w-full form-select" required>
                    <option value="">— Select Business —</option>
                    @foreach($businesses as $b)
                    <option value="{{ $b->id }}" {{ old('business_id')==$b->id?'selected':'' }}>
                        {{ $b->business_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div id="branch-container" class="hidden">
                <label class="block mb-1">Branch</label>
                <select id="branch-select" name="branch_id" class="w-full form-select">
                    <option value="">— Select Branch —</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button id="submit-btn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Save
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(function(){
  // 1) Category → identifier_label
  const catLabels = @json($categories->pluck('identifier_label','id'));
  $('#category-select').change(function(){
    const lbl = catLabels[this.value] || 'Unique Identifier';
    $('#identifier-label').text('Input ' + lbl);
  }).trigger('change');

  // 2) Business → Branch
  $('#business-select').change(function(){
    const bid = this.value;
    if(!bid) return $('#branch-container').addClass('hidden');

    fetch(`/business/${bid}/branches`)
      .then(r=>r.json())
      .then(list=>{
        let opts = `<option value="">— Select Branch —</option>`;
        list.forEach(b=>{
          opts += `<option value="${b.id}">${b.branch_name}</option>`;
        });
        $('#branch-select').html(opts);
        $('#branch-container').toggle(list.length>0);
      });
  }).trigger('change');

  // 3) Submit loading
  $('#product-form').submit(function(){
    $('#submit-btn').attr('disabled',true).text('Saving…');
  });
});
</script>
@endpush