@php
$isEdit = isset($user) && $user instanceof \App\Models\User;

$oldName = old('name', $isEdit ? $user->name : '');
$oldEmail = old('email', $isEdit ? $user->email : '');
$oldNin = old('nin', $isEdit ? $user->nin : '');
$oldPhone = old('phone_number', old('phone_number', $isEdit ? $user->phone_number : ''));
$oldAddress = old('address', $isEdit ? $user->address : '');
$oldStateId = old('state_id', $isEdit ? $user->state_id : '');
$oldLgaId = old('lga_id', $isEdit ? $user->lga_id : '');
$currentRoles = $isEdit ? $user->getRoleNames()->toArray() : [];
$oldRole = old('role', count($currentRoles) ? $currentRoles[0] : '');
$oldBusinessId = old('business_id', $isEdit && $user->businessStaff ? $user->businessStaff->business_id : '');
$oldBranchId = old('branch_id', $isEdit && $user->businessStaff ? $user->businessStaff->branch_id : '');
$currentPerms = $isEdit ? $user->getPermissionNames()->toArray() : [];
$oldPerms = old('permissions', $currentPerms);

$action = $isEdit ? route('users.update', $user) : route('users.store');
@endphp

<form id="user-form" action="{{ $action }}" method="POST">
  @csrf
  @if($isEdit) @method('PUT') @endif

  {{-- Name & Email --}}
  <div class="grid md:grid-cols-2 gap-4 mb-4">
    <div>
      <label>Full Name</label>
      <input type="text" name="name" class="w-full form-input" required value="{{ $oldName }}">
    </div>
    <div>
      <label>Email</label>
      <input type="email" name="email" class="w-full form-input" required value="{{ $oldEmail }}">
    </div>
  </div>

  {{-- NIN & Phone --}}
  <div class="grid md:grid-cols-2 gap-4 mb-4">
    <div>
      <label>NIN</label>
      <input type="text" name="nin" class="w-full form-input" required value="{{ $oldNin }}">
    </div>
    <div>
      <label>Phone Number</label>
      <input type="text" name="phone_number" class="w-full form-input" required value="{{ $oldPhone }}">
    </div>
  </div>

  {{-- Address --}}
  <div class="mb-4">
    <label>Address</label>
    <input type="text" name="address" class="w-full form-input" required value="{{ $oldAddress }}">
  </div>

  {{-- State & LGA --}}
  <div class="grid md:grid-cols-2 gap-4 mb-4">
    <div>
      <label>State</label>
      <select id="state-select" name="state_id" class="w-full form-select" required>
        <option value="">Select State</option>
        @foreach($states as $s)
        <option value="{{ $s->id }}" {{ $oldStateId==$s->id ? 'selected' : '' }}>
          {{ $s->name }}
        </option>
        @endforeach
      </select>
    </div>
    <div>
      <label>LGA</label>
      <select id="lga-select" name="lga_id" class="w-full form-select" required>
        <option value="">Select LGA</option>
      </select>
    </div>
  </div>

  {{-- Role --}}
  <div class="mb-4">
    <label>Select Role</label>
    <select id="role-select" name="role" class="w-full form-select" required>
      <option value="">-- Select Role --</option>
      @foreach($roles as $r)
      <option value="{{ $r->name }}" {{ $oldRole===$r->name ? 'selected' : '' }}>
        {{ $r->name }}
      </option>
      @endforeach
    </select>
  </div>

  {{-- Business & Branch --}}
  <div id="business-fields" class="mb-4 {{ $oldRole === 'Business Staff' ? '' : 'hidden' }}">
    <div class="mb-3">
      <label>Select Business</label>
      <select id="business-select" name="business_id" class="w-full form-select">
        <option value="">Select Business</option>
        @foreach($businesses as $b)
        <option value="{{ $b->id }}" {{ $oldBusinessId==$b->id ? 'selected' : '' }}>
          {{ $b->business_name }}
        </option>
        @endforeach
      </select>
    </div>
    <div>
      <label>Select Branch</label>
      <select id="branch-select" name="branch_id" class="w-full form-select {{ $oldBranchId ? '' : 'hidden' }}">
        <option value="">Select Branch</option>
      </select>
    </div>
  </div>

  {{-- Permissions --}}
  @foreach($grouped as $category => $perms)
  <h2 class="mt-6 font-semibold">{{ $category }}</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
    @foreach($perms as $perm)
    <label class="inline-flex items-center space-x-2">
      <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="form-checkbox perm-checkbox" {{
        in_array($perm->name, $oldPerms) ? 'checked' : '' }}>
      <span style="text-transform: capitalize">{{ $perm->name }}</span>
    </label>
    @endforeach
  </div>
  @endforeach

  <div class="text-right">
    <button id="submit-btn" type="submit" class="px-5 py-2 bg-blue-600 text-white rounded">
      {{ $isEdit ? 'Update User' : 'Create User' }}
    </button>
  </div>
</form>

<script>
  const rolePermissions = @json($rolePermissions);

  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('state-select').dispatchEvent(new Event('change'));
    document.getElementById('business-select')?.dispatchEvent(new Event('change'));
  });

  document.getElementById('state-select').addEventListener('change', function () {
    fetch(`/state/${this.value}/lgas`)
      .then(r => r.json())
      .then(data => {
        const sel = document.getElementById('lga-select');
        sel.innerHTML = '<option value="">Select LGA</option>';
        data.forEach(l => {
          sel.insertAdjacentHTML('beforeend',
            `<option value="${l.id}" ${+('{{ $oldLgaId }}') === l.id ? 'selected' : ''}>
              ${l.name}
            </option>`
          );
        });
      });
  });

  document.getElementById('role-select').addEventListener('change', function () {
    const perms = rolePermissions[this.value] || [];
    document.querySelectorAll('.perm-checkbox')
      .forEach(cb => cb.checked = perms.includes(cb.value));
    document.getElementById('business-fields')
      .classList.toggle('hidden', this.value !== 'Business Staff');
  });

  document.getElementById('business-select').addEventListener('change', function () {
    fetch(`/business/${this.value}/branches`)
      .then(r => r.json())
      .then(data => {
        const sel = document.getElementById('branch-select');
        sel.innerHTML = '<option value="">Select Branch</option>';
        data.forEach(b => {
          sel.insertAdjacentHTML('beforeend',
            `<option value="${b.id}" ${+('{{ $oldBranchId }}') === b.id ? 'selected' : ''}>
              ${b.branch_name}
            </option>`
          );
        });
        sel.classList.toggle('hidden', data.length === 0);
      });
  });

  document.getElementById('user-form').addEventListener('submit', function () {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = '{{ $isEdit ? "Updating…" : "Creating…" }}';
  });
</script>