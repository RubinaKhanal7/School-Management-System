<?php

namespace App\Http\Controllers\SchoolAdmin;

use Validator;
use App\Models\Classg;
use App\Models\FeeType;
use App\Models\FeeGroup;
use App\Models\Section;
use App\Models\StudentSession;
use App\Models\FeeGroupType;
use App\Models\FeeDue;
use App\Models\FeeCollection;
use Illuminate\Http\Request;
use App\Models\AcademicSession;
use Illuminate\Validation\Rule;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class FeeGroupTypesController extends Controller
{
    //
    public function index(Request $request)
    {
        $results = DB::table('fee_groups')
            ->join('fee_groups_types', 'fee_groups.id', '=', 'fee_groups_types.fee_group_id')
            ->join('fee_types', 'fee_types.id', '=', 'fee_groups_types.fee_type_id')
            ->select([
                'fee_groups_types.fee_group_id as fee_group_id',
                'fee_groups_types.id',
                'fee_groups.name as name',
                'fee_types.name as fee_group_type_name',
                'fee_groups_types.fee_type_id',
                'fee_groups_types.academic_session_id',
                'fee_groups_types.school_id',
                'fee_groups_types.amount',
                'fee_groups_types.is_active',
                'fee_groups_types.created_at',
                'fee_groups_types.updated_at',
            ])
            ->get()
            ->groupBy('name')
            ->toArray();
            // dd($result);

        $feeTypes = FeeType::all();
        $feeGroups = FeeGroup::all();
        $academic_session = AcademicSession::all();
        $page_title = 'Fee Group Type Listing';

        $fee_group_types = FeeGroupType::with('feeType')->get();
        $formattedData = $fee_group_types->map(function ($feeGroup) {
            return [
                'id' => $feeGroup->id,
                'fee_group_name' => $feeGroup->feeGroup->name,
                'amount' => $feeGroup->amount,
                'fee_type' => $feeGroup->feeType->toArray(),
            ];
        });

        return view('backend.school_admin.fee_group_type.index', compact('page_title','feeTypes','feeGroups','academic_session','results'));
    }

public function store(Request $request)
{
    $validatedData = Validator::make($request->all(), [
        'amount' => 'required',
        'is_active' => 'required|boolean',
        'fee_type_id' => 'required',
        'fee_group_id' => [
            'required',
            Rule::unique('fee_groups_types')->where(function ($query) use ($request) {
                return $query->where('fee_type_id', $request->input('fee_type_id'))
                             ->where('academic_session_id', $request->input('academic_session_id'))
                             ->where('school_id', 1); // Adjust the condition based on your needs
            }),
        ],
        'academic_session_id' => 'required',
    ]);

    if ($validatedData->fails()) {
        return back()->withToastError($validatedData->messages()->all()[0])->withInput();
    }

    try {
        $feeGroupType = $request->all();
        $feeGroupType['school_id'] = 1;

        $savedData = FeeGroupType::create($feeGroupType);
        return redirect()->back()->withToastSuccess('Fee Group Type Saved Successfully!');
    } catch (\Exception $e) {
        return back()->withToastError($e->getMessage());
    }
}


    public function edit(string $id)
    {

        $feeGroupType = FeeGroupType::find($id);
        $page_title = 'Fee Group Type';
        return view('backend.school_admin.fee_group_type.index', compact('feeGroupType','page_title'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'amount' => 'required',
            'is_active' => 'required|boolean',
            'fee_type_id'=> 'required',
            'fee_group_id'=> 'required',
            'academic_session_id'=> 'required',

        ]);

        if ($validatedData->fails()) {
            return back()->withToastError($validatedData->messages()->all()[0])->withInput();
        }

        $feeGroupType = FeeGroupType::findOrFail($id);

        if (!$feeGroupType) {
            return back()->withToastError('Fee Group Type not found.');
        }

        try {
            $data = $request->all();
            $data['school_id'] = 1;
            $feeGroupType->update($data);

            return redirect()->back()->withToastSuccess('Fee Group Type Updated Successfully!');
        } catch (\Exception $e) {
            return back()->withToastError($e->getMessage())->withInput();
        }
        return back()->withToastError('Cannot Update Fee Group Type. Please try again')->withInput();
    }



    public function destroy($id)
    {
        $feeGroupType = FeeGroupType::find($id);

        try {
            $feeGroupType->delete();
            return redirect()->back()->withToastSuccess('Fee Group Type has been Successfully Deleted!');
        } catch (\Exception $e) {
            return back()->withToastError($e->getMessage());
        }
        return back()->withToastError('Something went wrong. Please try again');
    }

    public function assignStudent(Request $request, $fee_group_id)
    {
        $page_title = "Assign Student";
        $schoolId = auth()->user()->school_id;
        
        $feeGroup = FeeGroup::with(['feeGroupTypes' => function($query) {
            $query->with('feeType');
        }])->findOrFail($fee_group_id);
        
        $classes = Classg::where('school_id', $schoolId)->get();
        $selectedClass = $request->input('class_id');
        $sections = $selectedClass ? Section::whereHas('classSections', function($query) use ($selectedClass) {
            $query->where('class_id', $selectedClass);
        })->get() : collect();
        
        return view('backend.school_admin.fee_group_type.assign', [
            'classes' => $classes,
            'sections' => $sections,
            'feeGroup' => $feeGroup,
            'page_title' => $page_title,
        ]);
    }
    
    public function storeAssignment(Request $request)
{
    $validated = $request->validate([
        'student_ids' => 'required|array',
        'student_ids.*' => 'exists:student_sessions,id',
        'fee_group_id' => 'required|exists:fee_groups,id'
    ]);

    try {
        DB::beginTransaction();
        $feeGroup = FeeGroup::findOrFail($request->fee_group_id);
        $feeGroup->studentSessions()->syncWithoutDetaching($request->student_ids);

        // Populate the fee_dues table for the assigned students
        $this->populateFeeDueTable($request->student_ids, $feeGroup->id, session('academic_session_id'), auth()->user()->school_id);

        DB::commit();
        return redirect()->back()->withToastSuccess('Students assigned to fee group successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error in fee group assignment: ' . $e->getMessage(), [
            'exception' => $e,
            'request_data' => $request->all()
        ]);

        return back()
            ->withToastError('Failed to assign students: ' . $e->getMessage())
            ->withInput();
    }
}

private function removeFeeDuesForPaidFees($studentIds, $feeGroupId, $schoolId, $academicSessionId)
{
    $paidFeeRecords = FeeCollection::whereIn('student_session_id', $studentIds)
        ->where('fee_groups_types_id', $feeGroupId)
        ->where('school_id', $schoolId)
        ->where('academic_session_id', $academicSessionId)
        ->get();

    foreach ($paidFeeRecords as $paidFee) {
        FeeDue::where('fee_groups_id', $paidFee->fee_groups_types_id)
            ->where('student_session_id', $paidFee->student_session_id)
            ->delete();
    }
}
}
