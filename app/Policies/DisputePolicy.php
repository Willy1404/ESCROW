<?php
// app/Policies/DisputePolicy.php
namespace App\Policies;

use App\Models\Dispute;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisputePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the dispute.
     */
/**
     * Determine whether the user can view the dispute.
     */
    public function view(User $user, Dispute $dispute)
    {
        // Users can view disputes they are involved in
        return $user->user_id === $dispute->buyer_id 
            || $user->user_id === $dispute->seller_id 
            || in_array($user->role, ['maker', 'checker', 'it_support', 'bank_staff']);
    }

    /**
     * Determine whether the user can resolve the dispute.
     */
    public function resolve(User $user, Dispute $dispute)
    {
        // Only makers and checkers can resolve disputes
        return in_array($user->role, ['maker', 'checker']);
    }
}