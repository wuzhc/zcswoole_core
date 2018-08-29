<?php

namespace zcswoole\http;


use zcswoole\Config;
use zcswoole\ZCSwoole;
use Swoole\Http\Response;
use Swoole\Http\Request;
use Swoole\Http\Server;

/**
 * Class HttpController
 * @package zcswoole\http
 * @author wuzhc 2018-08-09
 */
class HttpController
{
    /** @var string 动作 */
    public $actionID;
    /** @var Request $request */
    protected $request;
    /** @var Response $response */
    protected $response;
    /** @var Server */
    protected $server;
    /** @var Session */
    protected $session;

    /**
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->server = ZCSwoole::$app->server;
        $this->session = new Session($request, $response);
    }

    /**
     * 在action之前运行,例如可以处理一些统一认证
     */
    public function beforeAction()
    {
    }

    /**
     * 在action之后运行,例如可以处理一些日志类操作
     */
    public function afterAction()
    {
    }

    /**
     * 渲染页面
     * Note: controller首字母大写,与类名保持一致
     * @param string $template 以最后斜杠作为action,例如backend/Default/index, action为index, controller为backend/Default,
     * 对应模板文件为app/views/theme/backend/Default/index.html
     * @param array $params
     */
    public function render($template = '', $params = [])
    {
        $theme = Config::get('template_theme', 'default');
        $suffix = Config::get('template_file_suffix', '.html');

        $viewDir = DIR_ROOT . '/app/views/' . $theme . '/';
        $controllerDir = str_replace('app\controllers\\', '', get_class($this));
        $templateFile = $this->actionID . $suffix;

        if ($template) {
            $pos = strrpos($template, '/');
            if (false === $pos) {
                $templateFile = $template . $suffix;
            } else {
                $controllerDir = substr($template, 0, $pos);
                $templateFile = substr($template, $pos) . $suffix;
            }
        }

        $template = $viewDir . $controllerDir . '/' . $templateFile;
        $template = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $template);
        if (!file_exists($template)) {
            $this->response->end("Error: $template not exist");
            return ;
        }

        @ob_end_clean();
        ob_start();
        $smarty = new \Smarty();
        $smarty->setTemplateDir(DIR_ROOT . '/app/data/smarty/templates_c/');
        $smarty->setCompileDir(DIR_ROOT . '/app/data/smarty/templates_c/');
        $smarty->setConfigDir(DIR_ROOT . '/app/data/smarty/configs/');
        $smarty->setCacheDir(DIR_ROOT . '/app/data/smarty/cache/');

        $smarty->left_delimiter = '{#';
        $smarty->right_delimiter = '#}';

        $smarty->assign($params);
        $smarty->display($template);
        $content = ob_get_contents();
        ob_end_clean();

        $this->response->end($content);
    }

    /**
     * @param $data
     * @param int $status
     * @param string $msg
     */
    public function endJson($data, $status = 0, $msg = '')
    {
        $this->response->header('Access-Control-Allow-Origin', '*'); // cors跨域
        $this->response->header('Content-type', 'application/json');

        if (!is_array($data)) {
            $data = (array)$data;
        }

        $data['status'] = $status;
        $data['msg'] = $msg;
        $this->response->end(json_encode($data));
    }

    /**
     * 创建url
     * @param string $router
     * @param array $params
     * @return string
     */
    public function createUrl(string $router = '', $params = []):string
    {
        $queryString = $params ? http_build_query($params) : '';
        $url = 'http://' . $this->host() . '/' . ltrim($router, '/');
        $url .= $queryString ? '?' . $queryString : '';

        return $url;
    }

    /**
     * 当前域名
     * @param bool $isWithPort 是否要端口
     * @return string
     */
    public function host($isWithPort = true):string
    {
        /** @var Server $server */
        return $this->request->server['remote_addr'] . ($isWithPort === true ? ':' . $this->request->server['server_port'] : '');
    }
}