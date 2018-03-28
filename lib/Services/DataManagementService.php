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

    public function assignTable($tableId, $userId, User $user);



}