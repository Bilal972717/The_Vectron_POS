<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPSTORM_META\map;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['total_item'];
    protected $casts = [
        'promised_payment_date' => 'date',
        'is_delivered' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }
    public function transactions()
    {
        return $this->hasMany(OrderTransaction::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function getTotalItemAttribute()
    {
        return $this->products()->sum('quantity');
    }
    public function packer()
    {
        return $this->belongsTo(User::class, 'packed_by');
    }
    public function deliverer()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}
