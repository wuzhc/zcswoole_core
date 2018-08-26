<?php

namespace zcswoole\utils;


use ReflectionClass;

class Factory
{
    /**
     * 创建对象
     * @param string $className
     * @param array $constructorParams
     * @param array $properties
     * @return null|object
     */
    public static function createObject($className, $constructorParams = [], $properties = [])
    {
        if (!$className) {
            return null;
        }

        $class = new ReflectionClass($className);
        $instance = $class->newInstanceArgs($constructorParams);
        foreach ($properties as $k => $v) {
            $instance->$k = $v;
        }

        return $instance;
    }
}