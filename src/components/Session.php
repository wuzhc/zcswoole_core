<?php
/**
 * Created by PhpStorm.
 * User: wuzhc
 * Date: 18-8-14
 * Time: 下午5:19
 */

namespace zcswoole\components;


use \Redis;
use zcswoole\Config;

class Session extends Component
{
    /**
     * 每一次必须建立一个链接,不能在多个进程中共用一个链接
     * @return Redis
     */
    public function getConn()
    {
        try {
            $config = Config::get('redis');
            $redis = new Redis();
            $redis->connect($config['host'] ?? '127.0.0.1', $config['port'] ?? 6379);
            return $redis;
        } catch (\Error $e) {
            echo $e->getMessage();
        }
    }

    public function set($key, $data = [], $timeout)
    {
        $conn = $this->getConn();
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $k => $v) {
            $conn->hSet($key, $k, $v);
        }

        if (-1 !== $timeout) {
            $conn->expire($key, $timeout);
        }
        return true;
    }

    public function get($key, $hashKey = '')
    {
        if (!$hashKey) {
            return $this->getConn()->hGetAll($key);
        } else {
            return $this->getConn()->hget($key, $hashKey);
        }
    }

    public function drop($key)
    {
        return $this->getConn()->del($key);
    }
}