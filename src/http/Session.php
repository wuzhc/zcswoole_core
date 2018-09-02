<?php

namespace zcswoole\http;


use Swoole\Http\Request;
use Swoole\Http\Response;
use zcswoole\utils\Random;

/**
 * Class Session
 * @package zcswoole\components
 * @author wuzhc 2018-08-28
 */
class Session
{
    public $savePath = 'session_files';
    public $sessionID;
    public $gc_maxlifetime = 86400;
    public $cookieKey = 'PHPSESSIONID';

    public $sessionData = [];
    protected $handler;
    protected $isStart = false;

    /** @var Request $request */
    public $request;
    /** @var Response $response */
    public $response;

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->handler = new SessionHandler();
        $this->start();

        $cookie = $this->request->cookie[$this->cookieKey] ?? null;
        if (!$cookie) {
            $this->sessionID = RandomUtil::generateUniqueStr();
            $this->response->cookie($this->cookieKey, $this->sessionID, 0, '/');
        } else {
            $this->sessionID = $cookie;
            $this->sessionData = json_decode($this->handler->read($this->sessionID), true);
        }
    }

    /**
     * 开启session
     */
    public function start()
    {
        if ($this->isStart) {
            return ;
        }

        $this->isStart = true;
        $this->handler->open($this->savePath, 'sess_');
        $this->handler->gc($this->gc_maxlifetime); // TODO 优化:需要异步去执行
    }

    public function set($key, $value)
    {
        $this->sessionData[$key] = $value;
        return true;
    }

    public function get($key)
    {
        return $this->sessionData[$key] ?? null;
    }

    public function drop()
    {
        $this->sessionData = null;
        $this->response->cookie($this->cookieKey, null, null, '/');
    }

    public function __destruct()
    {
        if ($this->isStart == false) {
            return ;
        }

        if (null === $this->sessionData) {
            $this->handler->destroy($this->sessionID);
        } else {
            $this->handler->write($this->sessionID, json_encode($this->sessionData));
        }
        $this->handler->close();
        $this->isStart = false;
    }
}