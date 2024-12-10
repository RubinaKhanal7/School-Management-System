@can('edit_exam_routines')
    <a href="{{ route('admin.exam-routines.edit', $routine->id) }}" class="btn btn-outline-primary btn-sm mx-1"
        data-toggle="tooltip" data-placement="top" title="Edit">
        <i class="fa fa-edit"></i>
    </a>
@endcan
{{-- @can('delete_exam_routines')
    <a href="{{ route('admin.exam-routines.destroy', $routine->id) }}" class="btn btn-outline-danger btn-sm mx-1"
        data-toggle="tooltip" data-placement="top" title="Delete">
        <i class="fa fa-edit"></i>
    </a>
@endcan --}}

<!-- Delete button and modal -->
@can('delete_exam_routines')
    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
        data-bs-target="#delete{{ $routine->id }}" data-toggle="tooltip" data-placement="top" title="Delete">
        <i class="far fa-trash-alt"></i>
    </button>

    <div class="modal fade" id="delete{{ $routine->id }}" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.exam-routines.destroy', $routine->id) }}"
                    accept-charset="UTF-8" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                        <p>Are you sure to delete <span id="underscore" class="must"> </span>?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">No</button>
                        <button type="submit" class="btn btn-danger">Yes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

{{-- @can('create_assign_students')
    <a href="{{ route('admin.assign-students.create', $examinations->id) }}" class="btn btn-outline-success btn-sm mx-1"
        data-toggle="tooltip" data-placement="top" title="Assign Students">
        <i class="fas fa-arrow-circle-right"></i>
    </a>
@endcan --}}


@can('create_assign_students')
    <a href="{{ route('admin.assign-students.create.for-examroutine', ['exam_id' => $examinations->id, 'routine_id' => $routine->id]) }}"
        class="btn btn-outline-success btn-sm mx-1" data-toggle="tooltip" data-placement="top" title="Assign Students">
        <i class="fas fa-arrow-circle-right"></i>
    </a>
@endcan



@can('create_exam_results')
    <a href="{{ route('admin.exam-results.create.for-examroutine', ['exam_id' => $examinations->id, 'routine_id' => $routine->id]) }}"
        class="btn btn-outline-success btn-sm mx-1" data-toggle="tooltip" data-placement="top" title="Exam Results">
        <i class="fas fa-arrow-circle-right"></i>
    </a>
@endcan


<button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal"
        data-bs-target="#printRoutine{{ $routine->id }}" data-toggle="tooltip" data-placement="top" title="Print Routine">
        <i class="fas fa-print"></i>
    </button>

    <!-- Print Modal -->
    <div class="modal fade" id="printRoutine{{ $routine->id }}" tabindex="-1" aria-labelledby="printRoutineLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printRoutineLabel">Exam Routine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="printableArea{{ $routine->id }}">
                    <div class="text-center mb-4">
                        <h4>{{ $examinations->exam }} Examination Schedule</h4>
                        <p>Class: {{ $routine->classes->class }} | Section: {{ $routine->sections->section_name }}</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="5%">S.N</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $schedules = App\Models\ExamSchedule::where('class_id', $routine->class_id)
                                        ->where('section_id', $routine->section_id)
                                        ->where('subject_group_id', $routine->subject_group_id)
                                        ->with(['subject'])
                                        ->get();
                                    $i = 1;
                                @endphp
                                @foreach($schedules as $schedule)
                                    <tr>
                                        <td class="text-center">{{ $i++ }}</td>
                                        <td>{{ $schedule->subject->subject }}</td>
                                        <td>{{ $schedule->exam_date }}</td>
                                        <td>{{ $schedule->exam_time }}</td>
                                        <td>{{ $schedule->exam_duration }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printRoutine('{{ $routine->id }}')">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>