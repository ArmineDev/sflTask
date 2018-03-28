<?php
namespace SITE\DAO;

use SITE\Models\Product;
use SITE\Models\Table;


/**
 * Interface DataManagement
 * @package SITE\DAO
 */
interface DataManagement
{
    public function createProduct(Product $product);

    public function createTable(Table $table);

    public function createTableAssignment($tableId, $userId);

    public function getTableList($userId, $filter = [], $start = 0, $limit = 10);

    public function getProductList($filter = [], $start = 0, $limit = 10);


    public function doOrderForTable($assignmentId, $userId);

    public function cancelTableOrder($assignmentId, $userId);

    public function orderProducts($assignmentId, $userId, $products = []);


}