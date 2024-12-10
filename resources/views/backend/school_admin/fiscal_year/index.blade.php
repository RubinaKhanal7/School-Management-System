@extends('backend.layouts.master')

@section('content')
    <div class="container">
        <h3 class="mb-3">Fiscal Years</h3>
        
        <table class="table table-bordered" id="fiscalYearTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated by AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editFiscalYearModal" tabindex="-1" aria-labelledby="editFiscalYearModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFiscalYearModalLabel">Edit Fiscal Year</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFiscalYearForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="fiscalYearId">
                        <div class="mb-3">
                            <label for="name" class="form-label">Fiscal Year Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
        $(document).ready(function() {
            var table = $('#fiscalYearTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('admin.fiscal-years.index') !!}', 
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'from_date_nepali', name: 'from_date_nepali' },
                    { data: 'to_date_nepali', name: 'to_date_nepali' },
                    { data: 'status', name: 'status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            // Edit button click event
            $(document).on('click', '.edit-btn', function() {
                var fiscalYearId = $(this).data('id');
                var name = $(this).data('name');
                var fromDate = $(this).data('from-date');
                var toDate = $(this).data('to-date');
                var status = $(this).data('status');

                $('#fiscalYearId').val(fiscalYearId);
                $('#name').val(name);
                $('#from_date').val(fromDate);
                $('#to_date').val(toDate);
                $('#status').val(status);

                $('#editFiscalYearModal').modal('show');
            });

            $('#editFiscalYearForm').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = form.serialize();
                var fiscalYearId = $('#fiscalYearId').val();

                $.ajax({
                    url: '/admin/fiscal-years/' + fiscalYearId,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#editFiscalYearModal').modal('hide');
                            table.ajax.reload();
                        } else {
                            alert('Failed to update fiscal year');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection
