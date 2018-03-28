<?php

namespace SITE\Models;



use SITE\Exceptions\ConfigurationException;
use SITE\Helpers\GetterSetter;
use SITE\Helpers\Model;
use SITE\Helpers\Notification;

class Product extends Model {
    use GetterSetter;
    private   $productId;

    private   $requiredFields = [
        "productName",
        "amount"
    ];

    protected   $userId;
    protected   $productName;
    protected   $amount;
    protected   $description;
    protected   $creationDate;
    protected   $imageName;
    public function __construct($data = [], $checkRequired = true) {
        if (!isset($data['creationDate'])) {
            $this->creationDate = date('Y-m-d H:i:s');

        }
        if($checkRequired) {
            foreach ($this->requiredFields as $value) {
                if (!array_key_exists($value, $data) || $data[$value] === "") {
                    Notification::error(1,"Please name your sport.");
                    throw new ConfigurationException($value . " property is required");
                }
            }
        }
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    public function getImageName() {
        return $this->imageName;
    }

    /**
     * @param mixed $imageName
     */
    public function setImageName($imageName) {
        $this->imageName = $imageName;
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }
    /**
     * @return mixed
     */
    public function getProductName() {
        return $this->productName;
    }

    /**
     * @param mixed $productName
     */
    public function setProductName($productName) {
        $this->productName = $productName;
    }
    /**
     * @return mixed
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount) {
        $this->amount = $amount;
    }

  



    /**
     * @return mixed
     */
    public function getProductId() {
        return (int)$this->productId;
    }

    /**
     * @param mixed $productId
     */
    public function setId($productId) {
        $this->productId = (int)$productId;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getCreationDate() {
        return $this->creationDate;
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {

        $this->creationDate = $creationDate;
    }








}