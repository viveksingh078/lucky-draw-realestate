<?php

namespace Botble\RealEstate\Forms;

use Assets;
use BaseHelper;
use Botble\Base\Forms\FormAbstract;
use Botble\RealEstate\Http\Requests\AccountCreateRequest;
use Botble\RealEstate\Models\Account;
use RealEstateHelper;
use Throwable;

class AccountForm extends FormAbstract
{

    /**
     * @var string
     */
    protected $template = 'plugins/real-estate::account.admin.form';

    /**
     * @return mixed|void
     * @throws Throwable
     */
    public function buildForm()
    {
        Assets::addStylesDirectly('vendor/core/plugins/real-estate/css/account-admin.css')
            ->addScriptsDirectly(['/vendor/core/plugins/real-estate/js/account-admin.js']);

        $this
            ->setupModel(new Account)
            ->setValidatorClass(AccountCreateRequest::class)
            ->withCustomFields()
            ->add('first_name', 'text', [
                'label'      => trans('plugins/real-estate::account.first_name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/real-estate::account.first_name'),
                    'data-counter' => 120,
                ],
            ])
            ->add('last_name', 'text', [
                'label'      => trans('plugins/real-estate::account.last_name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/real-estate::account.last_name'),
                    'data-counter' => 120,
                ],
            ])
            ->add('username', 'text', [
                'label'      => trans('plugins/real-estate::account.username'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/real-estate::account.username_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('company', 'text', [
                'label'      => trans('plugins/real-estate::account.company'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'placeholder'  => trans('plugins/real-estate::account.company_placeholder'),
                    'data-counter' => 255,
                ],
            ])
            ->add('phone', 'text', [
                'label'      => trans('plugins/real-estate::account.phone'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'placeholder'  => trans('plugins/real-estate::account.phone_placeholder'),
                    'data-counter' => 20,
                ],
            ])
            ->add('dob', 'date', [
                'label'         => trans('plugins/real-estate::account.dob'),
                'label_attr'    => ['class' => 'control-label'],
                'attr'          => [
                    'data-date-format' => config('core.base.general.date_format.js.date'),
                ],
                'default_value' => BaseHelper::formatDate(now()),
            ])
            ->add('email', 'text', [
                'label'      => trans('plugins/real-estate::account.form.email'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/real-estate::account.email_placeholder'),
                    'data-counter' => 60,
                ],
            ])
            ->add('is_featured', 'onOff', [
                'label'         => trans('core/base::forms.is_featured'),
                'label_attr'    => ['class' => 'control-label'],
                'default_value' => false,
            ])
            ->add('is_change_password', 'checkbox', [
                'label'      => trans('plugins/real-estate::account.form.change_password'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'hrv-checkbox',
                ],
                'value'      => 1,
            ])
            ->add('password', 'password', [
                'label'      => trans('plugins/real-estate::account.form.password'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'data-counter' => 60,
                ],
                'wrapper'    => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ($this->getModel()->id ? ' hidden' : null),
                ],
            ])
            ->add('password_confirmation', 'password', [
                'label'      => trans('plugins/real-estate::account.form.password_confirmation'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'data-counter' => 60,
                ],
                'wrapper'    => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ($this->getModel()->id ? ' hidden' : null),
                ],
            ])
            ->add('avatar_image', 'mediaImage', [
                'label'      => trans('core/base::forms.image'),
                'label_attr' => ['class' => 'control-label'],
                'value'      => $this->getModel()->avatar->url,
            ])
            ->setBreakFieldPoint('avatar_image');

        // Add KYC and Payment fields if account exists
        if ($this->getModel()->id) {
            $this->add('membership_plan_info', 'html', [
                'html' => '<div class="form-group mb-3"><label class="control-label">Membership Plan</label><div class="form-control-plaintext">' . 
                    ($this->getModel()->membership_plan_id ? 
                        \DB::table('membership_plans')->where('id', $this->getModel()->membership_plan_id)->value('name') : 
                        'N/A') . 
                    '</div></div>',
            ])
            ->add('pan_card_number', 'text', [
                'label'      => 'PAN Card Number',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'readonly' => 'readonly',
                ],
            ])
            ->add('pan_card_file_display', 'html', [
                'html' => '<div class="form-group mb-3"><label class="control-label">PAN Card File</label><div>' . 
                    ($this->getModel()->pan_card_file ? 
                        '<a href="' . url($this->getModel()->pan_card_file) . '" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-download"></i> View PAN Card</a>' : 
                        'Not uploaded') . 
                    '</div></div>',
            ])
            ->add('aadhaar_number', 'text', [
                'label'      => 'Aadhaar Card Number',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'readonly' => 'readonly',
                ],
            ])
            ->add('aadhaar_front_image_display', 'html', [
                'html' => '<div class="form-group mb-3"><label class="control-label">Aadhaar Front Image</label><div>' . 
                    ($this->getModel()->aadhaar_front_image ? 
                        '<a href="' . url($this->getModel()->aadhaar_front_image) . '" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-download"></i> View Aadhaar Front</a>' : 
                        'Not uploaded') . 
                    '</div></div>',
            ])
            ->add('aadhaar_back_image_display', 'html', [
                'html' => '<div class="form-group mb-3"><label class="control-label">Aadhaar Back Image</label><div>' . 
                    ($this->getModel()->aadhaar_back_image ? 
                        '<a href="' . url($this->getModel()->aadhaar_back_image) . '" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-download"></i> View Aadhaar Back</a>' : 
                        'Not uploaded') . 
                    '</div></div>',
            ])
            ->add('payment_utr_number', 'text', [
                'label'      => 'Payment UTR Number',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'readonly' => 'readonly',
                ],
            ])
            ->add('payment_screenshot_display', 'html', [
                'html' => '<div class="form-group mb-3"><label class="control-label">Payment Screenshot</label><div>' . 
                    ($this->getModel()->payment_screenshot ? 
                        '<a href="' . url($this->getModel()->payment_screenshot) . '" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-download"></i> View Payment Screenshot</a>' : 
                        'Not uploaded') . 
                    '</div></div>',
            ])
            ->add('account_status', 'customSelect', [
                'label'      => 'Account Status',
                'label_attr' => ['class' => 'control-label'],
                'choices'    => [
                    'pending'  => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ],
            ])
            ->add('admin_notes', 'textarea', [
                'label'      => 'Admin Notes',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'rows' => 3,
                    'placeholder' => 'Add notes about this account...',
                ],
            ]);
        }


        if ($this->getModel()->id && RealEstateHelper::isEnabledCreditsSystem()) {
            $this->addMetaBoxes([
                'credits' => [
                    'title'   => null,
                    'content' => view('plugins/real-estate::account.admin.credits', [
                        'account'      => $this->model,
                        'transactions' => $this->model->transactions()->orderBy('created_at', 'DESC')->get(),
                    ])->render(),
                    'wrap'    => false,
                ],
            ]);
        }
    }
}
