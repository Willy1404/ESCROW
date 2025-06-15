@extends('layouts.app')

@section('title', 'Edit Staff - Azania Bank')

@section('header', 'Edit Staff Member')

@section('header_buttons')
    <a href="{{ route('it_support.staff.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Staff List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Staff Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('it_support.staff.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User ID</label>
                            <input type="text" class="form-control" id="user_id" value="{{ $user->user_id }}" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                        
                        <div class="mb-3">
    <label for="role" class="form-label">Role</label>
    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
        <option value="maker" {{ (old('role', $user->role) == 'maker') ? 'selected' : '' }}>Maker</option>
        <option value="checker" {{ (old('role', $user->role) == 'checker') ? 'selected' : '' }}>Checker</option>
        <option value="it_support" {{ (old('role', $user->role) == 'it_support') ? 'selected' : '' }}>IT Support</option>
    </select>
    @error('role')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">
        <strong>Maker:</strong> Can initiate actions but cannot approve them.<br>
        <strong>Checker:</strong> Can review and approve actions initiated by makers.<br>
        <strong>IT Support:</strong> Can manage all user accounts and system settings.
    </div>
</div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Staff Member</button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5>Reset Password</h5>
                            <p class="text-muted">To reset this user's password, click the button.</p>
                        </div>
                        <a href="{{ route('it_support.staff.reset-password', $user->id) }}" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i> Reset Password
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5>Account Status</h5>
                            <p class="text-muted">Current status: 
                                @if($user->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </p>
                        </div>
                        
                        @if($user->id != auth()->user()->id) {{-- Don't allow self-suspension --}}
                            <form action="{{ route('it_support.staff.toggle-status', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-{{ $user->status == 'active' ? 'danger' : 'success' }}" 
                                        onclick="return confirm('Are you sure you want to {{ $user->status == 'active' ? 'suspend' : 'activate' }} this user?')">
                                    <i class="fas fa-{{ $user->status == 'active' ? 'ban' : 'check' }} me-2"></i> 
                                    {{ $user->status == 'active' ? 'Suspend Account' : 'Activate Account' }}
                                </button>
                            </form>
                        @else
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-info-circle me-2"></i> Cannot change own status
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection