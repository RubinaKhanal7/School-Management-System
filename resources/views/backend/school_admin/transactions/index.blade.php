@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h2>Transactions</h2>

    <div class="d-flex justify-content-end mb-2">
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#transactionModal">
            Add Transaction <i class="fas fa-plus"></i>
        </button>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <div>
            <a href="{{ route('admin.transactions.export.excel') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Transaction Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="transactionForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Transaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="hidden" class="form-control" name="transaction_date_eng" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="text" class="form-control" name="transaction_date_nepali" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Voucher Type</label>
                                <select class="form-select" name="voucher_type_id" required>
                                    @foreach($voucherTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="transactionEntries" class="row">
                            <!-- First Entry (DR) -->
                            <div class="transaction-entry col-12 row mb-2">
                                <div class="col-md-5">
                                    <select class="form-select" name="accounts[]" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select type-select" name="types[]" required>
                                        <option value="dr" selected>DR</option>
                                        <option value="cr">CR</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control amount-input" name="amounts[]" placeholder="Amount" min="0" value="0" required>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-entry" style="display:none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Second Entry (CR) -->
                            <div class="transaction-entry col-12 row  mb-1">
                                <div class="col-md-5">
                                    <select class="form-select" name="accounts[]" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select type-select" name="types[]" required>
                                        <option value="dr">DR</option>
                                        <option value="cr" selected>CR</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control amount-input" name="amounts[]" placeholder="Amount" min="0" value="0" required>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-entry" style="display:none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                        </div>
                       
                    </div>
                    <div class="col-10 d-flex flex-end justify-content-end">
                        <button type="button" class="btn btn-info btn-sm" id="addEntry">
                            Add Entry <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="col-11 mx-2 mb-4">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="1"></textarea>
                    </div>
                
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="submitTransaction" disabled>Save Transaction</button>
                        </div>
                    </div>
                   
                </form>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table id="transactionsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Voucher No</th>
                        <th>Date</th>
                        <th>Voucher Type</th>
                        <th>Account</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    window.accounts = {!! $accounts->toJson() !!};

    let table = $('#transactionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.transactions.data') }}",
        columns: [
            {data: 'voucher_no', name: 'transactions.voucher_no'},
            {data: 'transaction_date_nepali', name: 'transactions.transaction_date_nepali'},
            {data: 'voucher_type', name: 'voucher_type'},
            {data: 'account_name', name: 'account_names'},
            {
                data: 'total_debit',
                name: 'total_debit',
                render: $.fn.dataTable.render.number(',', '.', 2)
            },
            {
                data: 'total_credit',
                name: 'total_credit',
                render: $.fn.dataTable.render.number(',', '.', 2)
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }
        ],
        order: [[1, 'desc']],
    });

    $('input[name="transaction_date_nepali"]').nepaliDatePicker({
        dateFormat: 'YYYY-MM-DD',
        closeOnDateSelect: true
    });

    const today = new Date();
    $('input[name="transaction_date_eng"]').val(today.toISOString().split('T')[0]);
    const npToday = NepaliFunctions.GetCurrentBsDate();
    const npDate = `${npToday.year}-${String(npToday.month).padStart(2, '0')}-${String(npToday.day).padStart(2, '0')}`;
    $('input[name="transaction_date_nepali"]').val(npDate);

    const formatNumber = (num) => parseFloat(num).toFixed(2);

    function generateAccountOptions(selectedId = '') {
        return window.accounts.map(account => 
            `<option value="${account.id}" ${account.id === selectedId ? 'selected' : ''}>
                ${account.name}
            </option>`
        ).join('');
    }

    $('#addEntry').click(function() {
        const entryTemplate = `
            <div class="transaction-entry col-12 row mb-2">
                <div class="col-md-5">
                    <select class="form-select" name="accounts[]" required>
                        <option value="">Select Account</option>
                        ${generateAccountOptions()}
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select type-select" name="types[]" required>
                        <option value="dr">DR</option>
                        <option value="cr">CR</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control amount-input" 
                           name="amounts[]" placeholder="Amount" min="0" value="0" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-entry">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        $('#transactionEntries').append(entryTemplate);
        updateTotals();
    });

    $(document).on('click', '.edit-transaction', function() {
        const voucherNo = $(this).data('voucher');
        resetForm();

        $('#transactionForm').attr('data-voucher', voucherNo);
        $('.modal-title').text('Edit Transaction');
        $('#submitTransaction').text('Update Transaction');

        $.ajax({
            url: `/admin/transactions/${voucherNo}/edit`,
            type: 'GET',
            success: function(response) {
                const transaction = response.transaction;
                const entries = response.entries;

                $('input[name="transaction_date_eng"]').val(transaction.transaction_date_eng);
                $('input[name="transaction_date_nepali"]').val(transaction.transaction_date_nepali);
                $('select[name="voucher_type_id"]').val(transaction.voucher_type_id);
                $('textarea[name="description"]').val(transaction.description || '');

                $('#transactionEntries').empty();

                entries.forEach((entry, index) => {
                    const entryTemplate = `
                        <div class="transaction-entry col-12 row mb-2">
                            <div class="col-md-5">
                                <select class="form-select" name="accounts[]" required>
                                    <option value="">Select Account</option>
                                    ${generateAccountOptions(entry.account_id)}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select type-select" name="types[]" required>
                                    <option value="dr" ${entry.type === 'dr' ? 'selected' : ''}>DR</option>
                                    <option value="cr" ${entry.type === 'cr' ? 'selected' : ''}>CR</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control amount-input" 
                                       name="amounts[]" placeholder="Amount" min="0" 
                                       value="${formatNumber(entry.amount)}" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-entry" 
                                        ${index < 2 ? 'style="display:none;"' : ''}>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    $('#transactionEntries').append(entryTemplate);
                });

                updateTotals();
                $('#transactionModal').modal('show');
            },
            error: function(xhr) {
                toastr.error('Error loading transaction details');
            }
        });
    });

    $('#transactionForm').submit(function(e) {
        e.preventDefault();
        
        const voucherNo = $(this).data('voucher');
        const isUpdate = !!voucherNo;
        const url = isUpdate ? `/admin/transactions/${voucherNo}` : '{{ route("admin.transactions.store") }}';
        const method = isUpdate ? 'PUT' : 'POST';

        let totalDr = 0;
        let totalCr = 0;
        $('.transaction-entry').each(function() {
            const amount = parseFloat($(this).find('input[name="amounts[]"]').val()) || 0;
            const type = $(this).find('select[name="types[]"]').val();
            if (type === 'dr') totalDr += amount;
            if (type === 'cr') totalCr += amount;
        });

        if (Math.abs(totalDr - totalCr) > 0.01) {
            toastr.error('Total debit must equal total credit');
            return;
        }

        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#transactionModal').modal('hide');
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error saving transaction');
            }
        });
    });

    function updateTotals() {
        let totalDr = 0;
        let totalCr = 0;
        let hasZeroAmount = false;
        let hasEmptyAccount = false;

        $('.transaction-entry').each(function() {
            const amount = parseFloat($(this).find('.amount-input').val()) || 0;
            const accountId = $(this).find('select[name="accounts[]"]').val();
            
            if (amount === 0) hasZeroAmount = true;
            if (!accountId) hasEmptyAccount = true;
            
            const type = $(this).find('.type-select').val();
            if (type === 'dr') totalDr += amount;
            if (type === 'cr') totalCr += amount;
        });

        const isBalanced = Math.abs(totalDr - totalCr) < 0.01;
        const hasEntries = totalDr > 0 || totalCr > 0;

        $('#submitTransaction').prop('disabled', 
            !isBalanced || !hasEntries || hasZeroAmount || hasEmptyAccount
        );
    }

    function resetForm() {
        $('#transactionForm')[0].reset();
        $('#transactionEntries').empty();
        $('#transactionForm').removeAttr('data-voucher');
        $('.modal-title').text('Add New Transaction');
        $('#submitTransaction').text('Save Transaction').prop('disabled', true);

        ['dr', 'cr'].forEach(type => {
            const entryTemplate = `
                <div class="transaction-entry col-12 row mb-2">
                    <div class="col-md-5">
                        <select class="form-select" name="accounts[]" required>
                            <option value="">Select Account</option>
                            ${generateAccountOptions()}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select type-select" name="types[]" required>
                            <option value="dr" ${type === 'dr' ? 'selected' : ''}>DR</option>
                            <option value="cr" ${type === 'cr' ? 'selected' : ''}>CR</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control amount-input" 
                               name="amounts[]" placeholder="Amount" min="0" value="0" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-entry" style="display:none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#transactionEntries').append(entryTemplate);
        });
        
        updateTotals();

        const today = new Date();
        $('input[name="transaction_date_eng"]').val(today.toISOString().split('T')[0]);
        const npToday = NepaliFunctions.GetCurrentBsDate();
        const npDate = `${npToday.year}-${String(npToday.month).padStart(2, '0')}-${String(npToday.day).padStart(2, '0')}`;
        $('input[name="transaction_date_nepali"]').val(npDate);
    }

    $(document).on('click', '.remove-entry', function() {
        if ($('.transaction-entry').length > 2) {
            $(this).closest('.transaction-entry').remove();
            updateTotals();
        }
    });

    $(document).on('input', '.amount-input', updateTotals);
    $(document).on('change', '.type-select, select[name="accounts[]"]', updateTotals);
    $('#transactionModal').on('hidden.bs.modal', resetForm);
});
</script>
@endsection