<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'escrow_id',
        'tracking_id',
        'carrier',
        'estimated_arrival',
        'status',
    ];

    protected $casts = [
        'estimated_arrival' => 'date',
    ];

    // Relationships
    public function escrowTransaction()
    {
        return $this->belongsTo(EscrowTransaction::class, 'escrow_id', 'escrow_id');
    }
}