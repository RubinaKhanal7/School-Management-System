@extends('backend.layouts.master')

@section('content')
    <div class="mt-4">
        <div class="d-flex justify-content-between mb-4">
            <div class="border-bottom border-primary">
                <h2>{{ $page_title }}</h2>
            </div>
            @include('backend.school_admin.fee_collection.partials.action')
        </div>

        <form id="filterForm">
            @csrf
            <div class="row align-items-center">
                <div class="col-lg-3 col-sm-6 mt-2">
                    <label for="class_id">Class:</label>
                    <div class="select">
                        <select name="class_id">
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->class }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('class_id')
                        <strong class="text-danger">{{ $message }}</strong>
                    @enderror
                </div>
            
                <div class="col-lg-3 col-sm-6 mt-2">
                    <label for="section_id">Section:</label>
                    <div class="select">
                        <select name="section_id">
                            <option disabled>Select Section</option>
                            <option value=""></option>
                        </select>
                    </div>
                    @error('section_id')
                        <strong class="text-danger">{{ $message }}</strong>
                    @enderror
                </div>
            
                <div class="col-lg-2 col-sm-4 mt-2">
                    <button type="button" class="btn btn-primary" id="searchButton" style="margin-top: 24px;">Search</button>
                </div>
            </div>            
        </form>
    </div>

    <div class="feecollectiontable">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">S.N.</th>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">Father Name</th>
                    <th scope="col">DOB</th>
                    <th scope="col">Mobile Number</th>
                    <th scope="col">Admission Number</th>
                    <th scope="col">Class</th>
                    <th scope="col">Section</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

<!-- Fee Collection Modal -->
<div class="modal fade" id="feeCollectionModal" tabindex="-1" aria-labelledby="feeCollectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="feeCollectionModalLabel">Fee Collection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Student Info Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <p class="mb-1"><small class="text-muted">Student Name</small></p>
                                <p class="fw-bold" id="studentName"></p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1"><small class="text-muted">Admission No</small></p>
                                <p class="fw-bold" id="admissionNo"></p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1"><small class="text-muted">Class</small></p>
                                <p class="fw-bold" id="studentClass"></p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1"><small class="text-muted">Section</small></p>
                                <p class="fw-bold" id="studentSection"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Groups Section -->
                <div class="fee-groups mb-4">
                    <h6 class="mb-3">Fee Details</h6>
                    <div id="feeGroupsContainer">
                        <!-- Fee groups will be populated here -->
                    </div>
                </div>

                <!-- Payment Form -->
                <div id="feeCollectionFormContainer" style="display: none;">
                    <form id="feeCollectionForm" class="card">
                        <div class="card-body">
                            @csrf
                            <input type="hidden" name="student_session_id" id="studentSessionId">
                            <input type="hidden" name="selected_fee_types" id="selectedFeeTypes">
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="paymentMode" class="form-label">Payment Mode</label>
                                        <select class="form-select" id="paymentMode" name="payment_mode_id" required>
                                            <option value="">Select Payment Mode</option>
                                            <option value="1">Cash</option>
                                            <option value="2">Online</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nepali-datepicker" class="form-label">Payment Date</label>
                                        <input type="text" class="form-control" id="nepali-datepicker" name="payed_on" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="1" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="collectFeeButton" style="display: none;">
                    Collect Selected Fees
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
 // Initialize nepali-datepicker
 $('#nepali-datepicker').nepaliDatePicker({
            dateFormat: 'YYYY-MM-DD',
            closeOnDateSelect: true,
        });
    
        var currentDate = NepaliFunctions.GetCurrentBsDate();
        var padZero = function (num) {
            return num < 10 ? '0' + num : num;
        };
        var formattedDate = currentDate.year + '-' + padZero(currentDate.month) + '-' + padZero(currentDate.day);
        $('#nepali-datepicker').val(formattedDate);
        $('#searchButton').click(function() {
            var classId = $('select[name="class_id"]').val();
            var sectionId = $('select[name="section_id"]').val();
            
            $.ajax({
                url: '/admin/get-studentscollection',
                type: 'POST',
                data: {
                    classId: classId,
                    sectionId: sectionId
                },
                success: function(data) {
                    updateStudentTable(data);
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.error('Ajax Request Error:', textStatus, errorThrown);
                }
            });
        });

