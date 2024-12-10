@extends('backend.layouts.master')


@section('content')
    <div class="container-fluid mt-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Generate Result Of SECOND TERM EXAM</h1>
            <div>
                <a href="#" class="btn btn-secondary">« Back</a>
                <a href="#" class="btn btn-success">Export Results</a>
            </div>
        </div>


        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ request()->url() }}">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="class_id">Select Class:</label>
                                <select name="class_id" id="class_id" class="form-control">
                                    <option value="">-- Select Class --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}"
                                            {{ request('class_id') == $class->id ? 'selected' : '' }}
                                            data-sections='@json($class->sections)'>
                                            {{ $class->class ?? 'Unnamed Class' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="section_id">Select Section:</label>
                                <select name="section_id" id="section_id" class="form-control">
                                    <option value="">-- Select Section --</option>
                                    @if(request('class_id'))
                                        @foreach($classes->find(request('class_id'))->sections as $section)
                                            <option value="{{ $section->id }}"
                                                {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                                {{ $section->section_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">View Results</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- Results Table -->
        @if(request('class_id') && request('section_id'))
            @if($studentSessions->isEmpty())
                <div class="alert alert-info">
                    No results found for the selected class and section.
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Admission No</th>
                                        <th rowspan="2">Roll No</th>
                                        <th rowspan="2">Name</th>
                                       
                                        @foreach($examinations->subjectByRoutine as $subject)
                                            <th colspan="5" class="text-center bg-primary text-white">
                                                {{ $subject->subject ?? 'N/A' }}
                                            </th>
                                        @endforeach
                                        <th rowspan="2" class="text-center bg-primary text-white">Overall GPA</th>
                                    </tr>
                                    <tr class="bg-secondary text-white">
                                        @foreach($examinations->subjectByRoutine as $subject)
                                            <th>Participant Marks</th>
                                            <th>Practical Marks</th>
                                            <th>Theory Marks</th>
                                            <th>Total</th>
                                            <th>Grade points</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($studentSessions as $session)
                                        <tr>
                                            <td>{{ $session->student->admission_no  }}</td>
                                            <td>{{ $session->student->roll_no ?? 'N/A' }}</td>
                                            <td>{{ $session->student->user->f_name ?? 'N/A' }}</td>
                                       
                                           
                                            @php
                                                $totalCreditHours = 0;
                                                $totalGradePoints = 0;
                                            @endphp


                                            @foreach($examinations->subjectByRoutine as $subject)
                                                @php
                                                    $result = $restructuredResults[$session->id][$subject->id] ?? null;
                                                    $creditHours = $subject->examSchedule->credit_hour ?? 1;
                                                   
                                                    if($result) {
                                                        $normalizedTheoryMarks = ($result->theory_assessment * 10 / 50);
                                                        $total = $result->participant_assessment +
                                                                $result->practical_assessment +
                                                                $normalizedTheoryMarks;
                                                       
                                                        $gpa = calculateGPA(
                                                            $result->participant_assessment,
                                                            $result->practical_assessment,
                                                            $normalizedTheoryMarks
                                                        );
                                                       
                                                        if ($gpa !== null) {
                                                            $totalCreditHours += $creditHours;
                                                            $totalGradePoints += $gpa * $creditHours;
                                                        }
                                                    }
                                                @endphp


                                                @if($result)
                                                    <td class="text-center">{{ number_format($result->participant_assessment, 2) }}</td>
                                                    <td class="text-center">{{ number_format($result->practical_assessment, 2) }}</td>
                                                    <td class="text-center">{{ number_format($normalizedTheoryMarks, 2) }}</td>
                                                    <td class="text-center">{{ number_format($total, 2) }}</td>
                                                    <td class="text-center">{{ $gpa ? number_format($gpa, 2) : 'N/A' }}</td>
                                                @else
                                                    <td colspan="5" class="text-center text-muted">N/A</td>
                                                @endif
                                            @endforeach


                                            <td class="text-center font-weight-bold">
                                                {{ $totalCreditHours ? number_format($totalGradePoints / $totalCreditHours, 2) : '0.00' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>


    @php
        function calculateGPA($participantAssessment, $practicalAssessment, $theoryAssessment) {
            $totalMarks = $participantAssessment + $practicalAssessment + $theoryAssessment;
            $gpa = 0;
            if ($totalMarks > 45) {
                $gpa = 4.0;
            } elseif ($totalMarks > 40) {
                $gpa = 3.6;
            } elseif ($totalMarks > 35) {
                $gpa = 3.2;
            } elseif ($totalMarks > 30) {
                $gpa = 2.8;
            } elseif ($totalMarks > 25) {
                $gpa = 2.4;
            } elseif ($totalMarks > 20) {
                $gpa = 2.0;
            } elseif ($totalMarks > 18) {
                $gpa = 1.6;
            } else {
                $gpa = 0.0;
            }
            return $gpa;
        }
    @endphp


    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const classSelect = document.getElementById('class_select');
            const sectionSelect = document.getElementById('section_select');
            const resultsTable = document.getElementById('results_table');
            const resultsBody = document.getElementById('results_body');
            const loadingSpinner = document.getElementById('loading_spinner');
            const noResultsMessage = document.getElementById('no_results_message');


            function showLoading() {
                loadingSpinner.style.display = 'block';
                resultsTable.style.display = 'none';
                noResultsMessage.style.display = 'none';
            }


            function hideLoading() {
                loadingSpinner.style.display = 'none';
            }


            function showNoResults() {
                noResultsMessage.style.display = 'block';
                resultsTable.style.display = 'none';
            }


            // Handle class selection
            classSelect.addEventListener('change', function() {
                const classId = this.value;
                sectionSelect.disabled = !classId;
                sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
                resultsTable.style.display = 'none';
                noResultsMessage.style.display = 'none';


                if (classId) {
                    showLoading();
                    // Fetch sections for selected class
                    fetch(`/admin/sections-by-class/${classId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(sections => {
                        sections.forEach(section => {
                            const option = document.createElement('option');
                            option.value = section.id;
                            option.textContent = section.section;
                            sectionSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error('Error loading sections. Please try again.');
                    })
                    .finally(() => {
                        hideLoading();
                    });
                }
            });


            // Handle section selection
            sectionSelect.addEventListener('change', function() {
                const sectionId = this.value;
                const classId = classSelect.value;


                if (classId && sectionId) {
                    showLoading();


                    // Fetch students and results for selected class and section
                    fetch(`/admin/students-by-class-section`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            class_id: classId,
                            section_id: sectionId,
                            examination_id: '{{ $examinations->id }}'
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        resultsBody.innerHTML = ''; // Clear existing results


                        if (data.students.length === 0) {
                            showNoResults();
                            return;
                        }


                        // Populate table with new data
                        data.students.forEach(session => {
                            let totalCreditHours = 0;
                            let totalGradePoints = 0;


                            const row = document.createElement('tr');
                           
                            // Add student info cells
                            row.innerHTML = `
                                <td>${session.student.admission_no ?? 'N/A'}</td>
                                <td>${session.student.roll_no ?? 'N/A'}</td>
                                <td>${session.student.user.f_name ?? 'N/A'}</td>
                                <td>${session.classg.class ?? 'N/A'}</td>
                                <td>${session.section.section ?? 'N/A'}</td>
                            `;


                            // Add subject results
                            @foreach($examinations->subjectByRoutine as $subject)
                                const result = data.results[session.id]?.['{{ $subject->id }}'];
                                const creditHours = {{ $subject->examSchedule->credit_hour ?? 1 }};
                               
                                if (result) {
                                    const normalizedTheoryMarks = (result.theory_assessment * 10 / 50);
                                    const gpa = calculateGPA(
                                        result.participant_assessment,
                                        result.practical_assessment,
                                        normalizedTheoryMarks
                                    );


                                    if (gpa !== null) {
                                        totalCreditHours += creditHours;
                                        totalGradePoints += gpa * creditHours;
                                    }


                                    row.innerHTML += `
                                        <td>${result.participant_assessment}</td>
                                        <td>${result.practical_assessment}</td>
                                        <td>${normalizedTheoryMarks.toFixed(2)}</td>
                                        <td>${(result.participant_assessment + result.practical_assessment + normalizedTheoryMarks).toFixed(2)}</td>
                                        <td>${gpa ? gpa.toFixed(2) : 'N/A'}</td>
                                    `;
                                } else {
                                    row.innerHTML += `
                                        <td colspan="5" class="text-center text-muted">N/A</td>
                                    `;
                                }
                            @endforeach


                            // Add overall GPA
                            const overallGPA = totalCreditHours ? (totalGradePoints / totalCreditHours).toFixed(2) : '0.00';
                            row.innerHTML += `
                                <td class="text-center font-weight-bold">${overallGPA}</td>
                            `;


                            resultsBody.appendChild(row);
                        });


                        resultsTable.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error('Error loading results. Please try again.');
                        showNoResults();
                    })
                    .finally(() => {
                        hideLoading();
                    });
                } else {
                    resultsTable.style.display = 'none';
                    noResultsMessage.style.display = 'none';
                }
            });
        });


        function updateSections(classSelect) {
            const sectionSelect = document.getElementById('section_id');
            sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
           
            const selectedOption = classSelect.options[classSelect.selectedIndex];
            if (selectedOption.value) {
                try {
                    const sections = JSON.parse(selectedOption.dataset.sections);
                    sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = section.section_name;
                        sectionSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error parsing sections:', error);
                }
            }
        }
    </script>
   
    @endpush
@endsection

