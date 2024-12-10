@extends('backend.layouts.master')

@section('content')
    <div class="container">
        <h2>Voucher Types</h2>
        <table class="table" id="voucherTypesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated by DataTables -->
            </tbody>
        </table>
    </div>

    <!-- Edit Voucher Type Modal -->
    <div class="modal fade" id="editVoucherModal" tabindex="-1" aria-labelledby="editVoucherModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editVoucherModalLabel">Edit Voucher Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editVoucherForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="voucher_type_id" id="voucher_type_id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="code" name="code">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#voucherTypesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.voucher_types.index') }}', 
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });
            $('#voucherTypesTable').on('click', '.edit-btn', function() {
                var voucherTypeId = $(this).data('id');
                $.ajax({
                    url: '{{ url('admin/voucher_types') }}/' + voucherTypeId + '/edit',
                    method: 'GET',
                    success: function(response) {
                        $('#voucher_type_id').val(response.id);
                        $('#name').val(response.name);
                        $('#code').val(response.code);
                        $('#status').val(response.status);
                        $('#editVoucherModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching voucher type data.');
                    }
                });
            });

            $('#editVoucherForm').submit(function(e) {
                e.preventDefault();

                var voucherTypeId = $('#voucher_type_id').val();
                var formData = $(this).serialize();

                $.ajax({
                    url: '{{ url('admin/voucher_types') }}/' + voucherTypeId,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        $('#editVoucherModal').modal('hide');
                        table.ajax.reload();
                    },
                    error: function() {
                        alert('Error updating voucher type.');
                    }
                });
            });
        });
    </script>
@endsection
