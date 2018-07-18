<?php
namespace Swoole\Protocol;
use Swoole;

/**
 * 协议基类，实现一些公用的方法
 * @package Swoole\Protocol
 */
abstract class Base implements Swoole\IFace\Protocol
{
    public $default_port;
    public $default_host;
    /**
     * @var \Swoole\IFace\Log
     */
    public $log;

    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var array
     */
    protected $clients;

    /**
     * 设置Logger
     * @param $log
     */
    function setLogger($log)
    {
        $this->log = $log;
    }

    function run($array)
    {
        \Swoole\Error::$echo_html = true;
        $this->server->run($array);
    }

    function daemonize()
    {
        $this->server->daemonize();
    }

    /**
     * 打印Log信息
     * @param $msg
     * @param string $type
     */
    function log($msg)
    {
        $this->log->info($msg);
    }

    function task($task, $dstWorkerId = -1, $callback = null)
    {
        $this->server->task($task, $dstWorkerId = -1, $callback);
    }

    function onStart($server)
    {

    }

    function onConnect($server, $client_id, $from_id)
    {

    }

    function onClose($server, $client_id, $from_id)
    {

    }

    function onShutdown($server)
    {

    }
}