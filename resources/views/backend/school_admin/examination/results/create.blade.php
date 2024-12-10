@extends('backend.layouts.master')


<!-- Main content -->
@section('content')
    <div class="mt-4">
        <div class="d-flex justify-content-between mb-4">
            <div class="border-bottom border-primary">
                <h2>
                    {{ $page_title }}
                </h2>
            </div>
            <a href="{{ url()->previous() }}"><button class="btn-primary btn-sm"><i class="fa fa-angle-double-left"></i>
                Back</button></a>
        </div>
        <div class="card mb-2">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <form id="filterForm" method="POST" action="{{ route('admin.exam-routines.storeexamroutine') }}">
                            @csrf
                            <input type="hidden" name="examination_id" value="{{ $examinations->id }}">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>Class</label><small class="req"> *</small>
                                    <div class="form-group select">
                                        <select name="class_id">
                                            <option value="">Select Class</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->class }}</option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label>Section</label><small class="req"> *</small>
                                    <div class="form-group select">
                                        <select name="section_id" id="dynamic_section_id" class="input-text single-input-text">
                                            <option disabled>Select Section</option>
                                        </select>
                                        @error('section_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                </div>


                                <div class="mt-4" id="ajax_response">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
       
        <!-- Create Exam Marks Modal -->
        <div class="modal fade" id="createExamMarks" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Enter Marks Obtained By Students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="examheight100 relative">
                            <div class="marksEntryForm">
                                <div class="divider"></div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <form method="POST" action="{{ route('admin.exam-results.bulkimport') }}"
                                            enctype="multipart/form-data" id="fileUploadForm">
                                            @csrf
                                            <input type="hidden" name="exam_schedule_id" id="exam_schedule_id" value="">
                                            <input type="hidden" name="class_id" id="class_id" value="">
                                            <input type="hidden" name="section_id" id="section_id" value="">
                                            <input type="hidden" name="subject_id" id="subject_id" value="">
                                            <input type="hidden" name="exam_id" id="exam_id" value="">
                                            <input type="hidden" name="subject_group_id" id="subject_group_id" value="">


                                            <div class="input-group mb10">
                                                <div class="dropify-wrapper" style="height: 35.1111px;">
                                                    Import Marks:
                                                    <input id="my-file-selector" name="file" data-height="34"
                                                        class="dropify" type="file">
                                                </div>
                                                <div class="input-group-btn">
                                                    <input type="submit" class="btn btn-sm btn-success mt-2" value="Submit"
                                                        id="btnSubmit">
                                                </div>
                                            </div>
                                        </form>
                                    </div>


                                    <div class="col-md-3">
                                        <a class="btn btn-primary pull-right"
                                            href="{{ asset('sample-files/sample_resultImport.csv') }}" target="_blank"><i
                                                class="fa fa-download"></i> Import Sample</a>
                                    </div>
                                </div>
                                <hr>


                                <form method="post" action="{{ route('admin.students-mark.save') }}" id="student_marks">
                                    @csrf
                                    <div class="row" id="students_details">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Edit Marks Modal -->
        <div class="modal fade" id="editExamMarks" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Marks Obtained By Students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="examheight100 relative">
                            <div class="marksEntryForm">
                                <form method="post" action="{{ route('admin.students-mark.update') }}" id="edit_student_marks">
                                    @csrf
                                    <input type="hidden" name="exam_schedule_id" id="edit_exam_schedule_id" value="">
                                    <input type="hidden" name="class_id" id="edit_class_id" value="">
                                    <input type="hidden" name="section_id" id="edit_section_id" value="">
                                    <input type="hidden" name="subject_id" id="edit_subject_id" value="">
                                    <input type="hidden" name="exam_id" id="edit_exam_id" value="">
                                    <input type="hidden" name="subject_group_id" id="edit_subject_group_id" value="">
                                   
                                    <div class="row" id="edit_students_details">
                                       
                                        function generateEditModalHTML(students) {
                                            return `
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Roll No</th>
                                                                <th>Student Name</th>
                                                                <th>Attendance</th>
                                                                <th>Participant Assessment</th>
                                                                <th>Practical Assessment</th>
                                                                <th>Theory Assessment</th>
                                                                <th>Notes</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            ${students.map(student => `
                                                                <tr>
                                                                    <td>${student.student_session.student.admission_no}</td>
                                                                    <td>${student.student_session.student.name}</td>
                                                                    <td>
                                                                        <input type="checkbox"
                                                                            class="edit_attendance_chk"
                                                                            name="attendance[]"
                                                                            value="0"
                                                                            ${student.attendance == 0 ? 'checked' : ''}>
                                                                        <input type="hidden" name="student_session_id[]" value="${student.student_session_id}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            class="form-control edit_participant_assessment"
                                                                            name="participant_assessment[]"
                                                                            value="${student.participant_assessment || 0}"
                                                                            ${student.attendance == 0 ? 'disabled' : ''}>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            class="form-control edit_practical_assessment"
                                                                            name="practical_assessment[]"
                                                                            value="${student.practical_assessment || 0}"
                                                                            ${student.attendance == 0 ? 'disabled' : ''}>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            class="form-control edit_theory_assessment"
                                                                            name="theory_assessment[]"
                                                                            value="${student.theory_assessment || 0}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                            class="form-control"
                                                                            name="notes[]"
                                                                            value="${student.notes || ''}">
                                                                    </td>
                                                                </tr>
                                                            `).join('')}
                                                        </tbody>
                                                    </table>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary">Update Marks</button>
                                                    </div>
                                                </div>
                                            `;
                                        }
                                     
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>


    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            // SEARCH BUTTON CLICK EVENT
            $('#searchButton').on('click', function() {
                dataTable.order([
                    [4, 'asc']
                ]).ajax.reload();
            });


            // Attach change event handler to the class dropdown
            $('select[name="class_id"]').change(function() {
                var classId = $(this).val();
                fetchSections(classId);
            });


            function fetchSections(classId) {
                $.ajax({
                    url: '{{ url('admin/get-section-by-class') }}/' + classId,
                    type: 'GET',
                    success: function(data) {
                        $('select[name="section_id"]').empty();
                        $('select[name="section_id"]').append('<option disabled selected>Select Section</option>');
                        $.each(data, function(key, value) {
                            $('select[name="section_id"]').append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            }


            $('select[name="section_id"]').change(function() {
                var sectionId = $(this).val();
                var classId = $('select[name="class_id"]').val();
                var examinationId = $('input[name="examination_id"]').val();
                fetchSubjects(classId, sectionId, examinationId);
            });


            function fetchSubjects(classId, sectionId, examinationId) {
                $.ajax({
                    url: '{{ url('admin/exam-results/get-routine-wise-subject/class-section-and-examination') }}',
                    type: 'GET',
                    data: {
                        class_id: classId,
                        sections: sectionId,
                        examination_id: examinationId
                    },
                    success: function(data) {
                        $('#ajax_response').empty();
                        if (data.message) {
                            alert(data.message);
                        } else {
                            $('#ajax_response').html(data);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#ajax_response').empty();
                        if (xhr.status === 400) {
                            var errorMessage = JSON.parse(xhr.responseText).message;
                            toastr.error(errorMessage);
                        } else {
                            toastr.error('An error occurred while processing your request. Please try again later.');
                        }
                    }
                });
            }


            // Handle modal for assigning marks
            $(document).on('click', '.assignMarks', function() {
                var examScheduleId = this.dataset.exam_schedule_id;
                var classId = this.dataset.class_id;
                var sectionId = this.dataset.section_id;
                var subjectId = this.dataset.subject_id;
                var examId = this.dataset.exam_id;
                var subjectGroupId = this.dataset.subject_group_id;


                $('#class_id').val(classId);
                $('#section_id').val(sectionId);
                $('#subject_id').val(subjectId);
                $('#exam_id').val(examId);
                $('#subject_group_id').val(subjectGroupId);
                $('#exam_schedule_id').val(examScheduleId);


                fetchStudentDetails(classId, sectionId, subjectId, examId, examScheduleId);
                $('#createExamMarks').modal('show');
            });


            function fetchStudentDetails(classId, sectionId, subjectId, examId, examScheduleId) {
                $.ajax({
                    url: '{{ url('admin/exam-results/get-students-by-class-section-subject-and-examination') }}',
                    type: 'POST',
                    data: {
                        class_id: classId,
                        section_id: sectionId,
                        subject_id: subjectId,
                        examination_id: examId,
                        examination_schedule_id: examScheduleId
                    },
                    success: function(data) {
                        $('#students_details').html(data);
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while processing your request. Please try again later.');
                    }
                });
            }


            // Handle attendance checkbox in create marks modal
            $(document).on('click', '.attendance_chk', function() {
                var isChecked = $(this).prop('checked');
                if (isChecked) {
                    $(this).closest('tr').find('.participant_assessment').prop('disabled', true).val(0);
                    $(this).closest('tr').find('.practical_assessment').prop('disabled', true).val(0);
                    $(this).closest('tr').find('.theory_assessment').prop('disabled', false).val(0);
                } else {
                    $(this).closest('tr').find('.participant_assessment').prop('disabled', false).val('');
                    $(this).closest('tr').find('.practical_assessment').prop('disabled', false).val('');
                    $(this).closest('tr').find('.theory_assessment').prop('disabled', false).val('0');
                }
            });


        });


       // Handle modal for editing marks
$(document).on('click', '.editMarks', function() {
    var examScheduleId = $(this).data('exam_schedule_id');
    var classId = $(this).data('class_id');
    var sectionId = $(this).data('section_id');
    var subjectId = $(this).data('subject_id');
    var examId = $(this).data('exam_id');
    var subjectGroupId = $(this).data('subject_group_id');


    // Set values in edit modal's hidden inputs
    $('#edit_exam_schedule_id').val(examScheduleId);
    $('#edit_class_id').val(classId);
    $('#edit_section_id').val(sectionId);
    $('#edit_subject_id').val(subjectId);
    $('#edit_exam_id').val(examId);
    $('#edit_subject_group_id').val(subjectGroupId);


    // Fetch student details for editing
    $.ajax({
        url: '{{ route("admin.students-marks.get-for-edit") }}',
        type: 'POST',
        data: {
            examination_schedule_id: examScheduleId,
            subject_id: subjectId
        },
        success: function(response) {
            if (response.status === 'success') {
                $('#edit_students_details').html(response.html);
                $('#editExamMarks').modal('show');
            } else {
                toastr.error(response.message || 'Error loading student marks');
            }
        },
        error: function(xhr) {
            toastr.error('Error loading student marks: ' +
                (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
});


// Handle form submission
$('#edit_student_marks').on('submit', function(e) {
    e.preventDefault();
   
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.status === 'success') {
                toastr.success('Marks updated successfully');
                $('#editExamMarks').modal('hide');
                // Refresh the main view
                fetchSubjects(
                    $('select[name="class_id"]').val(),
                    $('select[name="section_id"]').val(),
                    $('input[name="examination_id"]').val()
                );
            } else {
                toastr.error(response.message || 'Error updating marks');
            }
        },
        error: function(xhr) {
            toastr.error('Error updating marks: ' +
                (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
});


// Handle attendance checkbox changes
$(document).on('change', '.edit_attendance_chk', function() {
    const row = $(this).closest('tr');
    const isAbsent = $(this).prop('checked');
   
    row.find('.edit_participant_assessment, .edit_practical_assessment')
        .prop('disabled', isAbsent)
        .val(isAbsent ? '0' : '');
   
    if (isAbsent) {
        row.find('.edit_theory_assessment').val('0');
    }
});
    </script>
@endsection



