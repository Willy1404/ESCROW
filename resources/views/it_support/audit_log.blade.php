@extends('layouts.app')

@section('title', 'System Audit Log - Azania Bank')

@section('header', 'System Audit Log')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Audit Trail</h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" placeholder="Search audit log..." id="auditSearchInput">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="alert alert-primary m-3">
                <i class="fas fa-info-circle me-2"></i> 
                <strong>Available Audit Features:</strong>
                <ul class="mb-0 mt-2">
                    <li>Staff login attempts (successful and failed)</li>
                    <li>Password changes and resets</li>
                    <li>Staff account creations/modifications</li>
                    <li>Maker-checker actions (approvals and rejections)</li>
                    <li>Escrow transaction status changes</li>
                    <li>Dispute creations and resolutions</li>
                    <li>Other system-level actions</li>
                </ul>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="auditLogTable">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Get the latest audit log records (example data - will be replaced)
                            $logs = \App\Models\AuditLog::with('user')
                                ->orderBy('created_at', 'desc')
                                ->take(10)
                                ->get();
                        @endphp
                        
                        @if($logs->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-database me-2"></i> Audit log records will appear here
                                </td>
                            </tr>
                        @else
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                    <td>
                                        @if($log->user)
                                            {{ $log->user->name }} <br>
                                            <small class="text-muted">{{ $log->user->role }}</small>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->details)
                                            {{ \Illuminate\Support\Str::limit($log->details, 50) }}
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
                        @endif
                    </tbody>
                </table>
            </div>
            
            <div class="m-3 text-center">
                <a href="{{ route('audit-log.index') }}" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> View Full Audit Log
                </a>
            </div>
        </div>
    </div>

    @section('scripts')
    <script>
        // Simple filter function for the audit log table
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('auditSearchInput');
            const table = document.getElementById('auditLogTable');
            
            if (searchInput && table) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = table.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const display = text.includes(searchTerm) ? '' : 'none';
                        row.style.display = display;
                    });
                });
            }
        });
    </script>
    @endsection
@endsection