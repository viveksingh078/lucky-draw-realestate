<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Traits\HasDeleteManyItemsTrait;
use Botble\Media\Repositories\Interfaces\MediaFileInterface;
use Botble\RealEstate\Forms\AccountForm;
use Botble\RealEstate\Http\Requests\AccountCreateRequest;
use Botble\RealEstate\Http\Requests\AccountEditRequest;
use Botble\RealEstate\Http\Resources\AccountResource;
use Botble\RealEstate\Repositories\Interfaces\AccountInterface;
use Botble\RealEstate\Tables\AccountTable;
use Carbon\Carbon;
use EmailHandler;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends BaseController
{
    use HasDeleteManyItemsTrait;

    /**
     * @var AccountInterface
     */
    protected $accountRepository;

    /**
     * @param AccountInterface $accountRepository
     */
    public function __construct(AccountInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param AccountTable $dataTable
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index(AccountTable $dataTable)
    {
        page_title()->setTitle(trans('plugins/real-estate::account.name'));

        return $dataTable->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/real-estate::account.create'));

        return $formBuilder
            ->create(AccountForm::class)
            ->remove('is_change_password')
            ->renderForm();
    }

    /**
     * @param AccountCreateRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(AccountCreateRequest $request, BaseHttpResponse $response)
    {
        $account = $this->accountRepository->getModel();
        $account->fill($request->input());
        $account->is_featured = $request->input('is_featured');
        $account->confirmed_at = now();

        $account->password = bcrypt($request->input('password'));
        $account->dob = Carbon::parse($request->input('dob'))->toDateString();

        if ($request->input('avatar_image')) {
            $image = app(MediaFileInterface::class)->getFirstBy(['url' => $request->input('avatar_image')]);
            if ($image) {
                $account->avatar_id = $image->id;
            }
        }

        $account = $this->accountRepository->createOrUpdate($account);

        event(new CreatedContentEvent(ACCOUNT_MODULE_SCREEN_NAME, $request, $account));

        return $response
            ->setPreviousUrl(route('account.index'))
            ->setNextUrl(route('account.edit', $account->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param int $id
     * @param FormBuilder $formBuilder
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function edit($id, FormBuilder $formBuilder)
    {
        $account = $this->accountRepository->findOrFail($id);

        page_title()->setTitle(trans('plugins/real-estate::account.edit', ['name' => $account->name]));

        $account->password = null;

        return $formBuilder
            ->create(AccountForm::class, ['model' => $account])
            ->renderForm();
    }

    /**
     * @param int $id
     * @param AccountEditRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, AccountEditRequest $request, BaseHttpResponse $response)
    {
        $account = $this->accountRepository->findOrFail($id);

        $account->fill($request->except('password'));

        if ($request->input('is_change_password') == 1) {
            $account->password = bcrypt($request->input('password'));
        }

        $account->dob = Carbon::parse($request->input('dob'))->toDateString();

        if ($request->input('avatar_image')) {
            $image = app(MediaFileInterface::class)->getFirstBy(['url' => $request->input('avatar_image')]);
            if ($image) {
                $account->avatar_id = $image->id;
            }
        }

        $account->is_featured = $request->input('is_featured');
        $account = $this->accountRepository->createOrUpdate($account);

        event(new UpdatedContentEvent(ACCOUNT_MODULE_SCREEN_NAME, $request, $account));

        return $response
            ->setPreviousUrl(route('account.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    /**
     * @param Request $request
     * @param $id
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $account = $this->accountRepository->findOrFail($id);
            $this->accountRepository->delete($account);
            event(new DeletedContentEvent(ACCOUNT_MODULE_SCREEN_NAME, $request, $account));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.cannot_delete'));
        }
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     * @throws Exception
     */
    public function deletes(Request $request, BaseHttpResponse $response)
    {
        return $this->executeDeleteItems($request, $response, $this->accountRepository, ACCOUNT_MODULE_SCREEN_NAME);
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     */
    public function getList(Request $request, BaseHttpResponse $response)
    {
        $keyword = $request->input('q');

        if (!$keyword) {
            return $response->setData([]);
        }

        $data = $this->accountRepository->getModel()
            ->where('first_name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('last_name', 'LIKE', '%' . $keyword . '%')
            ->select(['id', 'first_name', 'last_name'])
            ->take(10)
            ->get();

        return $response->setData(AccountResource::collection($data));
    }

    /**
     * Approve account
     *
     * @param int $id
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function approve($id, BaseHttpResponse $response)
    {
        try {
            $account = $this->accountRepository->findOrFail($id);
            
            // Get membership plan details
            $membershipPlan = DB::table('membership_plans')->where('id', $account->membership_plan_id)->first();
            
            $account->account_status = 'approved';
            $account->membership_status = 'active';
            $account->confirmed_at = now();
            $account->approved_at = now();
            $account->approved_by = auth()->id();
            
            // Initialize draw credits and wallet balance based on membership plan
            if ($membershipPlan) {
                $account->draws_used = 0;
                $account->draws_remaining = $membershipPlan->draws_allowed;
                
                // Set wallet balance to plan price
                $account->wallet_balance = $membershipPlan->price;
                $account->wallet_on_hold = 0;
                $account->wallet_used = 0;
            }
            
            $this->accountRepository->createOrUpdate($account);

            // Send approval email to user
            try {
                $this->sendApprovalEmail($account, $membershipPlan);
            } catch (\Exception $e) {
                // Continue even if email fails
            }

            return response()->json([
                'error' => false,
                'message' => 'Account approved successfully! Email sent to user.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to approve account: ' . $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject account
     *
     * @param int $id
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function reject($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $account = $this->accountRepository->findOrFail($id);
            
            $account->account_status = 'rejected';
            $account->membership_status = 'rejected';
            $account->admin_notes = $request->input('reason', 'Account rejected by admin');
            
            $this->accountRepository->createOrUpdate($account);

            // Send rejection email to user
            try {
                $this->sendRejectionEmail($account, $account->admin_notes);
            } catch (\Exception $e) {
                // Continue even if email fails
            }

            return response()->json([
                'error' => false,
                'message' => 'Account rejected successfully! Email sent to user.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to reject account: ' . $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Send approval email to user
     */
    protected function sendApprovalEmail($account, $membershipPlan)
    {
        try {
            $siteName = setting('site_title', 'AADS Property Portal');
            $planName = $membershipPlan->name ?? 'N/A';
            $planPrice = number_format($membershipPlan->price ?? 0, 2);
            $planDuration = $membershipPlan ? round($membershipPlan->duration_days / 30) . ' Months' : 'N/A';
            $loginUrl = route('public.account.login');
            
            $subject = "Account Approved - Welcome to " . $siteName;
            
            $message = "
            <html>
            <head>
                <title>Account Approved</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #28a745; color: white; padding: 20px; text-align: center;'>
                        <h1 style='margin: 0;'>Account Approved!</h1>
                    </div>
                    <div style='padding: 20px; background: #f9f9f9;'>
                        <h2>Congratulations " . $account->name . "!</h2>
                        <p>Your account has been approved and is now active.</p>
                        
                        <div style='background: #fff; border: 2px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                            <h3 style='color: #28a745; margin-top: 0;'>Your Membership Details</h3>
                            <table style='width: 100%;'>
                                <tr><td><strong>Plan:</strong></td><td>" . $planName . "</td></tr>
                                <tr><td><strong>Price:</strong></td><td>Rs. " . $planPrice . "</td></tr>
                                <tr><td><strong>Duration:</strong></td><td>" . $planDuration . "</td></tr>
                                <tr><td><strong>Status:</strong></td><td><span style='color: #28a745; font-weight: bold;'>ACTIVE</span></td></tr>
                            </table>
                        </div>
                        
                        <p><strong>You can now:</strong></p>
                        <ul>
                            <li>Login to your account</li>
                            <li>Post properties</li>
                            <li>Access all membership features</li>
                            <li>Manage your listings</li>
                        </ul>
                        
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='" . $loginUrl . "' style='background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Login Now</a>
                        </p>
                        
                        <p>Thank you for choosing " . $siteName . "!</p>
                        
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
            \Log::error('Approval email failed: ' . $e->getMessage());
        }
    }

    /**
     * Send rejection email to user
     */
    protected function sendRejectionEmail($account, $reason)
    {
        try {
            $siteName = setting('site_title', 'AADS Property Portal');
            $subject = "Account Registration - Update Required | " . $siteName;
            
            $message = "
            <html>
            <head>
                <title>Account Update Required</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #dc3545; color: white; padding: 20px; text-align: center;'>
                        <h1 style='margin: 0;'>Account Update Required</h1>
                    </div>
                    <div style='padding: 20px; background: #f9f9f9;'>
                        <h2>Hello " . $account->name . ",</h2>
                        <p>Thank you for your interest in " . $siteName . ".</p>
                        
                        <p>Unfortunately, we need some additional information or corrections before we can approve your account.</p>
                        
                        <div style='background: #fff; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0;'>
                            <h3 style='margin-top: 0; color: #dc3545;'>Reason:</h3>
                            <p>" . $reason . "</p>
                        </div>
                        
                        <p><strong>What to do next:</strong></p>
                        <ul>
                            <li>Review the reason mentioned above</li>
                            <li>Contact our support team for assistance</li>
                            <li>Provide the required information or corrections</li>
                        </ul>
                        
                        <p>If you have any questions, please feel free to contact us.</p>
                        
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
            \Log::error('Rejection email failed: ' . $e->getMessage());
        }
    }
}
