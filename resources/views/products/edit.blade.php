@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
    <h1 class="text-2xl font-bold">Edit Product</h1>
</div>

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
</div>
@endif

<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">
    <form id="product-form" action="{{ route('products.update', $product->uuid) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Product Name -->
        <div>
            <label class="block mb-1">Product Name</label>
            <input name="name" value="{{ old('name', $product->name) }}" class="w-full form-input" required>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Category</label>
                <select id="category-select" name="category_id" class="w-full form-select" required>
                    <option value="">— Select Category —</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ old('category_id', $product->category_id)==$c->id ? 'selected' : ''
                        }}>
                        {{ $c->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label id="identifier-label" class="block mb-1">Unique Identifier</label>
                <input name="unique_identifier" value="{{ old('unique_identifier', $product->unique_identifier) }}"
                    class="w-full form-input" required>
            </div>
        </div>

        <!-- Business & Branch -->
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Business</label>
                <select id="business-select" name="business_id" class="w-full form-select" required>
                    <option value="">— Select Business —</option>
                    @foreach($businesses as $b)
                    <option value="{{ $b->id }}" {{ old('business_id', $product->business_id)==$b->id ? 'selected' : ''
                        }}>
                        {{ $b->business_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div id="branch-container" class="{{ $product->branch_id ? '' : 'hidden' }}">
                <label class="block mb-1">Branch</label>
                <select id="branch-select" name="branch_id" class="w-full form-select">
                    <option value="">— Select Branch —</option>
                    @foreach($product->business->branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $product->branch_id)==$branch->id ? 'selected'
                        : '' }}>
                        {{ $branch->address }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button id="submit-btn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Update
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(function(){
  // 1) Rebuild the category→identifier-label map
  const catLabels = @json($categories->pluck('identifier_label','id'));

  // whenever category changes (or on page load) update the label:
  function refreshIdentifierLabel(){
    const c = $('#category-select').val();
    const lbl = catLabels[c] || 'Unique Identifier';
    $('#identifier-label').text('Update ' + lbl);
  }
  $('#category-select')
    .on('change', refreshIdentifierLabel)
    .trigger('change');

  // 2) Business → branch
  function refreshBranchSelect(){
    const bid = $('#business-select').val();
    if(!bid){
      return $('#branch-container').addClass('hidden');
    }
    fetch(`/business/${bid}/branches`)
      .then(r=>r.json())
      .then(list=>{
        let opts = `<option value="">— Select Branch —</option>`;
        list.forEach(b=>{
          opts += `<option value="${b.id}"
            ${b.id == "{{ old('branch_id', $product->branch_id) }}" ? 'selected' : ''}>
            ${b.branch_name}
          </option>`;
        });
        $('#branch-select').html(opts);
        $('#branch-container').toggle(list.length>0);
      });
  }
  $('#business-select')
    .on('change', refreshBranchSelect)
    .trigger('change');

  // 3) Submit loading
  $('#product-form').on('submit', function(){
    $('#submit-btn')
      .attr('disabled', true)
      .text('Updating…');
  });
});
</script>
@endpush