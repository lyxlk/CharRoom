<?php
//加载核心的文件
require_once __DIR__ . '/Loader.php';
require_once __DIR__ . '/ModelLoader.php';

use Swoole\Exception\NotFound;

/**
 * Swoole系统核心类，外部使用全局变量$php引用
 * Swoole框架系统的核心类，提供一个swoole对象引用树和基础的调用功能
 *
 * @package    SwooleSystem
 * @author     Tianfeng.Han
 * @subpackage base
 * @property \Swoole\Database    $db
 * @property \Swoole\IFace\Cache $cache
 * @property \Swoole\Upload      $upload
 * @property \Swoole\Component\Event       $event
 * @property \Swoole\Session     $session
 * @property \Swoole\Template    $tpl
 * @property \redis              $redis
 * @property \MongoClient        $mongo
 * @property \Swoole\Config      $config
 * @property \Swoole\Http\PWS    $http
 * @property \Swoole\Log         $log
 * @property \Swoole\Auth        $user
 * @property \Swoole\URL         $url
 * @property \Swoole\Limit       $limit
 * @method \Swoole\Database      db
 * @method \MongoClient          mongo
 * @method \redis                redis
 * @method \Swoole\IFace\Cache   cache
 * @method \Swoole\URL           url
 * @method \Swoole\Platform\Linux os
 */
class Swoole
{
    //所有全局对象都改为动态延迟加载
    //如果希望启动加载,请使用Swoole::load()函数

    /**
     * @var Swoole\Protocol\HttpServer
     */
    public $server;
    public $protocol;

    /**
     * @var Swoole\Request
     */
    public $request;

    public $config;

    /**
     * @var Swoole\Response
     */
    public $response;

    static public $app_path;
    static public $controller_path = '';

    /**
     * @var Swoole\Http\ExtServer
     */
    public $ext_http_server;

    /**
     * 可使用的组件
     */
    static $modules = array(
		'redis' => true,  //redis
        'mongo' => true,  //mongodb
    	'db' => true,  //数据库
        'codb' => true, //并发MySQLi客户端
    	'tpl' => true, //模板系统
    	'cache' => true, //缓存
    	'event' => true, //异步事件
    	'log' => true, //日志
    	'upload' => true, //上传组件
    	'user' => true,   //用户验证组件
        'session' => true, //session
        'http' => true, //http
        'url' => true, //urllib
        'limit' => true, //频率限制组件
    );

    /**
     * 允许多实例的模块
     * @var array
     */
    static $multi_instance = array(
        'cache' => true,
        'db' => true,
        'mongo' => true,
        'redis' => true,
        'url' => true,
        'log' => true,
        'codb' => true,
        'event' => true,
    );

    static $default_controller = array('controller' => 'page', 'view' => 'index');

    static $charset = 'utf-8';
    static $debug = false;

    /**
     * 开启 Swoole-2.x 协程模式
     * @var bool
     */
    static $enableCoroutine = false;
    protected static $coroutineInit = false;

    /**
     * 是否缓存 echo 输出
     * @var bool
     */
    static $enableOutputBuffer = true;

    static $setting = array();
    public $error_call = array();
    /**
     * Swoole类的实例
     * @var Swoole
     */
    static public $php;
    public $pagecache;

    /**
     * 命令
     * @var array
     */
    protected $commands = array();

    /**
     * 捕获异常
     */
    protected $catchers = array();

    /**
     * 对象池
     * @var array
     */
    protected $objects = array();

    /**
     * 传给factory
     */
    public $factory_key = 'master';

    /**
     * 发生错误时的回调函数
     */
    public $error_callback;

    public $load;

    /**
     * @var \Swoole\ModelLoader
     */
    public $model;
    public $env;

    protected $hooks = array();
    protected $router_function;

    const HOOK_INIT = 1; //初始化
    const HOOK_ROUTE = 2; //URL路由
    const HOOK_CLEAN = 3; //清理
    const HOOK_BEFORE_ACTION = 4;
    const HOOK_AFTER_ACTION = 5;

    private function __construct($appDir = '')
    {
        if (!defined('DEBUG')) define('DEBUG', 'on');

        $this->env['sapi_name'] = php_sapi_name();
        if ($this->env['sapi_name'] != 'cli')
        {
            Swoole\Error::$echo_html = true;
        }

        if (!empty($appDir))
        {
            self::$app_path = $appDir;
        }
        elseif (defined('APPSPATH'))
        {
            self::$app_path = APPSPATH;
        }
        elseif (defined('WEBPATH'))
        {
            self::$app_path = WEBPATH . '/apps';
            define('APPSPATH', self::$app_path);
        }

        if (empty(self::$app_path))
        {
            Swoole\Error::info("core error", __CLASS__ . ": Swoole::\$app_path and WEBPATH empty.");
        }

        //将此目录作为App命名空间的根目录
        Swoole\Loader::addNameSpace('App', self::$app_path . '/classes');

        $this->load = new Swoole\Loader($this);
        $this->model = new Swoole\ModelLoader($this);
        $this->config = new Swoole\Config;
        $this->config->setPath(self::$app_path . '/configs');

        //添加默认路由器
        $this->addRouter(new Swoole\Router\Rewrite());
        $this->addRouter(new Swoole\Router\MVC);

        //设置路由函数
        $this->router(array($this, 'urlRoute'));
    }

    /**
     * 初始化
     * @return Swoole
     */
    static function getInstance()
    {
        if (!self::$php)
        {
            self::$php = new Swoole;
        }
        return self::$php;
    }

    /**
     * 获取资源消耗
     * @return array
     */
    function runtime()
    {
        // 显示运行时间
        $return['time'] = number_format((microtime(true)-$this->env['runtime']['start']),4).'s';

        $startMem =  array_sum(explode(' ',$this->env['runtime']['mem']));
        $endMem   =  array_sum(explode(' ',memory_get_usage()));
        $return['memory'] = number_format(($endMem - $startMem)/1024).'kb';
        return $return;
    }
    /**
     * 压缩内容
     * @return null
     */
    function gzip()
    {
        //不要在文件中加入UTF-8 BOM头
        //ob_end_clean();
        ob_start("ob_gzhandler");
        #是否开启压缩
        if (function_exists('ob_gzhandler'))
        {
            ob_start('ob_gzhandler');
        }
        else
        {
            ob_start();
        }
    }

    /**
     * 初始化环境
     * @return null
     */
    function __init()
    {
        #DEBUG
        if (defined('DEBUG') and strtolower(DEBUG) == 'on')
        {
            //记录运行时间和内存占用情况
            $this->env['runtime']['start'] = microtime(true);
            $this->env['runtime']['mem'] = memory_get_usage();
            //使用whoops美化错误页面
            if (class_exists('\\Whoops\\Run'))
            {
                $whoops = new \Whoops\Run;
                if ($this->env['sapi_name'] == 'cli')
                {
                    $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
                }
                else
                {
                    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                }
                $whoops->register();
            }
        }
        $this->callHook(self::HOOK_INIT);
    }

    static function go($func)
    {
        $app = self::getInstance();

        if (!self::$coroutineInit)
        {
            if (Swoole::$enableCoroutine === false)
            {
                throw new RuntimeException("Swoole::\$enableCoroutine cannot be false.");
            }
            $app->loadAllModules();
        }

        return Swoole\Coroutine::create(function () use ($func, $app) {
            $app->callHook(self::HOOK_INIT);
            $app->callHook(self::HOOK_BEFORE_ACTION);
            $func();
            $app->callHook(self::HOOK_AFTER_ACTION);
            $app->callHook(self::HOOK_CLEAN);
        });
    }

    /**
     * 执行Hook函数列表
     * @param $type
     */
    function callHook($type)
    {
        if (isset($this->hooks[$type]))
        {
            foreach ($this->hooks[$type] as $f)
            {
                if (!is_callable($f))
                {
                    trigger_error("SwooleFramework: hook function[$f] is not callable.");
                    continue;
                }
                $f();
            }
        }
    }

    /**
     * 清理
     */
    function __clean()
    {
        $this->env['runtime'] = array();
        $this->callHook(self::HOOK_CLEAN);
    }

    /**
     * 增加钩子函数
     * @param $type
     * @param $func
     * @param $prepend bool
     */
    function addHook($type, $func, $prepend = false)
    {
        if ($prepend)
        {
            array_unshift($this->hooks[$type], $func);
        }
        else
        {
            $this->hooks[$type][] = $func;
        }
    }

    /**
     * 清理钩子程序
     * @param $type
     */
    function clearHook($type = 0)
    {
        if ($type == 0)
        {
            $this->hooks = array();
        }
        else
        {
            $this->hooks[$type] = array();
        }
    }

    /**
     * 在请求之前执行一个函数
     * @param callable $callback
     */
    function beforeRequest(callable $callback)
    {
        $this->addHook(self::HOOK_INIT, $callback);
    }

    /**
     * 在请求之后执行一个函数
     * @param callable $callback
     */
    function afterRequest(callable $callback)
    {
        $this->addHook(self::HOOK_CLEAN, $callback);
    }

    /**
     * 在Action执行前回调
     * @param callable $callback
     */
    function beforeAction(callable $callback)
    {
        $this->addHook(self::HOOK_BEFORE_ACTION, $callback);
    }

    /**
     * 在Action执行后回调
     * @param callable $callback
     */
    function afterAction(callable $callback)
    {
        $this->addHook(self::HOOK_AFTER_ACTION, $callback);
    }

    function __get($lib_name)
    {
        //如果不存在此对象，从工厂中创建一个
        if (empty($this->$lib_name))
        {
            //载入组件
            $this->$lib_name = $this->loadModule($lib_name);
        }
        return $this->$lib_name;
    }

    /**
     * 加载内置的Swoole模块
     * @param $module
     * @param $id
     * @throws NotFound
     * @return mixed
     */
    protected function loadModule($module, $id = 'master')
    {
        $key = $module . '_' . $id;
        if (empty($this->objects[$key]))
        {
            $this->factory_key = $id;
            $user_factory_file = self::$app_path . '/factory/' . $module . '.php';
            //尝试从用户工厂构建对象
            if (is_file($user_factory_file))
            {
                $object = require $user_factory_file;
            }
            //Swoole 2.0 协程模式
            elseif (self::$enableCoroutine)
            {
                $system_factory_2x_file = LIBPATH . '/factory_2x/' . $module . '.php';
                //不存在，继续使用 1.x 的工厂
                if (!is_file($system_factory_2x_file))
                {
                    goto get_factory_file;
                }
                $object = require $system_factory_2x_file;
            }
            //系统默认
            else
            {
                get_factory_file: $system_factory_file = LIBPATH . '/factory/' . $module . '.php';
                //组件不存在，抛出异常
                if (!is_file($system_factory_file))
                {
                    throw new NotFound("module [$module] not found.");
                }
                $object = require $system_factory_file;
            }
            $this->objects[$key] = $object;
        }
        return $this->objects[$key];
    }

    /**
     * 卸载的Swoole模块
     * @param $module
     * @param $object_id
     * @throws NotFound
     * @return bool
     */
    function unloadModule($module, $object_id = 'all')
    {
        //卸载全部
        if ($object_id == 'all')
        {
            //清除配置
            if (isset($this->config[$module]))
            {
                unset($this->config[$module]);
            }
            $find = false;
            foreach($this->objects as $key => $object)
            {
                list($name, $id) = explode('_', $key, 2);
                //找到了此模块
                if ($name === $module)
                {
                    $this->unloadModule($module, $id);
                    $find = true;
                }
            }
            return $find;
        }
        //卸载某个对象
        else
        {
            //清除配置
            if (isset($this->config[$module][$object_id]))
            {
                unset($this->config[$module][$object_id]);
            }
            $key = $module.'_'.$object_id;
            if (empty($this->objects[$key]))
            {
                return false;
            }
            $object = $this->objects[$key];
            //存在close方法，自动调用
            if (is_object($object) and method_exists($object, 'close'))
            {
                call_user_func(array($object, 'close'));
            }
            //删除对象
            unset($this->objects[$key]);
            //master
            if ($object_id == 'master')
            {
                $this->{$module} = null;
            }
            return true;
        }
    }

    function __call($func, $param)
    {
        //swoole built-in module
        if (isset(self::$multi_instance[$func]))
        {
            if (empty($param[0]) or !is_string($param[0]))
            {
                throw new Exception("module name cannot be null.");
            }
            return $this->loadModule($func, $param[0]);
        }
        //尝试加载用户定义的工厂类文件
        elseif(is_file(self::$app_path . '/factory/' . $func . '.php'))
        {
            $object_id = $func . '_' . $param[0];
            //已创建的对象
            if (isset($this->objects[$object_id]))
            {
                return $this->objects[$object_id];
            }
            else
            {
                $this->factory_key = $param[0];
                $object = require self::$app_path . '/factory/' . $func . '.php';
                $this->objects[$object_id] = $object;
                return $object;
            }
        }
        else
        {
            throw new Exception("call an undefine method[$func].");
        }
    }

    /**
     * 添加路由器
     * @param \Swoole\IFace\Router $router
     * @param $prepend bool
     */
    function addRouter(Swoole\IFace\Router $router, $prepend = false)
    {
        $this->addHook(Swoole::HOOK_ROUTE, array($router, 'handle'), $prepend);
    }

    /**
     * 设置路由器
     * @param $function
     */
    function router($function)
    {
        $this->router_function = $function;
    }

    /**
     * URL路由
     * @return array|bool
     */
    protected function urlRoute()
    {
        if (empty($this->hooks[self::HOOK_ROUTE]))
        {
            echo Swoole\Error::info('MVC Error!',"UrlRouter hook is empty");
            return false;
        }

        $uri = strstr($_SERVER['REQUEST_URI'], '?', true);
        if ($uri === false)
        {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $uri = trim($uri, '/');
        $mvc = array();

        //URL Router
        foreach ($this->hooks[self::HOOK_ROUTE] as $hook)
        {
            if (!is_callable($hook))
            {
                trigger_error("SwooleFramework: hook function[$hook] is not callable.");
                continue;
            }
            $mvc = $hook($uri);
            //命中
            if ($mvc !== false)
            {
                break;
            }
        }
        return $mvc;
    }

    /**
     * 设置应用程序路径
     * @param $dir
     */
    static function setAppPath($dir)
    {
        if (is_dir($dir))
        {
            self::$app_path = $dir;
        }
        else
        {
            \Swoole\Error::info("fatal error", "app_path[$dir] is not exists.");
        }
    }

    /**
     * 设置应用程序路径
     * @param $dir
     */
    static function setControllerPath($dir)
    {
        if (is_dir($dir))
        {
            self::$controller_path = $dir;
        }
        else
        {
            \Swoole\Error::info("fatal error", "controller_path[$dir] is not exists.");
        }
    }

    function handlerServer(Swoole\Request $request)
    {
        $response = new Swoole\Response();
        $request->setGlobal();

        //处理静态请求
        if (!empty($this->server->config['apps']['do_static']) and $this->server->doStaticRequest($request, $response))
        {
            return $response;
        }

        $php = Swoole::getInstance();

        //将对象赋值到控制器
        $php->request = $request;
        $php->response = $response;
        
        try
        {
            try
            {
                //捕获echo输出的内容
                if (self::$enableOutputBuffer)
                {
                    ob_start();
                    $response->body = $php->runMVC();
                    $response->body .= ob_get_contents();
                    ob_end_clean();
                }
                else
                {
                    $response->body = $php->runMVC();
                }
            }
            catch(Swoole\Exception\Response $e)
            {
                if ($request->finish != 1)
                {
                    $this->server->httpError(500, $response, $e->getMessage());
                }
            }
            catch (Swoole\Exception\NotFound $e)
            {
                $this->server->httpError(404, $response, $e->getMessage());
            }
        }
        catch (\Exception $e)
        {
            $this->server->httpError(500, $response, $e->getMessage()."<hr />".nl2br($e->getTraceAsString()));
        }
        
        //重定向
        if (isset($response->head['Location']) and ($response->http_status < 300 or $response->http_status > 399))
        {
            $response->setHttpStatus(301);
        }
        return $response;
    }

    /**
     * 加载所有模块
     */
    function loadAllModules()
    {
        //redis
        $redis_conf = $this->config['redis'];
        if (!empty($redis_conf))
        {
            foreach ($redis_conf as $k => $v)
            {
                $this->loadModule('redis', $k);
            }
        }
        //cache
        $cache_conf = $this->config['cache'];
        if (!empty($cache_conf))
        {
            foreach ($cache_conf as $k => $v)
            {
                $this->loadModule('cache', $k);
            }
        }
        //db
        $db_conf = $this->config['db'];
        if (!empty($db_conf))
        {
            foreach ($db_conf as $k => $v)
            {
                $this->loadModule('db', $k);
            }
        }
    }

    function runHttpServer($host = '0.0.0.0', $port = 9501, $config = array())
    {
        define('SWOOLE_SERVER', true);
        define('SWOOLE_HTTP_SERVER', true);
        $this->loadAllModules();
        $this->ext_http_server = $this->http = new Swoole\Http\ExtServer($config);
        Swoole\Network\Server::$useSwooleHttpServer = true;
        $server = new Swoole\Network\Server($host, $port);
        $server->setProtocol($this->http);
        $server->run($config);
    }

    /**
     * 运行MVC处理模型
     */
    function runMVC()
    {
        if (empty($this->request))
        {
            $this->request = new \Swoole\Request();
            $this->request->initWithLamp();
        }

        $mvc = call_user_func($this->router_function);
        if ($mvc === false)
        {
            $this->http->status(404);
            throw new \Swoole\Exception\NotFound("MVC Error: url route failed!");
        }
        //check controller name
        if (!preg_match('/^[a-z0-9_]+$/i', $mvc['controller']))
        {
        	throw new \Swoole\Exception\NotFound("MVC Error: controller[{$mvc['controller']}] name incorrect.Regx: /^[a-z0-9_]+$/i");
        }
        //check view name
        if (!preg_match('/^[a-z0-9_]+$/i', $mvc['view']))
        {
            throw new \Swoole\Exception\NotFound("MVC Error: view[{$mvc['view']}] name incorrect.Regx: /^[a-z0-9_]+$/i");
        }
        //directory
        if (isset($mvc['directory']) and !preg_match('/^[a-z0-9_]+$/i', $mvc['directory']))
        {
            throw new \Swoole\Exception\NotFound("MVC Error: directory[{$mvc['view']}] incorrect. Regx: /^[a-z0-9_]+$/i");
        }

		$this->env['mvc'] = $mvc;

        //控制器名称
        $controller_name = ucwords($mvc['controller']);
        //控制器文件目录
        if (self::$controller_path)
        {
            $controller_dir = self::$controller_path;
        }
        else
        {
            $controller_dir = self::$app_path . '/controllers';
        }
        //子目录
        if (isset($mvc['directory']))
        {
            $directory = ucwords($mvc['directory']);
            $controller_class = '\\App\\Controller\\' . $directory . '\\' . $controller_name;
            $controller_dir .= '/' . $directory;
        }
        else
        {
            $controller_class = '\\App\\Controller\\' . $controller_name;
        }
        //控制器代码文件
        $controller_file = $controller_dir . '/' . $controller_name . '.php';

        if (class_exists($controller_class, false))
        {
            goto do_action;
        }
        else
        {
            if (is_file($controller_file))
            {
                require_once $controller_file;
                goto do_action;
            }
        }

        //file not found
        $this->http->status(404);
        throw new \Swoole\Exception\NotFound("MVC Error: Controller <b>{$mvc['controller']}</b>[{$controller_file}] not exist!<br />\nURL: {$_SERVER['REQUEST_URI']}");

        do_action:

        //服务器模式下，尝试重载入代码
        if (defined('SWOOLE_SERVER'))
        {
            $this->reloadController($mvc, $controller_file);
        }
        $controller = new $controller_class($this);
        if (!method_exists($controller, $mvc['view']))
        {
            $this->http->status(404);
            $msg = "MVC Error:  {$mvc['controller']}->{$mvc['view']} Not Found!<br />\n";
            $msg .= "URL: {$_SERVER['REQUEST_URI']}";
            throw new \Swoole\Exception\NotFound($msg);
        }

        $param = empty($mvc['param']) ? null : $mvc['param'];
        $method = $mvc['view'];
        //before action
        $this->callHook(self::HOOK_BEFORE_ACTION);
        //magic method
        if (method_exists($controller, '__beforeAction'))
        {
            $controller->{'__beforeAction'}($mvc);
        }
        //do action
        try
        {
            $return = $controller->$method($param);
        }
        catch (\Exception $e)
        {
            $catched = false;
            foreach ($this->catchers as $k => $v)
            {
                if (call_user_func($v['handler'], $e) === false)
                {
                    continue;
                }
                else
                {
                    $catched = true;
                }
            }
            //移除非永久的捕获器
            foreach ($this->catchers as $k => $v)
            {
                if (!$v['persistent'])
                {
                    unset($this->catchers[$k]);
                }
            }
            if (!$catched)
            {
                throw $e;
            }
        }
        //magic method
        if (method_exists($controller, '__afterAction'))
        {
            $controller->{'__afterAction'}($return);
        }
        //after action
        $this->callHook(self::HOOK_AFTER_ACTION);
        //响应请求
        if (!empty($controller->is_ajax))
        {
            $this->http->header('Cache-Control', 'no-cache, must-revalidate');
            $this->http->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
            $this->http->header('Content-Type', 'application/json');
            $return = json_encode($return);
        }
        if (defined('SWOOLE_SERVER'))
        {
            return $return;
        }
        else
        {
            echo $return;
        }
    }

    function addCommand($class)
    {
        if (!class_exists($class))
        {
            throw new NotFound("Command[$class] not found.");
        }
        $command = new $class;
        if ($command instanceof Symfony\Component\Console\Command\Command)
        {
            $this->commands[] = $command;
        }
        else
        {
            throw new Swoole\Exception\InvalidParam("class[$class] not instanceof " . ' Symfony\Component\Console\Command\Command');
        }
    }

    function addCatcher(callable $catcher, $persistent = false)
    {
        $this->catchers[] = ['handler' => $catcher, 'persistent' => $persistent];
    }

    /**
     * 命令行工具
     * @throws Exception
     */
    function runConsole()
    {
        $app = new  Symfony\Component\Console\Application("<info>Swoole Framework</info> Console Tool.");
        $app->add(new Swoole\Command\MakeController());
        $app->add(new Swoole\Command\MakeModel());
        $app->add(new Swoole\Command\MakeConfig());
        $app->run();
    }

    function reloadController($mvc, $controller_file)
    {
        if (extension_loaded('runkit') and $this->server->config['apps']['auto_reload'])
        {
            clearstatcache();
            $fstat = stat($controller_file);
            //修改时间大于加载时的时间
            if(isset($this->env['controllers'][$mvc['controller']]) && $fstat['mtime'] > $this->env['controllers'][$mvc['controller']]['time'])
            {
                runkit_import($controller_file, RUNKIT_IMPORT_CLASS_METHODS|RUNKIT_IMPORT_OVERRIDE);
                $this->env['controllers'][$mvc['controller']]['time'] = time();
            } else {
                $this->env['controllers'][$mvc['controller']]['time'] = time();
            }
        }
    }
}
