@extends('layouts.app')

@section('title', 'Review Action - Azania Bank')

@section('header', 'Review Pending Action')

@section('header_buttons')
    <a href="{{ route('admin.maker-checker.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Pending Actions
    </a>
@endsection

@php
// Helper function to format currency
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
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Action Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Action ID:</div>
                        <div class="col-md-8">{{ $pendingAction->action_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Action Type:</div>
                        <div class="col-md-8">
                            @if($pendingAction->action_type == 'dispute_resolution')
                                <span class="badge bg-danger">Dispute Resolution</span>
                            @elseif($pendingAction->action_type == 'user_role_change')
                                <span class="badge bg-warning">Role Change</span>
                            @elseif($pendingAction->action_type == 'user_suspension')
                                <span class="badge bg-dark">User Suspension</span>
                            @else
                                <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($pendingAction->action_type)) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Description:</div>
                        <div class="col-md-8">{{ $pendingAction->description }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Created By:</div>
                        <div class="col-md-8">{{ $pendingAction->maker->name }} ({{ $pendingAction->maker->user_id }})</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Created At:</div>
                        <div class="col-md-8">{{ $pendingAction->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    
                    @if(auth()->user()->role === 'checker')
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> You are responsible for reviewing and either approving or rejecting this action.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Data -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Proposed Changes</h5>
                </div>
                <div class="card-body">
                    @if($pendingAction->action_type == 'dispute_resolution')
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Resolution Status:</div>
                            <div class="col-md-8">
                                @if($pendingAction->action_data['status'] == 'Resolved')
                                    <span class="badge bg-success">Resolved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Resolution Details:</div>
                            <div class="col-md-8">
                                <div class="p-3 bg-light rounded">
                                    {{ $pendingAction->action_data['resolution'] }}
                                </div>
                            </div>
                        </div>
                        @if(isset($pendingAction->action_data['refund_buyer']) && $pendingAction->action_data['refund_buyer'])
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Refund:</div>
                            <div class="col-md-8">
                                <span class="badge bg-info">Refund to Buyer</span>
                            </div>
                        </div>
                        @endif
                    @elseif($pendingAction->action_type == 'user_role_change')
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">User ID:</div>
                            <div class="col-md-8">{{ $entity->user_id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">User Name:</div>
                            <div class="col-md-8">{{ $entity->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Current Role:</div>
                            <div class="col-md-8">
                                <span class="badge bg-secondary">{{ ucfirst($entity->role) }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">New Role:</div>
                            <div class="col-md-8">
                                <span class="badge bg-primary">{{ ucfirst($pendingAction->action_data['new_role']) }}</span>
                            </div>
                        </div>
                    @elseif($pendingAction->action_type == 'user_suspension')
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">User ID:</div>
                            <div class="col-md-8">{{ $entity->user_id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">User Name:</div>
                            <div class="col-md-8">{{ $entity->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Current Status:</div>
                            <div class="col-md-8">
                                <span class="badge bg-{{ $entity->status == 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($entity->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">New Status:</div>
                            <div class="col-md-8">
                                <span class="badge bg-{{ $pendingAction->action_data['status'] == 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($pendingAction->action_data['status']) }}
                                </span>
                            </div>
                        </div>
                    @elseif($pendingAction->action_type == 'user_creation')
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Name:</div>
                            <div class="col-md-8">{{ $pendingAction->action_data['name'] }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Email:</div>
                            <div class="col-md-8">{{ $pendingAction->action_data['email'] }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Role:</div>
                            <div class="col-md-8">
                                <span class="badge bg-primary">{{ ucfirst($pendingAction->action_data['role']) }}</span>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            A new user account will be created with these details if you approve this action.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <!-- Entity Details -->
            @if($pendingAction->action_type == 'dispute_resolution' && $entity)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Dispute Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Dispute ID:</div>
                            <div class="col-md-8">{{ $entity->dispute_id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Escrow ID:</div>
                            <div class="col-md-8">
                                <a href="{{ route('escrow.show', $entity->escrow_id) }}">
                                    {{ $entity->escrow_id }}
                                </a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Buyer:</div>
                            <div class="col-md-8">{{ $entity->buyer->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Seller:</div>
                            <div class="col-md-8">{{ $entity->seller->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Current Status:</div>
                            <div class="col-md-8">
                                <span class="badge bg-warning">{{ $entity->status }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Reason:</div>
                            <div class="col-md-8">
                                <div class="p-3 bg-light rounded">
                                    {{ $entity->reason }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Evidence Photos Section -->
                @php
                    // Get the escrow transaction related to this dispute
                    $escrow = $entity->escrowTransaction;
                    // Get photos if available - These are critical for checkers to make informed decisions
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
                                    Seller Evidence
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="buyer-tab" data-bs-toggle="tab" data-bs-target="#buyer-photos" type="button" role="tab" aria-controls="buyer-photos" aria-selected="false">
                                    Buyer Evidence
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
                                            <div class="col-md-6 mb-4">
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
                                                            @if($photo->uploader_id == $entity->buyer_id)
                                                                <span class="text-primary">{{ $photo->uploader->name }} (Buyer)</span>
                                                            @elseif($photo->uploader_id == $entity->seller_id)
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
                                    $sellerPhotos = $photos->where('uploader_id', $entity->seller_id)
                                                         ->where('photo_type', 'shipment_evidence');
                                @endphp
                                
                                @if(count($sellerPhotos) > 0)
                                    <div class="row">
                                        @foreach($sellerPhotos as $photo)
                                            <div class="col-md-6 mb-4">
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
                                    $buyerPhotos = $photos->where('uploader_id', $entity->buyer_id)
                                                        ->where('photo_type', 'delivery_evidence');
                                @endphp
                                
                                @if(count($buyerPhotos) > 0)
                                    <div class="row">
                                        @foreach($buyerPhotos as $photo)
                                            <div class="col-md-6 mb-4">
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
                                            <div class="col-md-6 mb-4">
                                                <div class="card h-100">
                                                    <img src="{{ asset('storage/' . $photo->file_path) }}" class="card-img-top" alt="Dispute Photo">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Dispute Evidence</h6>
                                                        <p class="card-text small">{{ $photo->description }}</p>
                                                        <p class="card-text text-muted small">
                                                            Uploaded by: 
                                                            @if($photo->uploader_id == $entity->buyer_id)
                                                                <span class="text-primary">{{ $photo->uploader->name }} (Buyer)</span>
                                                            @elseif($photo->uploader_id == $entity->seller_id)
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
            @endif

            <!-- Approval/Rejection Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your Decision</h5>
                </div>
                <div class="card-body">
                    @if($canApproveOrReject)
                        <div class="d-flex justify-content-between mb-4">
                            <form action="{{ route('admin.maker-checker.approve', $pendingAction->action_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this action?')">
                                    <i class="fas fa-check me-2"></i> Approve
                                </button>
                            </form>
                            
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times me-2"></i> Reject
                            </button>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> 
                            @if(auth()->user()->role !== 'checker')
                                You cannot approve or reject this action because you are not a checker.
                            @else
                                You cannot approve or reject your own actions.
                            @endif
                        </div>
                    @endif
                    
                    <div class="alert alert-info">
                        <ul class="mb-0">
                            <li><strong>Approve:</strong> The proposed changes will be applied.</li>
                            <li><strong>Reject:</strong> The action will be rejected and no changes will be made.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.maker-checker.reject', $pendingAction->action_id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                            <div class="form-text">Please provide a detailed explanation for why you are rejecting this action.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection