<?php
namespace SITE\Services;
use SITE\Models\Product;


/**
 * Interface DataManagement
 * @package SITE\Services
 */
interface DataManagementService
{
        public function createProduct(Product $product, $userId);


}