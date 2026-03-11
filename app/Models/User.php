<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'username',
        'google_id', 'profile_image',
        'is_google_registered', 'is_suspended',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $appends = ['pro_pic'];

    public function getProPicAttribute()
    {
        return imageRecover($this->profile_image);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrollRuns()
    {
        return $this->hasMany(PayrollRun::class);
    }

    public function packedOrders()
    {
        return $this->hasMany(Order::class, 'packed_by');
    }

    public function deliveredOrders()
    {
        return $this->hasMany(Order::class, 'delivered_by');
    }
}
