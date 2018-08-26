<?php

namespace zcswoole\command;
use zcswoole\utils\Console;


/**
 * 把参数传递给命令对象
 * Class CommandContext
 * @package zcswoole\command
 * @author wuzhc 2018-08-14
 */
class CommandContext
{
    private $_cmd = '';
    private $_action;
    private $_params = [];
    private $_entryFile = '';

    /**
     * 1. 检测运行环境
     * 2. 解析命令行参数
     * CommandContext constructor.
     */
    public function __construct()
    {
        global $argv, $argc;
        if ($argc < 3 || ($argc == 3 && $argv[1] == '--help')) {
            Console::help($this->_entryFile);
        }

        $this->_cmd = $argv[1];
        $this->_entryFile = $argv[0];
        $this->_action = $argv[2];

        // 其他参数,格式为键值对,e.g. name=wuzhc
        if (($count = count($argv)) > 3) {
            for ($i=3; $i<$count; $i++) {
                $arr = explode('=', $argv[$i], 2);
                if (count($arr) == 2) {
                    list($key, $value) = $arr;
                    $this->_params[$key] = $value;
                } else {
                    $this->_params[] = $arr[0];
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getCmd()
    {
        return $this->_cmd;
    }

    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->_params[$key] ?? null;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->_params[$key] = $value;
    }

    /**
     * @return string
     */
    public function getEntryFile()
    {
        return $this->_entryFile;
    }
}