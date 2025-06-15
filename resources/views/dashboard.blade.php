@extends('layouts.app')

@section('title', 'Dashboard - Azania Bank Escrow')

@section('header', 'Dashboard')

@section('header_buttons')
    @if(auth()->user()->role == 'buyer')
    <a href="{{ route('escrow.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Escrow
    </a>
    @endif
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
    <!-- Welcome Card -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Welcome to Azania Bank Escrow System</h5>
            <p class="card-text">You are logged in as a {{ ucfirst(auth()->user()->role) }}. You can use this platform to securely handle transactions through our escrow service.</p>
        </div>
    </div>
    
    <!-- Stats Overview -->
    @php
        // Calculate statistics based on user role
        if(auth()->user()->role == 'buyer') {
            $activeEscrows = App\Models\EscrowTransaction::where('buyer_id', auth()->user()->user_id)
                ->whereNotIn('status', ['Funds Released'])
                ->count();
                
            $completedEscrows = App\Models\EscrowTransaction::where('buyer_id', auth()->user()->user_id)
                ->where('status', 'Funds Released')
                ->count();
                
            $inProgressEscrows = App\Models\EscrowTransaction::where('buyer_id', auth()->user()->user_id)
                ->whereIn('status', ['In Transit', 'Funds Received'])
                ->count();
                
            // Get totals grouped by currency
            $totalsByCurrency = App\Models\EscrowTransaction::where('buyer_id', auth()->user()->user_id)
                ->select('currency', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total_amount'))
                ->groupBy('currency')
                ->get()
                ->keyBy('currency');
        } 
        elseif(auth()->user()->role == 'seller') {
            $activeEscrows = App\Models\EscrowTransaction::where('seller_id', auth()->user()->user_id)
                ->whereNotIn('status', ['Funds Released'])
                ->count();
                
            $completedEscrows = App\Models\EscrowTransaction::where('seller_id', auth()->user()->user_id)
                ->where('status', 'Funds Released')
                ->count();
                
            $inProgressEscrows = App\Models\EscrowTransaction::where('seller_id', auth()->user()->user_id)
                ->whereIn('status', ['In Transit', 'Funds Received'])
                ->count();
                
            // Get totals grouped by currency
            $totalsByCurrency = App\Models\EscrowTransaction::where('seller_id', auth()->user()->user_id)
                ->select('currency', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total_amount'))
                ->groupBy('currency')
                ->get()
                ->keyBy('currency');
        }
        else {
            // Bank staff sees all transactions
            $activeEscrows = App\Models\EscrowTransaction::whereNotIn('status', ['Funds Released'])->count();
            $completedEscrows = App\Models\EscrowTransaction::where('status', 'Funds Released')->count();
            $inProgressEscrows = App\Models\EscrowTransaction::whereIn('status', ['In Transit', 'Funds Received'])->count();
            
            // Get totals grouped by currency for all transactions
            $totalsByCurrency = App\Models\EscrowTransaction::select('currency', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total_amount'))
                ->groupBy('currency')
                ->get()
                ->keyBy('currency');
        }
    @endphp
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Active Escrows</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-primary">{{ $activeEscrows }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-lock text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Completed</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-success">{{ $completedEscrows }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">In Progress</h6>
                    <div class="d-flex justify-content-between">
                        <h2 class="mb-0 text-warning">{{ $inProgressEscrows }}</h2>
                        <div class="align-self-center">
                            <i class="fas fa-spinner text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Value</h6>
                    <div class="pt-2">
                        @if($totalsByCurrency->isEmpty())
                            <p class="mb-0">No transactions</p>
                        @else
                            @foreach($totalsByCurrency as $currency => $data)
                                <div class="mb-1">
                                    <span class="fs-5 fw-bold text-info">{{ formatCurrency($data->total_amount, $currency) }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if(auth()->user()->role === 'checker')
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pending Actions for Approval</h5>
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

    @if(auth()->user()->role === 'maker')
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Pending Actions</h5>
                <a href="{{ route('admin.maker-checker.my-actions') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @php
                    $myPendingActions = App\Models\PendingAction::where('maker_id', auth()->user()->user_id)
                        ->where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                
                @if($myPendingActions->isEmpty())
                    <div class="alert alert-info m-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i> You don't have any pending actions awaiting approval.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Action Type</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myPendingActions as $action)
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
                                        <td>{{ $action->created_at->diffForHumans() }}</td>
                                        <td>
                                            <span class="badge bg-warning">Awaiting Approval</span>
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
    
    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Escrow Transactions</h5>
        </div>
        <div class="card-body">
            @php
                // Get recent transactions
                if(auth()->user()->role == 'buyer') {
                    $transactions = App\Models\EscrowTransaction::with(['buyer', 'seller'])
                        ->where('buyer_id', auth()->user()->user_id)
                        ->latest()
                        ->take(5)
                        ->get();
                } 
                elseif(auth()->user()->role == 'seller') {
                    $transactions = App\Models\EscrowTransaction::with(['buyer', 'seller'])
                        ->where('seller_id', auth()->user()->user_id)
                        ->latest()
                        ->take(5)
                        ->get();
                }
                else {
                    // Bank staff sees all transactions
                    $transactions = App\Models\EscrowTransaction::with(['buyer', 'seller'])
                        ->latest()
                        ->take(5)
                        ->get();
                }
            @endphp
            
            @if($transactions->isNotEmpty())
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Escrow ID</th>
                                <th>Buyer</th>
                                <th>Seller</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->escrow_id }}</td>
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
                                    <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('escrow.show', $transaction->escrow_id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No transactions found. 
                    @if(auth()->user()->role == 'buyer')
                        <a href="{{ route('escrow.create') }}" class="alert-link">Create a new escrow transaction</a>.
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection


