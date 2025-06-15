<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'role',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    /**
     * Boot function to auto-generate user_id
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            // Auto-generate user_id if not set
            if (!$user->user_id) {
                $prefix = substr($user->role ?? 'USR', 0, 3);
                $random = strtoupper(substr(md5(uniqid()), 0, 6));
                $user->user_id = "{$prefix}{$random}";
            }
        });
    }

    /**
     * Check if user is a maker
     */
    public function isMaker()
    {
        return $this->role === 'maker';
    }

    /**
     * Check if user is a checker
     */
    public function isChecker()
    {
        return $this->role === 'checker';
    }
}