<?php namespace Affiliates\Classes;

use Closure;
use Response;
use Affiliates\Facades\Auth;
use Affiliates\Facades\Helper;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return Helper::redirect('login');
        }

        return $next($request);
    }
}
