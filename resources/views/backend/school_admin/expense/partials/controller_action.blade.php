@can('edit_expenses')
    <a href="#" class="btn btn-outline-primary btn-sm mx-1 edit-expense" data-id="{{ $expense->id }}"
        data-expensehead_id="{{ $expense->expensehead_id }}" data-name="{{ $expense->name }}"
        data-invoice_number="{{ $expense->invoice_number }}" data-date="{{ $expense->date }}"
        data-amount="{{ $expense->amount }}" data-description="{{ $expense->description }}"
        data-document="{{ $expense->document }}" data-is_active="{{ $expense->is_active }}" data-toggle="tooltip"
        data-placement="top" title="Edit">
        <i class="fa fa-edit"></i>
    </a>
@endcan

@can('delete_expenses')
    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
        data-bs-target="#delete{{ $expense->id }}" data-toggle="tooltip" data-placement="top" title="Delete">
        <i class="far fa-trash-alt"></i>
    </button>

    <div class="modal fade" id="delete{{ $expense->id }}" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.expenses.destroy', $expense->id) }}" accept-charset="UTF-8"
                    method="POST">
                    <div class="modal-body">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                        <p>Are you sure to delete <span id="underscore"> {{ $expense->name }} </span>?</p>
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
