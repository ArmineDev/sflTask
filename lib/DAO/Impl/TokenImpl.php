<?php

namespace SITE\DAO\Impl;

use SITE\DAO\Token;
use SITE\Helpers\Defines;
use SITE\Helpers\Notification;
use SITE\Models\Token as TokenModel;

class TokenImpl implements Token {
    /**
     * @Inject
     * @var \SITE\Helpers\DB
     */
    protected $db;
    protected $tokenTableName = 'token';

    public function getTokenObj($tokenStr, $userId ,$type = Defines::TOKEN_TYPE_REGISTRATION) {
        if(empty($tokenStr) || empty($userId)){
            Notification::error(1, _('Wrong arguments'), 'Token');
            return false;
        }
        $tokenData = $this->db->select($this->tokenTableName, 'token = :token and userId = :userId and type = :type and used = :used', ['token' => $tokenStr, 'userId' => $userId, 'type' => $type ,'used' => 'NO']);
        if (!empty($tokenData) && count($tokenData) == 1) {
            return new TokenModel($tokenData[0]);
        }else{
            Notification::error(1, _('Wrong Token'), 'Token');
            return false;
        }
    }

    public function setTokenUsed(TokenModel $token) {
        $this->db->update($this->tokenTableName,['used' => 'YES'],"token = :token and type = :type",['token' => $token->getToken(),'type' => $token->type]);
        return true;

    }
    public function setTokenUsedByTypeAndId($userId, $type) {
        $this->db->update($this->tokenTableName,['used' => 'YES'],"type = :type and userId = :userId",['userId' => $userId,'type' => $type]);
        return true;

    }




}