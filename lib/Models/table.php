<?php

namespace SITE\Models;


use SITE\Exceptions\ConfigurationException;
use SITE\Helpers\GetterSetter;
use SITE\Helpers\Model;
use SITE\Helpers\Notification;

class Table extends Model
{
    use GetterSetter;
    private $tableId;
    private $requiredFields = [
        "tableName",
        "userCount"
    ];
    protected $userId;
    protected $tableName;
    protected $description;
    protected $userCount;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

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

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return bool|string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param bool|string $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
    protected $creationDate;

    public function __construct($data = [], $checkRequired = true)
    {
        if (!isset($data['creationDate'])) {
            $this->creationDate = date('Y-m-d H:i:s');

        }
        if ($checkRequired) {
            foreach ($this->requiredFields as $value) {
                if (!array_key_exists($value, $data) || $data[$value] === "") {
                    Notification::error(1, "Please name your sport.");
                    throw new ConfigurationException($value . " property is required");
                }
            }
        }
        parent::__construct($data);
    }



}