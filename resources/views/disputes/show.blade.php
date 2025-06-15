@extends('layouts.app')

@section('title', 'Dispute Details - Azania Bank')

@section('header', 'Dispute Details')

@section('header_buttons')
    <a href="{{ route('admin.disputes') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Disputes
    </a>
@endsection

@php
// Scope the function to avoid conflicts
if (!function_exists('adminDisputeFormatCurrency')) {
    function adminDisputeFormatCurrency($amount, $currency) {
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
}
@endphp

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Dispute Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Dispute ID:</div>
                        <div class="col-md-8">{{ $dispute->dispute_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Related Escrow:</div>
                        <div class="col-md-8">
                            <a href="{{ route('escrow.show', $dispute->escrow_id) }}">
                                {{ $dispute->escrow_id }}
                            </a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Status:</div>
                        <div class="col-md-8">
                            @if($dispute->status == 'Pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($dispute->status == 'Resolved')
                                <span class="badge bg-success">Resolved</span>
                            @elseif($dispute->status == 'Rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Filed By:</div>
                        <div class="col-md-8">{{ $dispute->buyer->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Against:</div>
                        <div class="col-md-8">{{ $dispute->seller->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Filed On:</div>
                        <div class="col-md-8">{{ $dispute->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Reason:</div>
                        <div class="col-md-8">
                            <div class="p-3 bg-light rounded">
                                {{ $dispute->reason }}
                            </div>
                        </div>
                    </div>
                    
                    @if($dispute->status != 'Pending')
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Resolution:</div>
                            <div class="col-md-8">
                                <div class="p-3 bg-light rounded">
                                    {{ $dispute->resolution }}
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Resolved By:</div>
                            <div class="col-md-8">{{ $dispute->resolver ? $dispute->resolver->name : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Resolved On:</div>
                            <div class="col-md-8">{{ $dispute->updated_at->format('M d, Y H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Evidence Photos Section -->
            @php
                // Get the escrow transaction related to this dispute
                $escrow = $dispute->escrowTransaction;
                // Get photos if available
                $photos = $escrow->photos ?? collect();
            @endphp
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Evidence Photos</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="evidenceTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-photos" type="button" role="tab" aria-controls="all-photos" aria-selected="true">
                                All Photos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="seller-tab" data-bs-toggle="tab" data-bs-target="#seller-photos" type="button" role="tab" aria-controls="seller-photos" aria-selected="false">
                                Seller Shipment Evidence
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="buyer-tab" data-bs-toggle="tab" data-bs-target="#buyer-photos" type="button" role="tab" aria-controls="buyer-photos" aria-selected="false">
                                Buyer Delivery Evidence
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dispute-tab" data-bs-toggle="tab" data-bs-target="#dispute-photos" type="button" role="tab" aria-controls="dispute-photos" aria-selected="false">
                                Dispute Evidence
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="evidenceTabsContent">
                        <!-- All Photos Tab -->
                        <div class="tab-pane fade show active" id="all-photos" role="tabpanel" aria-labelledby="all-tab">
                            @if(count($photos) > 0)
                                <div class="row">
                                    @foreach($photos as $photo)
                                        <div class="col-md-4 col-sm-6 mb-4">
                                            <div class="card h-100">
                                                <img src="{{ asset('storage/' . $photo->file_path) }}" class="card-img-top" alt="Transaction Photo">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        @if($photo->photo_type == 'shipment_evidence')
                                                            <span class="badge bg-info">Shipment Evidence</span>
                                                        @elseif($photo->photo_type == 'delivery_evidence')
                                                            <span class="badge bg-primary">Delivery Evidence</span>
                                                        @elseif($photo->photo_type == 'dispute_evidence')
                                                            <span class="badge bg-warning">Dispute Evidence</span>
                                                        @endif
                                                    </h6>
                                                    <p class="card-text small">{{ $photo->description }}</p>
                                                    <p class="card-text text-muted small">
                                                        Uploaded by: 
                                                        @if($photo->uploader_id == $dispute->buyer_id)
                                                            <span class="text-primary">{{ $photo->uploader->name }} (Buyer)</span>
                                                        @elseif($photo->uploader_id == $dispute->seller_id)
                                                            <span class="text-success">{{ $photo->uploader->name }} (Seller)</span>
                                                        @else
                                                            {{ $photo->uploader->name }}
                                                        @endif
                                                        <br>
                                                        {{ $photo->created_at->format('M d, Y H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No photos have been uploaded for this transaction.
                                </div>
                            @endif
                        </div>
                        
                        <!-- Seller Photos Tab -->
                        <div class="tab-pane fade" id="seller-photos" role="tabpanel" aria-labelledby="seller-tab">
                            @php
                                $sellerPhotos = $photos->where('uploader_id', $dispute->seller_id)
                                                     ->where('photo_type', 'shipment_evidence');
                            @endphp
                            
                            @if(count($sellerPhotos) > 0)
                                <div class="row">
                                    @foreach($sellerPhotos as $photo)
                                        <div class="col-md-4 col-sm-6 mb-4">
                                            <div class="card h-100">
                                                <img src="{{ asset('storage/' . $photo->file_path) }}" class="card-img-top" alt="Shipment Photo">
                                                <div class="card-body">
                                                    <h6 class="card-title">Shipment Evidence</h6>
                                                    <p class="card-text small">{{ $photo->description }}</p>
                                                    <p class="card-text text-muted small">
                                                        Uploaded by: <span class="text-success">{{ $photo->uploader->name }} (Seller)</span><br>
                                                        {{ $photo->created_at->format('M d, Y H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Seller has not uploaded any shipment evidence photos.
                                </div>
                            @endif
                        </div>
                        
                        <!-- Buyer Photos Tab -->
                        <div class="tab-pane fade" id="buyer-photos" role="tabpanel" aria-labelledby="buyer-tab">
                            @php
                                $buyerPhotos = $photos->where('uploader_id', $dispute->buyer_id)
                                                    ->where('photo_type', 'delivery_evidence');
                            @endphp
                            
                            @if(count($buyerPhotos) > 0)
                                <div class="row">
                                    @foreach($buyerPhotos as $photo)
                                        <div class="col-md-4 col-sm-6 mb-4">
                                            <div class="card h-100">
                                                <img src="{{ asset('storage/' . $photo->file_path) }}" class="card-img-top" alt="Delivery Photo">
                                                <div class="card-body">
                                                    <h6 class="card-title">Delivery Evidence</h6>
                                                    <p class="card-text small">{{ $photo->description }}</p>
                                                    <p class="card-text text-muted small">
                                                        Uploaded by: <span class="text-primary">{{ $photo->uploader->name }} (Buyer)</span><br>
                                                        {{ $photo->created_at->format('M d, Y H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Buyer has not uploaded any delivery evidence photos.
                                </div>
                            @endif
                        </div>
                        
                        <!-- Dispute Photos Tab -->
                        <div class="tab-pane fade" id="dispute-photos" role="tabpanel" aria-labelledby="dispute-tab">
                            @php
                                $disputePhotos = $photos->where('photo_type', 'dispute_evidence');
                            @endphp
                            
                            @if(count($disputePhotos) > 0)
                                <div class="row">
                                    @foreach($disputePhotos as $photo)
                                        <div class="col-md-4 col-sm-6 mb-4">
                                            <div class="card h-100">
                                                <img src="{{ asset('storage/' . $photo->file_path) }}" class="card-img-top" alt="Dispute Photo">
                                                <div class="card-body">
                                                    <h6 class="card-title">Dispute Evidence</h6>
                                                    <p class="card-text small">{{ $photo->description }}</p>
                                                    <p class="card-text text-muted small">
                                                        Uploaded by: 
                                                        @if($photo->uploader_id == $dispute->buyer_id)
                                                            <span class="text-primary">{{ $photo->uploader->name }} (Buyer)</span>
                                                        @elseif($photo->uploader_id == $dispute->seller_id)
                                                            <span class="text-success">{{ $photo->uploader->name }} (Seller)</span>
                                                        @else
                                                            {{ $photo->uploader->name }}
                                                        @endif
                                                        <br>
                                                        {{ $photo->created_at->format('M d, Y H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> No dispute evidence photos have been uploaded.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Transaction Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Amount:</strong>
                        <div>{{ adminDisputeFormatCurrency($escrow->amount, $escrow->currency) }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Current Status:</strong>
                        <div>
                            @if($escrow->status == 'Funds Pending')
                                <span class="badge bg-light text-dark border">Funds Pending</span>
                            @elseif($escrow->status == 'Funds Received')
                                <span class="badge bg-light text-dark border">Funds Received</span>
                            @elseif($escrow->status == 'In Transit')
                                <span class="badge bg-light text-dark border">In Transit</span>
                            @elseif($escrow->status == 'Waiting for Buyer Approval')
                                <span class="badge bg-light text-dark border">Waiting Approval</span>
                            @elseif($escrow->status == 'Funds Released')
                                <span class="badge bg-light text-dark border">Completed</span>
                            @elseif($escrow->status == 'Escrow On Hold')
                                <span class="badge bg-light text-dark border">On Hold</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Created On:</strong>
                        <div>{{ $escrow->created_at->format('M d, Y') }}</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Admin Actions:</strong>
                        <ul class="mb-0 mt-2">
                            @if($dispute->status == 'Pending')
                                <li>Review evidence photos from both parties</li>
                                <li>Compare the shipment and delivery photos</li>
                                <li>Make an impartial decision based on evidence</li>
                                <li>Provide clear reasoning in your resolution</li>
                            @else
                                <li>This dispute has been {{ strtolower($dispute->status) }}</li>
                                <li>No further action is required</li>
                            @endif
                        </ul>
                    </div>
                    
                    <!-- @if($dispute->status == 'Pending')
                        <div class="d-grid gap-2">
                            <a href="{{ route('disputes.resolve', $dispute->dispute_id) }}" class="btn btn-warning">
                                <i class="fas fa-gavel me-2"></i> Resolve Dispute
                            </a>
                        </div>
                    @endif -->
                </div>
            </div>
            
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
                        @if(isset($escrow->payments) && $escrow->payments->isNotEmpty())
                            @foreach($escrow->payments as $payment)
                                <li class="list-group-item">
                                    <div class="d-flex">
                                        <div class="bg-success rounded-circle text-white d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Funds Deposited</div>
                                            <div>{{ adminDisputeFormatCurrency($payment->amount, $escrow->currency) }} via {{ $payment->payment_method }}</div>
                                            <div class="text-muted small">{{ $payment->created_at->format('M d, Y H:i') }}</div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endif
                        @if(isset($escrow->shipments) && $escrow->shipments->isNotEmpty())
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
                        @endif
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
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection


