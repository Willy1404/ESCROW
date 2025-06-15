@extends('layouts.app')

@section('title', 'Manage Transactions - Azania Bank')

@section('header', 'All Transactions')

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
            <h5 class="mb-0">Escrow Transactions</h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search transactions...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
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
        </div>
    </div>
@endsection