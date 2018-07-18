<?php
namespace Swoole\Client;
/**
 * Swoole WebService客户端
 * @author Tianfeng.Han
 * @package Swoole
 * @subpackage Client
 */
class Rest
{
    public $server_url;
    public $keep_alive = false;
    public $client_type;
    public $http;
    public $debug;

    function __construct($url,$user='',$password='')
    {
        $this->server_url = $url."?user=$user&pass=".\Auth::mkpasswd($user,$password).'&';
        $this->client_type = 'curl';
        $this->http = new CURL($this->debug);
        if($this->keep_alive)
        {
            $header[] = "Connection: keep-alive";
            $header[] = "Keep-Alive: 300";
            $this->http->set_header($header);
        }
    }

    function call($param,$post=null)
    {
        foreach($param as &$m)
        {
            if(is_array($m) or is_object($m)) $m = serialize($m);
        }
        $url = $this->server_url.\Swoole\Tool::combine_query($param);
        if($post===null) $res = $this->http->get($url);
        else $res = $this->http->post($url,$post);
        if($this->debug) echo $url,BL,$res;
        return json_decode($res);
    }

    function method($class,$method,$attrs,$param)
    {
        $attrs['class'] = $class;
        $attrs['method'] = $method;
        return $this->call($attrs,$param);
    }

    function func($func,$param)
    {
        $param['func'] = $func;
        return $this->call($param);
    }
    function create($class,$param=array())
    {
        $obj = new RestObject($class,$this);
        $obj->attrs = $param;
        return $obj;
    }
}

class RestObject
{
    public $server_url;
    private $class;
    private $rest;
    private $attrs;

    function __construct($class,$rest)
    {
        $this->class = $class;
        $this->rest = $rest;
    }

    function __get($attr)
    {
        return $this->attrs[$attr];
    }

    function __set($attr,$value)
    {
        $this->attrs[$attr] = $value;
        return true;
    }
    function __call($method,$param)
    {
        return $this->rest->method($this->class,$method,$this->attrs,$param);
    }
}