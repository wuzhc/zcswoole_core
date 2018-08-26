<?php

namespace zcswoole;


use Swoole\Server;
use zcswoole\rpc\RpcProtocol;

/**
 * 为swoole_http_server和swoole_websocket_server提供服务
 * Class App
 * @property \zcswoole\Table $table
 * @property \zcswoole\components\Session $session
 * @property \zcswoole\components\Logger logger
 * @property \zcswoole\components\RpcClient rpcClient
 * @author wuzhc 2018-08-09
 */
class App extends ServiceLocator
{
    /** @var string 当前应用服务类型 */
    public $service;
    /** @var string 入口文件 */
    public $entryFile;
    /** @var Server*/
    public $server;
    /** @var  */
    public $vendorPath;

    /**
     * App constructor.
     */
    public function __construct($server)
    {
        include_once 'ZCSwoole.php';

        // 当前应用作为单例模式使用
        ZCSwoole::$app = $this;

        // 注册组件
        $this->registerComponents();

        // server对象
        $this->server = $server;
    }

    /**
     * 启动服务
     */
    public function start():void
    {
        $this->server->start();
    }

    /**
     * 向服务定位器注册组件
     */
    public function registerComponents():void
    {
        foreach ($this->allComponents() as $name => $properties) {
            $this->set($name, $properties);
        }
    }

    /**
     * 所有组件,包括用户自定义组件和核心组件
     * @return array
     * @throws \Exception
     */
    public function allComponents():array
    {
        $customComponents = Config::get('components');
        $coreComponents = $this->coreComponents();

        // 以用户自定义组件为准
        foreach ($coreComponents as $c => &$v) {
            if (isset($customComponents[$c])) {
                $v = array_merge($v, $customComponents[$c]);
                unset($customComponents[$c]);
            }
        }
        unset($c,$v);

        // 检测自定义组件
        foreach ($customComponents as $c => $v) {
            if (empty($v['class'])) {
                throw new \Exception("{$c} has not class");
            }
        }

        return array_merge($customComponents, $coreComponents);
    }

    /**
     * 核心组件
     * @return array
     */
    public function coreComponents():array
    {
        return [
            'logger' => [
                'class' => 'zcswoole\components\Logger'
            ],
            'session' => [
                'class' => 'zcswoole\components\Session'
            ],
            'rpcClient' => [
                'class' => 'zcswoole\components\RpcClient',
                'encodeType' => RpcProtocol::PHP_JSON,
                'servers' => [
                    [
                        'host' => '127.0.0.1',
                        'port' => 9504,
                        'weight' => 100
                    ]
                ]
            ],
        ];
    }
}