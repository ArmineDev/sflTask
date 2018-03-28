<?php

namespace SITE\Services\Impl;


use SITE\Helpers\Notification;
use SITE\Models\Product;
use SITE\Models\Table;
use SITE\Models\User;
use SITE\Services\DataManagementService;

/**
 * Class UserServiceImpl
 * @package SITE\Services\Impl
 * @Transactional
 */
class DataManagementServiceImpl implements DataManagementService {
    /**
     * @Inject
     * @var \SITE\DAO\DataManagement
     */
    protected $dataManagement;
    /**
     * @Inject
     * @var \SITE\DAO\User
     */
    protected $user;

    public function createProduct(Product $product, $userId){
        $product->setUserId($userId);
        return $this->dataManagement->createProduct( $product);
    }
    public function createTable (Table $table, $userId){
        $table->setUserId($userId);
        return $this->dataManagement->createTable( $table);
    }
    public function assignTable($tableId, $userId, User $user){
        $owner =  $this->user->getUserById($user->getUserId());
        if (!isset($owner)) {
            Notification::error(1, _('Not valid dta'), 'assignTable');
            return false;
        }
        $owner->setTableId($tableId);

        $fields = $owner->getEditableProperties();
        return $this->user->updateUserInfo($user->getUserId(),$fields);
    }

}
