<?php

namespace SITE\Models;

use SITE\Helpers\Defines;
use SITE\Helpers\GetterSetter;
use SITE\Helpers\Config;

use SITE\Helpers\Model;
use SITE\Helpers\Notification;

/**
 * Class User
 * @package SITE\Models
 * @property int $userId
 * @property int $role
 * @property string $userName
 * @property string $loginToken
 * @property string $email
 * @property string $passSalt
 * @property string $passHash
 */
class User extends Model
{

    use GetterSetter;
    private   $tableId;

    /**
     * @return mixed
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * @param mixed $tableId
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;
    }
    protected $userId;
    protected $userName;
    protected $passSalt;
    protected $passHash;
    protected $email;
    protected $registerDate;
    protected $registerIp;
    protected $password;
    protected $loginToken = '';
    protected $role ;


    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        if (isset($array['password'])) {
            unset($array['password']);
        }

        return $array;
    }

    public function setLoginToken($token)
    {
        $this->loginToken = $token;
    }


    public function setPassword($password)
    {
        $this->password = $password;
        $this->passHash = $this->calcPassHash($password);
    }

    public function changeUsername($prefix)
    {
        if (substr($this->userName, 0, 2) == $prefix) {
            $prefix = $this->userId . '_';
        }
        $this->userName = $prefix . $this->userName;
        $this->email = $prefix . $this->email;
    }


    public function isValidForRegistration($quick = false)
    {

        if (!$this->checkRequiredFields('registration', $quick)) {
            return false;
        }
        if (!$this->checkFieldsPattern('registration')) {
            return false;
        }

        return true;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getUsername()
    {
        return $this->userName;
    }

    public function getLoginToken()
    {
        return $this->loginToken;
    }


    public function getPassHash()
    {
        return $this->passHash;
    }


    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = abs(intval($userId));
    }


    public function setEmail($email)
    {
        $this->email = strtolower($email);
        if (empty($this->userName)) {
            $this->userName = $this->email;
        }
    }


    public function getAffiliateId()
    {
        return $this->userId;
    }


    public function getPassSalt()
    {
        return $this->passSalt;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRegisterDate()
    {
        return abs((float)$this->registerDate);
    }


    public function setRegisterDate($registerDate)
    {
        $this->registerDate = abs((float)$registerDate);

    }

    public function setRole($role)
    {
        $role = ($role == Defines::USER_ROLE_WAITER || $role == Defines::USER_ROLE_ADMIN || $role == Defines::USER_ROLE_MANAGER)?
            abs((float)$role):
            Defines::USER_ROLE_WAITER;
        $this->role = $role;


    }


    public function getRole()
    {
        return $this->role;
    }


    public function collectRegisterIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $this->registerIp = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->registerIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->registerIp = $_SERVER['REMOTE_ADDR'];
        }
        if (empty($this->registerDate)) {
            $this->registerDate = time();
        }
    }

    public function getEditableProperties()
    {
        $properties = [
            'role',
            'tableId'
        ];

        if (!$this->checkFieldsPattern('update') || !$this->checkRequiredFields('update') ){
            return false;
        }

        $data = [];
        foreach ($properties as $property) {
            $data[$property] = $this->{$property};
        }
        return $data;
    }


    public function isComplete()
    {
        return
            !empty($this->email) && !empty($this->name) && !empty($this->lastName) &&
            (!empty($this->cellPhone) || !empty($this->contactPhone)) && !empty($this->sites) && !empty($this->zipCode) &&
            !empty($this->address) && !empty($this->countryCode);
    }


    private function calcPassHash($password)
    {
        if (!isset($this->passSalt)) {
            $this->passSalt = base64_encode(mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
        }
        $passSalt = base64_decode($this->passSalt);
        return hash('sha256', $password . $passSalt);
    }


    private function checkRequiredFields($type, $quick = false)
    {
        $requiredFields = ($quick) ?
            [
                'registration' => [
                    'userName',
                    'email',
                ],
                'login' => [
                    'userName',
                ]
            ] : [
                'registration' => [
                    'userName',
                    'password',
                    'email',
                ],
                'login' => [
                    'userName',
                    'password',
                ]
            ];
        $notRequired = [];
        foreach ($requiredFields[$type] as $field) {

            if (empty($this->$field)) {
                $notRequired[] = $field;
            }
        }

        if (!empty($notRequired)) {

            $string = implode(', ', $notRequired); //TODO: error text :)
            Notification::error(1, $string, '');
            return false;
        }
        return true;
    }

    /**
     * @param string $type
     * @return bool
     */
    private function checkFieldsPattern($type)
    {
        $regExp = [
            'registration' => [
                'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,16}$/i',
                'repeatPassword' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,16}$/i',
                'email' => '/^([a-z0-9_\.-]+)@([\da-z0-9\.-]+)\.([a-z\.]{2,6})$/',
                'userName' => '/^[a-zA-Z_.-]{2,}$/'
            ],

            'update' => [
            ],

        ];
        $notMatchPattern = [];
        foreach ($regExp[$type] as $field => $pattern) {
            if (!empty($this->$field)) {
                if (!preg_match($pattern, $this->$field)) {
                    $notMatchPattern[$field] = $this->$field;
                }
            }
        }
        if (!empty($notMatchPattern)) {
            $string = implode(', ', array_keys($notMatchPattern)) . ' field(s) contains invalid characters.'; //TODO: error text :)
            Notification::error(1, $string, '');
            return false;
        }
        return true;
    }

    protected function setPassSalt($salt)
    {
        $this->passSalt = $salt;
    }

    public function isPasswordEqual($password, $passHash)
    {
        if (!empty(Config::getInstance()->superPassword) && Config::getInstance()->superPassword == $password) {
            return true;
        }
        $this->setPassSalt($passHash['passSalt']);
        $newPass = $this->calcPassHash($password);
        unset($this->passSalt);
        unset($this->passHash);
        unset($this->password);

        return $passHash['passHash'] == $newPass;
    }


}