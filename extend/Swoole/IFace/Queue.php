<?php
namespace Swoole\IFace;

interface Queue
{
    function push($data);
    function pop();
}