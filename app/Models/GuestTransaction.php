<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GuestTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_token',
        'control_number',
        'buyer_email',
        'buyer_name',
        'payment_status',
        'escrow_id',
        'amount',
        'currency',
        'item_name',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Boot function to auto-generate transaction token
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($transaction) {
            // Auto-generate transaction_token if not set
            if (!$transaction->transaction_token) {
                $transaction->transaction_token = Str::random(32);
            }
            
            // Set expiration time (24 hours from now)
            if (!$transaction->expires_at) {
                $transaction->expires_at = now()->addDay();
            }
        });
    }

    /**
     * Get the control number record
     */
    public function controlNumber()
    {
        return $this->belongsTo(ControlNumber::class, 'control_number', 'control_number');
    }

    /**
     * Get the escrow transaction if converted
     */
    public function escrowTransaction()
    {
        return $this->belongsTo(EscrowTransaction::class, 'escrow_id', 'escrow_id');
    }

    /**
     * Convert this guest transaction to a regular escrow transaction
     */
    public function convertToEscrow($userId)
    {
        // Get control number details
        $controlNumber = ControlNumber::where('control_number', $this->control_number)
            ->where('is_used', false)
            ->first();
            
        if (!$controlNumber) {
            return false;
        }
        
        // Create new escrow transaction
        $escrow = new EscrowTransaction();
        $escrow->escrow_id = 'ESC-' . Str::random(8);
        $escrow->buyer_id = $userId;
        $escrow->seller_id = $controlNumber->seller_id;
        $escrow->amount = $controlNumber->amount;
        $escrow->currency = $controlNumber->currency;
        $escrow->status = 'Funds Pending';
        $escrow->delivery_deadline = $controlNumber->delivery_deadline;
        $escrow->inspection_period = $controlNumber->inspection_period;
        $escrow->contract_terms = json_encode([
            'item' => $controlNumber->item_name,
            'condition' => $controlNumber->item_condition,
            'description' => $controlNumber->item_description,
        ]);
        $escrow->save();
        
        // Associate photos from control number to the escrow transaction
        $photos = TransactionPhoto::where('control_number', $controlNumber->control_number)->get();
        foreach ($photos as $photo) {
            // Create a copy of the photo linked to the escrow
            $newPhoto = $photo->replicate();
            $newPhoto->escrow_id = $escrow->escrow_id;
            $newPhoto->photo_type = 'shipment_evidence'; // Convert to shipment evidence
            $newPhoto->save();
        }
        
        // Mark control number as used
        $controlNumber->is_used = true;
        $controlNumber->save();
        
        // Update this guest transaction
        $this->escrow_id = $escrow->escrow_id;
        $this->save();
        
        return $escrow;
    }
}