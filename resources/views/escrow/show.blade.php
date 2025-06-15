@extends('layouts.app')

@section('title', 'Escrow Details - Azania Bank')

@section('header', 'Escrow Transaction Details')

@section('header_buttons')
    <a href="{{ route('escrow.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Transactions
    </a>
@endsection

@php
// Simple function to format currency amounts based on currency code
function formatCurrency($amount, $currency) {
    switch ($currency) {
        case 'TZS':
            return 'TZS ' . number_format($amount, 0); // No decimal for TZS
        case 'USD':
            return '$' . number_format($amount, 2);
        case 'EUR':
            return '€' . number_format($amount, 2);
        case 'GBP':
            return '£' . number_format($amount, 2);
        default:
            return $currency . ' ' . number_format($amount, 2);
    }
}
@endphp

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Transaction Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Escrow ID:</div>
                        <div class="col-md-8">{{ $escrow->escrow_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Amount:</div>
                        <div class="col-md-8">{{ formatCurrency($escrow->amount, $escrow->currency) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Status:</div>
                        <div class="col-md-8">
                            @if($escrow->status == 'Funds Pending')
                                <span class="badge bg-warning text-dark">Funds Pending</span>
                            @elseif($escrow->status == 'Funds Received')
                                <span class="badge bg-info">Funds Received</span>
                            @elseif($escrow->status == 'In Transit')
                                <span class="badge bg-primary">In Transit</span>
                            @elseif($escrow->status == 'Waiting for Buyer Approval')
                                <span class="badge bg-secondary">Waiting Approval</span>
                            @elseif($escrow->status == 'Funds Released')
                                <span class="badge bg-success">Completed</span>
                            @elseif($escrow->status == 'Escrow On Hold')
                                <span class="badge bg-danger">On Hold</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Buyer:</div>
                        <div class="col-md-8">{{ $escrow->buyer->name }} ({{ $escrow->buyer->email }})</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Seller:</div>
                        <div class="col-md-8">{{ $escrow->seller->name }} ({{ $escrow->seller->email }})</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Delivery Deadline:</div>
                        <div class="col-md-8">{{ $escrow->delivery_deadline->format('M d, Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Inspection Period:</div>
                        <div class="col-md-8">{{ $escrow->inspection_period }} days</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Created At:</div>
                        <div class="col-md-8">{{ $escrow->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    @if($escrow->contract_terms)
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Contract Terms:</div>
                            <div class="col-md-8">
                                <div class="bg-light p-3 rounded">
                                    @foreach(json_decode($escrow->contract_terms) as $key => $value)
                                        <div class="mb-2">
                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            <span>{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($escrow->shipments->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Shipment Details</h5>
                    </div>
                    <div class="card-body">
                        @foreach($escrow->shipments as $shipment)
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Tracking ID:</div>
                                <div class="col-md-8">{{ $shipment->tracking_id }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Carrier:</div>
                                <div class="col-md-8">{{ $shipment->carrier }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Estimated Arrival:</div>
                                <div class="col-md-8">{{ $shipment->estimated_arrival->format('M d, Y') }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Status:</div>
                                <div class="col-md-8">
                                    @if($shipment->status == 'Pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($shipment->status == 'In Transit')
                                        <span class="badge bg-primary">In Transit</span>
                                    @elseif($shipment->status == 'Delivered')
                                        <span class="badge bg-success">Delivered</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Photo Gallery Section -->
            @if(isset($escrow->photos) && $escrow->photos->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Transaction Photos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($escrow->photos as $photo)
                                <div class="col-md-4 col-sm-6 mb-4">
                                    <div class="card h-100">
                                        <img src="{{ asset('storage/' . $photo->file_path) }}" class="card-img-top" alt="Transaction Photo">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                @if($photo->photo_type == 'shipment_evidence')
                                                    Shipment Evidence
                                                @elseif($photo->photo_type == 'delivery_evidence')
                                                    Delivery Evidence
                                                @elseif($photo->photo_type == 'dispute_evidence')
                                                    Dispute Evidence
                                                @endif
                                            </h6>
                                            <p class="card-text small">{{ $photo->description }}</p>
                                            <p class="card-text text-muted small">
                                                Uploaded by: {{ $photo->uploader->name }}<br>
                                                {{ $photo->created_at->format('M d, Y H:i') }}
                                            </p>
                                            @if(auth()->user()->user_id == $photo->uploader_id && $escrow->status == 'Funds Pending')
                                                <form action="{{ route('photos.delete', $photo->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this photo?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="col-md-4">
            <!-- Action Panel -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    @if($escrow->status == 'Funds Pending' && auth()->user()->user_id == $escrow->buyer_id)
                        <form action="{{ route('payments.deposit', $escrow->escrow_id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select payment method</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Mobile Money">Mobile Money</option>
                                </select>
                            </div>
                            <div class="alert alert-info">
                                <strong>Amount to deposit:</strong> 
                                {{ formatCurrency($escrow->amount, $escrow->currency) }}
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Deposit Funds</button>
                        </form>
                    @elseif($escrow->status == 'Funds Received' && auth()->user()->user_id == $escrow->seller_id)
                        <!-- Simplified view - just show shipment update form directly -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Update Shipping Information</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('shipments.update', $escrow->escrow_id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-3">
                                        <label for="tracking_id" class="form-label">Tracking ID</label>
                                        <input type="text" class="form-control" id="tracking_id" value="{{ $escrow->escrow_id }}" disabled>
                                        <div class="form-text">The escrow ID will be used as the tracking ID.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="carrier" class="form-label">Carrier</label>
                                        <select class="form-select" id="carrier" name="carrier" required>
                                            <option value="">Select carrier</option>
                                            <option value="Bus">Bus</option>
                                            <option value="Motorcycle">Motorcycle (BodaBoda)</option>
                                            <option value="Ship">Ship</option>
                                            <option value="Plane">Plane</option>
                                            <option value="Other">Other (Specify)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3" id="otherCarrierDiv" style="display: none;">
                                        <label for="carrier_other" class="form-label">Specify Other Carrier</label>
                                        <input type="text" class="form-control" id="carrier_other" name="carrier_other">
                                    </div>
                                    <div class="mb-3">
                                        <label for="estimated_arrival" class="form-label">Estimated Arrival</label>
                                        <input type="date" class="form-control" id="estimated_arrival" name="estimated_arrival" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Update Shipment</button>
                                </form>
                                
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const carrierSelect = document.getElementById('carrier');
                                        const otherCarrierDiv = document.getElementById('otherCarrierDiv');
                                        const carrierOtherInput = document.getElementById('carrier_other');
                                        
                                        if (carrierSelect) {
                                            carrierSelect.addEventListener('change', function() {
                                                if (this.value === 'Other') {
                                                    otherCarrierDiv.style.display = 'block';
                                                    carrierOtherInput.required = true;
                                                } else {
                                                    otherCarrierDiv.style.display = 'none';
                                                    carrierOtherInput.required = false;
                                                }
                                            });
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                    @elseif($escrow->status == 'Waiting for Buyer Approval' && auth()->user()->user_id == $escrow->buyer_id)
                        <div class="d-grid gap-2">
                            <form action="{{ route('payments.release', $escrow->escrow_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 mb-2">Approve & Release Funds</button>
                            </form>
                            <a href="{{ route('disputes.create', $escrow->escrow_id) }}" class="btn btn-danger w-100">Dispute Transaction</a>
                        </div>
                    @elseif($escrow->status == 'Escrow On Hold' && in_array(auth()->user()->role, ['maker', 'checker', 'bank_staff']))
                        <a href="{{ route('disputes.resolve', $escrow->disputes->first()->dispute_id) }}" class="btn btn-warning w-100">Resolve Dispute</a>
                    @elseif($escrow->status == 'Funds Released')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> Transaction completed successfully.
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Waiting for the next action from involved parties.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Photo Upload Section for Buyer -->
            @if($escrow->status == 'In Transit' && auth()->user()->user_id == $escrow->buyer_id)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Upload Delivery Photos & Confirm</h5>
                    </div>
                    <div class="card-body">
                        <p>Upload photos of the received item to document its condition and confirm delivery.</p>
                        <form action="{{ route('photos.upload', $escrow->escrow_id) }}" method="POST" enctype="multipart/form-data" id="photoUploadForm">
                            @csrf
                            <input type="hidden" name="photo_type" value="delivery_evidence">
                            <input type="hidden" name="confirm_delivery" value="1">
                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo</label>
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*" capture required>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Upload & Confirm Delivery</button>
                        </form>
                        
                        <!-- Optional: Keep a separate upload button for users who want to add multiple photos -->
                        <!-- <div class="text-center mt-2">
                            <button type="button" class="btn btn-link btn-sm" id="uploadOnlyBtn">
                                Upload without confirming delivery
                            </button>
                        </div> -->
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const form = document.getElementById('photoUploadForm');
                                const uploadOnlyBtn = document.getElementById('uploadOnlyBtn');
                                
                                if (uploadOnlyBtn) {
                                    uploadOnlyBtn.addEventListener('click', function() {
                                        const confirmInput = form.querySelector('input[name="confirm_delivery"]');
                                        confirmInput.value = "0";
                                        form.submit();
                                    });
                                }
                            });
                        </script>
                    </div>
                </div>
            @endif
            
            <!-- Transaction Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transaction Timeline</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="d-flex">
                                <div class="bg-primary rounded-circle text-white d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Transaction Created</div>
                                    <div class="text-muted small">{{ $escrow->created_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                        </li>
                        @foreach($escrow->payments as $payment)
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <div class="bg-success rounded-circle text-white d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Funds Deposited</div>
                                        <div>{{ formatCurrency($payment->amount, $escrow->currency) }} via {{ $payment->payment_method }}</div>
                                        <div class="text-muted small">{{ $payment->created_at->format('M d, Y H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        @foreach($escrow->shipments as $shipment)
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <div class="bg-info rounded-circle text-white d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Shipment Updated</div>
                                        <div>Tracking: {{ $shipment->tracking_id }} via {{ $shipment->carrier }}</div>
                                        <div class="text-muted small">{{ $shipment->created_at->format('M d, Y H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                            @if($shipment->status == 'Delivered')
                                <li class="list-group-item">
                                    <div class="d-flex">
                                        <div class="bg-success rounded-circle text-white d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Delivery Confirmed</div>
                                            <div class="text-muted small">{{ $shipment->updated_at->format('M d, Y H:i') }}</div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                        @foreach($escrow->disputes as $dispute)
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <div class="bg-danger rounded-circle text-white d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Dispute Filed</div>
                                        <div>{{ Str::limit($dispute->reason, 50) }}</div>
                                        <div class="text-muted small">{{ $dispute->created_at->format('M d, Y H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                            @if($dispute->status != 'Pending')
                                <li class="list-group-item">
                                    <div class="d-flex">
                                        <div class="bg-{{ $dispute->status == 'Resolved' ? 'success' : 'secondary' }} rounded-circle text-white d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                            <i class="fas fa-gavel"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Dispute {{ $dispute->status }}</div>
                                            <div>{{ Str::limit($dispute->resolution, 50) }}</div>
                                            <div class="text-muted small">{{ $dispute->updated_at->format('M d, Y H:i') }}</div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                        @if($escrow->status == 'Funds Released')
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <div class="bg-success rounded-circle text-white d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Transaction Completed</div>
                                        <div>Funds released to {{ $escrow->seller->name }}</div>
                                        <div class="text-muted small">{{ $escrow->updated_at->format('M d, Y H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection