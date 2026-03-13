<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card login-form">
                <div class="card-body">
                    <h4 class="text-center">{{ trans('plugins/real-estate::dashboard.register-title') }}</h4>
                    <br>
                    @include(Theme::getThemeNamespace() . '::views.real-estate.account.auth.includes.messages')
                    <br>
                    <form method="POST" action="{{ route('public.account.register') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Basic Information -->
                        <h5 class="mb-3 border-bottom pb-2">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="first_name" type="text"
                                           class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}"
                                           name="first_name" value="{{ old('first_name') }}" required autofocus
                                           maxlength="20"
                                           pattern="[A-Za-z\s]+"
                                           title="Only letters and spaces allowed (max 20 characters)"
                                           placeholder="{{ trans('plugins/real-estate::dashboard.first_name') }}">
                                    <small class="text-muted">Max 20 characters, letters only</small>
                                    @if ($errors->has('first_name'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('first_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="last_name" type="text"
                                           class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}"
                                           name="last_name" value="{{ old('last_name') }}" required
                                           maxlength="20"
                                           pattern="[A-Za-z\s]+"
                                           title="Only letters and spaces allowed (max 20 characters)"
                                           placeholder="{{ trans('plugins/real-estate::dashboard.last_name') }}">
                                    <small class="text-muted">Max 20 characters, letters only</small>
                                    @if ($errors->has('last_name'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('last_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <input id="username" type="text"
                                   class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}"
                                   name="username" value="{{ old('username') }}" required
                                   maxlength="30"
                                   pattern="[A-Za-z0-9]+"
                                   title="Only letters and numbers allowed (max 30 characters)"
                                   placeholder="{{ trans('plugins/real-estate::dashboard.username') }}">
                            <small class="text-muted">Max 30 characters, letters and numbers only (no spaces)</small>
                            @if ($errors->has('username'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
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
                                           minlength="10" maxlength="12"
                                           pattern="[0-9]{10,12}"
                                           title="Phone number must be 10-12 digits only"
                                           onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                           placeholder="{{ trans('plugins/real-estate::dashboard.phone') }}">
                                    <small class="text-muted">10-12 digits only</small>
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
                                <div class="form-group">
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
                                <div class="form-group">
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" required
                                           placeholder="{{ trans('plugins/real-estate::dashboard.password-confirmation') }}">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Membership Plan Selection - PREMIUM DESIGN -->
                        <div class="membership-section" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 20px; padding: 30px 20px; margin: 25px 0;">
                            <h5 class="section-title" style="text-align: center; font-size: 1.6rem; font-weight: 800; color: #2c3e50; margin-bottom: 5px;">
                                <i class="fas fa-crown" style="color: #f39c12; margin-right: 8px; font-size: 1.6rem;"></i> Choose Your Membership Plan
                            </h5>
                            <p class="section-subtitle" style="text-align: center; color: #7f8c8d; font-size: 0.9rem; margin-bottom: 25px;">Select the perfect plan for your property investment journey</p>
                        
                            @if(isset($membershipPlans) && count($membershipPlans) > 0)
                                <div class="row plans-row">
                                    @foreach($membershipPlans as $index => $plan)
                                        <div class="col-md-4 mb-4">
                                            <div class="premium-plan-card plan-{{ strtolower($plan->name) }}" 
                                                 id="plan-card-{{ $plan->id }}" 
                                                 onclick="selectPlanCard({{ $plan->id }}, {{ $plan->price }})"
                                                 style="background: #ffffff; border-radius: 20px; padding: 20px; text-align: center; cursor: pointer; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1); height: 100%; border: 3px solid #e0e0e0; position: relative; display: flex; flex-direction: column;">
                                                
                                                @if($index == 1)
                                                    <div class="popular-badge" style="position: absolute; top: -8px; right: 15px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: #ffffff; padding: 4px 12px; border-radius: 12px; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.5px; box-shadow: 0 2px 10px rgba(231, 76, 60, 0.4); z-index: 10;">
                                                        <i class="fas fa-star" style="margin-right: 2px;"></i> POPULAR
                                                    </div>
                                                @endif
                                                
                                                <!-- Icon and Title Row -->
                                                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px; gap: 12px;">
                                                    <div class="plan-icon" style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); flex-shrink: 0;">
                                                        <i class="fas fa-{{ $index == 0 ? 'gem' : ($index == 1 ? 'crown' : 'trophy') }}" style="font-size: 1.4rem; color: #ffffff;"></i>
                                                    </div>
                                                    <h3 class="plan-title" style="font-size: 1.4rem; font-weight: 800; color: #2c3e50; text-transform: uppercase; letter-spacing: 1.5px; margin: 0;">{{ $plan->name }}</h3>
                                                </div>
                                                
                                                <!-- Pricing -->
                                                <div class="plan-pricing" style="margin: 10px 0; padding: 10px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px;">
                                                    <div class="price-tag" style="display: flex; align-items: flex-start; justify-content: center; margin-bottom: 2px;">
                                                        <span class="currency" style="font-size: 1rem; font-weight: 700; color: #27ae60; margin-right: 3px; margin-top: 5px;">₹</span>
                                                        <span class="amount" style="font-size: 2.2rem; font-weight: 900; background: linear-gradient(135deg, #27ae60 0%, #229954 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1;">{{ number_format($plan->price, 0) }}</span>
                                                    </div>
                                                    <div class="price-period" style="font-size: 0.8rem; color: #7f8c8d; font-weight: 600;">{{ round($plan->duration_days / 30) }} Months</div>
                                                </div>
                                                
                                                <div class="plan-desc" style="color: #7f8c8d; font-size: 0.8rem; line-height: 1.3; margin: 8px 0; padding: 0 5px; min-height: 32px;">{{ $plan->description }}</div>
                                                
                                                @if($plan->features)
                                                    @php $features = json_decode($plan->features, true) @endphp
                                                    @if($features)
                                                        <ul class="plan-features-list" style="list-style: none; padding: 0; margin: 10px 0; text-align: left; flex-grow: 1;">
                                                            @foreach($features as $feature)
                                                                <li style="padding: 6px 8px; margin-bottom: 4px; background: #f8f9fa; border-radius: 6px; display: flex; align-items: center; transition: all 0.3s ease;">
                                                                    <i class="fas fa-check-circle" style="color: #27ae60; font-size: 0.9rem; margin-right: 6px; flex-shrink: 0;"></i>
                                                                    <span style="color: #2c3e50; font-size: 0.8rem; font-weight: 500;">{{ $feature }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                @endif
                                                
                                                <button type="button" class="plan-cta-btn" style="width: 100%; padding: 12px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; border: none; border-radius: 50px; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 5px 18px rgba(102, 126, 234, 0.4); margin-top: 10px; text-transform: uppercase; letter-spacing: 0.8px;">
                                                    <span class="btn-text" style="display: inline;">
                                                        <i class="fas fa-hand-pointer" style="margin-right: 5px; font-size: 0.95rem;"></i> Choose Plan
                                                    </span>
                                                    <span class="btn-selected" style="display: none;">
                                                        <i class="fas fa-check-circle" style="margin-right: 5px; font-size: 0.95rem;"></i> Selected
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <strong>No membership plans available!</strong> Please contact administrator.
                                </div>
                            @endif
                            
                            <input type="hidden" name="membership_plan_id" id="membership_plan_id" value="{{ old('membership_plan_id') }}">
                            @if ($errors->has('membership_plan_id'))
                                <div class="alert alert-danger mt-3">{{ $errors->first('membership_plan_id') }}</div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <!-- KYC Details -->
                        <h5 class="mb-3 border-bottom pb-2">Add Your KYC Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pan_card_number">PAN Card Number <span class="text-danger">*</span></label>
                                    <input id="pan_card_number" type="text"
                                           class="form-control text-uppercase{{ $errors->has('pan_card_number') ? ' is-invalid' : '' }}"
                                           name="pan_card_number" value="{{ old('pan_card_number') }}" required
                                           placeholder="AFZPK7190K" maxlength="10"
                                           onkeypress="return validatePanInput(event)"
                                           title="Enter valid PAN: e.g., AFZPK7190K">
                                    <small class="text-muted">Format: AFZPK7190K (5 letters + 4 digits + 1 letter)</small>
                                    <div id="pan-validation-msg" class="small mt-1"></div>
                                    @if ($errors->has('pan_card_number'))
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $errors->first('pan_card_number') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pan_card_file">Upload PAN Card <span class="text-danger">*</span></label>
                                    <input id="pan_card_file" type="file"
                                           class="form-control{{ $errors->has('pan_card_file') ? ' is-invalid' : '' }}"
                                           name="pan_card_file" required accept="image/*">
                                    <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                    @if ($errors->has('pan_card_file'))
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $errors->first('pan_card_file') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Aadhaar Card Details -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="aadhaar_number">Aadhaar Card Number <span class="text-danger">*</span></label>
                                    <input id="aadhaar_number" type="text"
                                           class="form-control{{ $errors->has('aadhaar_number') ? ' is-invalid' : '' }}"
                                           name="aadhaar_number" value="{{ old('aadhaar_number') }}" required
                                           placeholder="Enter 12 digit Aadhaar number" maxlength="12"
                                           onkeypress="return validateAadhaarInput(event)"
                                           oninput="formatAadhaarNumber(this)"
                                           title="Enter valid 12-digit Aadhaar number">
                                    <small class="text-muted">12 digits only (e.g., 123456789012)</small>
                                    <div id="aadhaar-validation-msg" class="small mt-1"></div>
                                    @if ($errors->has('aadhaar_number'))
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $errors->first('aadhaar_number') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="aadhaar_front_image">Upload Aadhaar Front <span class="text-danger">*</span></label>
                                    <input id="aadhaar_front_image" type="file"
                                           class="form-control{{ $errors->has('aadhaar_front_image') ? ' is-invalid' : '' }}"
                                           name="aadhaar_front_image" required accept="image/*"
                                           onchange="previewImage(this, 'aadhaar-front-preview')">
                                    <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                    <div id="aadhaar-front-preview" class="mt-2"></div>
                                    @if ($errors->has('aadhaar_front_image'))
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $errors->first('aadhaar_front_image') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="aadhaar_back_image">Upload Aadhaar Back <span class="text-danger">*</span></label>
                                    <input id="aadhaar_back_image" type="file"
                                           class="form-control{{ $errors->has('aadhaar_back_image') ? ' is-invalid' : '' }}"
                                           name="aadhaar_back_image" required accept="image/*"
                                           onchange="previewImage(this, 'aadhaar-back-preview')">
                                    <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                    <div id="aadhaar-back-preview" class="mt-2"></div>
                                    @if ($errors->has('aadhaar_back_image'))
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $errors->first('aadhaar_back_image') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3 border-bottom pb-2">Payment Details</h5>
                        <div class="alert alert-info" id="payment-info" style="display: none;">
                            <strong>Payment Amount: ₹<span id="payment-amount">0.00</span></strong><br>
                            <small>Please scan the QR code below and make the payment. After payment, enter UTR number and upload screenshot.</small>
                        </div>

                        <div class="text-center mb-4" id="qr-code-section" style="display: none;">
                            <!-- QR code will be loaded dynamically -->
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_utr_number">Payment UTR Number <span class="text-danger">*</span></label>
                                    <input id="payment_utr_number" type="text"
                                           class="form-control{{ $errors->has('payment_utr_number') ? ' is-invalid' : '' }}"
                                           name="payment_utr_number" value="{{ old('payment_utr_number') }}" required
                                           placeholder="Enter 12-22 digit UTR number"
                                           minlength="12" maxlength="22"
                                           onkeypress="return validateUtrInput(event)"
                                           title="UTR must be 12-22 digits only">
                                    <small class="text-muted">Only numbers allowed (12-22 digits)</small>
                                    <div id="utr-validation-msg" class="small mt-1"></div>
                                    @if ($errors->has('payment_utr_number'))
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $errors->first('payment_utr_number') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_screenshot">Upload Payment Screenshot <span class="text-danger">*</span></label>
                                    <input id="payment_screenshot" type="file"
                                           class="form-control{{ $errors->has('payment_screenshot') ? ' is-invalid' : '' }}"
                                           name="payment_screenshot" required accept="image/*">
                                    <small class="text-muted">Max size: 2MB (JPG, PNG)</small>
                                    @if ($errors->has('payment_screenshot'))
                                        <span class="invalid-feedback d-block">
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

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-blue btn-full fw6">
                                {{ trans('plugins/real-estate::dashboard.register-cta') }}
                            </button>
                        </div>

                        <div class="form-group text-center">
                            <p>{{ __('Have an account already?') }} <a href="{{ route('public.account.login') }}" class="d-block d-sm-inline-block text-sm-left text-center">{{ __('Login') }}</a></p>
                        </div>

                        <div class="text-center">
                            {!! apply_filters(BASE_FILTER_AFTER_LOGIN_OR_REGISTER_FORM, null, \Botble\RealEstate\Models\Account::class) !!}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ========== PREMIUM MEMBERSHIP PLAN CARDS ========== */
    
    .membership-section {
        padding: 30px 0;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 20px;
        padding: 40px 30px;
        margin: 20px 0;
    }
    
    .section-title {
        text-align: center;
        font-size: 2rem;
        font-weight: 800;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .section-title i {
        color: #f39c12;
        margin-right: 10px;
        animation: rotate 3s linear infinite;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .section-subtitle {
        text-align: center;
        color: #7f8c8d;
        font-size: 1.1rem;
        margin-bottom: 40px;
    }
    
    .plans-row {
        display: flex;
        align-items: stretch;
    }
    
    /* Premium Plan Card */
    .premium-plan-card {
        background: #ffffff;
        border-radius: 25px;
        padding: 35px 25px;
        text-align: center;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        border: 3px solid transparent;
    }
    
    /* Gradient borders for different plans */
    .premium-plan-card.plan-silver {
        border-image: linear-gradient(135deg, #bdc3c7, #2c3e50) 1;
    }
    
    .premium-plan-card.plan-gold {
        border-image: linear-gradient(135deg, #f39c12, #e67e22) 1;
    }
    
    .premium-plan-card.plan-diamond {
        border-image: linear-gradient(135deg, #3498db, #2980b9) 1;
    }
    
    /* Popular Badge */
    .popular-badge {
        position: absolute;
        top: -10px;
        right: 20px;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: #ffffff;
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
        z-index: 10;
    }
    
    .popular-badge i {
        margin-right: 5px;
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    
    /* Plan Header */
    .plan-header {
        margin-bottom: 25px;
    }
    
    .plan-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }
    
    .plan-icon i {
        font-size: 2.5rem;
        color: #ffffff;
    }
    
    .plan-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: #2c3e50;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0;
    }
    
    /* Pricing Section */
    .plan-pricing {
        margin: 25px 0;
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
    }
    
    .price-tag {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        margin-bottom: 10px;
    }
    
    .price-tag .currency {
        font-size: 1.5rem;
        font-weight: 700;
        color: #27ae60;
        margin-right: 5px;
        margin-top: 10px;
    }
    
    .price-tag .amount {
        font-size: 3.5rem;
        font-weight: 900;
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
    }
    
    .price-period {
        font-size: 1rem;
        color: #7f8c8d;
        font-weight: 600;
    }
    
    /* Plan Description */
    .plan-desc {
        font-size: 0.95rem;
        color: #7f8c8d;
        margin: 20px 0;
        min-height: 45px;
        line-height: 1.6;
    }
    
    /* Features List */
    .plan-features-list {
        list-style: none;
        padding: 0;
        margin: 25px 0;
        text-align: left;
        flex-grow: 1;
    }
    
    .plan-features-list li {
        padding: 12px 0;
        font-size: 0.95rem;
        color: #34495e;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #ecf0f1;
    }
    
    .plan-features-list li:last-child {
        border-bottom: none;
    }
    
    .plan-features-list li i {
        color: #27ae60;
        font-size: 1.1rem;
        margin-right: 12px;
        flex-shrink: 0;
    }
    
    /* CTA Button */
    .plan-cta-btn {
        width: 100%;
        padding: 16px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #ffffff;
        border: none;
        border-radius: 30px;
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        position: relative;
        overflow: hidden;
    }
    
    .plan-cta-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .plan-cta-btn:hover::before {
        width: 400px;
        height: 400px;
    }
    
    .plan-cta-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
    }
    
    /* Hover State */
    .premium-plan-card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }
    
    .premium-plan-card:hover .plan-icon {
        transform: scale(1.1) rotate(10deg);
    }
    
    /* ========== SELECTED STATE - SUPER PREMIUM ========== */
    .premium-plan-card.card-selected {
        border: 4px solid #27ae60;
        background: linear-gradient(135deg, #d5f4e6 0%, #80ffdb 100%);
        box-shadow: 0 0 0 6px rgba(39, 174, 96, 0.2), 
                    0 25px 70px rgba(39, 174, 96, 0.4);
        transform: translateY(-20px) scale(1.05);
        animation: selectedPulse 2s infinite;
    }
    
    @keyframes selectedPulse {
        0%, 100% {
            box-shadow: 0 0 0 6px rgba(39, 174, 96, 0.2), 
                        0 25px 70px rgba(39, 174, 96, 0.4);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(39, 174, 96, 0.3), 
                        0 25px 70px rgba(39, 174, 96, 0.6);
        }
    }
    
    .premium-plan-card.card-selected .plan-icon {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        transform: scale(1.15);
        animation: iconBounce 1s infinite;
    }
    
    @keyframes iconBounce {
        0%, 100% { transform: scale(1.15) translateY(0); }
        50% { transform: scale(1.15) translateY(-10px); }
    }
    
    .premium-plan-card.card-selected .plan-title {
        color: #27ae60;
        font-size: 2rem;
    }
    
    .premium-plan-card.card-selected .price-tag .amount {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 3.8rem;
    }
    
    .premium-plan-card.card-selected .plan-cta-btn {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        box-shadow: 0 12px 40px rgba(39, 174, 96, 0.6);
        animation: buttonGlow 2s infinite;
    }
    
    @keyframes buttonGlow {
        0%, 100% {
            box-shadow: 0 12px 40px rgba(39, 174, 96, 0.6);
        }
        50% {
            box-shadow: 0 12px 50px rgba(39, 174, 96, 0.8);
        }
    }
    
    .premium-plan-card.card-selected .btn-text {
        display: none;
    }
    
    .premium-plan-card.card-selected .btn-selected {
        display: inline !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .membership-section {
            padding: 30px 15px;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .premium-plan-card {
            margin-bottom: 30px;
        }
        
        .price-tag .amount {
            font-size: 2.5rem;
        }
    }
</style>

<script>
    function selectPlanCard(planId, price) {
        // Remove selected class and styles from all cards
        document.querySelectorAll('.premium-plan-card').forEach(function(card) {
            card.classList.remove('card-selected');
            // Reset to default styles
            card.style.border = '3px solid #e0e0e0';
            card.style.background = '#ffffff';
            card.style.transform = 'scale(1)';
            card.style.boxShadow = '0 10px 35px rgba(0, 0, 0, 0.1)';
            
            // Reset button text
            const btnText = card.querySelector('.btn-text');
            const btnSelected = card.querySelector('.btn-selected');
            if (btnText) btnText.style.display = 'inline';
            if (btnSelected) btnSelected.style.display = 'none';
            
            // Reset button style
            const btn = card.querySelector('.plan-cta-btn');
            if (btn) {
                btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                btn.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.4)';
            }
        });
        
        // Add selected class and styles to clicked card
        const selectedCard = document.getElementById('plan-card-' + planId);
        if (selectedCard) {
            selectedCard.classList.add('card-selected');
            
            // Apply SUPER VISIBLE green selection styles with inline CSS
            selectedCard.style.border = '5px solid #27ae60';
            selectedCard.style.background = 'linear-gradient(135deg, #d5f4e6 0%, #80ffdb 100%)';
            selectedCard.style.transform = 'scale(1.05)';
            selectedCard.style.boxShadow = '0 20px 60px rgba(39, 174, 96, 0.6)';
            
            // Change button to green and show "Selected"
            const btn = selectedCard.querySelector('.plan-cta-btn');
            if (btn) {
                btn.style.background = 'linear-gradient(135deg, #27ae60 0%, #229954 100%)';
                btn.style.boxShadow = '0 12px 40px rgba(39, 174, 96, 0.7)';
            }
            
            // Toggle button text
            const btnText = selectedCard.querySelector('.btn-text');
            const btnSelected = selectedCard.querySelector('.btn-selected');
            if (btnText) btnText.style.display = 'none';
            if (btnSelected) btnSelected.style.display = 'inline';
            
            // Change icon color to green
            const icon = selectedCard.querySelector('.plan-icon');
            if (icon) {
                icon.style.background = 'linear-gradient(135deg, #27ae60 0%, #229954 100%)';
                icon.style.transform = 'scale(1.15)';
            }
            
            // Change title color to green
            const title = selectedCard.querySelector('.plan-title');
            if (title) {
                title.style.color = '#27ae60';
            }
        }
        
        // Set hidden input value
        document.getElementById('membership_plan_id').value = planId;
        
        // Show payment info
        document.getElementById('payment-info').style.display = 'block';
        document.getElementById('payment-amount').textContent = price.toFixed(2);
        
        // Load QR code dynamically
        loadQRCode(planId);
        
        // Scroll to payment section smoothly
        setTimeout(function() {
            document.getElementById('payment-info').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 300);
    }

    function loadQRCode(planId) {
        const qrSection = document.getElementById('qr-code-section');
        qrSection.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading QR Code...</p></div>';
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
                    <div class="card" style="max-width: 320px; margin: 0 auto;">
                        <div class="card-body text-center">
                            <img src="${data.data.qr_code_url}" alt="Payment QR Code" style="max-width: 200px; border: 2px solid #ddd; padding: 10px; border-radius: 10px;" class="img-fluid">
                            <h4 class="mt-3 text-success">₹${data.data.amount}</h4>
                            <p class="mb-1"><strong>Scan & Pay</strong></p>
                            <p class="small text-muted mb-0">UPI ID: ${data.data.upi_id}</p>
                            <p class="small text-primary">Plan: ${data.data.plan_name}</p>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error loading QR code:', error);
                qrSection.innerHTML = '<div class="alert alert-danger">Failed to load QR code. Please try again.</div>';
            });
    }

    // PAN Card Validation
    // Format: [A-Z]{5}[0-9]{4}[A-Z]
    // Example: AFZPK7190K (5 letters + 4 digits + 1 letter)
    
    function validatePanInput(event) {
        const input = document.getElementById('pan_card_number');
        const currentLength = input.value.length;
        const char = String.fromCharCode(event.which || event.keyCode).toUpperCase();
        
        // Position 1-5: Only letters A-Z
        if (currentLength < 5) {
            return /[A-Za-z]/.test(char);
        }
        // Position 6-9: Only numbers 0-9
        else if (currentLength >= 5 && currentLength < 9) {
            return /[0-9]/.test(char);
        }
        // Position 10: Only letters A-Z (check digit)
        else if (currentLength === 9) {
            return /[A-Za-z]/.test(char);
        }
        
        return false;
    }
    
    // Auto-uppercase PAN card input and validate
    document.getElementById('pan_card_number').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        
        const pan = this.value;
        // Simple PAN regex: 5 letters + 4 digits + 1 letter
        const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;
        const msgDiv = document.getElementById('pan-validation-msg');
        
        if (pan.length === 0) {
            msgDiv.innerHTML = '';
            this.classList.remove('is-valid', 'is-invalid');
        } else if (pan.length < 10) {
            let hint = '';
            if (pan.length < 5) hint = 'Enter 5 letters (A-Z)';
            else if (pan.length < 9) hint = 'Enter 4 digits (0-9)';
            else hint = 'Enter last letter (A-Z)';
            
            msgDiv.innerHTML = '<span class="text-warning">⚠ ' + hint + '</span>';
            this.classList.remove('is-valid');
        } else if (panRegex.test(pan)) {
            msgDiv.innerHTML = '<span class="text-success">✓ Valid PAN format</span>';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            msgDiv.innerHTML = '<span class="text-danger">✗ Invalid PAN format. Format: 5 letters + 4 digits + 1 letter</span>';
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });
    
    // UTR Number Validation - Only numbers, 12-22 digits
    function validateUtrInput(event) {
        const char = String.fromCharCode(event.which || event.keyCode);
        return /[0-9]/.test(char);
    }
    
    document.getElementById('payment_utr_number').addEventListener('input', function() {
        // Remove any non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        const utr = this.value;
        const msgDiv = document.getElementById('utr-validation-msg');
        
        if (utr.length === 0) {
            msgDiv.innerHTML = '';
            this.classList.remove('is-valid', 'is-invalid');
        } else if (utr.length < 12) {
            msgDiv.innerHTML = '<span class="text-warning">⚠ UTR must be at least 12 digits (' + utr.length + '/12)</span>';
            this.classList.remove('is-valid');
        } else if (utr.length >= 12 && utr.length <= 22) {
            msgDiv.innerHTML = '<span class="text-success">✓ Valid UTR format (' + utr.length + ' digits)</span>';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            msgDiv.innerHTML = '<span class="text-danger">✗ UTR cannot exceed 22 digits</span>';
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });

    // Auto-select if old value exists
    document.addEventListener('DOMContentLoaded', function() {
        @if(old('membership_plan_id'))
            const planId = {{ old('membership_plan_id') }};
            const selectedCard = document.getElementById('plan-card-' + planId);
            if (selectedCard) {
                // Add selected class
                selectedCard.classList.add('card-selected');
                
                document.getElementById('membership_plan_id').value = planId;
                document.getElementById('payment-info').style.display = 'block';
                
                // Get price from card
                const priceText = selectedCard.querySelector('.amount').textContent;
                const price = parseFloat(priceText.replace(/[^0-9.]/g, ''));
                document.getElementById('payment-amount').textContent = price.toFixed(2);
                
                loadQRCode(planId);
            }
        @endif
    });

    // Aadhaar Number Validation
    function validateAadhaarInput(event) {
        const charCode = event.which ? event.which : event.keyCode;
        // Only allow numbers (0-9)
        if (charCode < 48 || charCode > 57) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    function formatAadhaarNumber(input) {
        // Remove any non-digit characters
        let value = input.value.replace(/\D/g, '');
        
        // Limit to 12 digits
        if (value.length > 12) {
            value = value.substring(0, 12);
        }
        
        input.value = value;
        
        const msgDiv = document.getElementById('aadhaar-validation-msg');
        
        if (value.length === 0) {
            msgDiv.innerHTML = '';
            input.classList.remove('is-valid', 'is-invalid');
        } else if (value.length < 12) {
            msgDiv.innerHTML = '<span class="text-warning">⚠ Aadhaar must be exactly 12 digits (' + value.length + '/12)</span>';
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        } else if (value.length === 12) {
            msgDiv.innerHTML = '<span class="text-success">✓ Valid Aadhaar format</span>';
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    }

    // Image Preview Function
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Check file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                input.value = '';
                return;
            }
            
            // Check file type
            if (!file.type.match('image.*')) {
                alert('Please select an image file');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">';
            };
            reader.readAsDataURL(file);
        }
    }
</script>
