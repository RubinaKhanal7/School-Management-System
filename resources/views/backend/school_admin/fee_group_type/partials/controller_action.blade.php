@can('edit_fee_grouptypes')
    <a href="#" class="btn btn-outline-primary btn-sm mx-1 edit-fee-group-type" data-id="{{ $feeGroupType->id }}"
        data-amount="{{ $feeGroupType->amount }}" data-fee_type_id="{{ $feeGroupType->fee_type_id }}" data-fee_group_id="{{ $feeGroupType->fee_group_id }}"
        data-academic_session_id="{{ $feeGroupType->academic_session_id }}" data-is_active="{{ $feeGroupType->is_active }}"
        data-toggle="tooltip" data-placement="top" title="Edit">
        <i class="fa fa-edit"></i>
    </a>
@endcan

@can('delete_fee_grouptypes')
    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
        data-bs-target="#delete{{ $feeGroupType->id }}" data-toggle="tooltip" data-placement="top" title="Delete">
        <i class="far fa-trash-alt"></i>
    </button>

    <div class="modal fade" id="delete{{ $feeGroupType->id }}" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.fee-grouptypes.destroy', $feeGroupType->id) }}" accept-charset="UTF-8"
                    method="POST">
                    <div class="modal-body">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                        <p>Are you sure to delete <span id="underscore" class="must"> {{ $feeGroupType->name }} </span>?</p>
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