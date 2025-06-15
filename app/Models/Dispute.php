<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_id',
        'escrow_id',
        'buyer_id',
        'seller_id',
        'reason',
        'status',
        'resolution',
        'resolved_by',
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

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'user_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by', 'user_id');
    }
}