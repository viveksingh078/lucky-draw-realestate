@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <form action="{{ route('membership-plans.store') }}" method="POST">
            @csrf
            <div class="flexbox-annotated-section">
                <div class="flexbox-annotated-section-annotation">
                    <div class="annotated-section-title pd-all-20">
                        <h2>Create Membership Plan</h2>
                    </div>
                </div>

                <div class="flexbox-annotated-section-content">
                    <div class="wrapper-content pd-all-20">
                        <div class="form-group mb-3">
                            <label class="control-label required">Plan Name</label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="control-label required">Price (₹)</label>
                                    <input type="number" name="price" class="form-control" step="0.01" required value="{{ old('price') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="control-label required">Duration (Days)</label>
                                    <input type="number" name="duration_days" class="form-control" required value="{{ old('duration_days', 365) }}">
                                    <small class="text-muted">365 days = 1 year</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="control-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="control-label">Features</label>
                            <div id="features-container">
                                <div class="input-group mb-2">
                                    <input type="text" name="features[]" class="form-control" placeholder="Enter feature">
                                    <button type="button" class="btn btn-success btn-add-feature"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="control-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="control-label">Status</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Create Plan</button>
                            <a href="{{ route('membership-plans.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        $(document).on('click', '.btn-add-feature', function() {
            var html = '<div class="input-group mb-2">' +
                '<input type="text" name="features[]" class="form-control" placeholder="Enter feature">' +
                '<button type="button" class="btn btn-danger btn-remove-feature"><i class="fa fa-minus"></i></button>' +
                '</div>';
            $('#features-container').append(html);
        });

        $(document).on('click', '.btn-remove-feature', function() {
            $(this).closest('.input-group').remove();
        });
    </script>
@endsection
