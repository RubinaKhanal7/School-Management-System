@extends('backend.layouts.master')

@section('content')
<div class="mt-4">
    <div class="d-flex justify-content-between mb-4">
        <div class="border-bottom border-primary">
            <h2>{{ $page_title }} - {{ $feeGroup->name }}</h2>
        </div>
    </div>

    {{-- Fee Group Details Card --}}
    <div class="card mb-4">
        <div class="card-header text-white">
            <h4 class="mb-0">Fee Group Details</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Fee Type</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalAmount = 0; @endphp
                        @foreach($feeGroup->feeGroupTypes as $feeGroupType)
                        @php $totalAmount += $feeGroupType->amount; @endphp
                        <tr>
                            <td>{{ $feeGroupType->feeType->name }}</td>
                            <td class="text-end">{{ number_format($feeGroupType->amount, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td class="fw-bold">Total Amount</td>
                            <td class="text-end fw-bold">{{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Student Selection Form --}}
    <form id="assignmentForm" method="POST" action="{{ route('admin.fee-grouptypes.store-assignment') }}">
        @csrf
        <input type="hidden" name="fee_group_id" value="{{ $feeGroup->id }}">

        <div class="row mb-4">
            {{-- Class Selection --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label for="class_id" class="form-label">Class:</label>
                    <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" 
                                {{ (old('class_id') == $class->id) ? 'selected' : '' }}
                                data-sections='@json($class->sections)'>
                                {{ $class->class }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Section Selection --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label for="section_id" class="form-label">Section:</label>
                    <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" required>
                        <option value="">Select Section</option>
                    </select>
                    @error('section_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Search Button --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-primary d-block" id="searchButton"> Search Students</button>
                </div>
            </div>

            {{-- Search Input for Student Filtering --}}
        <div class="col-mb-4">
            <div class="form-group">
                <label for="studentSearch" class="form-label">Search Student by Name:</label>
                <input type="text" id="studentSearch" class="form-control" placeholder="Enter student name">
            </div>
        </div>
        </div>

        {{-- Student List Table --}}
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Students List</h4>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus me-2"></i>Assign Selected Students
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <div class="form-check">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                        <label class="form-check-label" for="selectAll">All</label>
                                    </div>
                                </th>
                                <th>S.N.</th>
                                <th>Student Name</th>
                                <th>Father's Name</th>
                                <th>Admission No.</th>
                                <th>Contact</th>
                                <th scope="col">Class</th>
                                <th scope="col">Section</th>
                            </tr>
                        </thead>
                        <tbody id="studentList">
                            {{-- Students will be loaded here via AJAX --}}
                        </tbody>
                    </table>
                </div>

                <div id="noStudentsMessage" class="alert alert-info d-none">
                    No students found for the selected class and section.
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let loadingIndicator = $('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

    $('#class_id').change(function() {
        let selectedOption = $(this).find('option:selected');
        let sections = selectedOption.data('sections') || [];
        let sectionSelect = $('#section_id');
        
        sectionSelect.empty().append('<option value="">Select Section</option>');
        sections.forEach(function(section) {
            sectionSelect.append($('<option>', {
                value: section.id,
                text: section.section_name
            }));
        });
        $('#studentList').empty();
        $('#noStudentsMessage').addClass('d-none');
    });

    // Handle student search
    $('#searchButton').click(function() {
        let classId = $('#class_id').val();
        let sectionId = $('#section_id').val();
        if (!classId || !sectionId) {
            Swal.fire({
                icon: 'warning',
                title: 'Selection Required',
                text: 'Please select both class and section',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        let tbody = $('#studentList');
        tbody.html(loadingIndicator);
        $('#noStudentsMessage').addClass('d-none');
        $.ajax({
            url: '{{ route("admin.get-studentscollection") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                classId: classId,
                sectionId: sectionId
            },
            success: function(data) {
                tbody.empty();
                
                if (data.length === 0) {
                    $('#noStudentsMessage').removeClass('d-none');
                    return;
                }

                data.forEach(function(student, index) {
                    tbody.append(`
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" name="student_ids[]" 
                                           value="${student.student_session_id}" 
                                           class="form-check-input student-checkbox"
                                           id="student${student.student_session_id}">
                                </div>
                            </td>
                            <td>${index + 1}</td>
                            <td>${student.f_name} ${student.l_name}</td>
                            <td>${student.father_name || '-'}</td>
                            <td>${student.admission_no || '-'}</td>
                            <td>${student.phone || '-'}</td>
                            <td>${student.class_name || '-'}</td>
                            <td>${student.section_name || '-'}</td>
                        </tr>
                    `);
                });
            },
            error: function(xhr) {
                console.error('Error loading students:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load students. Please try again.',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    });

    $('#selectAll').change(function() {
        $('.student-checkbox').prop('checked', $(this).prop('checked'));
    });

    $(document).on('change', '.student-checkbox', function() {
        let allChecked = $('.student-checkbox:checked').length === $('.student-checkbox').length;
        $('#selectAll').prop('checked', allChecked);
    });

    $('#studentSearch').on('keyup', function() {
        let searchValue = $(this).val().toLowerCase();
        $('#studentList tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().includes(searchValue));
        });
    });
});
</script>
@endsection
