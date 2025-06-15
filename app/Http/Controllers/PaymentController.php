<?php

namespace App\Http\Controllers;

use App\Models\EscrowTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'buyer') {
            $payments = Payment::where('buyer_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif ($user->role === 'seller') {
            $payments = Payment::whereIn('escrow_id', function($query) use ($user) {
                $query->select('escrow_id')
                    ->from('escrow_transactions')
                    ->where('seller_id', $user->user_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        } else {
            // For bank staff, show all payments
            $payments = Payment::orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('payments.index', compact('payments'));
    }
    
    public function deposit(Request $request, $escrowId)
    {
        // Validate request
        $request->validate([
            'payment_method' => 'required|string',
        ]);
        
        // Find the escrow transaction
        $escrow = EscrowTransaction::where('escrow_id', $escrowId)->firstOrFail();
        
        // Check if user is the buyer
        if (auth()->user()->user_id !== $escrow->buyer_id) {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Only the buyer can deposit funds.');
        }
        
        // Check if transaction is in the correct state
        if ($escrow->status !== 'Funds Pending') {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Funds have already been deposited for this transaction.');
        }
        
        // Create new payment
        $payment = new Payment();
        $payment->payment_id = 'PAY-' . Str::random(8);
        $payment->escrow_id = $escrow->escrow_id;
        $payment->buyer_id = auth()->user()->user_id;
        $payment->amount = $escrow->amount;
        $payment->payment_method = $request->payment_method;
        $payment->status = 'Completed'; // In a real app, would be set by payment gateway
        $payment->save();
        
        // Update escrow status
        $escrow->status = 'Funds Received';
        $escrow->save();
        
        return redirect()->route('escrow.show', $escrow->escrow_id)
            ->with('success', 'Payment deposited successfully.');
    }
    
    public function release($escrowId)
    {
        // Find the escrow transaction
        $escrow = EscrowTransaction::where('escrow_id', $escrowId)->firstOrFail();
        
        // Check if user is the buyer or bank staff (maker/checker)
        if (auth()->user()->user_id !== $escrow->buyer_id && 
            !in_array(auth()->user()->role, ['maker', 'checker', 'bank_staff'])) {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Only the buyer or bank staff can release funds.');
        }
        
        // Check if transaction is in the correct state
        if ($escrow->status !== 'Waiting for Buyer Approval') {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'This transaction is not ready for fund release.');
        }
        
        // Update escrow status
        $escrow->status = 'Funds Released';
        $escrow->save();
        
        return redirect()->route('escrow.show', $escrow->escrow_id)
            ->with('success', 'Funds released to seller successfully.');
    }
}