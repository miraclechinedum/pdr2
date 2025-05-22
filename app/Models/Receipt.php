<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Receipt extends Model
{
    protected $fillable = [
        'uuid',
        'reference_number',
        'customer_id',
        'seller_id'
    ];

    public static function booted()
    {
        static::creating(function ($r) {
            $r->uuid = (string) Str::uuid();
            $r->reference_number = 'REF-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    public function products()
    {
        return $this->hasMany(ReceiptProduct::class);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
