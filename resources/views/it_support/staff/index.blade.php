@extends('layouts.app')

@section('title', 'Manage Staff - Azania Bank')

@section('header', 'Manage Bank Staff')

@section('header_buttons')
    <a href="{{ route('it_support.staff.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Add New Staff
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Staff Members</h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search staff..." id="staffSearch">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($staff->isEmpty())
                <div class="alert alert-info m-3">
                    <i class="fas fa-info-circle me-2"></i> No staff members found.
                    <a href="{{ route('it_support.staff.create') }}" class="alert-link">Add a new staff member?</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="staffTable">
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
                            @foreach($staff as $user)
                                <tr>
                                    <td>{{ $user->user_id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role == 'maker')
                                            <span class="badge bg-primary">Maker</span>
                                        @elseif($user->role == 'checker')
                                            <span class="badge bg-warning">Checker</span>
                                        @elseif($user->role == 'it_support')
                                            <span class="badge bg-success">IT Support</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Suspended</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('it_support.staff.edit', $user->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('it_support.staff.reset-password', $user->id) }}" class="btn btn-sm btn-outline-warning" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </a>
                                            @if($user->id != auth()->user()->id) {{-- Don't allow self-suspension --}}
                                                <form action="{{ route('it_support.staff.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-{{ $user->status == 'active' ? 'danger' : 'success' }}" 
                                                            onclick="return confirm('Are you sure you want to {{ $user->status == 'active' ? 'suspend' : 'activate' }} this user?')"
                                                            title="{{ $user->status == 'active' ? 'Suspend' : 'Activate' }}">
                                                        <i class="fas fa-{{ $user->status == 'active' ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="small text-muted">
                        Showing {{ $staff->firstItem() }} to {{ $staff->lastItem() }} of {{ $staff->total() }} results
                    </div>
                    {{ $staff->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('staffSearch');
            const table = document.getElementById('staffTable');
            
            if (searchInput && table) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = table.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endsection