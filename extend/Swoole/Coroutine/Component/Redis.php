<?php

namespace Swoole\Coroutine\Component;

use Swoole\Coroutine\Redis as CoRedis;

class Redis extends Base
{
    protected $type = 'redis';

    function create()
    {
        $redis = new CoRedis($this->config);
        if ($redis->connect($this->config['host'], $this->config['port']) === false)
        {
            return false;
        }
        else
        {
            return $redis;
        }
    }

    function get($key)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }
        return $redis->get($key);
    }

    public function set($key, $value, $expire = null)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        if ($expire)
        {
            return $redis->setEx($key, $expire, $value);
        }
        else
        {
            return $redis->set($key, $value);
        }
    }

    /**
     * @return mixed
     */
    public function setBit(){}

    /**
     * @return mixed
     */
    public function setEx($key, $ttl, $value)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->setEx($key, $ttl, $value);
    }

    /**
     * @return mixed
     */
    public function psetEx(){}

    /**
     * @return mixed
     */
    public function lSet(){}

    /**
     * @return mixed
     */
    public function mGet(){}

    /**
     * @return mixed
     */
    public function del(){}

    /**
     * @return mixed
     */
    public function hDel(){}

    /**
     * @return mixed
     */
    public function hSet(){}

    /**
     * @return mixed
     */
    public function hMSet(){}

    /**
     * @return mixed
     */
    public function hSetNx(){}

    /**
     * @return mixed
     */
    public function delete(){}

    /**
     * @return mixed
     */
    public function mSet(){}

    /**
     * @return mixed
     */
    public function mSetNx(){}

    /**
     * @return mixed
     */
    public function getKeys(){}

    /**
     * @return mixed
     */
    public function keys(){}

    /**
     * @param $key [required]
     * @return mixed
     */
    public function exists($key)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->exists($key);
    }

    /**
     * @param $key[required]
     * @return mixed
     */
    public function type($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function strLen($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function lPop($key){}

    /**
     * @return mixed
     */
    public function blPop(){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function rPop($key){}

    /**
     * @return mixed
     */
    public function brPop(){}

    /**
     * @return mixed
     */
    public function bRPopLPush(){}

    /**
     * @return mixed
     */
    public function lSize(){}

    /**
     * @return mixed
     */
    public function lLen(){}

    /**
     * @return mixed
     */
    public function sSize(){}

    /**
     * @return mixed
     */
    public function scard(){}

    /**
     * @return mixed
     */
    public function sPop(){}

    /**
     * @param $key [required]
     * @return mixed
     */
    public function sMembers($key)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->sMembers($key);
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function sGetMembers($key)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->sGetMembers($key);
    }

    /**
     * @param $key
     * @param int $count
     * @return bool|mixed
     */
    public function sRandMember($key, $count = 1)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->sRandMember($key, $count);
    }

    /**
     * @return mixed
     */
    public function persist(){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function ttl($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function pttl($key){}

    /**
     * @return mixed
     */
    public function zCard(){}

    /**
     * @return mixed
     */
    public function zSize(){}

    /**
     * @return mixed
     */
    public function hLen(){}

    /**
     * @return mixed
     */
    public function hKeys(){}

    /**
     * @return mixed
     */
    public function hVals(){}

    /**
     * @return mixed
     */
    public function hGetAll(){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function debug($key){}

    /**
     * @return mixed
     */
    public function restore(){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function dump($key){}

    /**
     * @return mixed
     */
    public function renameKey(){}

    /**
     * @return mixed
     */
    public function rename(){}

    /**
     * @return mixed
     */
    public function renameNx(){}

    /**
     * @return mixed
     */
    public function rpoplpush(){}

    /**
     * @return mixed
     */
    public function randomKey(){}

    /**
     * @return mixed
     */
    public function ping(){}

    /**
     * @return mixed
     */
    public function auth(){}

    /**
     * @return mixed
     */
    public function unwatch(){}

    /**
     * @return mixed
     */
    public function watch(){}

    /**
     * @return mixed
     */
    public function save(){}

    /**
     * @return mixed
     */
    public function bgSave(){}

    /**
     * @return mixed
     */
    public function lastSave(){}

    /**
     * @return mixed
     */
    public function flushDB(){}

    /**
     * @return mixed
     */
    public function flushAll(){}

    /**
     * @return mixed
     */
    public function dbSize(){}

    /**
     * @return mixed
     */
    public function bgrewriteaof(){}

    /**
     * @return mixed
     */
    public function time(){}

    /**
     * @return mixed
     */
    public function role(){}

    /**
     * @return mixed
     */
    public function setRange(){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function setNx($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function getSet($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function append($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function lPushx($key, $value){}

    /**
     * @return mixed
     */
    public function lPush(){}

    /**
     * @return mixed
     */
    public function rPush(){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function rPushx($key, $value){}

    /**
     * @param $key [required]
     * @param $value [required]
     * @return mixed
     */
    public function sContains($key, $value)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->sContains($key, $value);
    }

    /**
     * @param $key [required]
     * @param $value [required]
     * @return mixed
     */
    public function sismember($key, $value)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->sismember($key, $value);
    }

    /**
     * @param $key [required]
     * @param $value [required]
     * @return mixed
     */
    public function zScore($key, $value)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->zScore($key, $value);
    }

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function zRank($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function zRevRank($key, $value){}

    /**
     * @param $key
     * @param $hashKey
     * @return bool|mixed
     */
    public function hGet($key, $hashKey)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->hGet($key, $hashKey);
    }

    /**
     * @return mixed
     */
    public function hMGet($key, $hashKeys)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->hMGet($key, $hashKeys);
    }

    /**
     * @return mixed
     */
    public function hExists(){}

    /**
     * @return mixed
     */
    public function publish(){}

    /**
     * @param $key
     * @param $value
     * @param $member
     * @return bool|mixed
     */
    public function zIncrBy($key, $value, $member)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->zIncrBy($key, $value, $member);
    }

    /**
     * @param array ...$args
     * @return bool|mixed
     */
    public function zAdd(...$args)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->zAdd(...$args);
    }

    /**
     * @return mixed
     */
    public function zDeleteRangeByScore(){}

    /**
     * @return mixed
     */
    public function zRemRangeByScore(){}

    /**
     * @return mixed
     */
    public function zCount(){}

    /**
     * @return mixed
     */
    public function zRange($key, $start, $end, $withscores = null)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->zRange($key, $start, $end, $withscores);
    }

    /**
     * @return mixed
     */
    public function zRevRange(){}

    /**
     * @return mixed
     */
    public function zRangeByScore(){}

    /**
     * @return mixed
     */
    public function zRevRangeByScore(){}

    /**
     * @return mixed
     */
    public function zRangeByLex(){}

    /**
     * @return mixed
     */
    public function zRevRangeByLex(){}

    /**
     * @return mixed
     */
    public function zInter(){}

    /**
     * @return mixed
     */
    public function zinterstore(){}

    /**
     * @return mixed
     */
    public function zUnion(){}

    /**
     * @return mixed
     */
    public function zunionstore(){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function incrBy($key, $integer){}

    /**
     * @return mixed
     */
    public function hIncrBy(){}

    /**
     * @param $key [required]
     * @return mixed
     */
    public function incr($key)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->incr($key);
    }

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function decrBy($key, $integer){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function decr($key){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function getBit($key, $integer){}

    /**
     * @return mixed
     */
    public function lInsert(){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function lGet($key, $integer){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function lIndex($key, $integer){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function setTimeout($key, $integer){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function expire($key, $integer){}

    /**
     * @return mixed
     */
    public function pexpire(){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function expireAt($key, $integer){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function pexpireAt($key, $integer){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function move($key, $integer){}

    /**
     * @return mixed
     */
    public function select(){}

    /**
     * @return mixed
     */
    public function getRange(){}

    /**
     * @return mixed
     */
    public function listTrim(){}

    /**
     * @return mixed
     */
    public function ltrim(){}

    /**
     * @return mixed
     */
    public function lGetRange(){}

    /**
     * @return mixed
     */
    public function lRange(){}

    /**
     * @return mixed
     */
    public function lRem(){}

    /**
     * @return mixed
     */
    public function lRemove(){}

    /**
     * @return mixed
     */
    public function zDeleteRangeByRank(){}

    /**
     * @return mixed
     */
    public function zRemRangeByRank(){}

    /**
     * @param $key[required]
     * @param $float_number[required]
     * @return mixed
     */
    public function incrByFloat($key, $float_number){}

    /**
     * @return mixed
     */
    public function hIncrByFloat(){}

    /**
     * @return mixed
     */
    public function bitCount(){}

    /**
     * @return mixed
     */
    public function bitOp(){}

    /**
     * @return mixed
     */
    public function sAdd($key, $value)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->sAdd($key, $value);
    }

    /**
     * @return mixed
     */
    public function sMove(){}

    /**
     * @return mixed
     */
    public function sDiff(){}

    /**
     * @return mixed
     */
    public function sDiffStore(){}

    /**
     * @return mixed
     */
    public function sUnion(){}

    /**
     * @return mixed
     */
    public function sUnionStore(){}

    /**
     * @return mixed
     */
    public function sInter(){}

    /**
     * @return mixed
     */
    public function sInterStore(){}

    /**
     * @return mixed
     */
    public function sRemove($key, $member){
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->sRemove($key, $member);
    }

    /**
     * @return mixed
     */
    public function srem($key, $member)
    {
        /**
         * @var $redis CoRedis
         */
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }

        return $redis->sRemove($key, $member);
    }

    /**
     * @return mixed
     */
    public function zDelete(){}

    /**
     * @return mixed
     */
    public function zRemove(){}

    /**
     * @return mixed
     */
    public function zRem(){}

    /**
     * @return mixed
     */
    public function pSubscribe(){}

    /**
     * @return mixed
     */
    public function subscribe(){}

    /**
     * @return mixed
     */
    public function multi(){}

    /**
     * @return mixed
     */
    public function exec(){}

    /**
     * @return mixed
     */
    public function evaluate(){}

    /**
     * @return mixed
     */
    public function evalSha(){}

    /**
     * @return mixed
     */
    public function script(){}

}