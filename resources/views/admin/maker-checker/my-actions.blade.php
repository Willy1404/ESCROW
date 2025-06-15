@extends('layouts.app')

@section('title', 'My Pending Actions - Azania Bank')

@section('header', 'My Submitted Actions')

@section('content')
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab">All Actions</a>
        </li>
        @foreach($actionsByType as $type => $actions)
            <li class="nav-item">
                <a class="nav-link" id="{{ $type }}-tab" data-bs-toggle="tab" href="#{{ $type }}" role="tab">
                    {{ ucfirst(str_replace('_', ' ', $type)) }} 
                    <span class="badge bg-secondary">{{ count($actions) }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        <!-- All Actions Tab -->
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Actions Submitted by Me</h5>
                </div>
                <div class="card-body p-0">
                    @if($pendingActions->isEmpty())
                        <div class="alert alert-info m-3">
                            <i class="fas fa-info-circle me-2"></i> You have no pending actions awaiting approval.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Action ID</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingActions as $action)
                                        <tr>
                                            <td>{{ $action->action_id }}</td>
                                            <td>
                                                @if($action->action_type == 'dispute_resolution')
                                                    <span class="badge bg-danger">Dispute Resolution</span>
                                                @elseif($action->action_type == 'user_role_change')
                                                    <span class="badge bg-warning">Role Change</span>
                                                @elseif($action->action_type == 'user_suspension')
                                                    <span class="badge bg-dark">User Suspension</span>
                                                @elseif($action->action_type == 'user_creation')
                                                    <span class="badge bg-success">User Creation</span>
                                                @elseif($action->action_type == 'user_update')
                                                    <span class="badge bg-primary">User Update</span>
                                                @elseif($action->action_type == 'user_deletion')
                                                    <span class="badge bg-danger">User Deletion</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($action->action_type)) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $action->description }}</td>
                                            <td>
                                                @if($action->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($action->status == 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($action->status == 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $action->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.maker-checker.show', $action->action_id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="px-4 py-3 border-top">
                            {{ $pendingActions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Categorized Tabs -->
        @foreach($actionsByType as $type => $actions)
            <div class="tab-pane fade" id="{{ $type }}" role="tabpanel" aria-labelledby="{{ $type }}-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ ucfirst(str_replace('_', ' ', $type)) }} Actions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Action ID</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($actions as $action)
                                        <tr>
                                            <td>{{ $action->action_id }}</td>
                                            <td>{{ $action->description }}</td>
                                            <td>
                                                @if($action->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($action->status == 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($action->status == 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $action->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.maker-checker.show', $action->action_id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('scripts')
<script>
    // Preserve active tab on page reload
    $(document).ready(function() {
        // Check for hash in URL and activate tab if present
        if (window.location.hash) {
            $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
        }
        
        // Update hash when tab is clicked
        $('.nav-tabs a').on('shown.bs.tab', function(e) {
            window.location.hash = e.target.hash;
        });
    });
</script>
@endsection