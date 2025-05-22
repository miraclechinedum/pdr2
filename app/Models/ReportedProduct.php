<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReportedProduct extends Model
{
    protected $fillable = [
        'uuid',
        'product_id',
        'user_id',
        'description',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($report) {
            $report->uuid = (string) Str::uuid();
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
