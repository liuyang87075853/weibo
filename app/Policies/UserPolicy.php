<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    /* public function __construct()
    {
    //
    } */
    public function update(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }
    //用户删除策略，必须是管理员且不能删除自己
    public function destroy(User $currentUser, User $user)
    {
        return $currentUser->is_admin && $currentUser->id !== $user->id;
    }
    public function follow(User $currentUser, User $user)
    {
        return $currentUser->id !== $user->id;
    }
}
