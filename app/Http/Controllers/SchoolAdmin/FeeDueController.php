<?php

namespace App\Http\Controllers\SchoolAdmin;

use Alert;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Classg;
use App\Models\FeeDue;
use App\Models\Section;
use App\Models\Student;
use App\Models\FeeGroup;
use Illuminate\Http\Request;
use App\Models\StudentSession;
use App\Models\FeeGroupType;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeeDueController extends Controller
{
    public function index(Request $request)
    {
        $page_title = 'Fee Due Listing';
    
        $feegroup = FeeGroup::orderBy('created_at', 'desc')->paginate(10);
        $schoolId = auth()->user()->school_id;
        $classmanagement = Classg::where('school_id', $schoolId)->get();
        $selectedClass = $request->input('class_id');
        $sections = $selectedClass ? Section::whereHas('classSections', function($query) use ($selectedClass) {
            $query->where('class_id', $selectedClass);
        })->get() : collect();
        
        return view('backend.school_admin.fee_due.index', compact('page_title', 'feegroup', 'classmanagement', 'sections'));
    }

  public function getAllSearchData(Request $request)
{
    $classId = $request->input('classId');
    $sectionId = $request->input('sectionId');
    $feeGroupsID = $request->input('feeGroupsID');
    $academicSessionId = session('academic_session_id');
    $schoolId = session('school_id');
    $students = $this->getStudentsWithFeeData($classId, $sectionId, $feeGroupsID, $academicSessionId, $schoolId);
    $this->populateFeeDueTable($students, $feeGroupsID, $academicSessionId, $schoolId);

    return $students;
}

private function populateFeeDueTable($studentIds, $feeGroupId, $academicSessionId, $schoolId)
{
    $feeGroupTypes = FeeGroupType::where('fee_group_id', $feeGroupId)
        ->where('academic_session_id', $academicSessionId)
        ->where('school_id', $schoolId)
        ->get();

    foreach ($studentIds as $studentId) {
        // Fetch related data for student session
        $studentSession = StudentSession::find($studentId);
        if (!$studentSession) {
            Log::error("Student session not found for ID: $studentId");
            continue; // Skip this student ID if session data is missing
        }

        foreach ($feeGroupTypes as $feeGroupType) {
            try {
                FeeDue::create([
                    'fee_groups_id' => $feeGroupId,
                    'class_id' => $studentSession->class_id,
                    'section_id' => $studentSession->section_id,
                    'student_session_id' => $studentSession->id,
                ]);
            } catch (\Exception $e) {
                Log::error("Error creating fee due record: " . $e->getMessage(), [
                    'student_id' => $studentId,
                    'fee_group_id' => $feeGroupId,
                    'fee_group_type_id' => $feeGroupType->id,
                ]);
            }
        }
    }
}
}
