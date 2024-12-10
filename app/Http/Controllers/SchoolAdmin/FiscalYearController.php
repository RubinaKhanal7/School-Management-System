<?php

namespace App\Http\Controllers\SchoolAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use Yajra\DataTables\Facades\DataTables;

class FiscalYearController extends Controller
{
    /**
     * Display a listing of the fiscal years.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $fiscalYears = FiscalYear::select(['id', 'name', 'from_date_nepali', 'to_date_nepali', 'status']);
    
            return DataTables::of($fiscalYears)
                ->addColumn('from_date_nepali', function ($row) {
                    return \Carbon\Carbon::parse($row->from_date_nepali)->toDateString();
                })
                ->addColumn('to_date_nepali', function ($row) {
                    return \Carbon\Carbon::parse($row->to_date_nepali)->toDateString(); 
                })
                ->addColumn('status', function ($row) {
                    $statusClass = $row->status == 1 ? 'btn-success' : 'btn-danger';
                    $statusText = $row->status == 1 ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editButton = '<button class="btn btn-primary btn-sm edit-btn" data-id="' . $row->id . '" data-name="' . $row->name . '" data-from-date="' . $row->from_date_nepali . '" data-to-date="' . $row->to_date_nepali . '" data-status="' . $row->status . '">Edit</button>';
                    $deleteButton = '<form action="' . route('admin.fiscal-years.destroy', $row->id) . '" method="POST" style="display:inline;">
                                        ' . csrf_field() . method_field('DELETE') . '
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')">Delete</button>
                                        </form>';
                    return $editButton . ' ' . $deleteButton;
                })
                ->rawColumns(['status', 'actions']) 
                ->make(true);
        }
    
        return view('backend.school_admin.fiscal_year.index');
    }
    

    public function edit(FiscalYear $fiscalYear)
    {
        return response()->json($fiscalYear);
    }

    public function update(Request $request, FiscalYear $fiscalYear)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:fiscal_years,name,' . $fiscalYear->id,
            'from_date' => 'required|date',
            'to_date' => 'required|date|after:from_date',
            'from_date_nepali' => 'required|string',
            'to_date_nepali' => 'required|string',
            'status' => 'boolean',
        ]);

        $fiscalYear->update([
            'name' => $validated['name'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'from_date_nepali' => $validated['from_date_nepali'],
            'to_date_nepali' => $validated['to_date_nepali'],
            'status' => $validated['status'] ?? 0,
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Fiscal year updated successfully']);
    }

    /**
     * Remove the specified fiscal year from storage.
     *
     * @param  \App\Models\FiscalYear  $fiscalYear
     * @return \Illuminate\Http\Response
     */
    public function destroy(FiscalYear $fiscalYear)
    {
        $fiscalYear->delete();
        return redirect()->route('admin.fiscal-years.index')->with('success', 'Fiscal year deleted successfully.');
    }

    /**
     * Set the specified fiscal year as active and deactivate others.
     *
     * @param  \App\Models\FiscalYear  $fiscalYear
     * @return \Illuminate\Http\Response
     */
    public function setActive(FiscalYear $fiscalYear)
    {
        FiscalYear::where('status', 1)->update(['status' => 0]);
        $fiscalYear->update(['status' => 1]);

        return redirect()->route('admin.fiscal-years.index')->with('success', 'Fiscal year set as active.');
    }
}
