@extends('layouts.app')

@section('title', 'Manage Disputes - Azania Bank')

@section('header', 'Dispute Management')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Disputes</h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search disputes...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
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
                                <th>Buyer</th>
                                <th>Seller</th>
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
@endsection