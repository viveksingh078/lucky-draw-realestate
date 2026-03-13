@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>Edit Reward Draw</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">Update reward draw information</p>
                    <a href="{{ route('lucky-draws.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <form action="{{ route('lucky-draws.update', $draw->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-3">
                            <label for="name" class="control-label required">Draw Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $draw->name) }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="draw_type" class="control-label required">Draw Type</label>
                                    <select class="form-control" id="draw_type" name="draw_type" required>
                                        <option value="weekly" {{ old('draw_type', $draw->draw_type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('draw_type', $draw->draw_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="property_id" class="control-label required">Select Property</label>
                                    <select class="form-control" id="property_id" name="property_id" required>
                                        <option value="">-- Select Property --</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->id }}" {{ old('property_id', $draw->property_id) == $property->id ? 'selected' : '' }}>
                                                {{ $property->name }} - {{ $property->location }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="property_value" class="control-label required">Property Value (₹)</label>
                                    <input type="number" class="form-control" id="property_value" name="property_value" value="{{ old('property_value', $draw->property_value) }}" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="entry_fee" class="control-label required">Entry Fee (₹)</label>
                                    <input type="number" class="form-control" id="entry_fee" name="entry_fee" value="{{ old('entry_fee', $draw->entry_fee) }}" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="start_date" class="control-label required">Start Date</label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $draw->start_date->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="end_date" class="control-label required">End Date</label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $draw->end_date->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="draw_date" class="control-label required">Draw Date</label>
                                    <input type="datetime-local" class="form-control" id="draw_date" name="draw_date" value="{{ old('draw_date', $draw->draw_date->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="control-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $draw->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Draw
                            </button>
                            <a href="{{ route('lucky-draws.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
