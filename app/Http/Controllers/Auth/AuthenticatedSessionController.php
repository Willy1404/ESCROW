<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            // First, check credentials without logging in
            if (Auth::validate($request->only('email', 'password'))) {
                // Get the user by email
                $user = \App\Models\User::where('email', $request->email)->first();
                
                // Check if user is suspended
                if ($user && $user->status === 'suspended') {
                    // Log the attempt
                    AuditService::logLogin($request->email, false, 'Account suspended');
                    
                    throw ValidationException::withMessages([
                        'email' => 'Your account has been suspended. Please contact the administrator.',
                    ]);
                }
            }
            
            // If not suspended, proceed with authentication
            $request->authenticate();
            
            // Log successful login
            AuditService::logLogin($request->email, true);
            
            $request->session()->regenerate();

            // Redirect based on user role
            $user = Auth::user();
            
            // Update this section to properly handle IT Support role
            if ($user->role === 'it_support') {
                return redirect()->route('it_support.dashboard');
            } elseif (in_array($user->role, ['maker', 'checker'])) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Rethrow the exception if it's about suspension (we already added the message)
            if (isset($e->errors()['email']) && str_contains($e->errors()['email'][0], 'suspended')) {
                throw $e;
            }
            
            // Log failed login
            AuditService::logLogin($request->email, false, 'Invalid credentials');
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log logout
        if (Auth::check()) {
            AuditService::log('logout', 'user', Auth::user()->user_id);
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}