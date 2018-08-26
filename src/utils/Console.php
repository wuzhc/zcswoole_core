<?php

namespace zcswoole\utils;


class Console
{
    /**
     * 帮助信息
     * @param $entryFile
     * @param bool $isExist
     */
    public static function help($entryFile, $isExist = true)
    {
        echo "Usage : \n";
        echo " php $entryFile [options] \n\n";
        echo "Selection by list: \n";
        echo " ". str_pad("server", 10, ' ') . " swoole_server \n";
        echo " " . str_pad("http", 10, ' ') . " swoole_http_server \n";
        echo " " . str_pad("websocket", 10, ' ') . " swoole_websocket_server \n\n";
        echo "Example: \n";
        echo " php $entryFile server \n";

        if (true === $isExist) {
            exit(0);
        }
    }

    /**
     * 终端错误输出
     * @param $msg
     * @param bool $isExist
     */
    public static function error($msg, $isExist = true)
    {
        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }
        echo "\e[31mError: $msg \e[0m\n";
        if (true === $isExist) {
            exit(0);
        }
    }

    /**
     * 终端成功提示输出
     * @param $msg
     */
    public static function success($msg)
    {
        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }
        echo "\e[32mSuccess: $msg\e[0m \n";
    }

    /**
     * @param $msg
     */
    public static function msg($msg)
    {
        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }
        echo "\e[33mMessage: $msg\e[0m \n";
    }

    /**
     * 运行环境检测
     */
    public static function checkEnv()
    {
        if (php_sapi_name() !== 'cli') {
            self::error('zcswoole only run in cli mode');
        }

        if (PHP_OS == 'win') {
            self::error('only run in linux');
        }

        if (version_compare(PHP_VERSION, '7.1.0') <= 0) {
            self::error("PHP version must 7.1.0, your version: " . PHP_VERSION);
        }

        if (version_compare(swoole_version(), '2.0.0') <= 0) {
            self::error('swoole version must 2.0.0');
        }
    }
}