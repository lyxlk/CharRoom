<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2018/2/12
 * Time: 15:11
 */
namespace app\server\model;
use app\index\model\Model_Keys;
use My\Kit;
use My\RedisPackage;
use think\Db;
use think\Model;

class asyncTask extends Model {

    /**
     * @return PlayerLog|null
     * 数据库对象
     */
    public static function getDbObj() {
        try{
            static $hasgone      = 0;
            static $PlayerLogObj = null;

            $time = time();
            if((!$PlayerLogObj) || ($time - $hasgone > 7200)) {
                Db::clear();
                $PlayerLogObj = new PlayerLog();
            }
            $hasgone = $time;

            return $PlayerLogObj;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param null $serv
     * @param null $task_id
     * @param null $src_worker_id
     * @param null $msg
     * @return bool
     * 异步进程
     */
    public static function logToDb($serv=null, $task_id=null, $src_worker_id=null, $msg=null,$status=0) {
        $PlayerLogObj = self::getDbObj();
        if($PlayerLogObj) {
            return $PlayerLogObj->insertsAll(time(), $msg, $status);
        }

        return false;
    }

    /**
     * @param int $fd
     * @param string $img
     * @param string $nick
     * @param string $ip
     * @return bool|void
     * 修改昵称头像
     */
    public static function LogUserInfoToDb($fd=0,$img='',$nick='',$ip='') {
        $PlayerLogObj = self::getDbObj();
        if($PlayerLogObj) {
            return $PlayerLogObj->LogUserInfoToDb($fd,$img,$nick,$ip);
        }

        return false;
    }
}