<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'inquiry_type',
        'message',
        'status',
    ];
}
