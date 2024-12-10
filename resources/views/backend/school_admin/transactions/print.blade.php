<!DOCTYPE html>
<html>
<head>
    <title>Voucher</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .contact-info {
            text-align: center;
            margin-bottom: 15px;
            color: #666;
        }
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .transaction-table th,
        .transaction-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }
        .transaction-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .amount-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        .description-row td {
            background-color: #f8f9fa;
            font-style: italic;
            color: #666;
            padding-left: 40px;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #dee2e6;
        }
        .print-button {
            text-align: center;
            margin-top: 30px;
        }
        .print-button button {
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .print-button button:hover {
            background-color: #0b5ed7;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                margin: 0;
                padding: 20px;
            }
            .transaction-table {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $transaction->createdBy->f_name ?? 'N/A'}}</h2>
        <div class="contact-info">
            {{ $schoolInfo->address ?? $schoolInfo['address'] }}<br>
            Phone: {{ $schoolInfo->phone ?? $schoolInfo['phone'] }} | Email: {{ $schoolInfo->email ?? $schoolInfo['email'] }}
        </div>
        <h3>{{ $transaction->voucherType->name }} Voucher #{{ $transaction->voucher_no }}</h3>
    </div>

    <table class="transaction-table">
        <thead>
            <tr>
                <th style="width: 15%">Date</th>
                <th style="width: 45%">Account</th>
                <th style="width: 20%">Debit (DR)</th>
                <th style="width: 20%">Credit (CR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->transactionDetails as $detail)
                <tr>
                    <td>{{ $detail->transaction->transaction_date_nepali }}</td>
                    <td>{{ $detail->account->name }}</td>
                    <td class="amount-cell">{{ number_format($detail->debit, 2) }}</td>
                    <td class="amount-cell">{{ number_format($detail->credit, 2) }}</td>
                </tr>
                @if($loop->last)
                    <tr class="description-row">
                        <td colspan="4">
                            <strong>Description:</strong> {{ $transaction->description }}
                        </td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: right"><strong>Total</strong></td>
                <td class="amount-cell">{{ number_format($totalDebit, 2) }}</td>
                <td class="amount-cell">{{ number_format($totalCredit, 2) }}</td>
            </tr>
        </tbody>
    </table>    
    
    <div class="print-button">
        <button onclick="window.print()">Print Voucher</button>
    </div>
</body>
</html>