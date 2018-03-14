<?php namespace Affiliates\Models;

use Str;
use Auth;
use Model;
use October\Rain\Auth\Models\User as UserBase;


class Affiliate extends UserBase
{
    use \October\Rain\Database\Traits\Purgeable;

    protected $table = 'bbf_affiliates';

    protected $primaryKey = 'id';

    public $rules = [
        'email'    => 'required|between:6,255|email|unique:bbf_affiliates',
        'username' => 'required|between:2,255|unique:bbf_affiliates',
        'password' => 'required:create|between:4,255|confirmed',
        'password_confirmation' => 'required_with:password|between:4,255'
    ];

    protected $hidden = ['password'];

    public $belongsToMany = [];

    public $hasMany = [
        'coupons' => ['Bbf\Models\Coupon']
    ];

    protected $fillable = [
        'username',
        'email',
        'password',
        'password_confirmation'
    ];

    protected $purgeable = ['password_confirmation'];
}