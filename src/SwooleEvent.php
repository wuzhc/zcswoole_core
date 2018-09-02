<?php

namespace zcswoole;


use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server;
use zcswoole\http\HttpController;

/**
 * Trait SwooleEvent
 * @package zcswoole\event
 * @author wuzhc 2018-08-09
 */
trait SwooleEvent
{
    /**
     * 1.6.11之后Task进程中也会触发onWorkerStart事件,
     * 可以通过$server->taskworker属性来判断当前是Worker进程还是Task进程
     * @param $server
     * @param $workerID
     */
    public function workerStart($server, $workerID)
    {
    }

    /**
     * 此事件在worker进程终止时发生。在此函数中可以回收worker进程申请的各类资源
     * 进程异常结束，如被强制kill、致命错误、core dump 时无法执行onWorkerStop回调函数
     * @param $server
     * @param int $workerID
     */
    public function workerStop($server, $workerID)
    {
    }

    /**
     * UDP协议下只有onReceive事件，没有onConnect/onClose事件
     * @param $server
     * @param int $fd 是连接的文件描述符，发送数据/关闭连接时需要此参数
     * @param int $reactorID 来自哪个Reactor线程
     */
    public function connect($server, $fd, $reactorID)
    {
    }

    /**
     * 接收到数据时回调此函数，发生在worker进程中
     * @link https://wiki.swoole.com/wiki/page/50.html
     * @param $server
     * @param int $fd 是连接的文件描述符，发送数据/关闭连接时需要此参数
     * @param int $reactorID 来自哪个Reactor线程
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     */
    public function receive($server, $fd, $reactorID, $data)
    {
    }

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     * onClose回调函数如果发生了致命错误，会导致连接泄漏。通过netstat命令会看到大量CLOSE_WAIT状态的TCP连接
     * @link https://wiki.swoole.com/wiki/page/p-event/onClose.html
     * @param Server $server
     * @param int $fd $fd 是连接的文件描述符，发送数据/关闭连接时需要此参数
     * @param int $reactorID 来自哪个Reactor线程
     */
    public function close($server, $fd, $reactorID)
    {

    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function request($request, $response)
    {
        $pathInfo = $request->server['path_info'];

        // 非动态请求检测
        $suffix = strstr($pathInfo, '.');
        if ($suffix && ($suffix != '.php' || $suffix != '.PHP')) {
            if (file_exists($pathInfo)) {
                $response->end(file_get_contents($pathInfo));
            } else {
                $response->status(404);
                $response->end('Nothing');
            }
        } else {
            $router = new Router($request->server['path_info']);
            list($controller, $action) = $router->parse();

            try {
                /** @var HttpController $obj */
                $obj = new $controller($request, $response);
                if (!($obj instanceof HttpController)) {
                    throw new \Error("'$controller is not an instance of 'HttpController'");
                }

                $obj->actionID = $action;
                $obj->beforeAction();
                $obj->$action();
                $obj->afterAction();

                // ZCSwoole::$app->table->incr('request_total', 'total');
                ZCSwoole::$app->logger->asyncWrite($request->server['request_uri']);
            } catch (\Error $e) {
                $response->end($e->getMessage());
            }
        }
    }

    /**
     * 数据统计
     * @param Server $server
     * @param $fd
     * @param $reactorID
     * @param $data
     */
    public function statReceive(Server $server, $fd, $reactorID, $data)
    {
        $loadavg = sys_getloadavg();
        $stats = $server->stats();

        $content = '';
        $content .= "---------------------------------------GLOBAL STATUS--------------------------------------------\n";
        $content .= 'Swoole version: ' . swoole_version() . "\n";
        $content .= "PHP version: ".PHP_VERSION."\n";
        $content .= 'start time: '. date('Y-m-d H:i:s', $stats['start_time']).'   run ' . floor((time()-$stats['start_time'])/(24*60*60)). ' days ' . floor(((time()-$stats['start_time'])%(24*60*60))/(60*60)) . " hours   \n";
        $content .= 'load average: ' . implode(", ", $loadavg) . "\n";
        $content .= "------------------------------------------------------------------------------------------------\n";
        $content .= str_pad('connection_num', 25, ' ') . $stats['connection_num'] . "\n";
        $content .= str_pad('accept_count', 25, ' ') . $stats['accept_count'] . "\n";
        $content .= str_pad('close_count', 25, ' ') . $stats['close_count'] . "\n";
        $content .= str_pad('tasking_num', 25, ' ') . $stats['tasking_num'] . "\n";
        $content .= str_pad('request_count', 25, ' ') . $stats['request_count'] . "\n";
        $content .= str_pad('worker_request_count', 25, ' ') . $stats['worker_request_count'] . "\n";
        $content .= str_pad('coroutine_num', 25, ' ') . $stats['coroutine_num'] . "\n";
        $content .= "------------------------------------------------------------------------------------------------\n";

        $server->send($fd, $content);
    }
}