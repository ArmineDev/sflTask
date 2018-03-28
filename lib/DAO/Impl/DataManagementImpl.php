<?php

namespace SITE\DAO\Impl;

use SITE\DAO\DataManagement;
use SITE\Exceptions\DBException;
use SITE\Helpers\Defines;
use SITE\Helpers\Notification;
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
        $where = "user_table.userId = :userId";
        $bind  = ['userId' => $userId];
        if(isset($filter['ordered'])) {
            $order = ($filter['ordered'] == "true")?1:0;
            $where .= " AND user_table.ordered = :ordered ";
            $bind["ordered"] = $order;
        }
        $query = "SELECT user_table.*,tablelist.tableName FROM user_table
                  INNER JOIN tablelist on tablelist.tableId = user_table.tableId
                  WHERE {$where}";
        Utils::limit($query, $start, $limit);
        return $this->db->run($query, $bind, ['fetch' => true]);
    }

    public function getProductList($filter = [], $start = 0, $limit = 10){
        $query = "SELECT * FROM products ";
        Utils::limit($query, $start, $limit);
        return $this->db->run($query, [], ['fetch' => true]);

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
    public function orderProducts($assignmentId, $userId, $products = [])
    {
        $res = $this->db->select($this->userTableListConnectionTable, "userId = :userId and id = :id and ordered = 1", ['userId' => $userId, 'id' => $assignmentId]);
        if (!isset($res[0])) {
            return false;
        }
        $arr = [
            "orderId" => $assignmentId,
        ];
        $this->db->beginTransaction();
        try {
            foreach ($products as $key => $val) {
                $arr['productId'] = $val;
                $this->db->insert("order_products", $arr);
            }

            $this->db->commit();
            return true;
        } catch (DBException $e) {
            $this->db->rollback();
            Notification::error(1, _('Wrong data'), '');
            return false;
        }
    }



        }