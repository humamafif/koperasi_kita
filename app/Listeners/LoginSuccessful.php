<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class LoginSuccessful
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $userRoles = $user->roles->pluck('name')->toArray();
        if (
            in_array('anggota', $userRoles) &&
            !in_array('admin', $userRoles) &&
            !in_array('super_admin', $userRoles)
        ) {
            Session::put('redirect_to_anggota', true);
        }
    }
}
