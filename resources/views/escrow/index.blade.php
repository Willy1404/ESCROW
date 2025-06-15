@extends('layouts.app')

@section('title', 'Escrow Transactions - Azania Bank')

@section('header', 'Escrow Transactions')

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
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Transactions</h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search transactions...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($transactions->isEmpty())
                <div class="alert alert-info m-3">
                    <i class="fas fa-info-circle me-2"></i> No escrow transactions found.
                    @if(auth()->user()->role == 'buyer')
                    <a href="{{ route('escrow.create') }}" class="alert-link">Create a new one?</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                                            <span class="badge bg-warning text-dark">Funds Pending</span>
                                        @elseif($transaction->status == 'Funds Received')
                                            <span class="badge bg-info">Funds Received</span>
                                        @elseif($transaction->status == 'In Transit')
                                            <span class="badge bg-primary">In Transit</span>
                                        @elseif($transaction->status == 'Waiting for Buyer Approval')
                                            <span class="badge bg-secondary">Waiting Approval</span>
                                        @elseif($transaction->status == 'Funds Released')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($transaction->status == 'Escrow On Hold')
                                            <span class="badge bg-danger">On Hold</span>
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
                
                <!-- Professional Pagination -->
                <div class="px-4 py-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} results
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Previous Page Link -->
                                @if ($transactions->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 bg-transparent" aria-hidden="true">&laquo; Previous</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link border-0" href="{{ $transactions->previousPageUrl() }}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo; Previous</span>
                                        </a>
                                    </li>
                                @endif

                                <!-- Pagination Elements -->
                                @foreach ($transactions->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                                    @if ($page == $transactions->currentPage())
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                <!-- Next Page Link -->
                                @if ($transactions->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link border-0" href="{{ $transactions->nextPageUrl() }}" aria-label="Next">
                                            <span aria-hidden="true">Next &raquo;</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 bg-transparent" aria-hidden="true">Next &raquo;</span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .page-link {
            color: #3399BB;
        }
        .page-item.active .page-link {
            background-color: #3399BB;
            border-color: #3399BB;
        }
        .page-link:hover {
            color: #2980a5;
        }
    </style>
@endsection