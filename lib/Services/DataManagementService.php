<?php
namespace SITE\Services;

use SITE\Models\Product;
use SITE\Models\Table;
use SITE\Models\User;


/**
 * Interface DataManagement
 * @package SITE\Services
 */
interface DataManagementService
{
    public function createProduct(Product $product, $userId);

    public function createTable(Table $table, $userId);

    public function createTableAssignment($tableId, $userId, User $user);

    public function doOrderForTable($assignmentId, $userId);

    public function orderProducts($assignmentId, $userId, $products = []);

    public function cancelTableOrder($assignmentId, $userId);

    public function getTableList($userId, $filter = [], $start = 0, $limit = 10) ;

    public function getProductList($userId, $filter = [], $start = 0, $limit = 10) ;

    public function getOrderedProductList($userId, $assignmentId) ;



}