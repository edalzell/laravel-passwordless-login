<?php

namespace Grosv\LaravelPasswordlessLogin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleAuthenticatedUsers
{
    public function handle(Request $request, \Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                return redirect($request->get('redirect_to', self::getHomeRoute()));
            }
        }

        return $next($request);
    }

    /**
     * Figure out the home route the programmer has defined in their RouteServiceProvider (if it exists) by checking the
     * home() function, then the HOME constant, then finally the value from the config file
     *
     * @static function getHomeRoute
     */
    private static function getHomeRoute(): string
    {
        $provider_name = '\\'.app()->getNamespace().'Providers\\RouteServiceProvider';

        if (class_exists($provider_name)) {
            if (method_exists($provider_name, 'home')) {
                return call_user_func([$provider_name, 'home']);
            }

            if (defined($provider_name.'::HOME')) {
                return constant($provider_name.'::HOME');
            }
        }

        return config('laravel-passwordless-login.redirect_on_success', '/');
    }
}
