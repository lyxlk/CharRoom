<?php
namespace Swoole\Log;
use Swoole;

class EchoLog extends Swoole\Log implements Swoole\IFace\Log
{
    protected $display = true;

    function __construct($config)
    {
        if (isset($config['display']) and $config['display'] == false)
        {
            $this->display = false;
        }
        parent::__construct($config);
    }

    function put($msg, $level = self::INFO)
    {
        if ($this->display)
        {
            $log = $this->format($msg, $level);
            if ($log) echo $log;
        }
    }
}