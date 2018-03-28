<?php
namespace SITE\DAO;

use SITE\Models\Token;
use SITE\Models\User as Users;

/**
 * Interface User
 * @package SITE\DAO
 */
interface User
{

    /**
     * @param Users $user
     * @param $quick
     * @return bool
     */
    public function isValidForRegistration(Users $user, $quick = false);

    public function createToken(Token $token);

    public function getUserPasswordHash($userId);

    public function createUser(Users $user);


    /**
     * @param string $email
     * @return Users|null
     */
    public function getUserByEmail($email);

    public function isLoggedUser($token);

    /**
     * @param string $usernameOrEmail
     * @return Users|null
     */
    public function getUserByUsernameOrEmail($usernameOrEmail);
    /**
     * @param int $userId
     * @return Users|null
     */
    public function getUserById($userId);


    public function getUserByToken($token);

    public function updateUserInfo($userId, $fields);

    /**
     * @param Users $user
     * @return int
     */
    public function updateToken(Users $user);

    public function signOut($token);


    }
