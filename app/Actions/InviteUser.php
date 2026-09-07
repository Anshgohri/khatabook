<?php

namespace App\Actions;

use App\Models\Role;
use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InviteUser
{
    /**
     * Create an invited user and email them an invitation link.
     */
    public function invite(string $name, string $email, Role $role): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'role_id' => $role->id,
            'status' => 'invited',
        ]);

        $user->notify(new UserInvited);

        return $user;
    }
}
