<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Admission no.</th>
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
            @foreach($studentsMarks as $index => $mark)
                <tr>
                    <td>{{ $mark->studentSession->student->admission_no }}</td>
                    <td>{{ $mark->studentSession->student->roll_no ?? 'N/A' }}</td>
                    <td>{{ $mark->studentSession->student->user->full_name }}</td>
                    <td>
                        <input type="hidden"
                               name="attendance[{{ $index }}]"
                               value="1"> {{-- Default to present --}}
                        <input type="checkbox"
                               class="edit_attendance_chk"
                               name="attendance_check[{{ $index }}]"
                               value="0"
                               {{ ($mark->attendance ?? 1) == 0 ? 'checked' : '' }}>
                        <input type="hidden"
                               name="student_session_id[{{ $index }}]"
                               value="{{ $mark->student_session_id ?? '' }}">
                    </td>
                    <td>
                        <input type="number"
                               class="form-control edit_participant_assessment"
                               name="participant_assessment[{{ $index }}]"
                               value="{{ $mark->participant_assessment ?? 0 }}">
                    </td>
                    <td>
                        <input type="number"
                               class="form-control edit_practical_assessment"
                               name="practical_assessment[{{ $index }}]"
                               value="{{ $mark->practical_assessment ?? 0 }}">
                    </td>
                    <td>
                        <input type="number"
                               class="form-control edit_theory_assessment"
                               name="theory_assessment[{{ $index }}]"
                               value="{{ $mark->theory_assessment ?? 0 }}">
                    </td>
                    <td>
                        <input type="text"
                               class="form-control"
                               name="notes[{{ $index }}]"
                               value="{{ $mark->notes ?? '' }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="text-center mt-3">
        <button type="submit" class="btn btn-primary">Update Marks</button>
    </div>
</div>



