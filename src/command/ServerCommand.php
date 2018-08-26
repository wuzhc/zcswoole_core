<?php

namespace zcswoole\command;


use swoole_server;
use zcswoole\App;
use zcswoole\Event;
use zcswoole\Config;

/**
 * swoole_server服务
 * Class HttpServerCommand
 * @package zcswoole\command
 * @author wuzhc 2018-08-14
 */
class ServerCommand extends Command
{
    use Event;

    /** @var \Swoole\Server */
    private $_server;

    /**
     * @param CommandContext $context
     */
    public function execute(CommandContext $context)
    {
        $config = Config::get('http_server');
        $this->_server = new swoole_server($config['host'], $config['port']);
        $this->_server->set($config['setting'] ?? []);
        $this->onEvent();
        (new App($this->_server))->start();
    }

    /**
     * 绑定回调事件
     */
    protected function onEvent()
    {
        $this->_server->on('workerStart', [$this, 'workerStart']);
    }
}