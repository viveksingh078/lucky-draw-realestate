<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card login-form">
                <div class="card-body">
                    <h4 class="text-center">🏢 Dealer Registration</h4>
                    <p class="text-center text-muted">Register as a property dealer to list and manage properties</p>
                    <br>
                    @include(Theme::getThemeNamespace() . '::views.real-estate.account.auth.includes.messages')
                    <br>
                    <form method="POST" action="{{ route('public.account.register.dealer') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="account_type" value="dealer">
                        
                        <!-- Basic Information -->
                        <h5 class="mb-3 border-bottom pb-2">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name <span class="text-danger">*</span></label>
                                    <input id="first_name" type="text"
                                           class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}"
                                           name="first_name" value="{{ old('first_name') }}" required autofocus
                                           placeholder="Enter first name">
                                    @if ($errors->has('first_name'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('first_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                    <input id="last_name" type="text"
                                           class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}"
                                           name="last_name" value="{{ old('last_name') }}" required
                                           placeholder="Enter last name">
                                    @if ($errors->has('last_name'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('last_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="company">Company/Agency Name <span class="text-danger">*</span></label>
                            <input id="company" type="text"
                                   class="form-control{{ $errors->has('company') ? ' is-invalid' : '' }}"
                                   name="company" value="{{ old('company') }}" required
                                   placeholder="Enter company or agency name">
                            @if ($errors->has('company'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('company') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <input id="username" type="text"
                                   class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}"
                                   name="username" value="{{ old('username') }}" required
                                   placeholder="Choose a username">
                            @if ($errors->has('username'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input id="email" type="email"
                                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                           name="email" value="{{ old('email') }}" required
                                           placeholder="Enter email address">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone <span class="text-danger">*</span></label>
                                    <input id="phone" type="text"
                                           class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                           name="phone" value="{{ old('phone') }}" required
                                           placeholder="Enter phone number">
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
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <input id="password" type="password"
                                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                           name="password" required
                                           placeholder="Enter password">
                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password-confirm">Confirm Password <span class="text-danger">*</span></label>
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" required
                                           placeholder="Confirm password">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- KYC Details -->
                        <h5 class="mb-3 border-bottom pb-2">KYC Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pan_card_number">PAN Card Number <span class="text-danger">*</span></label>
                                    <input id="pan_card_number" type="text"
                                           class="form-control text-uppercase{{ $errors->has('pan_card_number') ? ' is-invalid' : '' }}"
                                           name="pan_card_number" value="{{ old('pan_card_number') }}" required
                                           placeholder="AFZPK7190K" maxlength="10">
                                    <small class="text-muted">Format: AFZPK7190K</small>
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

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="aadhaar_number">Aadhaar Card Number <span class="text-danger">*</span></label>
                                    <input id="aadhaar_number" type="text"
                                           class="form-control{{ $errors->has('aadhaar_number') ? ' is-invalid' : '' }}"
                                           name="aadhaar_number" value="{{ old('aadhaar_number') }}" required
                                           placeholder="Enter 12 digit Aadhaar number" maxlength="12">
                                    <small class="text-muted">12 digits only</small>
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
                                           name="aadhaar_front_image" required accept="image/*">
                                    <small class="text-muted">Max size: 2MB</small>
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
                                           name="aadhaar_back_image" required accept="image/*">
                                    <small class="text-muted">Max size: 2MB</small>
                                    @if ($errors->has('aadhaar_back_image'))
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $errors->first('aadhaar_back_image') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-blue btn-full fw6">
                                Register as Dealer
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="{{ route('public.account.login') }}?type=dealer">Login as Dealer</a></p>
                            <p><a href="{{ route('public.account.register') }}?type=user">Register as User instead</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
