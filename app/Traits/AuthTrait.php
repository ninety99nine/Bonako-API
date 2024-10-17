<?php

namespace App\Traits;

use App\Models\User;

trait AuthTrait
{
    /**
     * Return the current authenticated user.
     *
     * @return User|null
     */
    public function getAuthUser()
    {
        return request()->auth_user;
    }

    /**
     * Check if the current authenticated user exists.
     *
     * @return bool
     */
    public function hasAuthUser(): bool
    {
        return request()->auth_user_exists;
    }

    /**
     * Check if the current authenticated is a super admin.
     *
     * @return bool
     */
    public function authUserIsSuperAdmin(): bool
    {
        return $this->hasAuthUser() ? request()->auth_user->isSuperAdmin() : false;
    }
}
