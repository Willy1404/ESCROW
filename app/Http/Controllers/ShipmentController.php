<?php

namespace App\Http\Controllers;

use App\Models\EscrowTransaction;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function update(Request $request, $escrowId)
    {
        // Validate request
        $request->validate([
            'carrier' => 'required|string',
            'carrier_other' => 'required_if:carrier,Other',
            'estimated_arrival' => 'required|date',
        ]);
        
        // Find the escrow transaction
        $escrow = EscrowTransaction::where('escrow_id', $escrowId)->firstOrFail();
        
        // Check if user is the seller
        if (auth()->user()->user_id !== $escrow->seller_id) {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Only the seller can update shipment details.');
        }
        
        // Check if transaction is in the correct state
        if ($escrow->status !== 'Funds Received') {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Shipment can only be updated when funds are received.');
        }
        
        // Determine the carrier value
        $carrierValue = $request->carrier;
        if ($request->carrier === 'Other' && $request->has('carrier_other')) {
            $carrierValue = $request->carrier_other;
        }
        
        // Create or update shipment
        $shipment = Shipment::updateOrCreate(
            ['escrow_id' => $escrow->escrow_id],
            [
                'tracking_id' => $escrow->escrow_id, // Use escrow_id as tracking_id
                'carrier' => $carrierValue,
                'estimated_arrival' => $request->estimated_arrival,
                'status' => 'In Transit',
            ]
        );
        
        // Update escrow status
        $escrow->status = 'In Transit';
        $escrow->save();
        
        return redirect()->route('escrow.show', $escrow->escrow_id)
            ->with('success', 'Shipment details updated successfully.');
    }
    
    public function confirm($escrowId)
    {
        // Find the escrow transaction
        $escrow = EscrowTransaction::where('escrow_id', $escrowId)->firstOrFail();
        
        // Check if user is the buyer (changed from seller to buyer)
        if (auth()->user()->user_id !== $escrow->buyer_id) {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Only the buyer can confirm delivery.');
        }
        
        // Check if transaction is in the correct state
        if ($escrow->status !== 'In Transit') {
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('error', 'Delivery can only be confirmed when shipment is in transit.');
        }
        
        // Update shipment status
        $shipment = Shipment::where('escrow_id', $escrow->escrow_id)->firstOrFail();
        $shipment->status = 'Delivered';
        $shipment->save();
        
        // Update escrow status
        $escrow->status = 'Waiting for Buyer Approval';
        $escrow->save();
        
        return redirect()->route('escrow.show', $escrow->escrow_id)
            ->with('success', 'Delivery confirmed successfully.');
    }
}