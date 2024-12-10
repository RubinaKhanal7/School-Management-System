<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoucherType;
use Yajra\DataTables\DataTables;

class VoucherTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $voucherTypes = VoucherType::select(['id', 'name', 'code', 'status']);

            return DataTables::of($voucherTypes)
            ->addColumn('status', function ($row) {
                $statusClass = $row->status == 1 ? 'btn-success' : 'btn-danger';
                $statusText = $row->status == 1 ? 'Active' : 'Inactive';
                return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
            })
                ->addColumn('actions', function ($row) {
                    $editButton = '<button data-id="' . $row->id . '" class="btn btn-warning btn-sm edit-btn">Edit</button>';
                    $deleteButton = '<form action="' . route('admin.voucher_types.destroy', $row->id) . '" method="POST" style="display:inline;">
                                        ' . csrf_field() . method_field('DELETE') . '
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')">Delete</button>
                                    </form>';
                
                    return $editButton . ' ' . $deleteButton;
                })
                
                ->rawColumns(['status', 'actions']) 
                ->make(true);
        }
        return view('backend.school_admin.voucher_type.index');
    }

    /**
     * Show the form for editing the specified voucher type.
     */
    public function edit(VoucherType $voucherType)
    {
        if (request()->ajax()) {
            return response()->json($voucherType);
        }
        return view('voucher_types.edit', compact('voucherType'));
    }
    

    /**
     * Update the specified voucher type in storage.
     */
    public function update(Request $request, VoucherType $voucherType)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:10|unique:voucher_types,code,' . $voucherType->id,
        'status' => 'required|boolean',
    ]);

    $voucherType->update([
        'name' => $request->name,
        'code' => $request->code,
        'status' => $request->status,
        'updated_by' => auth()->id(),
    ]);

    return response()->json(['success' => 'Voucher Type updated successfully']);
}

    /**
     * Remove the specified voucher type from storage.
     */
    public function destroy(VoucherType $voucherType)
    {
        $voucherType->delete();

        return redirect()->route('admin.voucher_types.index')->with('success', 'Voucher Type deleted successfully');
    }
}
