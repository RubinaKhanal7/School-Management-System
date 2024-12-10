@extends('backend.layouts.master')
@section('content')
    <div class="mt-4">
        <div class="d-flex justify-content-between mb-4">
            <div class="border-bottom border-primary">
                <h2>{{ $page_title }}</h2>
            </div>
            <div>
                <a href="{{ url()->previous() }}"><button class="btn-primary btn-sm"><i class="fa fa-angle-double-left"></i>
                    Back</button></a>
                <button type="button" class="btn btn-block btn-success btn-sm" data-bs-toggle="modal"
                data-bs-target="#createSource">
                Add Source <i class="fas fa-plus"></i>
            </button>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div id="example1_wrapper" class="dataTables_wrapper dt-bootstrap4">
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-12">
                            <div class="report-table-container">
                                <div class="table-responsive">
                                    <table id="source-table" class="table table-bordered table-striped dataTable dtr-inline"
                                        aria-describedby="example1_info">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="createSource" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add Source</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="">
                        <form method="post" id="sourceForm" action="{{ route('admin.sources.store') }}">
                            @csrf
                            <input type="hidden" name="_method" id="methodField" value="POST">
                            <input type="hidden" name="dynamic_id" id="dynamic_id">
                            <div class="col-md-12">
                                <div class="p-2 label-input">
                                    <label>Source Title<span class="must"> *</span></label>
                                    <div class="single-input-modal">
                                        <input type="text" value="{{ old('source_title') }}" name="source_title"
                                            class="input-text single-input-text" id="dynamic_title" autofocus required>
                                    </div>
                                </div>
                                <div class="p-2 label-input">
                                    <label>Source Description<span class="must"> *</span></label>
                                    <div class="single-input-modal">
                                        <textarea name="source_description" class="input-text single-input-text"
                                            id="dynamic_description" rows="4" required>{{ old('source_description') }}</textarea>
                                    </div>
                                </div>
                                <div class="border-top col-md-12 d-flex justify-content-end p-2">
                                    <button type="submit" class="btn btn-sm btn-success mt-2">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('scripts')
    <script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let table = $('#source-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.sources.get") }}',
                type: 'POST',
                error: function (xhr, error, thrown) {
                    console.error('DataTables error:', error);
                    console.error('Details:', thrown);
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'source_title', name: 'source_title' },
                { data: 'source_description', name: 'source_description' },
                { 
                    data: 'actions', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false 
                }
            ],
            order: [[0, 'desc']], 
            responsive: true,
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
            }
        });

        $(document).on('click', '.edit-source', function() {
            var id = $(this).data('id');
            var title = $(this).data('title');
            var description = $(this).data('description');
            
            $('#dynamic_id').val(id);
            $('#dynamic_title').val(title);
            $('#dynamic_description').val(description);
            $('#sourceForm').attr('action', '{{ route("admin.sources.update", "") }}/' + id);
            $('#methodField').val('PUT');
            $('#createSource').modal('show');
        });

        $('#sourceForm').on('submit', function() {
            $(this).find('button[type="submit"]').prop('disabled', true);
        });
    });
    </script>
    @endsection
    @endsection