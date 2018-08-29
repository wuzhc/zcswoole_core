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
            $this->sessionID = Random::generateUniqueCode();
        } else {
            $this->sessionID = $cookie;
            $this->sessionData = \swoole_serialize::unpack($this->handler->read($this->sessionID));
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
    }

    public function __destruct()
    {
        if (null === $this->sessionData) {
            $this->handler->destroy($this->sessionID);
            $this->response->cookie($this->cookieKey, null, null, '/');
        } else {
            $this->handler->write($this->sessionID, \swoole_serialize::pack($this->sessionData));
            $this->response->cookie($this->cookieKey, $this->sessionID, 0, '/');
        }
    }
}