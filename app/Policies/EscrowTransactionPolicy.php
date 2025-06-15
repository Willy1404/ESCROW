<?php
// app/Policies/EscrowTransactionPolicy.php
namespace App\Policies;

use App\Models\EscrowTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EscrowTransactionPolicy
{
    use HandlesAuthorization;

/**
     * Determine whether the user can view the escrow transaction.
     */
    public function view(User $user, EscrowTransaction $escrow)
    {
        // Users can view transactions they are involved in
        return $user->user_id === $escrow->buyer_id 
            || $user->user_id === $escrow->seller_id 
            || in_array($user->role, ['maker', 'checker', 'it_support', 'bank_staff']);
    }

    /**
     * Determine whether the user can deposit funds into the escrow.
     */
    public function deposit(User $user, EscrowTransaction $escrow)
    {
        // Only the buyer can deposit funds, and only if the status is 'Funds Pending'
        return $user->user_id === $escrow->buyer_id && $escrow->status === 'Funds Pending';
    }

    /**
     * Determine whether the user can update the shipment details.
     */
    public function updateShipment(User $user, EscrowTransaction $escrow)
    {
        // Only the seller can update shipment, and only if the status is 'Funds Received'
        return $user->user_id === $escrow->seller_id && $escrow->status === 'Funds Received';
    }

    /**
     * Determine whether the user can confirm delivery.
     */
    public function confirmDelivery(User $user, EscrowTransaction $escrow)
    {
        // Only the buyer can confirm delivery, and only if the status is 'In Transit'
        // Changed from seller to buyer
        return $user->user_id === $escrow->buyer_id && $escrow->status === 'In Transit';
    }

    /**
     * Determine whether the user can release funds.
     */
    public function release(User $user, EscrowTransaction $escrow)
    {
        // Only the buyer can release funds, and only if the status is 'Waiting for Buyer Approval'
        return $user->user_id === $escrow->buyer_id && $escrow->status === 'Waiting for Buyer Approval';
    }

    /**
     * Determine whether the user can create a dispute.
     */
    public function createDispute(User $user, EscrowTransaction $escrow)
    {
        // Only the buyer can create a dispute, and only if the status is 'Waiting for Buyer Approval'
        return $user->user_id === $escrow->buyer_id && $escrow->status === 'Waiting for Buyer Approval';
    }
}