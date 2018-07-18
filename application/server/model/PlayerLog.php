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

        return self::execute(
            'insert into t_msg (fd,msg, add_time,status) values (:fd, :msg, :add_time, :status)',
            [
                'fd'=>$fd,
                'msg'=>$msg,
                'add_time'=>intval($time),
                'status'=>intval($status)
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
}