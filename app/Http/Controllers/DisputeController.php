<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\EscrowTransaction;
use App\Models\TransactionPhoto;
use App\Models\PendingAction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class DisputeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'buyer') {
            $disputes = Dispute::with(['buyer', 'seller'])
                ->where('buyer_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif ($user->role === 'seller') {
            $disputes = Dispute::with(['buyer', 'seller'])
                ->where('seller_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // For bank staff, show all disputes
            $disputes = Dispute::with(['buyer', 'seller'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            // For checkers specifically, we'll filter in the view to show
            // only disputes with pending actions requiring their approval
        }
        
        return view('disputes.index', compact('disputes'));
    }
    
    public function create($escrowId)
    {
        // Find the escrow transaction
        $escrow = EscrowTransaction::where('escrow_id', $escrowId)->firstOrFail();
        
        // Check if user is the buyer
        if (auth()->user()->user_id !== $escrow->buyer_id) {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Only the buyer can create a dispute.');
        }
        
        // Check if transaction is in the correct state
        if ($escrow->status !== 'Waiting for Buyer Approval') {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Disputes can only be created after delivery but before approval.');
        }
        
        return view('disputes.create', compact('escrow'));
    }
    
    public function store(Request $request, $escrowId)
    {
        // Validate request
        $request->validate([
            'reason' => 'required|string|min:10',
            'photo' => 'required|image|max:5120', // Make photo required, 5MB max
            'additional_photos.*' => 'nullable|image|max:5120', // Allow additional photos
        ]);
        
        // Find the escrow transaction
        $escrow = EscrowTransaction::where('escrow_id', $escrowId)->firstOrFail();
        
        // Check if user is the buyer
        if (auth()->user()->user_id !== $escrow->buyer_id) {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Only the buyer can create a dispute.');
        }
        
        // Create dispute
        $dispute = new Dispute();
        $dispute->dispute_id = 'DISP-' . Str::random(8);
        $dispute->escrow_id = $escrow->escrow_id;
        $dispute->buyer_id = $escrow->buyer_id;
        $dispute->seller_id = $escrow->seller_id;
        $dispute->reason = $request->reason;
        $dispute->status = 'Pending';
        $dispute->save();
        
        // Handle primary photo upload
        $file = $request->file('photo');
        $fileName = time() . '_' . $file->getClientOriginalName();
        
        $filePath = $file->storeAs(
            'transaction_photos/' . $escrowId, 
            $fileName, 
            'public'
        );
        
        // Create primary photo record
        TransactionPhoto::create([
            'escrow_id' => $escrowId,
            'uploader_id' => auth()->user()->user_id,
            'photo_type' => 'dispute_evidence',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => 'Dispute evidence: ' . Str::limit($request->reason, 100),
            'dispute_id' => $dispute->dispute_id
        ]);
        
        // Handle additional photos if any
        if ($request->hasFile('additional_photos')) {
            foreach ($request->file('additional_photos') as $additionalPhoto) {
                $addFileName = time() . '_' . rand(1000, 9999) . '_' . $additionalPhoto->getClientOriginalName();
                
                $addFilePath = $additionalPhoto->storeAs(
                    'transaction_photos/' . $escrowId, 
                    $addFileName, 
                    'public'
                );
                
                TransactionPhoto::create([
                    'escrow_id' => $escrowId,
                    'uploader_id' => auth()->user()->user_id,
                    'photo_type' => 'dispute_evidence',
                    'file_name' => $addFileName,
                    'file_path' => $addFilePath,
                    'mime_type' => $additionalPhoto->getMimeType(),
                    'file_size' => $additionalPhoto->getSize(),
                    'description' => 'Additional dispute evidence',
                    'dispute_id' => $dispute->dispute_id
                ]);
            }
        }
        
        // Update escrow status
        $escrow->status = 'Escrow On Hold';
        $escrow->save();
        
        return redirect()->route('escrow.show', $escrow->escrow_id)
            ->with('success', 'Dispute created successfully with evidence photos.');
    }
    
    public function show($disputeId)
    {
        $dispute = Dispute::where('dispute_id', $disputeId)->firstOrFail();
        $user = auth()->user();
        
        // Check if user is involved or is bank staff
        if (!in_array($user->role, ['maker', 'checker', 'bank_staff']) && 
            $user->user_id !== $dispute->buyer_id && 
            $user->user_id !== $dispute->seller_id) {
            abort(403, 'Unauthorized');
        }
        
        // For checkers, verify there's a pending action related to this dispute
        if ($user->role === 'checker') {
            $pendingActions = PendingAction::where('entity_type', 'dispute')
                ->where('entity_id', $disputeId)
                ->where('status', 'pending')
                ->count();
                
            if ($pendingActions === 0) {
                // Redirect checker to pending actions list
                return redirect()->route('admin.maker-checker.index')
                    ->with('info', 'As a checker, you can only view disputes that have pending resolutions to approve or reject.');
            }
        }
        
        return view('disputes.show', compact('dispute'));
    }
    
    public function resolveForm($disputeId)
    {
        $dispute = Dispute::where('dispute_id', $disputeId)->firstOrFail();
        
        // Check if user is a maker
        if (auth()->user()->role !== 'maker') {
            return redirect()->route('disputes.show', $dispute->dispute_id)
                ->with('error', 'Only makers can initiate dispute resolutions.');
        }
        
        return view('disputes.resolve', compact('dispute'));
    }
    
    public function resolve(Request $request, $disputeId)
    {
        // Validate request
        $request->validate([
            'resolution' => 'required|string',
            'status' => 'required|in:Resolved,Rejected',
        ]);
        
        $dispute = Dispute::where('dispute_id', $disputeId)->firstOrFail();
        
        // Check if user is a maker
        if (auth()->user()->role !== 'maker') {
            return redirect()->route('disputes.show', $dispute->dispute_id)
                ->with('error', 'Only makers can initiate dispute resolutions.');
        }
        
        // Create a pending action instead of directly resolving
        $actionData = [
            'resolution' => $request->resolution,
            'status' => $request->status,
        ];
        
        if ($request->has('refund_buyer') && $request->refund_buyer) {
            $actionData['refund_buyer'] = true;
        }
        
        PendingAction::create([
            'maker_id' => auth()->user()->user_id,
            'action_type' => 'dispute_resolution',
            'action_data' => $actionData,
            'entity_type' => 'dispute',
            'entity_id' => $dispute->dispute_id,
            'description' => "Dispute resolution for {$dispute->dispute_id}: " . ($request->status == 'Resolved' ? 'Resolved' : 'Rejected')
        ]);
        
        return redirect()->route('disputes.show', $dispute->dispute_id)
            ->with('success', 'Dispute resolution has been submitted for approval.');
    }
}