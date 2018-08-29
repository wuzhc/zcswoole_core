<?php

namespace zcswoole\command;


use Swoole\Http\Server;
use swoole_http_server;
use zcswoole\App;
use zcswoole\SwooleEvent;
use zcswoole\Config;
use zcswoole\rpc\RpcProtocol;
use zcswoole\utils\Console;

/**
 * swoole_http_server服务
 * Class HttpServerCommand
 * @package zcswoole\command
 * @author wuzhc 2018-08-14
 */
class HttpServerCommand extends Command
{
    use SwooleEvent;

    /** @var Server */
    protected $server;

    public function __construct()
    {
    }

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
                $this->reload('http_server');
                break;
            case 'stop':
                $this->stop('http_server');
                break;
        }
    }

    /**
     * 钩子函数,用于服务启动前设置
     */
    protected function beforeStart()
    {
        // 增加一个rpc服务,监听端口号9504
        $rpc = $this->server->addListener('127.0.0.1', 9504, SWOOLE_SOCK_TCP);
        $rpc->set([]); // 需要调用 set 方法覆盖主服务器的设置
        $rpc->on('receive', [$this, 'rpcReceive']);
    }

    /**
     * 绑定回调事件
     */
    protected function onEvent()
    {
        $this->server->on('workerStart', [$this, 'workerStart']);
        $this->server->on('request', [$this, 'request']);
    }

    /**
     * rpc接受客户端事件
     * @param $server
     * @param int $fd
     * @param int $reactorID
     * @param string $data
     */
    public function rpcReceive(Server $server, $fd, $reactorID, $data)
    {
        list($status, $header, $body) = RpcProtocol::decode($data);

        // 解包成功后处理业务
        $t1 = microtime(true);
        if ($status === RpcProtocol::ERR_UNPACK_OK) {
            for ($i=0;$i<10000000;$i++) {

            }
            $status = 'success';
        } else {
            $status = 'failed';
        }

        $time = microtime(true) - $t1;

        echo $time . PHP_EOL;
        // 通知结果给客户端
        $server->send($fd, RpcProtocol::encode(['body' => $body, 'time'=>$time, 'status'=>$status], $header['encodeType']));
    }

    const BEFORE_START_SERVICE = 'before_start_service';

    /**
     * 服务启动
     */
    public function start()
    {
        $config = Config::get('http_server');
        $this->server = new swoole_http_server($config['host'], $config['port']);
        $this->server->set($config['setting'] ?? []);
        $this->onEvent();
        $this->addListenerForStat();
        $this->beforeStart();

        Event::trigger(self::BEFORE_START_SERVICE);
        Console::msg('http starting');
        (new App($this->server))->start();
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
}