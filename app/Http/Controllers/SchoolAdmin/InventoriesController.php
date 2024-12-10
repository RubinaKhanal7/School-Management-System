<?php
namespace App\Http\Controllers\SchoolAdmin;
use Alert;
use App\Models\InventoryHead;
use App\Models\Source;
use Validator;
use Carbon\Carbon;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class InventoriesController extends Controller
{
    public function index()
    {
        $inventory = Inventory::all();
        $page_title = 'Inventory Listing';
        $inventorieshead = InventoryHead::orderBy('created_at', 'desc')->paginate(10);
        $sourceshead = Source::orderBy('created_at', 'desc')->paginate(10);
        return view('backend.school_admin.inventory.index', compact('page_title', 'inventorieshead','sourceshead','inventory'));
    }
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'inventory_head_id' => 'required|exists:inventory_head,id',
            'name' => 'required|string',
            'condition' => 'required|string',
            'costprice' => 'required|string',
            'tax' => 'nullable|string',
            'specs_details' => 'nullable|string',
            'guess_life' => 'nullable|string',
            'sources_id' => 'required|exists:sources,id',
            'tax_free_amount' => 'nullable|string',
            'tax_free_details' => 'nullable|string',
            'depreciation_percentage' => 'nullable|string',
            'other_details' => 'nullable|string',
            'land_area' => 'nullable|string',
            'land_type' => 'nullable|string',
            'land_costprice' => 'nullable|string',
            'land_owner_certificate_no' => 'nullable|string',
            'land_location' => 'nullable|string',
            'land_kitta_no' => 'nullable|string',
            'if_donation' => 'nullable|boolean',
            'land_market_value' => 'nullable|string',
            'if_physical_structure_there' => 'nullable|boolean',
            'physical_structure_detail' => 'nullable|string',
        ]);
        if ($validatedData->fails()) {
            return back()->withToastError($validatedData->messages()->all()[0])->withInput();
        }
        try {
            $inventoryData = $request->all();
            $inventoryData['school_id'] = Auth::user()->school_id;
            $savedData = Inventory::create($inventoryData);
            return redirect()->back()->withToastSuccess('Inventory Saved Successfully!');
        } catch (\Exception $e) {
            return back()->withToastError($e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $inventory = Inventory::with(['inventoryHead', 'source'])
                ->findOrFail($id);
            return response()->json($inventory);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   
    public function update(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'inventory_head_id' => 'required|exists:inventory_head,id',
            'name' => 'required|string',
            'condition' => 'required|string',
            'costprice' => 'required|string',
            'tax' => 'nullable|string',
            'specs_details' => 'nullable|string',
            'guess_life' => 'nullable|string',
            'sources_id' => 'required|exists:sources,id',
            'tax_free_amount' => 'nullable|string',
            'tax_free_details' => 'nullable|string',
            'depreciation_percentage' => 'nullable|string',
            'other_details' => 'nullable|string',
            'land_area' => 'nullable|string',
            'land_type' => 'nullable|string',
            'land_costprice' => 'nullable|string',
            'land_owner_certificate_no' => 'nullable|string',
            'land_location' => 'nullable|string',
            'land_kitta_no' => 'nullable|string',
            'if_donation' => 'nullable|boolean',
            'land_market_value' => 'nullable|string',
            'if_physical_structure_there' => 'nullable|boolean',
            'physical_structure_detail' => 'nullable|string',
        ]);
        if ($validatedData->fails()) {
            return back()->withToastError($validatedData->messages()->all()[0])->withInput();
        }
        try {
            $inventory = Inventory::findOrFail($id);
            $inventory->update($request->all());
            return redirect()->route('admin.inventories.index')->withToastSuccess('Inventory Updated Successfully!');
        } catch (\Exception $e) {
            return back()->withToastError($e->getMessage());
        }
    }
    public function getAllInventories(Request $request)
    {
        $inventories = $this->getForDataTable($request->all());
        $sources = $this->getForDataTable($request->all());
        return Datatables::of($inventories)
            ->escapeColumns([])
            ->addColumn('inventory_head_id', function ($inventory) {
                return $inventory->inventoryHead->name;
            })
            ->addColumn('sources_id', function ($inventory) {
                return $inventory->source ? $inventory->source->source_title : 'N/A';
            })
            ->addColumn('name', function ($inventory) {
                return $inventory->name;
            })
            ->addColumn('condition', function ($inventory) {
                return $inventory->condition;
            })
            ->addColumn('costprice', function ($inventory) {
                return $inventory->costprice;
            })
            ->addColumn('created_at', function ($inventory) {
                return $inventory->created_at->diffForHumans();
            })
            ->addColumn('actions', function ($inventory) {
                return view('backend.school_admin.inventory.partials.controller_action', ['inventory' => $inventory])->render();
            })
            ->make(true);
    }
    public function getForDataTable($request)
    {
        $schoolId = session('school_id');
        $dataTableQuery = Inventory::where('school_id', $schoolId)
        ->where(function ($query) use ($request) {
            if (isset($request->id)) {
                $query->where('id', $request->id);
            }
        })
            ->get();
        return $dataTableQuery;
    }

    public function getAllSources()
    {
        $sources = Source::all(['id', 'source_title']);
        return response()->json($sources);
    }
   
    public function destroy($id)
    {
        try {
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();
            return redirect()->back()->withToastSuccess('Inventory Deleted Successfully!');
        } catch (\Exception $e) {
            return back()->withToastError('Failed to delete the inventory: ' . $e->getMessage());
        }
    }

}

