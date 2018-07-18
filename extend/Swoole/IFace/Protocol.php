<?php
namespace Swoole\IFace;

interface Protocol
{
    function onStart($server);
    function onConnect($server, $client_id, $from_id);
    function onReceive($server,$client_id, $from_id, $data);
    function onClose($server, $client_id, $from_id);
    function onShutdown($server);
}
