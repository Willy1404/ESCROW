<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3399BB;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .receipt {
            background-color: white;
            padding: 15px;
            border: 1px solid #eee;
            margin: 20px 0;
        }
        .details {
            width: 100%;
            border-collapse: collapse;
        }
        .details th, .details td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .details th {
            width: 40%;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #3399BB;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .cta-button {
            display: inline-block;
            background-color: #3399BB;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/azania-logo.png') }}" alt="Azania Bank" class="logo">
            <h1>Payment Receipt</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $transaction->buyer_name }},</p>
            
            <p>Thank you for your payment. This email serves as your receipt for your recent transaction.</p>
            
            <div class="receipt">
                <h3>Transaction Details</h3>
                <table class="details">
                    <tr>
                        <th>Control Number:</th>
                        <td>{{ $transaction->control_number }}</td>
                    </tr>
                    <tr>
                        <th>Transaction Date:</th>
                        <td>{{ $transaction->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Item:</th>
                        <td>{{ $transaction->item_name }}</td>
                    </tr>
                    <tr>
                        <th>Amount:</th>
                        <td class="amount">
                            @php
                                function formatReceipt($amount, $currency) {
                                    switch ($currency) {
                                        case 'TZS':
                                            return 'TZS ' . number_format($amount, 0);
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
                            @endphp
                            {{ formatReceipt($transaction->amount, $transaction->currency) }}
                        </td>
                    </tr>
                </table>
            </div>
            
            <p>The seller will now prepare your item for shipping. You'll receive updates as your order progresses.</p>
            
            <p><strong>Want to track your order and get full escrow protection?</strong></p>
            
            <p>Create an account to gain access to our full escrow service features, including:</p>
            <ul>
                <li>Track your order status</li>
                <li>Confirm delivery</li>
                <li>Upload photos</li>
                <li>Request assistance if needed</li>
                <li>Access our dispute resolution process</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{ route('register') }}" class="cta-button">Create an Account</a>
            </div>
            
            <p>If you already have an account, simply log in and you'll be able to see all your transactions.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email, please do not reply directly to this message.</p>
            <p>&copy; {{ date('Y') }} Azania Bank Escrow Service. All rights reserved.</p>
        </div>
    </div>
</body>
</html>