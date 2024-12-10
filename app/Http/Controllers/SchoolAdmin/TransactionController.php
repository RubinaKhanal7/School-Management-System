<?php
namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\VoucherType;
use App\Models\FiscalYear;
use App\Models\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        $voucherTypes = VoucherType::all();
        $fiscalYears = FiscalYear::where('status', 1)->get();
        $accounts = Account::where('status', 1)->get();
        
        return view('backend.school_admin.transactions.index', compact('voucherTypes', 'fiscalYears', 'accounts'));
    }


    public function getData(Request $request)
    {
        $query = TransactionDetail::with(['transaction.voucherType', 'account', 'transaction.createdBy'])
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereHas('transaction', function($q) {
                $q->where('created_by', Auth::id())
                  ->where('is_opening_balance', 0);
            })
            ->select([
                'transactions.voucher_no',
                'transactions.transaction_date_eng',
                'transactions.transaction_date_nepali',
                'transactions.voucher_type_id',
                DB::raw('GROUP_CONCAT(DISTINCT accounts.name) as account_names'),
                DB::raw('SUM(transaction_details.debit) as total_debit'),
                DB::raw('SUM(transaction_details.credit) as total_credit'),
                DB::raw('MIN(transactions.id) as transaction_id')
            ])
            ->join('accounts', 'transaction_details.account_id', '=', 'accounts.id')
            ->groupBy([
                'transactions.voucher_no',
                'transactions.transaction_date_eng',
                'transactions.transaction_date_nepali',
                'transactions.voucher_type_id'
            ]);
    
        return datatables()->of($query)
            ->addColumn('voucher_type', function ($detail) {
                return VoucherType::find($detail->voucher_type_id)->name ?? '';
            })
            ->addColumn('account_name', function ($detail) {
                return implode(', ', array_unique(explode(',', $detail->account_names)));
            })
            ->addColumn('actions', function ($detail) {
                return '<button data-voucher="' . $detail->voucher_no . '" class="btn btn-primary btn-sm edit-transaction">Edit</button>';
            })
            ->addColumn('actions', function ($detail) {
                return '<button data-voucher="' . $detail->voucher_no . '" class="btn btn-primary btn-sm edit-transaction">Edit</button> ' .
                       '<a href="' . route('admin.transactions.print', $detail->voucher_no) . '" class="btn btn-info btn-sm" target="_blank">Print</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date_eng' => 'required|date',
            'transaction_date_nepali' => 'required|string',
            'voucher_type_id' => 'required|exists:voucher_types,id',
            'description' => 'nullable|string',
            'accounts' => 'required|array|min:2',
            'accounts.*' => 'required|exists:accounts,id',
            'types' => 'required|array|min:2',
            'types.*' => 'required|in:dr,cr',
            'amounts' => 'required|array|min:2',
            'amounts.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($request->types as $key => $type) {
                if ($type === 'dr') {
                    $totalDebit += $request->amounts[$key];
                } else {
                    $totalCredit += $request->amounts[$key];
                }
            }

            if ($totalDebit !== $totalCredit) {
                throw new \Exception('Total debit must equal total credit');
            }

            $transaction = Transaction::create([
                'voucher_no' => $this->generateVoucherNumber(),
                'description' => $validated['description'],
                'voucher_type_id' => $validated['voucher_type_id'],
                'status' => 1,
                'transaction_date_eng' => $validated['transaction_date_eng'],
                'transaction_date_nepali' => $validated['transaction_date_nepali'],
                'fiscal_year_id' => FiscalYear::where('status', 1)->first()->id,
                'transaction_amount' => $totalDebit, 
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ($request->accounts as $key => $accountId) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $accountId,
                    'debit' => $request->types[$key] === 'dr' ? $request->amounts[$key] : 0,
                    'credit' => $request->types[$key] === 'cr' ? $request->amounts[$key] : 0,
                    'fiscal_year_id' => $transaction->fiscal_year_id,
                    'transaction_date_eng' => $transaction->transaction_date_eng,
                    'transaction_date_nepali' => $transaction->transaction_date_nepali,
                    'status' => 1,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction created successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function edit($voucherNo)
    {
        $transaction = Transaction::where('voucher_no', $voucherNo)
            ->with(['voucherType', 'transactionDetails.account'])
            ->firstOrFail();

        $entries = $transaction->transactionDetails->map(function ($detail) {
            return [
                'account_id' => $detail->account_id,
                'debit' => $detail->debit,
                'credit' => $detail->credit,
                'type' => $detail->debit > 0 ? 'dr' : 'cr',
                'amount' => $detail->debit > 0 ? $detail->debit : $detail->credit
            ];
        });
    
        return response()->json([
            'transaction' => $transaction,
            'entries' => $entries
        ]);
    }

    public function update(Request $request, $voucherNo)
    {
        $validated = $request->validate([
            'transaction_date_eng' => 'required|date',
            'transaction_date_nepali' => 'required|string',
            'voucher_type_id' => 'required|exists:voucher_types,id',
            'description' => 'nullable|string',
            'accounts' => 'required|array|min:2',
            'accounts.*' => 'required|exists:accounts,id',
            'types' => 'required|array|min:2',
            'types.*' => 'required|in:dr,cr',
            'amounts' => 'required|array|min:2',
            'amounts.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($request->types as $key => $type) {
                if ($type === 'dr') {
                    $totalDebit += $request->amounts[$key];
                } else {
                    $totalCredit += $request->amounts[$key];
                }
            }

            if ($totalDebit !== $totalCredit) {
                throw new \Exception('Total debit must equal total credit');
            }

            $transaction = Transaction::where('voucher_no', $voucherNo)->firstOrFail();
            $transaction->update([
                'description' => $validated['description'],
                'voucher_type_id' => $validated['voucher_type_id'],
                'transaction_date_eng' => $validated['transaction_date_eng'],
                'transaction_date_nepali' => $validated['transaction_date_nepali'],
                'transaction_amount' => $totalDebit,
                'updated_by' => Auth::id(),
            ]);

            TransactionDetail::where('transaction_id', $transaction->id)->delete();

            foreach ($request->accounts as $key => $accountId) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $accountId,
                    'debit' => $request->types[$key] === 'dr' ? $request->amounts[$key] : 0,
                    'credit' => $request->types[$key] === 'cr' ? $request->amounts[$key] : 0,
                    'fiscal_year_id' => $transaction->fiscal_year_id,
                    'transaction_date_eng' => $transaction->transaction_date_eng,
                    'transaction_date_nepali' => $transaction->transaction_date_nepali,
                    'status' => 1,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function generateVoucherNumber()
    {
        $lastVoucher = Transaction::latest()->first();
        return $lastVoucher ? $lastVoucher->voucher_no + 1 : 1;
    }

    public function print($voucherNo)
    {
        $transaction = Transaction::where('voucher_no', $voucherNo)
            ->with(['voucherType', 'transactionDetails.account', 'createdBy'])
            ->firstOrFail();
        $schoolInfo = Auth::user()->school;  
        $schoolInfo = \App\Models\School::find(Auth::user()->school_id);
    
        $totalDebit = $transaction->transactionDetails->sum('debit');
        $totalCredit = $transaction->transactionDetails->sum('credit');
    
        return view('backend.school_admin.transactions.print', 
            compact('transaction', 'totalDebit', 'totalCredit', 'schoolInfo'));
    }


    public function exportExcel()
    {
        $transactions = Transaction::with(['voucherType', 'transactionDetails.account', 'createdBy'])
            ->where('created_by', Auth::id())
            ->where('is_opening_balance', 0)
            ->get();

        $createdByName = $transactions->first()->createdBy->f_name ?? Auth::user()->f_name;
        
        $schoolInfo = \App\Models\School::find(Auth::user()->school_id);
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transactions');

        $sheet->setCellValue('A1', $createdByName);
        $sheet->setCellValue('A2', $schoolInfo->address . ' | ' . $schoolInfo->phone . ' | ' . $schoolInfo->email);

        $sheet->setCellValue('A4', 'Voucher No');
        $sheet->setCellValue('B4', 'Date');
        $sheet->setCellValue('C4', 'Voucher Type');
        $sheet->setCellValue('D4', 'Account');
        $sheet->setCellValue('E4', 'Description');
        $sheet->setCellValue('F4', 'Debit');
        $sheet->setCellValue('G4', 'Credit');
    
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        
        // Data rows
        $row = 5;
        foreach ($transactions as $transaction) {
            foreach ($transaction->transactionDetails as $detail) {
                $sheet->setCellValue('A' . $row, $transaction->voucher_no);
                $sheet->setCellValue('B' . $row, $transaction->transaction_date_nepali);
                $sheet->setCellValue('C' . $row, $transaction->voucherType->name);
                $sheet->setCellValue('D' . $row, $detail->account->name);
                $sheet->setCellValue('E' . $row, $transaction->description);
                $sheet->setCellValue('F' . $row, number_format($detail->debit, 2));
                $sheet->setCellValue('G' . $row, number_format($detail->credit, 2));
                $row++;
            }
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'transactions_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
    
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}