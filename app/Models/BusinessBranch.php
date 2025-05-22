<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'branch_name',
        'business_id',
        'address',
        'lga_id',
        'state_id',
        'status',
        'user_id',
    ];

    protected static function booted()
    {
        static::creating(function ($branch) {
            $branch->uuid = (string) Str::uuid();
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
