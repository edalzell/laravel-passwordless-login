<?php

namespace Grosv\LaravelPasswordlessLogin;

use Grosv\LaravelPasswordlessLogin\Events\LoginLinkExpired;
use Grosv\LaravelPasswordlessLogin\Events\LoginLinkInvalid;
use Grosv\LaravelPasswordlessLogin\Events\LoginLinkSuccessful;
use Grosv\LaravelPasswordlessLogin\Exceptions\ExpiredSignatureException;
use Grosv\LaravelPasswordlessLogin\Exceptions\InvalidSignatureException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;

class LaravelPasswordlessLoginController extends Controller
{
    public function __construct(
        private readonly PasswordlessLoginService $passwordlessLoginService,
        private readonly UrlGenerator $urlGenerator,
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

        $guard = $user->guard_name ?? config('laravel-passwordless-login.user_guard');

        $rememberLogin = $user->should_remember_login ?? config('laravel-passwordless-login.remember_login');

        $redirectUrl = $user->redirect_url ?? ($request->redirect_to ?: config('laravel-passwordless-login.redirect_on_success'));

        if (method_exists(Auth::guard($guard), 'login')) {
            Auth::guard($guard)->login($user, $rememberLogin);

            abort_unless($user == Auth::guard($guard)->user(), 401);
        }

        LoginLinkSuccessful::dispatch($user);

        return $user->guard_name ? $user->onPasswordlessLoginSuccess($request) : redirect($redirectUrl);
    }

    /**
     * Redirect testing.
     *
     * @return Response
     */
    public function redirectTestRoute()
    {
        return response(Auth::user()->name, 200);
    }

    /**
     * Redirect override testing.
     *
     * @return Response
     */
    public function overrideTestRoute()
    {
        return response('Redirected '.Auth::user()->name, 200);
    }
}
