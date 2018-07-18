<?php
namespace Swoole;
/**
 * 插件加载器
 * 加载一个Swoole插件
 * @author Tianfeng.Han
 * @package SwooleSystem
 * @subpackage base
 */
class PluginLoader
{
	private $swoole;
	private $plugins = array();

	function __construct($swoole)
	{
		$this->swoole = $swoole;
	}
	/**
	 * 查看插件信息，如果没有加载此插件，则返回false
	 * @param $plugin_name
	 * @return unknown_type
	 */
	function info($plugin_name)
	{
		if(isset($this->plugins[$plugin_name])) return $this->plugins[$plugin_name];
		return false;
	}
	/**
	 * 注册插件对象到Swoole树
	 * @param $plugin_name
	 * @param $object
	 * @return unknown_type
	 */
	function register($plugin_name,$object)
	{
		$this->swoole->$plugin_name = $object;
	}
	/**
	 * 导入插件
	 * @param $plugin_name
	 * @return unknown_type
	 */
	function load($plugin_name,$plugin_param = null)
	{
		$path = WEBPATH.'/swoole_plugin/'.$plugin_name.'/Swoole.plugin.php';
		if(file_exists($path))
		{
			require_once($path);
			$this->plugins[$plugin_name] = $swoole_plugin;
		}
		else Error::info('Plugin not Found!',"Plugin file <b>$path</b> not exists!");
	}
	/**
	 * 必须包含某个插件
	 * @param $plugin_name
	 * @return unknown_type
	 */
	function require_plugin($plugin_name)
	{
		if(!isset($this->plugins[$plugin_name])) $this->load($plugin_name);
	}
}
