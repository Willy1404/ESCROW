@extends('layouts.app')

@section('title', 'IT Support Dashboard - Azania Bank')

@section('header', 'IT Support Dashboard')

@section('content')
    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Bank Staff</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-primary">{{ $bank_staff_count }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-users-cog text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">IT Support</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-success">{{ $it_support_count }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-user-shield text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Buyers</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-info">{{ $buyer_count }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Sellers</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-warning">{{ $seller_count }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-store text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('it_support.staff.create') }}" class="card bg-light text-center p-3 text-decoration-none border">
                                <div class="py-3">
                                    <i class="fas fa-user-plus fa-2x text-primary mb-2"></i>
                                    <h6 class="mb-0">Create New Staff</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('it_support.staff.index') }}" class="card bg-light text-center p-3 text-decoration-none border">
                                <div class="py-3">
                                    <i class="fas fa-users-cog fa-2x text-success mb-2"></i>
                                    <h6 class="mb-0">Manage Staff</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('it_support.audit-log') }}" class="card bg-light text-center p-3 text-decoration-none border">
                                <div class="py-3">
                                    <i class="fas fa-clipboard-list fa-2x text-info mb-2"></i>
                                    <h6 class="mb-0">View Audit Log</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Staff</h5>
                    <a href="{{ route('it_support.staff.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recent_staff->isEmpty())
                        <div class="alert alert-info m-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i> No staff members found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_staff as $staff)
                                        <tr>
                                            <td>{{ $staff->name }}</td>
                                            <td>
                                                @if($staff->role == 'bank_staff')
                                                    <span class="badge bg-primary">Bank Staff</span>
                                                @elseif($staff->role == 'it_support')
                                                    <span class="badge bg-success">IT Support</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($staff->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Suspended</span>
                                                @endif
                                            </td>
                                            <td>{{ $staff->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection