<?php

namespace zcswoole\http;


use SessionHandlerInterface;
use zcswoole\utils\File;

/**
 * session处理器
 * Class SessionHandler
 * @package zcswoole
 */
class SessionHandler implements SessionHandlerInterface
{
    private $savePath = 'session_files';
    private $sessionName = 'sess_';
    private $gc_probability = 1;
    private $gc_divisor = 100;

    public function open($savePath, $sessionName)
    {
        $this->savePath = $savePath;
        $this->sessionName = $sessionName;
        File::createDir($this->savePath);
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        return (string)@file_get_contents("$this->savePath/$this->sessionName{$id}");
    }

    public function write($id, $data)
    {
        return file_put_contents("$this->savePath/$this->sessionName{$id}", $data) === false ? false : true;
    }

    public function destroy($id)
    {
        $file = "$this->savePath/$this->sessionName{$id}";
        return File::delete($file);
    }

    public function gc($maxlifetime)
    {
        if ($this->gc_probability <= mt_rand(1, $this->gc_divisor)) {
            foreach (glob("$this->savePath/$this->sessionName*") as $file) {
                if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                    unlink($file);
                }
            }
        }

        return true;
    }
}