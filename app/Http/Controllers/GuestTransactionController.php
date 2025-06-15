<?php

namespace App\Http\Controllers;

use App\Models\ControlNumber;
use App\Models\GuestTransaction;
use App\Models\Payment;
use App\Models\EscrowTransaction;
use App\Models\TransactionPhoto;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;


class GuestTransactionController extends Controller
{
    /**
     * Show the control number verification form
     */
    public function showVerifyForm()
    {
        return view('guest.verify');
    }
    
    /**
     * Verify the control number and redirect to payment
     */
    public function verifyControlNumber(Request $request)
    {
        $request->validate([
            'control_number' => 'required|string|max:20'
        ]);
        
        $controlNumber = ControlNumber::where('control_number', $request->control_number)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->with('seller')
            ->first();
            
        if (!$controlNumber) {
            return redirect()->back()
                ->with('error', 'Invalid or expired control number')
                ->withInput();
        }
        
        // Create a guest transaction record
        $guestTransaction = GuestTransaction::create([
            'control_number' => $controlNumber->control_number,
            'amount' => $controlNumber->amount,
            'currency' => $controlNumber->currency,
            'item_name' => $controlNumber->item_name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        // Redirect to payment form
        return redirect()->route('guest.payment', ['token' => $guestTransaction->transaction_token]);
    }
    
    /**
     * Show the payment form
     */
    public function showPaymentForm($token)
    {
        $transaction = GuestTransaction::where('transaction_token', $token)
            ->where('expires_at', '>', now())
            ->where('payment_status', 'pending')
            ->with('controlNumber.seller')
            ->firstOrFail();
            
        // Get photos associated with this control number
        $photos = TransactionPhoto::where('control_number', $transaction->control_number)->get();
        
        return view('guest.payment', compact('transaction', 'photos'));
    }
    
    /**
     * Process the payment
     */
    public function processPayment(Request $request, $token)
    {
        $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email|max:255',
            'payment_method' => 'required|string|in:Bank Transfer,Credit Card,Mobile Money',
            'create_account' => 'nullable|boolean',
            'password' => 'required_if:create_account,1|nullable|min:8|confirmed',
        ]);
        
        $transaction = GuestTransaction::where('transaction_token', $token)
            ->where('expires_at', '>', now())
            ->where('payment_status', 'pending')
            ->with('controlNumber.seller')
            ->firstOrFail();
            
        // Update guest transaction with buyer details
        $transaction->buyer_name = $request->buyer_name;
        $transaction->buyer_email = $request->buyer_email;
        $transaction->save();
        
        // Process payment (in a real app, you'd integrate with a payment gateway here)
        
        // For demonstration, we'll just mark it as completed
        $transaction->payment_status = 'completed';
        $transaction->save();

        // Send payment receipt email
        Mail::to($transaction->buyer_email)->send(new \App\Mail\GuestTransactionReceipt($transaction));
        
        // Create account if requested
        if ($request->create_account) {
            // Check if email already exists
            $existingUser = \App\Models\User::where('email', $request->buyer_email)->first();
            
            if (!$existingUser) {
                $user = \App\Models\User::create([
                    'name' => $request->buyer_name,
                    'email' => $request->buyer_email,
                    'role' => 'buyer',
                    'status' => 'active',
                    'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                ]);
                
                // Convert guest transaction to escrow
                $escrow = $transaction->convertToEscrow($user->user_id);
                
                // Create payment record
                if ($escrow) {
                    $payment = Payment::create([
                        'payment_id' => 'PAY-' . Str::random(8),
                        'escrow_id' => $escrow->escrow_id,
                        'buyer_id' => $user->user_id,
                        'amount' => $escrow->amount,
                        'payment_method' => $request->payment_method,
                        'status' => 'Completed',
                    ]);
                    
                    // Update escrow status
                    $escrow->status = 'Funds Received';
                    $escrow->save();
                    
                    // Log the transaction
                    AuditService::log('escrow_creation', 'escrow', $escrow->escrow_id, null, [
                        'buyer_id' => $escrow->buyer_id,
                        'seller_id' => $escrow->seller_id,
                        'amount' => $escrow->amount,
                        'currency' => $escrow->currency,
                    ]);
                    
                    // Auto-login the new user
                    Auth::login($user);
                    
                    return redirect()->route('escrow.show', $escrow->escrow_id)
                        ->with('success', 'Account created and payment processed successfully.');
                }
            } else {
                return redirect()->route('login')
                    ->with('error', 'An account with this email already exists. Please log in to claim your transaction.');
            }
        }
        
        // If no account creation, just show success page
        return redirect()->route('guest.success', ['transaction_id' => $transaction->id]);
    }
    
    /**
     * Show transaction success page
     */
    public function showTransactionSuccess($transaction_id)
    {
        $transaction = GuestTransaction::findOrFail($transaction_id);
        
        // Check if transaction is completed
        if ($transaction->payment_status != 'completed') {
            return redirect()->route('guest.verify')
                ->with('error', 'Invalid transaction');
        }
        
        return view('guest.success', compact('transaction'));
    }
    
    /**
     * Claim a guest transaction after login
     */
    public function claimTransaction(Request $request, $token)
    {
        $transaction = GuestTransaction::where('transaction_token', $token)
            ->where('payment_status', 'completed')
            ->whereNull('escrow_id')
            ->firstOrFail();
            
        // Verify email matches
        if ($transaction->buyer_email != Auth::user()->email) {
            return redirect()->route('dashboard')
                ->with('error', 'The email address associated with this transaction does not match your account.');
        }
        
        // Convert to escrow
        $escrow = $transaction->convertToEscrow(Auth::user()->user_id);
        
        if (!$escrow) {
            return redirect()->route('dashboard')
                ->with('error', 'Unable to claim the transaction. The control number may have been used.');
        }
        
        // Create payment record
        $payment = Payment::create([
            'payment_id' => 'PAY-' . Str::random(8),
            'escrow_id' => $escrow->escrow_id,
            'buyer_id' => Auth::user()->user_id,
            'amount' => $escrow->amount,
            'payment_method' => 'Guest Payment',
            'status' => 'Completed',
        ]);
        
        // Update escrow status
        $escrow->status = 'Funds Received';
        $escrow->save();
        
        // Log the transaction
        AuditService::log('escrow_creation', 'escrow', $escrow->escrow_id, null, [
            'buyer_id' => $escrow->buyer_id,
            'seller_id' => $escrow->seller_id,
            'amount' => $escrow->amount,
            'currency' => $escrow->currency,
        ]);
        
        return redirect()->route('escrow.show', $escrow->escrow_id)
            ->with('success', 'Transaction claimed successfully.');
    }
}