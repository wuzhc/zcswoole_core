<?php

namespace zcswoole\http;


use zcswoole\Config;
use Swoole\Http\Request;

/**
 * 一个简单路由实现
 * Class Router
 * @package zcswoole
 * @author wuzhc 2018-08-09
 */
class Router
{
    public $defaultController = 'index';
    public $defaultAction = 'index';

    /** @var Request */
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 请求解析
     * 无限级子目录 e.g.
     * http://127.0.0.1:9501/index/index => new(/app/controllers/Index())->index()
     * http://127.0.0.1:9501/index/index/index => new(/app/controllers/index/Index())->index()
     * @return array
     */
    public function handleRequest(): array
    {
        $action = Config::get('defaultAction') ?? $this->defaultAction;
        $controller = Config::get('defaultController') ?? $this->defaultController;

        $pathInfo = $this->request->server['path_info'];
        $router = array_values(array_filter(explode('/', $pathInfo)));
        $count = count($router);

        // 路由解析
        if ($count == 1) {
            $controller = $router[0];
            $bashPath = array();
        } elseif ($count == 2) {
            list($controller, $action) = $router;
            $bashPath = array();
        } else {
            $controller = $router[$count - 2];
            $action = $router[$count - 1];
            $bashPath = array_slice($router, 0, $count - 2);
        }

        $bashPath = $bashPath ? '\\' . implode('\\', $bashPath) . '\\' : '\\';
        $controllerClass = '\app\controllers' . $bashPath . ucfirst($controller) . 'Controller';

        return [$controllerClass, $action];
    }
}