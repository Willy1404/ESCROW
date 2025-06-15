@extends('layouts.app')

@section('title', 'Reset Password - Azania Bank')

@section('header', 'Reset Staff Password')

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
                    <h5 class="mb-0">Reset Password for {{ $user->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Resetting this password will invalidate the current password immediately.
                    </div>
                    
                    <form action="{{ route('it_support.staff.update-password', $user->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
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
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="text" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-warning">Reset Password</button>
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