@extends('layouts.app')

@section('title', 'System Reports - Azania Bank')

@section('header', 'System Reports')

@php
// Simple function to format currency amounts based on currency code
function formatCurrency($amount, $currency) {
    switch ($currency) {
        case 'TZS':
            return 'TZS ' . number_format($amount, 0); // No decimal for TZS
        case 'USD':
            return '$' . number_format($amount, 2);
        case 'EUR':
            return '€' . number_format($amount, 2);
        case 'GBP':
            return '£' . number_format($amount, 2);
        default:
            return $currency . ' ' . number_format($amount, 2);
    }
}

// Prepare the data with currency grouping
$currencyGroupedTransactions = [];

// Group the monthly transactions by currency
foreach($monthly_transactions as $transaction) {
    // Get the currency-specific transactions for this month and year
    $currencyTransactions = App\Models\EscrowTransaction::whereRaw('MONTH(created_at) = ?', [$transaction->month])
        ->whereRaw('YEAR(created_at) = ?', [$transaction->year])
        ->select('currency', \Illuminate\Support\Facades\DB::raw('SUM(amount) as amount'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
        ->groupBy('currency')
        ->get();
    
    // Store them in the grouped array
    $currencyGroupedTransactions[$transaction->year . '-' . $transaction->month] = [
        'month' => date('F', mktime(0, 0, 0, $transaction->month, 1)),
        'year' => $transaction->year,
        'currencies' => $currencyTransactions
    ];
}
@endphp

@section('content')
    <!-- Transaction Volume -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Monthly Transaction Volume</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Transactions</th>
                            <th>Total<th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currencyGroupedTransactions as $yearMonth => $data)
                            <tr>
                                <td>{{ $data['month'] }}</td>
                                <td>{{ $data['year'] }}</td>
                                <td>
                                    @php
                                        $totalCount = 0;
                                        foreach ($data['currencies'] as $currency) {
                                            $totalCount += $currency->count;
                                        }
                                    @endphp
                                    {{ $totalCount }}
                                </td>
                                <td>
                                    @foreach($data['currencies'] as $currency)
                                        <div>{{ formatCurrency($currency->amount, $currency->currency) }}</div>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Dispute Rate -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Dispute Statistics</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h2 class="display-4">{{ $dispute_rate['percentage'] }}%</h2>
                            <p>Dispute Rate</p>
                            <div class="text-muted">
                                {{ $dispute_rate['disputed'] }} disputes out of {{ $dispute_rate['total_transactions'] }} transactions
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Tips to Reduce Disputes</h5>
                            <ul class="mb-0">
                                <li>Encourage sellers to provide clear product descriptions</li>
                                <li>Set realistic expectations for delivery times</li>
                                <li>Verify seller credentials thoroughly</li>
                                <li>Implement longer inspection periods for expensive items</li>
                                <li>Provide clear guidelines on return policies</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transaction Volume by Currency -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Transaction Volume by Currency</h5>
        </div>
        <div class="card-body">
            @php
                $currencyStats = App\Models\EscrowTransaction::select('currency', 
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'), 
                    \Illuminate\Support\Facades\DB::raw('SUM(amount) as total_amount'),
                    \Illuminate\Support\Facades\DB::raw('MIN(amount) as min_amount'),
                    \Illuminate\Support\Facades\DB::raw('MAX(amount) as max_amount'),
                    \Illuminate\Support\Facades\DB::raw('AVG(amount) as avg_amount'))
                    ->groupBy('currency')
                    ->get();
            @endphp
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Transactions</th>
                            <th>Total Value</th>
                            <th>Minimum</th>
                            <th>Maximum</th>
                            <th>Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currencyStats as $stat)
                            <tr>
                                <td>{{ $stat->currency }}</td>
                                <td>{{ $stat->count }}</td>
                                <td>{{ formatCurrency($stat->total_amount, $stat->currency) }}</td>
                                <td>{{ formatCurrency($stat->min_amount, $stat->currency) }}</td>
                                <td>{{ formatCurrency($stat->max_amount, $stat->currency) }}</td>
                                <td>{{ formatCurrency($stat->avg_amount, $stat->currency) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Download Reports -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Download Reports</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body py-4">
                            <i class="fas fa-file-csv fa-3x text-primary mb-3"></i>
                            <h5>Transaction Report</h5>
                            <p class="text-muted">Download detailed transaction data</p>
                            <button class="btn btn-primary mt-2">Export CSV</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body py-4">
                            <i class="fas fa-file-csv fa-3x text-success mb-3"></i>
                            <h5>User Report</h5>
                            <p class="text-muted">Download user registration data</p>
                            <button class="btn btn-success mt-2">Export CSV</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body py-4">
                            <i class="fas fa-file-csv fa-3x text-danger mb-3"></i>
                            <h5>Dispute Report</h5>
                            <p class="text-muted">Download dispute resolution data</p>
                            <button class="btn btn-danger mt-2">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection