<?php
namespace Swoole;

/**
 * 视图类
 * 提供一个简单试图封装
 * @package SwooleSystem
 * @author Tianfeng.Han
 * @subpackage MVC
 */
class View extends \ArrayObject
{
    protected $_var = array();
    protected $trace = array();
    protected $swoole;
    public $template_dir = '';
    public $if_pagecache = false;
    public $cache_life = 3600;
    public $show_runtime = false;

    function __construct($swoole)
    {
        $this->swoole = $swoole;
        $this->template_dir = \Swoole::$app_path . '/views/';
    }

    /**
     * +----------------------------------------------------------
     * 模板变量赋值
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param mixed $name
     * @param mixed $value
    +----------------------------------------------------------
     */
    public function assign($name, $value = '')
    {
        if (is_array($name))
        {
            $this->_var = array_merge($this->_var, $name);
        }
        elseif (is_object($name))
        {
            foreach ($name as $key => $val)
            {
                $this->_var[$key] = $val;
            }
        }
        else
        {
            $this->_var[$name] = $value;
        }
    }

    /**
     * +----------------------------------------------------------
     * Trace变量赋值
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param mixed $name
     * @param mixed $value
    +----------------------------------------------------------
     */
    public function trace($title, $value = '')
    {
        if (is_array($title))
        {
            $this->trace = array_merge($this->trace, $title);
        }
        else
        {
            $this->trace[$title] = $value;
        }
    }

    /**
     * +----------------------------------------------------------
     * 取得模板变量的值
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $name
    +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    public function get($name)
    {
        if (isset($this->_var[$name]))
        {
            return $this->_var[$name];
        }
        else
        {
            return false;
        }
    }

    /**
     * +----------------------------------------------------------
     * 加载模板和页面输出 可以返回输出内容
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $templateFile 模板文件名 留空为自动获取
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    public function display($templateFile = '', $charset = '', $contentType = 'text/html')
    {
        $this->fetch($templateFile, $charset, $contentType, true);
    }

    /**
     * +----------------------------------------------------------
     * 输出布局模板
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $display 是否直接显示
     * +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    public function layout($content, $charset = '', $contentType = 'text/html')
    {
        // 查找布局包含的页面
        $find = preg_match_all('/<!-- layout::(.+?)::(.+?) -->/is', $content, $matches);
        if ($find)
        {
            for ($i = 0; $i < $find; $i++)
            {
                // 读取相关的页面模板替换布局单元
                if (0 === strpos($matches[1][$i], '$'))
                {
                    // 动态布局
                    $matches[1][$i] = $this->get(substr($matches[1][$i], 1));
                }
                if (0 != $matches[2][$i])
                {
                    // 设置了布局缓存
                    // 检查布局缓存是否有效
                    $guid = md5($matches[1][$i]);
                    $cache = S($guid);
                    if ($cache)
                    {
                        $layoutContent = $cache;
                    }
                    else
                    {
                        $layoutContent = $this->fetch($matches[1][$i], $charset, $contentType);
                        S($guid, $layoutContent, $matches[2][$i]);
                    }
                }
                else
                {
                    $layoutContent = $this->fetch($matches[1][$i], $charset, $contentType);
                }
                $content = str_replace($matches[0][$i], $layoutContent, $content);
            }
        }

        return $content;
    }

    /**
     * +----------------------------------------------------------
     * 加载模板和页面输出
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $templateFile 模板文件名 留空为自动获取
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $display 是否直接显示
     * +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    public function fetch($templateFile = '', $charset = '', $contentType = 'text/html', $display = false)
    {
        $GLOBALS['_viewStartTime'] = microtime(true);
        if (null === $templateFile)
        {
            // 使用null参数作为模版名直接返回不做任何输出
            return;
        }
        if (empty($charset))
        {
            $charset = 'utf-8';
        }
        // 网页字符编码
        header("Content-Type:" . $contentType . "; charset=" . $charset);
        header("Cache-control: private");  //支持页面回跳
        //页面缓存
        ob_start();
        ob_implicit_flush(0);
        $this->render($templateFile);
        // 获取并清空缓存
        $content = ob_get_clean();

        // 布局模板解析
        $content = $this->layout($content, $charset, $contentType);

        // 输出模板文件
        return $this->output($content, $display);
    }

    private function render($templateFile)
    {
        extract($this->_var);
        $templateFile = $this->parseTemplateFile($templateFile);
        require($templateFile);
    }

    /**
     * +----------------------------------------------------------
     *  创建静态页面
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @htmlfile 生成的静态文件名称
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * +----------------------------------------------------------
     * @return string
    +----------------------------------------------------------
     */
    public function buildHtml($htmlfile = '', $templateFile = '', $charset = '', $contentType = 'text/html')
    {
        $content = $this->fetch($templateFile, $charset, $contentType);
        if (empty($htmlfile))
        {
            $htmlfile = HTML_PATH . rtrim($_SERVER['PATH_INFO'], '/') . C('HTML_FILE_SUFFIX');
        }
        if (!is_dir(dirname($htmlfile)))
        {
            // 如果静态目录不存在 则创建
            mk_dir(dirname($htmlfile));
        }
        if (false === file_put_contents($htmlfile, $content))
        {
            throw_exception(L('_CACHE_WRITE_ERROR_'));
        }

        return $content;//readfile($htmlfile);
    }

    /**
     * +----------------------------------------------------------
     * 输出模板
     * +----------------------------------------------------------
     * @access protected
     * +----------------------------------------------------------
     * @param string $content 模板内容
     * @param boolean $display 是否直接显示
     * +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    protected function output($content, $display)
    {
        if ($this->if_pagecache)
        {
            $pagecache = new Swoole_pageCache($this->cache_life);
            if ($pagecache->isCached())
            {
                $pagecache->load();
            }
            else
            {
                $pagecache->create($content);
            }
        }
        if ($display)
        {
            $showTime = $this->showTime();
            echo $content;
            if ($this->show_runtime)
            {
                $this->showTrace();
            }

            return null;
        }
        else
        {
            return $content;
        }
    }

    private function parseTemplateFile($templateFile)
    {
        if ('' == $templateFile)
        {
            $templateFile = $this->swoole->env['mvc']['controller'] . '_' . $this->swoole->env['mvc']['view'] . '.html';
        }
        $templateFile = $this->template_dir . $templateFile;
        if (!file_exists($templateFile))
        {
            Error::info('View Error!', 'Template file not exists! <b>' . $templateFile . '</b>');
        }

        return $templateFile;
    }

    /**
     * +----------------------------------------------------------
     * 显示运行时间、数据库操作、缓存次数、内存使用信息
     * +----------------------------------------------------------
     * @access protected
     * +----------------------------------------------------------
     * @return string
    +----------------------------------------------------------
     */
    protected function showTime()
    {
        // 显示运行时间
        $startTime = $this->swoole->env['runtime']['start'];
        $endTime = microtime(true);
        $total_run_time = number_format(($endTime - $startTime), 4);
        $showTime = '执行时间: ' . $total_run_time . 's ';

        $startMem = array_sum(explode(' ', $this->swoole->env['runtime']['mem']));
        $endMem = array_sum(explode(' ', memory_get_usage()));
        $showTime .= ' | 内存占用:' . number_format(($endMem - $startMem) / 1024) . ' kb';

        return $showTime;
    }

    /**
     * +----------------------------------------------------------
     * 显示页面Trace信息
     * +----------------------------------------------------------
     * @access protected
     * +----------------------------------------------------------
     * @param string $showTime 运行时间信息
     * +----------------------------------------------------------
     */
    public function showTrace($detail = false)
    {
        // 显示页面Trace信息 读取Trace定义文件
        // 定义格式 return array('当前页面'=>$_SERVER['PHP_SELF'],'通信协议'=>$_SERVER['SERVER_PROTOCOL'],...);
        $_trace = array();
        // 系统默认显示信息
        $this->trace('当前页面', $_SERVER['REQUEST_URI']);
        $this->trace('请求方法', $_SERVER['REQUEST_METHOD']);
        $this->trace('通信协议', $_SERVER['SERVER_PROTOCOL']);
        $this->trace('请求时间', date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']));
        $this->trace('用户代理', $_SERVER['HTTP_USER_AGENT']);
        if (isset($_SESSION))
        {
            $this->trace('会话ID', session_id());
        }
        $this->trace('读取数据库', $this->swoole->db->read_times . '次');
        $this->trace('写入数据库', $this->swoole->db->write_times . '次');
        $included_files = get_included_files();
        $this->trace('加载文件', count($included_files));
        $this->trace('PHP执行', $this->showTime());
        $_trace = array_merge($_trace, $this->trace);
        // 调用Trace页面模板
        echo <<<HTML
		<div id="think_page_trace" style="background:white;margin:6px;font-size:14px;border:1px dashed silver;padding:8px">
		<fieldset id="querybox" style="margin:5px;">
		<legend style="color:gray;font-weight:bold">页面Trace信息</legend>
		<div style="overflow:auto;height:300px;text-align:left;">
HTML;

        foreach ($_trace as $key => $info)
        {
            echo $key . ' : ' . $info . '<br/>';
        }
        if ($detail)
        {
            //输出包含的文件
            echo '加载的文件<br/>';
            foreach ($included_files as $file)
            {
                echo 'require ' . $file . '<br/>';
            }
        }
        echo "</div></fieldset>	</div>";
    }
}
