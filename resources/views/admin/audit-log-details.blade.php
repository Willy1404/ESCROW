@extends('layouts.app')

@section('title', 'Audit Log Details - Azania Bank')

@section('header', 'Audit Log Details')

@section('header_buttons')
    <a href="{{ route('audit-log.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Audit Log
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Log Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Date & Time:</div>
                    <div class="col-md-8">{{ $log->created_at->format('M d, Y H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Action:</div>
                    <div class="col-md-8">
                        <span class="badge bg-info">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">User:</div>
                    <div class="col-md-8">
                        @if($log->user)
                            {{ $log->user->name }} ({{ $log->user->user_id }})
                            <div class="text-muted">Role: {{ ucfirst($log->user->role) }}</div>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </div>
                </div>
                @if($log->entity_type && $log->entity_id)
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Entity Type:</div>
                        <div class="col-md-8">{{ ucfirst($log->entity_type) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Entity ID:</div>
                        <div class="col-md-8">{{ $log->entity_id }}</div>
                    </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">IP Address:</div>
                    <div class="col-md-8">{{ $log->ip_address }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">User Agent:</div>
                    <div class="col-md-8">
                        <div style="word-break: break-all;">{{ $log->user_agent }}</div>
                    </div>
                </div>
                @if($log->details)
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Details:</div>
                        <div class="col-md-8">{{ $log->details }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        @if($log->old_values || $log->new_values)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Changed Values</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($log->old_values && $log->new_values)
                                @foreach($log->new_values as $key => $value)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                        <td>
                                            @if(isset($log->old_values[$key]))
                                                @if(is_array($log->old_values[$key]))
                                                    <pre>{{ json_encode($log->old_values[$key], JSON_PRETTY_PRINT) }}</pre>
                                                @else
                                                    {{ $log->old_values[$key] }}
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_array($value))
                                                <pre>{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif($log->new_values)
                                @foreach($log->new_values as $key => $value)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                        <td><span class="text-muted">—</span></td>
                                        <td>
                                            @if(is_array($value))
                                                <pre>{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif($log->old_values)
                                @foreach($log->old_values as $key => $value)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                        <td>
                                            @if(is_array($value))
                                                <pre>{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                        <td><span class="text-muted">—</span></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection