<?php

namespace Grosv\LaravelPasswordlessLogin\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException as Exception;

class InvalidSignatureException extends Exception
{
    public function __construct()
    {
        parent::__construct(401, config('laravel-passwordless-login.invalid_signature_message'));
    }
}
