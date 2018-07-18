<?php
namespace Swoole\IFace;

interface EventHandler
{
    function trigger($type, $data);
}