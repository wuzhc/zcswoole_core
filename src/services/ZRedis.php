<?php

namespace zcswoole\services;


use zcswoole\Config;
use zcswoole\utils\Console;

/**
 * Class ZRedis
 * @package zcswoole\services
 * @author wuzhc 2018-08-14
 */
class ZRedis
{
    private $_redis;
    private static $_instance;

    /**
     * ZRedis constructor.
     */
    private function __construct()
    {
        try {
            $config = Config::get('redis');
            $this->_redis = new \Redis();
            $this->_redis->connect($config['host']??'127.0.0.1',$config['port']??6379);
        } catch (\Error $e) {
            Console::error($e->getMessage());
        }
    }

    /**
     * @return ZRedis
     */
    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function set($key, $value, $timeout = 0)
    {
        $this->_redis->set($key, $value, $timeout);
    }

    public function get($key, $default = null)
    {
        return $this->_redis->get($key) ?? $default;
    }

    /**
     * @param $key
     * @param $hashKey
     * @param $value
     * @return int
     */
    public function hSet($key, $hashKey, $value)
    {
        return $this->_redis->hSet($key, $hashKey, $value);
    }

    /**
     * @param $key
     * @param $data
     * @param int $timeout
     * @return bool
     */
    public function hMultiSet($key, $data, $timeout = -1)
    {
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $k => $v) {
            $this->hSet($key, $k, $v);
        }

        if (-1 !== $timeout) {
            $this->expire($key, $timeout);
        }
        return true;
    }

    public function hGet($key, $hashKey)
    {
        return $this->_redis->hGet($key, $hashKey);
    }

    public function hGetAll($key)
    {
        return $this->_redis->hGetAll($key);
    }

    /**
     * 有序集合
     * @param $key
     * @param mixed $value
     * @param int $timeout
     */
    public function sAdd($key, $value, $timeout = -1)
    {
        if (!is_array($value)) {
            $value = (array)$value;
        }
        $this->_redis->sAddArray($key, $value);
        if (-1 !== $timeout) {
            $this->expire($key, $timeout);
        }
    }

    public function sMembers($key)
    {
        return $this->_redis->sMembers($key);
    }

    public function sRem($key, $member)
    {
        return $this->_redis->sRem($key, $member);
    }

    public function sIsMember($key, $member)
    {
        return $this->_redis->sIsMember($key, $member);
    }

    public function del($key)
    {
        return $this->_redis->del($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->_redis->exists($key);
    }

    public function expire($key, $timeout)
    {
        $this->_redis->expire($key, $timeout);
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}