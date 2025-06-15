@extends('layouts.app')

@section('title', 'Control Numbers - Azania Bank')

@section('header', 'My Control Numbers')

@section('header_buttons')
    <a href="{{ route('control-numbers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Generate New Control Number
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
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Control Numbers</h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search control numbers...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($controlNumbers->isEmpty())
                <div class="alert alert-info m-3">
                    <i class="fas fa-info-circle me-2"></i> No control numbers found.
                    <a href="{{ route('control-numbers.create') }}" class="alert-link">Generate a new one?</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Control Number</th>
                                <th>Item</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($controlNumbers as $controlNumber)
                                <tr>
                                    <td>{{ $controlNumber->control_number }}</td>
                                    <td>{{ $controlNumber->item_name }}</td>
                                    <td>{{ formatCurrency($controlNumber->amount, $controlNumber->currency) }}</td>
                                    <td>
                                        @if($controlNumber->is_used)
                                            <span class="badge bg-success">Used</span>
                                        @elseif($controlNumber->expires_at < now())
                                            <span class="badge bg-danger">Expired</span>
                                        @else
                                            <span class="badge bg-primary">Active</span>
                                        @endif
                                    </td>
                                    <td>{{ $controlNumber->created_at->format('M d, Y') }}</td>
                                    <td>{{ $controlNumber->expires_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('control-numbers.show', $controlNumber->control_number) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$controlNumber->is_used && $controlNumber->expires_at > now())
                                            <button type="button" class="btn btn-sm btn-outline-info copy-btn" data-control-number="{{ $controlNumber->control_number }}">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        @endif
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
                            Showing {{ $controlNumbers->firstItem() }} to {{ $controlNumbers->lastItem() }} of {{ $controlNumbers->total() }} results
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Previous Page Link -->
                                @if ($controlNumbers->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 bg-transparent" aria-hidden="true">&laquo; Previous</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link border-0" href="{{ $controlNumbers->previousPageUrl() }}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo; Previous</span>
                                        </a>
                                    </li>
                                @endif

                                <!-- Pagination Elements -->
                                @foreach ($controlNumbers->getUrlRange(1, $controlNumbers->lastPage()) as $page => $url)
                                    @if ($page == $controlNumbers->currentPage())
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
                                @if ($controlNumbers->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link border-0" href="{{ $controlNumbers->nextPageUrl() }}" aria-label="Next">
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
                        this.innerHTML = '<i class="fas fa-check"></i>';
                        this.classList.remove('btn-outline-info');
                        this.classList.add('btn-success');
                        
                        // Revert back after 2 seconds
                        setTimeout(() => {
                            this.innerHTML = originalHTML;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-outline-info');
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