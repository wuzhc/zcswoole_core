<?php

namespace zcswoole;


use ReflectionClass;

/**
 * 容器会有缓存,不能存放请求期创建的对象,例如控制器
 * Class Container
 * @package Core
 * @author wuzhc 2018-08-09
 */
class Container
{
    public $containers = [];

    /**
     * @param $id
     * @param array $args
     * @param array $properties
     * @return null|object
     */
    public function get($id, $args = [], $properties = [])
    {
        if (!isset($this->containers[$id])) {
            $this->build($id, $args, $properties);
        }

        return $this->containers[$id] ?? null;
    }

    /**
     * @param $id
     * @param array $args
     * @param array $properties
     * @return null|object
     */
    public function set($id, $args = [], $properties = [])
    {
        if (!isset($this->containers[$id])) {
            $this->build($id, $args, $properties);
        }
    }

    /**
     * @param $id
     * @param array $args
     * @param array $properties
     * @return null
     */
    public function build($id, $args = [], $properties = [])
    {
        if (!$args['class']) {
            return null;
        }

        $class = new ReflectionClass($args['class']);
        unset($args['class']);

        $instance = $class->newInstanceArgs($args);
        foreach ($properties as $k => $v) {
            $instance->$k = $v;
        }

        $this->containers[$id] = $instance;
        return $instance;
    }
}