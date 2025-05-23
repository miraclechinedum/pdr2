<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Product;
use App\Models\ReceiptProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

use App\Services\AuditService;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserCredentials;

class MyProductsController extends Controller
{
    public function index()
    {
        $myProducts = Auth::user()
            ->receivedProducts()
            ->where('receipt_products.status', 'active')
            ->get()
            ->map(fn($p) => [
                'id'                => $p->id,
                'name'              => $p->name,
                'unique_identifier' => $p->unique_identifier,
            ]);

        return view('products.my', compact('myProducts'));
    }


    /**
     * AJAX endpoint for DataTables.
     */
    public function data(Request $request)
    {
        $user = Auth::user();

        return DataTables::of(
            $user->receivedProducts()
                ->where('receipt_products.status', 'active')
        )
            ->addColumn(
                'checkbox',
                fn($row) =>
                '<input type="checkbox" class="select-p" '
                    . 'data-id="' . e($row->id) . '" '
                    . 'data-name="' . e($row->name) . '" '
                    . 'data-uid="' . e($row->unique_identifier) . '" />'
            )
            // Seller Name column
            ->addColumn('seller_name', fn($row) => e($row->seller_name))
            // Seller Phone column
            ->addColumn('seller_phone', fn($row) => e($row->seller_phone))
            // Sold date, parsed via Carbon
            ->addColumn('sold_at', function ($row) {
                return Carbon::parse($row->sold_at)
                    ->format('jS M, Y g:ia');
            })
            ->rawColumns(['checkbox'])
            ->make(true);
    }

    /**
     * Transfer selected products in batch (and generate new receipt).
     */
    public function transferBatch(Request $request)
    {
        $data = $request->validate([
            'new_owner_nin'    => ['required', 'digits:11'],
            'customer_name'    => 'required|string',
            'customer_email'   => ['required', 'email'],
            'customer_phone'   => ['required', 'digits:11'],
            'customer_address' => 'required|string',
            'customer_state_id' => 'required|exists:states,id',
            'customer_lga_id'  => 'required|exists:lgas,id',
            'products'         => 'required|array|min:1',
            'products.*'       => 'exists:products,id',
        ]);

        Log::debug('TransferBatch payload', $data);

        // 1) find or create the new customer
        $customer = User::firstOrCreate(
            ['nin' => $data['new_owner_nin']],
            [
                'name'         => $data['customer_name'],
                'email'        => $data['customer_email'],
                'phone_number' => $data['customer_phone'],
                'address'      => $data['customer_address'],
                'state_id'     => $data['customer_state_id'],
                'lga_id'       => $data['customer_lga_id'],
                // generate a random password
                'password'     => bcrypt($plain = Str::random(12)),
            ]
        );

        // if newly created, assign role and email credentials
        if ($customer->wasRecentlyCreated) {
            $customer->assignRole('Customer');
            // send credentials
            Mail::to($customer->email)
                ->queue(new NewUserCredentials($customer, $plain));
        } else {
            $customer->assignRole('Customer');
        }

        // 2) mark old as transferred
        ReceiptProduct::whereIn('product_id', $data['products'])
            ->where('status', 'active')
            ->update(['status' => 'transferred']);

        // 3) create a new receipt
        $receipt = Receipt::create([
            'customer_id' => $customer->id,
            'seller_id'   => Auth::id(),
        ]);

        // 4) attach each product and audit‐log the transfer
        foreach ($data['products'] as $prodId) {
            ReceiptProduct::create([
                'receipt_id' => $receipt->id,
                'product_id' => $prodId,
                'quantity'   => 1,
                'status'     => 'active',
            ]);

            $p = Product::findOrFail($prodId);

            AuditService::log(
                'product_transferred',
                Auth::user()->name
                    . ' (' . Auth::user()->phone_number . ') transferred '
                    . $p->name . ' (' . $p->unique_identifier . ') to '
                    . $customer->name . ' (' . $customer->phone_number . ')'
            );
        }

        return response()->json([
            'success'          => true,
            'reference_number' => $receipt->reference_number,
        ], 201);
    }
}
