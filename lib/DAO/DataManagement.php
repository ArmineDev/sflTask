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
}