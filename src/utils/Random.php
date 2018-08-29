<?php
/**
 * Created by PhpStorm.
 * User: wuzhc
 * Date: 18-8-29
 * Time: 下午10:57
 */

namespace zcswoole\utils;


class Random
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
    public static function generateUniqueCode()
    {
        return md5(uniqid(microtime(true),true));
    }
}