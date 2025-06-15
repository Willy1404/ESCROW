<?php

namespace App\Http\Controllers;

use App\Models\PendingAction;
use App\Models\Dispute;
use App\Models\EscrowTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Services\AuditService;

class MakerCheckerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display all pending actions for checkers
     */
    public function index()
    {
        if (!Gate::allows('checker')) {
            abort(403, 'Unauthorized action. Only checkers can view pending actions.');
        }
        
        $pendingActions = PendingAction::with(['maker'])
            ->where('status', 'pending')
            ->whereNot('maker_id', auth()->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.maker-checker.index', compact('pendingActions'));
    }
    
    /**
     * Show pending action details
     */
    public function show($actionId)
    {
        $pendingAction = PendingAction::where('action_id', $actionId)->firstOrFail();
        
        // Load entity details based on entity_type
        $entity = null;
        
        switch ($pendingAction->entity_type) {
            case 'dispute':
                $entity = Dispute::with(['buyer', 'seller', 'escrowTransaction'])
                    ->where('dispute_id', $pendingAction->entity_id)
                    ->first();
                break;
            case 'user':
                if($pendingAction->entity_id != 'new_user') {
                    $entity = User::where('user_id', $pendingAction->entity_id)->first();
                } else {
                    // For new user creation, entity is the action data itself
                    $entity = (object)$pendingAction->action_data;
                }
                break;
            case 'escrow':
                $entity = EscrowTransaction::with(['buyer', 'seller'])
                    ->where('escrow_id', $pendingAction->entity_id)
                    ->first();
                break;
        }
        
        // Determine if current user can approve or reject
        $canApproveOrReject = auth()->user()->role === 'checker' && 
                              auth()->user()->user_id !== $pendingAction->maker_id;
        
        return view('admin.maker-checker.show', compact('pendingAction', 'entity', 'canApproveOrReject'));
    }
    
    /**
     * Approve a pending action
     */
    public function approve(Request $request, $actionId)
    {
        $pendingAction = PendingAction::where('action_id', $actionId)->firstOrFail();
        
        if (!Gate::allows('check-action', $pendingAction)) {
            return redirect()->back()->with('error', 'You cannot approve your own actions');
        }
        
        try {
            // Begin transaction
            \DB::beginTransaction();
            
            // Process the action based on action_type
            switch ($pendingAction->action_type) {
                case 'dispute_resolution':
                    $this->processDisputeResolution($pendingAction);
                    break;
                case 'user_role_change':
                    $this->processUserRoleChange($pendingAction);
                    break;
                case 'user_suspension':
                    $this->processUserSuspension($pendingAction);
                    break;
                case 'user_creation':
                    $this->processUserCreation($pendingAction);
                    break;
                case 'user_update':
                    $this->processUserUpdate($pendingAction);
                    break;
                case 'user_deletion':
                    $this->processUserDeletion($pendingAction);
                    break;
                default:
                    throw new \Exception("Unknown action type: {$pendingAction->action_type}");
            }
            
            // Update pending action status
            $pendingAction->status = 'approved';
            $pendingAction->checker_id = auth()->user()->user_id;
            $pendingAction->approved_at = now();
            $pendingAction->save();
            
            // Log the maker-checker approval
            AuditService::logMakerCheckerAction(
                $pendingAction, 
                'approved', 
                "Action {$pendingAction->action_type} approved by {$pendingAction->checker_id}"
            );
            
            \DB::commit();
            
            return redirect()->route('admin.maker-checker.index')
                ->with('success', 'Action has been approved and processed successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error("Action approval failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process the action: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject a pending action
     */
    public function reject(Request $request, $actionId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5'
        ]);
        
        $pendingAction = PendingAction::where('action_id', $actionId)->firstOrFail();
        
        if (!Gate::allows('check-action', $pendingAction)) {
            return redirect()->back()->with('error', 'You cannot reject your own actions');
        }
        
        $pendingAction->status = 'rejected';
        $pendingAction->checker_id = auth()->user()->user_id;
        $pendingAction->rejection_reason = $request->rejection_reason;
        $pendingAction->rejected_at = now();
        $pendingAction->save();
        
        // Log the maker-checker rejection
        AuditService::logMakerCheckerAction(
            $pendingAction, 
            'rejected', 
            "Action {$pendingAction->action_type} rejected by {$pendingAction->checker_id} with reason: {$request->rejection_reason}"
        );
        
        return redirect()->route('admin.maker-checker.index')
            ->with('success', 'Action has been rejected successfully');
    }
    
    /**
     * Process a dispute resolution action
     */
    private function processDisputeResolution(PendingAction $pendingAction)
    {
        $actionData = $pendingAction->action_data;
        $dispute = Dispute::where('dispute_id', $pendingAction->entity_id)->firstOrFail();
        
        // Apply the changes from the pending action
        $dispute->resolution = $actionData['resolution'];
        $dispute->status = $actionData['status'];
        $dispute->resolved_by = auth()->user()->user_id;
        $dispute->save();
        
        // Update escrow transaction status
        $escrow = $dispute->escrowTransaction;
        if ($actionData['status'] === 'Resolved') {
            if (isset($actionData['refund_buyer']) && $actionData['refund_buyer']) {
                $escrow->status = 'Funds Released'; // To buyer
                // Logic to refund buyer would go here
            } else {
                $escrow->status = 'Funds Released'; // To seller
            }
        } else {
            $escrow->status = 'Waiting for Buyer Approval'; // Return to previous state
        }
        $escrow->save();
    }
    
    /**
     * Process a user role change action
     */
    private function processUserRoleChange(PendingAction $pendingAction)
    {
        $actionData = $pendingAction->action_data;
        $user = User::where('user_id', $pendingAction->entity_id)->firstOrFail();
        
        // Apply the role change
        $user->role = $actionData['new_role'];
        $user->save();
        
        Log::info('User role changed', [
            'user_id' => $user->user_id,
            'old_role' => $user->getOriginal('role'),
            'new_role' => $user->role
        ]);
    }
    
    /**
     * Process a user suspension action
     * This method handles both suspension and activation of user accounts
     */
    private function processUserSuspension(PendingAction $pendingAction)
{
    $actionData = $pendingAction->action_data;
    $user = User::where('user_id', $pendingAction->entity_id)->firstOrFail();
    
    // Apply suspension or activation status
    $user->status = $actionData['status']; // 'suspended' or 'active'
    $user->save();
    
    $statusChange = $actionData['status'] === 'suspended' ? 'suspended' : 'activated';
    
    Log::info('User status changed', [
        'user_id' => $user->user_id,
        'old_status' => $user->getOriginal('status'),
        'new_status' => $user->status,
        'action' => $statusChange,
        'reason' => $actionData['reason'] ?? 'No reason provided'
    ]);
}
    
    /**
     * Process a user creation action
     */
    private function processUserCreation(PendingAction $pendingAction)
    {
        $actionData = $pendingAction->action_data;
        
        // Create the new user
        $user = User::create([
            'name' => $actionData['name'],
            'email' => $actionData['email'],
            'role' => $actionData['role'],
            'status' => 'active',
            'password' => $actionData['password'], // Already hashed in AdminController
        ]);
        
        // Update the pending action with the actual user_id
        $pendingAction->entity_id = $user->user_id;
        $pendingAction->save();
        
        Log::info('New user created via maker-checker process', [
            'user_id' => $user->user_id, 
            'name' => $user->name,
            'role' => $user->role
        ]);
    }
    
    /**
     * Process a user update action
     */
    private function processUserUpdate(PendingAction $pendingAction)
    {
        $actionData = $pendingAction->action_data;
        $user = User::where('user_id', $pendingAction->entity_id)->firstOrFail();
        
        // Apply the updates
        $user->name = $actionData['name'];
        $user->email = $actionData['email'];
        $user->role = $actionData['role'];
        $user->save();
        
        Log::info('User updated via maker-checker process', [
            'user_id' => $user->user_id, 
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]);
    }
    
    /**
     * Process a user deletion action
     */
    private function processUserDeletion(PendingAction $pendingAction)
    {
        $user = User::where('user_id', $pendingAction->entity_id)->firstOrFail();
        
        // Log deletion for audit purposes
        Log::info('User deleted via maker-checker process', [
            'user_id' => $user->user_id, 
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'reason' => $pendingAction->action_data['reason'] ?? 'No reason provided'
        ]);
        
        // Delete the user
        $user->delete();
    }
    
    /**
     * Display actions submitted by the current user
     */
    public function myActions()
    {
        if (!Gate::allows('maker')) {
            abort(403, 'Unauthorized action. Only makers can view their submitted actions.');
        }
        
        // Get all actions submitted by the current user
        $allPendingActions = PendingAction::where('maker_id', auth()->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Group actions by type
        $actionsByType = $allPendingActions->groupBy('action_type');
        
        // Get paginated actions for the regular view
        $pendingActions = PendingAction::where('maker_id', auth()->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.maker-checker.my-actions', compact('pendingActions', 'actionsByType'));
    }
}