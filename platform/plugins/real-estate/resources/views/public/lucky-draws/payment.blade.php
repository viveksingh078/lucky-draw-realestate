@extends(Theme::getThemeNamespace() . '::views.template')

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Page Header -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold">💳 Complete Payment</h2>
                    <p class="text-muted">Complete your payment to confirm participation in the reward draw</p>
                </div>

                <!-- Draw Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="fw-bold mb-2">{{ $participant->draw->name }}</h4>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-home"></i> {{ $participant->draw->property->name ?? 'Premium Property' }}
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-calendar"></i> Draw Date: {{ $participant->draw->draw_date->format('M d, Y H:i') }}
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="bg-primary text-white rounded p-3 text-center">
                                    <h3 class="fw-bold mb-0">₹{{ number_format($participant->entry_fee_paid, 0) }}</h3>
                                    <small>Entry Fee</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="row">
                    <!-- QR Code Payment -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-qrcode"></i> Scan & Pay (Recommended)
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <img src="{{ $qrCodeUrl }}" alt="Payment QR Code" 
                                         class="img-fluid border rounded" 
                                         style="max-width: 200px;">
                                </div>
                                <h5 class="fw-bold text-success">₹{{ number_format($participant->entry_fee_paid, 0) }}</h5>
                                <p class="text-muted mb-2">Scan with any UPI app</p>
                                <div class="d-flex justify-content-center gap-2 mb-3">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/Google_Pay_Logo.svg" 
                                         alt="Google Pay" style="height: 30px;">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/PhonePe_Logo.svg" 
                                         alt="PhonePe" style="height: 30px;">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c7/Paytm_logo.svg" 
                                         alt="Paytm" style="height: 30px;">
                                </div>
                                <small class="text-muted">UPI ID: {{ $upiId }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Payment Info -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-mobile-alt"></i> Manual Payment
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">UPI ID:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{ $upiId }}" readonly>
                                        <button class="btn btn-outline-secondary" onclick="copyUpiId()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Amount:</label>
                                    <input type="text" class="form-control" value="₹{{ number_format($participant->entry_fee_paid, 0) }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Note:</label>
                                    <input type="text" class="form-control" value="Reward Draw: {{ $participant->draw->name }}" readonly>
                                </div>
                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-info-circle"></i>
                                        Copy the UPI ID and make payment through your UPI app
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Proof Form -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt"></i> Submit Payment Proof
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('public.lucky-draws.submit-payment', $participant->id) }}" 
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_utr" class="form-label">
                                            UTR/Transaction ID <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('payment_utr') is-invalid @enderror" 
                                               id="payment_utr" 
                                               name="payment_utr" 
                                               value="{{ old('payment_utr') }}" 
                                               placeholder="Enter 12-digit UTR number"
                                               required>
                                        @error('payment_utr')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="fas fa-info-circle"></i> 
                                            Find UTR number in your payment app's transaction history
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_screenshot" class="form-label">
                                            Payment Screenshot <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" 
                                               class="form-control @error('payment_screenshot') is-invalid @enderror" 
                                               id="payment_screenshot" 
                                               name="payment_screenshot" 
                                               accept="image/*"
                                               required>
                                        @error('payment_screenshot')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="fas fa-info-circle"></i> 
                                            Upload screenshot showing successful payment
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="alert alert-warning">
                                <h6 class="fw-bold">
                                    <i class="fas fa-exclamation-triangle"></i> Important Instructions:
                                </h6>
                                <ul class="mb-0">
                                    <li>Make sure the payment amount is exactly <strong>₹{{ number_format($participant->entry_fee_paid, 0) }}</strong></li>
                                    <li>Take a clear screenshot of the successful payment</li>
                                    <li>Enter the correct UTR/Transaction ID</li>
                                    <li>Payment verification may take 2-24 hours</li>
                                </ul>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-check"></i> Submit Payment Proof
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Need help? Contact us at 
                        <a href="tel:9876543210">9876543210</a> or 
                        <a href="mailto:support@propertyportal.com">support@propertyportal.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
<script>
function copyUpiId() {
    const upiInput = document.querySelector('input[value="{{ $upiId }}"]');
    upiInput.select();
    upiInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        alert('UPI ID copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
}

// Preview uploaded image
document.getElementById('payment_screenshot').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You can add image preview here if needed
            console.log('Image selected:', file.name);
        };
        reader.readAsDataURL(file);
    }
});
</script>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
}
</style>
@endpush