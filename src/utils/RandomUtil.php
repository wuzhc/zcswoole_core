<?php

namespace zcswoole\utils;

/**
 * 生成随机值工具类
 * Class RandomUtil
 * @package zcswoole\utils
 * @author wuzhc 2018-08-29
 */
class RandomUtil
{
    /**
     * 随机字符串
     * @param $len
     * @param $type
     * @return string
     */
    public static function generateRandCode($len, $type)
    {
        if ($len <= 0) {
            return '';
        }

        mt_srand(); // @see https://wiki.swoole.com/wiki/page/732.html

        if ($type == 1) {
            $str = '0123456789';
        } elseif ($type == 2) {
            $str = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        } elseif ($type == 3) {
            $str = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        } else {
            return '';
        }

        $code = '';
        $strLen = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $code .= $str[mt_rand(0, $strLen - 1)];
        }

        return $code;
    }

    /**
     * 唯一字符串
     * @return string
     */
    public static function generateUniqueStr()
    {
        return md5(uniqid(microtime(true), true));
    }

    /**
     * 唯一数字
     * @return int
     */
    public static function generateUniqueNum()
    {
        $us = strstr(microtime(), ' ', true);
        return intval(strval($us * 1000 * 1000) . rand(100, 999));
    }
}