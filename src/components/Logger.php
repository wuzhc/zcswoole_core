<?php

namespace zcswoole\components;

/**
 * 日志工具类
 * Class Logger
 */
class Logger extends Component
{

    /** @var string 日志文件 */
    public $logFile;

    public function __construct()
    {
        $this->logFile = $this->logFile ?: DIR_ROOT . '/app/log/zcswoole.log';
        $this->createDir(dirname($this->logFile));
    }

    /**
     * 异步写入
     * e.g. ZCSwoole::$app->logger->asyncWrite('hello logger', DIR_ROOT . '/app/log/async_zcswoole.log');
     * @param $msg
     * @param string $logFile
     * @param string $mode
     * @param bool $showTrace
     */
    public function asyncWrite($msg, $logFile = '', $mode = 'a+', $showTrace = false)
    {
        if ($msg === null) {
            return ;
        }

        $logFile = $logFile ?: $this->logFile;
        $this->createDir(dirname($this->logFile));

        $flag = 0;
        if ($mode == 'a+') {
            $flag = FILE_APPEND;
        }

        // 是否显示调用文件路径
        if (false !== $showTrace) {
            $traces = debug_backtrace();
            $trace = array_pop($traces);
            $content = '[' . date('Y-m-d H:i:s', time()) . ' => ' . $trace['file'] . ']';
            $content .= "\r\n";
        } else {
            $content = '[' . date('Y-m-d H:i:s', time()) . '] ';
        }

        $content .= $msg;
        $content .= "\r\n";

        swoole_async_writefile($logFile, $content, function(){}, $flag);
    }

    /**
     * Write log
     * e.g. ZCSwoole::$app->logger->write('hello logger');
     * @param string $msg 日志内容
     * @param string $mode 模式
     * @param string $logFile 日志文件
     * @param bool $showTrace 是否显示调用文件
     * @return bool
     * @author wuzhc 2017-06-20
     */
    public function write($msg, $logFile = '', $mode = 'a+', $showTrace = false)
    {
        if ($msg === null) {
            return false;
        }

        $logFile = $logFile ?: $this->logFile;
        $this->createDir(dirname($this->logFile));

        $fp = fopen($logFile, $mode);
        if (!$fp) {
            echo 'fopen() failed';
            return false;
        }

        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }

        $max = 3; // 重试次数
        $retries = 0;
        do {
            if ($retries > 0) {
                sleep(1);
            }
            $retries++;
        } while (!flock($fp, LOCK_EX) && $retries < $max); // 日志加锁，防止并发条件下数据错乱
        if ($max == $retries) {
            return false;
        }

        // 是否显示调用文件路径
        if (false !== $showTrace) {
            $traces = debug_backtrace();
            $trace = array_pop($traces);
            $header = '[' . date('Y-m-d H:i:s', time()) . ' => ' . $trace['file'] . ']';
            $header .= "\r\n";
            fwrite($fp, $header);
        } else {
            $header = '[' . date('Y-m-d H:i:s', time()) . '] ';
            fwrite($fp, $header);
        }

        fwrite($fp, $msg, strlen($msg));
        fwrite($fp, "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    /**
     * Delete log
     * @param string $logFile 需要传递文件的绝对路径
     * @param bool   $hint
     * @return bool
     * @author wuzhc 2017-06-20
     */
    public function delete($logFile, $hint = false)
    {
        $res = @unlink($logFile);
        if ($hint) {
            echo $res ? 'delete log success' : 'delete log fail';
        }
        return $res;
    }

    /**
     * Read log
     * @param string $logFile 需要传递文件的绝对路径
     * @return bool
     * @author wuzhc 2017-06-20
     */
    public function read($logFile = '')
    {
        @ob_end_clean(); // 清除缓冲区
        @ob_implicit_flush(true); // 实时输出

        $logFile = $logFile ?: $this->logFile;

        $fp = @fopen($logFile, 'r');
        if (!$fp) {
            echo 'fopen() failed';
            return false;
        }
        while (!feof($fp)) {
            echo fgets($fp, 8192);
            echo php_sapi_name() == 'cli' ? PHP_EOL : '<br>';
        }
        fclose($fp);
    }

    /**
     * 创建目录（支持多级目录）
     * @param string $path
     * @param int $mode
     * @return bool
     * @author wuzhc 2017-06-21
     */
    public function createDir($path, $mode = 0777)
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
     * @author wuzhc 2017-06-21
     */
    public function readDir($path)
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
     * @author wuzhc 2017-06-21
     */
    public function delDir($path)
    {
        $dirs = @scandir($path);
        if (!$dirs) {
            return false;
        }
        $files = array_diff($dirs, array(
            '.',
            '..'
        ));
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? $this->delDir("$path/$file") : unlink("$path/$file");
        }
        return rmdir($path);
    }
}
