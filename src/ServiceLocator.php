<?php

namespace zcswoole;


use zcswoole\utils\FactoryUtil;

/**
 * 服务定位器
 * Class ServiceLocator
 *
 * @package zcswoole
 */
class ServiceLocator implements ServiceLocatorInterface
{
    private $_services = [];
    private $_definitions = [];
    private $_singletons = [];

    /**
     * 注册服务
     *
     * @param string       $id 组件ID
     * @param array|object $definition
     * @param bool         $isSingleton 是否为单例模式,非单例模式下每次获取组件时都会实例化一次对象
     * @throws \Exception
     */
    public function set($id, $definition, $isSingleton = true): void
    {
        if (!$definition || !is_array($definition)) {
            return;
        }

        if (!isset($definition['class']) && !$definition['class']) {
            throw new \Exception("$id class is empty");
        }

        if (isset($this->_services[$id])) {
            unset($this->_services[$id]);
        }

        $this->_singletons[$id] = $isSingleton;
        $this->_definitions[$id] = $definition;
    }

    /**
     * 获取服务实例
     *
     * @param string $id
     * @return mixed|null|object
     * @throws \Exception
     */
    public function get($id)
    {
        $isSingleton = $this->_singletons[$id] ?? false;

        if (isset($this->_services[$id]) && true === $isSingleton) {
            return $this->_services[$id];
        }

        if (!isset($this->_definitions[$id])) {
            throw new \Exception("Unknown component id $id");
        }

        $definition = $this->_definitions[$id];
        if (is_object($definition)) {
            $obj = $definition;
        } else {
            $className = $definition['class'];
            $classParams = $definition['params'] ?? [];
            if (!$className) {
                throw new \Exception("Unknown className id $id");
            }

            $obj = FactoryUtil::createObject($className, $classParams, $definition);
        }

        if ($isSingleton) {
            $this->_services[$id] = $definition;
        }

        return $obj;
    }

    /**
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->_definitions[$id]) || isset($this->_services[$id]);
    }

    /**
     * 魔术方法
     * e.g. ZCSwoole::$app->get('logger') or ZCSwoole::$app->logger
     *
     * @param $id
     * @return mixed
     */
    public function __get($id)
    {
        if ($this->has($id)) {
            return $this->get($id);
        }

        $method = 'get' . ucfirst($id);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }
}