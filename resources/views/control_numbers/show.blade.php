@extends('layouts.app')

@section('title', 'Control Number Details - Azania Bank')

@section('header', 'Control Number Details')

@section('header_buttons')
    <a href="{{ route('control-numbers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Control Numbers
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Control
                    <div class="card-header">
                    <h5 class="mb-0">Control Number Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Control Number:</div>
                        <div class="col-md-8">
                            <span class="fs-5">{{ $controlNumber->control_number }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-2 copy-btn" data-control-number="{{ $controlNumber->control_number }}">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Status:</div>
                        <div class="col-md-8">
                            @if($controlNumber->is_used)
                                <span class="badge bg-success">Used</span>
                            @elseif($controlNumber->expires_at < now())
                                <span class="badge bg-danger">Expired</span>
                            @else
                                <span class="badge bg-primary">Active</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Item:</div>
                        <div class="col-md-8">{{ $controlNumber->item_name }}</div>
                    </div>
                    
                    @if($controlNumber->item_condition)
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Condition:</div>
                        <div class="col-md-8">{{ $controlNumber->item_condition }}</div>
                    </div>
                    @endif
                    
                    @if($controlNumber->item_description)
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Description:</div>
                        <div class="col-md-8">{{ $controlNumber->item_description }}</div>
                    </div>
                    @endif
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Amount:</div>
                        <div class="col-md-8">{{ formatCurrency($controlNumber->amount, $controlNumber->currency) }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Delivery Deadline:</div>
                        <div class="col-md-8">{{ $controlNumber->delivery_deadline->format('M d, Y') }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Inspection Period:</div>
                        <div class="col-md-8">{{ $controlNumber->inspection_period }} days</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Created On:</div>
                        <div class="col-md-8">{{ $controlNumber->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Expires On:</div>
                        <div class="col-md-8">{{ $controlNumber->expires_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sharing Information</h5>
                </div>
                <div class="card-body">
                    @if(!$controlNumber->is_used && $controlNumber->expires_at > now())
                        <p>Share this control number with your buyer:</p>
                        
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" value="{{ $controlNumber->control_number }}" readonly>
                            <button class="btn btn-outline-primary copy-btn" type="button" data-control-number="{{ $controlNumber->control_number }}">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Instructions for your buyer:
                            <ol class="mb-0 mt-2">
                                <li>Log in to their Azania Bank Escrow account</li>
                                <li>Click on "Create Escrow" in the dashboard</li>
                                <li>Enter this control number when prompted</li>
                                <li>Verify the details and confirm the transaction</li>
                            </ol>
                        </div>
                    @elseif($controlNumber->is_used)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>This control number has been used.</strong>
                            <p class="mb-0 mt-2">The associated escrow transaction has been created.</p>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>This control number has expired.</strong>
                            <p class="mb-0 mt-2">You can generate a new control number for this transaction.</p>
                        </div>
                        
                        <a href="{{ route('control-numbers.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-plus"></i> Generate New Control Number
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.copy-btn');
        
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const controlNumber = this.getAttribute('data-control-number');
                navigator.clipboard.writeText(controlNumber)
                    .then(() => {
                        // Change button to show success
                        const originalHTML = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-success');
                        
                        // Revert back after 2 seconds
                        setTimeout(() => {
                            this.innerHTML = originalHTML;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-outline-primary');
                        }, 2000);
                    })
                    .catch(err => {
                        console.error('Failed to copy text: ', err);
                        alert('Failed to copy to clipboard');
                    });
            });
        });
    });
</script>
@endsection