<?php

namespace Grosv\LaravelPasswordlessLogin;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class HandleAuthenticatedUsers
{
    public function __construct(
        private readonly Application $app,
        private readonly AuthFactory $auth,
        private readonly ConfigRepository $config,
        private readonly Redirector $redirector,
    ) {}

    public function handle(Request $request, \Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {

                return $this->redirector->to($request->get('redirect_to', $this->getHomeRoute()));
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
    private function getHomeRoute(): string
    {
        $provider_name = '\\'.$this->app->getNamespace().'Providers\\RouteServiceProvider';

        if (class_exists($provider_name)) {
            if (method_exists($provider_name, 'home')) {
                return call_user_func([$provider_name, 'home']);
            }

            if (defined($provider_name.'::HOME')) {
                return constant($provider_name.'::HOME');
            }
        }

        return $this->config->get('laravel-passwordless-login.redirect_on_success', '/');
    }
}
