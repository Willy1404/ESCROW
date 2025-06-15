<?php

namespace App\Http\Controllers;

use App\Models\ControlNumber;
use App\Models\TransactionPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ControlNumberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:seller')->except(['verify', 'show']);
    }
    
    public function index()
    {
        $controlNumbers = ControlNumber::where('seller_id', auth()->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
                
        return view('control_numbers.index', compact('controlNumbers'));
    }
    
    public function create()
    {
        return view('control_numbers.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'item_name' => 'required|string|max:255',
            'item_condition' => 'nullable|string|max:255',
            'item_description' => 'nullable|string',
            'delivery_deadline' => 'required|date|after:today',
            'inspection_period' => 'required|integer|min:1|max:30',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|image|max:5120', // 5MB max per image
            'photo_description' => 'nullable|string|max:500',
        ]);
        
        // Generate a unique control number with fixed prefix
        $prefix = '9920409'; // Your 7-digit fixed prefix
        $randomPart = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT); // 5 random digits, zero-padded
        $controlNumber = $prefix . $randomPart;

        // Ensure uniqueness by checking if it already exists
        while (ControlNumber::where('control_number', $controlNumber)->exists()) {
            $randomPart = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $controlNumber = $prefix . $randomPart;
        }
        
        // Create the control number record
        $controlNumberRecord = ControlNumber::create([
            'control_number' => $controlNumber,
            'seller_id' => auth()->user()->user_id,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'item_name' => $request->item_name,
            'item_condition' => $request->item_condition,
            'item_description' => $request->item_description,
            'delivery_deadline' => $request->delivery_deadline,
            'inspection_period' => $request->inspection_period,
            'expires_at' => Carbon::now()->addDays(7), // Control number expires in 7 days
        ]);
        
       // Process photo uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $fileName = time() . '_' . $photo->getClientOriginalName();
                
                // Store the file in a control-number specific folder
                $filePath = $photo->storeAs(
                    'control_number_photos/' . $controlNumber, 
                    $fileName, 
                    'public'
                );
                
                // Create photo record linked to the control number
                TransactionPhoto::create([
                    'escrow_id' => null, // Set escrow_id to null - we need to add a migration to make this nullable
                    'control_number' => $controlNumber, 
                    'uploader_id' => auth()->user()->user_id,
                    'photo_type' => 'shipment_evidence', // Using existing type
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => $photo->getMimeType(),
                    'file_size' => $photo->getSize(),
                    'description' => $request->photo_description,
                ]);
            }
        }
        
        return redirect()->route('control-numbers.index')
            ->with('success', 'Control number generated successfully with item photos.');
    }
    
    public function show($controlNumber)
    {
        $controlNumber = ControlNumber::where('control_number', $controlNumber)->firstOrFail();
        
        return view('control_numbers.show', compact('controlNumber'));
    }
    
    public function verify(Request $request)
    {
        try {
            // Log the request for debugging
            \Log::info('Verify request received', ['data' => $request->all()]);
            
            $request->validate([
                'control_number' => 'required|string|max:20'
            ]);
            
            $controlNumber = ControlNumber::where('control_number', $request->control_number)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->first();
                
            \Log::info('Control number lookup result', [
                'found' => $controlNumber ? true : false,
                'control_number' => $request->control_number
            ]);
                
            if (!$controlNumber) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid or expired control number'
                ]);
            }
            
            // Make sure we have the seller relationship
            $seller = $controlNumber->seller;
            
            if (!$seller) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Seller information not found'
                ]);
            }
            
            // Get photos associated with this control number
            $photos = TransactionPhoto::where('control_number', $controlNumber->control_number)->get();
            $photoUrls = [];
            
            foreach ($photos as $photo) {
                $photoUrls[] = [
                    'url' => asset('storage/' . $photo->file_path),
                    'description' => $photo->description,
                    'uploaded_at' => $photo->created_at->format('M d, Y H:i')
                ];
            }
            
            return response()->json([
                'valid' => true,
                'data' => [
                    'seller' => $seller->name,
                    'amount' => $controlNumber->amount,
                    'currency' => $controlNumber->currency,
                    'item_name' => $controlNumber->item_name,
                    'item_condition' => $controlNumber->item_condition,
                    'item_description' => $controlNumber->item_description,
                    'delivery_deadline' => $controlNumber->delivery_deadline->format('Y-m-d'),
                    'inspection_period' => $controlNumber->inspection_period,
                    'photos' => $photoUrls
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in verify method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'valid' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}