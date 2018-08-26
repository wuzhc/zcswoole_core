<?php

namespace zcswoole\command;


use Swoole\WebSocket\Server;
use swoole_websocket_server;
use zcswoole\App;
use zcswoole\Event;
use zcswoole\Config;
use zcswoole\rpc\RpcProtocol;
use zcswoole\utils\Console;

/**
 * swoole_http_server服务
 * Class WebSocketServerCommand
 * @package zcswoole\command
 * @author wuzhc 2018-08-14
 */
class WebSocketServerCommand extends Command
{
    use Event;

    /** @var Server */
    protected $server;

    /**
     * @param CommandContext $context
     */
    public function execute(CommandContext $context)
    {
        switch ($context->getAction()) {
            case 'start':
                $this->start();
                break;
            case 'status':
                $this->status();
                break;
            case 'reload':
                $this->reload('websocket_server');
                break;
            case 'stop':
                $this->stop('websocket_server');
                break;
        }
    }

    /**
     * 启动服务
     */
    public function start()
    {
        $config = Config::get('websocket_server');
        $this->server = new swoole_websocket_server($config['host'], $config['port']);
        $this->server->set($config['setting'] ?? []);
        $this->onEvent();
        $this->addListenerForStat();
        $this->beforeStart();
        Console::msg('webSocket starting');
        (new App($this->server))->start();
    }

    /**
     * 钩子函数,用于服务启动前设置
     */
    protected function beforeStart()
    {

    }

    /**
     * 数据统计状态
     */
    public function addListenerForStat()
    {
        $stat = $this->server->addListener('127.0.0.1', 9505, SWOOLE_SOCK_TCP);
        $stat->set([]);
        $stat->on('receive', [$this, 'statReceive']);
    }

    /**
     * 绑定回调事件
     */
    protected function onEvent()
    {
        $this->server->on('workerStart', [$this, 'workerStart']);
        $this->server->on('open', [$this, 'open']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('close', [$this, 'close']);
    }

    public function open(Server $server, $request)
    {
    }

    public function message(Server $server, $frame)
    {
    }

    public function close(Server $server, $fd)
    {
    }
}