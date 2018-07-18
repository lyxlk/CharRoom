<?php
namespace Swoole;
use Swoole\Exception\NotFound;

/**
 * Class Factory
 * @package Swoole
 * @method static getCache
 */
class Factory
{
    public static function __callStatic($func, $params)
    {
        $resource_id = empty($params[0]) ? 'master' : $params[0];
        $resource_type = strtolower(substr($func, 3));
        if (empty(\Swoole::$php->config[$resource_type][$resource_id]))
        {
            throw new NotFound(__CLASS__.": resource[{$resource_type}/{$resource_id}] not found.");
        }
        $config = \Swoole::$php->config[$resource_type][$resource_id];
        $class = '\\Swoole\\'.ucfirst($resource_type).'\\' . $config['type'];
        return new $class($config);
    }
}
