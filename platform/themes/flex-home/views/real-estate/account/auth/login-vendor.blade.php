<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card login-form">
                <div class="card-body">
                    <h4 class="text-center">🏢 Vendor Login</h4>
                    <p class="text-center text-muted">Login to manage your properties</p>
                    <br>
                    @include(Theme::getThemeNamespace() . '::views.real-estate.account.auth.includes.messages')
                    <br>
                    <form method="POST" action="{{ route('public.account.login') }}">
                        @csrf
                        <input type="hidden" name="account_type" value="vendor">
                        
                        <div class="form-group">
                            <label for="email">Email or Username</label>
                            <input id="email" type="text"
                                   class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                   placeholder="Enter email or username"
                                   name="email" value="{{ old('email') }}" autofocus>
                            @if ($errors->has('email'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" type="password"
                                   class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                   placeholder="Enter password" name="password">
                            @if ($errors->has('password'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                        
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox"
                                                   name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-md-center">
                                    <a href="{{ route('public.account.password.request') }}">
                                        Forgot Password?
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-blue btn-full fw6">
                                Login as Vendor
                            </button>
                        </div>

                        <div class="form-group text-center">
                            <p>Don't have a vendor account? <a href="{{ route('public.account.register') }}?type=vendor">Register as Vendor</a></p>
                            <p><a href="{{ route('public.account.login') }}?type=user">Login as User instead</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
