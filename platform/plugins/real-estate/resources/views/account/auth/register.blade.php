@extends('plugins/real-estate::account.layouts.skeleton')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card login-form">
                    <div class="card-body">
                        <h4 class="text-center">{{ trans('plugins/real-estate::dashboard.register-title') }}</h4>
                        <br>
                        <form method="POST" action="{{ route('public.account.register') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Basic Information -->
                            <h5 class="mb-3">Basic Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <input id="first_name" type="text"
                                               class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}"
                                               name="first_name" value="{{ old('first_name') }}" required autofocus
                                               placeholder="{{ trans('plugins/real-estate::dashboard.first_name') }}">
                                        @if ($errors->has('first_name'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('first_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <input id="last_name" type="text"
                                               class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}"
                                               name="last_name" value="{{ old('last_name') }}" required
                                               placeholder="{{ trans('plugins/real-estate::dashboard.last_name') }}">
                                        @if ($errors->has('last_name'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('last_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <input id="username" type="text"
                                       class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}"
                                       name="username" value="{{ old('username') }}" required
                                       placeholder="{{ trans('plugins/real-estate::dashboard.username') }}">
                                @if ($errors->has('username'))
                                    <span class="invalid-feedback">
                                    <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <input id="email" type="email"
                                               class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                               name="email" value="{{ old('email') }}" required
                                               placeholder="{{ trans('plugins/real-estate::dashboard.email') }}">
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <input id="phone" type="text"
                                               class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                               name="phone" value="{{ old('phone') }}" required
                                               placeholder="{{ trans('plugins/real-estate::dashboard.phone') }}">
                                        @if ($errors->has('phone'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('phone') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <input id="password" type="password"
                                               class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                               name="password" required
                                               placeholder="{{ trans('plugins/real-estate::dashboard.password') }}">
                                        @if ($errors->has('password'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <input id="password-confirm" type="password" class="form-control"
                                               name="password_confirmation" required
                                               placeholder="{{ trans('plugins/real-estate::dashboard.password-confirmation') }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Membership Plan Selection -->
                            <h5 class="mb-3">Select Membership Plan</h5>
                            
                            @if(isset($membershipPlans) && count($membershipPlans) > 0)
                                <div class="row mb-4">
                                    @foreach($membershipPlans as $plan)
                                        <div class="col-md-4 mb-3">
                                            <div class="card membership-plan-card {{ old('membership_plan_id') == $plan->id ? 'selected' : '' }}" 
                                                 style="cursor: pointer; border: 2px solid #ddd;" 
                                                 onclick="selectPlan({{ $plan->id }}, {{ $plan->price }})">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">{{ $plan->name }}</h5>
                                                    <h3 class="text-primary">₹{{ number_format($plan->price, 2) }}</h3>
                                                    <p class="text-muted">{{ round($plan->duration_days / 30) }} Months</p>
                                                    <p class="small">{{ $plan->description }}</p>
                                                    @if($plan->features)
                                                        @php $features = json_decode($plan->features, true) @endphp
                                                        @if($features)
                                                            <ul class="list-unstyled text-start small">
                                                                @foreach($features as $feature)
                                                                    <li>✓ {{ $feature }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <strong>No membership plans available!</strong><br>
                                    Please contact administrator.
                                    <br><small>Debug: Plans count = {{ isset($membershipPlans) ? count($membershipPlans) : 'undefined' }}</small>
                                </div>
                            @endif
                            <input type="hidden" name="membership_plan_id" id="membership_plan_id" value="{{ old('membership_plan_id') }}">
                            @if ($errors->has('membership_plan_id'))
                                <span class="text-danger">
                                    <strong>{{ $errors->first('membership_plan_id') }}</strong>
                                </span>
                            @endif

                            <hr class="my-4">

                            <!-- KYC Details -->
                            <h5 class="mb-3">Add Your KYC Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="pan_card_number">PAN Card Number <span class="text-danger">*</span></label>
                                        <input id="pan_card_number" type="text"
                                               class="form-control text-uppercase{{ $errors->has('pan_card_number') ? ' is-invalid' : '' }}"
                                               name="pan_card_number" value="{{ old('pan_card_number') }}" required
                                               placeholder="ABCDE1234F" maxlength="10">
                                        <small class="text-muted">Format: ABCDE1234F</small>
                                        @if ($errors->has('pan_card_number'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('pan_card_number') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="pan_card_file">Upload PAN Card <span class="text-danger">*</span></label>
                                        <input id="pan_card_file" type="file"
                                               class="form-control{{ $errors->has('pan_card_file') ? ' is-invalid' : '' }}"
                                               name="pan_card_file" required accept="image/*">
                                        <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                        @if ($errors->has('pan_card_file'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('pan_card_file') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Payment Details -->
                            <h5 class="mb-3">Payment Details</h5>
                            <div class="alert alert-info" id="payment-info" style="display: none;">
                                <strong>Payment Amount: ₹<span id="payment-amount">0.00</span></strong><br>
                                <small>Please scan the QR code below and make the payment. After payment, enter UTR number and upload screenshot.</small>
                            </div>

                            <div class="text-center mb-3" id="qr-code-section" style="display: none;">
                                <!-- QR code will be loaded dynamically -->
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="payment_utr_number">Payment UTR Number <span class="text-danger">*</span></label>
                                        <input id="payment_utr_number" type="text"
                                               class="form-control{{ $errors->has('payment_utr_number') ? ' is-invalid' : '' }}"
                                               name="payment_utr_number" value="{{ old('payment_utr_number') }}" required
                                               placeholder="Enter 12-22 digit UTR number">
                                        @if ($errors->has('payment_utr_number'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('payment_utr_number') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="payment_screenshot">Upload Payment Screenshot <span class="text-danger">*</span></label>
                                        <input id="payment_screenshot" type="file"
                                               class="form-control{{ $errors->has('payment_screenshot') ? ' is-invalid' : '' }}"
                                               name="payment_screenshot" required accept="image/*">
                                        <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                        @if ($errors->has('payment_screenshot'))
                                            <span class="invalid-feedback">
                                            <strong>{{ $errors->first('payment_screenshot') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if (is_plugin_active('captcha') && setting('enable_captcha') && setting('real_estate_enable_recaptcha_in_register_page', 0))
                                <div class="form-group mb-3">
                                    {!! Captcha::display() !!}
                                </div>
                            @endif

                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-blue btn-full fw6">
                                    {{ trans('plugins/real-estate::dashboard.register-cta') }}
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                {!! apply_filters(BASE_FILTER_AFTER_LOGIN_OR_REGISTER_FORM, null, \Botble\RealEstate\Models\Account::class) !!}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .membership-plan-card {
            transition: all 0.3s;
        }
        .membership-plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .membership-plan-card.selected {
            border-color: #007bff !important;
            background-color: #f0f8ff;
        }
    </style>

    <script>
        function selectPlan(planId, price) {
            // Remove selected class from all cards
            document.querySelectorAll('.membership-plan-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Set hidden input value
            document.getElementById('membership_plan_id').value = planId;
            
            // Show payment info
            document.getElementById('payment-info').style.display = 'block';
            document.getElementById('payment-amount').textContent = price.toFixed(2);
            
            // Load QR code dynamically
            loadQRCode(planId);
        }

        function loadQRCode(planId) {
            const qrSection = document.getElementById('qr-code-section');
            qrSection.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Loading QR Code...</p></div>';
            qrSection.style.display = 'block';
            
            // Fetch QR code from server
            fetch('{{ route("public.account.membership.qr-code") }}?plan_id=' + planId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        qrSection.innerHTML = '<div class="alert alert-danger">Failed to load QR code</div>';
                        return;
                    }
                    
                    qrSection.innerHTML = `
                        <img src="${data.data.qr_code_url}" alt="Payment QR Code" style="max-width: 250px; border: 2px solid #ddd; padding: 10px;" class="img-fluid">
                        <p class="mt-2"><strong>Scan & Pay ₹${data.data.amount}</strong></p>
                        <p class="small text-muted">UPI ID: ${data.data.upi_id}</p>
                        <p class="small">Plan: ${data.data.plan_name}</p>
                    `;
                })
                .catch(error => {
                    console.error('Error loading QR code:', error);
                    qrSection.innerHTML = '<div class="alert alert-danger">Failed to load QR code. Please try again.</div>';
                });
        }

        // Auto-select if old value exists
        @if(old('membership_plan_id'))
            document.addEventListener('DOMContentLoaded', function() {
                const planId = {{ old('membership_plan_id') }};
                const card = document.querySelector(`[onclick*="selectPlan(${planId}"]`);
                if (card) {
                    card.click();
                }
            });
        @endif
    </script>
@endsection
@push('scripts')
    <!-- Laravel Javascript Validation -->
    <script type="text/javascript" src="{{ asset('vendor/core/core/js-validation/js/js-validation.js')}}"></script>
    {!! JsValidator::formRequest(\Botble\RealEstate\Http\Requests\RegisterRequest::class); !!}
@endpush
