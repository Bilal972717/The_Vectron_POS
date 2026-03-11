<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = ['paid_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
