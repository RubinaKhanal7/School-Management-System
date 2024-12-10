@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h1>Accounts</h1>

    @can('create_accounts')
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" id="createAccountButton">
                Add Accounts <i class="fas fa-plus"></i>
            </button>
        </div>
    @endcan

    <table id="accounts-table" class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Opening Balance</th>
                <th>Balance Type</th>
                <th>Expense Header No</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Account Modal -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountModalLabel">Create Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="accountForm">
                    @csrf
                    <input type="hidden" id="accountId" name="id">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Account Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Account Code</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>

                    <div class="mb-3">
                        <label for="opening_balance" class="form-label">Opening Balance</label>
                        <input type="number" class="form-control" id="opening_balance" name="opening_balance" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="balance_type" class="form-label">Balance Type</label>
                        <select class="form-control" id="balance_type" name="balance_type" required>
                            <option value="DR">DR</option>
                            <option value="CR">CR</option>
                        </select>
                    </div>

                    
                    <div class="mb-3">
                        <label for="expense_header_no" class="form-label">Expense Header No</label>
                        <input type="number" class="form-control" id="expense_header_no" name="expense_header_no" min="1">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitAccountBtn">Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var table = $('#accounts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.accounts.index') }}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'opening_balance', name: 'opening_balance' },
            { data: 'balance_type', name: 'balance_type' },
            { data: 'expense_header_no', name: 'expense_header_no' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });


    $('#createAccountButton').click(function() {
        $('#accountModalLabel').text('Create Account');
        $('#accountForm')[0].reset();
        $('#accountId').val('');
        $('#submitAccountBtn').text('Save Account');
        $('#accountModal').modal('show');
    });

    $('#accountForm').submit(function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var accountId = $('#accountId').val();
        var url = accountId ? 
            "{{ route('admin.accounts.update', ':id') }}".replace(':id', accountId) : 
            "{{ route('admin.accounts.store') }}";
        var method = accountId ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                $('#accountModal').modal('hide');
                table.ajax.reload();
                toastr.success(response.message);
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(function(key) {
                    toastr.error(errors[key][0]);
                });
            }
        });
    });

    // Edit account
    $(document).on('click', '.edit-account', function() {
        var accountId = $(this).data('id');
        
        $.ajax({
            url: "{{ route('admin.accounts.edit', ':id') }}".replace(':id', accountId),
            type: 'GET',
            success: function(response) {
                $('#accountModalLabel').text('Edit Account');
                $('#accountId').val(response.id);
                $('#name').val(response.name);
                $('#code').val(response.code);
                $('#opening_balance').val(response.opening_balance);
                $('#balance_type').val(response.balance_type);
                $('#expense_header_no').val(response.expense_header_no);
                $('#status').val(response.status);
                $('#submitAccountBtn').text('Update Account');
                $('#accountModal').modal('show');
            },
            error: function(xhr) {
                toastr.error('Error loading account data');
            }
        });
    });

    // Delete account
    $(document).on('click', '.delete-account', function() {
        var accountId = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this account?')) {
            $.ajax({
                url: "{{ route('admin.accounts.destroy', ':id') }}".replace(':id', accountId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    table.ajax.reload();
                    toastr.success(response.message);
                },
                error: function(xhr) {
                    toastr.error('Error deleting account');
                }
            });
        }
    });
});
</script>
@endsection