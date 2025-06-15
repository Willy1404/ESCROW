@extends('layouts.guest_welcome')

@section('title', 'Welcome to Azania Bank Escrow')

@section('content')
<div class="hero-section">
    <div class="container">
        <h1>Azania Bank Escrow Service</h1>
        <p class="lead">A secure transaction platform for buyers and sellers</p>
        <div class="mt-4">
            <a href="{{ route('login') }}" class="btn btn-light btn-lg me-2">Sign In</a>
            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Create Account</a>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-container bg-primary text-white me-3">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="mb-0">Secure Transactions</h4>
                    </div>
                    <p class="text-muted">Our escrow service provides a secure platform for buyers and sellers to complete transactions with confidence. Funds are held safely until all conditions are met.</p>
                    <div class="text-end mt-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-container bg-success text-white me-3">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4 class="mb-0">Quick Checkout</h4>
                    </div>
                    <p class="text-muted">Have a control number? Complete your transaction quickly without creating an account. Simply enter your control number and proceed with payment.</p>
                    <div class="text-end mt-3">
                        <a href="{{ route('guest.verify') }}" class="btn btn-success">
                            Quick Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="feature-box text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-lock"></i>
                </div>
                <h5>Secure Payments</h5>
                <p class="text-muted small">Your funds are securely held until all transaction conditions are met.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-box text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h5>Buyer Protection</h5>
                <p class="text-muted small">Inspect your goods before the seller receives payment.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-box text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h5>Dispute Resolution</h5>
                <p class="text-muted small">Professional mediation for any transaction disputes.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .hero-section {
        background-color: #3399BB;
        background-image: linear-gradient(135deg, #3399BB 0%, #2980a5 100%);
        color: white;
        padding: 80px 0;
        border-radius: 0;
        margin-bottom: 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .hero-section h1 {
        font-weight: 600;
        font-size: 2.5rem;
        margin-bottom: 15px;
    }
    
    .hero-section .lead {
        font-size: 1.25rem;
        font-weight: 300;
        margin-bottom: 30px;
    }
    
    .icon-container {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .feature-box {
        padding: 20px;
        transition: all 0.3s ease;
    }
    
    .feature-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto;
        background-color: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #3399BB;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .card {
        border: none;
        border-radius: 8px;
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .border-success {
        border-left: 4px solid #28a745 !important;
    }
    
    .btn-outline-primary {
        border-color: #3399BB;
        color: #3399BB;
    }
    
    .btn-outline-primary:hover {
        background-color: #3399BB;
        color: white;
    }
</style>
@endsection