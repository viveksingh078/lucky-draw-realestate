<?php

namespace Botble\RealEstate\Tables;

use BaseHelper;
use Botble\RealEstate\Repositories\Interfaces\AccountInterface;
use Botble\Table\Abstracts\TableAbstract;
use Html;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use RvMedia;
use Yajra\DataTables\DataTables;

class AccountTable extends TableAbstract
{

    /**
     * @var bool
     */
    protected $hasActions = true;

    /**
     * @var bool
     */
    protected $hasFilter = true;

    /**
     * @var bool
     */
    protected $hasResponsive = false;

    /**
     * AccountTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     * @param AccountInterface $accountRepository
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, AccountInterface $accountRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $accountRepository;

        if (!Auth::user()->hasAnyPermission(['account.edit', 'account.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @since 2.1
     */
    public function ajax()
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('first_name', function ($item) {
                if (!Auth::user()->hasPermission('account.edit')) {
                    return clean($item->name);
                }

                return Html::link(route('account.edit', $item->id), clean($item->name));
            })
            ->editColumn('avatar_id', function ($item) {
                return Html::image(RvMedia::getImageUrl($item->avatar->url, 'thumb', false, RvMedia::getDefaultImage()),
                    clean($item->name), ['width' => 50]);
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('phone', function ($item) {
                return clean($item->phone ?: '&mdash;');
            })
            ->editColumn('membership_plan_id', function ($item) {
                if ($item->membership_plan_id) {
                    $plan = \DB::table('membership_plans')->where('id', $item->membership_plan_id)->first();
                    return $plan ? $plan->name : '—';
                }
                return '—';
            })
            ->editColumn('pan_card_number', function ($item) {
                return $item->pan_card_number ?: '—';
            })
            ->editColumn('payment_utr_number', function ($item) {
                return $item->payment_utr_number ? substr($item->payment_utr_number, 0, 15) : '—';
            })
            ->editColumn('account_status', function ($item) {
                $status = $item->account_status ?: 'approved';
                $colors = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ];
                $color = $colors[$status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($status) . '</span>';
            })
            ->addColumn('operations', function ($item) {
                $actions = $this->getOperations('account.edit', 'account.destroy', $item);
                
                // Add approve/reject buttons for pending accounts
                if (($item->account_status === 'pending' || $item->account_status === null) && Auth::user()->hasPermission('account.edit')) {
                    $csrfToken = csrf_token();
                    $approveUrl = route('account.approve', $item->id);
                    $rejectUrl = route('account.reject', $item->id);
                    
                    $approveBtn = '<button class="btn btn-success btn-sm me-1" onclick="approveAccount(' . $item->id . ', \'' . $csrfToken . '\', \'' . $approveUrl . '\')" title="Approve"><i class="fa fa-check"></i></button>';
                    $rejectBtn = '<button class="btn btn-danger btn-sm me-1" onclick="rejectAccount(' . $item->id . ', \'' . $csrfToken . '\', \'' . $rejectUrl . '\')" title="Reject"><i class="fa fa-times"></i></button>';
                    $actions = $approveBtn . $rejectBtn . $actions;
                }
                
                return $actions;
            })
            ->rawColumns(['account_status', 'operations', 'checkbox', 'first_name', 'avatar_id', 'created_at']);

        return $this->toJson($data);
    }

    /**
     * Get the query object to be processed by the table.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     *
     * @since 2.1
     */
    public function query()
    {
        $query = $this->repository
            ->getModel()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'created_at',
                'credits',
                'avatar_id',
                'account_status',
                'membership_plan_id',
                'pan_card_number',
                'payment_utr_number',
            ])
            ->with(['avatar'])
            ->withCount(['properties']);

        return $this->applyScopes($query);
    }

    /**
     * @return array
     *
     * @since 2.1
     */
    public function columns()
    {
        return [
            'id'         => [
                'title' => 'ID',
                'width' => '20px',
                'class' => 'text-center',
            ],
            'avatar_id'  => [
                'title' => 'Image',
                'width' => '50px',
                'class' => 'text-center no-sort',
                'orderable' => false,
            ],
            'first_name' => [
                'title' => 'Name',
                'class' => 'text-start',
                'width' => '120px',
            ],
            'email'      => [
                'title' => 'Email',
                'class' => 'text-start',
                'width' => '150px',
            ],
            'phone'    => [
                'title' => 'Phone',
                'class' => 'text-start',
                'width' => '100px',
            ],
            'membership_plan_id' => [
                'title' => 'Plan',
                'class' => 'text-center',
                'width' => '70px',
            ],
            'pan_card_number' => [
                'title' => 'PAN',
                'class' => 'text-center',
                'width' => '90px',
            ],
            'payment_utr_number' => [
                'title' => 'UTR',
                'class' => 'text-center',
                'width' => '110px',
            ],
            'account_status' => [
                'title' => 'Status',
                'class' => 'text-center',
                'width' => '80px',
            ],
            'created_at' => [
                'title' => 'Date',
                'class' => 'text-center',
                'width' => '90px',
            ],
        ];
    }

    /**
     * @return array
     *
     * @throws \Throwable
     * @since 2.1
     */
    public function buttons()
    {
        return $this->addCreateButton(route('account.create'), 'account.create');
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('account.deletes'), 'account.destroy', parent::bulkActions());
    }

    /**
     * @return array
     */
    public function getBulkChanges(): array
    {
        return [
            'first_name' => [
                'title'    => trans('plugins/real-estate::account.first_name'),
                'type'     => 'text',
                'validate' => 'required|max:120',
            ],
            'last_name'  => [
                'title'    => trans('plugins/real-estate::account.last_name'),
                'type'     => 'text',
                'validate' => 'required|max:120',
            ],
            'email'      => [
                'title'    => trans('core/base::tables.email'),
                'type'     => 'text',
                'validate' => 'required|max:120|email',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type'  => 'date',
            ],
        ];
    }
}
