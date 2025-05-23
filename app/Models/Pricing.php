<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    protected $fillable = ['uuid', 'service', 'amount', 'slug', 'user_id', 'is_active'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
