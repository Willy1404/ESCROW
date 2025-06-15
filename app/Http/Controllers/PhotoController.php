<?php

namespace App\Http\Controllers;

use App\Models\EscrowTransaction;
use App\Models\TransactionPhoto;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function upload(Request $request, $escrowId)
    {
        // Validate request
        $request->validate([
            'photo' => 'required|image|max:5120', // 5MB max
            'photo_type' => 'required|in:shipment_evidence,delivery_evidence,dispute_evidence',
            'description' => 'nullable|string|max:500',
            'dispute_id' => 'nullable|string|exists:disputes,dispute_id',
            'confirm_delivery' => 'nullable|string'
        ]);
        
        // Find the escrow transaction
        $escrow = EscrowTransaction::where('escrow_id', $escrowId)->firstOrFail();
        
        // Check permission based on photo_type
        if ($request->photo_type == 'shipment_evidence' && auth()->user()->user_id !== $escrow->seller_id) {
            return redirect()->back()->with('error', 'Only the seller can upload shipment evidence.');
        }
        
        if ($request->photo_type == 'delivery_evidence' && auth()->user()->user_id !== $escrow->buyer_id) {
            return redirect()->back()->with('error', 'Only the buyer can upload delivery evidence.');
        }
        
        // For dispute evidence, either party or bank staff can upload
        if ($request->photo_type == 'dispute_evidence' && 
            auth()->user()->user_id !== $escrow->buyer_id && 
            auth()->user()->user_id !== $escrow->seller_id && 
            auth()->user()->role !== 'bank_staff') {
            return redirect()->back()->with('error', 'You are not authorized to upload dispute evidence.');
        }
        
        // Handle file upload
        $file = $request->file('photo');
        $fileName = time() . '_' . $file->getClientOriginalName();
        
        // Store the file in a transaction-specific folder
        $filePath = $file->storeAs(
            'transaction_photos/' . $escrowId, 
            $fileName, 
            'public'
        );
        
        // Create photo record
        $photo = new TransactionPhoto();
        $photo->escrow_id = $escrowId;
        $photo->uploader_id = auth()->user()->user_id;
        $photo->photo_type = $request->photo_type;
        $photo->file_name = $fileName;
        $photo->file_path = $filePath;
        $photo->mime_type = $file->getMimeType();
        $photo->file_size = $file->getSize();
        $photo->description = $request->description;
        $photo->dispute_id = $request->dispute_id;
        $photo->save();
        
        // Check if we need to confirm delivery
        if ($request->has('confirm_delivery') && $request->confirm_delivery == '1' && 
            $escrow->status == 'In Transit' && auth()->user()->user_id == $escrow->buyer_id) {
            
            // Find the shipment
            $shipment = Shipment::where('escrow_id', $escrow->escrow_id)->firstOrFail();
            $shipment->status = 'Delivered';
            $shipment->save();
            
            // Update escrow status
            $escrow->status = 'Waiting for Buyer Approval';
            $escrow->save();
            
            return redirect()->route('escrow.show', $escrow->escrow_id)
                ->with('success', 'Photo evidence uploaded and delivery confirmed successfully.');
        }
        
        return redirect()->back()->with('success', 'Photo evidence uploaded successfully.');
    }
    
    public function delete($photoId)
    {
        $photo = TransactionPhoto::findOrFail($photoId);
        
        // Get the associated escrow transaction
        $escrow = EscrowTransaction::where('escrow_id', $photo->escrow_id)->first();
        
        // Check if the escrow exists and has moved beyond the "Funds Pending" stage
        if ($escrow && $escrow->status !== 'Funds Pending') {
            // If payment has been made, prevent deletion of original evidence photos
            return redirect()->back()->with('error', 'Photos cannot be deleted after payment has been made.');
        }
        
        // Check if user is authorized to delete the photo
        if (auth()->user()->user_id !== $photo->uploader_id && auth()->user()->role !== 'bank_staff') {
            return redirect()->back()->with('error', 'You are not authorized to delete this photo.');
        }
        
        // Delete the file from storage
        Storage::disk('public')->delete($photo->file_path);
        
        // Delete the record
        $photo->delete();
        
        return redirect()->back()->with('success', 'Photo deleted successfully.');
    }
}