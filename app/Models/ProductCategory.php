<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'label',
        'identifier_label',
        'user_id',
    ];

    /**
     * Boot and generate a unique 6-digit code on creating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cat) {
            do {
                // numeric, 6 digits
                $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            } while (self::where('uuid', $code)->exists());

            $cat->uuid = $code;
        });
    }

    /**
     * Who created this category.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
