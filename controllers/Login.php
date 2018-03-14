<?php namespace Affiliates\Controllers;


use Affiliates\Facades\Auth;
use Affiliates\Facades\Helper;
use Bbf\Models\Affiliate;
use Flash;
use Event;
use Session;
use Request;
use Redirect;
use Response;
use Validator;
use Exception;
use ValidationException;
use System\Traits\ViewMaker;
use Bbf\Models\SubscriptionPlan;

class Login extends BaseController
{
    protected $publicActions = ['index', 'forgot'];

    /**
     * @var bool wether a code exists for password reset or not
     */
    protected $hasCode = false;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Anmelden';
        $this->layout = 'default';
    }

    /**
     * Default route, displays the login page
     */
    public function index()
    {
        if(Auth::check())
        {
            return Helper::redirect('stats');
        }
    }

    public function onLogin()
    {
        try {
            /*
             * Validate input
             */
            $data = post();
            $rules = [
                'email' => 'required|email|between:6,255',
                'password' => 'required|between:4,255'
            ];

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Authenticate user
             */
            $credentials = [
                'email'    => array_get($data, 'email'),
                'password' => array_get($data, 'password')
            ];

            Event::fire('bbf.beforeAuthenticate', [$this, $credentials]);

            Auth::authenticate($credentials, true);

            return Helper::redirect('stats');
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    public function forgot($code = null)
    {
        if(Auth::check())
        {
            return Helper::redirect('stats');
        }

        $this->hasCode = !is_null($code);
        $this->pageTitle = 'Passwort vergessen';
    }

    /**
     * Trigger the password reset email
     */
    public function onRestorePassword()
    {
        $rules = [
            'email' => 'required|email|between:6,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        if (!$user = Affiliate::findByEmail(post('email'))) {
            throw new ApplicationException(trans('rainlab.user::lang.account.invalid_user'));
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);

        $link = $this->controller->currentPageUrl([
            $this->property('paramCode') => $code
        ]);

        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('rainlab.user::mail.restore', $data, function($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });
    }

    /**
     * Perform the password reset
     */
    public function onResetPassword()
    {
        $rules = [
            'code'     => 'required',
            'password' => 'required|between:4,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        /*
         * Break up the code parts
         */
        $parts = explode('!', post('code'));
        if (count($parts) != 2) {
            throw new ValidationException(['code' => trans('rainlab.user::lang.account.invalid_activation_code')]);
        }

        list($userId, $code) = $parts;

        if (!strlen(trim($userId)) || !($user = Auth::findUserById($userId))) {
            throw new ApplicationException(trans('rainlab.user::lang.account.invalid_user'));
        }

        if (!$user->attemptResetPassword($code, post('password'))) {
            throw new ValidationException(['code' => trans('rainlab.user::lang.account.invalid_activation_code')]);
        }
    }

    /**
     * Returns the reset password code from the URL
     * @return string
     */
    public function code()
    {
        $routeParameter = $this->property('paramCode');

        return $this->param($routeParameter);
    }
}