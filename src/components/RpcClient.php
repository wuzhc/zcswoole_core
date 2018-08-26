<?php

namespace zcswoole\components;


use Swoole\Client;
use zcswoole\rpc\RpcProtocol;

/**
 * rpc客户端
 * Class RpcClient
 * @package zcswoole\components
 * @author wuzhc 2018-08-20
 *
 */
class RpcClient extends Component
{
    public $servers = [];
    public $encodeType = 1;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
    }

    /**
     * 获取一个客户端
     * @return null|\Swoole\Client
     */
    protected function getClient()
    {
        if (count($this->servers) == 0) {
            return null;
        }

        $flag = false;
        $servers = $this->servers;
        $client = null;
        while (count($servers) > 0) {
            list($key, $server) = $this->getServer($servers);
            $client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
            for ($i=0; $i<2; $i++) {
                $res = $client->connect($server['host'], $server['port'], 10);
                if ($res === false && ($client->errCode == 114 || $client->errCode == 105)) {
                    $client->close();
                    continue;
                } else {
                    $flag = true;
                    break;
                }
            }
            if (true === $flag) {
                break;
            } else {
                unset($servers[$key]);
            }
        }

        return $client;
    }

    /**
     * 添加一个目标服务器
     * @param $data
     * [
     *      'host' => '127.0.0.1',
     *      'port' => '9301',
     *      'weight' => 100
     * ]
     * @return bool
     */
    public function addServer($data)
    {
        if (empty($data['host']) || empty($data['port'])) {
            return false;
        }

        // 权重默认为100
        if (!isset($data['weight'])) {
            $data['weight'] = 100;
        }

        $key = $data['host'] . ':' . $data['port'];
        $this->servers[$key] = $data;
    }

    /**
     * 根据权重随机获取一个server
     * 算法借鉴 swoole_framework
     * @return array [key, server]
     */
    public function getServer($servers)
    {
        // 根据权重随机获取一个
        $weight = 0;
        foreach ($servers as $key => $server) {
            if (!$server['port'] || !$server['host']) {
                unset($servers[$key], $this->servers[$key]);
            }
            $weight += $server['weight'] ?? 100;
        }
        $rand = rand(0, $weight - 1);

        $weight = 0;
        foreach ($this->servers as $key => $server) {
            $weight += $server['weight'] ?? 100;
            if ($rand < $weight) {
                return [$key, $server];
            }
        }
    }

    /**
     * @param $data
     * @return array
     */
    public function request($data):array
    {
        $returnData = null;
        $commandID = $this->getCommandID();
        $client = $this->getClient();
        if (null !== $client) {
            if ($client->send(RpcProtocol::encode($data, $this->encodeType, $commandID)) === false) {
                $client->close();
                return [
                    'code' => $client->errCode,
                    'data' => $returnData
                ];
            }
            list($code,,$returnData) = RpcProtocol::decode($client->recv());
            $client->close();
            return [
                'code' => $code,
                'data' => $returnData
            ];
        } else {
            return [
                'code' => 1,
                'data' => $returnData
            ];
        }
    }

    public function asyncRequest($data)
    {
        $returnData = null;
        $commandID = $this->getCommandID();
        if (count($this->servers) == 0) {
            return null;
        }

        $flag = false;
        $servers = $this->servers;
        $client = null;
        while (count($servers) > 0) {
            list($key, $server) = $this->getServer($servers);
            $client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
            $client->on('connect',function(Client $cli) use ($data, $commandID){
                if ($cli->send(RpcProtocol::encode($data, $this->encodeType, $commandID)) === false) {
                    $cli->close();
                    return [
                        'code' => $cli->errCode,
                        'data' => null
                    ];
                }
            });
            $client->on('error',function(){});
            $client->on('close',function(){});
            $client->on('receive', function(Client $cli){
                $cli->close();
            });
            for ($i=0; $i<2; $i++) {
                $res = $client->connect($server['host'], $server['port'], 10);
                if ($res === false && ($client->errCode == 114 || $client->errCode == 105)) {
                    $client->close();
                    continue;
                } else {
                    $flag = true;
                    break;
                }
            }
            if (true === $flag) {
                break;
            } else {
                unset($servers[$key]);
            }
        }

        return [
            'code' => 1,
            'data' => $returnData
        ];
    }

    /**
     * 消息命令ID的算法参考swoole_frame
     * @return int
     */
    public function getCommandID():int
    {
        $us = strstr(microtime(), ' ', true);
        return intval(strval($us * 1000 * 1000) . rand(100, 999));
    }
}