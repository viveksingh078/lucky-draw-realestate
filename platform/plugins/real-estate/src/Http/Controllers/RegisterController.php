<?php

namespace Botble\RealEstate\Http\Controllers;

use App\Http\Controllers\Controller;
use BaseHelper;
use Botble\ACL\Traits\RegistersUsers;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Repositories\Interfaces\AccountInterface;
use Botble\RealEstate\Helpers\QRCodeHelper;
use EmailHandler;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use RealEstateHelper;
use RvMedia;
use SeoHelper;
use Theme;
use URL;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default, this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = null;

    /**
     * @var AccountInterface
     */
    protected $accountRepository;

    /**
     * Create a new controller instance.
     *
     * @param AccountInterface $accountRepository
     */
    public function __construct(AccountInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
        $this->redirectTo = route('public.account.register');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Response
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function showRegistrationForm(Request $request)
    {
        if (!RealEstateHelper::isRegisterEnabled()) {
            abort(404);
        }

        // Check if vendor registration
        if ($request->get('type') === 'vendor') {
            SeoHelper::setTitle(__('Vendor Registration'));
            
            if (view()->exists(Theme::getThemeNamespace() . '::views.real-estate.account.auth.register-vendor')) {
                return Theme::scope('real-estate.account.auth.register-vendor')->render();
            }
            
            return view('plugins/real-estate::account.auth.register-vendor');
        }

        // User registration
        SeoHelper::setTitle(__('Register'));

        // Get active membership plans using direct DB query
        $membershipPlans = DB::table('membership_plans')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // Debug: Check if plans are loaded
        if ($membershipPlans->isEmpty()) {
            // If no plans found, create default ones
            DB::table('membership_plans')->insert([
                [
                    'name' => 'Silver',
                    'slug' => 'silver',
                    'description' => 'Basic membership plan with essential features',
                    'price' => 999.00,
                    'duration_days' => 365,
                    'features' => '["Feature 1", "Feature 2", "Feature 3"]',
                    'is_active' => 1,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Gold',
                    'slug' => 'gold',
                    'description' => 'Premium membership plan with advanced features',
                    'price' => 1999.00,
                    'duration_days' => 365,
                    'features' => '["All Silver features", "Feature 4", "Feature 5", "Feature 6"]',
                    'is_active' => 1,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Diamond',
                    'slug' => 'diamond',
                    'description' => 'Ultimate membership plan with all features',
                    'price' => 4999.00,
                    'duration_days' => 365,
                    'features' => '["All Gold features", "Feature 7", "Feature 8", "Feature 9", "Priority Support"]',
                    'is_active' => 1,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
            
            // Reload plans
            $membershipPlans = DB::table('membership_plans')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get();
        }

        if (view()->exists(Theme::getThemeNamespace() . '::views.real-estate.account.auth.register')) {
            return Theme::scope('real-estate.account.auth.register', compact('membershipPlans'))->render();
        }

        return view('plugins/real-estate::account.auth.register', compact('membershipPlans'));
    }

    /**
     * Confirm a user with a given confirmation code.
     *
     * @param int $id
     * @param Request $request
     * @param BaseHttpResponse $response
     * @param AccountInterface $accountRepository
     * @return BaseHttpResponse
     */
    public function confirm($id, Request $request, BaseHttpResponse $response, AccountInterface $accountRepository)
    {
        if (!RealEstateHelper::isRegisterEnabled()) {
            abort(404);
        }

        if (!URL::hasValidSignature($request)) {
            abort(404);
        }

        $account = $accountRepository->findOrFail($id);

        $account->confirmed_at = now();
        $this->accountRepository->createOrUpdate($account);

        $this->guard()->login($account);

        return $response
            ->setNextUrl(route('public.account.dashboard'))
            ->setMessage(__('You successfully confirmed your email address.'));
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return auth('account');
    }

    /**
     * Resend a confirmation code to a user.
     *
     * @param \Illuminate\Http\Request $request
     * @param AccountInterface $accountRepository
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function resendConfirmation(
        Request $request,
        AccountInterface $accountRepository,
        BaseHttpResponse $response
    ) {
        if (!RealEstateHelper::isRegisterEnabled()) {
            abort(404);
        }

        $account = $accountRepository->getFirstBy(['email' => $request->input('email')]);
        if (!$account) {
            return $response
                ->setError()
                ->setMessage(__('Cannot find this account!'));
        }

        $this->sendConfirmationToUser($account);

        return $response
            ->setMessage(__('We sent you another confirmation email. You should receive it shortly.'));
    }

    /**
     * Send the confirmation code to a user.
     *
     * @param Account $account
     */
    protected function sendConfirmationToUser($account)
    {
        // Notify the user
        $notificationConfig = config('plugins.real-estate.real-estate.notification');
        if ($notificationConfig) {
            $notification = app($notificationConfig);
            $account->notify($notification);
        }
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function register(Request $request, BaseHttpResponse $response)
    {
        if (!RealEstateHelper::isRegisterEnabled()) {
            abort(404);
        }

        $this->validator($request->all())->validate();

        event(new Registered($account = $this->create($request->all())));

        // Get membership plan details for message
        $membershipPlan = DB::table('membership_plans')->where('id', $account->membership_plan_id)->first();
        $planName = $membershipPlan->name ?? 'N/A';
        $planPrice = number_format($membershipPlan->price ?? 0, 2);
        $planDuration = $membershipPlan ? round($membershipPlan->duration_days / 30) . ' Months' : 'N/A';

        // Send email to user
        $this->sendRegistrationEmail($account, $planName, $planPrice, $planDuration);
        
        // Send email to admin
        $this->sendAdminNotificationEmail($account, $planName, $planPrice, $planDuration);

        return $response
            ->setNextUrl(route('public.account.login'))
            ->setMessage('Registration successful! Your account is pending approval. Plan: ' . $planName . '. You will receive an email once approved.');
    }

    /**
     * Handle a vendor registration request
     *
     * @param \Illuminate\Http\Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function registerVendor(Request $request, BaseHttpResponse $response)
    {
        if (!RealEstateHelper::isRegisterEnabled()) {
            abort(404);
        }

        $this->vendorValidator($request->all())->validate();

        event(new Registered($account = $this->createVendor($request->all())));

        // Send email to vendor
        $this->sendVendorRegistrationEmail($account);
        
        // Send email to admin
        $this->sendVendorAdminNotificationEmail($account);

        return $response
            ->setNextUrl(route('public.account.login') . '?type=vendor')
            ->setMessage('Vendor registration successful! Your account is pending admin approval. You will receive an email once approved.');
    }

    /**
     * Send registration email to user
     */
    protected function sendRegistrationEmail($account, $planName, $planPrice, $planDuration)
    {
        try {
            $siteName = setting('site_title', 'AADS Property Portal');
            $subject = "Registration Received - Account Under Review | " . $siteName;
            
            $message = "
            <html>
            <head>
                <title>Registration Received</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #007bff; color: white; padding: 20px; text-align: center;'>
                        <h1 style='margin: 0;'>" . $siteName . "</h1>
                    </div>
                    <div style='padding: 20px; background: #f9f9f9;'>
                        <h2>Hello " . $account->name . ",</h2>
                        <p>Thank you for registering with us! Your account has been created successfully.</p>
                        
                        <p><strong>Current Status:</strong> <span style='background: #ffc107; color: #000; padding: 5px 15px; border-radius: 20px;'>WAITING FOR APPROVAL</span></p>
                        
                        <p>Your account is currently under review. Our team will verify your payment and KYC details.</p>
                        
                        <div style='background: #fff; border: 2px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                            <h3 style='color: #28a745; margin-top: 0;'>Your Membership Plan</h3>
                            <table style='width: 100%;'>
                                <tr><td><strong>Plan Name:</strong></td><td>" . $planName . "</td></tr>
                                <tr><td><strong>Plan Price:</strong></td><td>Rs. " . $planPrice . "</td></tr>
                                <tr><td><strong>Duration:</strong></td><td>" . $planDuration . "</td></tr>
                                <tr><td><strong>UTR Number:</strong></td><td>" . $account->payment_utr_number . "</td></tr>
                            </table>
                        </div>
                        
                        <p><strong>What happens next?</strong></p>
                        <ol>
                            <li>Our team will verify your payment</li>
                            <li>Your KYC documents will be reviewed</li>
                            <li>Once approved, you will receive a confirmation email</li>
                            <li>You can then login and access all features</li>
                        </ol>
                        
                        <p>Best Regards,<br><strong>" . $siteName . " Team</strong></p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $siteName . " <" . setting('email_from_address', 'noreply@sspl20.com') . ">\r\n";

            mail($account->email, $subject, $message, $headers);
        } catch (\Exception $e) {
            \Log::error('User registration email failed: ' . $e->getMessage());
        }
    }

    /**
     * Send notification email to admin
     */
    protected function sendAdminNotificationEmail($account, $planName, $planPrice, $planDuration)
    {
        try {
            $adminEmail = setting('admin_email');
            if (!$adminEmail) return;

            $siteName = setting('site_title', 'AADS Property Portal');
            $subject = "New Registration Pending Approval | " . $siteName;
            
            $message = "
            <html>
            <head>
                <title>New Registration</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #dc3545; color: white; padding: 20px; text-align: center;'>
                        <h1 style='margin: 0;'>New Registration - Pending Approval</h1>
                    </div>
                    <div style='padding: 20px; background: #f9f9f9;'>
                        <p>A new user has registered and is waiting for approval.</p>
                        
                        <div style='background: #fff; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                            <h3 style='margin-top: 0;'>User Details</h3>
                            <table style='width: 100%;'>
                                <tr><td><strong>Name:</strong></td><td>" . $account->name . "</td></tr>
                                <tr><td><strong>Email:</strong></td><td>" . $account->email . "</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>" . $account->phone . "</td></tr>
                                <tr><td><strong>Username:</strong></td><td>" . $account->username . "</td></tr>
                            </table>
                        </div>
                        
                        <div style='background: #fff; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                            <h3 style='margin-top: 0;'>Membership Plan</h3>
                            <table style='width: 100%;'>
                                <tr><td><strong>Plan:</strong></td><td>" . $planName . "</td></tr>
                                <tr><td><strong>Price:</strong></td><td>Rs. " . $planPrice . "</td></tr>
                                <tr><td><strong>Duration:</strong></td><td>" . $planDuration . "</td></tr>
                            </table>
                        </div>
                        
                        <div style='background: #fff; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                            <h3 style='margin-top: 0;'>Payment and KYC Details</h3>
                            <table style='width: 100%;'>
                                <tr><td><strong>PAN Card:</strong></td><td>" . $account->pan_card_number . "</td></tr>
                                <tr><td><strong>UTR Number:</strong></td><td>" . $account->payment_utr_number . "</td></tr>
                            </table>
                        </div>
                        
                        <p style='text-align: center; margin-top: 20px;'>
                            <a href='" . url('/admin/real-estate/accounts') . "' style='background: #28a745; color: white; padding: 10px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Review and Approve</a>
                        </p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $siteName . " <" . setting('email_from_address', 'noreply@sspl20.com') . ">\r\n";

            mail($adminEmail, $subject, $message, $headers);
        } catch (\Exception $e) {
            \Log::error('Admin notification email failed: ' . $e->getMessage());
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'first_name'         => 'required|max:20|regex:/^[a-zA-Z\s]+$/',
            'last_name'          => 'required|max:20|regex:/^[a-zA-Z\s]+$/',
            'username'           => 'required|max:30|min:2|regex:/^[a-zA-Z0-9]+$/|unique:re_accounts,username',
            'email'              => 'required|email|max:255|unique:re_accounts',
            'password'           => 'required|min:6|confirmed',
            'phone'              => ['required', 'regex:/^[0-9]{10,12}$/'],
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'pan_card_number'    => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i', 'unique:re_accounts,pan_card_number'],
            'pan_card_file'      => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'aadhaar_number'     => ['required', 'regex:/^[0-9]{12}$/', 'unique:re_accounts,aadhaar_number'],
            'aadhaar_front_image' => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'aadhaar_back_image'  => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'payment_utr_number' => ['required', 'regex:/^[0-9]{12,22}$/', 'unique:re_accounts,payment_utr_number', 'unique:re_credit_recharges,payment_utr_number'],
            'payment_screenshot' => 'required|file|mimes:jpeg,png,jpg|max:2048',
        ];

        if (is_plugin_active('captcha') && setting('enable_captcha') && setting('real_estate_enable_recaptcha_in_register_page', 0)) {
            $rules += ['g-recaptcha-response' => 'required|captcha'];
        }

        return Validator::make($data, $rules, [
            'first_name.required'             => __('First name is required.'),
            'first_name.max'                  => __('First name cannot exceed 20 characters.'),
            'first_name.regex'                => __('First name can only contain letters and spaces.'),
            'last_name.required'              => __('Last name is required.'),
            'last_name.max'                   => __('Last name cannot exceed 20 characters.'),
            'last_name.regex'                 => __('Last name can only contain letters and spaces.'),
            'username.required'               => __('Username is required.'),
            'username.max'                    => __('Username cannot exceed 30 characters.'),
            'username.regex'                  => __('Username can only contain letters and numbers (no spaces or special characters).'),
            'username.unique'                 => __('This username is already taken.'),
            'email.required'                  => __('Email is required.'),
            'email.email'                     => __('Please enter a valid email address.'),
            'email.unique'                    => __('This email is already registered.'),
            'phone.required'                  => __('Phone number is required.'),
            'phone.regex'                     => __('Phone number must be 10-12 digits only.'),
            'g-recaptcha-response.required'   => __('Captcha Verification Failed!'),
            'g-recaptcha-response.captcha'    => __('Captcha Verification Failed!'),
            'pan_card_number.regex'           => __('Invalid PAN format. Valid format: AFZPK7190K (5 letters + 4 digits + 1 letter)'),
            'pan_card_number.unique'          => __('This PAN card is already registered.'),
            'aadhaar_number.required'         => __('Aadhaar card number is required.'),
            'aadhaar_number.regex'            => __('Aadhaar number must be exactly 12 digits.'),
            'aadhaar_number.unique'           => __('This Aadhaar card is already registered.'),
            'aadhaar_front_image.required'    => __('Please upload Aadhaar front image.'),
            'aadhaar_front_image.mimes'       => __('Aadhaar front image must be a JPG or PNG image.'),
            'aadhaar_back_image.required'     => __('Please upload Aadhaar back image.'),
            'aadhaar_back_image.mimes'        => __('Aadhaar back image must be a JPG or PNG image.'),
            'membership_plan_id.required'     => __('Please select a membership plan.'),
            'payment_utr_number.required'     => __('Payment UTR number is required.'),
            'payment_utr_number.regex'        => __('UTR number must be 12-22 digits only.'),
            'payment_utr_number.unique'       => __('This UTR number has already been used. Please enter a different UTR number.'),
            'pan_card_file.required'          => __('Please upload your PAN card image.'),
            'pan_card_file.file'              => __('Please upload a valid PAN card file.'),
            'pan_card_file.mimes'             => __('PAN card must be a JPG or PNG image.'),
            'payment_screenshot.required'     => __('Please upload payment screenshot.'),
            'payment_screenshot.file'         => __('Please upload a valid payment screenshot.'),
            'payment_screenshot.mimes'        => __('Payment screenshot must be a JPG or PNG image.'),
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return Account
     */
    protected function create(array $data)
    {
        // Get membership plan using direct DB query
        $membershipPlan = DB::table('membership_plans')->where('id', $data['membership_plan_id'])->first();
        
        if (!$membershipPlan) {
            throw new \Exception('Membership plan not found');
        }
        
        // Upload PAN card file - Simple upload
        $panCardFile = null;
        try {
            if (request()->hasFile('pan_card_file')) {
                $file = request()->file('pan_card_file');
                $filename = 'pan_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/accounts/kyc'), $filename);
                $panCardFile = 'storage/accounts/kyc/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error('PAN card upload failed: ' . $e->getMessage());
        }

        // Upload payment screenshot - Simple upload
        $paymentScreenshot = null;
        try {
            if (request()->hasFile('payment_screenshot')) {
                $file = request()->file('payment_screenshot');
                $filename = 'payment_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/accounts/payments'), $filename);
                $paymentScreenshot = 'storage/accounts/payments/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error('Payment screenshot upload failed: ' . $e->getMessage());
        }

        // Upload Aadhaar front image
        $aadhaarFrontImage = null;
        try {
            if (request()->hasFile('aadhaar_front_image')) {
                $file = request()->file('aadhaar_front_image');
                $filename = 'aadhaar_front_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/accounts/kyc'), $filename);
                $aadhaarFrontImage = 'storage/accounts/kyc/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error('Aadhaar front image upload failed: ' . $e->getMessage());
        }

        // Upload Aadhaar back image
        $aadhaarBackImage = null;
        try {
            if (request()->hasFile('aadhaar_back_image')) {
                $file = request()->file('aadhaar_back_image');
                $filename = 'aadhaar_back_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/accounts/kyc'), $filename);
                $aadhaarBackImage = 'storage/accounts/kyc/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error('Aadhaar back image upload failed: ' . $e->getMessage());
        }

        // Calculate membership dates
        $membershipStartDate = now();
        $membershipEndDate = now()->addDays($membershipPlan->duration_days);

        // Create account with pending status
        return $this->accountRepository->create([
            'first_name'            => $data['first_name'],
            'last_name'             => $data['last_name'],
            'username'              => $data['username'],
            'email'                 => $data['email'],
            'phone'                 => $data['phone'],
            'password'              => bcrypt($data['password']),
            'membership_plan_id'    => $data['membership_plan_id'],
            'membership_start_date' => $membershipStartDate,
            'membership_end_date'   => $membershipEndDate,
            'membership_status'     => 'pending',
            'pan_card_number'       => strtoupper($data['pan_card_number']),
            'pan_card_file'         => $panCardFile,
            'aadhaar_number'        => $data['aadhaar_number'],
            'aadhaar_front_image'   => $aadhaarFrontImage,
            'aadhaar_back_image'    => $aadhaarBackImage,
            'payment_utr_number'    => $data['payment_utr_number'],
            'payment_screenshot'    => $paymentScreenshot,
            'account_status'        => 'pending',
        ]);
    }

    /**
     * Get a validator for vendor registration
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function vendorValidator(array $data)
    {
        $rules = [
            'first_name'         => 'required|max:120',
            'last_name'          => 'required|max:120',
            'company'            => 'required|max:255',
            'username'           => 'required|max:60|min:2|unique:re_accounts,username',
            'email'              => 'required|email|max:255|unique:re_accounts',
            'password'           => 'required|min:6|confirmed',
            'phone'              => 'required|' . BaseHelper::getPhoneValidationRule(),
            'pan_card_number'    => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i', 'unique:re_accounts,pan_card_number'],
            'pan_card_file'      => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'aadhaar_number'     => ['required', 'regex:/^[0-9]{12}$/', 'unique:re_accounts,aadhaar_number'],
            'aadhaar_front_image' => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'aadhaar_back_image'  => 'required|file|mimes:jpeg,png,jpg|max:2048',
        ];

        if (is_plugin_active('captcha') && setting('enable_captcha') && setting('real_estate_enable_recaptcha_in_register_page', 0)) {
            $rules += ['g-recaptcha-response' => 'required|captcha'];
        }

        return Validator::make($data, $rules, [
            'g-recaptcha-response.required'   => __('Captcha Verification Failed!'),
            'g-recaptcha-response.captcha'    => __('Captcha Verification Failed!'),
            'company.required'                => __('Company/Agency name is required.'),
            'pan_card_number.regex'           => __('Invalid PAN format. Valid format: AFZPK7190K (5 letters + 4 digits + 1 letter)'),
            'pan_card_number.unique'          => __('This PAN card is already registered.'),
            'aadhaar_number.required'         => __('Aadhaar card number is required.'),
            'aadhaar_number.regex'            => __('Aadhaar number must be exactly 12 digits.'),
            'aadhaar_number.unique'           => __('This Aadhaar card is already registered.'),
            'aadhaar_front_image.required'    => __('Please upload Aadhaar front image.'),
            'aadhaar_front_image.mimes'       => __('Aadhaar front image must be a JPG or PNG image.'),
            'aadhaar_back_image.required'     => __('Please upload Aadhaar back image.'),
            'aadhaar_back_image.mimes'        => __('Aadhaar back image must be a JPG or PNG image.'),
            'pan_card_file.required'          => __('Please upload your PAN card image.'),
            'pan_card_file.file'              => __('Please upload a valid PAN card file.'),
            'pan_card_file.mimes'             => __('PAN card must be a JPG or PNG image.'),
        ]);
    }

    /**
     * Create a new vendor account
     *
     * @param array $data
     * @return Account
     */
    protected function createVendor(array $data)
    {
        // Upload PAN card file
        $panCardFile = null;
        try {
            if (request()->hasFile('pan_card_file')) {
                $file = request()->file('pan_card_file');
                $filename = 'pan_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/accounts/kyc'), $filename);
                $panCardFile = 'storage/accounts/kyc/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error('PAN card upload failed: ' . $e->getMessage());
        }

        // Upload Aadhaar front image
        $aadhaarFrontImage = null;
        try {
            if (request()->hasFile('aadhaar_front_image')) {
                $file = request()->file('aadhaar_front_image');
                $filename = 'aadhaar_front_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/accounts/kyc'), $filename);
                $aadhaarFrontImage = 'storage/accounts/kyc/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error('Aadhaar front image upload failed: ' . $e->getMessage());
        }

        // Upload Aadhaar back image
        $aadhaarBackImage = null;
        try {
            if (request()->hasFile('aadhaar_back_image')) {
                $file = request()->file('aadhaar_back_image');
                $filename = 'aadhaar_back_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/accounts/kyc'), $filename);
                $aadhaarBackImage = 'storage/accounts/kyc/' . $filename;
            }
        } catch (\Exception $e) {
            \Log::error('Aadhaar back image upload failed: ' . $e->getMessage());
        }

        // Create vendor account with pending status
        return $this->accountRepository->create([
            'first_name'            => $data['first_name'],
            'last_name'             => $data['last_name'],
            'company'               => $data['company'],
            'username'              => $data['username'],
            'email'                 => $data['email'],
            'phone'                 => $data['phone'],
            'password'              => bcrypt($data['password']),
            'account_type'          => 'vendor',
            'pan_card_number'       => strtoupper($data['pan_card_number']),
            'pan_card_file'         => $panCardFile,
            'aadhaar_number'        => $data['aadhaar_number'],
            'aadhaar_front_image'   => $aadhaarFrontImage,
            'aadhaar_back_image'    => $aadhaarBackImage,
            'account_status'        => 'pending',
        ]);
    }

    /**
     * Send registration email to vendor
     */
    protected function sendVendorRegistrationEmail($account)
    {
        try {
            $siteName = setting('site_title', 'AADS Property Portal');
            $subject = "vendor Registration Received - Account Under Review | " . $siteName;
            
            $message = "
            <html>
            <head>
                <title>vendor Registration Received</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #007bff; color: white; padding: 20px; text-align: center;'>
                        <h1 style='margin: 0;'>" . $siteName . "</h1>
                    </div>
                    <div style='padding: 20px; background: #f9f9f9;'>
                        <h2>Hello " . $account->name . ",</h2>
                        <p>Thank you for registering as a vendor with us! Your account has been created successfully.</p>
                        
                        <p><strong>Current Status:</strong> <span style='background: #ffc107; color: #000; padding: 5px 15px; border-radius: 20px;'>PENDING APPROVAL</span></p>
                        
                        <p>Your vendor account is currently under review. Our team will verify your KYC details.</p>
                        
                        <div style='background: #fff; border: 2px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                            <h3 style='color: #28a745; margin-top: 0;'>Your Details</h3>
                            <table style='width: 100%;'>
                                <tr><td><strong>Name:</strong></td><td>" . $account->name . "</td></tr>
                                <tr><td><strong>Company:</strong></td><td>" . $account->company . "</td></tr>
                                <tr><td><strong>Email:</strong></td><td>" . $account->email . "</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>" . $account->phone . "</td></tr>
                            </table>
                        </div>
                        
                        <p><strong>What happens next?</strong></p>
                        <ol>
                            <li>Our team will verify your KYC documents</li>
                            <li>Once approved, you will receive a confirmation email</li>
                            <li>You can then login and start listing properties</li>
                        </ol>
                        
                        <p>Best Regards,<br><strong>" . $siteName . " Team</strong></p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $siteName . " <" . setting('email_from_address', 'noreply@sspl20.com') . ">\r\n";

            mail($account->email, $subject, $message, $headers);
        } catch (\Exception $e) {
            \Log::error('vendor registration email failed: ' . $e->getMessage());
        }
    }

    /**
     * Send notification email to admin for vendor registration
     */
    protected function sendvendorAdminNotificationEmail($account)
    {
        try {
            $adminEmail = setting('admin_email');
            if (!$adminEmail) return;

            $siteName = setting('site_title', 'AADS Property Portal');
            $subject = "New vendor Registration Pending Approval | " . $siteName;
            
            $message = "
            <html>
            <head>
                <title>New vendor Registration</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #dc3545; color: white; padding: 20px; text-align: center;'>
                        <h1 style='margin: 0;'>New vendor Registration - Pending Approval</h1>
                    </div>
                    <div style='padding: 20px; background: #f9f9f9;'>
                        <p>A new vendor has registered and is waiting for approval.</p>
                        
                        <div style='background: #fff; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                            <h3 style='margin-top: 0;'>vendor Details</h3>
                            <table style='width: 100%;'>
                                <tr><td><strong>Name:</strong></td><td>" . $account->name . "</td></tr>
                                <tr><td><strong>Company:</strong></td><td>" . $account->company . "</td></tr>
                                <tr><td><strong>Email:</strong></td><td>" . $account->email . "</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>" . $account->phone . "</td></tr>
                                <tr><td><strong>Username:</strong></td><td>" . $account->username . "</td></tr>
                            </table>
                        </div>
                        
                        <div style='background: #fff; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                            <h3 style='margin-top: 0;'>KYC Details</h3>
                            <table style='width: 100%;'>
                                <tr><td><strong>PAN Card:</strong></td><td>" . $account->pan_card_number . "</td></tr>
                                <tr><td><strong>Aadhaar:</strong></td><td>" . $account->aadhaar_number . "</td></tr>
                            </table>
                        </div>
                        
                        <p style='text-align: center; margin-top: 20px;'>
                            <a href='" . url('/admin/real-estate/accounts') . "' style='background: #28a745; color: white; padding: 10px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Review and Approve</a>
                        </p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $siteName . " <" . setting('email_from_address', 'noreply@sspl20.com') . ">\r\n";

            mail($adminEmail, $subject, $message, $headers);
        } catch (\Exception $e) {
            \Log::error('vendor admin notification email failed: ' . $e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getVerify()
    {
        if (!RealEstateHelper::isRegisterEnabled()) {
            abort(404);
        }

        return view('plugins/real-estate::account.auth.verify');
    }
}

