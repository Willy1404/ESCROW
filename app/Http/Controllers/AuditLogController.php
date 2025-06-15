<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Only IT Support can access audit logs
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'it_support') {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }
    
    /**
     * Display the audit log page
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');
        
        // Apply filters if provided
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('entity_type') && $request->entity_type) {
            $query->where('entity_type', $request->entity_type);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get common action types for the filter dropdown
        $actionTypes = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');
            
        // Get users for the filter dropdown
        $users = \App\Models\User::orderBy('name')
            ->get(['user_id', 'name', 'role']);
            
        // Get entity types for the filter dropdown
        $entityTypes = AuditLog::select('entity_type')
            ->whereNotNull('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');
            
        $logs = $query->paginate(20);
        
        return view('admin.audit-log', compact(
            'logs', 
            'actionTypes',
            'users',
            'entityTypes'
        ));
    }
    
    /**
     * Display details of a specific audit log entry
     */
    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        
        return view('admin.audit-log-details', compact('log'));
    }
}