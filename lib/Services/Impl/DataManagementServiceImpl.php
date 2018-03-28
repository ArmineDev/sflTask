<?php

namespace SITE\Services\Impl;


use SITE\Models\Product;
use SITE\Services\DataManagementService;

/**
 * Class UserServiceImpl
 * @package SITE\Services\Impl
 * @Transactional
 */
class DataManagementImpl implements DataManagementService {

    public function createProduct(Product $product, $userId){
        $product->setUserId($userId);
    }

}
