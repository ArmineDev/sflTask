<?php

namespace SITE\DAO\Impl;

use Facebook\Facebook;
use League\OAuth2\Client\Provider\Google;
use Monolog\Handler\Curl\Util;
use SITE\DAO\User;
use SITE\Helpers\Defines;
use SITE\Helpers\Notification;
use SITE\Helpers\Utils;
use SITE\Models\Dictionary;
use SITE\Models\User as Users;
use SITE\Models\Token;
use SITE\Exceptions\DBException;
use SITE\Helpers\Curl;


class UserImpl implements User
{
    /**
     * @Inject
     * @var \SITE\Helpers\DB
     */
    protected $db;
    protected $userTableName = 'users';
    protected $tokenTableName = 'token';
    protected $user;


    public function isValidForRegistration(Users $user, $quick = false)
    {
        $isValid = $user->isValidForRegistration($quick);
        if ($isValid) {
            if (!is_null($this->getUserByEmail($user->email))) {
                $isValid = false;
                Notification::error(1, _("We're sorry, that email is taken."), '');
            } elseif (!is_null($this->getAffiliateByUsername($user->username))) {
                $isValid = false;
                Notification::error(1, _('This username is already taken.'), '');
            }
        }
        return $isValid;
    }

    public function createToken(Token $token)
    {
        return $this->db->insert($this->tokenTableName, $token->toArray());
    }

    public function getUserPasswordHash($userId){
        $userData = $this->db->select($this->userTableName, 'userId = :userId', ['userId' => $userId],"passHash,passSalt");
        if (!empty($userData) && count($userData) == 1) {
            return $userData[0];
        }
        return false;
    }

    public function createUser(Users $user)
    {
        $this->db->beginTransaction();
        try {
            $user->collectRegisterIp();
            $this->db->insert($this->userTableName, $user->toArray());
            $user->userId = $this->db->getLastInsertId();
            $this->db->commit();
            return $user;
        } catch (DBException $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function getUserByEmail($email)
    {
        $user = null;
        $userData = $this->db->select($this->userTableName, ' email = :email', ['email' => $email]);
        if (!empty($userData) && count($userData) == 1) {
            $user = new Users($userData[0]);
        }
        return $user;
    }

    public function isLoggedUser($token)
    {
        $isLogged = false;
        $userData = $this->db->select($this->userTableName, ' loginToken = :token', ['token' => $token]);
        if (!empty($userData) && count($userData) == 1) {
            $isLogged = $userData[0];
        }
        return $isLogged;
    }

    public function getUserByUsernameOrEmail($usernameOrEmail)
    {
        $user = null;
        $userData = $this->db->select($this->userTableName, '(userName = :searchString OR email = :searchString)', ['searchString' => $usernameOrEmail], "userId,userName,email,registerDate,registerIp,loginToken,role");
        if (!empty($userData) && count($userData) == 1) {
            $res = $userData[0];
            $user = new Users($res);
        }
        return $user;
    }


    public function getUserByToken($token)
    {
        $user = null;
        $userData = $this->db->select($this->userTableName, '(loginToken= :loginToken)', ['loginToken' => $token]);
        if (!empty($userData) && count($userData) == 1) {
            $res = $userData[0];
            $user = new Users($res);
        }
        return $user;
    }

    public function getUserById($userId)
    {
        $user = null;
        $userData = $this->db->select($this->userTableName, '(userId= :userId)', ['userId' => $userId], "userId,userName,email,registerDate,registerIp,loginToken,role");
        if (!empty($userData) && count($userData) == 1) {
            $res = $userData[0];
            $user = new Users($res);
        }
        return $user;
    }


    public function signOut($token)
    {
        $expireDate = date('y-m-d H:i:s');
        $this->db->beginTransaction();
        try {
            $this->db->update($this->userTableName, ['loginToken' => ''], "loginToken = :loginToken", ['loginToken' => $token]);
            $this->db->update($this->tokenTableName, ['expireDate' => $expireDate], "token = :loginToken", ['loginToken' => $token]);
            $this->db->commit();
            return true;
        } catch (DBException $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function updateUserInfo($userId, $fields)
    {
        return $this->db->update($this->userTableName, $fields, "userId = :userId", ['userId' => $userId]);
    }
    public function updateToken(Users $user){
        return $this->db->update($this->userTableName, ['loginToken'=>$user->loginToken], 'userId = :userId', ['userId' => $user->userId]);
    }


}