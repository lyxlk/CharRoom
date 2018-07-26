<?php
/**
 * project: poker
 * Created by PhpStorm.
 * Author: xjc
 * Date: 2018/1/18
 * Time: 15:21
 * File: User.php
 */
namespace app\server\model;

use app\index\model\Model_Keys;
use My\RedisPackage;
use think\Model;

class PlayerLog extends Model{

    // 设置当前模型的数据库连接
    protected $connection = 'db.db1';

    /**
     * @param $time
     * @param $msg
     * @param $status
     * @return int
     * 插入数据
     */
    public function insertsAll($time,$msg,$status=0)
    {
        $data = json_decode($msg,true);
        $msg  = isset($data['msg']) ? $data['msg'] : "";
        $fd   = isset($data['fd']) ? $data['fd']   : 0;
        $ip   = isset($data['ip']) ? $data['ip']   : 0;

        return self::execute(
            'insert into t_msg (fd,msg, add_time,status,ip) values (:fd, :msg, :add_time, :status,:ip)',
            [
                'fd'=>$fd,
                'msg'=>$msg,
                'add_time'=>intval($time),
                'status'=>intval($status),
                'ip'=>$ip,
            ]
        );

    }

    //获取100条历史记录
    public function getOneLog() {
        try {
            $key   = Model_Keys::getOneLog();
            $redis = new RedisPackage([],9999);
            $ajson = $redis->LPOP($key);
            $aData = json_decode($ajson,true);
            if(empty($aData)) {
                $sql    = "SELECT `fd`,`msg` FROM `t_msg` WHERE  `status`=1 ORDER BY `id` DESC LIMIT 300";
                $result = self::query($sql);
                $aData  = array_pop($result);

                foreach ($result as  $ret) {
                    $redis->RPUSH($key,json_encode($ret));
                }

            }

            return [!empty($aData),$aData];
        }catch (\Exception $e) {
            return [false,$e->getMessage()];
        }

    }

    /**
     * @param int $fd
     * @param string $img
     * @param string $nick
     * @param string $ip
     * @return int
     * 昵称头像记录
     */
    public function LogUserInfoToDb($fd=0,$img='',$nick='',$ip='') {
        try {
            $time = time();
            return self::execute(
                'insert into t_modify_uinfo (fd,img,nick,ip,add_time) values (:fd, :img, :nick, :ip,:add_time)',
                [
                    'fd'=>$fd,
                    'img'=>$img,
                    'nick'=>$nick,
                    'ip'=>$ip,
                    'add_time'=>$time,
                ]
            );
        } catch (\Exception $e) {
            print_r($e->getMessage());exit;
        }

    }
}