<?php

namespace SITE\DAO\Impl;

use SITE\DAO\DataManagement;
use SITE\Helpers\Defines;
use SITE\Helpers\Utils;
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
    protected $tableName = 'tableList';
    protected $userTableListConnectionTable = 'user_table';


    public function createProduct(Product $product)
    {
        $this->db->insert($this->productTableName, $product->toArray());
        return ['productId' => $this->db->getLastInsertId()];

    }

    public function createTable(Table $table)
    {
        $this->db->insert($this->tableName, $table->toArray());
        return ['tableId' => $this->db->getLastInsertId()];

    }

    public function createTableAssignment($tableId, $userId)
    {
        $this->db->insert($this->userTableListConnectionTable, ['userId' => $userId, "tableId" => $tableId]);
        return ['assignmentId' => $this->db->getLastInsertId()];

    }

    public function getTableList($userId, $filter = [], $start = 0, $limit = 10)
    {
        $query = "SELECT user_table.*,tablelist.tableName FROM user_table
                  INNER JOIN tablelist on tablelist.tableId = user_table.tableId
                  WHERE user_table.userId = :userId";
        Utils::limit($query, $start, $limit);
        $res = $this->db->run($query, ['userId' => $userId], ['fetch' => true]);
        return $res;
    }

    public function doOrderForTable($assignmentId, $userId)
    {
        $this->db->update($this->userTableListConnectionTable, ['ordered' => Defines::ASSIGNMENT_ACCEPTED], "userId = :userId AND id = :id", ['userId' => $userId, 'id' => $assignmentId]);
        return true;
    }

    public function cancelTableOrder($assignmentId, $userId)
    {
        $this->db->delete($this->userTableListConnectionTable,"userId = :userId AND id = :id", ['userId' => $userId, 'id' => $assignmentId]);
        return true;
    }


}