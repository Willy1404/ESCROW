<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action to the audit trail
     *
     * @param string $action The action being performed
     * @param string|null $entityType The type of entity being affected
     * @param string|null $entityId The ID of the entity being affected
     * @param array|null $oldValues Previous values (for updates)
     * @param array|null $newValues New values (for updates)
     * @param string|null $details Additional details about the action
     * @return \App\Models\AuditLog
     */
    public static function log($action, $entityType = null, $entityId = null, $oldValues = null, $newValues = null, $details = null)
    {
        $userId = Auth::check() ? Auth::user()->user_id : null;
        
        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'details' => $details,
        ]);
    }
    
    /**
     * Log a login attempt
     *
     * @param string $email
     * @param bool $success
     * @param string|null $details
     * @return \App\Models\AuditLog
     */
    public static function logLogin($email, $success = true, $details = null)
    {
        $action = $success ? 'login_success' : 'login_failed';
        return self::log($action, 'user', null, null, null, "Email: {$email}. " . $details);
    }
    
    /**
     * Log a password change
     *
     * @param string $userId
     * @return \App\Models\AuditLog
     */
    public static function logPasswordChange($userId)
    {
        return self::log('password_change', 'user', $userId);
    }
    
    /**
     * Log user creation
     *
     * @param \App\Models\User $user
     * @return \App\Models\AuditLog
     */
    public static function logUserCreation($user)
    {
        $createdBy = Auth::check() ? Auth::user()->user_id : 'system';
        $details = "User created by: {$createdBy}";
        
        return self::log('user_creation', 'user', $user->user_id, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], $details);
    }
    
    /**
     * Log user update
     *
     * @param \App\Models\User $user
     * @param array $oldValues
     * @return \App\Models\AuditLog
     */
    public static function logUserUpdate($user, $oldValues)
    {
        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ];
        
        return self::log('user_update', 'user', $user->user_id, $oldValues, $newValues);
    }
    
    /**
     * Log maker-checker action
     *
     * @param \App\Models\PendingAction $action
     * @param string $status
     * @param string|null $details
     * @return \App\Models\AuditLog
     */
    public static function logMakerCheckerAction($action, $status, $details = null)
    {
        $auditAction = "maker_checker_{$status}";
        return self::log($auditAction, $action->entity_type, $action->entity_id, null, null, $details);
    }
    
    /**
     * Log escrow transaction creation
     *
     * @param \App\Models\EscrowTransaction $escrow
     * @return \App\Models\AuditLog
     */
    public static function logEscrowCreation($escrow)
    {
        return self::log('escrow_creation', 'escrow', $escrow->escrow_id, null, [
            'buyer_id' => $escrow->buyer_id,
            'seller_id' => $escrow->seller_id,
            'amount' => $escrow->amount,
            'currency' => $escrow->currency,
        ]);
    }
    
    /**
     * Log escrow transaction status change
     *
     * @param \App\Models\EscrowTransaction $escrow
     * @param string $oldStatus
     * @return \App\Models\AuditLog
     */
    public static function logEscrowStatusChange($escrow, $oldStatus)
    {
        return self::log('escrow_status_change', 'escrow', $escrow->escrow_id, 
            ['status' => $oldStatus], 
            ['status' => $escrow->status]
        );
    }
    
    /**
     * Log dispute creation
     *
     * @param \App\Models\Dispute $dispute
     * @return \App\Models\AuditLog
     */
    public static function logDisputeCreation($dispute)
    {
        return self::log('dispute_creation', 'dispute', $dispute->dispute_id, null, [
            'escrow_id' => $dispute->escrow_id,
            'buyer_id' => $dispute->buyer_id,
            'seller_id' => $dispute->seller_id,
        ]);
    }
    
    /**
     * Log dispute resolution
     *
     * @param \App\Models\Dispute $dispute
     * @param string $oldStatus
     * @return \App\Models\AuditLog
     */
    public static function logDisputeResolution($dispute, $oldStatus)
    {
        return self::log('dispute_resolution', 'dispute', $dispute->dispute_id, 
            ['status' => $oldStatus], 
            ['status' => $dispute->status, 'resolution' => $dispute->resolution]
        );
    }
}