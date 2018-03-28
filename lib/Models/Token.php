<?php

namespace SITE\Models;


use SITE\Helpers\GetterSetter;
use SITE\Helpers\Model;

/**
 * Class Token
 * @package SITE\Models
 *
 * @property string $token
 * @property int $userId
 * @property string $createDate
 * @property string $expireDate
 * @property string $type
 * @property string $used
 */
class Token extends Model {
    use GetterSetter;

    const TYPE_REGISTER       = 'REGISTER';
    const TYPE_PASSWORD_RESET = 'PASSWORD_RESET';
    const TYPE_LOGIN          = 'LOGIN';

    const USED_YES            = 'YES';
    const USED_NO             = 'NO';

    protected $token;
    protected $userId;
    protected $createDate;
    protected $expireDate;
    protected $type;
    protected $used = self::USED_NO;

    public function __construct($data=[]) {
        if(!isset($data['token'])) {
            $this->generateToken($data['userName']);
        }
        parent::__construct($data);
    }

    private function generateToken($data = '') {
        $hashString = uniqid('#R6$%adf');
        if($data !== ''){
            $hashString = $hashString.$data;
        }
        $this->token = hash('sha384', $hashString);
    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getCreateDate() {
        return $this->createDate;
    }

    /**
     * @param string $createDate
     */
    public function setCreateDate($createDate) {
        $this->createDate = $createDate;
    }

    /**
     * @return string
     */
    public function getExpireDate() {
        return $this->expireDate;
    }

    /**
     * @param string $expireDate
     */
    public function setExpireDate($expireDate) {
        $this->expireDate = $expireDate;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getUsed() {
        return $this->used;
    }

    /**
     * @param string $used
     */
    public function setUsed($used) {
        $this->used = $used;
    }

    public function isExpired() {
        return $this->used == self::USED_NO ? $this->expireDate <= date('Y-m-d H:i:s') : true;
    }


}