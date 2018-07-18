<?php
/**
 * Created by PhpStorm.
 * User: yanchunhao
 * Date: 2015/12/2
 * Time: 18:18
 */
namespace Swoole;

class CLPack {
    const MAX_LEN = 8388608, LEN_BYTE = 8;

    static function pack($data, $sign = 0) {
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $data = json_encode($data);
        }
        if (strlen($data) > self::MAX_LEN) {
            return false;
        }
        return pack('NN', strlen($data), $sign) . $data;
    }

    static function unpack($data) {
        $head = @unpack("Nlen/Nsign", $data);
        $body = @json_decode(substr($data, self::LEN_BYTE), 1);
        if (isset($head['sign'])) {
            return array(
                $head['sign'],
                $body
            );
        }
        return array();
    }
}