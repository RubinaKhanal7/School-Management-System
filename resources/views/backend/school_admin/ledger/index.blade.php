@extends('backend.layouts.master')
@include('backend.includes.nepalidate')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Ledger Account</h4>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.ledgers.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="account_id">Select Account</label>
                            <select name="account_id" id="account_id" class="form-control" required>
                                <option value="">Choose Account</option>
                                @foreach($accounts as $account)
                                    @if($account->id != 1)
                                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="from_date">From Date</label>
                            <input type="text" name="from_date" id="nepali-datepicker" class="form-control nepali-date" 
                                   value="{{ request('from_date') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="to_date">To Date</label>
                            <input type="text" name="to_date" id="nepali-datepicker2" class="form-control nepali-date" 
                                   value="{{ request('to_date') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary form-control">View Ledger</button>
                        </div>
                    </div>
                </div>
            </form>

            @if(request()->filled('account_id'))
            <div class="d-flex justify-content-end">
                <h5 class="mb-3">
                    Opening Balance: {{ number_format($openingBalance['amount'], 2) }}
                </h5>
            </div>            
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date (BS)</th>
                            <th>Voucher No</th>
                            <th>Description</th>
                            <th>Voucher Type</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $runningBalance = $openingBalance['type'] === 'DR' ? 
                                $openingBalance['amount'] : 
                                -$openingBalance['amount'];
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp
        
                        @foreach($ledgerEntries as $entry)
                            @php
                                $totalDebit += $entry->debit ?? 0;
                                $totalCredit += $entry->credit ?? 0;
                                $runningBalance += ($entry->debit ?? 0) - ($entry->credit ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $entry->transaction_date_nepali }}</td>
                                <td>{{ $entry->voucher_no }}</td>
                                <td>{{ $entry->description }}</td>
                                <td>{{ $entry->voucher_type }}</td>
                                <td class="text-right">{{ $entry->debit ? number_format($entry->debit, 2) : '-' }}</td>
                                <td class="text-right">{{ $entry->credit ? number_format($entry->credit, 2) : '-' }}</td>
                                <td class="text-right">
                                    {{ number_format(abs($runningBalance), 2) }} 
                                    {{ $runningBalance >= 0 ? 'DR' : 'CR' }}
                                </td>
                            </tr>
                        @endforeach
        
                        <tr class="font-weight-bold bg-light">
                            <td colspan="4" class="text-right">
                                <strong>Total</strong>
                            </td>
                            <td class="text-right">
                                <strong>{{ number_format($totalDebit, 2) }}</strong>
                            </td>
                            <td class="text-right">
                                <strong>{{ number_format($totalCredit, 2) }}</strong>
                            </td>
                            <td class="text-right">
                                <strong>
                                    {{ number_format($closingBalance['amount'], 2) }}
                                    {{ $closingBalance['type'] }}
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                <h5>
                    Closing Balance: {{ number_format($closingBalance['amount'], 2) }}
                </h5>
            </div>
        @endif
        </div>
    </div>
</div>
@endsection
