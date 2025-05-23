{{-- resources/views/products/my.blade.php --}}
@extends('layouts.app')

@section('content')

<div x-data="transferWizard()" x-cloak>

    <div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
        <h1 class="text-2xl font-bold">My Products</h1>
        <button id="batch-transfer" @click="openTransfer()" disabled
            class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
            Transfer Selected →
        </button>
    </div>

    {{-- Ajax alert placeholder --}}
    <div id="ajax-alert" class="mb-4 hidden max-w-6xl mx-auto"></div>

    <div class="bg-white p-6 rounded-lg shadow max-w-6xl mx-auto">
        <table id="my-products-table" class="min-w-full">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Identifier</th>
                    <th>Sold By</th>
                    <th>Seller Phone</th>
                    <th>Date Sold</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    {{-- Three-step Transfer Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl mx-auto">
            {{-- Header --}}
            <div class="p-4 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold" x-text="titles[step]"></h2>
                <button @click="close()" class="text-gray-600 hover:text-gray-800">&times;</button>
            </div>

            {{-- Step 1: New Owner NIN --}}
            <div class="p-6" x-show="step === 1">
                <label class="block mb-1">New Owner NIN</label>
                <input x-model="nin" @keyup.enter="checkNin()" maxlength="11" pattern="\d{11}"
                    title="Must be exactly 11 digits" class="form-input w-full">
                <p class="text-red-600 mt-1" x-text="errorNin"></p>
                <div class="mt-6 flex justify-end">
                    <button @click="checkNin()" :disabled="nin.length !== 11"
                        class="px-4 py-2 bg-blue-600 text-white rounded">
                        Next →
                    </button>
                </div>
            </div>

            {{-- Step 2: New Owner Details --}}
            <div class="p-6" x-show="step === 2">
                <template x-if="existing">
                    <p class="text-green-700 mb-4">Existing user found. You may review but cannot edit.</p>
                </template>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1">Name</label>
                        <input x-model="name" :disabled="existing" class="form-input w-full" required>
                    </div>
                    <div>
                        <label class="block mb-1">Email</label>
                        <input x-model="email" @blur="checkEmail()" type="email" :disabled="existing"
                            class="form-input w-full" required>
                        <p class="text-red-600 mt-1" x-text="errorEmail"></p>
                    </div>
                    <div>
                        <label class="block mb-1">Phone</label>
                        <input x-model="phone" @blur="checkPhone()" maxlength="11" pattern="\d{11}"
                            title="Must be exactly 11 digits" :disabled="existing" class="form-input w-full" required>
                        <p class="text-red-600 mt-1" x-text="errorPhone"></p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block mb-1">Address</label>
                    <input x-model="address" :disabled="existing" class="form-input w-full" required>
                </div>
                <div class="grid md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block mb-1">State</label>
                        <select x-model="stateId" @change="loadLgas()" :disabled="existing" class="form-select w-full"
                            required>
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
                <div class="mt-6 flex justify-between">
                    <button @click="step = 1" class="px-4 py-2 bg-gray-300 rounded">← Back</button>
                    <button @click="step = 3" class="px-4 py-2 bg-blue-600 text-white rounded">Next →</button>
                </div>
            </div>

            {{-- Step 3: Confirm Items --}}
            <div class="p-6" x-show="step === 3">
                <h2 class="font-semibold mb-4">Confirm Products</h2>
                <ul class="list-disc list-inside space-y-2">
                    <template x-for="(item,i) in selected" :key="item.id">
                        <li class="flex justify-between items-center">
                            <span x-text="item.name + ' (' + item.unique_identifier + ')'"></span>
                            <button @click="removeItem(i)" class="text-red-600 hover:underline">×</button>
                        </li>
                    </template>
                </ul>
                <div class="mt-4">
                    <label class="block mb-1">Add More Products</label>
                    <select id="transfer-products" class="w-full"></select>
                </div>
                <div class="mt-6 flex justify-between">
                    <button @click="step = 2" class="px-4 py-2 bg-gray-300 rounded">← Back</button>
                    <button @click="submitTransfer()" :disabled="isSubmitting || selected.length === 0"
                        class="px-4 py-2 bg-green-600 text-white rounded"
                        x-text="isSubmitting ? 'Transferring…' : 'Transfer'">
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // 1) make alert helper available immediately
  function showAjaxAlert(type, message) {
    const colors = {
      success: 'bg-green-100 text-green-800',
      error:   'bg-red-100 text-red-800'
    };
    $('#ajax-alert')
      .html(`<div class="p-3 rounded ${colors[type]}">${message}</div>`)
      .removeClass('hidden')
      .fadeIn()
      .delay(3000)
      .fadeOut()
      .queue(() => $('#ajax-alert').addClass('hidden'));
  }

  // 2) tell jQuery to always send Laravel's CSRF token
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  
    // capture Alpine instance so jQuery can reach it
  let transferWizardInstance;

  document.addEventListener('alpine:init', () => {
    Alpine.data('transferWizard', () => {
      const api = {
        step: 1,
        open: false,
        titles: {
          1: 'Step 1: New Owner NIN',
          2: 'Step 2: Owner Details',
          3: 'Step 3: Confirm Products',
        },
        nin: '', existing: false, errorNin: '',
        name:'', email:'', errorEmail:'', phone:'', errorPhone:'', address:'',
        stateId:'', lgaId:'', lgas:[],
        selected: [],
        addId: null,
        // populated from Blade:
        filteredProducts: @json($myProducts),
        isSubmitting: false,

        get hasSelection() {
          return $('.select-p:checked').length > 0;
        },

        openTransfer() {
          // reset step & forms
          this.step = 1;
          this.nin = '';
          this.errorNin = '';
          this.existing = false;
          this.name = this.email = this.phone = this.address = '';
          this.stateId = this.lgaId = '';
          this.lgas = [];
          // collect what’s already checked
          this.selected = $('.select-p:checked').map((i,el)=>({
            id: el.dataset.id,
            name: el.dataset.name,
            unique_identifier: el.dataset.uid
          })).get();
          // repopulate dropdown
          refreshSelect2Options();
          this.open = true;
        },

        close() {
          this.open = false;
        },

        checkNin() {
          if (!/^\d{11}$/.test(this.nin)) {
            this.errorNin = 'Must be exactly 11 digits';
            return;
          }
          this.errorNin = '';
          fetch(`/users/by-nin/${this.nin}`)
          .then(r=>r.json())
          .then(u=>{
            this.existing = !!u.id;
            if (u.id) {
              this.name = u.name;
              this.email = u.email;
              this.phone = u.phone_number;
              this.address = u.address;
              this.stateId = u.state_id;
              fetch(`/state/${this.stateId}/lgas`)
                .then(r=>r.json())
                .then(list=> {
                  this.lgas = list;
                  this.lgaId = u.lga_id;
                });
            }
            this.step = 2;
          });
        },

        checkEmail() {
          if (!this.existing) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            this.errorEmail = re.test(this.email)?'':'Invalid email';
          }
        },

        checkPhone() {
          if (!this.existing) {
            this.errorPhone = /^\d{11}$/.test(this.phone)?'':'Must be 11 digits';
          }
        },

        loadLgas() {
          if (!this.stateId) return this.lgas = [];
          fetch(`/state/${this.stateId}/lgas`)
            .then(r=>r.json())
            .then(list=> this.lgas=list);
        },

        removeItem(i) {
          this.selected.splice(i,1);
          // put it back into filteredProducts
          this.filteredProducts.push(this.selected[i]);
          refreshSelect2Options();
        },

        submitTransfer() {
          this.isSubmitting = true;
          const payload = {
            new_owner_nin:     this.nin,
            customer_name:     this.name,
            customer_email:    this.email,
            customer_phone:    this.phone,
            customer_address:  this.address,
            customer_state_id: this.stateId,
            customer_lga_id:   this.lgaId,
            products:          this.selected.map(x=>x.id),
          };
          $.post('{!! route("products.my.transfer") !!}', payload)
            .done(resp=>{
              showAjaxAlert('success','Transfer & receipt created.');
              $('#my-products-table').DataTable().ajax.reload();
              this.close();
            })
            .fail(xhr=>{
              showAjaxAlert('error',xhr.responseJSON?.message||'Error');
            })
            .always(()=>{
              this.isSubmitting = false;
            });
        }
      };
      transferWizardInstance = api;
      return api;
    });
  });

  // helper to re-build the Select2 dropdown
  function refreshSelect2Options(){
    const data = transferWizardInstance.filteredProducts.map(p=>({
      id: p.id,
      text:`${p.name} (${p.unique_identifier})`
    }));
    $('#transfer-products')
      .empty()
      .select2({ data, width:'100%', placeholder:'Search & add…' });
  }

  // after DOM ready:
  $(function(){
    // build DataTable
    const table = $('#my-products-table').DataTable({
      processing:true,
      serverSide:true,
      ajax:'{!! route("products.my.data") !!}',
      columns:[
        { data:'checkbox', orderable:false, searchable:false },
        { data:null,      orderable:false, searchable:false }, // S/N
        { data:'name' },
        { data:'unique_identifier' },
        { data:'seller_name' },
        { data:'seller_phone' },
        { data:'sold_at' },
      ],
      order:[[2,'asc']],
      drawCallback(settings){
        table.column(1).nodes().each((cell,i)=>{
          cell.innerHTML = i + 1 + settings._iDisplayStart;
        });
        toggleBatchTransferBtn();
      }
    });

    // select-all logic
    $('#select-all').on('click',function(){
      $('.select-p')
        .prop('checked',this.checked)
        .trigger('change');
    });

    // per-row checkbox toggles button
    $('#my-products-table').on('change','.select-p',toggleBatchTransferBtn);

    // finally: Init Select2 on your transfer dropdown
    $('#transfer-products').select2({ width:'100%', placeholder:'Search & add…', data:[] });
  });

  // toggle the transfer button
  function toggleBatchTransferBtn(){
    const any = $('.select-p:checked').length>0;
    $('#batch-transfer').prop('disabled',!any);
  }
</script>
@endpush