<?php

namespace zcswoole\services;


use zcswoole\Config;

/**
 * Class MysqliDB
 * @link https://github.com/ThingEngineer/PHP-MySQLi-Database-Class
 * @package zcswoole\services
 * @author wuzhc 2018-08-13
 */
class MysqliDB
{
    private $_db;
    private static $_instance;

    /**
     * MysqliDB constructor.
     */
    private function __construct()
    {
        $config = Config::get('mysql');
        $this->_db = new \MysqliDb ($config['host'], $config['user'], $config['password'], $config['dbname']);
    }

    /**
     * @return MysqliDB
     */
    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 插入单条数据
     * @param $table
     * @param array $data
     * [
     *      ['name'=>'xx','age'=>1]
     * ]
     * @return int
     */
    public function insert($table, array $data)
    {
        return $this->_db->insert($table, $data);
    }

    /**
     * @param $table
     * @param array $data
     * [
     *      ['name'=>'xx','age'=>1],
     *      ['name'=>'xx','age'=>1],
     * ]
     * or
     * [
     *      ['xx, '1'],
     *      ['xx, '1'],
     * ]
     * @param array $keys
     * [
     *      ['name', 'age']
     * ]
     * @return array|bool
     */
    public function insertMulti($table, array $data, $keys = null)
    {
        return $this->_db->insertMulti($table, $data, $keys);
    }

    /**
     * @param $table
     * @param array $data 更新数据
     * [
     *      'name'=>'new vale'
     * ]
     * @param null $limit 更新多少条记录
     * @return int 更新成功条数
     */
    public function update($table, $data, $limit = null)
    {
        return $this->_db->update($table, $data, $limit) ? $db->count : 0;
    }

    /**
     * @param $table
     * @param $data
     * @param $id
     * @return int|string
     */
    public function updateByPk($table, $data, $id)
    {
        $db = $this->_db;
        if (is_array($id)) {
            $db->where('id', $id, 'in');
        } else {
            $db->where('id', $id);
        }
        return $db->update($table, $data) ? $db->count : 0;
    }

    /**
     * @param $table
     * @param null $limit
     * @return bool
     */
    public function delete($table, $limit = null)
    {
        return $this->_db->delete($table, $limit);
    }

    public function deleteByPK($table, $id)
    {
        if (is_array($id)) {
            $this->_db->where('id', $id, 'in');
        } else {
            $this->_db->where('id', $id);
        }
        return $this->_db->delete($table);
    }

    /**
     * @param $table
     * @param array $criteria
     * {
     *      'select' => 'id,name,age',
     *      'where' => [
     *          'id' => [[1,2], 'in', 'AND'],
     *          'name' => ['xx']
     *      ],
     *      'order' => ['id' => 'desc']
     * }
     * @return array
     */
    public function get($table, $criteria = [])
    {
        $db = $this->_db;

        $field = [];
        if (!empty($criteria['select'])) {
            if (is_string($criteria)) {
                $field = explode(',', $criteria['select']);
            } else {
                $field = $criteria['select'];
            }
        }

        $limit = null;
        if (isset($criteria['limit'])) {
            $limit = (int)$criteria['limit'];
        }

        if (!empty($criteria['order'])) {
            $db->orderBy(key($criteria['order']), current($criteria['order']));
        }

        return $this->_db->get($table, $limit, $field);
    }

    public function orderBy($field, $value)
    {
        return $this->_db->orderBy($field, $value);
    }

    /**
     * @param $k
     * @param $v
     * @param string $op
     * @param string $cond
     * @return \MysqliDb
     */
    public function where($k, $v, $op = '=', $cond = 'AND')
    {
        return $this->_db->where($k, $v, $op, $cond);
    }

    public function join($table, $on, $joinType)
    {
        return $this->_db->join($table, $on, $joinType);
    }

    /**
     * @param $table
     * @param array $ids
     * @return array
     */
    public function getAllByPk($table, $ids = [])
    {
        return $this->where('id',$ids,'in')->get($table);
    }

    /**
     * @param $table
     * @param array $criteria
     * @return array
     */
    public function getOne($table, $criteria = [])
    {
        $record = $this->get($table, $criteria);
        return $record ? $record[0] : [];
    }

    /**
     * @param $table
     * @param $id
     * @return array|mixed
     */
    public function getOneByPk($table, $id)
    {
        return $this->where('id', $id)->getOne($table);
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->_db->getLastError();
    }

    /**
     * @return string
     */
    public function getLastQuery()
    {
        return $this->_db->getLastQuery();
    }

    private function __clone()
    {

    }

    private function __wakeup()
    {

    }
}