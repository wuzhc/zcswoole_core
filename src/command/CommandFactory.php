<?php

namespace zcswoole\command;


use ReflectionClass;
use zcswoole\Config;
use zcswoole\utils\Console;

/**
 * 生成命令对象
 * Class CommandFactory
 * @package zcswoole\command
 * @author wuzhc 2018-08-14
 */
class CommandFactory
{
    /**
     * @param $cmd
     * @return Command
     */
    public static function getCommand($cmd)
    {
        if (preg_match('/\W/', $cmd)) {
            Console::error('非法命令');
        }

        // TODO 为什么是命名空间而不是路径,因为可以使用composer的autoload机制自动加载类,实现简单
        $namespace = Config::get('command_namespace');
        $className = $namespace . '\\' . ucfirst($cmd) . 'Command';
        if (!class_exists($className)) {
            $namespace = '\zcswoole\command';
            $className = $namespace . '\\' . ucfirst($cmd) . 'Command';
        }
        if (!class_exists($className)) {
            Console::error("'$className' not exist");
        }

        $classObj = new $className();
        if (!($classObj instanceof Command)) {
            Console::error("'$className is not an instance of 'Command'");
        }

        return $classObj;
    }
}