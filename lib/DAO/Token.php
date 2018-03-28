<?php
namespace SITE\DAO;

use SITE\Helpers\Defines;

/**
 * Interface Token
 * @package SITE\DAO
 */
interface Token {

    public function getTokenObj($tokenStr, $userId, $type = Defines::TOKEN_TYPE_REGISTRATION);

    public function setTokenUsed(\SITE\Models\Token $token);

    public function setTokenUsedByTypeAndId($userId, $type);


}