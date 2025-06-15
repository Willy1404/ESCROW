@extends('layouts.app')

@section('title', 'System Audit Log - Azania Bank')

@section('header', 'System Audit Log')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .filter-section {
        border: 1px solid #eee;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .action-badge {
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 20px;
    }
    .action-login_success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .action-login_failed { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .action-logout { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
    .action-password_change { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .action-user_creation { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .action-user_update { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .action-escrow_creation { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
    .action-escrow_status_change { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .action-dispute_creation { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .action-dispute_resolution { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
    .action-maker_checker_approved { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .action-maker_checker_rejected { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    
</style>
@endsection


@section('content')
<div class="card">
    <div class="card-body">
        <!-- Filter Section -->
        <div class="filter-section mb-4">
            <form action="{{ route('audit-log.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label for="action" class="form-label">Action Type</label>
                    <select class="form-select" id="action" name="action">
                        <option value="">All Actions</option>
                        @foreach($actionTypes as $actionType)
                            <option value="{{ $actionType }}" {{ request('action') == $actionType ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $actionType)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}" {{ request('user_id') == $user->user_id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ ucfirst($user->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="entity_type" class="form-label">Entity Type</label>
                    <select class="form-select" id="entity_type" name="entity_type">
                        <option value="">All Types</option>
                        @foreach($entityTypes as $entityType)
                            <option value="{{ $entityType }}" {{ request('entity_type') == $entityType ? 'selected' : '' }}>
                                {{ ucfirst($entityType) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('audit-log.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table id="audit-log-table" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td>
                                @if($log->user)
                                    {{ $log->user->name }} <br>
                                    <small class="text-muted">{{ $log->user->user_id }} ({{ ucfirst($log->user->role) }})</small>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>{{ $log->ip_address }}</td>
                            <td>
                                <span class="action-badge action-{{ $log->action }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td>
                                @if($log->entity_type && $log->entity_id)
                                    <strong>{{ ucfirst($log->entity_type) }}:</strong> {{ $log->entity_id }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->details)
                                    {{ Str::limit($log->details, 50) }}
                                @elseif($log->old_values || $log->new_values)
                                    <a href="{{ route('audit-log.show', $log->id) }}" class="btn btn-sm btn-outline-primary">
                                        View Changes
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
    <div class="pagination-info text-muted">
        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} records
    </div>
    <div>
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#audit-log-table').DataTable({
            "paging": false,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
            "order": [[0, 'desc']]
        });
    });
</script>
@endsection