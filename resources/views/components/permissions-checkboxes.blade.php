@props([
// Base name for the inputs; [] will be appended
'name' => 'permissions',
// Array of IDs to pre-check
'selected' => [],
// Label for the group
'label' => 'Permissions',
])

@php
// Grab all permissions, ordered alphabetically
$allPermissions = \Spatie\Permission\Models\Permission::orderBy('name')->get();
@endphp

<fieldset class="mb-4">
    <legend class="block font-medium mb-2">{{ $label }}</legend>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
        @foreach($allPermissions as $perm)
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="{{ $name }}[]" value="{{ $perm->id }}" @checked(in_array($perm->id, (array)
            $selected))
            class="form-checkbox h-4 w-4 text-blue-600"
            >
            <span class="text-gray-700">{{ ucfirst($perm->name) }}</span>
        </label>
        @endforeach
    </div>
</fieldset>