<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendingAction extends Model
{
    use HasFactory;

    // Define the table name explicitly
    protected $table = 'pending_actions';
    
    // Define the primary key column name
    protected $primaryKey = 'action_id';
    
    // Define that our primary key is a string
    protected $keyType = 'string';
    
    // Define that our primary key is not auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'action_id',
        'maker_id',
        'checker_id',
        'action_type',
        'action_data',
        'entity_type',
        'entity_id',
        'description',
        'status',
        'rejection_reason',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'action_data' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];
    
    /**
     * Boot function to auto-generate action_id
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($action) {
            // Auto-generate action_id if not set
            if (!$action->action_id) {
                $action->action_id = 'ACT-' . strtoupper(Str::random(8));
            }
        });
    }
    
    // Relationships
    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id', 'user_id');
    }
    
    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id', 'user_id');
    }
}