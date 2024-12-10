<?php


namespace App\Http\Controllers\SchoolAdmin;


use App\Models\ExamSchedule;
use App\Models\ExamStudent;
use App\Models\Subject;
use Validator;
use App\Models\Classg;
use App\Models\ExamResult;
use App\Models\StudentSession;
use App\Models\Examination;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use App\Http\Services\ExamResultService;
use App\Imports\CombinedImport;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\ExamResultExport;
use App\Models\Section;
use Illuminate\Support\Facades\Log;


class GenerateResultController extends Controller
{
    protected $examResultService;
    public function __construct(ExamResultService $examResultService)
    {
        $this->examResultService = $examResultService;
    }
    //
    public function index(string $id)
    {
       
        $examinations = Examination::find($id);
        $page_title = "Generate Result Of " . $examinations->exam;
        $studentSessions = $this->generateExamResult($examinations);
        return view('backend.school_admin.exam_result.index', compact('page_title', 'examinations', 'studentSessions'));
    }


    public function create(string $id)
    {
        Log::info('Entering create method with id: ' . $id);
   
        $examinations = Examination::with('subjectByRoutine')->findOrFail($id);
        $page_title = "Generate Result Of " . $examinations->exam;
       
        // Get classes with their sections for the current school
        $classes = Classg::where('school_id', session('school_id'))
            ->with(['sections' => function($query) {
                $query->orderBy('section_name');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
       
        $studentSessions = collect([]);
        $restructuredResults = [];
   
        // If class and section are selected, fetch the results
        if (request('class_id') && request('section_id')) {
            $studentSessions = StudentSession::with(['student.user', 'classg', 'section'])
                ->whereHas('classg', function($query) {
                    $query->where('school_id', session('school_id'));
                })
                ->where('class_id', request('class_id'))
                ->where('section_id', request('section_id'))
                ->whereHas('examResults', function ($query) use ($id) {
                    $query->whereHas('examSchedule', function ($q) use ($id) {
                        $q->where('examination_id', $id);
                    });
                })
                ->get();
   
            // Get exam results for these students
            $examResults = ExamResult::with(['examSchedule.subject'])
                ->whereHas('examSchedule', function ($query) use ($id) {
                    $query->where('examination_id', $id);
                })
                ->whereIn('student_session_id', $studentSessions->pluck('id'))
                ->get();
   
            // Restructure results
            foreach ($examResults as $result) {
                $studentSessionId = $result->student_session_id;
                $subjectId = $result->examSchedule->subject_id;
                $restructuredResults[$studentSessionId][$subjectId] = $result;
            }
        }
   
        if ($examinations->exam_type == "terminal") {
            return view('backend.school_admin.exam_result.index', compact(
                'page_title',
                'examinations',
                'studentSessions',
                'restructuredResults',
                'classes'
            ));
        } else {
            return $this->createFinalExamResult($id, $page_title, $examinations, $studentSessions, $restructuredResults);
        }
    }


    public function getSectionsByClass(Request $request)
    {
        // Validate request
        $request->validate([
            'class_id' => 'required|exists:classes,id'
        ]);


        // Get sections for the selected class using pivot table
        $sections = Section::join('class_sections', 'sections.id', '=', 'class_sections.section_id')
                         ->join('classes', 'class_sections.class_id', '=', 'classes.id')
                         ->where('classes.school_id', session('school_id'))
                         ->where('class_sections.class_id', $request->class_id)
                         ->select('sections.id', 'sections.section_name')
                         ->orderBy('sections.section_name')
                         ->get();
           
        return response()->json($sections);
    }


    public function getStudentsByClassSection(Request $request)
    {
        // Validate request
        $request->validate([
            'class_id' => 'required|exists:classgs,id',
            'section_id' => 'required|exists:sections,id',
            'examination_id' => 'required|exists:examinations,id'
        ]);


        $classId = $request->class_id;
        $sectionId = $request->section_id;
        $examinationId = $request->examination_id;


        // Verify the class-section combination exists in pivot table
        $validCombination = DB::table('class_sections')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();


        if (!$validCombination) {
            return response()->json(['error' => 'Invalid class-section combination'], 400);
        }


        // Get students with their results
        $studentSessions = StudentSession::with(['student.user', 'classg', 'section'])
            ->whereHas('classg', function($query) {
                $query->where('school_id', session('school_id'));
            })
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereHas('examResults', function ($query) use ($examinationId) {
                $query->whereHas('examSchedule', function ($q) use ($examinationId) {
                    $q->where('examination_id', $examinationId);
                });
            })
            ->get();


        // Get exam results for these students
        $examResults = ExamResult::with(['examSchedule.subject'])
            ->whereHas('examSchedule', function ($query) use ($examinationId) {
                $query->where('examination_id', $examinationId);
            })
            ->whereIn('student_session_id', $studentSessions->pluck('id'))
            ->get();


        // Restructure results for easier frontend handling
        $restructuredResults = [];
        foreach ($examResults as $result) {
            $studentSessionId = $result->student_session_id;
            $subjectId = $result->examSchedule->subject_id;
            $restructuredResults[$studentSessionId][$subjectId] = $result;
        }


        return response()->json([
            'students' => $studentSessions,
            'results' => $restructuredResults
        ]);
    }


    public function createFinalExamResult(string $id, $page_title, $examinations, $studentSessions, $restructuredResults)
    {
      // For terminal examinations
        $firstTermResults = $this->getTermResults('terminal');
        // $secondTermResults = $this->getTermResults('terminal');


        Log::info('First term results count: ' . count($firstTermResults));
        // Log::info('Second term results count: ' . count($secondTermResults));
   
        return view('backend.school_admin.exam_result.final_exam_result', compact('page_title', 'examinations', 'studentSessions', 'restructuredResults', 'firstTermResults'));
    }
   
    private function getTermResults($examType)
    {
        Log::info('Fetching term results for exam type: ' . $examType);
   
        // Adjust the query to match the actual exam type value
        $termResults = ExamResult::with('examSchedule.examination')
            ->whereHas('examSchedule.examination', function ($query) use ($examType) {
                // Use the correct value "terminal" instead of "first_term" or "second_term"
                $query->where('exam_type', $examType);
            })
            ->get();
   
        Log::info('Term results fetched: ' . $termResults->count());
        Log::debug('Term results data: ' . json_encode($termResults));
   
        $restructuredTermResults = [];
        foreach ($termResults as $result) {
            $studentSessionId = $result->student_session_id;
            $subjectId = $result->examSchedule->subject_id;
            $restructuredTermResults[$studentSessionId][$subjectId] = $result;
        }
   
        Log::debug('Restructured term results data: ' . json_encode($restructuredTermResults));
   
        return $restructuredTermResults;
    }


    public function exportExamResults(string $id)
    {
        $examinations = Examination::find($id);
        $concatenatedString = str_replace(' ', '', $examinations);
        $concatenatedString = str_replace(' ', '', $examinations->exam);
        return Excel::download(new ExamResultExport($examinations), $concatenatedString . 'result.xlsx');
    }


    public function generateExamResult($examinations)
    {
        return $this->examResultService->getStudentResultsBySubject($examinations);
    }


    public function store(Request $request)
    {
       
    }


    public function edit(string $id)
    {
       
    }




    public function update(Request $request, string $id)
    {
       
    }


    public function destroy(string $id)
    {
    }


    public function getForDataTable($request)
    {
        $dataTableQuery = ExamResult::where(function ($query) use ($request) {
            if (isset ($request->id)) {
                $query->where('id', $request->id);
            }
        })
            ->get();


        return $dataTableQuery;
    }


    /**
     * Show the form for creating a new resource.
     */
    public function assignStudents(string $id)
    {
        $examinations = Examination::find($id);
        $page_title = "Store Students Marks To " . $examinations->exam;
        $classes = Classg::where('school_id', session('school_id'))
            ->orderBy('created_at', 'desc')
            ->get();


        return view('backend.school_admin.examination.results.create', compact('page_title', 'classes', 'examinations'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getRoutineDetails(Request $request)
    {
        $sectionId = $request->input('sections');
        $classId = $request->input('class_id');
        $examinationId = $request->input('examination_id');
        if ($sectionId && $classId && $examinationId) {
            $examSchedule = ExamSchedule::where('class_id', $classId)->where('section_id', $sectionId)->where('examination_id', $examinationId)->get();
            if ($examSchedule->isNotEmpty()) {
                return view('backend.school_admin.examination.results.ajax_subject', compact('examSchedule'));
            } else {
                return response()->json(['message' => 'No exam routine or schedule has been set yet!!!'], 400);
            }
        } else {
            // Handle the case where one or more parameters are missing
            return response()->json(['message' => 'Missing parameters'], 400);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function getStudentsDetails(Request $request)
    {
        $sectionId = $request->input('section_id');
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $examinationId = $request->input('examination_id');
        $examinationScheduleId = $request->input('examination_schedule_id');
        if ($sectionId && $classId && $examinationId) {
            $examStudent = $this->formService->getExamAssignStudentDetails($examinationId, $examinationScheduleId, $subjectId, $classId, $sectionId);


            // Check if any examStudents relationship is not empty
            if ($examStudent->isNotEmpty()) {
                // Iterate over each StudentSession instance
                foreach ($examStudent as $studentSession) {
                    // dd($studentSession);
                    if ($studentSession->examStudents->isNotEmpty()) {
                        return view('backend.school_admin.examination.results.ajax_student', compact('examStudent', 'examinationScheduleId', 'subjectId'));
                    } else {


                        return response()->json(['message' => 'Students has not been assigned for particular Examination!!!'], 400);
                    }
                }
            } else {
                return response()->json(['message' => 'No students found with the given search parameters!!!'], 400);
            }
        } else {
            // Handle the case where one or more parameters are missing
            return response()->json(['message' => 'Missing parameters'], 400);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getExamAssignStudents($id, $classId, $sectionId)
    {
        $students = $this->formService->getExamAssignStudents($id, $classId, $sectionId);
        return response()->json($students);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function saveStudentsMarks(Request $request)
    {
        try {
            $exam_schedule_id = $request->input('exam_schedule_id');
            $subject_id = $request->input('subject_id');
            //store the records
            foreach ($request->student_id as $key => $studentId) {
                $storeMarks = [
                    'exam_schedule_id' => $exam_schedule_id,
                    'subject_id' => $subject_id,
                    'student_session_id' => isset($request->student_session_id[$key]) ? $request->student_session_id[$key] : '',
                    'attendance' => isset($request->attendance[$key]) ? 0 : 1,
                    'marks' => isset($request->marks[$key]) ? $request->marks[$key] : 0,
                    'notes' => isset($request->notes[$key]) ? $request->notes[$key] : '',
                    'is_active' => 1
                ];


                // Update the record if it exists, otherwise create a new one
                ExamResult::updateOrCreate(
                    [
                        'exam_schedule_id' => $exam_schedule_id,
                        'student_session_id' => $request->student_session_id[$key]
                    ],
                    $storeMarks
                );
            }
            return back()->withToastSuccess('Marks successfully updated!!');
        } catch (\Exception $e) {
            return back()->withToastError('Error registering marks: ' . $e->getMessage());
        }
    }


    public function getExamResults($examinationId)
{
    // Fetch the examination details
    $examinations = Examination::find($examinationId);


    // Fetch exam results for the given examination
    $examResults = ExamResult::whereHas('examSchedule', function ($query) use ($examinations) {
        $query->where('examination_id', $examinations->id);
    })->with(['studentSession', 'examStudent', 'examSchedule', 'subject'])
    ->get();


    return $examResults;
}


public function getExamResultsData(string $id)
{
    $examinations = Examination::find($id);
    $examResults = ExamResult::whereHas('examSchedule', function ($query) use ($examinations) {
        $query->where('examination_id', $examinations->id);
    })->with(['studentSession', 'examStudent', 'examSchedule', 'subject'])
    ->get()->groupBy('student_session_id');
   
    $studentSessions = StudentSession::all();


    $data = $studentSessions->map(function($studentSession) use ($examResults) {
        $results = [];
        if (isset($examResults[$studentSession->id])) {
            foreach ($examResults[$studentSession->id] as $subjectResult) {
                $results[] = [
                    'subject_id' => $subjectResult->subject_id,
                    'participant_assessment' => $subjectResult->participant_assessment ?? '-',
                    'practical_assessment' => $subjectResult->practical_assessment ?? '-',
                    'theory_assessment' => $subjectResult->theory_assessment ?? '-',
                    'total' => ($subjectResult->participant_assessment + $subjectResult->practical_assessment + $subjectResult->theory_assessment) ?? '-'
                ];
            }
        }
       
        return [
            'admission_no' => $studentSession->admission_no,
            'roll_no' => $studentSession->roll_no,
            'student_name' => $studentSession->f_name . ' ' . $studentSession->m_name . ' ' . $studentSession->l_name,
            'father_name' => $studentSession->father_name,
            'class_name' => $studentSession->class_name,
            'section_name' => $studentSession->section_name,
            'results' => $results
        ];
    });


    return response()->json($data);
}
}

