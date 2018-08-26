<?php

namespace zcswoole;


/**
 * 全局配置,该类只加载一次配置
 * 使用方式:
 * Config::load($config);
 * Config::get(key);
 *
 * Class Config
 * @author wuzhc 2018-08-09
 */
class Config
{
    private static $_loaded = false;
    private static $_configs = [];

    /**
     * 加载配置文件
     * @param $config
     */
    public static function load($config):void
    {
        if (self::$_loaded) {
            return;
        }

        self::$_configs = $config;
        self::$_loaded = true;
    }

    /**
     * 获取配置
     * @param $key
     * @param $default
     * @return mixed|null
     */
    public static function get($key, $default = null)
    {
        return isset(self::$_configs[$key]) ? self::$_configs[$key] : $default;
    }

    /**
     * 获取所有配置
     * @return array
     */
    public static function getAll():array
    {
        return self::$_configs;
    }
}