<?php

namespace zcswoole\utils;

/**
 * Class FileUtil
 * @package zcswoole\utils
 * @author wuzhc 2018-08-28
 */
class FileUtil
{
    /**
     * 文件后缀明
     * @param $name
     * @return mixed
     */
    public static function getExtension($name)
    {
        $arr = explode('.', $name);
        return end($arr);
    }

    /**
     * 获取文件上次被修改的时间
     * @param $filePath
     * @return bool|int
     */
    public static function getFileMTime($filePath)
    {
        if (!is_file($filePath)) {
            return 0;
        }
        return filemtime($filePath);
    }

    /**
     * Write File
     * @param string $content 内容
     * @param string $filePath 文件目录
     * @param string $mode 模式
     * @return bool
     * @author wuzhc 2017-06-20
     */
    public static function write($content, $filePath, $mode = 'a+')
    {
        $fp = fopen($filePath, $mode);
        if (!$fp) {
            return false;
        }

        if (!is_string($content)) {
            $content = json_encode($content);
        }

        $max = 3; // 重试次数
        $retries = 0;
        do {
            if ($retries > 0) {
                sleep(1);
            }
            $retries++;
        } while (!flock($fp, LOCK_EX) && $retries < $max); // 加锁，防止并发条件下数据错乱
        if ($max == $retries) {
            return false;
        }

        fwrite($fp, $content, strlen($content));
        fwrite($fp, "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * Delete file
     * @param string $filePath 文件路径
     * @return bool
     * @author wuzhc 2017-06-20
     */
    public static function delete($filePath)
    {
        if (file_exists($filePath)) {
            return @unlink($filePath);
        } else {
            return true;
        }
    }

    /**
     * Read File
     * @param string $filePath
     * @return bool
     */
    public static function read($filePath)
    {
        $fp = @fopen($filePath, 'r');
        if (!$fp) {
            return false;
        }

        $content = '';
        while (!feof($fp)) {
            $content .= fgets($fp, 8192);
        }
        fclose($fp);

        return $content;
    }

    /**
     * 创建目录（支持多级目录）
     * @param string $path
     * @param int $mode
     * @return bool
     */
    public static function createDir($path, $mode = 0777)
    {
        if (is_dir($path)) {
            return true;
        }
        return @mkdir($path, $mode, true);
    }

    /**
     * 读取目录文件
     * @param $path
     * @return array
     */
    public static function readDir($path)
    {
        $dirs = @scandir($path);
        if (!$dirs) {
            return array();
        }
        return array_diff($dirs, array(
            '.',
            '..'
        ));
    }

    /**
     * 删除目录（支持删除多级目录）
     * @param $path
     * @return bool
     */
    public static function delDir($path)
    {
        $files = self::readDir($path);
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? self::delDir("$path/$file") : unlink("$path/$file");
        }
        return rmdir($path);
    }
}