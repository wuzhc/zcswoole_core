<?php

namespace zcswoole\command;


use zcswoole\Config;
use zcswoole\utils\Console;

/**
 * Class CommandController
 * @package zcswoole\command
 * @author wuzhc 2018-08-14
 */
class CommandController
{
    private $_context;

    public function __construct($config)
    {
        Console::checkEnv();
        Config::load($config);
        $this->_context = new CommandContext();
    }

    /**
     * 命令参数
     * @return CommandContext
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * 执行代码
     */
    public function run()
    {
        $cmd = $this->_context->getCmd();
        CommandFactory::getCommand($cmd)->execute($this->_context);
    }
}