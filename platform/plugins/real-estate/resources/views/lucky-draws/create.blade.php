@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>Create Reward Draw</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">Create a new reward draw for properties</p>
                    <a href="{{ route('lucky-draws.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <form action="{{ route('lucky-draws.store') }}" method="POST">
                        @csrf
                                
                                <div class="row">
                                    <!-- Draw Name -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Draw Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" 
                                                   placeholder="e.g., January Weekly Draw #1">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Draw Type -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="draw_type" class="form-label">Draw Type <span class="text-danger">*</span></label>
                                            <select class="form-select @error('draw_type') is-invalid @enderror" 
                                                    id="draw_type" name="draw_type">
                                                <option value="">Select Type</option>
                                                <option value="weekly" {{ old('draw_type') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                <option value="monthly" {{ old('draw_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            </select>
                                            @error('draw_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Property Selection -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="property_id" class="form-label">Select Property <span class="text-danger">*</span></label>
                                            <select class="form-select @error('property_id') is-invalid @enderror" 
                                                    id="property_id" name="property_id" onchange="updatePropertyValue()">
                                                <option value="">Choose Property</option>
                                                @foreach($properties as $property)
                                                    <option value="{{ $property->id }}" 
                                                            data-price="{{ $property->price }}"
                                                            {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                                        {{ $property->name }} - {{ $property->location }} (₹{{ number_format($property->price, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('property_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Property Value -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="property_value" class="form-label">Property Value (₹) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('property_value') is-invalid @enderror" 
                                                   id="property_value" name="property_value" value="{{ old('property_value') }}" 
                                                   step="0.01" min="1" placeholder="5000000">
                                            @error('property_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Entry Fee -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="entry_fee" class="form-label">Entry Fee (₹) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('entry_fee') is-invalid @enderror" 
                                                   id="entry_fee" name="entry_fee" value="{{ old('entry_fee') }}" 
                                                   step="0.01" min="1" placeholder="999">
                                            @error('entry_fee')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <span id="participants_needed">0</span> participants needed to break even
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Start Date -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" value="{{ old('start_date') }}">
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" name="end_date" value="{{ old('end_date') }}">
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Draw Date -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="draw_date" class="form-label">Draw Date <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('draw_date') is-invalid @enderror" 
                                                   id="draw_date" name="draw_date" value="{{ old('draw_date') }}">
                                            @error('draw_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="4" 
                                                      placeholder="Describe this reward draw...">{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Profit/Loss Calculator -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <h5><i class="fas fa-calculator"></i> Profit/Loss Calculator</h5>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>Property Value:</strong><br>
                                                    ₹<span id="calc_property_value">0</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Entry Fee:</strong><br>
                                                    ₹<span id="calc_entry_fee">0</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Break-even Point:</strong><br>
                                                    <span id="calc_breakeven">0</span> participants
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Profit Margin:</strong><br>
                                                    <span id="calc_profit_margin">0%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="text-end">
                                            <a href="{{ route('lucky-draws.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Create Draw
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function updatePropertyValue() {
    const select = document.getElementById('property_id');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    
    if (price) {
        document.getElementById('property_value').value = price;
        calculateBreakeven();
    }
}

function calculateBreakeven() {
    const propertyValue = parseFloat(document.getElementById('property_value').value) || 0;
    const entryFee = parseFloat(document.getElementById('entry_fee').value) || 0;
    
    if (propertyValue > 0 && entryFee > 0) {
        const breakeven = Math.ceil(propertyValue / entryFee);
        const profitMargin = ((entryFee * breakeven - propertyValue) / propertyValue * 100).toFixed(2);
        
        document.getElementById('participants_needed').textContent = breakeven;
        document.getElementById('calc_property_value').textContent = propertyValue.toLocaleString();
        document.getElementById('calc_entry_fee').textContent = entryFee.toLocaleString();
        document.getElementById('calc_breakeven').textContent = breakeven;
        document.getElementById('calc_profit_margin').textContent = profitMargin + '%';
    }
}

// Event listeners
document.getElementById('property_value').addEventListener('input', calculateBreakeven);
document.getElementById('entry_fee').addEventListener('input', calculateBreakeven);

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const tomorrow = new Date(now.getTime() + 24 * 60 * 60 * 1000);
    const nextWeek = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
    
    if (!document.getElementById('start_date').value) {
        document.getElementById('start_date').value = tomorrow.toISOString().slice(0, 16);
    }
    if (!document.getElementById('end_date').value) {
        document.getElementById('end_date').value = nextWeek.toISOString().slice(0, 16);
    }
    if (!document.getElementById('draw_date').value) {
        const drawDate = new Date(nextWeek.getTime() + 24 * 60 * 60 * 1000);
        document.getElementById('draw_date').value = drawDate.toISOString().slice(0, 16);
    }
});
</script>
@endsection