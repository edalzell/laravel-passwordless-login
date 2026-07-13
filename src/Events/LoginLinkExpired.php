<?php

namespace Grosv\LaravelPasswordlessLogin\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoginLinkExpired
{
    use Dispatchable, InteractsWithSockets,  SerializesModels;

    public function __construct(
        public Authenticatable $user,
    ) {}
}
