<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'owner_id',
        'rc_number',
        'business_name',
        'email',
        'phone',
        'address',
        'lga_id',
        'state_id',
    ];

    // boot to auto‐generate uuid
    protected static function booted()
    {
        static::creating(function ($business) {
            $business->uuid = (string) Str::uuid();
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function branches()
    {
        return $this->hasMany(BusinessBranch::class);
    }
}
