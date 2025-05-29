@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Upload Products</h1>

    @if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if(session('partial_success'))
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded mb-4">{{ session('partial_success') }}</div>
    @endif

    @if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ is_object($error) ? 'Row '.$error->row().': '.implode(', ', $error->errors()) : $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('products.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Select Business</label>
            <select name="business_id" id="business_id" class="form-select w-full" required>
                <option value="">-- Select Business --</option>
                @foreach($businesses as $business)
                <option value="{{ $business->id }}">{{ $business->business_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Select Branch</label>
            <select name="branch_id" id="branch_id" class="form-select w-full" required disabled>
                <option value="">-- Select Branch --</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Select Product Category</label>
            <select name="category_id" class="form-select w-full" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Upload File (.csv or .xlsx)</label>
            <input type="file" name="file" accept=".csv,.xlsx" required class="form-input w-full">
        </div>

        <div class="text-right">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                Upload
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('business_id').addEventListener('change', function () {
        const businessId = this.value;
        const branchSelect = document.getElementById('branch_id');

        branchSelect.innerHTML = '<option value="">-- Loading branches... --</option>';
        branchSelect.disabled = true;

        if (businessId) {
            fetch(`/api/businesses/${businessId}/branches`)
                .then(res => res.json())
                .then(data => {
                    branchSelect.innerHTML = '<option value="">-- Select Branch --</option>';
                    data.forEach(branch => {
                        const option = document.createElement('option');
                        option.value = branch.id;
                        option.textContent = branch.branch_name;
                        branchSelect.appendChild(option);
                    });
                    branchSelect.disabled = false;
                })
                .catch(() => {
                    branchSelect.innerHTML = '<option value="">-- Failed to load branches --</option>';
                });
        } else {
            branchSelect.innerHTML = '<option value="">-- Select Branch --</option>';
            branchSelect.disabled = true;
        }
    });
</script>
@endsection