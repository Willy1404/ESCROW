<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscrowTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'escrow_id',
        'buyer_id',
        'seller_id',
        'amount',
        'currency',
        'status',
        'delivery_deadline',
        'inspection_period',
        'contract_terms',
    ];

    protected $casts = [
        'delivery_deadline' => 'date',
        'contract_terms' => 'array',
    ];

    // Relationships
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id', 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'escrow_id', 'escrow_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'escrow_id', 'escrow_id');
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'escrow_id', 'escrow_id');
    }
    public function photos()
{
    return $this->hasMany(TransactionPhoto::class, 'escrow_id', 'escrow_id');
}
}