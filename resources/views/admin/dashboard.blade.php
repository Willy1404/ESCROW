@extends('layouts.app')

@section('title', 'Admin Overview - Azania Bank')

@section('header', 'Admin Overview')

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

// Extend the stats with currency-grouped totals
$totalValuesByCurrency = App\Models\EscrowTransaction::select('currency', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total_amount'))
    ->groupBy('currency')
    ->get();

$releasedValuesByCurrency = App\Models\EscrowTransaction::where('status', 'Funds Released')
    ->select('currency', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total_amount'))
    ->groupBy('currency')
    ->get()
    ->keyBy('currency');
@endphp

@section('content')
    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Users</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-primary">{{ $stats['total_users'] }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-users text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <span class="me-2">{{ $stats['buyers'] }} Buyers</span>
                        <span>{{ $stats['sellers'] }} Sellers</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Transactions</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-success">{{ $stats['transactions'] }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-exchange-alt text-success fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <span class="me-2">{{ $stats['active_escrows'] }} Active</span>
                        <span>{{ $stats['completed_escrows'] }} Completed</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Disputes</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-warning">{{ $stats['disputes'] }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <span>{{ $stats['pending_disputes'] }} Pending</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Value</h6>
                    @if($totalValuesByCurrency->isEmpty())
                        <p class="mb-0 text-muted">No transactions</p>
                    @else
                        @foreach($totalValuesByCurrency as $currencyData)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-info fw-bold">{{ formatCurrency($currencyData->total_amount, $currencyData->currency) }}</span>
                                <small class="text-muted">
                                    {{ formatCurrency($releasedValuesByCurrency[$currencyData->currency]->total_amount ?? 0, $currencyData->currency) }} Released
                                </small>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <!-- Transaction Status -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Transactions by Status</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['Funds Pending', 'Funds Received', 'In Transit', 'Waiting for Buyer Approval', 'Funds Released', 'Escrow On Hold'] as $status)
                                    <tr>
                                        <td>
                                            @if($status == 'Funds Pending')
                                                <span class="badge bg-light text-dark border">Funds Pending</span>
                                            @elseif($status == 'Funds Received')
                                                <span class="badge bg-light text-dark border">Funds Received</span>
                                            @elseif($status == 'In Transit')
                                                <span class="badge bg-light text-dark border">In Transit</span>
                                            @elseif($status == 'Waiting for Buyer Approval')
                                                <span class="badge bg-light text-dark border">Waiting Approval</span>
                                            @elseif($status == 'Funds Released')
                                                <span class="badge bg-light text-dark border">Completed</span>
                                            @elseif($status == 'Escrow On Hold')
                                                <span class="badge bg-light text-dark border">On Hold</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ isset($status_distribution[$status]) ? $status_distribution[$status]->count : 0 }}
                                        </td>
                                        <td>
                                            @php
                                                // Get values by currency for this status
                                                $statusValues = [];
                                                if(isset($status_distribution[$status])) {
                                                    $statusValues = App\Models\EscrowTransaction::where('status', $status)
                                                        ->select('currency', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total_amount'))
                                                        ->groupBy('currency')
                                                        ->get();
                                                }
                                            @endphp
                                            
                                            @if(count($statusValues) > 0)
                                                @foreach($statusValues as $valueData)
                                                    <div>{{ formatCurrency($valueData->total_amount, $valueData->currency) }}</div>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('admin.users.create') }}" class="card bg-light text-center p-3 text-decoration-none border">
                                <div class="py-3">
                                    <i class="fas fa-user-plus fa-2x text-primary mb-2"></i>
                                    <h6 class="mb-0">Create User</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.disputes') }}" class="card bg-light text-center p-3 text-decoration-none border">
                                <div class="py-3">
                                    <i class="fas fa-gavel fa-2x text-danger mb-2"></i>
                                    <h6 class="mb-0">Manage Disputes</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.users') }}" class="card bg-light text-center p-3 text-decoration-none border">
                                <div class="py-3">
                                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                                    <h6 class="mb-0">Manage Users</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.reports') }}" class="card bg-light text-center p-3 text-decoration-none border">
                                <div class="py-3">
                                    <i class="fas fa-chart-bar fa-2x text-success mb-2"></i>
                                    <h6 class="mb-0">View Reports</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if(auth()->user()->role === 'bank_staff')
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pending Actions</h5>
                <a href="{{ route('admin.maker-checker.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @php
                    $pendingActions = App\Models\PendingAction::with(['maker'])
                        ->where('status', 'pending')
                        ->whereNot('maker_id', auth()->user()->user_id)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                
                @if($pendingActions->isEmpty())
                    <div class="alert alert-info m-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i> No pending actions require your approval.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Action Type</th>
                                    <th>Description</th>
                                    <th>Submitted By</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingActions as $action)
                                    <tr>
                                        <td>
                                            @if($action->action_type == 'dispute_resolution')
                                                <span class="badge bg-danger">Dispute Resolution</span>
                                            @elseif($action->action_type == 'user_role_change')
                                                <span class="badge bg-warning">Role Change</span>
                                            @elseif($action->action_type == 'user_suspension')
                                                <span class="badge bg-dark">User Suspension</span>
                                            @else
                                                <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($action->action_type)) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($action->description, 40) }}</td>
                                        <td>{{ $action->maker->name }}</td>
                                        <td>{{ $action->created_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('admin.maker-checker.show', $action->action_id) }}" class="btn btn-sm btn-primary">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Transactions</h5>
                    <a href="{{ route('admin.transactions') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recent_transactions->isEmpty())
                        <div class="alert alert-info m-3 mb-0">
                            No transactions found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Buyer</th>
                                        <th>Seller</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_transactions as $transaction)
                                        <tr>
                                            <td>
                                                <a href="{{ route('escrow.show', $transaction->escrow_id) }}">
                                                    {{ $transaction->escrow_id }}
                                                </a>
                                            </td>
                                            <td>{{ $transaction->buyer->name }}</td>
                                            <td>{{ $transaction->seller->name }}</td>
                                            <td>{{ formatCurrency($transaction->amount, $transaction->currency) }}</td>
                                            <td>
                                                @if($transaction->status == 'Funds Pending')
                                                    <span class="badge bg-light text-dark border">Funds Pending</span>
                                                @elseif($transaction->status == 'Funds Received')
                                                    <span class="badge bg-light text-dark border">Funds Received</span>
                                                @elseif($transaction->status == 'In Transit')
                                                    <span class="badge bg-light text-dark border">In Transit</span>
                                                @elseif($transaction->status == 'Waiting for Buyer Approval')
                                                    <span class="badge bg-light text-dark border">Waiting Approval</span>
                                                @elseif($transaction->status == 'Funds Released')
                                                    <span class="badge bg-light text-dark border">Completed</span>
                                                @elseif($transaction->status == 'Escrow On Hold')
                                                    <span class="badge bg-light text-dark border">On Hold</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Recent Disputes -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Disputes</h5>
                    <a href="{{ route('admin.disputes') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recent_disputes->isEmpty())
                        <div class="alert alert-info m-3 mb-0">
                            No disputes found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Buyer</th>
                                        <th>Seller</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_disputes as $dispute)
                                        <tr>
                                            <td>{{ $dispute->dispute_id }}</td>
                                            <td>{{ $dispute->buyer->name }}</td>
                                            <td>{{ $dispute->seller->name }}</td>
                                            <td>
                                                @if($dispute->status == 'Pending')
                                                    <span class="badge bg-light text-dark border">Pending</span>
                                                @elseif($dispute->status == 'Resolved')
                                                    <span class="badge bg-light text-dark border">Resolved</span>
                                                @elseif($dispute->status == 'Rejected')
                                                    <span class="badge bg-light text-dark border">Rejected</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.disputes.show', $dispute->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
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