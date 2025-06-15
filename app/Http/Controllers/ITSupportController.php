<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ITSupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Only IT Support can access these routes
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'it_support') {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }
    
    /**
     * Display a dashboard for IT Support
     */
    public function dashboard()
    {
        $bank_staff_count = User::whereIn('role', ['maker', 'checker'])->count();
        $it_support_count = User::where('role', 'it_support')->count();
        $buyer_count = User::where('role', 'buyer')->count();
        $seller_count = User::where('role', 'seller')->count();
        
        $recent_staff = User::whereIn('role', ['maker', 'checker', 'it_support'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return view('it_support.dashboard', compact(
            'bank_staff_count', 
            'it_support_count', 
            'buyer_count', 
            'seller_count', 
            'recent_staff'
        ));
    }
    
    /**
     * Display a list of bank staff and IT support users
     */
    public function staffList()
    {
        $staff = User::whereIn('role', ['maker', 'checker', 'it_support'])
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15);
            
        return view('it_support.staff.index', compact('staff'));
    }
    
    /**
     * Show the form for creating a new staff member
     */
    public function createStaff()
    {
        return view('it_support.staff.create');
    }
    
    /**
     * Store a newly created staff member
     */
    public function storeStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:maker,checker,it_support',
            'password' => 'required|min:8|confirmed',
        ]);
        
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'status' => 'active',
                'password' => Hash::make($request->password),
            ]);
            
            \Log::info('New staff created', ['user_id' => $user->user_id, 'email' => $user->email]);
            
            return redirect()->route('it_support.staff.index')
                ->with('success', 'Staff member created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating staff', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error creating staff: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Show the form for editing a staff member
     */
    public function editStaff($id)
    {
        $user = User::findOrFail($id);
        
        // Allow editing of any staff type (maker, checker, it_support)
        return view('it_support.staff.edit', compact('user'));
    }
    
    /**
     * Update the specified staff member
     */
    public function updateStaff(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Allow updating any staff type (maker, checker, it_support)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:maker,checker,it_support',
        ]);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->save();
        
        return redirect()->route('it_support.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }
    
    /**
     * Toggle a staff member's status (active/suspended)
     */
    public function toggleStaffStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Allow toggling status for any staff type (maker, checker, it_support)
        
        // Don't allow self-suspension
        if ($user->id === auth()->user()->id) {
            return redirect()->route('it_support.staff.index')
                ->with('error', 'You cannot change your own status.');
        }
        
        $user->status = $user->status === 'active' ? 'suspended' : 'active';
        $user->save();
        
        $statusText = $user->status === 'active' ? 'activated' : 'suspended';
        
        return redirect()->route('it_support.staff.index')
            ->with('success', "Staff member {$statusText} successfully.");
    }
    
    /**
     * Show form to reset a staff member's password
     */
    public function showResetPassword($id)
    {
        $user = User::findOrFail($id);
        
        // Allow password reset for any staff type (maker, checker, it_support)
        return view('it_support.staff.reset_password', compact('user'));
    }
    
    /**
     * Reset a staff member's password
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Allow password reset for any staff type (maker, checker, it_support)
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);
        
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('it_support.staff.index')
            ->with('success', 'Password reset successfully.');
    }
    
    /**
     * Generate a random password
     */
    public function generatePassword()
    {
        $password = Str::random(12); // 12 character random password
        return response()->json(['password' => $password]);
    }
    
    /**
     * Show audit log of staff actions
     */
    public function auditLog()
    {
        return view('it_support.audit_log');
    }
}