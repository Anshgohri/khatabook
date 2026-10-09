<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'rented_at',
        'expected_return_date',
        'returned_at',
        'security_deposit',
        'discount',
        'total_rent',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'rented_at' => 'date',
        'expected_return_date' => 'date',
        'returned_at' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(RentalItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
