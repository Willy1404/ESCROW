@extends('layouts.app')

@section('title', 'Resolve Dispute - Azania Bank')

@section('header', 'Resolve Dispute')

@section('header_buttons')
    <a href="{{ route('disputes.show', $dispute->dispute_id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dispute
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Dispute Resolution Form</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('disputes.update', $dispute->dispute_id) }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Dispute ID:</div>
                            <div class="col-md-8">{{ $dispute->dispute_id }}</div>
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
                            <div class="col-md-4 fw-bold">Buyer's Reason:</div>
                            <div class="col-md-8">
                                <div class="p-3 bg-light rounded">
                                    {{ $dispute->reason }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="resolution" class="form-label">Resolution Details</label>
                            <textarea class="form-control @error('resolution') is-invalid @enderror" id="resolution" name="resolution" rows="5" required></textarea>
                            <div class="form-text">Provide a detailed explanation of how you're resolving this dispute.</div>
                            @error('resolution')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Resolution Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="">Select resolution status</option>
                                <option value="Resolved">Resolved (Approve Dispute)</option>
                                <option value="Rejected">Rejected (Deny Dispute)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3" id="refundOptions" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="refund_buyer" name="refund_buyer" value="1">
                                <label class="form-check-label" for="refund_buyer">
                                    Refund funds to buyer
                                </label>
                                <div class="form-text">Check this if the funds should be returned to the buyer. Otherwise, funds will be released to the seller.</div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Resolution</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transaction Summary</h5>
                </div>
                <div class="card-body">
                    @php
                        $escrow = $dispute->escrowTransaction;
                    @endphp
                    <div class="mb-3">
                        <strong>Amount:</strong>
                        <div>{{ $escrow->currency }} {{ number_format($escrow->amount, 2) }}</div>
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
                        <strong>Arbitrator Guidelines:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Review all communication between parties</li>
                            <li>Consider any proof of delivery/quality</li>
                            <li>Make an impartial decision based on evidence</li>
                            <li>Provide clear reasoning in your resolution</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const refundOptions = document.getElementById('refundOptions');
            
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Resolved') {
                    refundOptions.style.display = 'block';
                } else {
                    refundOptions.style.display = 'none';
                }
            });
        });
    </script>
    @endsection
@endsection