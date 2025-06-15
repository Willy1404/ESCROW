<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TransactionPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'escrow_id',
        'control_number', // Add this field
        'uploader_id',
        'photo_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'description',
        'dispute_id'
    ];

    // Relationships
    public function escrowTransaction()
    {
        return $this->belongsTo(EscrowTransaction::class, 'escrow_id', 'escrow_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id', 'user_id');
    }

    public function dispute()
    {
        return $this->belongsTo(Dispute::class, 'dispute_id', 'dispute_id');
    }
}