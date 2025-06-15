@extends('layouts.app')

@section('title', 'Disputes - Azania Bank')

@section('header', 'Dispute Management')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if(auth()->user()->role === 'checker')
                    Disputes with Pending Resolutions
                @else
                    All Disputes
                @endif
            </h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search disputes...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if(auth()->user()->role === 'checker')
                @php
                    // For checkers, get only disputes that have pending actions
                    $pendingActionDisputes = App\Models\PendingAction::where('entity_type', 'dispute')
                        ->where('status', 'pending')
                        ->pluck('entity_id')
                        ->toArray();
                    
                    // Filter disputes to show only those with pending actions
                    $filteredDisputes = $disputes->filter(function($dispute) use ($pendingActionDisputes) {
                        return in_array($dispute->dispute_id, $pendingActionDisputes);
                    });
                @endphp
                
                @if($filteredDisputes->isEmpty())
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i> There are no disputes with pending resolutions requiring your approval.
                        <p class="mb-0 mt-2">Please check the <a href="{{ route('admin.maker-checker.index') }}">Pending Approvals</a> section for all pending actions.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Dispute ID</th>
                                    <th>Escrow ID</th>
                                    <th>Filed By</th>
                                    <th>Against</th>
                                    <th>Status</th>
                                    <th>Filed On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($filteredDisputes as $dispute)
                                    <tr>
                                        <td>{{ $dispute->dispute_id }}</td>
                                        <td>
                                            <a href="{{ route('escrow.show', $dispute->escrow_id) }}">
                                                {{ $dispute->escrow_id }}
                                            </a>
                                        </td>
                                        <td>{{ $dispute->buyer->name }}</td>
                                        <td>{{ $dispute->seller->name }}</td>
                                        <td>
                                            @if($dispute->status == 'Pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($dispute->status == 'Resolved')
                                                <span class="badge bg-success">Resolved</span>
                                            @elseif($dispute->status == 'Rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>{{ $dispute->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $pendingActions = App\Models\PendingAction::where('entity_type', 'dispute')
                                                    ->where('entity_id', $dispute->dispute_id)
                                                    ->where('status', 'pending')
                                                    ->get();
                                            @endphp
                                            
                                            @foreach($pendingActions as $action)
                                                <a href="{{ route('admin.maker-checker.show', $action->action_id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-check-double"></i> Review
                                                </a>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <!-- Regular view for makers and other roles -->
                @if($disputes->isEmpty())
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i> No disputes found.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Dispute ID</th>
                                    <th>Escrow ID</th>
                                    <th>Filed By</th>
                                    <th>Against</th>
                                    <th>Status</th>
                                    <th>Filed On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disputes as $dispute)
                                    <tr>
                                        <td>{{ $dispute->dispute_id }}</td>
                                        <td>
                                            <a href="{{ route('escrow.show', $dispute->escrow_id) }}">
                                                {{ $dispute->escrow_id }}
                                            </a>
                                        </td>
                                        <td>{{ $dispute->buyer->name }}</td>
                                        <td>{{ $dispute->seller->name }}</td>
                                        <td>
                                            @if($dispute->status == 'Pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($dispute->status == 'Resolved')
                                                <span class="badge bg-success">Resolved</span>
                                            @elseif($dispute->status == 'Rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>{{ $dispute->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('disputes.show', ['disputeId' => $dispute->dispute_id]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(auth()->user()->role == 'maker' && $dispute->status == 'Pending')
                                                <a href="{{ route('disputes.resolve', ['disputeId' => $dispute->dispute_id]) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-gavel"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
            
            <!-- Professional Pagination -->
            <div class="px-4 py-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ $disputes->firstItem() }} to {{ $disputes->lastItem() }} of {{ $disputes->total() }} results
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- Previous Page Link -->
                            @if ($disputes->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link border-0 bg-transparent" aria-hidden="true">&laquo; Previous</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link border-0" href="{{ $disputes->previousPageUrl() }}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo; Previous</span>
                                    </a>
                                </li>
                            @endif

                            <!-- Pagination Elements -->
                            @foreach ($disputes->getUrlRange(1, $disputes->lastPage()) as $page => $url)
                                @if ($page == $disputes->currentPage())
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
                            @if ($disputes->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link border-0" href="{{ $disputes->nextPageUrl() }}" aria-label="Next">
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