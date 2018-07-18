<?php

namespace Swoole\Coroutine\Component;

use Swoole\Coroutine\Context;
use Swoole\Coroutine\MySQL as CoMySQL;
use Swoole\IDatabase;
use Swoole\IDbRecord;

class MySQL extends Base implements IDatabase
{
    protected $type = 'mysql';

    function create()
    {
        $db = new CoMySQL;
        if ($db->connect($this->config) === false)
        {
            return false;
        }
        else
        {
            return $db;
        }
    }

    function query($sql)
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return false;
        }

        $result = false;
        for ($i = 0; $i < 2; $i++)
        {
            $result = $db->query($sql);
            if ($result === false)
            {
                $db->close();
                Context::delete($this->type);
                $db = $this->_createObject();
                continue;
            }
            break;
        }

        return new MySQLRecordSet($result);
    }

    function quote($val)
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return false;
        }

        return $db->escape($val);
    }

    function lastInsertId()
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return false;
        }

        return $db->insert_id;
    }

    function getAffectedRows()
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return false;
        }

        return $db->affected_rows;
    }

    function errno()
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return -1;
        }

        return $db->errno;
    }

    function close()
    {

    }

    function connect()
    {

    }
}

class MySQLRecordSet implements IDbRecord
{
    public $result;

    function __construct($result)
    {
        $this->result = $result;
    }

    function fetch()
    {
        return $this->result[0];
    }

    function fetchall()
    {
        return $this->result;
    }

    function __get($key)
    {
        return $this->result->$key;
    }
}
