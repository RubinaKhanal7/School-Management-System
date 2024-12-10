<?php

namespace App\Http\Controllers\MunicipalityAdmin;

use App\Http\Controllers\Controller;
use App\Models\Source;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SourceController extends Controller
{
 /**
     * Display a listing of the sources
     */
    public function index()
    {
        $page_title = "Inventory Source";
        return view('backend.municipality_admin.sources.index', compact('page_title'));
    }

    /**
     * Get sources data for DataTables
     */
    public function get(Request $request)
    {
        if ($request->ajax()) {
            $sources = Source::select(['id', 'source_title', 'source_description']);
            
            return DataTables::of($sources)
                ->addIndexColumn()
                ->addColumn('actions', function ($source) {
                    $editBtn = '
                        <a href="#" class="btn btn-outline-primary btn-sm mx-1 edit-source"
                            data-id="' . $source->id . '"
                            data-title="' . htmlspecialchars($source->source_title) . '"
                            data-description="' . htmlspecialchars($source->source_description) . '"
                            data-toggle="tooltip" data-placement="top" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                    ';
    
                    $deleteBtn = '
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#delete' . $source->id . '" data-toggle="tooltip" data-placement="top" title="Delete">
                            <i class="far fa-trash-alt"></i>
                        </button>
                 ';
    
                    $deleteModal = '
                    <div class="modal fade" id="delete' . $source->id . '" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="' . route('admin.sources.destroy', $source->id) . '" accept-charset="UTF-8">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <div class="modal-body">
                                        <p>Are you sure to delete <span id="underscore" class="must"> ' . $source->source_title . ' </span>?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">No</button>
                                        <button type="submit" class="btn btn-danger">Yes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>';
    
                    return $editBtn . ' ' . $deleteBtn . $deleteModal;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    
        return abort(404);
    }

    /**
     * Store a newly created source
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_title' => 'required|string|max:255',
            'source_description' => 'required|string',
        ]);

        Source::create([
            'source_title' => $validated['source_title'],
            'source_description' => $validated['source_description'],
        ]);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Source created successfully.');
    }

    /**
     * Update the specified source
     */
    public function update(Request $request, Source $source)
    {
        $validated = $request->validate([
            'source_title' => 'required|string|max:255',
            'source_description' => 'required|string',
        ]);

        $source->update([
            'source_title' => $validated['source_title'],
            'source_description' => $validated['source_description'],
        ]);

        return redirect()->route('admin.sources.index')
            ->with('success', 'Source updated successfully.');
    }

    /**
     * Remove the specified source
     */
    public function destroy(Source $source)
    {
        $source->delete();

        return redirect()->route('admin.sources.index')
            ->with('success', 'Source deleted successfully.');
    }
}
