{{-- resources/views/receipts/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
  <h1 class="text-2xl font-bold">Generate Receipt</h1>
</div>

<div x-data="receiptWizard()" x-init="init()" class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">

  {{-- Step 1: Enter/NIN --}}
  <div x-show="step === 1" class="space-y-4">
    <label class="block mb-1">Customer NIN</label>
    <input x-model="nin" @keyup.enter="checkNin()" maxlength="11" pattern="\d{11}" title="Must be exactly 11 digits"
      class="form-input w-full" />
    <p class="text-red-600" x-text="errorNin"></p>
    <button @click="checkNin()" class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="nin.length !== 11">
      Next →
    </button>
  </div>

  {{-- Step 2: Customer Details --}}
  <div x-show="step === 2" class="space-y-4">
    <h2 class="font-semibold">Customer Details</h2>

    <template x-if="existing">
      <p class="text-green-700">Existing customer found. You may review but cannot edit.</p>
    </template>

    <div class="grid md:grid-cols-3 gap-4">
      {{-- Name --}}
      <div>
        <label class="block mb-1">Name</label>
        <input x-model="name" :disabled="existing" class="form-input w-full" required />
      </div>

      {{-- Email --}}
      <div>
        <label class="block mb-1">Email</label>
        <input x-model="email" @blur="checkEmail()" type="email" :disabled="existing" maxlength="255"
          class="form-input w-full" required />
        <p class="text-red-600" x-text="errorEmail"></p>
      </div>

      {{-- Phone --}}
      <div>
        <label class="block mb-1">Phone Number</label>
        <input x-model="phone" @blur="checkPhone()" maxlength="11" pattern="\d{11}" title="Must be exactly 11 digits"
          class="form-input w-full" :disabled="existing" required />
        <p class="text-red-600" x-text="errorPhone"></p>
      </div>
    </div>

    {{-- Address --}}
    <div>
      <label class="block mb-1">Address</label>
      <input x-model="address" class="form-input w-full" :disabled="existing" required />
    </div>

    {{-- State & LGA --}}
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1">State</label>
        <select x-model="stateId" @change="loadLgas()" :disabled="existing" class="form-select w-full" required>
          <option value="">— Select State —</option>
          @foreach(\App\Models\State::all() as $s)
          <option value="{{ $s->id }}">{{ $s->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block mb-1">LGA</label>
        <select x-model="lgaId" :disabled="existing" class="form-select w-full" required>
          <option value="">— Select LGA —</option>
          <template x-for="l in lgas" :key="l.id">
            <option :value="l.id" x-text="l.name"></option>
          </template>
        </select>
      </div>
    </div>

    <div class="flex justify-between">
      <button @click="step = 1" class="px-4 py-2 bg-gray-300 rounded">← Back</button>
      <button @click="step = 3" class="px-4 py-2 bg-blue-600 text-white rounded">Next →</button>
    </div>
  </div>

  {{-- Step 3: Sale Details --}}
  <div x-show="step === 3" class="space-y-4">
    <h2 class="font-semibold">Sale Details</h2>

    <div class="grid md:grid-cols-2 gap-4">
      {{-- Business --}}
      <div>
        <label class="block mb-1">Business</label>
        <select x-model="businessId" @change="loadBranches(); filterProducts()" :disabled="isStaff"
          class="form-select w-full" required>
          <option value="">— Select Business —</option>
          <template x-for="b in availableBusinesses" :key="b.id">
            <option :value="b.id" x-text="b.business_name"></option>
          </template>
        </select>
      </div>

      {{-- Branch --}}
      <div x-show="branches.length > 0">
        <label class="block mb-1">Branch</label>
        <select x-model="branchId" @change="filterProducts()" :disabled="isStaff" class="form-select w-full">
          <option value="">— Any Branch —</option>
          <template x-for="br in branches" :key="br.id">
            <option :value="br.id" x-text="br.branch_name"></option>
          </template>
        </select>
      </div>
    </div>

    {{-- Products multi-select --}}
    <div>
      <label class="block mb-1">Products</label>
      <select id="products-select" x-model="selectedProducts" multiple class="form-multiselect w-full">
        <template x-for="p in filteredProducts" :key="p.id">
          <option :value="p.id" x-text="`${p.name} (${p.unique_identifier})`"></option>
        </template>
      </select>
    </div>

    <button @click="addToCart()" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded" :disabled="!branchId">
      Add to Cart
    </button>

    {{-- Cart preview --}}
    <template x-if="cart.length">
      <div class="mt-4">
        <h3 class="font-semibold">Cart (<span x-text="cart.length"></span> batches)</h3>
        <table class="w-full text-sm border">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2">Business</th>
              <th class="p-2">Branch</th>
              <th class="p-2">Products</th>
              <th class="p-2">Remove</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="(entry,idx) in cart" :key="idx">
              <tr>
                <td class="p-2" x-text="businessName(entry.businessId)"></td>
                <td class="p-2" x-text="branchName(entry.branchId) ?? '— Any —'"></td>
                <td class="p-2">
                  <ul class="list-disc list-inside">
                    <template x-for="pid in entry.products" :key="pid">
                      <li x-text="productLabel(pid)"></li>
                    </template>
                  </ul>
                </td>
                <td class="p-2">
                  <button @click="removeFromCart(idx)" class="text-red-600 hover:underline">×</button>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </template>

    <div class="flex justify-between mt-6">
      <button @click="step = 2" class="px-4 py-2 bg-gray-300 rounded">← Back</button>
      <button @click="submitCart()" type="button" class="px-4 py-2 bg-green-600 text-white rounded"
        :disabled="isSubmitting || !cart.length" x-text="isSubmitting ? 'Generating…' : 'Generate'">
      </button>
    </div>
  </div>

  {{-- Confirmation Modal --}}
  <div x-show="showConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-cloak>
    <div class="bg-white rounded-2xl shadow-lg max-w-sm w-full p-6 space-y-4">
      <h2 class="text-lg font-semibold">Confirm Deduction</h2>
      <p>
        ₦<span x-text="fee.toLocaleString(undefined, { minimumFractionDigits: 2 })"></span>
        will be deducted from your wallet.
      </p>
      <div class="flex justify-end space-x-2">
        <button @click="showConfirm = false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
          Cancel
        </button>
        <button @click="confirmAndSubmit()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
          OK
        </button>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  // CSRF for all AJAX
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
  });

  function receiptWizard() {
    return {
      step: 1,
      nin: '', existing: false, errorNin: '',
      name:'', email:'', errorEmail: '', phone: '', errorPhone: '', address:'',
      stateId:'', lgaId:'', lgas:[],

      isStaff: @json(Auth::user()->hasRole('Business Staff')),
      staffBusinessId: @json(optional(Auth::user()->businessStaff)->business_id),
      staffBranchId: @json(optional(Auth::user()->businessStaff)->branch_id),

      businessId: null,
      branchId: null,
      branches: [],
      allBranches: @json($allBranches),
      products: @json($products),
      filteredProducts: [],
      selectedProducts: [],
      fee: @json($fee),      // ← injected fee
      isSubmitting: false,
      showConfirm: false,    // ← controls the confirmation modal

      get availableBusinesses() {
        if (this.isStaff) {
          return @json($businesses).filter(b => b.id === this.staffBusinessId);
        }
        return @json($businesses);
      },

      init() {
        // pre-select for staff
        if (this.isStaff && this.staffBusinessId) {
          this.businessId = this.staffBusinessId;
          this.branchId   = this.staffBranchId;
          this.loadBranches();
          this.filterProducts();
        }
        // initialize Select2 (empty for now)
        $('#products-select').select2({
          placeholder: 'Search & add products…',
          width: '100%',
          data: this.filteredProducts.map(p => ({
            id: p.id,
            text: `${p.name} (${p.unique_identifier})`
          }))
        }).on('change', () => {
          this.selectedProducts = $('#products-select').val()||[];
        });
      },

   checkNin() {
        if (!this.nin.trim()) {
          this.errorNin = 'Please enter a NIN';
          return;
        }
        if (this.nin.length !== 11 || !/^\d{11}$/.test(this.nin)) {
          this.errorNin = 'NIN must be exactly 11 digits.';
          return;
        }
        this.errorNin = '';
        fetch(`/users/by-nin/${this.nin}`)
          .then(r => r.json())
          .then(u => {
            if (u.id) {
              this.existing = true;
              this.name     = u.name;
              this.email    = u.email;
              this.phone    = u.phone_number;
              this.address  = u.address;
              this.stateId  = u.state_id;
              fetch(`/state/${this.stateId}/lgas`)
                .then(r=>r.json())
                .then(list=> {
                  this.lgas  = list;
                  this.lgaId = u.lga_id;
                  this.step  = 2;
                });
            } else {
              this.existing = false;
              this.name = this.email = this.phone = this.address = '';
              this.stateId = this.lgaId = '';
              this.lgas = [];
              this.step = 2;
            }
          });
      },

      async checkPhone() {
        // skip if existing customer
        if (this.existing) return;

        if (!/^\d{11}$/.test(this.phone)) {
          this.errorPhone = 'Phone must be exactly 11 digits.';
          return;
        }

        this.errorPhone = '';

        try {
          const res = await fetch(`/users/by-phone/${this.phone}`, {
            headers: { 'Accept': 'application/json' }
          });
          const payload = await res.json();

          if (payload.id) {
            this.errorPhone = 'That phone number is already in use.';
          }
        } catch (e) {
          console.error('Phone-check failed:', e);
          // optionally set a generic error
        }
      },

      async checkEmail() {
        // don’t re-check for existing customers
        if (this.existing) {
          this.errorEmail = '';
          return;
        }

        // quick client-side format check
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(this.email)) {
          this.errorEmail = 'Please enter a valid email.';
          return;
        }
        this.errorEmail = '';

        // server-side uniqueness check
        try {
          const res = await fetch(`/users/by-email/${encodeURIComponent(this.email)}`, {
            headers: { 'Accept': 'application/json' }
          });
          const payload = await res.json();
          if (payload.id) {
            this.errorEmail = 'That email is already in use.';
          }
        } catch (e) {
          console.error('Email uniqueness check failed:', e);
          // optional: set a generic error
        }
      },

      loadLgas() {
        if (!this.stateId) return this.lgas = [];
        fetch(`/state/${this.stateId}/lgas`)
          .then(r=>r.json())
          .then(list=> this.lgas = list);
      },

      loadBranches() {
  if (!this.businessId) {
    this.branches = [];
    return;
  }
  console.log('Fetching branches for business', this.businessId);
  fetch(`/business/${this.businessId}/branches`)
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    })
    .then(list => {
      console.log('Got branches:', list);
      this.branches = list;
    })
    .catch(err => {
      console.error('Error loading branches:', err);
      this.branches = [];
    });
},

     filterProducts() {
  // 1) start with products for this business/branch
  let list = this.products.filter(p =>
    p.business_id == this.businessId &&
    (!this.branchId || p.branch_id == this.branchId)
  );

  // 2) exclude any already in the cart
  const inCart = new Set(this.cart.flatMap(entry => entry.products));
  list = list.filter(p => !inCart.has(p.id));

  // 3) exclude reported or sold
  list = list.filter(p => !p.reported && !p.sold);

  // 4) assign
  this.filteredProducts = list;

  // 5) rebuild Select2
  this.$nextTick(() => {
    const data = list.map(p => ({
      id:   p.id,
      text: `${p.name} (${p.unique_identifier})`
    }));
    $('#products-select')
      .empty()
      .select2({ data, width:'100%', placeholder:'Search & add products…' })
      .val(this.selectedProducts)
      .trigger('change');
  });
},

          // NEW
    cart: [],

    // lookups:
    businessName(id) {
      const b = this.availableBusinesses.find(x=>x.id==id);
      return b ? b.business_name : '';
    },
    branchName(id) {
       const br = this.allBranches.find(x=>x.id==id);
      return br ? br.branch_name : null;
    },
    productLabel(pid) {
      const p = this.products.find(x=>x.id==pid);
      return p ? `${p.name} (${p.unique_identifier})` : pid;
    },

  addToCart() {
    if (!this.businessId) {
      return alert('Select a business first');
    }

    if (!this.branchId) {
      return alert('Select a branch first');
    }

    if (!this.selectedProducts.length) {
      return alert('Pick at least one product');
    }

    // existing dedup logic...
    const inCart = new Set(this.cart.flatMap(entry => entry.products));
    const newOnes = this.selectedProducts.filter(id => !inCart.has(id));

    if (!newOnes.length) {
      return alert('You’ve already added those products to the cart.');
    }

    this.cart.push({
      businessId: this.businessId,
      branchId:   this.branchId,
      products:   [...newOnes]
    });

    // clear selection
    this.selectedProducts = [];
    $('#products-select').val([]).trigger('change');
  },

    removeFromCart(idx) {
      this.cart.splice(idx,1);
    },

      submitCart() {
        // open confirmation modal instead of immediate submit
        this.showConfirm = true;
      },

      confirmAndSubmit() {
        // user confirmed, proceed with actual submission
        this.showConfirm = false;
        this.isSubmitting = true;

        const fd = new FormData();
        fd.append('customer_nin', this.nin);
        fd.append('customer_name', this.name);
        fd.append('customer_email', this.email);
        fd.append('customer_phone', this.phone);
        fd.append('customer_address', this.address);
        fd.append('customer_state_id', this.stateId);
        fd.append('customer_lga_id', this.lgaId);

        this.cart.forEach((entry, i) => {
          fd.append(`batches[${i}][business_id]`, entry.businessId);
          fd.append(`batches[${i}][branch_id]`, entry.branchId ?? '');
          entry.products.forEach(pid =>
            fd.append(`batches[${i}][products][]`, pid)
          );
        });

        fetch('{{ route("receipts.store") }}', {
          method: 'POST',
          credentials: 'same-origin',          // ← ensures Laravel session cookie is sent
          headers: {
            'X-CSRF-TOKEN':   document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With':'XMLHttpRequest', // ← marks it as AJAX
            'Accept':          'application/json', // ← says “I want JSON back”
          },
          body: fd,
        })
        .then(async response => {
          const text = await response.text();
          let data;
          try {
            data = JSON.parse(text);
          } catch {
            console.error('Expected JSON but got:', text);
            throw new Error('Server returned HTML—check Laravel log for the real error.');
          }
          if (!response.ok) {
            console.error('Error payload:', data);
            throw new Error(data.error || 'Server error');
          }
          return data;
        })
       .then(data => {
          // store a flash in localStorage
          localStorage.setItem(
            'receiptSuccess',
            `Receipt generated: ${data.reference_number}`
          );
          // then redirect
          window.location = '{{ route("receipts.index") }}';
        })
        .catch(err => {
          console.error('Receipt store failed:', err);
          alert(err.message);
        })
        .finally(() => {
          this.isSubmitting = false;
        });
      }
    }
  }

  document.addEventListener('alpine:init', () => {
    Alpine.data('receiptWizard', receiptWizard);
  });
</script>
@endpush