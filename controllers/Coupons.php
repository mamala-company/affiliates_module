<?php namespace Affiliates\Controllers;

class Coupons extends BaseController
{
    /**
     * @var array Extensions implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\ListController::class
    ];

    public $layout = 'default';

    public $pageTitle = 'Deine Coupons';

    public $publicActions = [];

    public $hiddenActions = [''];

    /**
     * @var array `ListController` configuration.
     */
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
    }

    public function listExtendQuery($query)
    {
        $query->where('affiliate_id', $this->user->id);
    }

}