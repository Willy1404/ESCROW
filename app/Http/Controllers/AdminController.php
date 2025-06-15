<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EscrowTransaction;
use App\Models\Dispute;
use App\Models\PendingAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        // Check if user is maker, checker, or it_support (bank_staff is transitional)
        if (auth()->check() && !in_array(auth()->user()->role, ['maker', 'checker', 'it_support', 'bank_staff'])) {
            abort(403, 'Unauthorized');
        }
    }
    
    public function dashboard()
    {
        $total_users = User::count();
        $total_transactions = EscrowTransaction::count();
        $total_disputes = Dispute::count();
        $total_value = EscrowTransaction::sum('amount');
        
        $status_distribution = EscrowTransaction::select('status')
            ->selectRaw('count(*) as count, sum(amount) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
            
        $recent_transactions = EscrowTransaction::with(['buyer', 'seller'])
            ->latest()
            ->take(5)
            ->get();
            
        $recent_disputes = Dispute::with(['buyer', 'seller'])
            ->latest()
            ->take(5)
            ->get();
        
        $stats = [
            'total_users' => $total_users,
            'buyers' => User::where('role', 'buyer')->count(),
            'sellers' => User::where('role', 'seller')->count(),
            'transactions' => $total_transactions,
            'active_escrows' => EscrowTransaction::whereNotIn('status', ['Funds Released'])->count(),
            'completed_escrows' => EscrowTransaction::where('status', 'Funds Released')->count(),
            'disputes' => $total_disputes,
            'pending_disputes' => Dispute::where('status', 'Pending')->count(),
            'total_value' => $total_value,
            'released_value' => EscrowTransaction::where('status', 'Funds Released')->sum('amount'),
        ];
        
        return view('admin.dashboard', compact(
            'stats', 
            'status_distribution',
            'recent_transactions',
            'recent_disputes'
        ));
    }
    
    public function users()
    {
        // Only fetch buyers and sellers for the user list
        if (auth()->user()->role === 'maker') {
            $users = User::whereIn('role', ['buyer', 'seller'])->get();
        } else {
            // For checkers and others, show all users
            $users = User::all();
        }
        
        return view('admin.users.index', compact('users'));
    }
    
    public function createUser()
    {
        // Check if user is a maker
        if (auth()->user()->role !== 'maker') {
            return redirect()->route('admin.users')->with('error', 'Only makers can create new users.');
        }
        
        return view('admin.users.create');
    }
    
    public function storeUser(Request $request)
    {
        // Check if user is a maker
        if (auth()->user()->role !== 'maker') {
            return redirect()->route('admin.users')->with('error', 'Only makers can create new users.');
        }
        
        // Ensure only buyer or seller roles can be created by makers
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:buyer,seller', // Only buyer or seller roles allowed
            'password' => 'required|min:8|confirmed',
        ]);
        
        // Create a pending action for user creation
        PendingAction::create([
            'maker_id' => auth()->user()->user_id,
            'action_type' => 'user_creation',
            'action_data' => [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ],
            'entity_type' => 'user',
            'entity_id' => 'new_user', // This will be updated when the user is created
            'description' => "Create new {$request->role} user: {$request->name} ({$request->email})"
        ]);
        
        return redirect()->route('admin.users')->with('success', 'User creation request has been submitted for approval.');
    }
    
    public function editUser($id)
    {
        // Check if user is a maker
        if (auth()->user()->role !== 'maker') {
            return redirect()->route('admin.users')->with('error', 'Only makers can edit users.');
        }
        
        $user = User::findOrFail($id);
        
        // Maker can only edit buyers and sellers
        if (!in_array($user->role, ['buyer', 'seller'])) {
            return redirect()->route('admin.users')->with('error', 'Makers can only manage buyer and seller accounts.');
        }
        
        return view('admin.users.edit', compact('user'));
    }
    
    public function updateUser(Request $request, $id)
    {
        // Check if user is a maker
        if (auth()->user()->role !== 'maker') {
            return redirect()->route('admin.users')->with('error', 'Only makers can update users.');
        }
        
        $user = User::findOrFail($id);
        
        // Maker can only update buyers and sellers
        if (!in_array($user->role, ['buyer', 'seller'])) {
            return redirect()->route('admin.users')->with('error', 'Makers can only manage buyer and seller accounts.');
        }
        
        // Ensure the user can only be updated to buyer or seller roles
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:buyer,seller', // Only buyer or seller roles allowed
        ]);
        
        // Create a pending action for user update
        PendingAction::create([
            'maker_id' => auth()->user()->user_id,
            'action_type' => 'user_update',
            'action_data' => [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role
            ],
            'entity_type' => 'user',
            'entity_id' => $user->user_id,
            'description' => "Update user {$user->name} (ID: {$user->user_id})"
        ]);
        
        return redirect()->route('admin.users')->with('success', 'User update request has been submitted for approval.');
    }
    
    public function transactions()
    {
        $transactions = EscrowTransaction::with(['buyer', 'seller'])->get();
        return view('admin.transactions', compact('transactions'));
    }
    
    public function disputes()
    {
        $disputes = Dispute::with(['buyer', 'seller', 'escrowTransaction'])->get();
        return view('admin.disputes', compact('disputes'));
    }
    
    public function showDispute($id)
    {
        $dispute = Dispute::with([
            'buyer', 
            'seller', 
            'escrowTransaction.photos.uploader',
            'escrowTransaction.payments',
            'escrowTransaction.shipments'
        ])->findOrFail($id);
        
        return view('admin.disputes.show', compact('dispute'));
    }
    
    public function resolveDispute(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'resolution' => 'required|string',
            'status' => 'required|in:Resolved,Rejected',
        ]);
        
        $dispute = Dispute::findOrFail($id);
        
        // Create a pending action instead of directly resolving
        $actionData = [
            'resolution' => $request->resolution,
            'status' => $request->status,
        ];
        
        if ($request->has('refund_buyer')) {
            $actionData['refund_buyer'] = $request->refund_buyer;
        }
        
        PendingAction::create([
            'maker_id' => auth()->user()->user_id,
            'action_type' => 'dispute_resolution',
            'action_data' => $actionData,
            'entity_type' => 'dispute',
            'entity_id' => $dispute->dispute_id,
            'description' => "Dispute resolution for {$dispute->dispute_id}: " . ($request->status == 'Resolved' ? 'Resolved' : 'Rejected')
        ]);
        
        return redirect()->route('admin.disputes')->with('success', 'Dispute resolution has been submitted for approval.');
    }
    
    public function reports()
    {
        // Placeholder for reports functionality
        $monthly_transactions = EscrowTransaction::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count, SUM(amount) as amount')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
            
        $total_transactions = EscrowTransaction::count();
        $disputed_transactions = Dispute::count();
        
        $dispute_rate = [
            'total_transactions' => $total_transactions,
            'disputed' => $disputed_transactions,
            'percentage' => $total_transactions > 0 ? round(($disputed_transactions / $total_transactions) * 100, 2) : 0,
        ];
        
        return view('admin.reports', compact('monthly_transactions', 'dispute_rate'));
    }
    
    public function suspendUser(Request $request, $id)
{
    // Check if user is a maker
    if (auth()->user()->role !== 'maker') {
        return redirect()->route('admin.users')->with('error', 'Only makers can initiate user status changes.');
    }
    
    $request->validate([
        'suspension_reason' => 'nullable|string|max:500',
    ]);
    
    $user = User::findOrFail($id);
    
    // Maker can only suspend/activate buyers and sellers
    if (!in_array($user->role, ['buyer', 'seller'])) {
        return redirect()->route('admin.users')->with('error', 'Makers can only manage buyer and seller accounts.');
    }

        // Get the new status (opposite of current status)
        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        
        // Create a pending action
        PendingAction::create([
            'maker_id' => auth()->user()->user_id,
            'action_type' => 'user_suspension',
            'action_data' => [
                'status' => $newStatus,
                'reason' => $request->suspension_reason ?? ($newStatus === 'active' ? 'Reactivation of suspended account' : 'No reason provided')
            ],
            'entity_type' => 'user',
            'entity_id' => $user->user_id,
            'description' => "Change user status to " . $newStatus . " for {$user->name}"
        ]);
        
        return redirect()->route('admin.users')->with('success', 'User status change request has been submitted for approval.');
    }

    public function changeUserRole(Request $request, $id)
    {
        // Check if user is a maker
        if (auth()->user()->role !== 'maker') {
            return redirect()->route('admin.users')->with('error', 'Only makers can initiate user role changes.');
        }
        
        // Verify role is only buyer or seller
        $request->validate([
            'role' => 'required|in:buyer,seller',
        ]);
        
        $user = User::findOrFail($id);
        
        // Maker can only change roles for buyers and sellers
        if (!in_array($user->role, ['buyer', 'seller'])) {
            return redirect()->route('admin.users')->with('error', 'Makers can only manage buyer and seller accounts.');
        }
        
        if ($user->role === $request->role) {
            return redirect()->back()->with('info', 'User already has this role.');
        }
        
        // Create a pending action
        PendingAction::create([
            'maker_id' => auth()->user()->user_id,
            'action_type' => 'user_role_change',
            'action_data' => [
                'new_role' => $request->role
            ],
            'entity_type' => 'user',
            'entity_id' => $user->user_id,
            'description' => "Change user role from {$user->role} to {$request->role} for {$user->name}"
        ]);
        
        return redirect()->route('admin.users')->with('success', 'User role change request has been submitted for approval.');
    }
    
    /**
     * Delete a user (creates a pending action for approval)
     */
    public function deleteUser(Request $request, $id)
    {
        // Check if user is a maker
        if (auth()->user()->role !== 'maker') {
            return redirect()->route('admin.users')->with('error', 'Only makers can initiate user deletion.');
        }
        
        $request->validate([
            'deletion_reason' => 'required|string|min:10|max:500',
        ]);
        
        $user = User::findOrFail($id);
        
        // Maker can only delete buyers and sellers
        if (!in_array($user->role, ['buyer', 'seller'])) {
            return redirect()->route('admin.users')->with('error', 'Makers can only manage buyer and seller accounts.');
        }
        
        // Check if user has any transactions or disputes
        $hasTransactions = EscrowTransaction::where('buyer_id', $user->user_id)
            ->orWhere('seller_id', $user->user_id)
            ->exists();
            
        $hasDisputes = Dispute::where('buyer_id', $user->user_id)
            ->orWhere('seller_id', $user->user_id)
            ->exists();
            
        if ($hasTransactions || $hasDisputes) {
            return redirect()->route('admin.users')
                ->with('error', 'This user cannot be deleted because they have transaction or dispute records. Consider suspending the account instead.');
        }
        
        // Create a pending action for deletion
        PendingAction::create([
            'maker_id' => auth()->user()->user_id,
            'action_type' => 'user_deletion',
            'action_data' => [
                'reason' => $request->deletion_reason,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role
            ],
            'entity_type' => 'user',
            'entity_id' => $user->user_id,
            'description' => "Delete user {$user->name} (ID: {$user->user_id})"
        ]);
        
        return redirect()->route('admin.users')->with('success', 'User deletion request has been submitted for approval.');
    }
}