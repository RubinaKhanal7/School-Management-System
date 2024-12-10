<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ledger;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    private function calculateOpeningBalance($accountId, $date = null)
    {
        $account = Account::find($accountId);
        $balance = [
            'amount' => 0,
            'type' => $account->balance_type ?? 'DR'
        ];

        $openingBalanceQuery = Transaction::query()
            ->select(
                DB::raw('SUM(transaction_details.debit) as total_debit'),
                DB::raw('SUM(transaction_details.credit) as total_credit')
            )
            ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->where('transaction_details.account_id', $accountId)
            ->where('transactions.is_opening_balance', 1);

        if ($date) {
            $openingBalanceQuery->where('transactions.transaction_date_nepali', '<=', $date);
        }

        $openingBalanceTransaction = $openingBalanceQuery->first();

        if ($openingBalanceTransaction) {
            $netOpeningBalance = $openingBalanceTransaction->total_debit - $openingBalanceTransaction->total_credit;
            $balance['amount'] = abs($netOpeningBalance);
            $balance['type'] = $netOpeningBalance >= 0 ? 'DR' : 'CR';
        }

        if ($date) {
            $previousTransactions = Transaction::query()
                ->select(
                    DB::raw('SUM(transaction_details.debit) as total_debit'),
                    DB::raw('SUM(transaction_details.credit) as total_credit')
                )
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->where('transaction_details.account_id', $accountId)
                ->where('transactions.is_opening_balance', 0)
                ->where('transactions.transaction_date_nepali', '<', $date)
                ->first();

            if ($previousTransactions) {
                $currentBalance = ($balance['type'] === 'DR' ? $balance['amount'] : -$balance['amount']);
                $netAmount = $currentBalance + ($previousTransactions->total_debit - $previousTransactions->total_credit);

                $balance['amount'] = abs($netAmount);
                $balance['type'] = $netAmount >= 0 ? 'DR' : 'CR';
            }
        }

        return $balance;
    }

    public function index(Request $request)
    {
        $accounts = Account::where('created_by', Auth::id())->orderBy('name')->get();
        $ledgerEntries = collect();
        $openingBalance = null;
        $closingBalance = null;
    
        if ($request->filled('account_id')) {
            $openingBalance = $this->calculateOpeningBalance(
                $request->account_id,
                $request->from_date
            );

            $query = Transaction::query()
                ->select([
                    'transactions.transaction_date_nepali',
                    'transactions.voucher_no',
                    'transactions.description',
                    'voucher_types.name as voucher_type',
                    'transaction_details.debit',
                    'transaction_details.credit',
                ])
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('voucher_types', 'transactions.voucher_type_id', '=', 'voucher_types.id')
                ->where('transaction_details.account_id', $request->account_id)
                ->where('transactions.is_opening_balance', 0)
                ->orderBy('transactions.transaction_date_nepali')
                ->orderBy('transactions.voucher_no');

            if ($request->filled('from_date') && !$request->filled('to_date')) {
                $query->where('transactions.transaction_date_nepali', '=', $request->from_date);
            } elseif (!$request->filled('from_date') && $request->filled('to_date')) {
                $query->where('transactions.transaction_date_nepali', '=', $request->to_date);
            } elseif ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('transactions.transaction_date_nepali', [
                    $request->from_date,
                    $request->to_date
                ]);
            }
    
            $ledgerEntries = $query->get();
            $closingBalance = $this->calculateClosingBalance($openingBalance, $ledgerEntries);
        }
    
        return view('backend.school_admin.ledger.index', compact(
            'accounts', 
            'ledgerEntries', 
            'openingBalance',
            'closingBalance'
        ));
    }

    private function calculateClosingBalance($openingBalance, $ledgerEntries)
    {
        $netAmount = $openingBalance['type'] === 'DR' ? $openingBalance['amount'] : -$openingBalance['amount'];
        foreach ($ledgerEntries as $entry) {
            $netAmount += ($entry->debit ?? 0) - ($entry->credit ?? 0);
        }

        return [
            'amount' => abs($netAmount),
            'type' => $netAmount >= 0 ? 'DR' : 'CR'
        ];
    }
}