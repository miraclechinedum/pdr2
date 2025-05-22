<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'category_id',
        'unique_identifier',
        'user_id',
        'business_id',
        'branch_id',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($p) {
            $p->uuid = Str::uuid()->toString();
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    public function branch()
    {
        return $this->belongsTo(BusinessBranch::class);
    }
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    /**
     * All of the ReceiptProduct pivot rows this product has ever appeared in.
     */
    public function receiptProducts()
    {
        return $this->hasMany(ReceiptProduct::class);
    }

    /**
     * The one active report on this product.
     */
    public function reportedProduct()
    {
        return $this->hasOne(ReportedProduct::class)
            ->where('status', true);
    }
}
