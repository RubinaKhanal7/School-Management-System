@extends('backend.layouts.master')
@section('content')
    <div class="mt-4">
        <div class="d-flex justify-content-between mb-4">
            <div class="border-bottom border-primary">
                <h2>{{ $page_title }}</h2>
            </div>
            <div>
                <a href="{{ route('admin.sources.index') }}" class="btn btn-primary">
                    Back to List
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.sources.update', $source->id) }}" id="sourceUpdateForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <!-- Source Title -->
                            <div class="mb-4">
                                <label class="form-label">Source Title<span class="must"> *</span></label>
                                <div class="input-group">
                                    <input type="text"
                                           name="source_title"
                                           class="form-control @error('source_title') is-invalid @enderror"
                                           value="{{ old('source_title', $source->source_title) }}"
                                           required>
                                    @error('source_title')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <!-- Source Description -->
                            <div class="mb-4">
                                <label class="form-label">Source Description<span class="must"> *</span></label>
                                <div class="input-group">
                                    <textarea name="source_description"
                                              rows="5"
                                              class="form-control @error('source_description') is-invalid @enderror"
                                              required>{{ old('source_description', $source->source_description) }}</textarea>
                                    @error('source_description')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <!-- Meta Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Created At:</label>
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control"
                                                   value="{{ $source->created_at->format('Y-m-d H:i:s') }}"
                                                   disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Last Updated:</label>
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control"
                                                   value="{{ $source->updated_at->format('Y-m-d H:i:s') }}"
                                                   disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Action Buttons -->
                            <div class="border-top pt-3 text-end">
                                <a href="{{ route('admin.sources.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-success">Update Source</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('styles')
<style>
    .must {
        color: red;
    }
    .form-label {
        font-weight: 500;
    }
    .input-group {
        position: relative;
    }
    .invalid-feedback {
        display: block;
        margin-top: 0.25rem;
    }
</style>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        // Form submission handling
        $('#sourceUpdateForm').on('submit', function(e) {
            e.preventDefault();
            // Remove any existing error messages
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');
            // Get form data
            var formData = $(this).serialize();
            // Submit form via AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Source updated successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('admin.sources.index') }}';
                        }
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        // Display validation errors
                        $.each(errors, function(key, value) {
                            var input = $('[name="' + key + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + value[0] + '</div>');
                        });
                    }
                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: 'There was an error updating the source',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });
</script>
@endsection