<?php

namespace zcswoole;


/**
 * 一个简单路由实现
 * Class Router
 * @package zcswoole
 * @author wuzhc 2018-08-09
 */
class RouterParse
{
    public $defaultController = 'index';
    public $defaultAction = 'index';

    public $pathInfo;

    public function __construct($pathInfo)
    {
        $this->pathInfo = $pathInfo;
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
        $defaultAction = Config::get('defaultAction') ?? $this->defaultAction;
        $defaultController = Config::get('defaultController') ?? $this->defaultController;

        $router = array_values(array_filter(explode('/', $this->pathInfo)));
        $count = count($router);

        // 路由解析
        if ($count == 1) {
            $controller = $router[0];
            $action = $defaultAction;
            $bashPath = array();
        } elseif ($count == 2) {
            list($controller, $action) = $router;
            $bashPath = array();
        } elseif ($count > 2) {
            $controller = $router[$count - 2];
            $action = $router[$count - 1];
            $bashPath = array_slice($router, 0, $count - 2);
        } else {
            $controller = $defaultController;
            $action = $defaultAction;
            $bashPath = array();
        }

        $bashPath = $bashPath ? '\\' . implode('\\', $bashPath) . '\\' : '\\';
        $controllerClass = '\app\controllers' . $bashPath . ucfirst($controller) . 'Controller';

        return [$controllerClass, $action];
    }
}