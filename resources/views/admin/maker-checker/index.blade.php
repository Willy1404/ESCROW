@extends('layouts.app')

@section('title', 'Pending Actions - Azania Bank')

@section('header', 'Pending Actions for Approval')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .badge-action-dispute {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    .badge-action-user {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    .badge-action-suspension {
        background-color: rgba(33, 37, 41, 0.1);
        color: #212529;
    }
    .badge-action-creation {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    .badge-action-update {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    .badge-action-deletion {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
    }
</style>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @if($pendingActions->isEmpty())
                <div class="alert alert-info m-3">
                    <i class="fas fa-info-circle me-2"></i> No pending actions require your approval at this time.
                </div>
            @else
                <div class="table-responsive">
                    <table id="actions-table" class="table table-hover table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Action ID</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Created By</th>
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
                                            <span class="badge badge-action-dispute">Dispute Resolution</span>
                                        @elseif($action->action_type == 'user_role_change')
                                            <span class="badge badge-action-user">Role Change</span>
                                        @elseif($action->action_type == 'user_suspension')
                                            <span class="badge badge-action-suspension">User Suspension</span>
                                        @elseif($action->action_type == 'user_creation')
                                            <span class="badge badge-action-creation">User Creation</span>
                                        @elseif($action->action_type == 'user_update')
                                            <span class="badge badge-action-update">User Update</span>
                                        @elseif($action->action_type == 'user_deletion')
                                            <span class="badge badge-action-deletion">User Deletion</span>
                                        @else
                                            <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($action->action_type)) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $action->description }}</td>
                                    <td>{{ $action->maker->name }}</td>
                                    <td data-order="{{ $action->created_at }}">{{ $action->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.maker-checker.show', $action->action_id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Review
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

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#actions-table').DataTable({
            order: [[4, 'desc']], // Sort by created date by default
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search actions...",
                lengthMenu: "Show _MENU_ actions per page",
                info: "Showing _START_ to _END_ of _TOTAL_ actions",
                infoEmpty: "Showing 0 to 0 of 0 actions",
                infoFiltered: "(filtered from _MAX_ total actions)"
            }
        });
    });
</script>
@endsection