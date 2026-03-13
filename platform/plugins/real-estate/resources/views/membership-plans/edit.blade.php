@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <form action="{{ route('membership-plans.update', $plan->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="flexbox-annotated-section">
                <div class="flexbox-annotated-section-annotation">
                    <div class="annotated-section-title pd-all-20">
                        <h2>Edit Membership Plan</h2>
                    </div>
                </div>

                <div class="flexbox-annotated-section-content">
                    <div class="wrapper-content pd-all-20">
                        <div class="form-group mb-3">
                            <label class="control-label required">Plan Name</label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name', $plan->name) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="control-label required">Price (₹)</label>
                                    <input type="number" name="price" class="form-control" step="0.01" required value="{{ old('price', $plan->price) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="control-label required">Duration (Days)</label>
                                    <input type="number" name="duration_days" class="form-control" required value="{{ old('duration_days', $plan->duration_days) }}">
                                    <small class="text-muted">365 days = 1 year</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="control-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $plan->description) }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="control-label">Features</label>
                            <div id="features-container">
                                @php
                                    $features = json_decode($plan->features, true) ?: [''];
                                @endphp
                                @foreach($features as $index => $feature)
                                    <div class="input-group mb-2">
                                        <input type="text" name="features[]" class="form-control" placeholder="Enter feature" value="{{ $feature }}">
                                        @if($index == 0)
                                            <button type="button" class="btn btn-success btn-add-feature"><i class="fa fa-plus"></i></button>
                                        @else
                                            <button type="button" class="btn btn-danger btn-remove-feature"><i class="fa fa-minus"></i></button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="control-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $plan->sort_order) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="control-label">Status</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1" {{ $plan->is_active ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$plan->is_active ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Plan</button>
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
