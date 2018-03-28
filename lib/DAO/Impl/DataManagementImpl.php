<?php

namespace SITE\DAO\Impl;

use SITE\DAO\DataManagement;
use SITE\Models\Product;
use SITE\Models\Table;


class DataManagementImpl implements DataManagement
{
    /**
     * @Inject
     * @var \SITE\Helpers\DB
     */
    protected $db;
    protected $productTableName = 'products';
    protected $tableName        = 'tableList';


    public function createProduct(Product $product)
    {
         $this->db->insert($this->productTableName, $product->toArray());
        return $this->db->getLastInsertId();

    }

    public function createTable(Table $table)
    {
         $this->db->insert($this->tableName, $table->toArray());
         return $this->db->getLastInsertId();

    }


}