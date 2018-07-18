<?php

namespace Swoole\Client;

use Swoole\Core;
use Swoole\Coroutine\RPC as CoRPC;

if (Core::$enableCoroutine)
{
    class SOA extends CoRPC
    {

    }
}
else
{
    class SOA extends RPC
    {

    }
}
