<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $accounts = Account::select(['id', 'name', 'code', 'opening_balance', 'balance_type', 'expense_header_no', 'status'])
            ->where('id', '!=', 1) 
            ->where('created_by', Auth::id()) 
            ->get();

            return DataTables::of($accounts)
                ->addColumn('status', function ($row) {
                    $statusClass = $row->status == 1 ? 'btn-success' : 'btn-danger';
                    $statusText = $row->status == 1 ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
                })          
                ->addColumn('actions', function ($row) {
                    $actions = '<button class="btn btn-warning btn-sm edit-account" data-id="' . $row->id . '">Edit</button> ';
                    $actions .= '<button class="btn btn-danger btn-sm delete-account" data-id="' . $row->id . '">Delete</button>';
                    return $actions;
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }
    
        return view('backend.school_admin.accounts.index');
    }    

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:accounts,code',
            'expense_header_no' => 'nullable|integer',
            'status' => 'required|in:0,1',
            'opening_balance' => 'required|numeric|min:0',
            'balance_type' => 'required|in:DR,CR',
        ]);

        DB::beginTransaction();
        
        try {
            $accountData = [
                'name' => $request->name,
                'code' => $request->code,
                'expense_header_no' => $request->expense_header_no,
                'status' => $request->status,
                'opening_balance' => $request->opening_balance,
                'balance_type' => $request->balance_type,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            
            $account = Account::create($accountData);

            if ($account) {
                $this->saveOpeningBalance($account);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully and opening balance saved'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating account: ' . $e->getMessage()
            ], 500);
        }
    }
    
    protected function saveOpeningBalance(Account $account)
    {
        $fiscalYear = FiscalYear::where('status', 1)->first();
        if (!$fiscalYear) {
            throw new \Exception('No active fiscal year found');
        }

        $amount = $account->opening_balance ?? 0;
        $balanceType = $account->balance_type ?? 'DR';

        $transaction = Transaction::create([
            'voucher_no' => Transaction::max('voucher_no') + 1,
            'description' => 'Opening balance',
            'voucher_type_id' => 1, 
            'status' => 1,
            'transaction_date_eng' => $fiscalYear->from_date,
            'transaction_date_nepali' => $fiscalYear->from_date_nepali,
            'fiscal_year_id' => $fiscalYear->id,
            'transaction_amount' => $amount,
            'is_opening_balance' => 1,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        $transactionDetails = [];

        $transactionDetails[] = [
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
            'debit' => $balanceType === 'DR' ? $amount : 0,
            'credit' => $balanceType === 'CR' ? $amount : 0,
            'fiscal_year_id' => $fiscalYear->id,
            'transaction_date_eng' => $fiscalYear->from_date,
            'transaction_date_nepali' => $fiscalYear->from_date_nepali,
            'is_opening_balance' => 1,
            'status' => 1,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ];

        $transactionDetails[] = [
            'transaction_id' => $transaction->id,
            'account_id' => 1, 
            'debit' => $balanceType === 'CR' ? $amount : 0,
            'credit' => $balanceType === 'DR' ? $amount : 0,
            'fiscal_year_id' => $fiscalYear->id,
            'transaction_date_eng' => $fiscalYear->from_date,
            'transaction_date_nepali' => $fiscalYear->from_date_nepali,
            'is_opening_balance' => 1,
            'status' => 1,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ];

        TransactionDetail::insert($transactionDetails);
    }

    public function edit(Account $account)
    {
        return response()->json($account);
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:accounts,code,' . $account->id,
            'expense_header_no' => 'nullable|integer',
            'status' => 'required|in:0,1',
            'opening_balance' => 'required|numeric|min:0',
            'balance_type' => 'required|in:DR,CR',
        ]);

        DB::beginTransaction();

        try {
            $openingBalanceTransactions = Transaction::where('is_opening_balance', 1)
                ->whereHas('transactionDetails', function($query) use ($account) {
                    $query->where('account_id', $account->id);
                })
                ->get();
            foreach ($openingBalanceTransactions as $transaction) {
                TransactionDetail::where('transaction_id', $transaction->id)->delete();
                $transaction->delete();
            }

            $account->update([
                'name' => $request->name,
                'code' => $request->code,
                'expense_header_no' => $request->expense_header_no,
                'status' => $request->status,
                'opening_balance' => $request->opening_balance,
                'balance_type' => $request->balance_type,
                'updated_by' => Auth::id(),
            ]);

            $this->saveOpeningBalance($account);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Account $account)
    {
        DB::beginTransaction();

        try {
            $hasTransactions = TransactionDetail::where('account_id', $account->id)
                ->whereHas('transaction', function($query) {
                    $query->where('is_opening_balance', 0);
                })
                ->exists();

            if ($hasTransactions) {
                throw new \Exception('Cannot delete account with existing transactions.');
            }

            $openingBalanceTransactions = Transaction::where('is_opening_balance', 1)
                ->whereHas('transactionDetails', function($query) use ($account) {
                    $query->where('account_id', $account->id);
                })
                ->get();

            foreach ($openingBalanceTransactions as $transaction) {
                TransactionDetail::where('transaction_id', $transaction->id)->delete();
                $transaction->delete();
            }

            $account->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting account: ' . $e->getMessage()
            ], 500);
        }
    }
}