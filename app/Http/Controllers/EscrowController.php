<?php

namespace App\Http\Controllers;

use App\Models\EscrowTransaction;
use App\Models\TransactionPhoto;
use App\Models\ControlNumber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EscrowController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'buyer') {
            $transactions = EscrowTransaction::where('buyer_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif ($user->role === 'seller') {
            $transactions = EscrowTransaction::where('seller_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // For bank staff (maker, checker), show all transactions
            $transactions = EscrowTransaction::orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('escrow.index', compact('transactions'));
    }
    
    public function create()
    {
        // Only buyers can create escrow transactions
        if (auth()->user()->role !== 'buyer') {
            return redirect()->route('escrow.index')
                ->with('error', 'Only buyers can create escrow transactions.');
        }
        
        return view('escrow.create');
    }
    
    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'control_number' => 'required|string|max:20',
            ]);
            
            // Find and validate control number
            $controlNumber = ControlNumber::where('control_number', $request->control_number)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->first();
                
            if (!$controlNumber) {
                return redirect()->back()
                    ->with('error', 'Invalid or expired control number')
                    ->withInput();
            }
            
            // Create new escrow transaction
            $escrow = new EscrowTransaction();
            $escrow->escrow_id = 'ESC-' . Str::random(8);
            $escrow->buyer_id = auth()->user()->user_id;
            $escrow->seller_id = $controlNumber->seller_id;
            $escrow->amount = $controlNumber->amount;
            $escrow->currency = $controlNumber->currency;
            $escrow->status = 'Funds Pending';
            $escrow->delivery_deadline = $controlNumber->delivery_deadline;
            $escrow->inspection_period = $controlNumber->inspection_period;
            $escrow->contract_terms = json_encode([
                'item' => $controlNumber->item_name,
                'condition' => $controlNumber->item_condition,
                'description' => $controlNumber->item_description,
            ]);
            $escrow->save();
            
            // Associate photos from control number to the escrow transaction
            $photos = TransactionPhoto::where('control_number', $controlNumber->control_number)->get();
            foreach ($photos as $photo) {
                // Create a copy of the photo linked to the escrow
                $newPhoto = $photo->replicate();
                $newPhoto->escrow_id = $escrow->escrow_id;
                $newPhoto->photo_type = 'shipment_evidence'; // Convert to shipment evidence
                $newPhoto->save();
            }
            
            // Mark control number as used
            $controlNumber->is_used = true;
            $controlNumber->save();
            
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('success', 'Escrow transaction created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating escrow: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function show($escrowId)
    {
        $escrow = EscrowTransaction::where('escrow_id', $escrowId)->firstOrFail();
        
        // Check if user is authorized to view this transaction
        if (auth()->user()->role !== 'bank_staff' && 
            auth()->user()->user_id !== $escrow->buyer_id && 
            auth()->user()->user_id !== $escrow->seller_id) {
            abort(403, 'Unauthorized');
        }
        
        return view('escrow.show', compact('escrow'));
    }
}