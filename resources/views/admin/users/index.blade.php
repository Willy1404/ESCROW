@extends('layouts.app')

@section('title', 'User Management - Azania Bank')

@section('header', 'User Management')

@section('header_buttons')
    @if(auth()->user()->role === 'maker')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Add New User
    </a>
    @endif
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .action-buttons .btn {
        padding: .25rem .5rem;
        font-size: 0.8rem;
    }
    .user-status-active {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .user-status-suspended {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-role-buyer {
        background-color: #e3f2fd;
        color: #0d6efd;
    }
    .badge-role-seller {
        background-color: #f8f9fa;
        color: #212529;
    }
    .badge-role-maker {
        background-color: #f0f8ff;
        color: #0dcaf0;
    }
    .badge-role-checker {
        background-color: #fff8f0;
        color: #fd7e14;
    }
    .badge-role-it_support {
        background-color: #f0fff8;
        color: #20c997;
    }
    .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
    }
    .user-action-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="users-table" class="table table-hover table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->user_id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-role-{{ $user->role }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            <span class="user-status-{{ $user->status }}">
                                {{ ucfirst($user->status) }}
                            </span>
                            
                            @php
                                // Check for pending actions on this user
                                $pendingAction = App\Models\PendingAction::where('entity_type', 'user')
                                    ->where('entity_id', $user->user_id)
                                    ->where('status', 'pending')
                                    ->first();
                            @endphp
                            
                            @if($pendingAction)
                                <span class="user-action-pending" title="Pending action: {{ $pendingAction->description }}">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="action-buttons">
    @if(auth()->user()->role === 'maker')
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton{{ $user->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $user->id }}">
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                        data-user-id="{{ $user->id }}"
                        data-user-name="{{ $user->name }}"
                        data-user-email="{{ $user->email }}"
                        data-user-role="{{ $user->role }}">
                        <i class="fas fa-edit text-primary"></i> Edit
                    </a>
                </li>
                <li>
                    @if($user->status === 'active')
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#suspendUserModal" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                            <i class="fas fa-ban text-warning"></i> Suspend
                        </a>
                    @else
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#activateUserModal" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                            <i class="fas fa-check-circle text-success"></i> Activate
                        </a>
                    @endif
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                        <i class="fas fa-trash-alt text-danger"></i> Delete
                    </a>
                </li>
            </ul>
        </div>
    @elseif(auth()->user()->role === 'checker')
        @php
            // Check for pending actions on this user
            $pendingAction = App\Models\PendingAction::where('entity_type', 'user')
                ->where('entity_id', $user->user_id)
                ->where('status', 'pending')
                ->first();
        @endphp
        
        @if($pendingAction)
            <a href="{{ route('admin.maker-checker.show', $pendingAction->action_id) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-check-double"></i> Review
            </a>
        @else
            <span class="text-muted">No pending actions</span>
        @endif
    @endif
</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> 
                        This change will require approval from a checker before taking effect.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit For Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Suspend User Modal -->
<div class="modal fade" id="suspendUserModal" tabindex="-1" aria-labelledby="suspendUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Important: Changed to POST method -->
            <form id="suspendUserForm" action="" method="POST">
                @csrf
                <!-- No method override needed -->
                <div class="modal-header">
                    <h5 class="modal-title" id="suspendUserModalLabel">Suspend User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to suspend <strong id="suspend_user_name"></strong>?</p>
                    <div class="mb-3">
                        <label for="suspension_reason" class="form-label">Reason for Suspension</label>
                        <textarea class="form-control" id="suspension_reason" name="suspension_reason" rows="3" required></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> 
                        This action will require approval from a checker before taking effect.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit For Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Activate User Modal -->
<div class="modal fade" id="activateUserModal" tabindex="-1" aria-labelledby="activateUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="activateUserForm" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="activateUserModalLabel">Activate User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to activate <strong id="activate_user_name"></strong>?</p>
                    <div class="mb-3">
                        <label for="activation_reason" class="form-label">Reason for Activation</label>
                        <textarea class="form-control" id="activation_reason" name="suspension_reason" rows="3"></textarea>
                        <div class="form-text">Please provide a reason for activating this account.</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> 
                        This action will require approval from a checker before taking effect.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit For Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Changed to POST method -->
            <form id="deleteUserForm" action="" method="POST">
                @csrf
                <!-- No @method('DELETE') -->
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="delete_user_name"></strong>?</p>
                    <div class="mb-3">
                        <label for="deletion_reason" class="form-label">Reason for Deletion</label>
                        <textarea class="form-control" id="deletion_reason" name="deletion_reason" rows="3" required></textarea>
                    </div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> 
                        This action cannot be undone and will require approval from a checker before taking effect.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit For Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Suspend User Modal
    const suspendUserModal = document.getElementById('suspendUserModal');
    if (suspendUserModal) {
        suspendUserModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            
            const modal = this;
            modal.querySelector('#suspend_user_name').textContent = userName;
            
            const form = document.getElementById('suspendUserForm');
            form.action = '/admin/users/' + userId + '/suspend';
        });
    }
    
    // Activate User Modal
    const activateUserModal = document.getElementById('activateUserModal');
    if (activateUserModal) {
        activateUserModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            
            const modal = this;
            modal.querySelector('#activate_user_name').textContent = userName;
            
            const form = document.getElementById('activateUserForm');
            form.action = '/admin/users/' + userId + '/suspend';
        });
    }
    
    // Delete User Modal
    const deleteUserModal = document.getElementById('deleteUserModal');
    if (deleteUserModal) {
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            
            const modal = this;
            modal.querySelector('#delete_user_name').textContent = userName;
            
            const form = document.getElementById('deleteUserForm');
            form.action = '/admin/users/' + userId + '/delete';
        });
    }
});
</script>
@endsection
