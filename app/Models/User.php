<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'nin',
        'phone_number',
        'address',
        'lga_id',
        'state_id',
        'role_id',
    ];

    /**
     * Boot callback to assign a unique 10-character UUID on creating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // generate until unique
            do {
                $candidate = Str::upper(Str::random(10));
            } while (self::where('uuid', $candidate)->exists());

            $user->uuid = $candidate;
        });
    }

    /**
     * Use the `uuid` column for route‐model binding.
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // Relations
    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    // Scope for police officers
    public function scopePolice($query)
    {
        return $query->role('Police');  // Spatie role filter
    }

    // For Excel export (FromCollection)
    public function collection()
    {
        return static::police()->select([
            'name',
            'email',
            'nin',
            'phone_number'
        ])->with(['lga:name', 'state:name'])->get()
            ->map(function ($u) {
                return [
                    'Name'         => $u->name,
                    'Email'        => $u->email,
                    'NIN'          => $u->nin,
                    'Phone'        => $u->phone_number,
                    'LGA'          => $u->lga->name ?? '',
                    'State'        => $u->state->name ?? '',
                ];
            });
    }

    public function businessStaff()
    {
        return $this->hasOne(BusinessStaffs::class, 'user_id');
    }

    /**
     * All ReceiptProduct rows (with status) for receipts belonging to this user.
     */
    public function receivedProducts()
    {
        return \App\Models\Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.unique_identifier',
                'receipt_products.status',
                'receipt_products.created_at as sold_at',

                // seller info
                'sellers.name     as seller_name',
                'sellers.phone_number as seller_phone',
            ])
            ->join('receipt_products', 'products.id', '=', 'receipt_products.product_id')
            ->join('receipts',         'receipt_products.receipt_id', '=', 'receipts.id')
            ->join('users as sellers', 'receipts.seller_id',         '=', 'sellers.id')
            ->where('receipts.customer_id', $this->id);
    }

    /**
     * All wallet transactions for this user.
     */
    public function wallets()
    {
        return $this->hasMany(Wallet::class, 'user_id');
    }

    // App\Models\User.php

    public function businesses()
    {
        // for Business Owner role; adjust if your Business table uses a different column name
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function hasRole($role)
    {
        return $this->roles->pluck('name')->contains($role);
    }
}
