@extends('layouts.app')

@section('title', 'Create Staff - Azania Bank')

@section('header', 'Create New Staff Member')

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
                    <form action="{{ route('it_support.staff.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
    <label for="role" class="form-label">Role</label>
    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
        <option value="">Select role</option>
        <option value="maker" {{ old('role') == 'maker' ? 'selected' : '' }}>Maker</option>
        <option value="checker" {{ old('role') == 'checker' ? 'selected' : '' }}>Checker</option>
        <option value="it_support" {{ old('role') == 'it_support' ? 'selected' : '' }}>IT Support</option>
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
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary" id="generatePassword">
                                    <i class="fas fa-random"></i> Generate
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="text" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Staff Member</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('generatePassword').addEventListener('click', function() {
                fetch('{{ route("it_support.staff.generate-password") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('password').value = data.password;
                    document.getElementById('password_confirmation').value = data.password;
                })
                .catch(error => {
                    console.error('Error generating password:', error);
                });
            });
        });
    </script>
@endsection