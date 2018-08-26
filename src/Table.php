<?php

namespace zcswoole;


use Swoole\Table as SwooleTable;

/**
 * 内存表
 * @see swoole_table
 * Class Table
 * @package zcswoole
 * @author wuzhc 2018-08-11
 */
class Table
{
    /**
     * @var SwooleTable
     */
    public $table;

    /**
     * Table constructor.
     */
    public function __construct()
    {
        $this->table = new SwooleTable(8);

        $this->table->column('total',SwooleTable::TYPE_INT, 1024);
        $this->table->create();
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->table->set($key, ['total' => $value]);
    }

    /**
     * @param $key
     * @return array
     */
    public function get($key)
    {
        return $this->table->get($key, 'total');
    }

    /**
     * @param $key
     * @param $column
     * @param int $incrby
     */
    public function incr($key, $column, $incrby = 1)
    {
        $this->table->incr($key, $column, $incrby);
    }
}