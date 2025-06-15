<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_number',
        'seller_id',
        'amount',
        'currency',
        'item_name',
        'item_condition',
        'item_description',
        'delivery_deadline',
        'inspection_period',
        'is_used',
        'expires_at'
    ];

    protected $casts = [
        'delivery_deadline' => 'date',
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    // Relationships
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'user_id');
    }
}