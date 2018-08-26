<?php
/**
 * Created by PhpStorm.
 * User: wuzhc
 * Date: 18-8-20
 * Time: 下午12:00
 */

namespace zcswoole\protocol;


class JsonProtocol extends Protocol
{
    public function encode($data)
    {
        $body = json_encode($data);
        $header = pack('N', 1);
        return $header . $body;
    }

    public function decode($data)
    {
        return json_decode($data);
    }
}