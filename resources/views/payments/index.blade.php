@extends('layouts.app')

@section('title', 'Payments - Azania Bank')

@section('header', 'Payment History')

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
            <h5 class="mb-0">All Payments</h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search payments...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($payments->isEmpty())
                <div class="alert alert-info m-3">
                    <i class="fas fa-info-circle me-2"></i> No payments found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Escrow ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_id }}</td>
                                    <td>
                                        <a href="{{ route('escrow.show', $payment->escrow_id) }}">
                                            {{ $payment->escrow_id }}
                                        </a>
                                    </td>
                                    <td>{{ formatCurrency($payment->amount, $payment->escrowTransaction->currency ?? 'TZS') }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>
                                        @if($payment->status == 'Pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($payment->status == 'Completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($payment->status == 'Failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('escrow.show', $payment->escrow_id) }}" class="btn btn-sm btn-outline-primary">
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
                            Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Previous Page Link -->
                                @if ($payments->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 bg-transparent" aria-hidden="true">&laquo; Previous</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link border-0" href="{{ $payments->previousPageUrl() }}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo; Previous</span>
                                        </a>
                                    </li>
                                @endif

                                <!-- Pagination Elements -->
                                @foreach ($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
                                    @if ($page == $payments->currentPage())
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
                                @if ($payments->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link border-0" href="{{ $payments->nextPageUrl() }}" aria-label="Next">
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