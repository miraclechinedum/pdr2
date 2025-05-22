<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptProduct extends Model
{
    protected $fillable = ['receipt_id', 'product_id', 'quantity'];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
