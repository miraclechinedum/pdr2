<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wallet extends Model
{

    protected $table = 'wallet_transactions';

    protected $fillable = [
        'uuid',
        'reference',
        'user_id',
        'amount',
        'type',
        'status'
    ];

    protected static function booted()
    {
        static::creating(function ($tx) {
            $tx->uuid = (string) Str::uuid();
            $tx->reference = 'WTX-' . date('YmdHis') . '-' . Str::upper(Str::random(6));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
