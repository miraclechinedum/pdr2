<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessStaffs extends Model
{
    // If your table isn’t the plural of the class name, specify it:
    // protected $table = 'business_staffs';

    protected $fillable = [
        'business_id',
        'branch_id',
        'user_id',
    ];

    /**
     * A BusinessStaff belongs to a User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A BusinessStaff belongs to a Business.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * A BusinessStaff belongs to a BusinessBranch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(BusinessBranch::class, 'branch_id');
    }
}
