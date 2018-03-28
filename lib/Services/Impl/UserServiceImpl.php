<?php

namespace SITE\Services\Impl;

use SITE\Models\User;
use SITE\Helpers\Defines;
use SITE\Helpers\Notification;
use SITE\Models\Token;
use SITE\Models\User as Users;
use SITE\Services\UserService;

/**
 * Class UserServiceImpl
 * @package SITE\Services\Impl
 * @Transactional
 */
class UserServiceImpl implements UserService {

    protected $registrationTokenLifeTime = 1209600; //2week default
    protected $signInTokenLifeTime = 1209600; //2week default
    protected $resetPasswordTokenLifeTime = 1209600; //2week default

    /**
     * @Inject
     * @var \SITE\DAO\User
     */
    protected $user;

    /**
     * @Inject
     * @var \SITE\DAO\Token
     */
    protected $tokens;



    public function createUser(Users $user) {
        if($user->getRole() == Defines::USER_ROLE_ADMIN){
            return false;
        }
        return  $this->user->createUser($user);
    }


    public function signIn($userName, $password) {
        $user = $this->user->getUserByUsernameOrEmail($userName);
        $passHash = $this->user->getUserPasswordHash($user->userId);
        if (isset($user)) {
            if ($user->isPasswordEqual($password, $passHash)) {
                $token = $this->generateToken(Defines::TOKEN_TYPE_SIGN_IN, $user->userName);
                if ($user->loginToken != '') {
                    if ($user->loginToken == $token->token) {
                        Notification::error(1, _("This user is already logged."), 'signIn');
                        return $user->toArray();
                    }
                }
                $token->userId = $user->userId;
                $token->used = 'YES';
                $this->user->createToken($token);
                $user->setLoginToken($token->token);
                $this->user->updateToken($user);

            } else {
                Notification::error(1, _('Invalid username or password.'), 'signIn');
                return false;
            }
            return $user->toArray();

        }
        return false;

    }
    public function changeUserRole($userId, $role, User $user){
        $loginUser =  $this->user->getUserById($userId);
        if($loginUser->getRole() != Defines::USER_ROLE_ADMIN || $userId == $user->getUserId()){
            return false;
        }
        $userUpdate =  $this->user->getUserById($user->getUserId());

        if (!isset($userUpdate)) {
            Notification::error(1, _('Not valid dta'), 'changeUserRole');
            return false;
        }
        $userUpdate->setRole($role);
        return $this->updateUserInfo($user->getUserId(),$userUpdate);


    }
    public function updateUserInfo($userId, User $user){
        $fields = $user->getEditableProperties();
        if (!$fields) {
            Notification::error(1, _('Wrong User Info'), 'updateUserInfo');
            return false;
        }
        return $this->user->updateUserInfo($userId, $fields);
    }

    protected function generateToken($type = Defines::TOKEN_TYPE_SIGN_IN, $userName = '') {
        $token = new \stdClass();
        if ($type == Defines::TOKEN_TYPE_SIGN_IN) {
            $token = new Token(['userName' => $userName]);
            $token->type = $type;
            $token->expireDate = date('y-m-d H:i:s', time() + $this->signInTokenLifeTime);
        } elseif ($type == Defines::TOKEN_TYPE_REGISTRATION) {
            $token = new Token(['token' => mt_rand(1000, 9999)]);
            $token->type = $type;
            $token->expireDate = date('y-m-d H:i:s', time() + $this->registrationTokenLifeTime);
        } elseif ($type == Defines::TOKEN_TYPE_FORGOT_PASSWORD) {
            $token = new Token(['token' => mt_rand(1000, 9999)]);
            $token->type = $type;
            $token->expireDate = date('y-m-d H:i:s', time() + $this->registrationTokenLifeTime);
        }
        $token->createDate = date('y-m-d H:i:s');
        return $token;
    }


}
