<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Yabacon\Paystack;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // summary
        $totalCredit = $user->wallets()->where('type', 'credit')->where('status', 'success')->sum('amount');
        $totalDebit  = $user->wallets()->where('type', 'debit')->where('status', 'success')->sum('amount');
        $balance     = $totalCredit - $totalDebit;

        return view('wallet.index', compact('totalCredit', 'totalDebit', 'balance'));
    }

    /** Data for DataTable **/
    public function data()
    {
        $user = Auth::user();

        return DataTables::of(
            $user->wallets()->orderBy('created_at', 'desc')
        )
            ->addColumn('reference', fn($w) => $w->reference)
            ->addColumn('amount', fn($w) => $w->amount)
            ->addColumn('type', fn($w) => ucfirst($w->type))
            ->addColumn('status', fn($w) => ucfirst($w->status))
            ->addColumn('date', fn($w) => $w->created_at->format('jS M, Y g:ia'))
            ->toJson();
    }

    /** Initialize a Paystack transaction **/
    public function initialize(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $user = Auth::user();
        $kobo = intval($request->amount * 100);

        // Create your pending record
        $wallet = Wallet::create([
            'user_id'   => $user->id,
            'amount'    => $request->amount,
            'type'      => 'credit',
            'status'    => 'pending',
        ]);

        // Initialize via the SDK
        $paystack = app(Paystack::class);
        $response = $paystack->transaction->initialize([
            'amount'       => $kobo,
            'email'        => $user->email,
            'reference'    => $wallet->reference,
            'callback_url' => route('wallet.callback'),
        ]);

        if (empty($response->status) || $response->status !== true) {
            return response()->json(['message' => 'Could not initialize payment'], 500);
        }

        // Return everything your JS needs
        return response()->json([
            'key'        => env('PAYSTACK_PUBLIC_KEY'),
            'email'      => $user->email,
            'amount'     => $kobo,
            'reference'  => $wallet->reference,
            'auth_url'   => $response->data->authorization_url,
        ]);
    }

    /** Paystack callback **/
    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        if (! $reference) {
            return redirect()->route('wallet.index')
                ->with('error', 'No transaction reference supplied.');
        }

        // get the SDK instance
        /** @var \Yabacon\Paystack $paystack */
        $paystack = app(Paystack::class);

        try {
            // THIS is the correct verify call:
            $tranx = $paystack->transaction->verify([
                'reference' => $reference,
            ]);
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            // something went wrong on Paystack’s end
            \Log::error('Paystack verify error', [
                'reference' => $reference,
                'exception' => $e->getMessage(),
            ]);
            return redirect()->route('wallet.index')
                ->with('error', 'Could not verify payment. Please try again.');
        }

        // find the pending wallet record
        $wallet = Wallet::where('reference', $reference)->first();

        if (! $wallet) {
            return redirect()->route('wallet.index')
                ->with('error', 'Transaction not found.');
        }

        // check the status returned from Paystack
        if (isset($tranx->data->status) && $tranx->data->status === 'success') {
            $wallet->update(['status' => 'success']);
            return redirect()->route('wallet.index')
                ->with('success', 'Wallet funded successfully!');
        }

        // anything else (failed/pending/abandoned)
        $wallet->update(['status' => 'failed']);
        return redirect()->route('wallet.index')
            ->with('error', 'Payment failed or was cancelled.');
    }
}
