@extends('layouts.app')

@section('content')
<div class="flex justify-between mb-6 bg-white p-6 rounded-lg">
  <h1 class="text-2xl font-bold">Transactions</h1>
  <button id="fund-wallet" class="px-4 py-2 bg-indigo-600 text-white rounded">
    Fund Wallet
  </button>
</div>

<div class="space-y-6">
  {{-- Summary cards --}}
  <div class="grid gap-6 md:grid-cols-3 py-6">
    <div class="p-6 bg-blue-100 rounded shadow">
      <div class="text-2xl font-bold">₦{{ number_format($totalCredit,2) }}</div>
      <div class="mt-2">Total Credit</div>
    </div>
    <div class="p-6 bg-red-100 rounded shadow">
      <div class="text-2xl font-bold">₦{{ number_format($totalDebit,2) }}</div>
      <div class="mt-2">Total Debit</div>
    </div>
    <div class="p-6 bg-green-100 rounded shadow">
      <div class="text-2xl font-bold">₦{{ number_format($balance,2) }}</div>
      <div class="mt-2">Current Balance</div>
    </div>
  </div>

  {{-- DataTable --}}
  <div class="bg-white p-6 rounded shadow max-w-6xl mx-auto">
    <table id="wallet-table" class="min-w-full table-auto">
      <thead>
        <tr>
          <th>S/N</th>
          <th>Reference</th>
          <th>Amount</th>
          <th>Type</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

{{-- Fund Modal --}}
<div id="fund-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto">
    <div class="p-4 border-b flex justify-between items-center">
      <h2 class="text-lg font-semibold">Fund Wallet</h2>
      <button class="close-modal text-gray-600 hover:text-gray-800">&times;</button>
    </div>
    <form id="fund-form" class="p-6 space-y-4">
      @csrf
      <div>
        <label class="block mb-1">Amount (₦)</label>
        <input type="number" name="amount" id="fund-amount" class="form-input w-full" min="1" required>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="fund-cancel" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
        <button type="submit" id="fund-submit" class="flex items-center px-4 py-2 bg-indigo-600 text-white rounded">
          <span id="fund-text">Pay</span>
          <svg id="fund-spinner" class="animate-spin h-5 w-5 ml-2 hidden" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
          </svg>
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
  $(function(){
    $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });

    // DataTable
    let table = $('#wallet-table').DataTable({
      processing:true, serverSide:true,
      ajax: '{!! route("wallet.data") !!}',
      columns: [
        { data:null, orderable:false, searchable:false },
        { data:'reference' },
        { data:'amount', render: a=>'₦'+parseFloat(a).toLocaleString() },
        { data:'type' },
        { data:'status' },
        { data:'date' },
      ],
      order:[[5,'desc']],
      drawCallback(settings){
        this.api().column(0).nodes().each((cell,i)=>{
          cell.innerHTML = i+1 + settings._iDisplayStart;
        });
      }
    });

    // Modal controls
    $('#fund-wallet').click(()=>$('#fund-modal').removeClass('hidden'));
    $('#fund-cancel, .close-modal').click(()=>$('#fund-modal').addClass('hidden'));

    // Fund form → initialize Paystack
    $('#fund-form').submit(function(e){
      e.preventDefault();
      const amt = $('#fund-amount').val();
      $('#fund-text').text('Processing…');
      $('#fund-spinner').removeClass('hidden');
      $('#fund-submit').prop('disabled',true);

      $.post('{!! route("wallet.initialize") !!}', { amount: amt })
      .done(cfg => {
        window.location.href = cfg.auth_url;
      })
      .fail(xhr => {
        alert('Error: ' + xhr.responseText);
        resetFundBtn();
      });

    });

    function resetFundBtn(){
      $('#fund-text').text('Pay');
      $('#fund-spinner').addClass('hidden');
      $('#fund-submit').prop('disabled',false);
    }
  });
</script>
@endpush