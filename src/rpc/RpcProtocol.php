<?php

namespace zcswoole\rpc;


/**
 * 包头(pkg_len+version+command_id+encode_type) + 包体(数据根据encode_type编码,例如json_encode)
 * Class RpcProtocol
 * @package zcswoole\rpc
 * @author wuzhc 2018-08-20
 */
class RpcProtocol
{
    const PHP_JSON = 1;
    const PHP_SERIALIZE = 2;
    const SWOOLE_SERIALIZE = 3;
    const PKG_HEADER_SIZE = 16;          // 包头固定为16字节

    const ERR_UNPACK_OK = 0;             // 解包成功
    const ERR_DATA_INCORRECT = 100;      // 数据格式不正确
    const ERR_PARSE_HEADER_FAILED = 101; // 解析包头失败
    const ERR_PARSE_BODY_FAILED = 102;   // 解析包体错误

    /**
     * 二进制打包; 包头(pkg_len+version+command_id+encodeType)
     * @param int $encodeType
     * @param int $commandID 消息命令ID
     * @param int $versionID
     * @param $data
     * @return bool|string
     */
    public static function encode($data, $encodeType, $commandID = 0, $versionID = 0)
    {
        if (empty($encodeType)) {
            return false;
        }

        switch ($encodeType) {
            case self::PHP_JSON:
                $body = json_encode($data);
                $header = pack('N4', strlen($body), $versionID, $commandID, self::PHP_JSON);
                break;
            case self::PHP_SERIALIZE:
                $body = serialize($data);
                $header = pack('N4', strlen($body), $versionID, $commandID, self::PHP_SERIALIZE);
                break;
            case self::SWOOLE_SERIALIZE:
                $body = \swoole_serialize::pack($data);
                $header = pack('N4', strlen($body), $versionID, $commandID, self::SWOOLE_SERIALIZE);
                break;
            default:
                return false;
        }

        return $header . $body;
    }

    /**
     * 二进制解包
     * @param $data
     * @return array [状态, 包头, 包体]
     */
    public static function decode($data)
    {
        if (!is_string($data)) {
            return [self::ERR_DATA_INCORRECT, null, null];
        }

        $header = substr($data, 0, self::PKG_HEADER_SIZE);
        $header = unpack('NpkgLen/Nversion/NcommandID/NencodeType', $header);
        if (empty($header['encodeType'])) {
            return [self::ERR_PARSE_HEADER_FAILED, null, null];
        }

        $body = substr($data, self::PKG_HEADER_SIZE);
        if (strlen($body) != $header['pkgLen']) {
            return [self::ERR_PARSE_BODY_FAILED, $header, null];
        }

        switch ($header['encodeType']) {
            case self::PHP_JSON:
                $body = json_decode($body, true);
                break;
            case self::PHP_SERIALIZE:
                $body = unserialize($body);
                break;
            case self::SWOOLE_SERIALIZE:
                $body = \swoole_serialize::unpack($body);
                break;
            default:
                return [self::ERR_DATA_INCORRECT, $header, $body];
        }

        return [self::ERR_UNPACK_OK, $header, $body];
    }
}