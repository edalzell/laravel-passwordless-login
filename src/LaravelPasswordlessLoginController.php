<?php

namespace Grosv\LaravelPasswordlessLogin;

use Grosv\LaravelPasswordlessLogin\Events\LoginLinkExpired;
use Grosv\LaravelPasswordlessLogin\Events\LoginLinkInvalid;
use Grosv\LaravelPasswordlessLogin\Events\LoginLinkSuccessful;
use Grosv\LaravelPasswordlessLogin\Exceptions\ExpiredSignatureException;
use Grosv\LaravelPasswordlessLogin\Exceptions\InvalidSignatureException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;

class LaravelPasswordlessLoginController extends Controller
{
    public function __construct(
        private readonly PasswordlessLoginService $passwordlessLoginService,
        private readonly UrlGenerator $urlGenerator,
        private readonly AuthFactory $auth,
        private readonly ConfigRepository $config,
        private readonly Redirector $redirector,
        private readonly ResponseFactory $response,
    ) {}

    /**
     * Handles login from the signed route.
     *
     *
     * @return RedirectResponse|Redirector
     *
     * @throws InvalidSignatureException|ExpiredSignatureException
     */
    public function login(Request $request)
    {
        if (! $this->urlGenerator->hasCorrectSignature($request) ||
            ($this->urlGenerator->signatureHasNotExpired($request) && ! $this->passwordlessLoginService->requestIsNew())) {
            LoginLinkInvalid::dispatch($this->passwordlessLoginService->user);

            throw new InvalidSignatureException;
        } elseif (! $this->urlGenerator->signatureHasNotExpired($request)) {
            LoginLinkExpired::dispatch($this->passwordlessLoginService->user);

            throw new ExpiredSignatureException;
        }

        $this->passwordlessLoginService->consumeRequest();

        $user = $this->passwordlessLoginService->user;

        $guard = $user->guard_name ?? $this->config->get('laravel-passwordless-login.user_guard');

        $rememberLogin = $user->should_remember_login ?? $this->config->get('laravel-passwordless-login.remember_login');

        $redirectUrl = $user->redirect_url ?? ($request->redirect_to ?: $this->config->get('laravel-passwordless-login.redirect_on_success'));

        $guardDriver = $this->auth->guard($guard);

        if (method_exists($guardDriver, 'login')) {
            $guardDriver->login($user, $rememberLogin);

            abort_unless($user == $guardDriver->user(), 401);
        }

        LoginLinkSuccessful::dispatch($user);

        return $user->guard_name ? $user->onPasswordlessLoginSuccess($request) : $this->redirector->to($redirectUrl);
    }

    /**
     * Redirect testing.
     *
     * @return Response
     */
    public function redirectTestRoute()
    {
        return $this->response->make($this->auth->user()->name, 200);
    }

    /**
     * Redirect override testing.
     *
     * @return Response
     */
    public function overrideTestRoute()
    {
        return $this->response->make('Redirected '.$this->auth->user()->name, 200);
    }
}