function updateStudentTable(data) {
    $('.feecollectiontable tbody').empty();
    $.each(data, function(key, value) {
        var rowHtml = `
            <tr>
                <th scope="row">${key + 1}</th>
                <td>${value.f_name}</td>
                <td>${value.l_name}</td>
                <td>${value.father_name}</td>
                <td>${value.dob}</td>
                <td>${value.mobile_number}</td>
                <td>${value.admission_no}</td>
                <td>${value.class_name}</td>
                <td>${value.section_name}</td>
                <td>
                    <button type="button" class="btn btn-success btn-sm collect-fee" 
                        data-student-id="${value.student_session_id}">
                        Collect Fee
                    </button>
                </td>
            </tr>`;
        $('.feecollectiontable tbody').append(rowHtml);
    });
}

$(document).on('click', '.collect-fee', function() {
    const studentSessionId = $(this).data('student-id');
    loadStudentFeeDetails(studentSessionId);
});

function loadStudentFeeDetails(studentSessionId) {
    $.ajax({
        url: `/admin/student-fee-details/${studentSessionId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                populateFeeModal(response.data);
                $('#feeCollectionModal').modal('show');
            } else {
                alert('Error loading student fee details');
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            alert('Error loading student fee details');
        }
    });
}

function populateFeeModal(data) {
    $('#studentSessionId').val(data.student_session_id);
    $('#studentName').text(data.student.name);
    $('#studentClass').text(data.student.class);
    $('#studentSection').text(data.student.section);
    $('#admissionNo').text(data.student.admission_no);
    
    let feeGroupsHtml = '';
    let totalDueAmount = 0;
    let totalPaidAmount = 0;
    let hasUnpaidFees = false;
    
    data.fee_details.forEach(group => {
        let feeTypesHtml = '';
        let groupDueAmount = 0;
        let groupPaidAmount = 0;
        
        group.fee_types.forEach(type => {
            const paidAmount = parseFloat(type.paid_amount) || 0;
            const remainingAmount = parseFloat(type.remaining_amount) || 0;
            
            if (type.is_paid) {
                groupPaidAmount += type.amount;
                totalPaidAmount += type.amount;
            } else {
                groupDueAmount += remainingAmount;
                totalDueAmount += remainingAmount;
                if (remainingAmount > 0) {
                    hasUnpaidFees = true;
                }
            }

            const typePayments = data.payment_history.filter(payment => 
                payment.fee_type_id === type.id
            );

            let paymentHistoryHtml = '';
            if (typePayments.length > 0) {
                paymentHistoryHtml = `
                    <div class="payment-history small text-muted mt-1">
                        <strong>Payment History:</strong>
                        ${typePayments.map(payment => `
                            <div>• Paid Rs ${payment.amount.toFixed(2)} on ${new Date(payment.date).toLocaleDateString()}</div>
                        `).join('')}
                    </div>
                `;
            }
            let statusBadge, statusClass;
            const isDisabled = type.is_paid || remainingAmount <= 0;
            
            if (type.is_paid) {
                statusBadge = `<span class="badge bg-success ms-2">Paid</span>`;
                statusClass = 'text-success';
            } else if (paidAmount > 0) {
                statusBadge = `<span class="badge bg-info ms-2">Partial (Rs ${remainingAmount.toFixed(2)} remaining)</span>`;
                statusClass = 'text-info';
            } else {
                statusBadge = `<span class="badge bg-warning ms-2">Pending (Rs ${remainingAmount.toFixed(2)})</span>`;
                statusClass = 'text-warning';
            }
            
            feeTypesHtml += `
                <div class="fee-type-item d-flex flex-column mb-3 p-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <input type="checkbox" 
                                class="fee-type-checkbox me-2" 
                                value="${type.id}" 
                                data-amount="${remainingAmount}"
                                ${isDisabled ? 'disabled' : ''}
                                ${type.is_paid ? 'checked' : ''}
                                name="fee_types[]">
                            <span class="fee-type-name">${type.name}</span>
                            ${statusBadge}
                        </div>
                        <div class="fee-amounts">
                            <span class="fee-type-amount">Total: Rs ${type.amount.toFixed(2)}</span>
                            ${paidAmount > 0 ? 
                                `<span class="${statusClass} ms-2">(Paid: Rs ${paidAmount.toFixed(2)})</span>` : 
                                ''}
                        </div>
                    </div>
                    ${paymentHistoryHtml}
                </div>
            `;
        });

        const groupPaidPercentage = (groupPaidAmount / (groupPaidAmount + groupDueAmount)) * 100;
        
        feeGroupsHtml += `
            <div class="fee-group-item border p-3 mb-3">
                <h6 class="mb-3 d-flex justify-content-between align-items-center">
                    <span>${group.group_name}</span>
                    <span class="text-muted">Total: Rs ${group.total_amount.toFixed(2)}</span>
                </h6>
                <div class="fee-types">
                    ${feeTypesHtml}
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                    <strong>Group Summary:</strong>
                    <div>
                        <span class="text-success">Paid: Rs ${groupPaidAmount.toFixed(2)}</span>
                        <span class="text-warning ms-2">Due: Rs ${groupDueAmount.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;
    });

    $('#feeGroupsContainer').html(feeGroupsHtml);
    if (hasUnpaidFees) {
        $('#feeCollectionFormContainer').show();
        $('#collectFeeButton').show();
    } else {
        $('#feeCollectionFormContainer').hide();
        $('#collectFeeButton').hide();
    }

    $('.fee-type-checkbox').change(function() {
        updateSelectedAmount();
        const hasSelectedFees = $('.fee-type-checkbox:checked:not(:disabled)').length > 0;
        $('#feeCollectionFormContainer').toggle(hasSelectedFees);
        $('#collectFeeButton').toggle(hasSelectedFees);
    });
}

function updateSelectedAmount() {
    let totalSelectedAmount = 0;
    const selectedFeeTypes = [];
    
    $('.fee-type-checkbox:checked:not(:disabled)').each(function() {
        const amount = parseFloat($(this).data('amount'));
        const feeTypeId = $(this).val();
        totalSelectedAmount += amount;
        selectedFeeTypes.push({
            id: feeTypeId,
            amount: amount
        });
    });
    
    $('#selectedAmount').text(`Rs ${totalSelectedAmount.toFixed(2)}`);
    $('#selectedFeeTypes').val(JSON.stringify(selectedFeeTypes));
}

$('#collectFeeButton').click(function() {
    const selectedFeeTypes = $('.fee-type-checkbox:checked:not(:disabled)');
    if (selectedFeeTypes.length === 0) {
        alert('Please select at least one unpaid fee type to collect');
        return;
    }
    const selectedFees = [];
    selectedFeeTypes.each(function() {
        selectedFees.push({
            fee_groups_types_id: $(this).val(),
            amount: $(this).data('amount')
        });
    });

    const formData = {
        student_session_id: $('#studentSessionId').val(),
        payment_mode_id: $('#paymentMode').val(),
        payed_on: $('#nepali-datepicker').val(),
        notes: $('#notes').val(),
        selected_fees: selectedFees,
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    $.ajax({
        url: '/admin/fee-collections',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#feeCollectionModal').modal('hide');
                alert('Fees collected successfully!');
                $('#searchButton').click(); 
            } else {
                alert('Error collecting fees: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('Error response:', xhr.responseText);
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Please correct the following errors:\n';
                Object.keys(errors).forEach(key => {
                    errorMessage += `${errors[key]}\n`;
                });
                alert(errorMessage);
            } else {
                alert('Error collecting fees. Please try again.');
            }
        }
    });
});

$(document).ready(function() {
    $('select[name="class_id"]').change(function() {
        var classId = $(this).val();
        $.ajax({
            url: 'get-section-by-class/' + classId,
            type: 'GET',
            success: function(data) {
                $('select[name="section_id"]').empty();
                $('select[name="section_id"]').append(
                    '<option disabled>Select Section</option>');
                $.each(data, function(key, value) {
                    $('select[name="section_id"]').append('<option value="' +
                        key + '">' + value + '</option>');
                });
            }
        });
    });
});
</script>
@endsection