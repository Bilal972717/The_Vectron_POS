<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'supplier_id',
        'user_id',
        'sub_total',
        'tax',
        'discount_value',
        'discount_type',
        'shipping',
        'grand_total',
        'status',
        'date',
    ];

    protected $appends = ['paid', 'due'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions()
    {
        return $this->hasMany(PurchaseTransaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getPaidAttribute()
    {
        // IMPORTANT:
        // Replace 'amount' below with your actual column name
        // Example: 'paid_amount' or 'transaction_amount'

        return (float) $this->transactions()->sum('amount');
    }

    public function getDueAttribute()
    {
        return (float) ($this->grand_total ?? 0) - (float) $this->paid;
    }
}