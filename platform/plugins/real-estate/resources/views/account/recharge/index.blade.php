@extends('plugins/real-estate::account.layouts.skeleton')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">💰 Add Credit to Wallet</h2>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Select a plan below to recharge your wallet. After payment, submit the details for admin approval.
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

            <!-- Plan Selection -->
            <div class="row mb-4">
                @foreach($plans as $plan)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 plan-card" id="plan-card-{{ $plan->id }}" style="cursor: pointer; transition: all 0.3s;" onclick="selectPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }}, {{ $plan->draws_allowed }})">
                        <div class="card-header text-center bg-light">
                            <h4 class="mb-0">{{ $plan->name }}</h4>
                            @if($loop->index == 1)
                                <span class="badge badge-warning">POPULAR</span>
                            @endif
                        </div>
                        <div class="card-body text-center">
                            <h2 class="text-primary">₹{{ number_format($plan->price, 0) }}</h2>
                            <hr>
                            <p class="mb-2"><i class="fas fa-check text-success"></i> {{ $plan->draws_allowed }} Draw Credits</p>
                            <p class="mb-2"><i class="fas fa-check text-success"></i> ₹{{ number_format($plan->credit_value, 0) }} per draw</p>
                            <p class="mb-2"><i class="fas fa-check text-success"></i> {{ $plan->max_concurrent_draws }} concurrent draws</p>
                            <hr>
                            <button type="button" class="btn btn-primary btn-block" onclick="selectPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }}, {{ $plan->draws_allowed }}); event.stopPropagation();">
                                Select Plan
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Payment Form (Hidden initially, or shown if there are errors) -->
            <div id="paymentSection" style="display: {{ old('membership_plan_id') || $errors->any() ? 'block' : 'none' }};">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">💳 Payment Details</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('public.account.recharge.submit') }}" method="POST" enctype="multipart/form-data" id="rechargeForm">
                            @csrf
                            <input type="hidden" name="membership_plan_id" id="selected_plan_id" value="{{ old('membership_plan_id') }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Selected Plan</h5>
                                    <div class="alert alert-success">
                                        <p class="mb-1"><strong>Plan:</strong> <span id="display_plan_name">{{ old('membership_plan_id') ? $plans->find(old('membership_plan_id'))->name : '' }}</span></p>
                                        <p class="mb-1"><strong>Amount:</strong> ₹<span id="display_amount">{{ old('membership_plan_id') ? number_format($plans->find(old('membership_plan_id'))->price, 0) : '' }}</span></p>
                                        <p class="mb-0"><strong>Credits:</strong> <span id="display_credits">{{ old('membership_plan_id') ? $plans->find(old('membership_plan_id'))->draws_allowed : '' }}</span> draws</p>
                                    </div>

                                    <h5 class="mt-4">Payment QR Code</h5>
                                    <div id="qrCodeContainer" class="text-center mb-3">
                                        @if(old('membership_plan_id'))
                                            <div class="spinner-border text-primary" role="status"></div>
                                            <p class="mt-2">Loading QR Code...</p>
                                        @else
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <p class="mt-2">Loading QR Code...</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h5>Submit Payment Proof</h5>
                                    
                                    <div class="form-group">
                                        <label for="payment_utr_number">UTR Number / Transaction ID <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('payment_utr_number') is-invalid @enderror" id="payment_utr_number" name="payment_utr_number" required placeholder="Enter 12-22 digit UTR number" pattern="[0-9]{12,22}" maxlength="22" onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="{{ old('payment_utr_number') }}">
                                        <small class="form-text text-muted">Enter the transaction reference number from your payment app (Only numbers allowed, 12-22 digits)</small>
                                        @error('payment_utr_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_screenshot">Payment Screenshot <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control-file" id="payment_screenshot" name="payment_screenshot" accept="image/*" required>
                                        <small class="form-text text-muted">Upload screenshot of successful payment (JPG, PNG - Max 2MB)</small>
                                    </div>

                                    <div class="form-group">
                                        <img id="screenshot_preview" src="" alt="Preview" style="max-width: 100%; display: none; margin-top: 10px; border: 1px solid #ddd; padding: 5px;">
                                    </div>

                                    <button type="submit" class="btn btn-success btn-lg btn-block">
                                        <i class="fas fa-paper-plane"></i> Submit for Approval
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="cancelPayment()">
                                        Cancel
                                    </button>
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
// Load QR code on page load if there's an old plan selected (after validation error)
document.addEventListener('DOMContentLoaded', function() {
    const oldPlanId = '{{ old("membership_plan_id") }}';
    if (oldPlanId) {
        loadQRCode(oldPlanId);
        // Highlight the selected plan card
        const selectedCard = document.getElementById('plan-card-' + oldPlanId);
        if (selectedCard) {
            selectedCard.classList.add('border-primary');
            selectedCard.style.boxShadow = '0 0 20px rgba(0,123,255,0.5)';
            selectedCard.style.transform = 'scale(1.05)';
            const selectedHeader = selectedCard.querySelector('.card-header');
            selectedHeader.classList.remove('bg-light');
            selectedHeader.classList.add('bg-primary', 'text-white');
        }
    }
});

function selectPlan(planId, planName, amount, credits) {
    // Remove highlight from all cards
    document.querySelectorAll('.plan-card').forEach(card => {
        card.classList.remove('border-primary');
        card.style.boxShadow = 'none';
        card.style.transform = 'scale(1)';
        // Reset header
        const header = card.querySelector('.card-header');
        header.classList.remove('bg-primary', 'text-white');
        header.classList.add('bg-light');
    });
    
    // Highlight selected card
    const selectedCard = document.getElementById('plan-card-' + planId);
    selectedCard.classList.add('border-primary');
    selectedCard.style.boxShadow = '0 0 20px rgba(0,123,255,0.5)';
    selectedCard.style.transform = 'scale(1.05)';
    // Highlight header
    const selectedHeader = selectedCard.querySelector('.card-header');
    selectedHeader.classList.remove('bg-light');
    selectedHeader.classList.add('bg-primary', 'text-white');
    
    // Set form values
    document.getElementById('selected_plan_id').value = planId;
    document.getElementById('display_plan_name').textContent = planName;
    document.getElementById('display_amount').textContent = amount.toLocaleString('en-IN');
    document.getElementById('display_credits').textContent = credits;

    // Show payment section
    document.getElementById('paymentSection').style.display = 'block';
    
    // Scroll to payment section
    document.getElementById('paymentSection').scrollIntoView({ behavior: 'smooth' });

    // Load QR code
    loadQRCode(planId);
}

function loadQRCode(planId) {
    const qrContainer = document.getElementById('qrCodeContainer');
    qrContainer.innerHTML = '<div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading QR Code...</p>';
    
    fetch('{{ route("public.account.membership.qr-code") }}?plan_id=' + planId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                qrContainer.innerHTML = '<div class="alert alert-danger">Failed to load QR code</div>';
                return;
            }
            
            qrContainer.innerHTML = `
                <div class="card" style="max-width: 320px; margin: 0 auto;">
                    <div class="card-body text-center">
                        <img src="${data.data.qr_code_url}" alt="Payment QR Code" style="max-width: 250px; border: 2px solid #28a745; padding: 10px; border-radius: 10px; background: white;" class="img-fluid">
                        <h3 class="mt-3 text-success">₹${data.data.amount.toLocaleString('en-IN')}</h3>
                        <p class="mb-1"><strong>Scan & Pay</strong></p>
                        <p class="small text-muted mb-0">UPI ID: ${data.data.upi_id}</p>
                        <p class="small text-primary">Plan: ${data.data.plan_name}</p>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading QR code:', error);
            qrContainer.innerHTML = '<div class="alert alert-danger">Failed to load QR code. Please try again.</div>';
        });
}

function cancelPayment() {
    // Remove highlight from all cards
    document.querySelectorAll('.plan-card').forEach(card => {
        card.classList.remove('border-primary');
        card.style.boxShadow = 'none';
        card.style.transform = 'scale(1)';
        const header = card.querySelector('.card-header');
        header.classList.remove('bg-primary', 'text-white');
        header.classList.add('bg-light');
    });
    
    document.getElementById('paymentSection').style.display = 'none';
    document.getElementById('rechargeForm').reset();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Preview screenshot
document.getElementById('payment_screenshot').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('screenshot_preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
