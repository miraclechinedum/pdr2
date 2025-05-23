<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportedProduct;
use App\Models\ReceiptProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SelfServiceController extends Controller
{
    public function show(string $serial)
    {
        // 1) Reported theft?
        $report = ReportedProduct::join('products', 'reported_products.product_id', '=', 'products.id')
            ->where('products.unique_identifier', $serial)
            ->select('reported_products.*')
            ->first();

        if ($report) {
            $status = 'reported';
            $detail = [
                'reported_at' => $report->created_at->format('jS F, Y'),
                'description' => $report->description,
            ];
        }
        // 2) Sold?
        elseif ($rp = ReceiptProduct::where('product_id', function ($q) use ($serial) {
            $q->select('id')
                ->from('products')
                ->where('unique_identifier', $serial)
                ->limit(1);
        })
            ->with(['receipt.customer', 'receipt.seller'])  // ← use seller()
            ->orderByDesc('receipt_id')
            ->first()
        ) {
            $status = 'sold';
            $detail = [
                'sold_on' => $rp->receipt->created_at->format('jS F, Y'),
                'sold_by' => $rp->receipt->seller->name
                    . ' (' . $rp->receipt->seller->phone_number . ')',
                'sold_to' => $rp->receipt->customer->name
                    . ' (' . $rp->receipt->customer->phone_number . ')',
            ];
        }
        // 3) In stock?
        elseif ($p = Product::where('unique_identifier', $serial)->first()) {
            $status = 'in_stock';
            $detail = [
                'product_name'  => $p->name,
                'stored_in'     => $p->branch?->branch_name ?? 'N/A',
                'qty_remaining' => 1,
            ];
        } else {
            $status = 'not_found';
            $detail = [];
        }

        $history = DB::table('audit_logs')
            ->where('description', 'like', "%{$serial}%")
            ->orderBy('created_at')
            ->get()
            ->map(fn($log) => [
                'id'          => $log->id,
                'date'        => Carbon::parse($log->created_at)->format('jS F, Y'),
                'description' => $this->formatLogDesc($log),
            ]);

        return view('selfservice.result', compact('serial', 'status', 'detail', 'history'));
    }

    protected function formatLogDesc($log)
    {
        $actor = User::find($log->user_id);
        $desc  = $log->description;

        // Replace “user #123”
        $desc = preg_replace_callback(
            '/user #(\d+)/',
            fn($m) => optional(User::find($m[1]))->name
                . ' (' . optional(User::find($m[1]))->phone_number . ')'
                ?? $m[0],
            $desc
        );

        if (in_array($log->action_type, ['inventory_sold', 'manual_sold'])) {
            $desc = $actor->name . " ({$actor->phone_number}) " . lcfirst($desc);
        }

        return $desc;
    }
}
