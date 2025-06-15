@extends('layouts.app')

@section('title', 'Payment - Azania Bank')

@section('header', 'Payment Details')

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

@section('styles')
<style>
    /* Lightbox CSS */
    .image-lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }

    .lightbox-overlay {
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
    }

    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        margin: 2% auto;
        text-align: center;
        z-index: 10000;
    }

    #lightbox-image {
        max-width: 100%;
        max-height: 90vh;
        margin: 0 auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }

    .lightbox-close {
        position: absolute;
        top: -20px;
        right: -20px;
        background: white;
        color: black;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 0 5px rgba(0,0,0,0.5);
    }

    .create-account-section {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('guest.payment.post', $transaction->transaction_token) }}" method="POST" id="paymentForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="buyer_name" class="form-label">Your Name</label>
                        <input type="text" class="form-control @error('buyer_name') is-invalid @enderror" 
                               id="buyer_name" name="buyer_name" value="{{ old('buyer_name') }}" required>
                        @error('buyer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="buyer_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('buyer_email') is-invalid @enderror" 
                               id="buyer_email" name="buyer_email" value="{{ old('buyer_email') }}" required>
                        <div class="form-text">
                            We'll send your receipt and transaction details to this email.
                        </div>
                        @error('buyer_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" 
                                id="payment_method" name="payment_method" required>
                            <option value="">Select payment method</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Mobile Money">Mobile Money</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-primary">
                        <div class="d-flex align-items-center">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                       id="create_account_toggle" name="create_account" value="1">
                                <label class="form-check-label" for="create_account_toggle">
                                    <strong>Create an account for full transaction protection</strong>
                                </label>
                            </div>
                        </div>
                        <div class="small mt-2">
                            Creating an account lets you track your order, confirm delivery, 
                            and request assistance if needed.
                        </div>
                    </div>
                    
                    <div id="create_account_section" class="create-account-section mb-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Create Account</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="terms_agree" required>
                        <label class="form-check-label" for="terms_agree">
                            I agree to the terms and conditions of the escrow service.
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            Pay {{ formatCurrency($transaction->amount, $transaction->currency) }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Transaction Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="fw-bold">Control Number:</div>
                    <div>{{ $transaction->control_number }}</div>
                </div>
                
                <div class="mb-3">
                    <div class="fw-bold">Seller:</div>
                    <div>{{ $transaction->controlNumber->seller->name }}</div>
                </div>
                
                <div class="mb-3">
                    <div class="fw-bold">Item:</div>
                    <div>{{ $transaction->item_name }}</div>
                </div>
                
                @if($transaction->controlNumber->item_condition)
                <div class="mb-3">
                    <div class="fw-bold">Condition:</div>
                    <div>{{ $transaction->controlNumber->item_condition }}</div>
                </div>
                @endif
                
                @if($transaction->controlNumber->item_description)
                <div class="mb-3">
                    <div class="fw-bold">Description:</div>
                    <div>{{ $transaction->controlNumber->item_description }}</div>
                </div>
                @endif
                
                <div class="mb-3">
                    <div class="fw-bold">Amount:</div>
                    <div class="fs-5 text-success">{{ formatCurrency($transaction->amount, $transaction->currency) }}</div>
                </div>
                
                <div class="mb-3">
                    <div class="fw-bold">Delivery Deadline:</div>
                    <div>{{ $transaction->controlNumber->delivery_deadline->format('M d, Y') }}</div>
                </div>
                
                <div class="mb-3">
                    <div class="fw-bold">Inspection Period:</div>
                    <div>{{ $transaction->controlNumber->inspection_period }} days</div>
                </div>
            </div>
        </div>
        
        @if($photos->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Item Photos</h5>
            </div>
            <div class="card-body">
                <div class="row" id="itemPhotosGallery">
                    @foreach($photos as $photo)
                    <div class="col-md-6 col-6 mb-3">
                        <div class="card h-100">
                            <img src="{{ asset('storage/' . $photo->file_path) }}" class="card-img-top" 
                                 alt="Item Photo" style="height: 100px; object-fit: cover; cursor: pointer;">
                            <div class="card-body p-2">
                                <p class="card-text small text-muted">
                                    {{ $photo->description ?: 'No description provided' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup account creation toggle
    const accountToggle = document.getElementById('create_account_toggle');
    const accountSection = document.getElementById('create_account_section');
    const passwordField = document.getElementById('password');
    const passwordConfirmField = document.getElementById('password_confirmation');
    
    accountToggle.addEventListener('change', function() {
        if (this.checked) {
            accountSection.style.display = 'block';
            passwordField.required = true;
            passwordConfirmField.required = true;
        } else {
            accountSection.style.display = 'none';
            passwordField.required = false;
            passwordConfirmField.required = false;
        }
    });
    
    // Setup for image lightbox
    const setupImageLightbox = () => {
        const photoGallery = document.getElementById('itemPhotosGallery');
        if (photoGallery) {
            const images = photoGallery.querySelectorAll('img');
            
            images.forEach(img => {
                img.style.cursor = 'pointer';
                img.addEventListener('click', function() {
                    showLightbox(this.src);
                });
            });
        }
    };
    
    // Function to show lightbox
    const showLightbox = (imageSrc) => {
        // Create lightbox elements if they don't exist
        let lightbox = document.getElementById('image-lightbox');
        
        if (!lightbox) {
            lightbox = document.createElement('div');
            lightbox.id = 'image-lightbox';
            lightbox.className = 'image-lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-overlay"></div>
                <div class="lightbox-content">
                    <img src="" alt="Enlarged view" id="lightbox-image">
                    <button class="lightbox-close">&times;</button>
                </div>
            `;
            document.body.appendChild(lightbox);
            
            // Add event listener to close button
            lightbox.querySelector('.lightbox-close').addEventListener('click', () => {
                lightbox.style.display = 'none';
            });
            
            // Add event listener to overlay for closing
            lightbox.querySelector('.lightbox-overlay').addEventListener('click', () => {
                lightbox.style.display = 'none';
            });
        }
        
        // Set the image source and display the lightbox
        document.getElementById('lightbox-image').src = imageSrc;
        lightbox.style.display = 'block';
    };
    
    // Initialize lightbox
    setupImageLightbox();
});
</script>
@endsection