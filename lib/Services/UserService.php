<?php
namespace SITE\Services;

use SITE\Models\User as Users;

/**
 * Interface UserService
 * @package SITE\Services
 */
interface UserService
{
    /**
     * @param Users $user
     * @return bool
     */
    public function createUser(Users $user);

    public function signIn($userName, $password);

    public function changeUserRole($userId, $role, Users $user);

}