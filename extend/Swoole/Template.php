<?php
namespace Swoole;

require(LIBPATH . "/module/smarty/Smarty.class.php");

/**
 * Smarty模板系统封装类
 * 提供模板引擎类，可以访问到MVC结构，增加了pagecache静态页面缓存的功能
 *
 * @author     Tianfeng.Han
 * @package    SwooleSystem
 * @subpackage template
 */
class Template extends \Smarty
{
	public $if_pagecache = false;
	public $cache_life = 3600;

	function __construct()
	{
		$this->compile_dir = WEBPATH."/cache/templates_c";
		$this->config_dir = WEBPATH."/configs";
		$this->cache_dir = WEBPATH."/cache/pagecache";
		$this->left_delimiter = "{{";
		$this->right_delimiter = "}}";
	}

	function __init()
	{
		$this->clear_all_assign();
	}

	function set_template_dir($dir)
	{
		$this->template_dir = WEBPATH . '/' . $dir;
	}

	function set_cache($time = 3600)
	{
		$this->caching = 1;
		$this->cache_lifetime = $time;
	}

	/**
	 * 缓存当前页面
	 * @return bool
	 */
	function pagecache()
	{
		$pagecache = new PageCache($this->cache_life);
		if ($pagecache->isCached())
		{
			$pagecache->load();
		}
		else
		{
			return false;
		}
		return true;
	}

	/**
	 * 传引用到模板中
	 * @param $key
	 * @param $value
	 * @return unknown_type
	 */
	function ref($key,&$value)
	{
	    $this->_tpl_vars[$key] = &$value;
	}

	function display($template = null, $cache_id = null, $complile_id = null)
	{
		if ($template == null)
		{
			global $php;
			$template = $php->env['mvc']['controller'] . '_' . $php->env['mvc']['view'] . '.html';
		}
		if ($this->if_pagecache)
		{
			$pagecache = new PageCache($this->cache_life);
			if (!$pagecache->isCached())
			{
				$pagecache->create(parent::fetch($template, $cache_id, $complile_id));
			}
			$pagecache->load();
		}
		else
		{
			parent::display($template, $cache_id, $complile_id);
		}
	}

	/**
	 * 生成静态页面
	 * @param $template
	 * @param $filename
	 * @return bool
	 */
	function outhtml($template, $filename, $path = '')
	{
		if ($path == '')
		{
			$path = dirname($filename);
			$filename = basename($filename);
		}
		if (!is_dir($path))
		{
			mkdir($path, 0777, true);
		}
		$content = $this->fetch($template);
		file_put_contents($path . '/' . $filename, $content);
		return true;
	}

	function push($data)
	{
		foreach($data as $key=>$value) $this->assign($key,$value);
	}
}
