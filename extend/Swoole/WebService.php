<?php
namespace Swoole;
class WebService
{
	public $access_ip = array();
	public $class_list = array();
	public $func_list = array();
	public $auth;
	public $error_msg = array('未知错误','IP不允许访问!','不存在的函数调用!','不存在的函数调用!','不存在的方法调用!','不存在的类!','用户名或密码错误!');

	function reg_auth($check)
	{
	    $this->auth = $check;
	}
    /**
     * 注册函数
     * @param $func_name
     * @param $func
     * @return unknown_type
     */
	function reg_func($func_name,$func)
	{
	    if(!function_exists($func)) error(903);
	    $this->func_list[$func_name] = $func;
	}
    /**
     * 注册类接口
     * @param $class_name
     * @param $class
     * @return unknown_type
     */
    function reg_class($class_name,$class)
	{
	    if(!class_exists($class)) error(904);
	    $this->class_list[$class_name] = $class;
	}
	function error($code)
	{
	    return json_encode(array('error_id'=>$code,'error_msg'=>$this->error_msg[$code]));
	}
	function run()
	{
	    $res = $this->worker();
	    echo $res;
	}
    /**
     * 运行
     * @return unknown_type
     */
	function worker()
	{
        $ip = Swoole_client::getIP();
        if(count($this->access_ip)>0 and !in_array($ip,$this->access_ip)) return $this->error(1);
        $auth_func = $this->auth;
        if(!$auth_func or $auth_func(trim($_GET['user']),trim($_GET['pass'])))
        {
            unset($_GET['user'],$_GET['pass']);
            if(!empty($_GET['func']))
            {
                $func = trim($_GET['func']);
                unset($_GET['func']);

                if(isset($this->func_list[$func]))
                {
                    $func = $this->func_list[$func];
                    if(!function_exists($func)) return $this->error(2);
                    return json_encode(call_user_func_array($func,$_GET));
                }
                else return $this->error(3);
            }
            elseif(!empty($_GET['class']) and !empty($_GET['method']))
            {
                $class = trim($_GET['class']);
                $method = trim($_GET['method']);
                unset($_GET['class'],$_GET['method']);

                if(isset($this->class_list[$class]))
                {
                    $class = $this->class_list[$class];
                    $obj = new $class;
                    if(!method_exists($obj,$method)) return $this->error(4);
                    foreach($_GET as $k=>$g)
                    {
                        $obj->$k = $g;
                    }
                    return json_encode(call_user_func_array(array($obj,$method),$_POST));
                }
                else return $this->error(5);
            }
        }
        else return $this->error(6);
	}
}