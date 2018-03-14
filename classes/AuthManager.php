<?php namespace Affiliates\Classes;

use October\Rain\Auth\Manager as RainAuthManager;

class AuthManager extends RainAuthManager
{
    protected static $instance;

    protected $sessionKey = 'affiliate_auth';

    protected $userModel = 'Affiliates\Models\Affiliate';

    //protected $groupModel = 'Bbf\Models\UserGroup';

    //protected $throttleModel = 'Bbf\Models\Throttle';

    protected $useThrottle = false;

    protected $requireActivation = false;

    /**
     * {@inheritDoc}
     */
    public function register(array $credentials, $activate = false)
    {
        return parent::register($credentials, $activate);
    }
}
