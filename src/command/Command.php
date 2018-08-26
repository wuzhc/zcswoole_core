<?php

namespace zcswoole\command;
use zcswoole\Config;
use zcswoole\utils\Console;


/**
 * Class Command
 * @package zcswoole\command
 */
abstract class Command
{
    abstract function execute(CommandContext $context);

    /**
     * 状态
     */
    public function status()
    {
        $client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $client->connect('127.0.0.1', 9505, 10);
        $client->send('run');
        $data = $client->recv();
        echo $data;
    }

    /**
     * 重启服务
     * @param $service
     */
    public function reload($service)
    {
        $config = Config::get($service);
        $pid = file_get_contents($config['setting']['pid_file']);
        if (posix_kill($pid, 0)) {
            if (posix_kill($pid, SIGUSR1)) {
                Console::success('reload success');
            } else {
                Console::error('reload failed');
            }
        }
    }

    /**
     * 停止服务
     * @param $service
     */
    public function stop($service)
    {
        $config = Config::get($service);
        $pid = file_get_contents($config['setting']['pid_file']);
        if (posix_kill($pid, 0)) {
            if (posix_kill($pid, SIGTERM)) {
                Console::success('stop success');
            } else {
                Console::error('stop failed');
            }
        }
    }
}