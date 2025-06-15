@extends('layouts.app')

@section('title', 'Verify Control Number - Azania Bank')

@section('header', 'Quick Checkout')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Enter Control Number</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('guest.verify.post') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="control_number" class="form-label">Control Number</label>
                        <input type="text" class="form-control @error('control_number') is-invalid @enderror" 
                               id="control_number" name="control_number" required>
                        <div class="form-text">Enter the control number provided by the seller.</div>
                        @error('control_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary verify-continue-button">Verify & Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Checkout Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6>How it works:</h6>
                    <ol class="mb-0">
                        <li>Enter the control number from the seller</li>
                        <li>Verify the transaction details</li>
                        <li>Make your payment</li>
                        <li>Optionally create an account to track your purchase</li>
                    </ol>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Tip:</strong> Creating an account lets you:
                    <ul class="mb-0 mt-2">
                        <li>Track your transaction</li>
                        <li>Confirm delivery</li>
                        <li>Open disputes if needed</li>
                        <li>Access full escrow protection</li>
                    </ul>
                </div>
                
                <div class="mt-3">
                    <p>Already have an account?</p>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                        Log In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection