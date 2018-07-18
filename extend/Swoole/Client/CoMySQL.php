<?php
namespace Swoole\Client;
use Swoole\Database\MySQLi;
use Swoole\Database\MySQLiRecord;

/**
 * 并发MySQL客户端
 * concurrent mysql client
 * Class CoMySQL
 * @package Swoole\Client
 */
class CoMySQL
{
    protected $config;
    protected $list;
    protected $results;
    protected $sqlIndex = 0;
    protected $pool = array();

    function __construct($db_key = 'master')
    {
        $this->config = \Swoole::getInstance()->config['db'][$db_key];
        //不能使用长连接，避免进程内占用大量连接
        $this->config['persistent'] = false;
    }

    protected function getConnection()
    {
        //没有可用的连接
        if (count($this->pool) == 0)
        {
            $db = new MySQLi($this->config);
            $db->connect();
            return $db;
        }
        //从连接池中取一个
        else
        {
            return array_pop($this->pool);
        }
    }

    /**
     * @param $sql
     * @param null $callback
     * @return bool|CoMySQLResult
     */
    function query($sql, $callback = null)
    {
        $db = $this->getConnection();
        $result = $db->queryAsync($sql);
        if (!$result)
        {
            return false;
        }
        $retObj = new CoMySQLResult($db, $callback);
        $retObj->sql = $sql;
        $this->list[] = $retObj;
        $retObj->id = $this->sqlIndex++;
        $db->_co_id = $retObj->id;
        return $retObj;
    }

    function wait($timeout = 1.0)
    {
        $_timeout_sec = intval($timeout);
        $_timeout_usec = intval(($timeout - $_timeout_sec) * 1000 * 1000);
        $taskSet = $this->list;

        $processed = 0;
        do
        {
            $links = $errors = $reject = array();
            foreach ($taskSet as $k => $retObj)
            {
                $links[] = $errors[] = $reject[] = $retObj->db;
            }
            //wait mysql server response
            if (!mysqli_poll($links, $errors, $reject, $_timeout_sec, $_timeout_usec))
            {
                continue;
            }
            /**
             * @var $link mysqli
             */
            foreach ($links as $link)
            {
                $_retObj = $this->list[$link->_co_id];
                $result = $link->reap_async_query();
                if ($result)
                {
                    if (is_object($result))
                    {
                        $_retObj->result = new MySQLiRecord($result);
                        if ($_retObj->callback)
                        {
                            call_user_func($_retObj->callback, $_retObj->result);
                        }
                        $_retObj->code = 0;
                    }
                    else
                    {
                        $_retObj->code = CoMySQLResult::ERR_NO_OBJECT;
                    }
                }
                else
                {
                    trigger_error(sprintf("MySQLi Error: %s", $link->error));
                    $_retObj->code = $link->errno;
                }
                //从任务队列中移除
                unset($taskSet[$link->_co_id]);
                $processed++;
            }
        } while ($processed < count($this->list));
        //将连接重新放回池中
        foreach ($this->list as $_retObj)
        {
            $this->pool[] = $_retObj->db;
        }
        //初始化数据
        $this->list = array();
        $this->sqlIndex = 0;
        return $processed;
    }
}

class CoMySQLResult
{
    public $id;
    public $db;
    public $callback = null;
    /**
     * @var MySQLiRecord
     */
    public $result;
    public $sql;
    public $code = self::ERR_NO_READY;

    const ERR_NO_READY = 6001;
    const ERR_TIMEOUT = 6002;
    const ERR_NO_OBJECT = 6003;

    function __construct(\mysqli $db, callable $callback = null)
    {
        $this->db = $db;
        $this->callback = $callback;
    }
}