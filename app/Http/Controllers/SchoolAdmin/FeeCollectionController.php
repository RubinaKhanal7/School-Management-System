<?php

namespace App\Http\Controllers\SchoolAdmin;

use Validator;
use App\Models\Classg;
use App\Models\Section;
use App\Models\StudentSession;
use App\Models\AcademicSession;
use App\Models\FeeGroupStudent;
use Illuminate\Http\Request;
use App\Models\FeeCollection;
use App\Models\FeeGroupType;
use App\Models\FeeDue;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FeeCollectionController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $classes = Classg::where('school_id', $schoolId)->get();
        $selectedClass = $request->input('class_id');
        $sections = $selectedClass ? Section::whereHas('classSections', function($query) use ($selectedClass) {
            $query->where('class_id', $selectedClass);
        })->get() : collect();
        
        $page_title = 'Fee Collection';
        return view('backend.school_admin.fee_collection.index', compact('page_title', 'classes', 'sections'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_session_id' => 'required|exists:student_sessions,id',
            'payment_mode_id' => 'required|in:1,2',
            'payed_on' => 'required|date',
            'notes' => 'required|string',
            'selected_fees' => 'required|array',
            'selected_fees.*.fee_groups_types_id' => 'required|exists:fee_groups_types,id',
            'selected_fees.*.amount' => 'required|numeric|min:0'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            DB::beginTransaction();
    
            foreach ($request->selected_fees as $selectedFee) {
                // Check if fee is already fully paid
                $existingPayments = FeeCollection::where('student_session_id', $request->student_session_id)
                    ->where('fee_groups_types_id', $selectedFee['fee_groups_types_id'])
                    ->sum('amount');
    
                $feeGroupType = FeeGroupType::find($selectedFee['fee_groups_types_id']);
    
                if ($existingPayments + $selectedFee['amount'] > $feeGroupType->amount) {
                    throw new \Exception('Payment amount exceeds remaining fee balance');
                }
                FeeCollection::create([
                    'student_session_id' => $request->student_session_id,
                    'fee_groups_types_id' => $selectedFee['fee_groups_types_id'],
                    'amount' => $selectedFee['amount'],
                    'payment_mode_id' => $request->payment_mode_id,
                    'payed_on' => $request->payed_on,
                    'notes' => $request->notes
                ]);
        }
    
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Fee collections saved successfully']);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee Collection Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving fee collection: ' . $e->getMessage()
            ], 500);
        }
    }
    
public function getStudentFeeDetails($studentSessionId)
{
    try {
        $studentSession = StudentSession::with([
            'user',
            'student',
            'classg',
            'section',
            'feeGroups'
        ])->findOrFail($studentSessionId);

        $currentSession = AcademicSession::where('is_active', true)->first();
        if (!$currentSession) {
            throw new \Exception('No active academic session found');
        }

        // Get all fee collections for this student
        $feeCollections = FeeCollection::where('student_session_id', $studentSessionId)
            ->get();

        $feeDetails = [];
        $paymentHistory = [];
        
        foreach ($studentSession->feeGroups as $feeGroup) {
            $groupTotal = 0;
            $feeTypes = [];

            // Get fee types for this group
            $groupFeeTypes = DB::table('fee_groups_types')
                ->join('fee_types', 'fee_types.id', '=', 'fee_groups_types.fee_type_id')
                ->where('fee_groups_types.fee_group_id', $feeGroup->id)
                ->where('fee_groups_types.academic_session_id', $currentSession->id)
                ->where('fee_groups_types.is_active', true)
                ->select(
                    'fee_types.*',
                    'fee_groups_types.amount',
                    'fee_groups_types.id as fee_groups_type_id'
                )
                ->get();

            foreach ($groupFeeTypes as $feeType) {
                $paidAmount = 0;
                
                // Calculate total paid amount for this fee type
                foreach ($feeCollections as $collection) {
                    if ($collection->fee_groups_types_id == $feeType->fee_groups_type_id) {
                        $paidAmount += $collection->amount;

                        // Add to payment history
                        $paymentHistory[] = [
                            'fee_type_id' => $feeType->id,
                            'amount' => $collection->amount,
                            'date' => $collection->payed_on,
                            'payment_mode' => $collection->payment_mode_id,
                            'notes' => $collection->notes
                        ];
                    }
                }

                $amount = $feeType->amount;
                $groupTotal += $amount;
                $remainingAmount = max(0, $amount - $paidAmount);
                
                $feeTypes[] = [
                    'id' => $feeType->fee_groups_type_id,
                    'name' => $feeType->name,
                    'amount' => $amount,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                    'is_paid' => $paidAmount >= $amount
                ];
            }

            if (!empty($feeTypes)) {
                $feeDetails[] = [
                    'group_id' => $feeGroup->id,
                    'group_name' => $feeGroup->name,
                    'fee_types' => $feeTypes,
                    'total_amount' => $groupTotal
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'name' => $studentSession->user->f_name . ' ' . $studentSession->user->l_name,
                    'admission_no' => $studentSession->student->admission_no,
                    'class' => $studentSession->classg->class,
                    'section' => $studentSession->section->section_name,
                ],
                'student_session_id' => $studentSession->id,
                'fee_details' => $feeDetails,
                'payment_history' => $paymentHistory
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Fee Details Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching student fee details: ' . $e->getMessage()
        ], 500);
    }
}

    public function getStudentsCollection(Request $request)
    {
        $classId = $request->input('classId');
        $sectionId = $request->input('sectionId');
        $schoolId = auth()->user()->school_id;
    
        $currentSession = AcademicSession::where('is_active', true)->first();
    
        $students = User::join('student_sessions', 'users.id', '=', 'student_sessions.user_id')
            ->join('students', 'users.id', '=', 'students.user_id')
            ->leftJoin('classes', 'student_sessions.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'student_sessions.section_id', '=', 'sections.id')
            ->where('users.user_type_id', '=', 8)
            ->where('student_sessions.academic_session_id', $currentSession->id)
            ->where('student_sessions.school_id', $schoolId)
            ->where('student_sessions.is_active', 1)
            ->when($classId, function ($query) use ($classId) {
                $query->where('student_sessions.class_id', $classId);
            })
            ->when($sectionId, function ($query) use ($sectionId) {
                $query->where('student_sessions.section_id', $sectionId);
            })
            ->select(
                'users.id as user_id',
                'users.f_name',
                'users.l_name',
                'users.father_name',
                'users.dob',
                'users.mobile_number',
                'students.admission_no',
                'classes.class as class_name',
                'sections.section_name',
                'student_sessions.id as student_session_id'
            )
            ->get();
    
        return response()->json($students);
    }
}
