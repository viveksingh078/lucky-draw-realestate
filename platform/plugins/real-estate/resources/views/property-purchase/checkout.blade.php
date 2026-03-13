@extends('plugins/real-estate::account.layouts.skeleton')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">🏠 Property Purchase Request</h2>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Review the details below and submit your purchase request. Admin will review and approve.
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Property Details -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">🏠 Property Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($property->image)
                                <img src="{{ RvMedia::getImageUrl($property->image) }}" alt="{{ $property->name }}" class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>{{ Str::title($property->name) }}</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ Str::title($property->location) }}</p>
                            <p>{{ Str::title(Str::limit($property->description, 150)) }}</p>
                            @if($property->square)
                                <p><strong>Area:</strong> {{ $property->square_text }}</p>
                            @endif
                            @if($property->number_bedroom)
                                <p><strong>Bedrooms:</strong> {{ $property->number_bedroom }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">💰 Price Breakdown</h4>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Property Price:</strong></td>
                            <td class="text-right"><strong>₹{{ number_format($propertyPrice, 0) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>GST (18%):</strong></td>
                            <td class="text-right"><strong>₹{{ number_format($gstAmount, 0) }}</strong></td>
                        </tr>
                        <tr class="border-top">
                            <td><strong>Subtotal:</strong></td>
                            <td class="text-right"><strong>₹{{ number_format($subtotal, 0) }}</strong></td>
                        </tr>
                        @if($lostDrawDiscount > 0)
                        <tr class="text-success">
                            <td><strong>Lost Draw Discount:</strong> <small>(auto-applied)</small></td>
                            <td class="text-right"><strong>-₹{{ number_format($lostDrawDiscount, 0) }}</strong></td>
                        </tr>
                        @endif
                        <tr class="text-info">
                            <td><strong>Wallet Discount:</strong> <small>(available: ₹{{ number_format($walletBalance, 0) }})</small></td>
                            <td class="text-right">
                                <div class="input-group" style="max-width: 200px; margin-left: auto;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">-₹</span>
                                    </div>
                                    <input type="number" class="form-control" id="wallet_discount" name="wallet_discount" 
                                           min="0" max="{{ $walletBalance }}" value="0" 
                                           onchange="calculateTotal()">
                                </div>
                            </td>
                        </tr>
                        <tr class="border-top bg-light">
                            <td><strong>Total Discount:</strong></td>
                            <td class="text-right"><strong id="total_discount">-₹{{ number_format($lostDrawDiscount, 0) }}</strong></td>
                        </tr>
                        <tr class="border-top bg-warning">
                            <td><h5><strong>FINAL AMOUNT:</strong></h5></td>
                            <td class="text-right"><h5><strong id="final_amount">₹{{ number_format($subtotal - $lostDrawDiscount, 0) }}</strong></h5></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Buyer Information -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">👤 Buyer Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name:</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone:</label>
                                <input type="text" class="form-control" value="{{ $user->phone }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>PAN Card:</label>
                                <input type="text" class="form-control" value="{{ $user->pan_card_number ?? 'Not provided' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Form -->
            <form action="{{ route('property.purchase.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="property_id" value="{{ $property->id }}">
                <input type="hidden" name="wallet_discount" id="wallet_discount_hidden" value="0">
                
                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-paper-plane"></i> Submit Purchase Request
                    </button>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        By submitting this request, you agree to purchase this property at the final amount shown above.
                        Admin will review and contact you for further process.
                    </small>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculateTotal() {
    const lostDrawDiscount = {{ $lostDrawDiscount }};
    const subtotal = {{ $subtotal }};
    const walletDiscount = parseFloat(document.getElementById('wallet_discount').value) || 0;
    
    const totalDiscount = lostDrawDiscount + walletDiscount;
    const finalAmount = subtotal - totalDiscount;
    
    document.getElementById('total_discount').textContent = '-₹' + totalDiscount.toLocaleString('en-IN');
    document.getElementById('final_amount').textContent = '₹' + finalAmount.toLocaleString('en-IN');
    document.getElementById('wallet_discount_hidden').value = walletDiscount;
}

// Initialize calculation
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>
@endsection