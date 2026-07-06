<?php

namespace Grosv\LaravelPasswordlessLogin\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoginLinkUsed
{
    use Dispatchable, InteractsWithSockets,  SerializesModels;

    public function __construct(
        public User $user,
    ) {}
}
