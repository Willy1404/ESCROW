<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'escrow_id',
        'buyer_id',
        'amount',
        'payment_method',
        'status',
    ];

    // Relationships
    public function escrowTransaction()
    {
        return $this->belongsTo(EscrowTransaction::class, 'escrow_id', 'escrow_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id', 'user_id');
    }
}