<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2018/1/11
 * Time: 14:41
 */

namespace My;
class RedisPackage
{
    public static $handler = null;
    public $work_id; //静态类需要和不同线程的id 绑定

    public $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,//关闭时间 0:代表不关闭
        'expire' => 0,
        'persistent' => true,//使用长连接
        'prefix' => '',
    ];

    public $merge_options = [];

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments) {
        try {
            if(method_exists(self::$handler[$this->work_id],$name)) {
                return call_user_func_array(array(self::$handler[$this->work_id],$name),$arguments);
            } else {
                Kit::debug(json_encode(array('name'=>$name,'ar'=>$arguments)),'redis_fun_not_exist');
                return false;
            }
        } catch (\Exception $e) {
            Kit::debug($e->getMessage(),'redis_call_func_err');
            return false;
        }
    }

    /**
     * @return bool
     * 获取状态
     */
    public function getStatus() {
        return is_resource(self::$handler[$this->work_id]->socket) && (self::$handler[$this->work_id]->ping() == '+PONG');
    }


    /**
     * RedisPackage constructor.
     * @param array $options
     * @param $work_id
     * 入口初始化
     */
    public function __construct($options = [],$work_id) {
        try {
            $this->work_id = $work_id;

            $this->merge_options = array_merge($this->options, $options);

            if(!isset(self::$handler[$this->work_id])) {
                if (!extension_loaded('redis')) {   //判断是否有扩展(如果你的apache没reids扩展就会抛出这个异常)
                    throw new \BadFunctionCallException('not support: redis');
                }
                self::$handler[$this->work_id] = new \Redis;
            }

            $func = $this->merge_options['persistent'] ? 'pconnect' : 'connect';     //判断是否长连接
            self::$handler[$this->work_id]->$func($this->merge_options['host'], $this->merge_options['port'], $this->merge_options['timeout']);

            if ('' != $this->merge_options['password']) {
                self::$handler[$this->work_id]->auth($this->merge_options['password']);
            }

            self::$handler[$this->work_id]->select($this->merge_options['select']);

        } catch (\Exception $e) {
            $err = "【".__CLASS__."|".__FUNCTION__."】|MSG|".$e->getMessage();
            Kit::debug($err,'redis_err');
        }

        return empty(self::$handler[$this->work_id]) ? null : self::$handler[$this->work_id];
    }

    /**
     * 释放资源
     */
    public function close() {
        self::$handler[$this->work_id]->close();
    }

    /**
     * 强制释放所有资源
     */
    public static function clear() {
        self::$handler = null;
    }

    /**
     * 程序退出 关闭redis链接
     */
    public function __destruct() {
       // $this->close();
    }
}
