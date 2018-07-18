<?php
namespace Swoole\Http;

use Swoole;

/**
 * Class Http_LAMP
 * @package Swoole
 */
class PWS implements Swoole\IFace\Http
{
    function header($k, $v)
    {
        $k = ucwords($k);
        \Swoole::$php->response->setHeader($k, $v);
    }

    function status($code)
    {
        \Swoole::$php->response->setHttpStatus($code);
    }

    function response($content)
    {
        $this->finish($content);
    }

    function redirect($url, $mode = 302)
    {
        \Swoole::$php->response->setHttpStatus($mode);
        \Swoole::$php->response->setHeader('Location', $url);
    }

    function finish($content = null)
    {
        \Swoole::$php->request->finish = 1;
        if ($content)
        {
            \Swoole::$php->response->body = $content;
        }
        throw new Swoole\Exception\Response;
    }

    function setcookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httponly = null)
    {
        \Swoole::$php->response->setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    function getRequestBody()
    {
        return \Swoole::$php->request->body;
    }
}
