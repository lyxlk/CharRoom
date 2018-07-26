<?php
/**
 * Created by PhpStorm.
 * User: KevinLin
 * Date: 2017/4/10
 * Time: 16:46
 */
namespace app\index\model;
use think\Model;

class Model_Keys extends Model {
    //聊天记录
    public static function getOneLog() {
        return __FUNCTION__;
    }

    //防并发
    public static function pokerReceive($fd) {
        return __FUNCTION__."|{$fd}";
    }

    //sessidAndFd
    public static function uinfo($sessid) {
        return __FUNCTION__."|{$sessid}";
    }

    public static function sessidAndFd() {
        return __FUNCTION__;
    }

}