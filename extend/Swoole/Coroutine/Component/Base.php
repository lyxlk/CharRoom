<?php

namespace Swoole\Coroutine\Component;

use Swoole;
use Swoole\Coroutine\Context;

abstract class Base
{
    /**
     * @var \SplQueue
     */
    protected $pool;
    protected $config;
    protected $type;

    function __construct($config)
    {
        if (empty($config['object_id']))
        {
            throw new Swoole\Exception\InvalidParam("require object_id");
        }
        $this->config = $config;
        $this->pool = new \SplQueue();
        $this->type .= '_'.$config['object_id'];
        \Swoole::getInstance()->beforeAction([$this, '_createObject']);
        \Swoole::getInstance()->afterAction([$this, '_freeObject']);
    }

    function _createObject()
    {
        if ($this->pool->count() > 0)
        {
            $object = $this->pool->pop();
        }
        else
        {
            $object = $this->create();
        }
        Context::put($this->type, $object);
        return $object;
    }

    function _freeObject()
    {
        $cid = Swoole\Coroutine::getuid();
        if ($cid < 0)
        {
            return;
        }
        $object = Context::get($this->type);
        if ($object)
        {
            $this->pool->push($object);
            Context::delete($this->type);
        }
    }

    protected function _getObject()
    {
        return Context::get($this->type);
    }

    abstract function create();
}